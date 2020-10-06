<?php
/**
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Models
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Date\Date;

/**
 * All Prices Model
 *
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Models
 * @since       1.0
 */
class RedshopbModelAll_Prices extends RedshopbModelList
{
	/**
	 * Name of the filter form to load
	 *
	 * @var  string
	 */
	protected $filterFormName = 'filter_all_prices';

	/**
	 * Limitstart field used by the pagination
	 *
	 * @var  string
	 */
	protected $limitField = 'all_prices_limit';

	/**
	 * Limitstart field used by the pagination
	 *
	 * @var  string
	 */
	protected $limitstartField = 'auto';

	/**
	 * Main table query prefix
	 *
	 * @var  array
	 */
	protected $mainTablePrefix = 'pp';

	/**
	 * Constructor
	 *
	 * @param   array  $config  Configuration array
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'sales_type', 'status', 'currency_id',
				'time_period', 'product_id', 'product_item_id',
				'pp.type_id', 'pp.id',
				'pp.starting_date', 'pp.ending_date',
				'pp.price', 'product_item_id',
				'sales_type_prices', 'time_period_prices',
				'currency_id_prices',
				'customer_price_group_id', 'company_id',
				'country_code', 'currency_code',
				'allow_discount'
			);
		}

		parent::__construct($config);
	}

	/**
	 * Method to get a table object, load it if necessary.
	 *
	 * @param   string  $name     The table name. Optional.
	 * @param   string  $prefix   The class prefix. Optional.
	 * @param   array   $options  Configuration array for model. Optional.
	 *
	 * @return  Table   A Table object
	 *
	 * @since   12.2
	 * @throws  Exception
	 */
	public function getTable($name = '', $prefix = 'RedshopbTable', $options = array())
	{
		if (empty($name))
		{
			$name = 'product_price';
		}

		return parent::getTable($name, $prefix, $options);
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * This method should only be called once per instantiation and is designed
	 * to be called on the first call to the getState() method unless the model
	 * configuration flag to ignore the request is set.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering   An optional ordering field.
	 * @param   string  $direction  An optional direction (asc|desc).
	 *
	 * @return  void
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		parent::populateState('pp.id', 'desc');
	}

