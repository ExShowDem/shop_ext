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
 * Get Types function.
 *
 * @package     Aesir.E-Commerce.Plugin
 * @subpackage  Fengel
 * @since       1.0
 */
class FengelGetType extends FengelFunctionBase
{
	/**
	 * @var string
	 */
	public $syncName = 'fengel.type';

	/**
	 * @var string
	 */
	public $tableClassName = 'Product_Attribute';

	/**
	 * PimFunctionBase constructor.
	 */
	public function __construct()
	{
		parent::__construct();

		$this->avoidOverrideWSProperties = array_merge(
			$this->avoidOverrideWSProperties, array(
				'enable_sku_value_display', 'conversion_sets', 'alias'
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
			$xml = $this->client->Red_GetItemVariantType();

			if (!is_object($xml))
			{
				throw new Exception(Text::_('PLG_RB_SYNC_FENGEL_FAILED_TO_FETCH_ITEMS'));
			}

			$rows = array();

			foreach ($xml->ItemVariantType as $obj)
			{
				$row       = array();
				$row['no'] = (string) $obj->No;

				if (isset($obj->Variants))
				{
					$variant = array();

					foreach ($obj->Variants as $variants)
					{
						if (isset($variants->Variant) && $variants->Variant !== null)
						{
							$i = 0;

							while (isset($variants->Variant[$i]))
							{
								$variant[$i]['default'] = $variants->Variant[$i]->__toString();

								if (isset($variants->Translations[$i]))
								{
									foreach ($variants->Translations[$i]->Translation as $translation)
									{
										$attributes            = $translation->attributes();
										$langTag               = (string) $attributes['Language'];
										$variant[$i][$langTag] = (string) $translation;
									}
								}

								$i++;
							}
						}
					}

					$row['attributes'] = $variant;
					$rows[]            = $row;
				}
			}

			$db->transactionStart();
			$strUpperLangTag = strtoupper($this->lang);
			$productTable    = RTable::getInstance('Product', 'RedshopbTable')
				->setOption('lockingMethod', 'Sync');

			// Fix flag from all old items as not synced
			$this->setSyncRowsAsExecuted($this->syncName);

			foreach ($rows as $item)
			{
				$ordering  = 0;
				$no        = $item['no'];
				$productId = $this->findSyncedId('fengel.product', $no);

				if (!$productId || !$productTable->load($productId))
				{
					RedshopbHelperSync::addMessage(Text::sprintf('PLG_RB_SYNC_FENGEL_PRODUCT_NOT_FOUND', $no), 'warning');
					continue;
				}

				foreach ($item['attributes'] as $attribute)
				{
					$table = RTable::getInstance($this->tableClassName, 'RedshopbTable')
						->setOption('forceWebserviceUpdate', true)
						->setOption('lockingMethod', 'Sync');
					$isNew = true;
					$ordering++;
					$row      = array();
					$itemData = $this->findSyncedId($this->syncName, $attribute['default'], $no, true, $table);

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
								$this->syncName, $attribute['default'],	'',	$no, false,
								0, $itemData->serialize, true
							);

							continue;
						}
						else
						{
							$this->deleteSyncedId($this->syncName, $attribute['default'], $no);
						}
					}

					$row['main_attribute'] = 0;
					$row['state']          = 1;
					$row['ordering']       = $ordering;
					$row['type_id']        = 1;
					$row['product_id']     = $productId;

					if ($attribute['default'] == 'Farve')
					{
						$row['main_attribute'] = 1;
					}
					else
					{
						$row['main_attribute'] = 0;
					}

					$row['name'] = $attribute['default'];

					if (!$table->save($row))
					{
						throw new Exception($table->getError());
					}

					if ($this->translationTable)
					{
						foreach ($attribute as $key => $translate)
						{
							if ($key == 'default' || $key == $strUpperLangTag)
							{
								continue;
							}

							$langCode    = explode('-', $key);
							$langCode[0] = strtolower($langCode[0]);
							$langCode    = implode('-', $langCode);

							$result = $this->storeTranslation(
								$this->translationTable,
								$table,
								$langCode,
								array(
									'id' => $table->id,
									'name' => $translate
								)
							);

							if ($result !== true)
							{
								throw new Exception($result);
							}
						}

						$result = $this->deleteNotSyncingLanguages($this->translationTable, $table);

						if ($result !== true)
						{
							throw new Exception($result);
						}
					}

					$this->recordSyncedId(
						$this->syncName, $attribute['default'], $table->id, $no, $isNew, 0, '',
						false, '', $table, 1
					);
				}
			}

			// If some sync items not exists in new sync -> delete it
			$this->deleteRowsNotPresentInRemote($this->syncName, $this->tableName);

			$db->transactionCommit();
		}
		catch (Exception $e)
		{
			$db->transactionRollback();
			RedshopbHelperSync::addMessage($e->getMessage(), 'error');

			return false;
		}

		RedshopbHelperSync::addMessage(Text::_('PLG_RB_SYNC_FENGEL_SYNCHRONIZE_SUCCESS'), 'success');

		return true;
	}
}
