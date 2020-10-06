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
 * GetCollection function.
 *
 * @package     Aesir.E-Commerce.Plugin
 * @subpackage  Fengel
 * @since       1.0
 */
class FengelGetCollection extends FengelFunctionBase
{
	/**
	 * @var string
	 */
	public $syncName = 'fengel.collection';

	/**
	 * @var array
	 */
	protected $allAccessories = array();

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
		$db               = Factory::getDbo();
		$counter          = 0;
		$start            = microtime(1);
		$goToNextPart     = false;
		$countCollections = 0;

		try
		{
			$db->transactionStart();

			$xml = $this->client->getCollectionLink('', '', '');

			if (!is_object($xml))
			{
				throw new Exception(Text::_('PLG_RB_SYNC_FENGEL_FAILED_TO_FETCH_ITEMS'));
			}

			foreach ($xml->CollectionLink as $obj)
			{
				$collectionNo = (string) $obj->CollectionNo;

				if (!(string) $obj->DepartmentNo || !$collectionNo)
				{
					continue;
				}

				$customerNo = (string) $obj->EndCustomer;

				if (!$customerNo)
				{
					$customerNo = (string) $obj->CustomerNo;
				}

				if (!$customerNo)
				{
					continue;
				}

				$departmentId = $this->findSyncedId('fengel.department', (string) $obj->DepartmentID);

				if (!$departmentId)
				{
					continue;
				}

				if (!isset($departments[$collectionNo]))
				{
					$departments[$collectionNo] = array();
				}

				if (!isset($departments[$collectionNo][$customerNo]))
				{
					$countCollections++;
				}

				$departments[$collectionNo][$customerNo][] = $departmentId;
			}

			$query = $db->getQuery(true);

			$xml = $this->client->getCollection('', '', '', '', '');

			if (!is_object($xml))
			{
				throw new Exception(Text::_('PLG_RB_SYNC_FENGEL_FAILED_TO_FETCH_ITEMS'));
			}

			$query->clear()
				->select('execute_sync')
				->from($db->qn('#__redshopb_cron'))
				->where('name = ' . $db->q('GetCollection'));
			$db->setQuery($query);

			if (!$db->loadResult())
			{
				// Fix flag for all old collections as not synced
				$query->clear()
					->update($db->qn('#__redshopb_sync'))
					->set('execute_sync = 1')
					->where('reference IN (' . $db->q($this->syncName) . ', ' . $db->q('fengel.accessory') . ')');

				$db->setQuery($query)->execute();

				$query->clear()
					->update($db->qn('#__redshopb_cron'))
					->set('execute_sync = 1')
					->where('name = ' . $db->q('GetCollection'));

				$db->setQuery($query)->execute();

				$countExecuted = 0;
			}
			else
			{
				// Get list executed in previous sync items
				$query = $db->getQuery(true)
					->select(array('*', 'CONCAT_WS(' . $db->q('_') . ', remote_key, remote_parent_key) AS concat_id'))
					->from($db->qn('#__redshopb_sync'))
					->where('reference = ' . $db->q($this->syncName))
					->where('execute_sync = 0');
				$db->setQuery($query);
				$executed      = $db->loadObjectList('concat_id');
				$countExecuted = count($executed);
			}

			$query->clear()
				->select(array('id', 'alpha3'))
				->from($db->qn('#__redshopb_currency'));
			$db->setQuery($query);
			$currencies = $db->loadObjectList('alpha3');

			$table = RTable::getInstance('Collection', 'RedshopbTable')
				->setOption('forceWebserviceUpdate', true)
				->setOption('lockingMethod', 'Sync');

			foreach ($xml->Collection as $obj)
			{
				$table->id = null;
				$table->reset();

				$collectionNo = (string) $obj->CollectionNo;

				$this->allAccessories = array();

				if (!isset($departments[$collectionNo]))
				{
					continue;
				}

				switch ((string) $obj->CollectionCurrency)
				{
					case 'POINT':
						$collectionCurrency = 'PTS';
						break;
					default:
						$collectionCurrency = (string) $obj->CollectionCurrency;
				}

				if (!isset($currencies[$collectionCurrency]))
				{
					RedshopbHelperSync::addMessage(Text::_('PLG_RB_SYNC_FENGEL_UNKNOWN_CURRENCY_IN_SYNC') . ': ' . $collectionCurrency, 'warning');
				}

				foreach ($departments[$collectionNo] as $customerNo => $departmentIds)
				{
					$counter++;

					if ($countExecuted > 0 && isset($executed[$collectionNo . '_' . $customerNo]))
					{
						continue;
					}

					$row         = array();
					$row['name'] = (string) $obj->Name;

					$isNew = true;

					$now          = Date::getInstance();
					$nowFormatted = $now->toSql();

					$row['company_id'] = $this->findSyncedId('fengel.customer', $customerNo);

					if (!$row['company_id'])
					{
						continue;
					}

					$id = $this->findSyncedId($this->syncName, $collectionNo, $customerNo);

					if ($id)
					{
						if ($table->load($id, true))
						{
							$isNew = false;
						}
						else
						{
							$this->deleteSyncedId($this->syncName, $collectionNo, $customerNo);
						}
					}

					if ($isNew)
					{
						$row['created_date'] = $nowFormatted;
					}

					$row['modified_date'] = $nowFormatted;
					$row['currency_id']   = $currencies[$collectionCurrency]->id;

					if ((string) $obj->Active == 'true')
					{
						$row['state'] = 1;
					}
					else
					{
						$row['state'] = 0;
					}

					$productIds          = array();
					$productAttributeIds = array();

					if (count($departmentIds) > 0)
					{
						$row['department_ids'] = $departmentIds;
						$table->setOption('departments.store', true);
					}

					foreach ($obj->CollectionItems->CollectionItem as $collectionItem)
					{
						if ((string) $collectionItem->ItemNo == '')
						{
							continue;
						}

						$productId = $this->findSyncedId('fengel.product', (string) $collectionItem->ItemNo);

						if ($productId)
						{
							$productIds[] = array('id' => $productId);
						}
						else
						{
							continue;
						}

						if ((string) $collectionItem->Active == 'true')
						{
							$itemState = 1;
						}
						else
						{
							$itemState = 0;
						}

						$points = str_replace('.', '', trim((string) $collectionItem->PointPrice));
						$points = (float) str_replace(',', '.', $points);

						if ((string) $collectionItem->ColorCode)
						{
							$attributeValueId = $this->findSyncedId(
								'fengel.attribute', 'Farve_' . (string) $collectionItem->ColorCode, (string) $collectionItem->ItemNo
							);

							if ($attributeValueId)
							{
								$productAttributeIds[(int) $attributeValueId] = array('points' => $points, 'state' => $itemState);
								$this->setAccessories($collectionItem, $attributeValueId);
							}
						}

						// ColorCode is empty then need store all colors relates
						else
						{
							$query->clear()
								->select('pav.id')
								->from($db->qn('#__redshopb_product_attribute_value', 'pav'))
								->leftJoin($db->qn('#__redshopb_product_attribute', 'pa') . ' ON pa.id = pav.product_attribute_id')
								->where('pa.product_id = ' . (int) $productId)
								->where('pa.main_attribute = 1')
								->group('pav.id');

							$results = $db->setQuery($query)->loadColumn();

							if ($results)
							{
								foreach ($results as $result)
								{
									$productAttributeIds[(int) $result] = array('points' => $points, 'state' => $itemState);
									$this->setAccessories($collectionItem, $result);
								}
							}
						}
					}

					if (count($productAttributeIds) > 0)
					{
						$query->clear()
							->select(array('pi.id', $db->qn('pav.id', 'attribute_value_id')))
							->from($db->qn('#__redshopb_product_item', 'pi'))
							->leftJoin($db->qn('#__redshopb_product_item_attribute_value_xref', 'piavx') . ' ON piavx.product_item_id = pi.id')
							->leftJoin($db->qn('#__redshopb_product_attribute_value', 'pav') . ' ON pav.id = piavx.product_attribute_value_id')
							->where('pav.id IN (' . implode(',', array_keys($productAttributeIds)) . ')')
							->order('pav.ordering');

						$results = $db->setQuery($query)->loadObjectList();

						if ($results)
						{
							$table->setOption('product_items.store', true)
								->setOption('product_items.update_only_state', false)
								->setOption('product_items.update_only_price', false)
								->setOption('ownProductItems.update', false);
							$row['product_item_ids'] = array();

							foreach ($results as $result)
							{
								$row['product_item_ids'][] = array(
									'id' => $result->id,
									'points' => $productAttributeIds[$result->attribute_value_id]['points'],
									'state' => $productAttributeIds[$result->attribute_value_id]['state']
								);
							}
						}
					}

					if (count($productIds) > 0)
					{
						$row['product_ids'] = $productIds;
						$table->setOption('products.store', true)
							->setOption('ownProducts.update', false);
					}

					if (!$table->save($row))
					{
						throw new Exception($table->getError());
					}

					if (count($this->allAccessories))
					{
						foreach ($this->allAccessories as $attributeValueId => $accessories)
						{
							foreach ($accessories as $accessory)
							{
								$accessoryTable = RTable::getInstance('Accessory', 'RedshopbTable')
									->setOption('lockingMethod', 'Sync');
								$isNewAccessory = true;
								$key            = base64_encode($accessory['description']);
								$parentKey      = (int) $attributeValueId . '_' . (int) $table->id . '_' . (int) $accessory['product_id'];
								$rowAccessory   = array();
								$idAccessory    = $this->findSyncedId('fengel.accessory', $key, $parentKey);

								if ($idAccessory)
								{
									if ($accessoryTable->load($idAccessory))
									{
										$isNewAccessory = false;
									}
									else
									{
										$this->deleteSyncedId('fengel.accessory', $key, $parentKey);
									}
								}

								if ($isNewAccessory)
								{
									$rowAccessory['attribute_value_id']   = $attributeValueId;
									$rowAccessory['collection_id']        = $table->id;
									$rowAccessory['accessory_product_id'] = $accessory['product_id'];
									$rowAccessory['description']          = $accessory['description'];
								}

								$rowAccessory['hide_on_collection'] = $accessory['hide_on_collection'];
								$rowAccessory['price']              = $accessory['price'];
								$rowAccessory['selection']          = $accessory['selection'];
								$rowAccessory['state']              = 1;

								if (!$accessoryTable->save($rowAccessory))
								{
									throw new Exception($table->getError());
								}

								$this->recordSyncedId('fengel.accessory', $key, $accessoryTable->id, $parentKey, $isNewAccessory);
							}
						}
					}

					$this->recordSyncedId($this->syncName, $collectionNo, $table->id, $customerNo, $isNew);

					if (microtime(1) - $start >= 25)
					{
						$goToNextPart = true;
						break;
					}
				}

				if ($goToNextPart)
				{
					break;
				}
			}

			// In last part if some sync users not exists in new sync -> delete it
			if (!$goToNextPart)
			{
				$subQuery = $db->getQuery(true)
					->select('local_id')
					->from($db->qn('#__redshopb_sync'))
					->where('reference = ' . $db->q($this->syncName))
					->where('execute_sync = 1');
				$query->clear()
					->delete($db->qn('#__redshopb_collection'))
					->where('id IN (' . $subQuery . ')');

				$db->setQuery($query)->execute();

				$query->clear()
					->delete($db->qn('#__redshopb_sync'))
					->where('reference = ' . $db->q($this->syncName))
					->where('execute_sync = 1');

				$db->setQuery($query)->execute();

				// Delete accessories not exists in last sync
				$subQuery = $db->getQuery(true)
					->select('local_id')
					->from($db->qn('#__redshopb_sync'))
					->where('reference = ' . $db->q('fengel.accessory'))
					->where('execute_sync = 1');
				$query->clear()
					->delete($db->qn('#__redshopb_product_item_accessory'))
					->where('id IN (' . $subQuery . ')');

				$db->setQuery($query)->execute();

				$query->clear()
					->delete($db->qn('#__redshopb_sync'))
					->where('reference = ' . $db->q('fengel.accessory'))
					->where('execute_sync = 1');

				$db->setQuery($query)->execute();

				$query->clear()
					->update($db->qn('#__redshopb_cron'))
					->set('execute_sync = 0')
					->where('name = ' . $db->q('GetCollection'));

				$db->setQuery($query)->execute();
			}

			$db->transactionCommit();
		}
		catch (Exception $e)
		{
			$db->transactionRollback();
			RedshopbHelperSync::addMessage($e->getMessage(), 'error');

			return false;
		}

