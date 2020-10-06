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
 * GetShiptoAddress function.
 *
 * @package     Aesir.E-Commerce.Plugin
 * @subpackage  Fengel
 * @since       1.0
 */
class FengelGetShiptoAddress extends FengelFunctionBase
{
	/**
	 * @var string
	 */
	public $syncName = 'fengel.ship_to_address';

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
		$start        = microtime(1);
		$goToNextPart = false;
		$counter      = 0;
		$db           = Factory::getDbo();

		try
		{
			$xml = $this->client->getShiptoAddress('', '', '', '', '');

			if (!is_object($xml))
			{
				throw new Exception(Text::_('PLG_RB_SYNC_FENGEL_FAILED_TO_FETCH_ITEMS'));
			}

			$db->transactionStart();

			$config           = RedshopbEntityConfig::getInstance();
			$defaultCountryId = $config->getInt('default_country_id', 59);

			$query = $db->getQuery(true)
				->select(array('id', 'alpha2'))
				->from($db->qn('#__redshopb_country'));
			$db->setQuery($query);
			$countries = $db->loadObjectList('alpha2');

			$query = $db->getQuery(true)
				->select('execute_sync')
				->from($db->qn('#__redshopb_cron'))
				->where('name = ' . $db->q('GetShiptoAddress'));
			$db->setQuery($query);

			if (!$db->loadResult())
			{
				// Fix flag from all old items as not synced
				$query->clear()
					->update($db->qn('#__redshopb_sync'))
					->set('execute_sync = 1')
					->where('reference = ' . $db->q($this->syncName));

				$db->setQuery($query)->execute();

				$query->clear()
					->update($db->qn('#__redshopb_cron'))
					->set('execute_sync = 1')
					->where('name = ' . $db->q('GetShiptoAddress'));

				$db->setQuery($query)->execute();

				$countExecuted = 0;
			}
			else
			{
				// Get list executed in previous sync items
				$query = $db->getQuery(true)
					->select('*')
					->from($db->qn('#__redshopb_sync'))
					->where('reference = ' . $db->q($this->syncName))
					->where('execute_sync = 0');
				$db->setQuery($query);
				$executed      = $db->loadObjectList('remote_key');
				$countExecuted = count($executed);
			}

			$table = RTable::getInstance('Address', 'RedshopbTable')
				->setOption('forceWebserviceUpdate', true)
				->setOption('lockingMethod', 'Sync');

			foreach ($xml->ShiptoAddress as $obj)
			{
				$counter++;
				$remoteKey = (string) $obj->Code . '_' . (string) $obj->Type . '_' . (string) $obj->CustomerNo;

				switch ((string) $obj->Type)
				{
					case 'Department':
						$remoteKey .= '_' . (string) $obj->DepartmentID;
						break;

					case 'Employee':
						$remoteKey .= '_' . (string) $obj->DepartmentID . '_' . (string) $obj->EmployeeID;
						break;
				}

				if ((string) $obj->Address == '' || ($countExecuted > 0 && isset($executed[$remoteKey])))
				{
					continue;
				}

				$row   = array();
				$isNew = true;

				switch ((string) $obj->Type)
				{
					case 'Customer':
						$row['customer_type'] = 'company';
						$row['customer_id']   = $this->findSyncedId('fengel.customer', (string) $obj->CustomerNo);
						$customerId           = (string) $obj->CustomerNo;
						break;
					case 'End Customer':
						$row['customer_type'] = 'company';
						$row['customer_id']   = $this->findSyncedId('fengel.customer', (string) $obj->EndCustomer);
						$customerId           = (string) $obj->EndCustomer;
						break;
					case 'Department':
						$row['customer_type'] = 'department';
						$row['customer_id']   = $this->findSyncedId('fengel.department',  (string) $obj->DepartmentID);
						$customerId           = (string) $obj->DepartmentID;
						break;
					case 'Employee':
						$row['customer_type'] = 'employee';
						$row['customer_id']   = $this->findSyncedId('fengel.user', (string) $obj->EmployeeID);
						$customerId           = (string) $obj->EmployeeID;
						break;
					default:
						$row['customer_type'] = '';
						$row['customer_id']   = 0;
						$customerId           = '';
				}

				if ($row['customer_type'] == '' || (int) $row['customer_id'] == 0)
				{
					RedshopbHelperSync::addMessage(
						Text::sprintf('PLG_RB_SYNC_FENGEL_CANT_FIND_RELATE_CUSTOMER', (string) $obj->Type, $customerId), 'warning'
					);
					continue;
				}

				$row['name']     = (string) $obj->Name;
				$row['name2']    = (string) $obj->Name2;
				$row['address']  = (string) $obj->Address;
				$row['address2'] = (string) $obj->Address2;
				$row['zip']      = (string) $obj->PostCode;
				$row['city']     = (string) $obj->City;
				$row['code']     = (string) $obj->Code;

				if (isset($countries[(string) $obj->CountryCode]))
				{
					$row['country_id'] = $countries[(string) $obj->CountryCode]->id;
				}
				else
				{
					$row['country_id'] = $defaultCountryId;
				}

				if ((string) $obj->DefaultAdress == 'true')
				{
					$row['type'] = 3;
				}
				else
				{
					$row['type'] = 1;
				}

				$id = $this->findSyncedId($this->syncName, $remoteKey);

				if ($id)
				{
					if ($table->load($id, true))
					{
						$isNew = false;
					}
					else
					{
						$this->deleteSyncedId($this->syncName, $remoteKey);
					}
				}

				if ((string) $obj->CountryCode == '' || $row['address'] == '' || $row['zip'] == '' || $row['city'] == '')
				{
					if ($id)
					{
						if (!$table->delete($id, true))
						{
							throw new Exception($table->getError());
						}

						$this->deleteSyncedId($this->syncName, $remoteKey);
					}

					continue;
				}

				if ($isNew)
				{
					$row['id'] = 0;
				}

				if (!$table->save($row))
				{
					throw new Exception($table->getError());
				}

				$this->recordSyncedId($this->syncName, $remoteKey, $table->id, '', $isNew);

				if (microtime(1) - $start >= 20)
				{
					$goToNextPart = true;
					break;
				}
			}

			// In last part if some items not exists in new sync -> delete it
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
					foreach ($results as $result)
					{
						if ($table->load($result, true))
						{
							if ($table->type == 2)
							{
								continue;
							}

							if (!$table->delete($result, true))
							{
								throw new Exception($table->getError());
							}
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
					->where('name = ' . $db->q('GetShiptoAddress'));

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

		RedshopbHelperSync::addMessage(Text::_('PLG_RB_SYNC_FENGEL_SYNCHRONIZE_SUCCESS'), 'success');

		return true;
	}
}
