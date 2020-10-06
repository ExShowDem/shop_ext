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
 * Get Customer Discount Group function.
 *
 * @package     Aesir.E-Commerce.Plugin
 * @subpackage  Fengel
 * @since       1.0
 */
class FengelGetCustomerPriceGroup extends FengelFunctionBase
{
	/**
	 * @var string
	 */
	public $syncName = 'fengel.customer_price_group';

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
		$db   = Factory::getDbo();
		$user = Factory::getUser();

		try
		{
			$xml = $this->client->Red_GetCustomerPriceGroup();

			if (!is_object($xml))
			{
				throw new Exception(Text::_('PLG_RB_SYNC_FENGEL_FAILED_TO_FETCH_ITEMS'));
			}

			$db->transactionStart();

			// Fix flag from all old items as not synced
			$query = $db->getQuery(true)
				->update($db->qn('#__redshopb_sync'))
				->set('execute_sync = 1')
				->where('reference = ' . $db->q($this->syncName));

			$db->setQuery($query)->execute();

			foreach ($xml->CustomerPriceGroup as $obj)
			{
				$table        = RTable::getInstance('Customer_price_group', 'RedshopbTable')
					->setOption('forceWebserviceUpdate', true)
					->setOption('lockingMethod', 'Sync')
					->setOption('customers.store', false);
				$now          = Date::getInstance();
				$nowFormatted = $now->toSql();
				$isNew        = true;
				$id           = $this->findSyncedId($this->syncName, (string) $obj->Code);

				if ($id)
				{
					if ($table->load($id))
					{
						$isNew = false;
					}
					else
					{
						$this->deleteSyncedId($this->syncName, (string) $obj->Code);
					}
				}
				else
				{
					$row['created_date'] = $nowFormatted;
					$id                  = 0;
				}

				$row['code']          = (string) $obj->Code;
				$row['state']         = 1;
				$row['created_date']  = $nowFormatted;
				$row['modified_date'] = $nowFormatted;

				if ($isNew)
				{
					$row['created_by'] = $user->id;
				}

				$row['modified_by'] = $user->id;

				if (trim((string) $obj->Name))
				{
					$row['name'] = trim((string) $obj->Name);
				}
				else
				{
					$row['name'] = (string) $obj->Code;
				}

				// New data
				if (!$table->save($row))
				{
					throw new Exception($table->getError());
				}

				$this->recordSyncedId($this->syncName, (string) $obj->Code, $table->id, '', $isNew);
			}

			$query->clear()
				->select('local_id')
				->from($db->qn('#__redshopb_sync'))
				->where('reference = ' . $db->q($this->syncName))
				->where('execute_sync = 1');

			$results = $db->setQuery($query)->loadColumn();

			if ($results)
			{
				$query->clear()
					->delete($db->qn('#__redshopb_customer_price_group'))
					->where('id IN (' . implode(',', $results) . ')');

				$db->setQuery($query)->execute();

				$query->clear()
					->delete($db->qn('#__redshopb_sync'))
					->where('reference = ' . $db->q($this->syncName))
					->where('execute_sync = 1');

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
