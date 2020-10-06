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
 * SetEndCustomer function.
 *
 * @package     Aesir.E-Commerce.Plugin
 * @subpackage  Fengel
 * @since       1.0
 */
class FengelSetEndCustomer extends FengelFunctionBase
{
	/**
	 * @var string
	 */
	public $syncName = 'fengel.customer';

	/**
	 * Send data in Set webservice, then store
	 *
	 * @param   object   $table           Table class from current item
	 * @param   boolean  $updateNulls     True to update null values as well.
	 * @param   boolean  $storingChanges  If true - changes storing in DB
	 *
	 * @throws Exception
	 *
	 * @return boolean
	 */
	public function store(&$table = null, $updateNulls = false, $storingChanges = true)
	{
		$db = Factory::getDbo();

		try
		{
			$db->transactionStart();

			// We do not want to translate
			$db->translate = false;

			$query = $db->getQuery(true);

			$query->clear()
				->select('s.remote_key')
				->from($db->qn('#__redshopb_company', 'parent'))
				->where($db->qn('parent.deleted') . ' = 0')
				->leftJoin(
					$db->qn('#__redshopb_company', 'node') . ' ON node.lft BETWEEN parent.lft AND parent.rgt AND ' . $db->qn('node.deleted') . ' = 0'
				)
				->leftJoin($db->qn('#__redshopb_sync', 's') . ' ON local_id = parent.id')
				->where('reference = ' . $db->q($this->syncName))
				->where('parent.type = ' . $db->q('customer'))
				->where('node.id = ' . (int) $table->get('parent_id'))
				->group('parent.id')
				->order('parent.lft DESC');
			$db->setQuery($query);

			if (!$db->loadResult())
			{
				if ($table->get('id'))
				{
					if (!$this->deleteInWebservice($table, $table->get('id'), false))
					{
						throw new Exception;
					}
				}

				return true;
			}

			if ((int) $table->parent_id > 1)
			{
				$query->clear()
					->select(array($db->qn('c.customer_number', 'CustomerNo')))
					->from($db->qn($table->getTableName(), 'c'))
					->where('c.id = ' . (int) $table->parent_id);

				$parent = $db->setQuery($query)->loadObject();

				if (!$parent)
				{
					$parent             = new stdClass;
					$parent->CustomerNo = '';
				}
			}
			else
			{
				$parent             = new stdClass;
				$parent->CustomerNo = '';
			}

			if ($table->id && $this->findSyncedId($this->syncName, $table->customer_number))
			{
				$no     = $table->customer_number;
				$setXml = '<status>update</status>';
			}
			else
			{
				$no     = '';
				$setXml = '<status>create</status>';
			}

			$freightItemNo = '';

			if ($table->product_id)
			{
				$query->clear()
					->select('sku')
					->from($db->qn('#__redshopb_product'))
					->where('id = ' . (int) $table->product_id);

				$result = $db->setQuery($query)->loadResult();

				if ($result)
				{
					$freightItemNo = $result;
				}
			}

			$currencyCode = '';

			if ($table->currency_id)
			{
				$query->clear()
					->select('alpha3')
					->from($db->qn('#__redshopb_currency'))
					->where('id = ' . (int) $table->currency_id);

				$result = $db->setQuery($query)->loadResult();

				if ($result)
				{
					$currencyCode = $result;
				}
			}

			$countryCode = '';

			if ((int) $table->get('country_id'))
			{
				$query->clear()
					->select('alpha2')
					->from($db->qn('#__redshopb_country'))
					->where('id = ' . (int) $table->get('country_id'));

				$result = $db->setQuery($query)->loadResult();

				if ($result)
				{
					$countryCode = $result;
				}
			}

			switch ($table->show_stock_as)
			{
				case 'hide':
					$showStockAs = 'Hide';
					break;
				case 'actual_stock':
					$showStockAs = 'Actual Stock';
					break;
				case 'color_codes':
					$showStockAs = 'Color Codes';
					break;
				default:
					$showStockAs = $table->show_stock_as;
			}

			$discountGroup = '';

			$customerDiscountTable = RedshopbTable::getAdminInstance('Customer_Discount_Group_Xref')
				->setOption('lockingMethod', 'Sync');

			if ($table->id && $customerDiscountTable->load(array('customer_id' => $table->id)))
			{
				if ($customerDiscountTable->discount_group_id)
				{
					$query->clear()
						->select('code')
						->from($db->qn('#__redshopb_customer_discount_group'))
						->where('id = ' . (int) $customerDiscountTable->discount_group_id);

					$result = $db->setQuery($query)->loadResult();

					if ($result)
					{
						$discountGroup = $result;
					}
				}
			}

			$priceGroup = '';

			$customerPriceTable = RedshopbTable::getAdminInstance('Customer_Price_Group_Xref')
				->setOption('lockingMethod', 'Sync');

			if ($table->id && $customerPriceTable->load(array('customer_id' => $table->id)))
			{
				if ($customerPriceTable->customer_price_id)
				{
					$query->clear()
						->select('code')
						->from($db->qn('#__redshopb_customer_price_group'))
						->where('id = ' . (int) $customerPriceTable->customer_price_id);

					$result = $db->setQuery($query)->loadResult();

					if ($result)
					{
						$priceGroup = $result;
					}
				}
			}

			$setXml .= '<CustomerNo>' . $parent->CustomerNo . '</CustomerNo>'
				. '<No>' . $no . '</No>'
				. '<Name>' . $table->name . '</Name>'
				. '<Name2>' . $table->name2 . '</Name2>'
				. '<Address>' . $table->get('address') . '</Address>'
				. '<Address2>' . $table->get('address2') . '</Address2>'
				. '<PostCode>' . $table->get('zip') . '</PostCode>'
				. '<City>' . $table->get('city') . '</City>'
				. '<DiscGroup>' . $discountGroup . '</DiscGroup>'
				. '<EmployeeMandatory>' . ($table->employee_mandatory ? 'true' : 'false') . '</EmployeeMandatory>'
				. '<Usepurse>' . ($table->use_wallets ? 'true' : 'false') . '</Usepurse>'
				. '<OrderApproval>' . ($table->order_approval ? 'Automatic' : 'Manual') . '</OrderApproval>'
				. '<FreightamountLimit>' . number_format($table->freight_amount_limit, 2, '.', ',') . '</FreightamountLimit>'
				. '<Freightamount>' . number_format($table->freight_amount, 2, '.', ',') . '</Freightamount>'
				. '<FreightItemno>' . $freightItemNo . '</FreightItemno>'
				. '<CurrencyCode>' . $currencyCode . '</CurrencyCode>'
				. '<CountryCode>' . $countryCode . '</CountryCode>'
				. '<LanguageCode>' . strtoupper($table->site_language) . '</LanguageCode>'
				. '<SizeLanguage>' . $table->get('size_language') . '</SizeLanguage>'
				. '<CalculateFee>' . ($table->calculate_fee ? 'true' : 'false') . '</CalculateFee>'
				. '<CustomerPriceGroup>' . $priceGroup . '</CustomerPriceGroup>'
				. '<ShowStockAs>' . $showStockAs . '</ShowStockAs>'
				. '<SendMailOnOrder>' . ($table->get('send_mail_on_order') ? 'true' : 'false') . '</SendMailOnOrder>'
				. '<ShowUnitListPrice>' . ($table->get('show_retail_price') ? 'true' : 'false') . '</ShowUnitListPrice>';

			$setXml = '<?xml version="1.0" encoding="UTF-8"?><EndCustomers><EndCustomer>' . $setXml . '</EndCustomer></EndCustomers>';
			$xml    = $this->client->setEndCustomer($setXml);

			if (!$xml)
			{
				throw new Exception(Text::sprintf('PLG_RB_SYNC_FENGEL_SET_WEBSERVICE_NOT_ANSWER'));
			}
			else
			{
				if (isset($xml->Errottext))
				{
					throw new Exception(Text::sprintf('PLG_RB_SYNC_FENGEL_SET_WEBSERVICE_GET_ERROR', $xml->Errottext));
				}

				$table->setOption('disableOnBeforeRedshopb', true);

				if (isset($xml->EndCustomers->Endcustomer))
				{
					$table->customer_number = (string) $xml->EndCustomers->Endcustomer;
				}

				if ($storingChanges)
				{
					if (!$table->store($updateNulls))
					{
						throw new Exception;
					}
				}

				$table->setOption('disableOnBeforeRedshopb', false);

				if (isset($xml->EndCustomers->Endcustomer) && !$this->findSyncedId($this->syncName, (string) $xml->EndCustomers->Endcustomer))
				{
					// Insert new item in sync table
					$this->recordSyncedId($this->syncName, (string) $xml->EndCustomers->Endcustomer, $table->id);
				}
			}

			// We put translation check back on
			$db->translate = true;

			$db->transactionCommit();
		}
		catch (Exception $e)
		{
			$db->transactionRollback();

			if ($e->getMessage())
			{
				$table->setError($e->getMessage());
			}

			return false;
		}

		return true;
	}

