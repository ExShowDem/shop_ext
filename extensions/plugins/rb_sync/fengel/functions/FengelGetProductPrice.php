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
 * Get Product Discount function.
 *
 * @package     Aesir.E-Commerce.Plugin
 * @subpackage  Fengel
 * @since       1.0
 */
class FengelGetProductPrice extends FengelFunctionBase
{
	/**
	 * @var string
	 */
	public $syncName = 'fengel.product_price_item';

	/**
	 * @var string
	 */
	public $syncProductPrice = 'fengel.product_price';

	/**
	 * @var string
	 */
	public $tableClassName = 'Product_Price';

	/**
	 * @var array
	 */
	public $executedProducts = array();

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
		$db     = Factory::getDbo();
		$offset = Factory::getConfig()->get('offset');

		try
		{
			$db->transactionStart();
			$oneMorePart = false;
			$fullSync    = false;
			$itemNo      = '999999';
			$source      = $params->get('source', 'wsdl');

			$params           = RedshopbApp::getConfig();
			$defaultCountryId = $params->get('default_country_id', 59);
			$productTable     = RTable::getAdminInstance('Product')
				->setOption('lockingMethod', 'Sync');

			if ($this->client->sendParameters && $webserviceData->get('fullSync', 1) == 1)
			{
				$fullSync = true;
				$query    = $db->getQuery(true)
					->select('execute_sync')
					->from($db->qn('#__redshopb_cron'))
					->where('name = ' . $db->q('GetProductPrice'));
				$db->setQuery($query);

				if (!$db->loadResult())
				{
					// Fix flag from all old items as not synced
					$query->clear()
						->update($db->qn('#__redshopb_sync'))
						->set('execute_sync = 1')
						->where('reference = ' . $db->q($this->syncProductPrice));

					$db->setQuery($query)->execute();

					$query->clear()
						->update($db->qn('#__redshopb_cron'))
						->set('execute_sync = 1')
						->where('name = ' . $db->q('GetProductPrice'));

					$db->setQuery($query)->execute();
				}

				// Find not synced prices
				$subQuery = $db->getQuery(true)
					->select('COUNT(s2.local_id)')
					->from($db->qn('#__redshopb_sync', 's2'))
					->where('s1.remote_key = s2.remote_key')
					->where('s2.reference = ' . $db->q($this->syncProductPrice))
					->where('s2.execute_sync = 0');

				$query = $db->getQuery(true)
					->select(array('s1.remote_key', 's1.local_id'))
					->from($db->qn('#__redshopb_sync', 's1'))
					->where('s1.reference = ' . $db->q('fengel.product'))
					->where('(' . $subQuery . ') = 0');
				$db->setQuery($query);
				$result = $db->loadObject();

				if ($result)
				{
					// Fix flag from all old prices as not synced
					$query = $db->getQuery(true)
						->update($db->qn('#__redshopb_sync'))
						->set('execute_sync = 1')
						->where('reference = ' . $db->q($this->syncName))
						->where('remote_parent_key = ' . $db->q($result->remote_key));

					$db->setQuery($query)->execute();

					$itemNo = $result->remote_key;
				}
			}

			$xml = $this->client->Red_GetItemPrice($itemNo, '');

			if (!is_object($xml))
			{
				throw new Exception(Text::_('PLG_RB_SYNC_FENGEL_FAILED_TO_FETCH_ITEMS'));
			}

			$query = $db->getQuery(true)
				->select(array('id', 'alpha3'))
				->from($db->qn('#__redshopb_currency'));
			$db->setQuery($query);
			$currencies = $db->loadObjectList('alpha3');

			$query->clear()
				->select(array('id', 'alpha2'))
				->from($db->qn('#__redshopb_country'));
			$db->setQuery($query);
			$countries = $db->loadObjectList('alpha2');

			if (!$fullSync)
			{
				// Check to see if we are already run Sync which is not finished (so we can have multiple parts if webservice is too big)
				if (!$this->isCronExecuted($this->cronName))
				{
					// We set them with a flag so we can delete the ones which are not present in the latest Sync xml
					$this->setSyncRowsAsExecuted($this->syncName);
					$this->setSyncRowsAsExecuted($this->syncProductPrice);

					// We set cron as executed so we can have multipart process if the process takes too long
					$this->setCronAsExecuted($this->cronName);
				}
				else
				{
					// Get list executed in previous sync items because this is multipart process
					$this->executed         = $this->getPreviousSyncExecutedList($this->syncName);
					$this->executedProducts = $this->getPreviousSyncExecutedList($this->syncProductPrice);
					$this->counter          = count($this->executed);
				}
			}

			foreach ($xml->ItemPrice as $itemPrice)
			{
				$no = (string) $itemPrice->No;

				if (!$fullSync)
				{
					$this->counterTotal += count($itemPrice->Keys->Prices->Key);

					if ($this->goToNextPart == true || $this->isExecutionTimeExceeded())
					{
						$this->goToNextPart = true;
						continue;
					}

					if (array_key_exists($no . '_', $this->executedProducts))
					{
						continue;
					}
				}

				$localId = $this->findSyncedId('fengel.product', $no);

				if (!$localId)
				{
					RedshopbHelperSync::addMessage(Text::sprintf('PLG_RB_SYNC_FENGEL_PRODUCT_NOT_FOUND', $no), 'warning');
					continue;
				}

				$query->clear()
					->select(array('s.local_id', 's.remote_key'))
					->from($db->qn('#__redshopb_sync', 's'))
					->leftJoin($db->qn('#__redshopb_sync', 'sp') . ' ON sp.remote_key = s.remote_parent_key')
					->where('s.reference = ' . $db->q('fengel.item_related'))
					->where('sp.reference = ' . $db->q('fengel.product'))
					->where('sp.local_id = ' . $db->q($localId));
				$db->setQuery($query);
				$items = $db->loadObjectList('remote_key');

				foreach ($itemPrice->Keys->Prices->Key as $obj)
				{
					$tableProductPrice = RTable::getInstance('Product_Price', 'RedshopbTable')
						->setOption('forceWebserviceUpdate', true)
						->setOption('lockingMethod', 'Sync');
					$price             = (float) str_replace(',', '', trim((string) $obj->UnitPrice));
					$retailPrice       = (float) str_replace(',', '', trim((string) $obj->UnitListPrice));

					if ((string) $obj->CurrencyCode == '')
					{
						$obj->CurrencyCode = RedshopbHelperProduct::getCurrency($params->get('default_currency', 38))->alpha3;
					}

					$skipCycle = false;

					switch ((string) $obj->SalesType)
					{
						case 'Debitor':
							$salesType = 'customer_price';
							$salesCode = $this->findSyncedId('fengel.customer', (string) $obj->SalesCode);

							if (!$salesCode)
							{
								$skipCycle = true;
							}

							break;
						case 'Alle debitorer':
							$salesType = 'all_customers';
							$salesCode = '';
							break;
						case 'Debitorprisgruppe':
							$salesType = 'customer_price_group';
							$salesCode = $this->findSyncedId('fengel.customer_price_group', (string) $obj->SalesCode);

							if (!$salesCode)
							{
								$skipCycle = true;
							}

							break;
						case 'Kampagne':
							$salesType = 'campaign';
							$salesCode = (string) $obj->SalesCode;
							break;
						default:
							$salesType = (string) $obj->SalesType;
							$salesCode = (string) $obj->SalesCode;
					}

					if ($skipCycle)
					{
						continue;
					}

					// If not have attributes - price from product
					if ((string) $obj->Value == '')
					{
						$type   = 'product';
						$typeId = $localId;
					}
					else // If exists attributes - price from attribute
					{
						$type = 'product_item';

						if (!isset($items[(string) $obj->Value]))
						{
							continue;
						}

						$typeId = $items[(string) $obj->Value]->local_id;
					}

					if ((string) $obj->AllowLineDiscount == 'true')
					{
						$allowDiscount = 1;
					}
					else
					{
						$allowDiscount = 0;
					}

					$row                   = array();
					$row['type_id']        = (int) $typeId;
					$row['type']           = (string) $type;
					$row['sales_type']     = (string) $salesType;
					$row['sales_code']     = (string) $salesCode;
					$row['currency_id']    = (int) $currencies[(string) $obj->CurrencyCode]->id;
					$row['allow_discount'] = $allowDiscount;

					if (!(string) $obj->StartingDate)
					{
						$row['starting_date'] = '0000-00-00 00:00:00';
					}
					else
					{
						$row['starting_date'] = Factory::getDate((string) $obj->StartingDate, $offset)->toSql();
					}

					if (!(string) $obj->EndingDate)
					{
						$row['ending_date'] = '0000-00-00 00:00:00';
					}
					else
					{
						$row['ending_date'] = Factory::getDate((string) $obj->StartingDate, $offset)->toSql();
					}

					$isNew    = true;
					$remoteId = base64_encode(implode('|', $row));

					if (!$fullSync)
					{
						if ($this->goToNextPart == true || $this->isExecutionTimeExceeded())
						{
							$this->goToNextPart = true;
							break;
						}

						if (array_key_exists($remoteId . '_' . $no, $this->executed))
						{
							continue;
						}

						$this->counter++;
					}

					if (isset($countries[(string) $obj->CountryCode]))
					{
						$row['country_id'] = $countries[(string) $obj->CountryCode]->id;
					}
					else
					{
						$row['country_id'] = $defaultCountryId;
					}

					$itemData = $this->findSyncedId($this->syncName, $remoteId, $no, true);

					if ($itemData)
					{
						if (!$itemData->deleted && $tableProductPrice->load($itemData->local_id))
						{
							$isNew = false;
						}

						// If item not exists, then user delete it, so lets skip it
						elseif ($itemData->deleted)
						{
							$this->recordSyncedId(
								$this->syncName, $remoteId, '', $no, false,
								0, $itemData->serialize, true
							);

							continue;
						}
						else
						{
							$this->deleteSyncedId($this->syncName, $remoteId, $no);
						}
					}

					$row['price']        = $price;
					$row['retail_price'] = $retailPrice;

					if (!$tableProductPrice->save($row))
					{
						throw new Exception($tableProductPrice->getError());
					}

					$this->recordSyncedId(
						$this->syncName, $remoteId, $tableProductPrice->id, $no, $isNew, 0, '',
						false, '', $tableProductPrice, 1
					);
				}

				if ($source == 'folder' || $fullSync)
				{
					$isNew               = true;
					$productPriceIdCheck = $this->findSyncedId($this->syncProductPrice, $no);

					if ($productPriceIdCheck)
					{
						if ($productTable->load($productPriceIdCheck))
						{
							$isNew = false;
						}
						else
						{
							$this->deleteSyncedId($this->syncProductPrice, $no);
						}
					}

					$this->recordSyncedId($this->syncProductPrice, $no, $localId, '', $isNew);
				}

				if ($fullSync)
				{
					// Delete all not synced prices from current product
					$subQuery = $db->getQuery(true)
						->select('id')
						->from($db->qn('#__redshopb_product_item'))
						->where('product_id = ' . (int) $localId);
					$query->clear()
						->delete($db->qn('#__redshopb_product_price'))
						->where(
							'((type = ' . $db->q('product') . ' AND type_id = ' . (int) $localId . ') OR (type = '
							. $db->q('product_item') . ' AND type_id IN (' . $subQuery . ')))'
						);
					$subQuery->clear()
						->select('local_id')
						->from($db->qn('#__redshopb_sync'))
						->where('reference = ' . $db->q($this->syncName))
						->where('execute_sync = 1');
					$query->where('id IN (' . $subQuery . ')');

					$db->setQuery($query)->execute();

					$query->clear()
						->delete($db->qn('#__redshopb_sync'))
						->where('reference = ' . $db->q($this->syncName))
						->where('execute_sync = 1');

					$db->setQuery($query)->execute();

					$subQuery->clear()
						->select('COUNT(s2.local_id)')
						->from($db->qn('#__redshopb_sync', 's2'))
						->where('s1.remote_key = s2.remote_key')
						->where('s2.reference = ' . $db->q($this->syncProductPrice))
						->where('s2.execute_sync = 0');

					$query->clear()
						->select('COUNT(s1.local_id)')
						->from($db->qn('#__redshopb_sync', 's1'))
						->where('s1.reference = ' . $db->q('fengel.product'))
						->where('(' . $subQuery . ') = 0');
					$db->setQuery($query);
					$test = $db->loadResult();

					// If true - all parts finished and can be switch in other webservice
					if (!$test)
					{
						$query->clear()
							->delete($db->qn('#__redshopb_sync'))
							->where('reference = ' . $db->q($this->syncProductPrice))
							->where('execute_sync = 1');

						$db->setQuery($query)->execute();

						$query->clear()
							->update($db->qn('#__redshopb_cron'))
							->set('execute_sync = 0')
							->where('name = ' . $db->q('GetProductPrice'));

						$db->setQuery($query)->execute();
					}
					else
					{
						$query->clear()
							->select('COUNT(s1.remote_key)')
							->from($db->qn('#__redshopb_sync', 's1'))
							->where('s1.reference = ' . $db->q('fengel.product'));
						$db->setQuery($query);
						$count = $db->loadResult();

						$oneMorePart = true;
					}
				}
			}

			if ($source == 'folder' && !$this->goToNextPart)
			{
				$db->transactionCommit();
				$db->transactionStart();

				// Remove items that were not present in the XML data
				$this->deleteRowsNotPresentInRemote($this->syncName, $this->tableName);
				$this->deleteRowsNotPresentInRemote($this->syncProductPrice);

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

		if ($fullSync)
		{
			if ($oneMorePart)
			{
				RedshopbHelperSync::addMessage(
					Text::sprintf('PLG_RB_SYNC_FENGEL_PRICES_FROM_PRODUCT_IS_SYNCHRONIZE', $localId, $count - $test, $count)
				);

				return array('parts' => $test, 'total' => $count);
			}
			else
			{
				RedshopbHelperSync::addMessage(Text::_('PLG_RB_SYNC_FENGEL_SYNCHRONIZE_SUCCESS'), 'success');

				return true;
			}
		}
		else
		{
			return $this->outputResult();
		}
	}
}
