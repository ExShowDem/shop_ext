<?php
/**
 * @package     Aesir.E-Commerce.Plugin
 * @subpackage  Fengel
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('JPATH_BASE') or die;

use Joomla\Registry\Registry;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Date\Date;

require_once __DIR__ . '/base.php';

/**
 * GetCustomer function.
 *
 * @package     Aesir.E-Commerce.Plugin
 * @subpackage  Fengel
 * @since       1.0
 */
class FengelGetCustomer extends FengelFunctionBase
{
	/**
	 * @var string
	 */
	public $syncName = 'fengel.customer';

	/**
	 * Read and store the data.
	 *
	 * @param   RTable    $webserviceData   Webservice object
	 * @param   Registry  $params           Parameters of the plugin
	 *
	 * @return  mixed                       True on success. Array of next item. False otherwise.
	 *
	 * @throws  Exception
	 */
	public function read(&$webserviceData, $params)
	{
		$start        = microtime(1);
		$goToNextPart = false;
		$db           = Factory::getDbo();
		$counter      = 0;

		try
		{
			$xml     = null;
			$session = Factory::getSession();
			$tmpXML  = $session->get('sync_GetCustomer', '', 'com_redshopb');

			if ($tmpXML == '')
			{
				throw new Exception(sprintf(Text::_('PLG_RB_SYNC_FENGEL_NO_TEMP'), 'GetCustomer'));
			}

			// Tries to load cache file, or else loads live GetCustomer function
			if (file_exists($tmpXML))
			{
				$xml = simplexml_load_file($tmpXML);
			}
			else
			{
				$xml = $this->client->getCustomer('');
				$xml->asXML($tmpXML);
			}

			if (!is_object($xml))
			{
				throw new Exception(Text::_('PLG_RB_SYNC_FENGEL_FAILED_TO_FETCH_ITEMS'));
			}

			$config           = RedshopbEntityConfig::getInstance();
			$defaultCountryId = $config->getInt('default_country_id', 59);
			$db->translate    = false;
			$db->transactionStart();

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

			$data = array();

			foreach ($xml->Customer as $obj)
			{
				$row   = array();
				$field = $this->map('No', (string) $obj->No);

				if ($field)
				{
					$row[$field->dbname] = $field;
				}

				$field = $this->map('name', (string) $obj->Name);

				if ($field)
				{
					$row[$field->dbname] = $field;
				}

				$field = $this->map('name2', (string) $obj->Name2);

				if ($field)
				{
					$row[$field->dbname] = $field;
				}

				$field = $this->map('address', (string) $obj->Address);

				if ($field)
				{
					$row[$field->dbname] = $field;
				}

				$field = $this->map('address2', (string) $obj->Address2);

				if ($field)
				{
					$row[$field->dbname] = $field;
				}

				$field = $this->map('city', (string) $obj->City);

				if ($field)
				{
					$row[$field->dbname] = $field;
				}

				$field = $this->map('zip', (string) $obj->Postcode);

				if ($field)
				{
					$row[$field->dbname] = $field;
				}

				$field = $this->map('address_name', trim(((string) $obj->Name) . ' ' . ((string) $obj->Name2)));

				if ($field)
				{
					$row['address_name'] = $field;
				}

				$field = $this->map('CustomerPriceGroup', (string) $obj->CustomerPriceGroup);

				if ($field)
				{
					$row[$field->dbname] = $field;
				}

				$field = $this->map('CustomerDiscountGroup', (string) $obj->CustomerDiscountGroup);

				if ($field)
				{
					$row[$field->dbname] = $field;
				}

				$field = $this->map('OrderApproval', (string) $obj->OrderApproval);

				if ($field)
				{
					$row[$field->dbname] = $field;
				}

				$field = $this->map('ShowStockAs', (string) $obj->ShowStockAs);

				if ($field)
				{
					$row[$field->dbname] = $field;
				}

				$field = $this->map('CurrencyCode', (string) $obj->CurrencyCode);

				if ($field)
				{
					$row[$field->dbname] = $field;
				}

				$field = $this->map('SiteLanguage', (string) $obj->SiteLanguage);

				if ($field)
				{
					$row[$field->dbname] = $field;
				}

				$field = $this->map('size_language', (string) $obj->SizeLanguage);

				if ($field)
				{
					$row[$field->dbname] = $field;
				}

				$field = $this->map('CalculateFee', (string) $obj->CalculateFee);

				if ($field)
				{
					$row[$field->dbname] = $field;
				}

				$field = $this->map('phone', (string) $obj->PhoneNumber);

				if ($field)
				{
					$row[$field->dbname] = $field;
				}

				$field = $this->map('SendMailOnOrder', (string) $obj->SendMailOnOrder);

				if ($field)
				{
					$row[$field->dbname] = $field;
				}

				if (isset($obj->SendMailOnOrder))
				{
					$field = $this->map('ShowUnitListPrice', (string) $obj->ShowUnitListPrice);

					if ($field)
					{
						$row[$field->dbname] = $field;
					}
				}

				$row['country_id']         = new stdClass;
				$row['country_id']->dbname = 'country_id';
				$countryCode               = (string) $obj->Countrycode;

				if (isset($countries[$countryCode]))
				{
					$row['country_id']->value = $countries[$countryCode]->id;
				}
				else
				{
					$row['country_id']->value = $defaultCountryId;
				}

				$data[] = $row;
			}

			$rows = $this->fieldstoArray($data);

			// Use F. Engel as parent
			$engelId = $this->getEngelId();

			$query->clear()
				->select('execute_sync')
				->from($db->qn('#__redshopb_cron'))
				->where('name = ' . $db->q('GetCustomer'));
			$db->setQuery($query);

			if (!$db->loadResult())
			{
				// Fix flag from all old items as not synced
				$subQuery = $db->getQuery(true)
					->select('id')
					->from($db->qn('#__redshopb_company'))
					->where($db->qn('deleted') . ' = 0')
					->where('type = ' . $db->q('customer'));
				$query    = $db->getQuery(true)
					->update($db->qn('#__redshopb_sync'))
					->set('execute_sync = 1')
					->where('reference = ' . $db->q($this->syncName))
					->where('local_id IN (' . $subQuery . ')');

				$db->setQuery($query)->execute();

				$query->clear()
					->update($db->qn('#__redshopb_cron'))
					->set('execute_sync = 1')
					->where('name = ' . $db->q('GetCustomer'));

				$db->setQuery($query)->execute();

				$countExecuted = 0;
			}
			else
			{
				// Get list executed in previous sync items
				$query = $db->getQuery(true)
					->select(array('s.*'))
					->from($db->qn('#__redshopb_sync', 's'))
					->innerJoin($db->qn('#__redshopb_company', 'c') . ' ON s.local_id = c.id AND ' . $db->qn('c.deleted') . ' = 0')
					->where('s.reference = ' . $db->q($this->syncName))
					->where('s.execute_sync = 0')
					->where('c.type = ' . $db->q('customer'));
				$db->setQuery($query);
				$executed      = $db->loadObjectList('remote_key');
				$countExecuted = count($executed);
			}

			foreach ($rows as $row)
			{
				$counter++;

				if ($countExecuted > 0 && isset($executed[$row['No']]))
				{
					continue;
				}

				/** @var RedshopbTableCompany $table */
				$table = RTable::getInstance('Company', 'RedshopbTable')
					->setOption('forceWebserviceUpdate', true)
					->setOption('lockingMethod', 'Sync');
				$isNew = true;

				$now          = Date::getInstance();
				$nowFormatted = $now->toSql();
				$allData      = $this->findSyncedId($this->syncName, $row['No'], '', true);
				$md5Row       = md5('11' . serialize($row));

				if (!empty($allData))
				{
					if ($allData->serialize == $md5Row && $webserviceData->get('fullSync', 1) != 1)
					{
						$this->recordSyncedId(
							$this->syncName, $row['No'], $allData->local_id, '', false, 0, $md5Row
						);

						if (microtime(1) - $start >= 20)
						{
							$goToNextPart = true;
							break;
						}

						continue;
					}

					$id = $allData->local_id;

					if ($table->load($id))
					{
						$isNew = false;
					}
					else
					{
						$this->deleteSyncedId($this->syncName, $row['No']);
						$row['created_date'] = $nowFormatted;
					}
				}
				else
				{
					$row['created_date'] = $nowFormatted;
				}

				$row['modified_date']         = $nowFormatted;
				$row['customer_number']       = $row['No'];
				$row['type']                  = 'customer';
				$row['order_approval']        = $row['OrderApproval'] == 'Automatic' ? 1 : 0;
				$row['calculate_fee']         = ($row['CalculateFee'] == 'true') ? 1 : 0;
				$row['send_mail_on_order']    = ($row['SendMailOnOrder'] == 'true') ? 1 : 0;
				$row['price_group_ids']       = array($this->findSyncedId('fengel.customer_price_group', $row['CustomerPriceGroup']));
				$row['customer_discount_ids'] = array($this->findSyncedId('fengel.customer_discount_group', $row['CustomerDiscountGroup']));
				$row['show_retail_price']     = (isset($row['ShowUnitListPrice']) && $row['ShowUnitListPrice'] == 'true') ? 1 : 0;

				// Empty price group if null
				if ($row['price_group_ids'] === null)
				{
					$row['price_group_ids'] = array();
				}

				// Empty discount group if null
				if ($row['customer_discount_ids'] === null)
				{
					$row['customer_discount_ids'] = array();
				}

				// Default show price is "Global configuration"
				if ($row['show_price'] === null)
				{
					$row['show_price'] = -1;
				}

				$row['site_language'] = $this->getLanguageTag((string) $row['SiteLanguage']);

				if (isset($currencies[(string) $row['CurrencyCode']]))
				{
					$row['currency_id'] = (int) $currencies[(string) $row['CurrencyCode']]->id;
				}
				else
				{
					$row['currency_id'] = null;
				}

				switch ($row['ShowStockAs'])
				{
					case 'Hide':
						$row['show_stock_as'] = 'hide';
						break;
					case 'Actual Stock':
						$row['show_stock_as'] = 'actual_stock';
						break;
					case 'Color Codes':
						$row['show_stock_as'] = 'color_codes';
						break;
					default:
						$row['show_stock_as'] = $row['ShowStockAs'];
				}

				// Set parent to root, for erp 'customers'.
				$row['parent_id'] = $engelId;

				if ($table->parent_id != $row['parent_id'] || !$table->id)
				{
					// Reprocesses record, forcing it to alter its ACL
					$table->setLocation($row['parent_id'], 'last-child');
					$table->setOption('rebuildACL', true);
				}

				// New data
				if (!$table->save($row))
				{
					throw new Exception($table->getError());
				}

				$this->recordSyncedId($this->syncName, $row['No'], $table->id, '', $isNew, 0, $md5Row);

				if (microtime(1) - $start >= 20)
				{
					$goToNextPart = true;
					break;
				}
			}

			// In last part if some sync companies not exists in new sync -> delete it
			if (!$goToNextPart)
			{
				$query->clear()
					->select('local_id')
					->from($db->qn('#__redshopb_sync'))
					->where('reference = ' . $db->q($this->syncName))
					->where('execute_sync = 1');

				$results = $db->setQuery($query)->loadColumn();

				if ($results)
				{
					$table = RTable::getInstance('Company', 'RedshopbTable')
						->setOption('forceWebserviceUpdate', true)
						->setOption('lockingMethod', 'Sync');

					foreach ($results as $result)
					{
						$table->load($result);

						if (!$table->delete($result))
						{
							throw new Exception($table->getError());
						}
					}

					$query->clear()
						->delete($db->qn('#__redshopb_sync'))
						->where('reference = ' . $db->q($this->syncName))
						->where('execute_sync = 1');

					$db->setQuery($query)->execute();
				}

				$query->clear()
					->update($db->qn('#__redshopb_cron'))
					->set('execute_sync = 0')
					->where('name = ' . $db->q('GetCustomer'));

				$db->setQuery($query)->execute();
			}

			$db->transactionCommit();
			$db->translate = true;
		}
		catch (Exception $e)
		{
			$db->transactionRollback();
			RedshopbHelperSync::addMessage($e->getMessage(), 'error');

			return false;
		}

		if ($goToNextPart)
		{
			$countInXml = count($xml->Customer);
			RedshopbHelperSync::addMessage(Text::sprintf('PLG_RB_SYNC_FENGEL_GOTO_NEXT_PART', $counter, $countInXml));

			return array('parts' => $countInXml - $counter, 'total' => $countInXml);
		}
		else
		{
			RedshopbHelperSync::addMessage(Text::_('PLG_RB_SYNC_FENGEL_SYNCHRONIZE_SUCCESS', 'success'));

			return true;
		}
	}