	/**
	 * Get isset items prices and relating items with attribute values
	 *
	 * @param   int  $productId  Object product values
	 *
	 * @return array
	 */
	public function getIssetItemsPrices($productId)
	{
		$db  = Factory::getDbo();
		$now = Date::getInstance()->toSql();

		// Select attribute values ids from items
		$subQuery = $db->getQuery(true)
			->select('GROUP_CONCAT(pav.id ORDER BY pa.ordering asc SEPARATOR ' . $db->q('_') . ')')
			->from($db->qn('#__redshopb_product_attribute_value', 'pav'))
			->leftJoin($db->qn('#__redshopb_product_attribute', 'pa') . ' ON pav.product_attribute_id = pa.id')
			->leftJoin($db->qn('#__redshopb_product_item_attribute_value_xref', 'pivx') . ' ON pivx.product_attribute_value_id = pav.id')
			->where('pa.product_id = pi.product_id ')
			->where('pi.id = pivx.product_item_id')
			->order('pa.ordering ASC')
			->order('pav.ordering, pav.id ASC');

		$query = $db->getQuery(true)
			->select(
				array(
					'(' . $subQuery . ') AS values_ids',
					'pp.*', 'c.alpha3',
					$db->qn('p2.name', 'type_name'),
					'(CASE pp.sales_type WHEN ' . $db->q('customer_price') . ' THEN company.name WHEN '
					. $db->q('customer_price_group') . ' THEN cpg.name WHEN ' . $db->q('campaign') . ' THEN pp.sales_code END) AS sales_name',
					'pi.sku'
				)
			)
			->from($db->qn('#__redshopb_product_price', 'pp'))
			->leftJoin($db->qn('#__redshopb_product_item', 'pi') . ' ON pp.type_id = pi.id')
			->leftJoin($db->qn('#__redshopb_currency', 'c') . ' ON c.id = pp.currency_id')
			->leftJoin($db->qn('#__redshopb_product', 'p2') . ' ON pi.product_id = p2.id')
			->leftJoin(
				$db->qn('#__redshopb_company', 'company') . ' ON pp.sales_type = ' . $db->q('customer_price') .
				' AND company.id = pp.sales_code AND ' . $db->qn('company.deleted') . ' = 0'
			)
			->leftJoin(
				$db->qn('#__redshopb_customer_price_group', 'cpg')
				. ' ON pp.sales_type = ' . $db->q('customer_price_group') . ' AND cpg.id = pp.sales_code'
			)
			->where('pp.type = ' . $db->q('product_item'))
			->where('pi.product_id  = ' . (int) $productId);

		// Filter by type
		$salesType = $this->getState('filter.sales_type_prices');

		if ($salesType != '')
		{
			$query->where('pp.sales_type  = ' . $db->q($salesType));
		}

		// Filter by time period
		switch ($this->getState('filter.time_period_prices'))
		{
			case 'now':
				$query->where('(pp.starting_date = ' . $db->q($db->getNullDate()) . ' OR pp.starting_date <= STR_TO_DATE('
					. $db->q($now) . ', ' . $db->q('%Y-%m-%d %H:%i:%s') . '))'
				)
					->where('(pp.ending_date = ' . $db->q($db->getNullDate()) . ' OR pp.ending_date >= STR_TO_DATE('
						. $db->q($now) . ', ' . $db->q('%Y-%m-%d %H:%i:%s') . '))'
					);
				break;
			case 'past':
				$query->where('pp.ending_date < STR_TO_DATE(' . $db->q($now) . ', ' . $db->q('%Y-%m-%d %H:%i:%s') . ')')
					->where('pp.ending_date != ' . $db->q($db->getNullDate()));
				break;
			case 'future':
				$query->where('pp.starting_date > STR_TO_DATE(' . $db->q($now) . ', ' . $db->q('%Y-%m-%d %H:%i:%s') . ')')
					->where('pp.starting_date != ' . $db->q($db->getNullDate()));
				break;
			case 'no_dependence':
				$query->where('pp.starting_date = ' . $db->q($db->getNullDate()))
					->where('pp.ending_date = ' . $db->q($db->getNullDate()));
				break;
		}

		// Filter by currency
		$currencyId = $this->getState('filter.currency_id_prices');

		if ($currencyId != '')
		{
			$query->where('pp.currency_id = ' . (int) $currencyId);
		}

		$db->setQuery($query);
		$results    = $db->loadObjectList();
		$issetItems = array();

		if ($results)
		{
			foreach ($results as $result)
			{
				if (!isset($issetItems[$result->values_ids]))
				{
					$issetItems[$result->values_ids] = array();
				}

				$issetItems[$result->values_ids][$result->id] = $result;
			}
		}

		return $issetItems;
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return  JDatabaseQuery
	 */
	protected function getListQuery()
	{
		$isTotal = $this->getState('list.isTotal', false);

		$now  = Date::getInstance()->toSql();
		$user = Factory::getUser();
		$db   = $this->getDbo();

		$internalQuery = $db->getQuery(true)
			->select(
				array(
					$db->qn('pp_i') . '.*',
					'IFNULL(' . $db->qn('p.company_id') . ', ' . $db->qn('ppi.company_id') . ') AS ' . $db->qn('product_company_id'),
					'IFNULL(' . $db->qn('p.sku') . ', ' . $db->qn('ppi.sku') . ') AS ' . $db->qn('product_sku'),
					$db->qn('company.id', 'company_id'),
					$db->qn('cpg.id', 'customer_price_group_id'),
					'IFNULL(' . $db->qn('p.name') . ', ' . $db->qn('ppi.name') . ') AS ' . $db->qn('product_name'),
					'(CASE ' . $db->qn('pp_i.sales_type') . ' WHEN ' . $db->q('customer_price') . ' THEN ' . $db->qn('company.name') . ' WHEN '
					. $db->q('customer_price_group') . ' THEN ' . $db->qn('cpg.name')
					. ' WHEN ' . $db->q('campaign') . ' THEN ' . $db->qn('pp_i.sales_code') . ' END) AS sales_name',
					'(CASE ' . $db->qn('pp_i.type') . ' WHEN ' . $db->q('product') . ' THEN ' . $db->qn('p.sku') . ' WHEN '
					. $db->q('product_item') . ' THEN pi.sku END) AS ' . $db->qn('sku'),
					'IF (' . $db->qn('pp_i.sales_type') . ' = ' . $db->q('campaign') . ', '
					. $db->qn('pp_i.sales_code') . ', NULL) AS ' . $db->qn('campaign_code'),
					'pi.sku as product_item_sku'
				)
			)
			->from($db->qn('#__redshopb_product_price', 'pp_i'))
			->leftJoin(
				$db->qn('#__redshopb_product_item', 'pi') . ' ON ' . $db->qn('pp_i.type_id') . ' = ' . $db->qn('pi.id') .
				' AND ' . $db->qn('pp_i.type') . ' = ' . $db->q('product_item')
			)
			->leftJoin(
				$db->qn('#__redshopb_product', 'ppi') . ' ON ' . $db->qn('ppi.id') . ' = ' . $db->qn('pi.product_id') .
				' AND ' . $db->qn('pp_i.type') . ' = ' . $db->q('product_item')
			)
			->leftJoin(
				$db->qn('#__redshopb_product', 'p') . ' ON ' . $db->qn('pp_i.type_id') . ' = ' . $db->qn('p.id') .
				' AND ' . $db->qn('pp_i.type') . ' = ' . $db->q('product')
			)
			->leftJoin(
				$db->qn('#__redshopb_company', 'company') . ' ON pp_i.sales_type = ' . $db->q('customer_price') .
				' AND company.id = pp_i.sales_code AND ' . $db->qn('company.deleted') . ' = 0'
			)
			->leftJoin(
				$db->qn('#__redshopb_customer_price_group', 'cpg')
				. ' ON pp_i.sales_type = ' . $db->q('customer_price_group') . ' AND cpg.id = pp_i.sales_code'
			);

		$query = $db->getQuery(true)
			->select(
				array(
						'pp.*',
						$db->qn('c.alpha3'),
						$db->qn('c.alpha3', 'currency_code'),
						$db->qn('co.alpha2', 'country_code'),
					)
			)
			->from('(' . $internalQuery . ') AS ' . $db->qn('pp'))
			->leftJoin($db->qn('#__redshopb_currency', 'c') . ' ON c.id = pp.currency_id')
			->leftJoin($db->qn('#__redshopb_country', 'co') . ' ON co.id = pp.country_id');

		// Filter by product Item
		$productItemId = $this->getState('filter.product_item_id', $this->getState('layout_filter.product_item_id', ''));

		if (is_numeric($productItemId))
		{
			$query->where('(pp.type_id = ' . (int) $productItemId . ' AND pp.type = ' . $db->q('product_item') . ')');
		}

		// Filter by product
		$productId = $this->getState('filter.product_id', $this->getState('layout_filter.product_id'));

		if (is_numeric($productId))
		{
			$query->where('pp.product_id = ' . (int) $productId);
		}

		// Filter search
		$search = $this->getState('filter.search_all_prices');

		if (!empty($search))
		{
			$search = $db->quote('%' . $db->escape($search, true) . '%');
			$query->where(
				'(IF(pp.type = ' . $db->q('product') . ', pp.product_sku, pp.product_item_sku) LIKE ' . $search
				. ' OR pp.product_name LIKE ' . $search . ')'
			);
		}

		// Filter by customer price group
		$customerPriceGroupId = $this->getState('filter.customer_price_group_id');

		if (is_numeric($customerPriceGroupId))
		{
			$query->where('pp.customer_price_group_id = ' . (int) $customerPriceGroupId);
		}

		// Filter by company id
		$companyId = $this->getState('filter.company_id');

		if (is_numeric($companyId))
		{
			$query->where('pp.company_id = ' . (int) $companyId);
		}

		// Filter by type
		$salesType = $this->getState('filter.sales_type');

		if ($salesType != '')
		{
			$query->where('pp.sales_type  = ' . $db->q($salesType));
		}

		// Filter by campaign code
		$campaignCode = $this->getState('filter.campaign_code');

		if (!empty($campaignCode))
		{
			$query->where('pp.campaign_code = ' . $db->q($campaignCode));
		}

		// Filter by time period
		switch ($this->getState('filter.time_period'))
		{
			case 'now':
				$query->where('(pp.starting_date = ' . $db->q($db->getNullDate()) . ' OR pp.starting_date <= STR_TO_DATE('
					. $db->q($now) . ', ' . $db->q('%Y-%m-%d %H:%i:%s') . '))'
				)
					->where('(pp.ending_date = ' . $db->q($db->getNullDate()) . ' OR pp.ending_date >= STR_TO_DATE('
						. $db->q($now) . ', ' . $db->q('%Y-%m-%d %H:%i:%s') . '))'
					);
				break;
			case 'past':
				$query->where('pp.ending_date < STR_TO_DATE(' . $db->q($now) . ', ' . $db->q('%Y-%m-%d %H:%i:%s') . ')')
					->where('pp.ending_date != ' . $db->q($db->getNullDate()));
				break;
			case 'future':
				$query->where('pp.starting_date > STR_TO_DATE(' . $db->q($now) . ', ' . $db->q('%Y-%m-%d %H:%i:%s') . ')')
					->where('pp.starting_date != ' . $db->q($db->getNullDate()));
				break;
			case 'no_dependence':
				$query->where('pp.starting_date = ' . $db->q($db->getNullDate()))
					->where('pp.ending_date = ' . $db->q($db->getNullDate()));
				break;
		}

		// Filter by currency
		$currencyId = $this->getState('filter.currency_id');

		if ($currencyId != '')
		{
			$query->where('pp.currency_id = ' . (int) $currencyId);
		}

		// Filter by currency code
		$currencyCode = $this->getState('filter.currency_code');

		if (!empty($currencyCode))
		{
			$query->where('c.alpha3 = ' . $db->q($currencyCode));
		}

		// Filter by country code
		$countryCode = $this->getState('filter.country_code');

		if (!empty($countryCode))
		{
			$query->where('co.alpha2 = ' . $db->q($countryCode));
		}

		// Filter by discount allowed.
		$allowDiscount = $this->getState('filter.allow_discount');

		if ($allowDiscount == '0' || $allowDiscount == 'false')
		{
			$query->where($db->qn('pp.allow_discount') . ' = 0');
		}
		elseif ($allowDiscount == '1' || $allowDiscount == 'true')
		{
			$query->where($db->qn('pp.allow_discount') . ' = 1');
		}

		// ACL based on product ownership
		$allowedCompanies = RedshopbHelperACL::listAvailableCompanies($user->id);
		$parts1           = array();
		$parts1[]         = $db->qn('pp.product_company_id') . ' IN (' . $allowedCompanies . ')';

		if (RedshopbHelperACL::getPermission('manage', 'mainwarehouse'))
		{
			$parts1[] = $db->qn('pp.product_company_id') . ' IS NULL';
		}

		$query->where('pp.product_id IS NOT NULL AND (' . implode(' OR ', $parts1) . ')');

		// Filter search (web service)
		$search = $this->getState('filter.search');

		if (!empty($search))
		{
			$search = $db->quote('%' . $db->escape($search, true) . '%');

			$searchFields = array(
				$db->qn('pp.campaign_code') . ' LIKE ' . $search,
			);

			$query->where('(' . implode(' OR ', $searchFields) . ')');
		}

		$query->group('pp.id');

		if (!$isTotal)
		{
			// Ordering
			$orderList     = $this->getState('list.ordering', 'pp.id');
			$directionList = $this->getState('list.direction', 'desc');

			$order     = !empty($orderList) ? $orderList : 'pp.id';
			$direction = !empty($directionList) ? $directionList : 'DESC';
			$query->order($db->escape($order) . ' ' . $db->escape($direction));
		}

		// Adds related web service data when requested
		$this->getListQueryWS($query);

		return $query;
	}

	/**
	 * Check current product item is available from current product or not
	 *
	 * @param   int  $productId      Id product
	 * @param   int  $productItemId  Id product item
	 *
	 * @return mixed
	 */
	public function isAvailableItemId($productId, $productItemId)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->select('id')
			->from($db->qn('#__redshopb_product_item'))
			->where('product_id = ' . (int) $productId)
			->where('id = ' . (int) $productItemId);
		$db->setQuery($query);

		return $db->loadResult();
	}
}
