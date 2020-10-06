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
 * Get Product Discount function.
 *
 * @package     Aesir.E-Commerce.Plugin
 * @subpackage  Fengel
 * @since       1.0
 */
class FengelGetProductDiscount extends FengelFunctionBase
{
	/**
	 * @var string
	 */
	public $syncName = 'fengel.product_discount';

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
			$xml = $this->client->Red_GetItemDiscount();

			if (!is_object($xml))
			{
				throw new Exception(Text::_('PLG_RB_SYNC_FENGEL_FAILED_TO_FETCH_ITEMS'));
			}

			$db->transactionStart();
			$config = RedshopbApp::getConfig();

			// Fix flag from all old items as not synced
			$query = $db->getQuery(true)
				->update($db->qn('#__redshopb_sync'))
				->set('execute_sync = 1')
				->where('reference = ' . $db->q($this->syncName));

			$db->setQuery($query)->execute();

			$query->clear()
				->select(array('id', 'alpha3'))
				->from($db->qn('#__redshopb_currency'));
			$db->setQuery($query);
			$currencies = $db->loadObjectList('alpha3');

			foreach ($xml->ItemDiscount as $obj)
			{
				$row                = array();
				$itemDiscountFields = $obj->ItemDiscountFields;

				if ((string) $itemDiscountFields->CurrencyCode == '')
				{
					$row['currency_id'] = $config->get('default_currency', 38);
				}
				elseif (isset($currencies[(string) $itemDiscountFields->CurrencyCode]))
				{
					$row['currency_id'] = $currencies[(string) $itemDiscountFields->CurrencyCode]->id;
				}
				else
				{
					continue;
				}

				$isNew        = true;
				$table        = RTable::getInstance('Product_discount', 'RedshopbTable')
					->setOption('forceWebserviceUpdate', true)
					->setOption('lockingMethod', 'Sync');
				$now          = Date::getInstance();
				$nowFormatted = $now->toSql();
				$id           = $this->findSyncedId($this->syncName, (string) $obj->ID);

				if ($id)
				{
					if ($table->load($id))
					{
						$isNew = false;
					}
					else
					{
						$this->deleteSyncedId($this->syncName, (string) $obj->ID);
						$row['created_date'] = $nowFormatted;
					}
				}
				else
				{
					$row['created_date'] = $nowFormatted;
				}

				$row['modified_date'] = $nowFormatted;

				switch ((string) $itemDiscountFields->Type)
				{
					case 'Vare':
						$row['type']    = 'product';
						$row['type_id'] = $this->findSyncedId('fengel.product', (string) $itemDiscountFields->Code);
						break;
					case 'Varerabatgruppe':
						$row['type']    = 'product_discount_group';
						$row['type_id'] = $this->findSyncedId('fengel.product_discount_group', (string) $itemDiscountFields->Code);
						break;
					default:
						continue;
				}

				// If empty 'type' - is wrong data
				if (!$row['type'])
				{
					continue;
				}

				$skipCycle = false;

				switch ((string) $itemDiscountFields->SalesType)
				{
					case 'Debitor':
						$row['sales_type'] = 'debtor';
						$row['sales_id']   = $this->findSyncedId('fengel.customer', (string) $itemDiscountFields->SalesCode);

						if (!$row['sales_id'])
						{
							$skipCycle = true;
						}

						break;
					case 'Debitorrabatgruppe':
						$row['sales_type'] = 'debtor_discount_group';
						$row['sales_id']   = $this->findSyncedId('fengel.customer_discount_group', (string) $itemDiscountFields->SalesCode);

						if (!$row['sales_id'])
						{
							$skipCycle = true;
						}

						break;
					case 'Alle debitorer':
						$row['sales_type'] = 'all_debtor';
						$row['sales_id']   = '';
						break;
					default :
						$row['sales_type'] = (string) $itemDiscountFields->SalesType;
						$row['sales_id']   = (string) $itemDiscountFields->SalesCode;
				}

				if ($skipCycle)
				{
					continue;
				}

				$row['state']         = 1;
				$row['percent']       = (float) $itemDiscountFields->LineDiscountPct;
				$row['starting_date'] = Factory::getDate((string) $itemDiscountFields->StartingDate, $offset)->toSql();
				$row['ending_date']   = Factory::getDate((string) $itemDiscountFields->EndingDate, $offset)->toSql();

				// New data
				if (!$table->save($row))
				{
					throw new Exception($table->getError());
				}

				$this->recordSyncedId($this->syncName, (string) $obj->ID, $table->id, '', $isNew);
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
					->delete($db->qn('#__redshopb_product_discount'))
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
