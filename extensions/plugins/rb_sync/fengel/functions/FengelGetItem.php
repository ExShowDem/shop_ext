<?php
/**
 * @package     Aesir.E-Commerce.Plugin
 * @subpackage  Fengel
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('JPATH_BASE') or die;

use Joomla\CMS\Factory;
use Joomla\Registry\Registry;
use Joomla\CMS\Language\Text;

require_once __DIR__ . '/base.php';

/**
 * Get Item function.
 *
 * @package     Aesir.E-Commerce.Plugin
 * @subpackage  Fengel
 * @since       1.0
 */
class FengelGetItem extends FengelFunctionBase
{
	/**
	 * @var string
	 */
	public $syncName = 'fengel.item_related';

	/**
	 * @var string
	 */
	public $tableClassName = 'Product_Item';

	/**
	 * @var array
	 */
	public $executedProducts = array();

	/**
	 * PimFunctionBase constructor.
	 */
	public function __construct()
	{
		parent::__construct();

		$this->avoidOverrideWSProperties = array_merge(
			$this->avoidOverrideWSProperties, array(
				'discontinued', 'stock_upper_level', 'stock_lower_level'
			)
		);
	}

	/**
	 * Read and store the data.
	 *
	 * @param   RTable     $webserviceData   Webservice object
	 * @param   Registry   $params           Parameters of the plugin
	 *
	 * @return  boolean
	 *
	 * @throws  Exception
	 */
	public function read(&$webserviceData, $params)
	{
		$db = Factory::getDbo();

		try
		{
			if ($params->get('source', 'wsdl') != 'folder')
			{
				$session = Factory::getSession();
				$tmpXML  = $session->get('sync_GetItem', '', 'com_redshopb');

				if ($tmpXML == '')
				{
					throw new Exception(sprintf(Text::_('PLG_RB_SYNC_FENGEL_NO_TEMP'), 'GetItem'));
				}

				// Tries to load cache file, or else loads live GetCustomer function
				if (file_exists($tmpXML))
				{
					$xml = simplexml_load_file($tmpXML);
				}
				else
				{
					$xml = $this->client->Red_GetItemVariantRealations();
					$xml->asXML($tmpXML);
				}
			}
			else
			{
				$xml = $this->client->Red_GetItemVariantRealations();
			}

			if (!is_object($xml))
			{
				throw new Exception(Text::_('PLG_RB_SYNC_FENGEL_FAILED_TO_FETCH_ITEMS'));
			}

			$db->transactionStart();

			// Check to see if we are already run Sync which is not finished (so we can have multiple parts if webservice is too big)
			if (!$this->isCronExecuted($this->cronName))
			{
				// We set them with a flag so we can delete the ones which are not present in the latest Sync xml
				$this->setSyncRowsAsExecuted($this->syncName);
				$this->setSyncRowsAsExecuted('fengel.product_item');

				// We set cron as executed so we can have multipart process if the process takes too long
				$this->setCronAsExecuted($this->cronName);
			}
			else
			{
				// Get list executed in previous sync items because this is multipart process
				$this->executed         = $this->getPreviousSyncExecutedList($this->syncName);
				$this->executedProducts = $this->getPreviousSyncExecutedList('fengel.product_item');
				$this->counter          = count($this->executed);
			}

			$query        = $db->getQuery(true);
			$productTable = RTable::getInstance('Product', 'RedshopbTable')
				->setOption('lockingMethod', 'Sync');

			foreach ($xml as $obj)
			{
				foreach ($obj->Keys->KeyInfo as $keyinfo)
				{
					$this->counterTotal += count($keyinfo->Pairs);
				}

				if ($this->goToNextPart == true || $this->isExecutionTimeExceeded())
				{
					$this->goToNextPart = true;
					continue;
				}

				if (array_key_exists((string) $obj->No, $this->executedProducts))
				{
					continue;
				}

				$productId = $this->findSyncedId('fengel.product', (string) $obj->No);

				if (!$productId || !$productTable->load($productId))
				{
					RedshopbHelperSync::addMessage(Text::sprintf('PLG_RB_SYNC_FENGEL_PRODUCT_NOT_FOUND', (string) $obj->No), 'warning');
					continue;
				}

				$isNewProductRelation = true;
				$allData              = $this->findSyncedId('fengel.product_item', (string) $obj->No, '', true);
				$md5Row               = md5('1' . $obj->asXML());

				if (!empty($allData))
				{
					$isNewProductRelation = false;
				}

				if (!empty($allData)
					&& $allData->serialize == $md5Row
					&& $allData->local_id == $productId
					&& $webserviceData->get('fullSync', 1) != 1
				)
				{
					foreach ($obj->Keys->KeyInfo as $keyinfo)
					{
						$this->counter += count($keyinfo->Pairs);
					}

					$query->clear()
						->update($db->qn('#__redshopb_sync'))
						->set('execute_sync = 0')
						->where('reference = ' . $db->q($this->syncName))
						->where('remote_parent_key = ' . $db->q((string) $obj->No));

					$db->setQuery($query)->execute();
				}
				else
				{
					$availableItems = array();

					foreach ($obj->Keys->KeyInfo as $keyinfo)
					{
						$blocked = (string) $keyinfo->Blocked;
						$state   = ($blocked == 'true' ? 0 : 1);
						$key     = (string) $keyinfo->Key;

						if ($this->goToNextPart == true || $this->isExecutionTimeExceeded())
						{
							$this->goToNextPart = true;
							break;
						}

						if (array_key_exists($key . '_' . (string) $obj->No, $this->executed))
						{
							$availableItems[] = $this->findSyncedId($this->syncName, $key, (string) $obj->No);

							continue;
						}

						$this->counter++;
						$foundAttributeValueIds = array();
						$attributeValueNotFound = false;
						$skipItem               = false;

						foreach ($keyinfo->Pairs->Pair as $onePart)
						{
							$values = explode(';', $onePart->Code);

							if (in_array('BLOCK', $values) || in_array('BLOK', $values))
							{
								$skipItem = true;
								break;
							}

							$productAttributeValueId = $this->findSyncedId('fengel.attribute', $values['0'] . '_' . $values['1'], (string) $obj->No);

							if (!$productAttributeValueId)
							{
								$attributeValueNotFound = true;
								break;
							}

							$foundAttributeValueIds[] = $productAttributeValueId;
						}

						if ($attributeValueNotFound)
						{
							RedshopbHelperSync::addMessage(
								Text::sprintf(
									'PLG_RB_SYNC_FENGEL_ATTRIBUTE_NOT_FOUND', $key, (string) $obj->No
								),
								'warning'
							);
						}

						if ($skipItem || $attributeValueNotFound)
						{
							continue;
						}

						$table    = RTable::getInstance($this->tableClassName, 'RedshopbTable')
							->setOption('forceWebserviceUpdate', true)
							->setOption('lockingMethod', 'Sync');
						$row      = array(
							'state' => $state,
							'product_id' => $productId
						);
						$isNew    = true;
						$itemData = $this->findSyncedId($this->syncName, $key, (string) $obj->No, true, $table);

						if ($itemData)
						{
							if (!$itemData->deleted && $table->load($itemData->local_id))
							{
								$isNew = false;
							}

							// If item not exists, then user delete it, so lets skip it
							elseif ($itemData->deleted)
							{
								$this->recordSyncedId(
									$this->syncName, $key, '', (string) $obj->No, false, 0, $itemData->serialize, true
								);

								continue;
							}
							else
							{
								$this->deleteSyncedId($this->syncName, $key, (string) $obj->No);
							}
						}

						if (!$table->save($row))
						{
							throw new Exception($table->getError());
						}

						$this->recordSyncedId(
							$this->syncName, $key, $table->id, (string) $obj->No, $isNew, 0, '',
							false, '', $table, 1
						);

						$query->clear()
							->delete($db->qn('#__redshopb_product_item_attribute_value_xref'))
							->where('product_item_id = ' . (int) $table->id);

						$db->setQuery($query)->execute();

						$availableItems[] = (int) $table->id;

						foreach ($foundAttributeValueIds as $foundAttributeValueId)
						{
							$query->clear()
								->insert('#__redshopb_product_item_attribute_value_xref')
								->columns('product_item_id, product_attribute_value_id')
								->values((int) $table->id . ', ' . (int) $foundAttributeValueId);

							$db->setQuery($query)->execute();
						}
					}

					if ($this->goToNextPart == true || $this->isExecutionTimeExceeded())
					{
						$this->goToNextPart = true;
						continue;
					}

					// Remove extra items
					$query = $db->getQuery(true)
						->delete($db->qn($this->tableName))
						->where('product_id = ' . (int) $productId);

					if (count($availableItems))
					{
						$query->where('id NOT IN (' . implode(',', $availableItems) . ')');
					}

					$db->setQuery($query)->execute();
				}

				$this->recordSyncedId(
					'fengel.product_item', (string) $obj->No, $productId, '', $isNewProductRelation, 0, $md5Row
				);
			}

			// In last part delete not using items
			if (!$this->goToNextPart && !$this->isExecutionTimeExceeded())
			{
				$db->transactionCommit();
				$db->transactionStart();

				// Remove items that were not present in the XML data
				$this->deleteRowsNotPresentInRemote($this->syncName, $this->tableName);
				$this->deleteRowsNotPresentInRemote('fengel.product_item');

				// We are setting cron as finished (no more parts)
				$this->setCronAsFinished($this->cronName);
			}

			$db->transactionCommit();
		}
		catch (Exception $e)
		{
			$db->transactionRollback();
			RedshopbHelperSync::addMessage($e->getMessage(), 'error');

			return false;
		}

		return $this->outputResult();
	}
}
