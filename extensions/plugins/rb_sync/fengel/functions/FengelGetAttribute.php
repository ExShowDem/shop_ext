<?php
/**
 * @package     Aesir.E-Commerce.Plugin
 * @subpackage  Fengel
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('JPATH_BASE') or die;

require_once __DIR__ . '/base.php';

use Joomla\CMS\Factory;
use Joomla\Registry\Registry;
use Joomla\CMS\Language\Text;

/**
 * Get Attributes function.
 *
 * @package     Aesir.E-Commerce.Plugin
 * @subpackage  Fengel
 * @since       1.0
 */
class FengelGetAttribute extends FengelFunctionBase
{
	/**
	 * @var string
	 */
	public $syncName = 'fengel.attribute';

	/**
	 * @var string
	 */
	public $tableClassName = 'Product_Attribute_Value';

	/**
	 * PimFunctionBase constructor.
	 */
	public function __construct()
	{
		parent::__construct();

		$this->avoidOverrideWSProperties = array_merge(
			$this->avoidOverrideWSProperties, array(
				'image', 'selected'
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
			$xml = $this->client->Red_GetItemVariantData();

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

			foreach ($xml as $obj)
			{
				$allData = $this->findSyncedId('fengel.product', (string) $obj->No, '', true);

				if ($allData)
				{
					$serialize = serialize(
						array(
							'Colour' => (string) $obj->ColourTable,
							'Size' => (string) $obj->SizeTable,
							'Style' => (string) $obj->StyleTable
						)
					);

					$this->recordSyncedId($allData->reference, (string) $obj->No, $allData->local_id, '', false, 0, $serialize);
				}

				foreach ($obj->Variantcodes as $variantCodes)
				{
					foreach ($variantCodes as $variants)
					{
						foreach ($variants as $key => $variant)
						{
							$this->counterTotal++;

							if ($this->goToNextPart == true || $this->isExecutionTimeExceeded())
							{
								$this->goToNextPart = true;
								continue;
							}

							if (array_key_exists($key . '_' . (string) $variant->Code . '_' . (string) $obj->No, $this->executed))
							{
								continue;
							}

							$this->counter++;

							if (mb_strtolower((string) $variant->Description) == 'block' || mb_strtolower((string) $variant->Description) == 'blok')
							{
								continue;
							}

							$table    = RTable::getInstance($this->tableClassName, 'RedshopbTable')
								->setOption('forceWebserviceUpdate', true)
								->setOption('lockingMethod', 'Sync');
							$isNew    = true;
							$row      = array();
							$itemData = $this->findSyncedId($this->syncName, $key . '_' . (string) $variant->Code, (string) $obj->No, true, $table);

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
										$this->syncName, $key . '_' . (string) $variant->Code,
										'',	(string) $obj->No, false, 0, $itemData->serialize, true
									);

									continue;
								}
								else
								{
									$this->deleteSyncedId($this->syncName, $key . '_' . (string) $variant->Code, (string) $obj->No);
								}
							}

							$row['product_attribute_id'] = $this->findSyncedId('fengel.type', $key, (string) $obj->No);

							if (!$row['product_attribute_id'])
							{
								RedshopbHelperSync::addMessage(Text::sprintf('PLG_RB_SYNC_FENGEL_PRODUCT_TYPE_NOT_FOUND', $key), 'warning');
								continue;
							}

							$row['state']    = 1;
							$row['ordering'] = (integer) $variant->SortOrder;
							$row['value']    = (string) $variant->Description;
							$row['sku']      = (string) $variant->Code;

							if (!$table->save($row))
							{
								throw new Exception($table->getError());
							}

							$this->recordSyncedId(
								$this->syncName, $key . '_' . (string) $variant->Code, $table->id,
								(string) $obj->No, $isNew, 0, '', false, '', $table, 1
							);
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
				$this->deleteRowsNotPresentInRemote($this->syncName, $this->tableName);

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