	/**
	 * Get engel company id, create if not existing
	 *
	 * @toDO: allow configuration for default company
	 *
	 * @throws Exception
	 * @return integer
	 */
	protected function getEngelId()
	{
		$engel = RTable::getInstance('Company', 'RedshopbTable')
			->setOption('forceWebserviceUpdate', true)
			->setOption('lockingMethod', 'Sync');

		if (!$engel->load(array('type' => 'main')))
		{
			$addressId = $this->getAddress(
				array(
					'address'    => 'K/S Norgesvej 12',
					'address2'    => '',
					'city'       => 'Haderslev',
					'zip'        => '6100',
					'country_id' => 59
				)
			);

			$now          = Date::getInstance();
			$nowFormatted = $now->toSql();

			$engel->setLocation(1, 'last-child');

			if (!$engel->save(
				array(
					'name'          => 'F. Engel',
					'parent_id'     => 1,
					'address_id'    => $addressId,
					'created_date'  => $nowFormatted,
					'modified_date' => $nowFormatted,
					'state'         => 1,
					'address'    => 'K/S Norgesvej 12',
					'city'       => 'Haderslev',
					'zip'        => '6100',
					'country_id' => 59,
					'customer_number' => 'F. Engel',
					'type' => 'main'
				)
			))
			{
				throw new Exception($engel->getError());
			}
		}

		return $engel->id;
	}
}
