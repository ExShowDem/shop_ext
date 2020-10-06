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
 * All Discounts Model
 *
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Models
 * @since       1.0
 */
class RedshopbModelAll_Discounts extends RedshopbModelList
{
	/**
	 * Name of the filter form to load
	 *
	 * @var  string
	 */
	protected $filterFormName = 'filter_all_discounts';

	/**
	 * Limitstart field used by the pagination
	 *
	 * @var  string
	 */
	protected $limitField = 'all_discounts_limit';

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
	protected $mainTablePrefix = 'pd';

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
				'pd.id', 'pd.type', 'pd.sales_type',
				'pd.starting_date', 'pd.ending_date',
				'pd.percent', 'discount_state',
				'discount_type', 'discount_sales_type',
				'discount_time_period', 'pd.state'
			);
		}

		parent::__construct($config);
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
		parent::populateState('p.id', 'desc');
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return  JDatabaseQuery
	 */
	protected function getListQuery()
	{
		$now  = Date::getInstance()->toSql();
		$user = Factory::getUser();
		$db   = $this->getDbo();

		$query = $db->getQuery(true)
			->select(
				array(
					'pd.*',
					'(CASE pd.type WHEN ' . $db->q('product') . ' THEN p.name '
					. 'WHEN ' . $db->q('product_item') . ' THEN pi.sku '
					. 'WHEN ' . $db->q('product_discount_group') . ' THEN pdg.name END) AS type_name',
					'(CASE pd.sales_type WHEN ' . $db->q('debtor') . ' THEN c.name WHEN '
					. $db->q('debtor_discount_group') . ' THEN cdg.name END) AS sales_name',
				)
			)
			->select(
				array(
					$db->qn('p.id', 'product_id'),
					$db->qn('pdg.id', 'product_discount_group_id'),
					$db->qn('c.id', 'company_id'),
					$db->qn('cdg.id', 'customer_discount_group_id'),
					$db->qn('cur.alpha3', 'currency_code')
				)
			)
			->from($db->qn('#__redshopb_product_discount', 'pd'))
			->leftJoin($db->qn('#__redshopb_product', 'p') . ' ON pd.type = ' . $db->q('product') . ' AND pd.type_id = p.id')
			->leftJoin(
				$db->qn('#__redshopb_product_discount_group', 'pdg')
				. ' ON pd.type = ' . $db->q('product_discount_group') . ' AND pdg.id = pd.type_id'
			)
			->leftJoin(
				$db->qn('#__redshopb_product_item', 'pi')
				. ' ON pd.type = ' . $db->q('product_item') . ' AND pi.id = pd.type_id'
			)
			->leftJoin(
				$db->qn('#__redshopb_company', 'c') . ' ON pd.sales_type = ' . $db->q('debtor')
				. ' AND  c.id = pd.sales_id AND ' . $db->qn('c.deleted') . ' = 0'
			)
			->leftJoin(
				$db->qn('#__redshopb_customer_discount_group', 'cdg')
				. ' ON pd.sales_type = ' . $db->q('debtor_discount_group') . ' AND cdg.id = pd.sales_id'
			)
			->leftJoin(
				$db->qn('#__redshopb_currency', 'cur') . ' ON cur.id = pd.currency_id'
			)
			->group('pd.id');

		// Filter by product (in product directly or inside a certain group)
		$product = $this->getState('filter.product');

		if (is_numeric($product))
		{
			$query->leftJoin(
				$db->qn('#__redshopb_product_discount_group_xref', 'pdgx')
					. ' ON pd.type = ' . $db->q('product_discount_group') . ' AND pd.type_id = pdgx.discount_group_id'
			)
				->where('((pd.type = ' . $db->q('product') . ' AND pd.type_id = ' . (int) $product
					. ') OR (pdgx.product_id = ' . (int) $product . '))'
				);
		}

		// Filter by product only
		$filterProductId = $this->getState('filter.product_id');

		if (is_numeric($filterProductId))
		{
			$query->where($db->qn('p.id') . ' = ' . $filterProductId);
		}

		// Filter by product discount group id
		$filterProductDiscountGroupId = $this->getState('filter.product_discount_group_id');

		if (is_numeric($filterProductDiscountGroupId))
		{
			$query->where($db->qn('pdg.id') . ' = ' . $filterProductDiscountGroupId);
		}

		// Filter by company
		$filterCompanyId = $this->getState('filter.company_id');

		if (is_numeric($filterCompanyId))
		{
			$query->where($db->qn('c.id') . ' = ' . $filterCompanyId);
		}

		// Filter by debtor discount group id
		$filterCustomerDiscountGroupId = $this->getState('filter.customer_discount_group_id');

		if (is_numeric($filterCustomerDiscountGroupId))
		{
			$query->where($db->qn('cdg.id') . ' = ' . $filterCustomerDiscountGroupId);
		}

		// Filter search
		$search = $this->getState('filter.search_all_discounts');

		if (!empty($search))
		{
			$search = $db->quote('%' . $db->escape($search, true) . '%');
			$query->where('(p.name LIKE ' . $search . ' OR c.name LIKE ' . $search . ' OR pdg.name LIKE '
				. $search . ' OR cdg.name LIKE' . $search . ' )'
			);
		}

		// Filter by state
		$state = $this->getState('list.discount_state', $this->getState('filter.state'));

		if ($state == '0' || $state == 'false')
		{
			$query->where($db->qn('pd.state') . ' = 0');
		}
		elseif ($state == '1' || $state == 'true')
		{
			$query->where($db->qn('pd.state') . ' = 1');
		}

		// Filter by type
		$discountType = $this->getState('filter.discount_type');

		if ($discountType != '')
		{
			$query->where('pd.type = ' . $db->q($discountType));
		}

		// Filter by sales type
		$discountSalesType = $this->getState('filter.discount_sales_type');

		if ($discountSalesType != '')
		{
			$query->where('pd.sales_type = ' . $db->q($discountSalesType));
		}

		// Filter by time period
		switch ($this->getState('filter.discount_time_period'))
		{
			case 'now':
				$query->where('(pd.starting_date = ' . $db->q($db->getNullDate()) . ' OR pd.starting_date <= STR_TO_DATE('
					. $db->q($now) . ', ' . $db->q('%Y-%m-%d %H:%i:%s') . '))'
				)
					->where('(pd.ending_date = ' . $db->q($db->getNullDate()) . ' OR pd.ending_date >= STR_TO_DATE('
						. $db->q($now) . ', ' . $db->q('%Y-%m-%d %H:%i:%s') . '))'
					);
				break;
			case 'past':
				$query->where('pd.ending_date < STR_TO_DATE(' . $db->q($now) . ', ' . $db->q('%Y-%m-%d %H:%i:%s') . ')')
					->where('pd.ending_date != ' . $db->q($db->getNullDate()));
				break;
			case 'future':
				$query->where('pp.starting_date > STR_TO_DATE(' . $db->q($now) . ', ' . $db->q('%Y-%m-%d %H:%i:%s') . ')')
					->where('pp.starting_date != ' . $db->q($db->getNullDate()));
				break;
			case 'no_dependence':
				$query->where('pd.starting_date = ' . $db->q($db->getNullDate()))
					->where('pd.ending_date = ' . $db->q($db->getNullDate()));
				break;
		}

		// ACL based on product ownership
		$allowedCompanies = RedshopbHelperACL::listAvailableCompanies($user->id);

		$partsp   = array();
		$partspdg = array();
		$partsc   = array();
		$partscdg = array();

		$partsp[]   = $db->qn('p.company_id') . ' IN (' . $allowedCompanies . ')';
		$partspdg[] = $db->qn('pdg.company_id') . ' IN (' . $allowedCompanies . ')';
		$partsc[]   = $db->qn('c.id') . ' IN (' . $allowedCompanies . ')';
		$partscdg[] = $db->qn('cdg.company_id') . ' IN (' . $allowedCompanies . ')';

		if (RedshopbHelperACL::getPermission('manage', 'mainwarehouse'))
		{
			$partsp[]   = $db->qn('p.company_id') . ' IS NULL';
			$partspdg[] = $db->qn('pdg.company_id') . ' IS NULL';
			$partsc[]   = $db->qn('c.id') . ' IS NULL';
			$partscdg[] = $db->qn('cdg.company_id') . ' IS NULL';
		}

		$query->where('(p.id IS NOT NULL AND (' . implode(' OR ', $partsp) . ') OR p.id IS NULL)')
			->where('(pdg.id IS NOT NULL AND (' . implode(' OR ', $partspdg) . ') OR pdg.id IS NULL)')
			->where('(c.id IS NOT NULL AND (' . implode(' OR ', $partsc) . ') OR c.id IS NULL)')
			->where('(cdg.id IS NOT NULL AND (' . implode(' OR ', $partscdg) . ') OR cdg.id IS NULL)');

		// Ordering
		$order     = $this->getState('list.ordering', 'pd.id');
		$direction = $this->getState('list.direction', 'desc');

		$query->order($db->escape($order) . ' ' . $db->escape($direction));

		// Adds related web service data when requested
		$this->getListQueryWS($query, $order, $direction);

		return $query;
	}

	/**
	 * Overridden to use "Product_discount" table by default
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
	public function getTable($name = null, $prefix = null, $options = array())
	{
		return parent::getTable('Product_discount', 'RedshopbTable', $options);
	}
}