	/**
	 * Send query to delete item in Set webservice, then delete in DB
	 *
	 * @param   object   $table     Table class from current item
	 * @param   integer  $pk        The primary key of the node to delete.
	 * @param   boolean  $children  True to delete child nodes, false to move them up a level.
	 *
	 * @throws Exception
	 *
	 * @return  boolean  True on success.
	 */
	public function delete(&$table, $pk = null, $children = true)
	{
		$db = Factory::getDbo();

		try
		{
			$db->transactionStart();

			if (!$this->deleteInWebservice($table, $pk, $children))
			{
				throw new Exception;
			}

			$db->transactionCommit();
		}
		catch (Exception $e)
		{
			$db->transactionRollback();

			if ($e->getMessage())
			{
				$table->setError($e->getMessage());
			}

			return false;
		}

		return true;
	}

	/**
	 * Delete In Webservice
	 *
	 * @param   object   $table     Table class from current item
	 * @param   integer  $pk        The primary key of the node to delete.
	 * @param   boolean  $children  True to delete child nodes, false to move them up a level.
	 *
	 * @return  boolean
	 *
	 * @throws Exception
	 */
	public function deleteInWebservice(&$table, $pk = null, $children = true)
	{
		try
		{
			$key = $table->get('_tbl_key');
			$pk  = (is_null($pk)) ? $table->get($key) : $pk;

			if ($children)
			{
				$pk = $table->getChildrenIds($pk);
			}

			if (!is_array($pk))
			{
				$pk = array($pk);
			}

			$cloneTable = clone $table;

			foreach ($pk as $id)
			{
				if (!$cloneTable->load($id, true))
				{
					continue;
				}

				if (!$this->findSyncedId($this->syncName, $cloneTable->get('customer_number')))
				{
					return true;
				}

				$setXml = '<?xml version="1.0" encoding="UTF-8"?><EndCustomers><EndCustomer><status>delete</status><No>'
					. $cloneTable->get('customer_number') . '</No></EndCustomer></EndCustomers>';
				$xml    = $this->client->setEndCustomer($setXml);

				if (!$xml)
				{
					throw new Exception(Text::sprintf('PLG_RB_SYNC_FENGEL_SET_WEBSERVICE_NOT_ANSWER'));
				}
				else
				{
					if (isset($xml->Errottext))
					{
						throw new Exception(Text::sprintf('PLG_RB_SYNC_FENGEL_SET_WEBSERVICE_GET_ERROR', $xml->Errottext));
					}

					$this->deleteSyncedId($this->syncName, $cloneTable->get('customer_number'));
				}
			}
		}
		catch (Exception $e)
		{
			if ($e->getMessage())
			{
				$table->setError($e->getMessage());
			}

			return false;
		}

		return true;
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
		return true;
	}
}
