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
use Joomla\CMS\Date\Date;

require_once __DIR__ . '/base.php';

/**
 * GetProduct function.
 *
 * @package     Aesir.E-Commerce.Plugin
 * @subpackage  Fengel
 * @since       1.0
 */
class FengelGetProduct extends FengelFunctionBase
{
	/**
	 * @var string
	 */
	public $syncName = 'fengel.product';

	/**
	 * @var string
	 */
	public $tableClassName = 'Product';

	/**
	 * PimFunctionBase constructor.
	 */
	public function __construct()
	{
		parent::__construct();

		$this->avoidOverrideWSProperties = array_merge(
			$this->avoidOverrideWSProperties, array(
				'stock_lower_level', 'stock_upper_level', 'discontinued', 'featured',
				'unit_measure_id', 'hits', 'template_id'
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
			$xml = $this->client->getItem('', '');

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

				// We set cron as executed so we can have multipart process if the process takes too long
				$this->setCronAsExecuted($this->cronName);
			}
			else
			{
				// Get list executed in previous sync items because this is multipart process
				$this->executed = $this->getPreviousSyncExecutedList($this->syncName);
				$this->counter  = count($this->executed);
			}

			$query             = $db->getQuery(true)
				->select('DISTINCT remote_parent_key AS type')
				->from($db->qn('#__redshopb_sync'))
				->where('reference = ' . $db->q('fengel.category'))
				->where('local_id IS NOT NULL')
				->where('execute_sync = 0');
			$tagTypes          = $db->setQuery($query)
				->loadObjectList();
			$washCareSpecTable = RTable::getAdminInstance('Wash_Care_Spec')
				->setOption('lockingMethod', 'Sync');

			foreach ($xml->Item as $item)
			{
				$this->counterTotal++;

				if ($this->goToNextPart == true || $this->isExecutionTimeExceeded())
				{
					$this->goToNextPart = true;
					continue;
				}

				$attributes     = $item->attributes();
				$row            = array();
				$row['sku']     = (string) $attributes['No'];
				$row['service'] = 0;
				$row['name']    = (string) $item->Description;

				if (array_key_exists($row['sku'] . '_', $this->executed))
				{
					continue;
				}

				$this->counter++;
				$categoryIds     = array();
				$washCareSpecIds = array();
				$customers       = array();
				$tagIds          = array();
				$now             = Date::getInstance();
				$nowFormatted    = $now->toSql();

				// Store the product
				$productTable = RTable::getAdminInstance($this->tableClassName)
					->setOption('forceWebserviceUpdate', true)
					->setOption('lockingMethod', 'Sync')
					->setOption('wash_care_relate.store', true);
				$isNew        = true;

				if (isset($item->Tags->Tag))
				{
					foreach ($item->Tags->Tag as $category)
					{
						$categoryAttributes = current($category->attributes());
						$categoryNum        = (string) $categoryAttributes['TagId'];
						$categoryId         = $this->findSyncedId('fengel.itemgroup', $categoryNum);

						if ($categoryId)
						{
							$categoryIds[] = $categoryId;
						}
						else
						{
							RedshopbHelperSync::addMessage(Text::sprintf('PLG_RB_SYNC_FENGEL_CATEGORY_NOT_FOUND', $categoryNum), 'warning');
						}
					}
				}

				if ($tagTypes)
				{
					foreach ($tagTypes as $tagType)
					{
						$type = (string) $tagType->type;
						$tag  = (string) $item->Categories->$type;

						if ($tag)
						{
							$tagId = $this->findSyncedId('fengel.category', $tag, $type);

							if ($tagId)
							{
								$tagIds[] = $tagId;
							}
							else
							{
								RedshopbHelperSync::addMessage(Text::sprintf('PLG_RB_SYNC_FENGEL_TAG_NOT_FOUND', $tag), 'warning');
							}
						}
					}
				}

				if (isset($item->Categories->Art) && (string) $item->Categories->Art == 'Ydelse')
				{
					$row['service'] = 1;
				}

				if (isset($item->WashCareSpec->Types->Type))
				{
					foreach ($item->WashCareSpec->Types->Type as $washCareSpec)
					{
						$washCareSpecId = $this->findSyncedId(
							'fengel.wash_care_spec', (string) $washCareSpec->Code, (string) $washCareSpec->TypeCode
						);

						if ($washCareSpecId && $washCareSpecTable->load($washCareSpecId))
						{
							$washCareSpecIds[] = array('id' => $washCareSpecId, 'ordering' => (int) $washCareSpec->Sort);
						}
					}
				}

				if (isset($item->Customers))
				{
					foreach ($item->Customers as $customer)
					{
						$customerId = $this->findSyncedId('fengel.customer', (string) $customer->Customer);

						if ($customerId)
						{
							$customers[] = $customerId;
						}
					}
				}

				$itemData = $this->findSyncedId($this->syncName, $row['sku'], '', true, $productTable);

				if ($itemData)
				{
					if (!$itemData->deleted && $productTable->load($itemData->local_id))
					{
						$isNew = false;
					}

					// If item not exists, then user delete it, so lets skip it
					elseif ($itemData->deleted)
					{
						$this->recordSyncedId(
							$this->syncName, $row['sku'], '', '', false,
							0, $itemData->serialize, true
						);

						continue;
					}
					else
					{
						$this->deleteSyncedId($this->syncName, $row['sku']);
					}
				}

				if ($isNew)
				{
					$row['created_date'] = $nowFormatted;
				}
				else
				{
					$row['modified_date'] = $nowFormatted;
				}

				$row['state']      = 1;
				$row['company_id'] = null;

				if (count($tagIds) > 0)
				{
					$row['tag_id'] = $tagIds;
				}

				if (count($washCareSpecIds) > 0)
				{
					$row['wash_care_spec_id'] = $washCareSpecIds;
				}

				if (count($categoryIds) > 0)
				{
					$row['category_id'] = $categoryIds;
				}

				if (count($customers) > 0)
				{
					$row['customer_ids'] = $customers;
				}

				if ((string) $item->NewsDate)
				{
					$row['date_new'] = (string) $item->NewsDate;
				}
				else
				{
					$row['date_new'] = '0000-00-00';
				}

				if (!$productTable->save($row))
				{
					throw new Exception($productTable->getError());
				}

				if ($this->translationTable)
				{
					if (isset($item->ItemTranslations->ItemTranslation))
					{
						foreach ($item->ItemTranslations->ItemTranslation as $itemTranslation)
						{
							$objItemTranslation = $itemTranslation->attributes();

							if (isset($objItemTranslation['LanguageCode'])
								&& isset($itemTranslation->Description)
								&& (string) $itemTranslation->Description != '')
							{
								$langCode = $this->getLanguageTag((string) $objItemTranslation['LanguageCode']);

								$result = $this->storeTranslation(
									$this->translationTable,
									$productTable,
									$langCode,
									array(
										'id' => $productTable->id,
										'name' => (string) $itemTranslation->Description
									)
								);

								if ($result !== true)
								{
									throw new Exception($result);
								}
							}
						}
					}

					$result = $this->storeTranslation(
						$this->translationTable,
						$productTable,
						'da-DK',
						array(
							'id' => $productTable->id,
							'name' => (string) $item->Description
						)
					);

					if ($result !== true)
					{
						throw new Exception($result);
					}

					$result = $this->deleteNotSyncingLanguages($this->translationTable, $productTable);

					if ($result !== true)
					{
						throw new Exception($result);
					}
				}

				$this->recordSyncedId(
					$this->syncName, $row['sku'], $productTable->id, '', $isNew, 0, '',
					false, '', $productTable, 1
				);
			}

			if (!$this->goToNextPart)
			{
				$db->transactionCommit();
				$db->transactionStart();

				// Remove items that were not present in the XML data
				$this->deleteRowsNotPresentInRemote($this->syncName, $this->tableClassName, array(), true);

				// We are setting cron as finished (no more parts)
				$this->setCronAsFinished($this->cronName);
			}

			$db->transactionCommit();
		}
		catch (Exception $e)
		{
			$db->transactionRollback();

			if ($e->getMessage())
			{
				RedshopbHelperSync::addMessage($e->getMessage(), 'error');
			}

			return false;
		}

		return $this->outputResult();
	}
}
