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
 * Get Red Item Detail function.
 *
 * @package     Aesir.E-Commerce.Plugin
 * @subpackage  Fengel
 * @since       1.0
 */
class FengelGetRedItemDetail extends FengelFunctionBase
{
	/**
	 * @var array
	 */
	public $issetTranslation = array();

	/**
	 * @var string
	 */
	public $syncName = 'fengel.product_description';

	/**
	 * @var string
	 */
	public $tableClassName = 'Product_Description';

	/**
	 * PimFunctionBase constructor.
	 */
	public function __construct()
	{
		parent::__construct();

		$this->avoidOverrideWSProperties = array_merge(
			$this->avoidOverrideWSProperties, array(
				'description_intro'
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
			$arrayProductDetail = $this->client->getRedItemDetail(
				'', '', $webserviceData->params->get('login', ''),
				$webserviceData->params->get('password', '')
			);

			if (!is_array($arrayProductDetail))
			{
				throw new Exception(Text::_('PLG_RB_SYNC_FENGEL_FAILED_TO_FETCH_ITEMS'));
			}

			$db->transactionStart();
			$lang = RTranslationHelper::getSiteLanguage();

			if (!$this->isCronExecuted($this->cronName))
			{
				// Fix flag from all old items as not synced
				$this->setSyncRowsAsExecuted($this->syncName);
				$this->setCronAsExecuted($this->cronName);
			}
			else
			{
				// Get list executed in previous sync items
				$this->executed = $this->getPreviousSyncExecutedList($this->syncName);
				$this->counter  = count($this->executed);
			}

			$this->counterTotal = count($arrayProductDetail);

			foreach ($arrayProductDetail as $oneItem)
			{
				if ($this->goToNextPart == true || $this->isExecutionTimeExceeded())
				{
					$this->goToNextPart = true;
					break;
				}

				if (isset($this->executed[$oneItem['colornumber'] . '_' . $oneItem['productnumber']]))
				{
					continue;
				}

				if (!isset($oneItem['lang'])
					|| strtoupper($lang) != $oneItem['lang'])
				{
					if (isset($this->issetTranslation[$oneItem['colornumber'] . '_' . $oneItem['productnumber']])
						&& $this->issetTranslation[$oneItem['colornumber'] . '_' . $oneItem['productnumber']] == true)
					{
						continue;
					}

					if (!isset($oneItem['lang']) || $oneItem['lang'] != 'EN-GB')
					{
						continue;
					}
				}
				else
				{
					$this->issetTranslation[$oneItem['colornumber'] . '_' . $oneItem['productnumber']] = true;
				}

				$this->counter++;
				$productDescriptionTable = RTable::getAdminInstance($this->tableClassName)
					->setOption('forceWebserviceUpdate', true)
					->setOption('lockingMethod', 'Sync');
				$isNew                   = true;
				$row                     = array();
				$itemData                = $this->findSyncedId(
					$this->syncName, $oneItem['colornumber'], $oneItem['productnumber'], true, $productDescriptionTable
				);

				if ($itemData)
				{
					if (!$itemData->deleted && $productDescriptionTable->load($itemData->local_id))
					{
						$isNew = false;
					}

					// If item not exists, then user delete it, so lets skip it
					elseif ($itemData->deleted)
					{
						$this->recordSyncedId(
							$this->syncName, $oneItem['colornumber'], '', $oneItem['productnumber'], false,
							0, $itemData->serialize, true
						);

						continue;
					}
					else
					{
						$this->deleteSyncedId($this->syncName, $oneItem['colornumber'], $oneItem['productnumber']);
					}
				}

				$row['sku']        = $oneItem['productnumber'];
				$row['product_id'] = $this->findSyncedId('fengel.product', $oneItem['productnumber']);

				if (!$row['product_id'])
				{
					RedshopbHelperSync::addMessage(Text::sprintf('PLG_RB_SYNC_FENGEL_PRODUCT_NOT_FOUND', $oneItem['productnumber']), 'warning');

					$this->recordSyncedId(
						$this->syncName, $oneItem['colornumber'], 0, $oneItem['productnumber'], $isNew,
						2, '', true
					);

					continue;
				}

				$row['main_attribute_value_id'] = $this->findSyncedId(
					'fengel.attribute', 'Farve_' . $oneItem['colornumber'], $oneItem['productnumber']
				);

				if (!$row['main_attribute_value_id'])
				{
					$this->recordSyncedId(
						$this->syncName, $oneItem['colornumber'], 0, $oneItem['productnumber'], $isNew,
						2, '', true
					);

					RedshopbHelperSync::addMessage(
						Text::sprintf(
							'PLG_RB_SYNC_FENGEL_PRODUCT_TYPE_NOT_FOUND', $oneItem['colornumber'] . ' (' . $oneItem['productnumber'] . ')'
						),
						'warning'
					);
					continue;
				}

				$row['description'] = array_filter(explode('%%', $oneItem['detaildescription']));

				if (count($row['description']) > 1)
				{
					$row['description'] = '<ul class="unstyled list-unstyled"><li>' . implode('</li><li>', $row['description']) . '</li></ul>';
				}
				else
				{
					$row['description'] = implode('', $row['description']);
				}

				if (!$productDescriptionTable->save($row))
				{
					throw new Exception($productDescriptionTable->getError());
				}

				$this->recordSyncedId(
					$this->syncName, $oneItem['colornumber'], $productDescriptionTable->get('id'), $oneItem['productnumber'], $isNew, 0, '',
					false, '', $productDescriptionTable, 1
				);
			}

			if ($this->translationTable && $this->goToNextPart != true && !$this->isExecutionTimeExceeded())
			{
				$itemIds = array();

				foreach ($arrayProductDetail as $oneItem)
				{
					if ($this->goToNextPart == true || $this->isExecutionTimeExceeded())
					{
						$this->goToNextPart = true;
						break;
					}

					if (isset($this->executed[$oneItem['colornumber'] . '_' . $oneItem['lang'] . '_' . $oneItem['productnumber']]))
					{
						continue;
					}

					if (!isset($oneItem['lang']) || strtoupper($lang) == $oneItem['lang'])
					{
						continue;
					}

					$this->counter++;

					$id = $this->findSyncedId($this->syncName, $oneItem['colornumber'], $oneItem['productnumber']);

					if (!$id)
					{
						continue;
					}

					$productDescriptionTable = RTable::getAdminInstance($this->tableClassName)
						->setOption('forceWebserviceUpdate', true)
						->setOption('lockingMethod', 'Sync');

					if ($productDescriptionTable->load($id))
					{
						$langCode    = explode('-', (string) $oneItem['lang']);
						$langCode[0] = strtolower($langCode[0]);
						$langCode    = implode('-', $langCode);

						$description = array_filter(explode('%%', $oneItem['detaildescription']));

						if (count($description) > 1)
						{
							$description = '<ul class="unstyled list-unstyled"><li>' . implode('</li><li>', $description) . '</li></ul>';
						}
						else
						{
							$description = implode('', $description);
						}

						$result = $this->storeTranslation(
							$this->translationTable,
							$productDescriptionTable,
							$langCode,
							array(
								'id' => $productDescriptionTable->id,
								'description' => $description
							)
						);

						if ($result !== true)
						{
							throw new Exception($result);
						}

						if ($this->findSyncedId($this->syncName, $oneItem['colornumber'] . '_' . $oneItem['lang'], $oneItem['productnumber']))
						{
							$isNew = false;
						}
						else
						{
							$isNew = true;
						}

						$this->recordSyncedId(
							$this->syncName,
							$oneItem['colornumber'] . '_' . $oneItem['lang'],
							(int) $id, $oneItem['productnumber'], $isNew
						);

						$itemIds[$id] = $id;
					}
				}

				if (count($itemIds) > 0)
				{
					$productDescriptionTable = RTable::getAdminInstance($this->tableClassName)
						->setOption('forceWebserviceUpdate', true)
						->setOption('lockingMethod', 'Sync');

					foreach ($itemIds as $itemId)
					{
						$productDescriptionTable->load($itemId);
						$result = $this->deleteNotSyncingLanguages($this->translationTable, $productDescriptionTable);

						if ($result !== true)
						{
							throw new Exception($result);
						}
					}
				}
			}

			// In last part delete not using items
			if (!$this->goToNextPart)
			{
				$db->transactionCommit();
				$db->transactionStart();

				// Remove items that were not present in the XML data
				$this->deleteRowsNotPresentInRemote($this->syncName, $this->tableName, array(1,2));

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