		if ($goToNextPart)
		{
			RedshopbHelperSync::addMessage(Text::sprintf('PLG_RB_SYNC_FENGEL_GOTO_NEXT_PART', $counter, $countCollections));

			return array('parts' => $countCollections - $counter, 'total' => $countCollections);
		}
		else
		{
			RedshopbHelperSync::addMessage(Text::_('PLG_RB_SYNC_FENGEL_SYNCHRONIZE_SUCCESS'), 'success');

			return true;
		}
	}

	/**
	 * Set Accessories
	 *
	 * @param   object  $collectionItem    Collection Item object
	 * @param   int     $attributeValueId  Attribute Value Id
	 *
	 * @return  void
	 */
	public function setAccessories($collectionItem, $attributeValueId)
	{
		if (isset($collectionItem->CollectionItemServices->CollectionItemService))
		{
			foreach ($collectionItem->CollectionItemServices->CollectionItemService as $collectionItemService)
			{
				$accessory               = array();
				$accessory['product_id'] = $this->findSyncedId('fengel.product', (string) $collectionItemService->ServiceItemNo);

				if (!$accessory['product_id'])
				{
					continue;
				}

				$accessory['hide_on_collection'] = ((string) $collectionItemService->HideOnCollection == 'true') ? 1 : 0;
				$accessory['price']              = str_replace('.', '', trim((string) $collectionItemService->ServicePrice));
				$accessory['price']              = (float) str_replace(',', '.', $accessory['price']);

				switch ((string) $collectionItemService->Selection)
				{
					case 'Påkrævet':
						$accessory['selection'] = 'require';
						break;
					case 'Foreslået':
						$accessory['selection'] = 'proposed';
						break;
					case 'Valgfri':
						$accessory['selection'] = 'optional';
						break;
					default:
						$accessory['selection'] = (string) $collectionItemService->Selection;
				}

				$accessory['description'] = (string) $collectionItemService->Description;

				if (!isset($this->allAccessories[$attributeValueId]))
				{
					$this->allAccessories[$attributeValueId] = array();
				}

				$this->allAccessories[$attributeValueId][] = $accessory;
			}
		}
	}
}
