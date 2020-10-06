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
jimport('joomla.user.helper');

/**
 * GetSalesPerson function.
 *
 * @package     Aesir.E-Commerce.Plugin
 * @subpackage  Fengel
 * @since       1.0
 */
class FengelGetSalesPerson extends FengelFunctionBase
{
	/**
	 * @var string
	 */
	public $syncName = 'fengel.sales_person';

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
		$db           = Factory::getDbo();
		$counter      = 0;
		$isNew        = true;

		try
		{
			$db->transactionStart();
			$currentDateTime = '';

			$xml = $this->client->getSalesPerson('', $currentDateTime);

			if (!is_object($xml))
			{
				throw new Exception(Text::_('PLG_RB_SYNC_FENGEL_FAILED_TO_FETCH_ITEMS'));
			}

			$query = $db->getQuery(true)
				->select('execute_sync')
				->from($db->qn('#__redshopb_cron'))
				->where('name = ' . $db->q('GetSalesPerson'));
			$db->setQuery($query);

			if (!$db->loadResult())
			{
				// Fix flag from all old items as not synced
				$query = $db->getQuery(true)
					->update($db->qn('#__redshopb_sync'))
					->set('execute_sync = 1')
					->where('reference = ' . $db->q($this->syncName));

				$db->setQuery($query)->execute();

				$query->clear()
					->update($db->qn('#__redshopb_cron'))
					->set('execute_sync = 1')
					->where('name = ' . $db->q('GetSalesPerson'));

				$db->setQuery($query)->execute();

				$countExecuted = 0;
			}
			else
			{
				// Get list executed in previous sync items
				$query = $db->getQuery(true)
					->select(array('s.*'))
					->from($db->qn('#__redshopb_sync', 's'))
					->where('s.reference = ' . $db->q($this->syncName))
					->where('s.execute_sync IN (0,2)');
				$db->setQuery($query);
				$executed      = $db->loadObjectList('remote_key');
				$countExecuted = count($executed);
			}

			foreach ($xml->SalesPerson as $obj)
			{
				$isNew = true;
				$counter++;

				if ($countExecuted > 0 && isset($executed[(string) $obj->Id]))
				{
					continue;
				}

				if (!(string) $obj->WebUserID)
				{
					RedshopbHelperSync::addMessage(
						Text::sprintf('PLG_RB_SYNC_FENGEL_ID_USER_IS_EMPTY', (string) $obj->Id), 'warning'
					);

					$this->recordSyncedId($this->syncName, (string) $obj->Id, (string) $obj->Id, '', true, 2, '');

					continue;
				}

				$row = array();
				$now = Date::getInstance();

				$allData = $this->findSyncedId($this->syncName, (string) $obj->Id, '', true);

				$objectFromMD5 = clone $obj;
				unset($objectFromMD5->CurrentDateTime);
				$md5Row = md5('5' . $objectFromMD5->asXML());

				$table = RTable::getInstance('User', 'RedshopbTable')
					->setOption('forceWebserviceUpdate', true)
					->setOption('lockingMethod', 'Sync')
					->setOption('forceCustomersUpdate', true)
					->setOption('noPasswordUpdate', true);

				$id = $this->findSyncedId('fengel.user', (string) $obj->WebUserID);

				if (!empty($allData))
				{
					$isNew = false;
					$id    = $allData->local_id;

					if (!$table->load($id))
					{
						RedshopbHelperSync::addMessage(
							Text::sprintf('PLG_RB_SYNC_FENGEL_ID_USER_NOT_EXISTS', (string) $obj->WebUserID), 'warning'
						);
						continue;
					}
				}
				elseif ($id)
				{
					if (!$table->load($id))
					{
						RedshopbHelperSync::addMessage(
							Text::sprintf('PLG_RB_SYNC_FENGEL_ID_USER_NOT_EXISTS', (string) $obj->WebUserID), 'warning'
						);
						continue;
					}
				}
				else
				{
					RedshopbHelperSync::addMessage(
						Text::sprintf('PLG_RB_SYNC_FENGEL_ID_USER_NOT_EXISTS', (string) $obj->WebUserID), 'warning'
					);
					continue;
				}

				$row['companyIds'] = array();

				if (isset($obj->Customers->Customer) && count($obj->Customers->Customer) > 0)
				{
					foreach ($obj->Customers->Customer as $customer)
					{
						$companyId = $this->findSyncedId('fengel.customer', (string) $customer->CustomerNo);

						if ($companyId)
						{
							$row['companyIds'][] = $companyId;
						}
						else
						{
							RedshopbHelperSync::addMessage(
								Text::sprintf('PLG_RB_SYNC_FENGEL_CUSTOMER_NOT_FOUND', (string) $customer->CustomerNo), 'warning'
							);
						}
					}
				}

				if (isset($obj->EndCustomers->EndCustomer) && count($obj->EndCustomers->EndCustomer) > 0)
				{
					foreach ($obj->EndCustomers->EndCustomer as $customer)
					{
						$companyId = $this->findSyncedId('fengel.customer', (string) $customer->EndCustomerNo);

						if ($companyId)
						{
							$row['companyIds'][] = $companyId;
						}
						else
						{
							RedshopbHelperSync::addMessage(
								Text::sprintf('PLG_RB_SYNC_FENGEL_ENDCUSTOMER_NOT_FOUND', (string) $customer->EndCustomerNo), 'warning'
							);
						}
					}
				}

				if (!$table->save($row))
				{
					throw new Exception($table->getError());
				}

				$this->recordSyncedId($this->syncName, (string) $obj->Id, $table->id, '', $isNew, 0, $md5Row);

				if (microtime(1) - $start >= 25)
				{
					$goToNextPart = true;
					break;
				}
			}

			// In last part if some sync users not exists in new sync -> delete the customer relations from that user
			if (!$goToNextPart)
			{
				if ($currentDateTime == '')
				{
					$query->clear()
						->select('ru.id')
						->from($db->qn('#__redshopb_user', 'ru'))
						->leftJoin($db->qn('#__redshopb_sync', 's') . ' ON ru.id = s.local_id')
						->where('s.reference = ' . $db->q($this->syncName))
						->where('s.execute_sync = 1');

					$results = $db->setQuery($query)->loadColumn();

					if ($results)
					{
						$table = RTable::getInstance('User', 'RedshopbTable')
							->setOption('forceWebserviceUpdate', true)
							->setOption('lockingMethod', 'Sync')
							->setOption('forceCustomersUpdate', true);

						foreach ($results as $user)
						{
							if ($table->load($user))
							{
								if (!$table->save(Array('companyIds' => array())))
								{
									throw new Exception($table->getError());
								}
							}
						}

						$query->clear()
							->delete($db->qn('#__redshopb_sync'))
							->where('reference = ' . $db->q($this->syncName))
							->where('execute_sync IN (1,2)');
						$db->setQuery($query);

						if (!$db->execute())
						{
							return false;
						}
					}
				}
				else
				{
					$query->clear()
						->update($db->qn('#__redshopb_sync'))
						->set('execute_sync = 0')
						->where('reference = ' . $db->q($this->syncName))
						->where('execute_sync = 1');

					$db->setQuery($query)->execute();
				}

				$attributes      = current($xml->attributes());
				$currentDateTime = (string) $attributes['DateTime'];

				$params   = array('CurrentDateTime' => $currentDateTime);
				$registry = new Registry;
				$registry->loadArray($params);
				$stringParams = (string) $registry;

				$query->clear()
					->update($db->qn('#__redshopb_cron'))
					->set('execute_sync = 0')
					->set('params = ' . $db->q($stringParams))
					->where('name = ' . $db->q('GetSalesPerson'));

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
			$countInXml = count($xml->User);
			RedshopbHelperSync::addMessage(Text::sprintf('PLG_RB_SYNC_FENGEL_GOTO_NEXT_PART', $counter, $countInXml));

			return array('parts' => $countInXml - $counter, 'total' => $countInXml);
		}
		else
		{
			RedshopbHelperSync::addMessage(Text::_('PLG_RB_SYNC_FENGEL_SYNCHRONIZE_SUCCESS'), 'success');

			return true;
		}
	}
}
