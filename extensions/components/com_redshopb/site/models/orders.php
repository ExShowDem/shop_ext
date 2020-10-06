<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Date\Date;
/**
 * Orders Model
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 * @since       1.0
 */
class RedshopbModelOrders extends RedshopbModelList
{
	/**
	 * Name of the filter form to load
	 *
	 * @var  string
	 */
	protected $filterFormName = 'filter_orders';

	/**
	 * Limitstart field used by the pagination
	 *
	 * @var  string
	 */
	protected $limitField = 'order_limit';

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
	protected $mainTablePrefix = 'orders';

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
				'id', 'orders.id',
				'status', 'orders.status',
				'customer_type', 'orders.customer_type',
				'customer_name', 'orders.customer_name',
				'employee_name', 'orders.employee_name',
				'department_name', 'orders.department_name',
				'company_name', 'orders.company_name',
				'vendor_name', 'orders.vendor_name',
				'log_type', 'ol.log_type',
				'created_date', 'orders.created_date',
				'modified_date', 'orders.modified_date',
				'created_by', 'orders.created_by',
				'modified_by', 'orders.modified_by',
				'order_status', 'orders.author',
				'company_id',
				'department_id',
				'user_id',
				'delivery_address_id',
				'currency_code',
				'previous_id',
				'payment_method', 'payment_title',
				'payment_status'
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
		parent::populateState('created_date', 'DESC');
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return  JDatabaseQuery
	 */
	protected function getListQuery()
	{
		$db                = $this->getDbo();
		$vendorOfCompanies = RedshopbEntityConfig::getInstance()->get('vendor_of_companies', 'parent');

		// If customer is Employee
		$employeeQuery = $db->getQuery(true)
			->select(
				array(
					'o.*',
					$db->qn('u3.name', 'employee_name'),
					$db->qn('d.name', 'department_name'),
					$db->qn('c.id', 'company_id'),
					$db->qn('d.id', 'department_id'),
					$db->qn('u.id', 'user_id'),
					$db->qn('c.name', 'company_name'),
					$db->qn('c2.name', 'vendor_name'),
					$db->qn('u1.name', 'author'),
					$db->qn('u2.name', 'editor'),
					$db->qn('u4.id', 'author_employee_id'),
					'o.total_price AS total'
				)
			)
			->from($db->qn('#__redshopb_order', 'o'))
			->leftJoin($db->qn('#__redshopb_order_item', 'oi') . ' ON ' . $db->qn('o.id') . ' = ' . $db->qn('oi.order_id'))
			->leftJoin($db->qn('#__redshopb_user', 'u') . ' ON ' . $db->qn('u.id') . ' = ' . $db->qn('o.customer_id'))
			->leftJoin($db->qn('#__users', 'u3') . ' ON ' . $db->qn('u3.id') . ' = ' . $db->qn('u.joomla_user_id'))
			->leftJoin($db->qn('#__redshopb_company', 'c') . ' ON ' . $db->qn('c.id') . ' = ' . $db->qn('o.customer_company'))
			->leftJoin(
				$db->qn('#__redshopb_company', 'c2') . ' ON '
				. ($vendorOfCompanies == 'parent'
					? ($db->qn('c2.id') . ' = ' . $db->qn('c.parent_id'))
					: ('c2.type = ' . $db->q('main') . ' AND c2.deleted = 0 AND c2.state = 1'))
			)
			->leftJoin($db->qn('#__users', 'u1') . ' ON ' . $db->qn('u1.id') . ' = ' . $db->qn('o.created_by'))
			->leftJoin($db->qn('#__users', 'u2') . ' ON ' . $db->qn('u2.id') . ' = ' . $db->qn('o.modified_by'))
			->leftJoin($db->qn('#__redshopb_department', 'd') . ' ON ' . $db->qn('o.customer_department') . ' = ' . $db->qn('d.id'))
			->leftJoin($db->qn('#__redshopb_user', 'u4') . ' ON ' . $db->qn('u4.joomla_user_id') . ' = ' . $db->qn('o.created_by'))
			->where($db->qn('o.customer_type') . ' = ' . $db->q('employee'))
			->group(
				array(
					$db->qn('o.id'),
					$db->qn('u3.name'),
					$db->qn('d.name'),
					$db->qn('c.id'),
					$db->qn('c2.name'),
					$db->qn('u1.name'),
					$db->qn('u2.name')
				)
			);

		// If customer is Department
		$departmentQuery = $db->getQuery(true)
			->select(
				array(
					'o.*',
					'NULL AS employee_name',
					$db->qn('d.name', 'department_name'),
					$db->qn('c.id', 'company_id'),
					$db->qn('d.id', 'department_id'),
					'NULL AS ' . $db->qn('user_id'),
					$db->qn('c.name', 'company_name'),
					$db->qn('c2.name', 'vendor_name'),
					$db->qn('u1.name', 'author'),
					$db->qn('u2.name', 'editor'),
					$db->qn('u4.id', 'author_employee_id'),
					'o.total_price AS total'
				)
			)
			->from($db->qn('#__redshopb_order', 'o'))
			->innerJoin($db->qn('#__redshopb_order_item', 'oi') . ' ON ' . $db->qn('o.id') . ' = ' . $db->qn('oi.order_id'))
			->leftJoin($db->qn('#__redshopb_department', 'd') . ' ON ' . $db->qn('d.id') . ' = ' . $db->qn('o.customer_id'))
			->leftJoin($db->qn('#__redshopb_company', 'c') . ' ON ' . $db->qn('c.id') . ' = ' . $db->qn('o.customer_company'))
			->leftJoin(
				$db->qn('#__redshopb_company', 'c2') . ' ON '
				. ($vendorOfCompanies == 'parent'
					? ($db->qn('c2.id') . ' = ' . $db->qn('c.parent_id'))
					: ('c2.type = ' . $db->q('main') . ' AND c2.deleted = 0 AND c2.state = 1'))
			)
			->leftJoin($db->qn('#__users', 'u1') . ' ON ' . $db->qn('u1.id') . ' = ' . $db->qn('o.created_by'))
			->leftJoin($db->qn('#__users', 'u2') . ' ON ' . $db->qn('u2.id') . ' = ' . $db->qn('o.modified_by'))
			->leftJoin($db->qn('#__redshopb_user', 'u4') . ' ON ' . $db->qn('u4.joomla_user_id') . ' = ' . $db->qn('o.created_by'))
			->where($db->qn('o.customer_type') . ' = ' . $db->q('department'))
			->group(
				array(
					$db->qn('o.id'),
					$db->qn('d.name'),
					$db->qn('c.id'),
					$db->qn('c.name'),
					$db->qn('c2.name'),
					$db->qn('u1.name'),
					$db->qn('u2.name')
				)
			);

		// If customer is Company
		$companyQuery = $db->getQuery(true)
			->select(
				array(
					'o.*',
					'NULL AS employee_name',
					'NULL AS department_name',
					$db->qn('c.id', 'company_id'),
					'NULL AS ' . $db->qn('department_id'),
					'NULL AS ' . $db->qn('user_id'),
					$db->qn('c.name', 'company_name'),
					$db->qn('c2.name', 'vendor_name'),
					$db->qn('u1.name', 'author'),
					$db->qn('u2.name', 'editor'),
					$db->qn('u4.id', 'author_employee_id'),
					'o.total_price AS total'
				)
			)
			->from($db->qn('#__redshopb_order', 'o'))
			->innerJoin($db->qn('#__redshopb_order_item', 'oi') . ' ON ' . $db->qn('o.id') . ' = ' . $db->qn('oi.order_id'))
			->leftJoin($db->qn('#__redshopb_company', 'c') . ' ON ' . $db->qn('c.id') . ' = ' . $db->qn('o.customer_id'))
			->leftJoin(
				$db->qn('#__redshopb_company', 'c2') . ' ON '
				. ($vendorOfCompanies == 'parent'
					? ($db->qn('c2.id') . ' = ' . $db->qn('c.parent_id'))
					: ('c2.type = ' . $db->q('main') . ' AND c2.deleted = 0 AND c2.state = 1'))
			)
			->leftJoin($db->qn('#__users', 'u1') . ' ON ' . $db->qn('u1.id') . ' = ' . $db->qn('o.created_by'))
			->leftJoin($db->qn('#__users', 'u2') . ' ON ' . $db->qn('u2.id') . ' = ' . $db->qn('o.modified_by'))
			->leftJoin($db->qn('#__redshopb_user', 'u4') . ' ON ' . $db->qn('u4.joomla_user_id') . ' = ' . $db->qn('o.created_by'))
			->where($db->qn('o.customer_type') . ' = ' . $db->q('company'))
			->group(
				array(
					$db->qn('o.id'),
					$db->qn('c.id'),
					$db->qn('c.name'),
					$db->qn('c2.name'),
					$db->qn('u1.name'),
					$db->qn('u2.name')
				)
			);

		if (RedshopbEntityConfig::getInstance()->get('count_cart_items', 'quantity') == 'quantity')
		{
			$employeeQuery->select('SUM(oi.quantity) AS products');
			$departmentQuery->select('SUM(oi.quantity) AS products');
			$companyQuery->select('SUM(oi.quantity) AS products');
		}
		else
		{
			$employeeQuery->select('COUNT(*) AS products');
			$departmentQuery->select('COUNT(*) AS products');
			$companyQuery->select('COUNT(*) AS products');
		}

		$this->setQueryRestrictions($employeeQuery, $departmentQuery, $companyQuery);

		$statuses   = RedshopbEntityOrder::getAllowedStatusCodes();
		$statusCase = 'NULL';

		if ($statuses)
		{
			$statusCase = 'CASE orders.status';

			foreach ($statuses as $statusId => $statusCode)
			{
				$statusCase .= ' WHEN ' . $statusId . ' THEN ' . $db->q($statusCode);
			}

			$statusCase .= ' END';
		}

		$query = $db->getQuery(true);
		$query->select(
			array(
				'orders.*',
				$db->qn('oitem.order_id'),
				$db->qn('oitem.product_name'),
				$db->qn('oitem.product_sku'),
				$db->qn('oitem.quantity'),
				$db->qn('oitem.price'),
				$db->qn('ia.string_value'),
				$db->qn('ia2.string_value', 'string_value2'),
				$db->qn('ol.log_type'),
				$db->qn('orders.created_date', 'date'),
				$db->qn('cu.alpha3', 'currency_code'),
				$statusCase . ' AS ' . $db->qn('status_code')
			)
		)
			->from('(' . $employeeQuery->union($departmentQuery)->union($companyQuery) . ') AS ' . $db->qn('orders'))
			->leftJoin($db->qn('#__redshopb_order_item', 'oitem') . ' ON ' . $db->qn('oitem.order_id') . ' = ' . $db->qn('orders.id'))
			->leftJoin(
				$db->qn('#__redshopb_order_item_attribute', 'ia')
				. ' ON (' . $db->qn('ia.order_item_id') . ' = ' . $db->qn('oitem.id') . ' AND ' . $db->qn('ia.ordering') . ' = ' . $db->q('0') . ')'
			)
			->leftJoin(
				$db->qn('#__redshopb_order_item_attribute', 'ia2')
				. ' ON (' . $db->qn('ia2.order_item_id') . ' = ' . $db->qn('oitem.id') . ' AND ' . $db->qn('ia2.ordering') . ' = ' . $db->q('1') . ')'
			)
			->leftJoin($db->qn('#__redshopb_order_logs', 'ol') . ' ON ' . $db->qn('orders.id') . ' = ' . $db->qn('ol.new_order_id'))
			->innerJoin($db->qn('#__redshopb_currency', 'cu') . ' ON ' . $db->qn('orders.currency_id') . ' = ' . $db->qn('cu.id'));

		if ($this->getState('nongrouping', 'false') == 'false')
		{
			$query->group('orders.id, ol.new_order_id');
		}
		else
		{
			$query->group('oitem.id');
			$this->setState('nongrouping', 'false');
		}

		// Filter parent
		$parents = RedshopbHelperDatabase::filterInteger($this->getState('filter.parent_id'));

		if ($parents)
		{
			$query->innerJoin($db->qn('#__redshopb_order_logs', 'rol') . ' ON ' . $db->qn('rol.order_id') . '= ' . $db->qn('orders.id'));

			if (count($parents) == 1)
			{
				$query->where('rol.new_order_id = ' . $parents[0]);
			}
			else
			{
				$query->where('rol.new_order_id IN (' . implode(',', $parents) . ')');
			}
		}

		// Filter search
		$search = $this->getState('filter.search_orders');

		if (!empty($search))
		{
			$orderNumber = (int) $search;
			$search      = $db->quote('%' . $db->escape($search, true) . '%');

			$searchCondition   = array();
			$searchCondition[] = '(' . $db->qn('orders.customer_name') . ' LIKE ' . $search . ')';
			$searchCondition[] = '(' . $db->qn('orders.vendor_name') . ' LIKE ' . $search . ')';

			if ($orderNumber > 0)
			{
				$searchCondition[] = '(' . $db->qn('orders.id') . ' = ' . (int) $orderNumber . ')';
			}

			$query->where(implode(' OR ', $searchCondition));
		}

		// Filter by user id
		$userId = $this->getState('filter.user_id', null);

		if (!empty($userId) && is_numeric($userId))
		{
			$query->where($db->qn('orders.user_id') . ' = ' . (int) $userId);
		}

		// Filter by order status.
		$orderStatus = $this->getState('filter.order_status');

		if (is_numeric($orderStatus))
		{
			$query->where($db->qn('orders.status') . ' = ' . (int) $orderStatus);
		}
		elseif (empty($search) && empty($userId))
		{
			$subQuery = $this->getParentsSubquery();
			$children = $db->setQuery($subQuery)->loadColumn();

			if (!empty($children))
			{
				$query->where('orders.id NOT IN (' . implode(',', $children) . ')');
			}
		}

		// Filter by company
		$companyFilter = $this->getState('filter.customer_company', $this->getState('filter.company_id'));

		if (is_numeric($companyFilter))
		{
			$query->where('orders.company_id = ' . (int) $companyFilter);
		}

		// Filter by customer department
		$departmentFilter = $this->getState('filter.customer_department', $this->getState('filter.department_id'));

		if (is_numeric($departmentFilter))
		{
			$query->where($db->qn('orders.department_id') . ' = ' . (int) $departmentFilter);
		}

		// Filter by user
		$deliveryAddressFilter = $this->getState('filter.delivery_address_id');

		if (is_numeric($deliveryAddressFilter))
		{
			$query->where($db->qn('orders.delivery_address_id') . ' = ' . (int) $deliveryAddressFilter);
		}

		// Filter above some order id
		$previousId = $this->getState('filter.previous_id', null);

		if (!is_null($previousId))
		{
			$query->where($db->qn('orders.id') . ' > ' . (int) $previousId);
		}

		// Filter by status code
		$statusFilter = $this->getState('filter.status_code', '');

		if (!empty($statusFilter))
		{
			$statusCodes  = RedshopbEntityOrder::getAllowedStatusCodes();
			$statusFilter = array_search($statusFilter, $statusCodes);

			if ($statusFilter === false)
			{
				// If given status code is not valid, does not return any record
				$query->where('0 = 1');
			}
			else
			{
				$query->where($db->qn('orders.status') . ' = ' . (int) $statusFilter);
			}
		}

		// Filter by date
		$filterDateFrom = $this->getState('filter.date_from', null);

		if (!empty($filterDateFrom))
		{
			$query->where($db->qn('orders.created_date') . ' >= ' . $db->q($filterDateFrom));
		}

		$filterDateTo = $this->getState('filter.date_to', null);

		if (!empty($filterDateTo))
		{
			$query->where($db->qn('orders.created_date') . ' <= ' . $db->q($filterDateTo));
		}

		// Filter by payment title (method)
		$filterPaymentMethod = $this->getState('filter.payment_title', $this->getState('filter.payment_method', null));

		if (!empty($filterPaymentMethod))
		{
			$query->where($db->qn('orders.payment_title') . ' = ' . $db->q($filterPaymentMethod));
		}

		// Filter by payment status
		$filterPaymentStatus = $this->getState('filter.payment_status', null);

		if (!empty($filterPaymentStatus))
		{
			$query->where($db->qn('orders.payment_status') . ' = ' . $db->q($filterPaymentStatus));
		}

		// Ordering
		$orderList     = $this->getState('list.ordering');
		$directionList = $this->getState('list.direction');

		$orderBy = $db->qn('orders.created_date') . ' DESC, ' . $db->qn('orders.id') . ' DESC';

		if (!empty($orderList) && !empty($directionList))
		{
			$orderBy = $db->escape($orderList) . ' ' . $db->escape($directionList);
		}

		$query->order($orderBy);

		// Adds related web service data when requested
		$this->getListQueryWS($query);

		return $query;
	}

	/**
	 * Set queries restrictions (ACL checks)
	 * Each of the query objects provided will have an additional where clause added unless the user is super admin
	 *
	 * @param   JDatabaseQuery  $employeeQuery    Query for employees' orders
	 * @param   JDatabaseQuery  $departmentQuery  Query for departments' orders
	 * @param   JDatabaseQuery  $companyQuery     Query for companies' orders
	 *
	 * @return  void
	 */
	protected function setQueryRestrictions($employeeQuery, $departmentQuery, $companyQuery)
	{
		if (RedshopbHelperACL::isSuperAdmin())
		{
			return;
		}

		// Check for available companies and departments for this user if not a system admin of the app
		$user = Factory::getUser();

		$availableCompanies = RedshopbHelperACL::listAvailableCompaniesByPermission($user->id, 'redshopb.order.view', 'comma', false);

		if (!$availableCompanies)
		{
			$availableCompanies = '0';
		}

		$availableDepartments = RedshopbHelperACL::listAvailableDepartmentsByPermission($user->id, 'redshopb.order.view', 'comma', false);

		if (!$availableDepartments)
		{
			$availableDepartments = '0';
		}

		$db = $this->getDbo();

		/**
		 * Join with departments to check depts permissions
		 */
		$employeeQuery
			->where(
				'(' .
				$db->qn('o.customer_id') . ' = ' . (int) RedshopbHelperUser::getUserRSid() .
				' OR ' . $db->qn('d.id') . ' IN (' . $availableDepartments . ')' .
				' OR ' . $db->qn('c.id') . ' IN (' . $availableCompanies . ')' .
				')'
			);

		// Checks for department specific permissions.  Applies to HODs and company admins too
		$departmentQuery->where($db->qn('o.customer_id') . ' IN (' . $availableDepartments . ')');

		// Searches for company-specific permission.  No HODs or users with department-specific permission will see this orders
		$companyQuery->where($db->qn('o.customer_id') . ' IN (' . $availableCompanies . ')');
	}

	/**
	 * Get subquery for getting parent orders, according to the current ACL
	 *
	 * @return  JDatabaseQuery
	 */
	protected function getParentsSubquery()
	{
		$db = $this->getDbo();

		// If customer is Employee
		$employeeQuery = $db->getQuery(true)
			->select('oc.id')
			->from($db->qn('#__redshopb_order', 'oc'))
			->from($db->qn('#__redshopb_order', 'o'))
			->innerJoin($db->qn('#__redshopb_order_logs', 'ol') . ' ON ' . $db->qn('o.id') . ' = ' . $db->qn('ol.new_order_id'))
			->where($db->qn('oc.id') . ' = ' . $db->qn('ol.order_id'))
			->leftJoin($db->qn('#__redshopb_user', 'u') . ' ON ' . $db->qn('u.id') . ' = ' . $db->qn('o.customer_id'))
			->leftJoin(
				$db->qn('#__redshopb_user_multi_company', 'umc') . ' ON umc.user_id = u.id AND umc.company_id = ' . $db->qn('o.user_company_id')
			)
			->leftJoin($db->qn('#__redshopb_company', 'c') . ' ON ' . $db->qn('c.id') . ' = ' . $db->qn('umc.company_id'))
			->leftJoin($db->qn('#__redshopb_department', 'd') . ' ON ' . $db->qn('u.department_id') . ' = ' . $db->qn('d.id'))
			->where($db->qn('o.customer_type') . ' = ' . $db->q('employee'));

		// If customer is Department
		$departmentQuery = $db->getQuery(true)
			->select('oc.id')
			->from($db->qn('#__redshopb_order', 'oc'))
			->from($db->qn('#__redshopb_order', 'o'))
			->innerJoin($db->qn('#__redshopb_order_logs', 'ol') . ' ON ' . $db->qn('o.id') . ' = ' . $db->qn('ol.new_order_id'))
			->where($db->qn('oc.id') . ' = ' . $db->qn('ol.order_id'))
			->where($db->qn('o.customer_type') . ' = ' . $db->q('department'));

		// If customer is Company
		$companyQuery = $db->getQuery(true)
			->select('oc.id')
			->from($db->qn('#__redshopb_order', 'oc'))
			->from($db->qn('#__redshopb_order', 'o'))
			->innerJoin($db->qn('#__redshopb_order_logs', 'ol') . ' ON ' . $db->qn('o.id') . ' = ' . $db->qn('ol.new_order_id'))
			->where($db->qn('oc.id') . ' = ' . $db->qn('ol.order_id'))
			->where($db->qn('o.customer_type') . ' = ' . $db->q('company'));

		$this->setQueryRestrictions($employeeQuery, $departmentQuery, $companyQuery);

		$query = $db->getQuery(true);
		$query->select(
			array('orders.id')
		)
			->from('(' . $employeeQuery->union($departmentQuery)->union($companyQuery) . ') AS ' . $db->qn('orders'));

		return $query;
	}

	/**
	 * Get the chludren orders for a glven parent
	 *
	 * @param   int  $parent  the parent id
	 *
	 * @return array
	 */
	public function getChildrenOrders($parent)
	{
		$db      = $this->getDbo();
		$results = array();
		$query   = $db->getQuery(true);

		// Get the children Ids
		$query->select('order_id')->from($db->qn('#__redshopb_order_logs'))->where($db->qn('new_order_id') . '=' . $db->q($parent));
		$db->setQuery($query);
		$orderIds = array_keys($db->loadAssocList('order_id'));

		// Now grab the children
		if ($orderIds)
		{
			$query = $this->getListQuery();
			$query->clear('where');
			$query->where($db->qn('orders.id') . 'IN (' . implode(',', $orderIds) . ')');
			$db->setQuery($query);
			$results = $db->loadObjectList();
		}

		return $results;
	}

	/**
	 * Get customer formatted order from db.
	 *
	 * @param   int  $orderId  Order id.
	 *
	 * @return object Customer order.
	 */
	public function getCustomerOrder($orderId)
	{
		$db = $this->getDbo();

		$query = $db->getQuery(true);
		$query->select(
			array(
					'oi.*',
					'p.category_id'
				)
		)
			->from($db->qn('#__redshopb_order_item', 'oi'))
			->join('left', $db->qn('#__redshopb_product', 'p') . ' ON ' . $db->qn('oi.product_id') . ' = ' . $db->qn('p.id'))
			->where($db->qn('oi.order_id') . ' = ' . (int) $orderId)
			->where($db->qn('oi.parent_id') . ' IS NULL');
		$items = $db->setQuery($query)->loadObjectList();

		$query->clear()
			->select('t.*')
			->from($db->qn('#__redshopb_order_tax', 't'))
			->where('t.order_id = ' . (int) $orderId);
		$taxes = $db->setQuery($query)->loadObjectList();

		$query->clear();
		$query->select('*')
			->from($db->qn('#__redshopb_order'))
			->where($db->qn('id') . ' = ' . (int) $orderId);
		$customerOrder = $db->setQuery($query)->loadObject();

		$this->populateCustomerOrder($customerOrder);

		if (!empty($items))
		{
			$customerOrder->regular->items = array_values($items);

			$i                                                = 0;
			$c                                                = 0;
			$subTotal                                         = 0.0;
			$customerOrder->regular->subtotalWithoutDiscounts = 0;
			$offersData                                       = array();

			foreach ($customerOrder->regular->items as $key => $item)
			{
				// @toDo : Missing translations!
				$query->clear()
					->select(
						array(
							$db->qn('name'),
							'(CASE WHEN string_value IS NOT NULL THEN string_value WHEN float_value IS NOT NULL THEN float_value ' .
							'WHEN int_value IS NOT NULL THEN int_value END) AS value',
							$db->qn('sku')
						)
					)
					->from($db->qn('#__redshopb_order_item_attribute'))
					->where($db->qn('order_item_id') . ' = ' . (int) $item->id);

				$item->attributes = $db->setQuery($query)->loadObjectList('name');

				// Stockroom data
				$item->stock        = 0;
				$item->stockroom_id = RedshopbHelperStockroom::getProductAvailableStockroom(
					$item->product_id,
					$item->product_item_id,
					$item->quantity
				);

				// @ToDo: This part needs to be altered, we can't use live stockroom status for completed orders
				if ($item->product_item_id)
				{
					$stockroom = RedshopbHelperStockroom::getProductItemStockroomData($item->product_item_id, array($item->stockroom_id));

					if (!empty($stockroom[$item->product_item_id . '_' . $item->stockroom_id]))
					{
						$stockroom = $stockroom[$item->product_item_id . '_' . $item->stockroom_id];
					}
				}
				else
				{
					$stockroom = RedshopbHelperStockroom::getProductStockroomData($item->product_id, $item->stockroom_id);
				}

				$this->setOrderItemStock($item, $stockroom);

				$item->product_item_code = $item->product_sku . ($item->product_item_sku != '' ? '-' . $item->product_item_sku : '');
				$item->order             = $item->quantity;

				$query->clear()
					->select(
						array(
							$db->qn('oi') . '.*',
							$db->qn('oi.product_sku', 'sku')
						)
					)
					->from($db->qn('#__redshopb_order_item', 'oi'))
					->where($db->qn('oi.parent_id') . ' = ' . (int) $item->id);
				$accessoriesPrice = 0;

				$item->keyAccessories = '';

				$item->accessories = $db->setQuery($query)->loadAssocList();

				if ($item->accessories)
				{
					$key = array();

					foreach ($item->accessories as $keyAccessory => $accessory)
					{
						$accessoryQuantity                            = $accessory['quantity'] / $item->quantity;
						$item->accessories[$keyAccessory]['quantity'] = $accessoryQuantity;
						$accessoriesPrice                            += $accessoryQuantity * $accessory['price'];
						$key[]                                        = $accessory['product_id'];
						$key[]                                        = 'q' . $accessoryQuantity;
					}

					$item->keyAccessories = implode('a', $key);
				}

				$item->price    = (float) $item->price;
				$item->discount = (float) $item->discount;

				$item->final_price = RedshopbHelperPrices::calculateDiscount($item->price_without_discount, $item->discount_type, $item->discount);
				$item->final_price = round($item->final_price, 2) * $item->quantity + ($accessoriesPrice * $item->quantity);

				$item->price_without_discount = (float) $item->price_without_discount;
				$item->params                 = new Joomla\Registry\Registry($item->params);

				$subtotalWithoutDiscounts = ($item->price_without_discount + $accessoriesPrice) * $item->quantity;

				if (isset($item->offer_id) && $item->offer_id > 0)
				{
					$item->collectionId = $c;
					$c++;

					if (!isset($offersData[$item->offer_id]))
					{
						$offersData[$item->offer_id]['items']                    = array();
						$offersData[$item->offer_id]['subtotal']                 = 0;
						$offersData[$item->offer_id]['subtotalWithoutDiscounts'] = 0;
					}

					$offersData[$item->offer_id]['subtotal']                 += $item->final_price;
					$offersData[$item->offer_id]['subtotalWithoutDiscounts'] += $subtotalWithoutDiscounts;
					$offersData[$item->offer_id]['items'][]                   = $item;

					unset($customerOrder->regular->items[$key]);
				}
				else
				{
					$subTotal                                         += $item->final_price;
					$customerOrder->regular->subtotalWithoutDiscounts += $subtotalWithoutDiscounts;
					$item->collectionId                                = $i;
					$i++;
				}
			}

			$this->processOffersData($offersData, $customerOrder);
		}

		if (!empty($taxes))
		{
			foreach ($taxes as $tax)
			{
				$singleTax           = new stdClass;
				$singleTax->name     = $tax->name;
				$singleTax->tax_rate = $tax->tax_rate;
				$singleTax->tax      = $tax->price;

				$customerOrder->taxs[] = $singleTax;
				$customerOrder->tax   += $singleTax->tax;
			}
		}

		// Taxes and shipping price already included in the total price
		$customerOrder->totalFinal = $customerOrder->total_price;

		RFactory::getDispatcher()->trigger('onGetRedshopbCustomerOrder', array(&$customerOrder));

		return $customerOrder;
	}

	/**
	 * Initially populates the data for a found $customerOrder object
	 *
	 * @param   object|null   $customerOrder   Customer order object or null
	 *
	 * @return void
	 */
	private function populateCustomerOrder(&$customerOrder)
	{
		if ($customerOrder)
		{
			$customerOrder->regular                = new stdClass;
			$customerOrder->customerType           = $customerOrder->customer_type;
			$customerOrder->customerId             = $customerOrder->customer_id;
			$customerOrder->regular->total         = $customerOrder->total_price;
			$customerOrder->regular->totalFinal    = $customerOrder->total_price;
			$customerOrder->regular->discount      = $customerOrder->discount;
			$customerOrder->regular->discount_type = $customerOrder->discount_type;
			$customerOrder->taxs                   = array();
			$customerOrder->tax                    = 0;
		}
	}

	/**
	 * Sets the stock for an item based on the stockroom data
	 *
	 * @param   object        $item        Item object
	 * @param   object|null   $stockroom   Stockroom data
	 *
	 * @return void
	 */
	private function setOrderItemStock(&$item, $stockroom)
	{
		if ($stockroom)
		{
			$item->stockroom_name = $stockroom->name;

			if ($stockroom->unlimited)
			{
				$item->stock = -1;
			}
			else
			{
				$item->stock = (int) $stockroom->amount;
			}
		}
	}

	/**
	 * Method to load additional form in the model
	 *
	 * @param   array   $offersData     Array containing data of offers
	 * @param   object  $customerOrder  Full object of the order
	 *
	 * @return void
	 */
	private function processOffersData($offersData, &$customerOrder)
	{
		if (!empty($offersData))
		{
			$customerOrder->offers = array();

			foreach ($offersData AS $offerId => $data)
			{
				$fullOffer                             = RedshopbHelperCart::loadOffer($offerId, true);
				$fullOffer['items']                    = $data['items'];
				$fullOffer['subtotal']                 = $data['subtotal'];
				$fullOffer['subtotalWithoutDiscounts'] = $data['subtotalWithoutDiscounts'];

				$customerOrder->offers[] = (object) $fullOffer;
			}
		}
	}

	/**
	 * Method to load additional form in the model
	 *
	 * @param   string  $name  Form name
	 *
	 * @return Form object
	 */
	public function getCustomForm($name)
	{
		// Get the form.
		$form = $this->loadForm(
			$this->context . '.' . $name, $name,
			array(
				'control'   => 'jform',
				'load_data' => false
			)
		);

		return $form;
	}

	/**
	 * Overridden to add template folder path to the Form paths
	 *
	 * @param   string   $name     The name of the form.
	 * @param   string   $source   The form source. Can be XML string if file flag is set to false.
	 * @param   array    $options  Optional array of options for the form creation.
	 * @param   boolean  $clear    Optional argument to force load a new form.
	 * @param   mixed    $xpath    An optional xpath to search for the fields.
	 *
	 * @return  mixed  Form object on success, False on error.
	 */
	protected function loadForm($name, $source = null, $options = array(), $clear = false, $xpath = false)
	{
		// Handle the optional arguments.
		$options['control'] = ArrayHelper::getValue($options, 'control', false);

		// Create a signature hash.
		$hash = md5($source . serialize($options));

		// Check if we can use a previously loaded form.
		if (isset($this->forms[$hash]) && !$clear)
		{
			return $this->forms[$hash];
		}

		$template         = Factory::getApplication()->getTemplate();
		$templateFormPath = JPATH_THEMES . '/' . $template . '/forms/com_redshopb/' . strtolower($this->getName());

		// Get the form.
		RForm::addFormPath(JPATH_COMPONENT . '/models/forms');
		RForm::addFormPath($templateFormPath);
		RForm::addFieldPath(JPATH_COMPONENT . '/models/fields');

		try
		{
			$form = RForm::getInstance($name, $source, $options, false, $xpath);

			if (isset($options['load_data']) && $options['load_data'])
			{
				// Get the data for the form.
				$data = $this->loadFormData();
			}
			else
			{
				$data = array();
			}

			// Allow for additional modification of the form, and events to be triggered.
			// We pass the data because plugins may require it.
			$this->preprocessForm($form, $data);

			// Filter the form data.
			$data = $form->filter($data);

			// Load the data into the form after the plugins have operated.
			$form->bind($data);
		}
		catch (Exception $e)
		{
			$this->setError($e->getMessage());

			return false;
		}

		// Store the form for later.
		$this->forms[$hash] = $form;

		return $form;
	}

	/**
	 * Getting last levels all orders
	 *
	 * @param   array   $orderIds  Order ids
	 * @param   string  $logType   Log type
	 *
	 * @return  array
	 */
	public function getChildOrders($orderIds, $logType = 'collect')
	{
		if (!count($orderIds))
		{
			return array();
		}

		$orderIds = ArrayHelper::toInteger($orderIds);
		$children = array();

		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->select('ol.order_id, ol.new_order_id')
			->from($db->qn('#__redshopb_order_logs', 'ol'))
			->where('ol.new_order_id IN (' . implode(',', $orderIds) . ')')
			->where('ol.log_type = ' . $db->q($logType));

		$results = $db->setQuery($query)->loadObjectList();

		if (empty($results))
		{
			$children = array_merge($children, $orderIds);

			return $children;
		}

		$newOrderIds = array();
		$hasChildren = array();

		foreach ($results as $result)
		{
			$newOrderIds[]                      = $result->order_id;
			$hasChildren[$result->new_order_id] = $result->new_order_id;
		}

		foreach ($orderIds as $orderId)
		{
			if (!in_array($orderId, $hasChildren))
			{
				$children[] = $orderId;
			}
		}

		if (count($newOrderIds))
		{
			$children = array_merge($children, $this->getChildOrders($newOrderIds, $logType));
		}

		return $children;
	}

	/**
	 * Collect selected orders and creates single one with updated quantity & price.
	 *
	 * @param   array   $orderIds     Order ids for collection.
	 * @param   int     $address      Delivery address id.
	 * @param   string  $comment      Order comment.
	 * @param   string  $requisition  Order requisition.
	 *
	 * @throws Exception
	 *
	 * @return  boolean  True on success, false otherwise.
	 */
	public function collectOrders($orderIds, $address = 0, $comment = '', $requisition = '')
	{
		$db                  = $this->getDbo();
		$query               = $db->getQuery(true);
		$newOrder            = array();
		$totalPrice          = 0.0;
		$childOrderIdsString = implode(',', $this->getChildOrders($orderIds));
		$orderIdsString      = implode(',', $orderIds);
		$app                 = Factory::getApplication();
		$config              = RedshopbEntityConfig::getInstance();
		$customerId          = $app->getUserState('orders.customer_id', 0);
		$customerType        = $app->getUserState('orders.customer_type', '');
		$parentEntity        = $app->getUserState('orders.parentEntity');
		$orderCompany        = RedshopbHelperCompany::getCompanyByCustomer($parentEntity->id, 'company', false);
		$purchaser           = RedshopbHelperOrder::getEntityFromCustomer($customerId, $customerType);
		$purchaserCompany    = RedshopbHelperCompany::getCompanyByCustomer($customerId, $customerType, false);
		$fees                = RedshopbHelperShop::getChargeProducts('fee');
		$currency            = $app->getUserState('orders.currency', 0);
		$now                 = Date::getInstance()->toSql();
		$itemList            = array();

		$defaultRequisition = '';

		if (isset($purchaser->requisition))
		{
			$defaultRequisition = $purchaser->requisition;
		}

		if ($currency == 0 || $purchaserCompany->type == 'customer')
		{
			if ($customerType == 'company' && !is_null($purchaser->currency_id))
			{
				$currency = $purchaser->currency_id;
			}
			else
			{
				$currency = $config->getInt('default_currency', 159);
			}
		}

		$collectionInfo = $this->getConcatenatedOrdersInfo($orderIds);

		$comment     = ($comment != '') ? $comment : $collectionInfo['comment'];
		$requisition = ($requisition != '') ? $requisition : (($defaultRequisition != '') ? $defaultRequisition : $collectionInfo['requisition']);

		if ($address == 0)
		{
			Factory::getApplication()->enqueueMessage(Text::_('COM_REDSHOPB_ORDERS_COLLECTION_MISSING_DELIVERY_ADDRESS'), 'error');

			return false;
		}

		$deliveryAddressId = $address;

		$query->clear()
			->select('o.*')
			->select(
				'SUM(CASE ' . $db->qn('oix.discount_type') . ' WHEN ' . $db->quote('percent') . ' THEN '
				. '(oix.price_without_discount * oix.quantity) - (oix.price_without_discount * oix.quantity * oix.discount / 100) '
				. ' ELSE (oix.price_without_discount * oix.quantity) - oix.discount END) AS offerSubtotal'
			)
			->from($db->qn('#__redshopb_order', 'o'))
			->leftJoin($db->qn('#__redshopb_order_item', 'oix') . ' ON o.id = oix.order_id')
			->where('o.id IN (' . $childOrderIdsString . ')')
			->where('o.offer_id IS NOT NULL')
			->group('o.id');
		$orders = $db->setQuery($query)->loadObjectList('id');

		$newOrder['discount_type'] = 'percent';
		$discountAmount            = 0;
		$discountPercent           = 0;

		foreach ($orders as $order)
		{
			if ($order->offer_id)
			{
				$totalPrice += $order->total_price;

				if ($order->discount_type == 'percent')
				{
					$discountAmount += $order->offerSubtotal - $order->total_price;

					if (!$discountPercent)
					{
						$discountPercent = $order->discount;
					}
					else
					{
						$newOrder['discount_type'] = 'total';
					}
				}

				if ($order->discount_type == 'total')
				{
					$newOrder['discount_type'] = 'total';
					$discountAmount           += $order->discount;
				}
			}
		}

		$query->clear()
			->select('oi.*, o.offer_id')
			->from($db->qn('#__redshopb_order_item', 'oi'))
			->leftJoin($db->qn('#__redshopb_order', 'o') . ' ON o.id = oi.order_id')
			->where($db->qn('o.id') . ' IN (' . $childOrderIdsString . ')')
			->where($db->qn('oi.parent_id') . ' IS NULL');

		if ($fees && count($fees))
		{
			$query->where($db->qn('oi.product_item_id') . ' NOT IN (' . implode(',', $fees) . ')');
		}

		$items = $db->setQuery($query)->loadObjectList();

		$itemIds = array(
			'products' => array(),
			'productItems' => array()
		);

		$newItems          = $itemIds;
		$collectionItemIds = $itemIds;
		$collectionItems   = $itemIds;
		$collectionPrices  = $itemIds;
		$iPrices           = $itemIds;

		foreach ($items as $item)
		{
			if (array_key_exists($item->order_id, $orders) && $orders[$item->order_id]->offer_id)
			{
				unset($item->offer_id);
				$itemList[] = $item;
			}
			else
			{
				unset($item->offer_id);

				if ($item->product_item_id)
				{
					$section = 'productItems';
					$id      = $item->product_item_id;
				}
				else
				{
					$section = 'products';
					$id      = $item->product_id;
				}

				if (!is_null($item->collection_id))
				{
					if (!isset($collectionItemIds[$section][$item->collection_id]))
					{
						$collectionItemIds[$section][$item->collection_id] = array();
					}

					if (!array_key_exists($item->product_item_id, $collectionItemIds[$section][$item->collection_id]))
					{
						$collectionItemIds[$section][$item->collection_id][$item->product_item_id] = 0;
					}

					$collectionItemIds[$section][$item->collection_id][$item->product_item_id] += $item->quantity;

					if (!isset($collectionItems[$section][$item->collection_id]))
					{
						$collectionItems[$section][$item->collection_id] = array();
					}

					$collectionItems[$section][$item->collection_id][$id] = $item;
				}
				else
				{
					if (!array_key_exists($id, $itemIds[$section]))
					{
						$itemIds[$section][$id] = 0;
					}

					$itemIds[$section][$id] += $item->quantity;
					$newItems[$section][$id] = $item;
				}
			}
		}

		$orderCompanyId = 0;

		if (!is_null($orderCompany) && $orderCompany->type == 'end_customer' && $customerType == 'company' && $purchaser->type == 'customer')
		{
			$orderCompanyId = $orderCompany->id;
		}

		foreach ($collectionItemIds as $section => $collections)
		{
			foreach ($collections as $collectionId => $wItems)
			{
				if ($section == 'productItems')
				{
					$collectionPrices[$section][$collectionId] = RedshopbHelperPrices::getProductItemsPrice(
						$wItems, array(), $customerId, $customerType, $currency, array($collectionId), '', $orderCompanyId, 0, false, true
					);
				}
				else
				{
					$collectionPrices[$section][$collectionId] = RedshopbHelperPrices::getProductsPrice(
						$wItems, $customerId, $customerType, $currency, array($collectionId), '', $orderCompanyId, 0, false, true
					);
				}
			}
		}

		if (!empty($itemIds['productItems']))
		{
			$iPrices['productItems'] = RedshopbHelperPrices::getProductItemsPrice(
				$itemIds['productItems'], array(), $customerId, $customerType, $currency, array(), '', $orderCompanyId, 0, false, true
			);
		}

		if (!empty($itemIds['products']))
		{
			$iPrices['products'] = RedshopbHelperPrices::getProductsPrice(
				$itemIds['products'], $customerId, $customerType, $currency, array(), '', $orderCompanyId, 0, false, true
			);
		}

		foreach ($newItems as $section => $items)
		{
			foreach ($items as $itemId => $item)
			{
				$temp = false;

				if ($section == 'products' && isset($iPrices[$section][$itemId]))
				{
					$temp = $iPrices[$section][$itemId];
				}
				elseif ($section == 'productItems' && isset($iPrices[$section][$item->product_id][$itemId]))
				{
					$temp = $iPrices[$section][$item->product_id][$itemId];
				}

				if ($temp)
				{
					$item->price                  = (float) $temp->price;
					$item->quantity               = $temp->quantity;
					$item->currency_id            = (int) RedshopbHelperProduct::getCurrency($item->currency_id)->id;
					$item->currency               = RedshopbHelperProduct::getCurrency($item->currency_id)->alpha3;
					$item->price_without_discount = $temp->price_without_discount;

					if (isset($temp->discount))
					{
						$item->discount = $temp->discount;
					}
					else
					{
						$item->discount = 0.0;
					}

					$totalPrice += $item->price * $item->quantity;
				}
				else
				{
					$item->price                  = 0.0;
					$item->currency_id            = $currency;
					$item->price_without_discount = 0.0;
					$item->discount               = 0;
				}

				$itemList[] = $item;
			}
		}

		foreach ($collectionItems as $section => $collections)
		{
			foreach ($collections as $collectionId => $wItems)
			{
				foreach ($wItems as $itemId => $item)
				{
					$temp = false;

					if ($section == 'products' && isset($collectionPrices[$section][$collectionId][$itemId]))
					{
						$temp = $collectionPrices[$section][$collectionId][$itemId];
					}
					elseif ($section == 'productItems' && isset($collectionPrices[$section][$collectionId][$item->product_id][$itemId]))
					{
						$temp = $collectionPrices[$section][$collectionId][$item->product_id][$itemId];
					}

					if ($temp)
					{
						$item->price                  = (float) $temp->price;
						$item->quantity               = $temp->quantity;
						$item->currency_id            = (int) RedshopbHelperProduct::getCurrency($item->currency_id)->id;
						$item->currency               = RedshopbHelperProduct::getCurrency($item->currency_id)->alpha3;
						$item->price_without_discount = $temp->price_without_discount;

						if (isset($temp->discount))
						{
							$item->discount = $temp->discount;
						}
						else
						{
							$item->discount = 0.0;
						}

						$totalPrice += $item->price * $item->quantity;
					}
					else
					{
						$item->price                  = 0.0;
						$item->currency_id            = $currency;
						$item->price_without_discount = 0.0;
						$item->discount               = 0;
					}

					$itemList[] = $item;
				}
			}
		}

		if ($newOrder['discount_type'] == 'percent' && count($itemList) == 0)
		{
			$newOrder['discount'] = $discountPercent;
		}
		else
		{
			$newOrder['discount']      = $discountAmount;
			$newOrder['discount_type'] = 'total';
		}

		$db->transactionStart();

		$currencyTable = RedshopbTable::getInstance('Currency', 'RedshopbTable');
		$currencyTable->load($currency);

		$newOrder['delivery_address_id'] = $deliveryAddressId;
		$newOrder['currency_id']         = $currency;
		$newOrder['currency']            = $currencyTable->alpha3;
		$newOrder['customer_id']         = $customerId;
		$newOrder['customer_type']       = $customerType;
		$newOrder['customer_name']       = $purchaser->name;
		$newOrder['customer_name2']      = $purchaser->name2;
		$newOrder['customer_department'] = null;
		$newOrder['customer_company']    = $customerId;

		if ($newOrder['customer_type'] == 'department')
		{
			$newOrder['customer_company']    = RedshopbHelperDepartment::getCompanyId($newOrder['customer_id']);
			$newOrder['customer_department'] = $customerId;
		}
		elseif ($newOrder['customer_type'] == 'employee')
		{
			$newOrder['customer_company'] = RedshopbHelperUser::getUserCompanyId($newOrder['customer_id']);
			$department                   = RedshopbHelperUser::getUserDepartmentId($newOrder['customer_id']);

			if (!empty($department))
			{
				$newOrder['customer_department'] = $department;
			}

			$newOrder['user_erp_id'] = RedshopbHelperShop::getCustomerErpId($newOrder['customer_id'], 'employee');
		}

		$newOrder['company_erp_id']    = RedshopbHelperShop::getCustomerErpId($newOrder['customer_company'], 'company');
		$newOrder['department_erp_id'] = RedshopbHelperShop::getCustomerErpId($newOrder['customer_department'], 'department');

		$newOrder['status']       = 0;
		$newOrder['placed']       = 0;
		$newOrder['comment']      = $comment;
		$newOrder['requisition']  = $requisition;
		$newOrder['total_price']  = $totalPrice;
		$newOrder['created_by']   = Factory::getUser()->id;
		$newOrder['created_date'] = $now;

		$addressInfo = RedshopbEntityAddress::getInstance($newOrder['delivery_address_id'])->getExtendedData();

		$newOrder['delivery_address_address']      = $addressInfo->address;
		$newOrder['delivery_address_address2']     = $addressInfo->address2;
		$newOrder['delivery_address_name']         = $addressInfo->name;
		$newOrder['delivery_address_name2']        = $addressInfo->name2;
		$newOrder['delivery_address_city']         = $addressInfo->city;
		$newOrder['delivery_address_country']      = Text::_($addressInfo->country);
		$newOrder['delivery_address_country_code'] = $addressInfo->country_code;
		$newOrder['delivery_address_state']        = $addressInfo->state_name;
		$newOrder['delivery_address_state_code']   = $addressInfo->state_code;
		$newOrder['delivery_address_zip']          = $addressInfo->zip;
		$newOrder['delivery_address_code']         = $addressInfo->address_code;
		$newOrder['delivery_address_type']         = $addressInfo->address_type;

		try
		{
			$orderTable = RedshopbTable::getAdminInstance('Order');

			if (is_null($orderTable))
			{
				throw new Exception('Missing order table definition!');
			}

			if (!$orderTable->save($newOrder))
			{
				throw new Exception($orderTable->getError());
			}

			$parentOrderId = $orderTable->id;

			$columns = array(
				$db->qn('new_order_id'),
				$db->qn('order_id'),
				$db->qn('log_type'),
				$db->qn('date')
			);

			$values = array();

			// Storing collection for historical purpose
			foreach ($orderIds as $orderId)
			{
				$values[] = (int) $parentOrderId . ',' . (int) $orderId . ',' . $db->q('collect') . ',' . $db->q($now);
			}

			$query->insert($db->qn('#__redshopb_order_logs'))
				->columns($columns)
				->values($values);

			$db->setQuery($query);

			if (!$db->execute())
			{
				throw new Exception($db->getErrorMsg());
			}

			// Updating collected orders status to "Collect"
			$query->clear();
			$query->update($db->qn('#__redshopb_order'))
				->set($db->qn('status') . ' = 7')
				->where($db->qn('id') . ' IN (' . $orderIdsString . ')');

			if (!$db->setQuery($query)->execute())
			{
				throw new Exception($db->getErrorMsg());
			}

			$orderItemTable          = RTable::getAdminInstance('Order_Item');
			$orderItemAttributeTable = RedshopbTable::getInstance('Order_Item_Attribute', 'RedshopbTable');

			// Save order items
			foreach ($itemList as $item)
			{
				$orderItemTable->reset();
				$orderItemTable->id = null;
				$orderItemId        = $item->id;

				$query->clear();
				$query->select('*')
					->from($db->qn('#__redshopb_order_item', 'oi'))
					->where($db->qn('oi.parent_id') . ' = ' . (int) $orderItemId)
					->group($db->qn('oi.product_sku'));
				$db->setQuery($query);

				$accessories = $db->loadObjectList();

				$query->clear();
				$query->select('*')
					->from($db->qn('#__redshopb_order_item_attribute', 'oia'))
					->where($db->qn('oia.order_item_id') . ' = ' . (int) $orderItemId);
				$db->setQuery($query);

				$attributes = $db->loadObjectList();

				$item->order_id = $parentOrderId;
				$item           = ArrayHelper::fromObject($item);
				unset($item['id']);

				if (!$orderItemTable->save($item))
				{
					throw new Exception($orderItemTable->getError());
				}

				$newItemId = $orderItemTable->id;

				foreach ($accessories as $accessory)
				{
					$accessory->order_id               = $parentOrderId;
					$accessory->parent_id              = $newItemId;
					$accessory->currency               = $item['currency'];
					$accessory->currency_id            = $item['currency_id'];
					$accessory->quantity               = $item['quantity'];
					$accessory->price                  = 0.0;
					$accessory->price_without_discount = 0.0;

					$orderItemTable->reset();
					$orderItemTable->id = null;
					$accessory          = ArrayHelper::fromObject($accessory);
					unset($accessory['id']);

					if (!$orderItemTable->save($accessory))
					{
						throw new Exception($orderItemTable->getError());
					}
				}

				foreach ($attributes as $attribute)
				{
					$orderItemAttributeTable->reset();
					$orderItemAttributeTable->id = null;
					$attribute                   = ArrayHelper::fromObject($attribute);
					unset($attribute['id']);
					$attribute['order_item_id'] = $newItemId;

					if (!$orderItemAttributeTable->save($attribute))
					{
						throw new Exception($orderItemAttributeTable->getError());
					}
				}
			}

			$db->transactionCommit();
		}
		catch (Exception $e)
		{
			$app->enqueueMessage($e->getMessage(), 'error');
			$db->transactionRollback();

			return false;
		}

		if ($customerType == 'company' && $purchaser->type == 'customer')
		{
			// Check total for additional charges
			$this->checkTotal($parentOrderId, $totalPrice, $purchaser);
		}

		return $parentOrderId;
	}

	/**
	 * Delete order items related to orders
	 *
	 * @param   array  $cid  order item ids
	 *
	 * @return boolean
	 */
	public function deleteOrderItems($cid)
	{
		$db       = $this->getDbo();
		$cleanIds = ArrayHelper::toInteger($cid);

		$query = $db->getQuery(true);
		$query->delete($db->qn('#__redshopb_order_item'))
			->where($db->qn('order_id') . ' IN (' . implode(',', $cleanIds) . ')');

		return $db->setQuery($query)->execute();
	}

	/**
	 * Get orders by user id.
	 *
	 * @param   int     $userId          User id
	 * @param   string  $search          Search query
	 * @param   int     $page            Pagination page
	 * @param   string  $orderBy         Order by column name
	 * @param   string  $orderDirection  Order direction ASC|DESC
	 * @param   int     $limit           Limit per page
	 *
	 * @return  object stdClass object with order items and items count.
	 */
	public function getOrdersByUserId($userId, $search = '', $page = 0, $orderBy = '', $orderDirection = 'ASC', $limit = 10)
	{
		$db     = $this->getDbo();
		$query  = $db->getQuery(true);
		$orders = new stdClass;

		$query->select('o.id AS id, o.status AS status, o.requisition AS requisition, o.total_price AS total, o.created_date AS created')
			->select('ju.name AS createdBy')
			->select('c.symbol AS currency')
			->from($db->qn('#__redshopb_order', 'o'))
			->innerJoin($db->qn('#__redshopb_user', 'ru') . ' ON ru.id = o.client_user_id')
			->innerJoin($db->qn('#__users', 'ju') . ' ON ju.id = ru.joomla_user_id')
			->innerJoin($db->qn('#__redshopb_currency', 'c') . ' ON c.id = o.currency_id')
			->where('ru.id = ' . $userId);

		if (isset($search) && !empty($search))
		{
			$search = $db->q('%' . $search . '%');
			$query->where('ju.name LIKE ' . $search . ' OR o.requisition LIKE ' . $search);
		}

		if (isset($orderBy) && !empty($orderBy))
		{
			$query->order($orderBy . ' ' . $orderDirection);
		}

		$page = ($page > 0) ? $page - 1 : 0;

		$db->setQuery($query, ($page * $limit), $limit);

		$orders->items = $db->loadObjectList();

		$query->clear();
		$query->select('COUNT(o.id)')
			->from($db->qn('#__redshopb_order', 'o'))
			->innerJoin($db->qn('#__redshopb_user', 'ru') . ' ON ru.id = o.client_user_id')
			->innerJoin($db->qn('#__users', 'ju') . ' ON ju.id = ru.joomla_user_id')
			->where('ru.id = ' . $userId);

		if (isset($search) && !empty($search))
		{
			$query->where('ju.name LIKE ' . $search . ' OR o.status LIKE ' . $search . ' OR o.requisition LIKE ' . $search);
		}

		$orders->count = (int) $db->setQuery($query)->loadResult();

		return $orders;
	}

	/**
	 * Send orders to an upper level.
	 * If upper level is level 1, trigger webservice plugin
	 * for order sync.
	 *
	 * @param   array   $orderIds     Order ids.
	 * @param   int     $address      Delivery address id.
	 * @param   string  $comment      Order comment.
	 * @param   string  $requisition  Order requisition.
	 *
	 * @return boolean True on success, false otherwise.
	 */
	public function expediteOrders($orderIds, $address = 0, $comment = '', $requisition = '')
	{
		if (!RedshopbEntityConfig::getInstance()->get('order_expedition', 0))
		{
			return false;
		}

		$db          = $this->getDbo();
		$orderModel  = RModel::getFrontInstance('order');
		$expediteIds = array();
		$config      = RedshopbEntityConfig::getInstance();
		$notPending  = array();
		$success     = 0;

		foreach ($orderIds as $orderId)
		{
			$order = $orderModel->getItem($orderId);

			if ($order->status == 0)
			{
				$purchaser     = RedshopbHelperOrder::getPurchaser($order);
				$vendor        = RedshopbHelperOrder::getVendor($order);
				$customerCType = $purchaser->type;

				if ($customerCType == 'end_customer' && isset($vendor))
				{
					try
					{
						// Starting transaction
						$db->transactionStart();

						if (empty($comment))
						{
							$comment = $order->comment;
						}

						if (empty($requisition))
						{
							$requisition = $order->requisition;
						}

						$expeditedOrderId = $this->expediteOrder($order, $vendor, $address, $comment, $requisition, $purchaser->id);
						$success ++;

						// Should we automatically expedite
						if ($vendor->order_approval == 1)
						{
							if ((int) $config->getInt('set_webservices', 0))
							{
								$expediteIds[] = $expeditedOrderId;
								$success ++;
							}
							else
							{
								Factory::getApplication()->enqueueMessage(Text::_('COM_REDSHOPB_ORDERS_EXPEDITE_FAIL_SET_DISABLED'), 'warning');
							}
						}

						// Order expedition success! Committing transaction.
						$db->transactionCommit();
					}
					catch (Exception $e)
					{
						$db->transactionRollback();
						Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');
						$success --;

						continue;
					}
				}
				// Auto expedition or expedition from Customer level is running
				elseif ($customerCType == 'customer')
				{
					// Order is from customer level
					if ($config->getInt('set_webservices', 0))
					{
						$expediteIds[] = $orderId;
						$success ++;
						$data = array();

						if (!empty($comment))
						{
							$data['comment'] = $comment;
						}

						if (!empty($requisition))
						{
							$data['requisition'] = $requisition;
						}

						if (!empty($address))
						{
							$data['delivery_address_id'] = $address;
						}

						if (!empty($comment) || !empty($requisition) || !empty($address))
						{
							$data['id'] = $orderId;
							$orderModel->save($data);
						}
					}
					else
					{
						Factory::getApplication()->enqueueMessage(Text::_('COM_REDSHOPB_ORDERS_EXPEDITE_FAIL_SET_DISABLED'), 'warning');
						$success --;

						continue;
					}
				}
				else
				{
					Factory::getApplication()->enqueueMessage(Text::_('COM_REDSHOPB_ORDERS_EXPEDITE_FAIL_MAIN_LEVEL'), 'warning');
					$success --;

					continue;
				}
			}
			else
			{
				$notPending[] = $orderId;
				$success --;

				continue;
			}
		}

		if (!empty($notPending))
		{
			Factory::getApplication()->enqueueMessage(
				sprintf(
					Text::_('COM_REDSHOPB_ORDERS_EXPEDITE_FAIL_NOT_PENDING'),
					implode(', ', $notPending)
				),
				'warning'
			);
		}

		if (!empty($expediteIds))
		{
			if ($config->getInt('set_webservices', 0))
			{
				PluginHelper::importPlugin('rb_sync');
				$dispatcher = RFactory::getDispatcher();
				$dispatcher->trigger('onFuncWrite', array('SetSalesOrder', array('ids' => $expediteIds)));
			}
			else
			{
				Factory::getApplication()->enqueueMessage(Text::_('COM_REDSHOPB_ORDER_EXPEDITE_SUCCESS'), 'message');
			}

			return true;
		}

		return false;
	}

	/**
	 * Expedite single order, creating it at destination level.
	 *
	 * @param   object  $order        Original order for expedition.
	 * @param   object  $vendor       Vendor company (F. Engel customer).
	 * @param   int     $address      Address for delivery.
	 * @param   string  $comment      Order comment.
	 * @param   string  $requisition  Order requisition.
	 * @param   int     $purchaser    End customer company id (original purchaser/order maker).
	 *
	 * @return integer New expedited order id.
	 *
	 * @throws Exception
	 */
	private function expediteOrder($order, $vendor, $address = 0, $comment = '', $requisition = '', $purchaser = null)
	{
		if (!RedshopbEntityConfig::getInstance()->get('order_expedition', 0))
		{
			throw new Exception(Text::_('COM_REDSHOPB_ORDER_EXPEDITE_FAIL_NOT_ENABLE'));
		}

		$vendorOrder     = $order;
		$orderId         = $order->id;
		$vendorOrder->id = 0;
		$table           = RedshopbTable::getAdminInstance('Order');
		$db              = $this->getDbo();
		$query           = $db->getQuery(true);
		$config          = RedshopbEntityConfig::getInstance();
		$defaultCurrency = $config->getInt('default_currency', 159);
		$fees            = RedshopbHelperShop::getChargeProducts('fee');
		$total           = (float) 0;

		// Getting default requisition number from destination company
		if (isset($vendor->requisition))
		{
			$defaultRequisition = $vendor->requisition;
		}
		else
		{
			$defaultRequisition = '';
		}

		// Getting default currency for $vendor company, if not set system default currency will be used for placing new order
		if (isset($vendor->currency_id))
		{
			$currency = $vendor->currency_id;
		}
		else
		{
			$currency = $defaultCurrency;
		}

		$now                              = Date::getInstance()->toSql();
		$vendorOrder->customer_type       = 'company';
		$vendorOrder->customer_id         = $vendor->id;
		$vendorOrder->customer_company    = $vendor->id;
		$vendorOrder->customer_department = null;
		$vendorOrder->customer_company_id = RedshopbHelperShop::getCustomerErpId($vendor->id, 'company');
		$vendorOrder->currency            = RedshopbHelperProduct::getCurrency($currency)->alpha3;
		$vendorOrder->customer_name       = RedshopbHelperShop::getCustomerName($vendor->id, 'company');
		$vendorOrder->customer_name2      = RedshopbHelperShop::getCustomerName2($vendor->id, 'company');
		$vendorOrder->company_erp_id      = RedshopbHelperShop::getCustomerErpId($vendor->id, 'company');
		$vendorOrder->department_erp_id   = null;
		$vendorOrder->user_erp_id         = null;
		$vendorOrder->status              = 0;
		$vendorOrder->modified_date       = $now;
		$vendorOrder->modified_by         = Factory::getUser()->id;
		$vendorOrder->delivery_address_id = ($address > 0) ? $address : $order->delivery_address_id;
		$vendorOrder->comment             = $comment;
		$vendorOrder->requisition         = ($requisition != '') ? $requisition : $defaultRequisition;
		$vendorOrder->currency_id         = $currency;

		$addressInfo = RedshopbEntityAddress::getInstance($vendorOrder->delivery_address_id)->getExtendedData();

		$vendorOrder->delivery_address_address  = $addressInfo->address;
		$vendorOrder->delivery_address_address2 = $addressInfo->address2;
		$vendorOrder->delivery_address_name     = $addressInfo->name;
		$vendorOrder->delivery_address_name2    = $addressInfo->name2;
		$vendorOrder->delivery_address_city     = $addressInfo->city;
		$vendorOrder->delivery_address_country  = Text::_($addressInfo->country);
		$vendorOrder->delivery_address_zip      = $addressInfo->zip;
		$vendorOrder->delivery_address_code     = $addressInfo->address_code;
		$vendorOrder->delivery_address_type     = $addressInfo->address_type;

		if ($fees && count($fees))
		{
			$itemsQuery = $db->getQuery(true);
			$itemsQuery->select('*')
				->from($db->qn('#__redshopb_order_item', 'oi'))
				->where($db->qn('oi.order_id') . ' = ' . (int) $orderId)
				->where($db->qn('oi.product_item_id') . ' NOT IN (' . implode(',', $fees) . ')')
				->where($db->qn('oi.parent_id') . ' IS NULL');
			$items = $db->setQuery($itemsQuery)->loadObjectList();

			$itemIds           = array();
			$collectionItemIds = array();
			$collectionPrices  = array();
			$prices            = array();

			foreach ($items as $item)
			{
				if (!is_null($item->collection_id))
				{
					if (!isset($collectionItemIds[$item->collection_id]))
					{
						$collectionItemIds[$item->collection_id] = array($item->product_item_id);
					}
					else
					{
						$collectionItemIds[$item->collection_id][] = $item->product_item_id;
					}
				}
				else
				{
					$itemIds[] = $item->product_item_id;
				}
			}

			if (!is_null($purchaser))
			{
				foreach ($collectionItemIds as $collectionId => $wItems)
				{
					$collectionPrices[$collectionId] = RedshopbHelperPrices::getProductItemsPrice(
						array_unique($wItems), array(), $vendor->id, 'company', $currency, array($collectionId), '', $purchaser
					);
				}

				if (!empty($itemIds))
				{
					$prices = RedshopbHelperPrices::getProductItemsPrice(
						array_unique($itemIds), array(), $vendor->id, 'company', $currency, array(), '', $purchaser
					);
				}
			}
			else
			{
				foreach ($collectionItemIds as $collectionId => $wItems)
				{
					$collectionPrices[$collectionId] = RedshopbHelperPrices::getProductItemsPrice(
						array_unique($wItems), array(), $vendor->id, 'company', $currency, array($collectionId)
					);
				}

				if (!empty($itemIds))
				{
					$prices = RedshopbHelperPrices::getProductItemsPrice(array_unique($itemIds), array(), $vendor->id, 'company', $currency);
				}
			}

			foreach ($items as $item)
			{
				if (!is_null($item->collection_id))
				{
					if (isset($collectionPrices[$item->collection_id][$item->product_id][$item->product_item_id]))
					{
						$temp                         = $collectionPrices[$item->collection_id][$item->product_id][$item->product_item_id];
						$item->price                  = (float) $temp->price;
						$item->currency_id            = (int) $temp->currency_id;
						$item->currency               = RedshopbHelperProduct::getCurrency($item->currency_id)->alpha3;
						$item->price_without_discount = $temp->price_without_discount;

						if (isset($temp->discount))
						{
							$item->discount = $temp->discount;
						}
						else
						{
							$item->discount = 0.0;
						}

						$total += $item->price * $item->quantity;
					}
					else
					{
						$item->price                  = 0.0;
						$item->currency_id            = $currency;
						$item->price_without_discount = 0.0;
						$item->discount               = 0;
					}
				}
				else
				{
					if (isset($prices[$item->product_id][$item->product_item_id]))
					{
						$temp                         = $prices[$item->product_id][$item->product_item_id];
						$item->price                  = (float) $temp->price;
						$item->currency_id            = (int) $temp->currency_id;
						$item->currency               = RedshopbHelperProduct::getCurrency($item->currency_id)->alpha3;
						$item->price_without_discount = $temp->price_without_discount;

						if (isset($temp->discount))
						{
							$item->discount = $temp->discount;
						}
						else
						{
							$item->discount = 0.0;
						}

						$total += $item->price * $item->quantity;
					}
					else
					{
						$item->price                  = 0.0;
						$item->currency_id            = $currency;
						$item->price_without_discount = 0.0;
						$item->discount               = 0;
					}
				}
			}
		}

		$vendorOrder->total_price = $total;
		$vendorOrder              = ArrayHelper::fromObject($vendorOrder);

		if (!$table->save($vendorOrder))
		{
			throw new Exception(Text::_('COM_REDSHOPB_ORDER_CREATE_FAIL'));
		}

		$expeditedOrderId = $table->id;

		$orderItemTable          = RTable::getAdminInstance('Order_Item');
		$orderItemAttributeTable = RedshopbTable::getInstance('Order_Item_Attribute', 'RedshopbTable');

		$totalWithAccessories = $total;

		// Save order items
		foreach ($items as $item)
		{
			$item->order_id = $expeditedOrderId;
			$orderItemId    = $item->id;
			$item           = ArrayHelper::fromObject($item);
			$orderItemTable->reset();
			$orderItemTable->id = null;

			$query->clear();
			$query->select('*')
				->from($db->qn('#__redshopb_order_item', 'oi'))
				->where($db->qn('oi.parent_id') . ' = ' . (int) $orderItemId)
				->group($db->qn('oi.product_sku'));
			$db->setQuery($query);

			$itemAccessories   = array();
			$accessoriesPrices = array();

			$accessories = $db->loadObjectList();

			if ($accessories)
			{
				foreach ($accessories as $accessory)
				{
					$itemAccessories[] = $accessory->product_id;
				}

				if (!is_null($purchaser))
				{
					$accessoriesPrices = RedshopbHelperPrices::getProductsPrice(
						$itemAccessories, $vendor->id, 'company', $item['currency_id'], array(), '', $purchaser
					);
				}
				else
				{
					$accessoriesPrices = RedshopbHelperPrices::getProductsPrice($itemAccessories, $vendor->id, 'company', $item['currency_id']);
				}
			}

			$query->clear();
			$query->select('*')
				->from($db->qn('#__redshopb_order_item_attribute', 'oia'))
				->where($db->qn('oia.order_item_id') . ' = ' . (int) $orderItemId);
			$db->setQuery($query);

			$attributes = $db->loadObjectList();
			unset($item['id']);

			if (!$orderItemTable->save($item))
			{
				throw new Exception($orderItemTable->getError());
			}

			$newItemId = $orderItemTable->id;

			foreach ($accessories as $accessory)
			{
				$accessory->order_id    = $expeditedOrderId;
				$accessory->parent_id   = $newItemId;
				$accessory->currency    = $item['currency'];
				$accessory->currency_id = $item['currency_id'];
				$accessory->quantity    = $item['quantity'];

				if (isset($accessoriesPrices[$accessory->product_id]))
				{
					$accessory->price                  = $accessoriesPrices[$accessory->product_id]->price;
					$accessory->price_without_discount = $accessoriesPrices[$accessory->product_id]->price_without_discount;
					$totalWithAccessories             += $accessory->price * $accessory->quantity;
				}
				else
				{
					$accessory->price                  = 0.0;
					$accessory->price_without_discount = 0.0;
				}

				$orderItemTable->reset();
				$orderItemTable->id = null;
				$accessory          = ArrayHelper::fromObject($accessory);
				unset($accessory['id']);

				if (!$orderItemTable->save($accessory))
				{
					throw new Exception($orderItemTable->getError());
				}
			}

			foreach ($attributes as $attribute)
			{
				$orderItemAttributeTable->reset();
				$orderItemAttributeTable->id = null;
				$attribute                   = ArrayHelper::fromObject($attribute);
				unset($attribute['id']);
				$attribute['order_item_id'] = $newItemId;

				if (!$orderItemAttributeTable->save($attribute))
				{
					throw new Exception($orderItemAttributeTable->getError());
				}
			}
		}

		if ($total != $totalWithAccessories)
		{
			if (!$table->save(array('total_price' => $totalWithAccessories)))
			{
				throw new Exception($table->getError());
			}
		}

		// Insert new log for order expedition
		$columns = array(
			$db->qn('new_order_id'),
			$db->qn('order_id'),
			$db->qn('log_type'),
			$db->qn('date')
		);

		$values = (int) $expeditedOrderId . ',' . (int) $orderId . ',' . $db->q('expedite') . ',' . $db->q($now);

		$query->clear()
			->insert($db->qn('#__redshopb_order_logs'))
			->columns($columns)
			->values($values);
		$db->setQuery($query);

		if (!$db->execute())
		{
			throw new Exception(Text::_('COM_REDSHOPB_ORDER_EXPEDITE_UPDATE_FAIL'));
		}

		// Updating old order status to "Sent to upper level"
		$query->clear()
			->update($db->qn('#__redshopb_order'))
			->set($db->qn('status') . ' = 6')
			->set($db->qn('comment') . ' = ' . $db->q($comment))
			->set($db->qn('requisition') . ' = ' . $db->q($requisition))
			->where($db->qn('id') . ' = ' . (int) $orderId);
		$db->setQuery($query);

		if (!$db->execute())
		{
			throw new Exception(Text::_('COM_REDSHOPB_ORDER_EXPEDITE_UPDATE_FAIL'));
		}

		// Check total for additional charges
		$this->checkTotal($expeditedOrderId, $total, $vendor);

		return $expeditedOrderId;
	}

	/**
	 * Check total price for additional charges.
	 *
	 * @param   int     $orderId  Order id.
	 * @param   float   $total    Order total price.
	 * @param   object  $company  Company object.
	 *
	 * @return void
	 */
	private function checkTotal($orderId, $total, $company)
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		if ((int) $company->calculate_fee && (int) $company->currency_id)
		{
			$fee = RedshopbHelperShop::getAdditionalCharges($company->currency_id, 'fee');

			// Check if fee isn't stored yet
			$query->select($db->qn('id'))
				->from($db->qn('#__redshopb_order_item'))
				->where($db->qn('order_id') . ' = ' . $orderId)
				->where($db->qn('product_item_id') . ' = ' . (int) $fee->itemId);
			$feeId     = (int) $db->setQuery($query)->loadResult();
			$feeExists = $feeId > 0;

			// Check if fee exists for given currency
			if (!is_null($fee) && $fee->limit > $total && !$feeExists)
			{
				// Fee doesn't exists, add it for given order
				$columns = array(
					$db->qn('order_id'),
					$db->qn('product_id'),
					$db->qn('product_name'),
					$db->qn('product_sku'),
					$db->qn('product_item_id'),
					$db->qn('currency_id'),
					$db->qn('currency'),
					$db->qn('price'),
					$db->qn('quantity')
				);
				$values  = ((int) $orderId) . ',' .
					((int) $fee->id) . ',' .
					$db->q($fee->name) . ',' .
					$db->q($fee->sku) . ',' .
					((int) $fee->itemId) . ',' .
					((int) $fee->currency_id) . ',' .
					$db->q($fee->currency) . ',' .
					((float) $fee->price) . ', 1';

				$query->clear()
					->insert($db->qn('#__redshopb_order_item'))
					->columns($columns)
					->values($values);

				$db->setQuery($query)->execute();
				$total += (float) $fee->price;
			}
			// Check if total is higher then fee limit and fee is in order
			elseif (!is_null($fee) && $fee->limit < ($total - $fee->price) && $feeExists)
			{
				$query->clear()
					->delete($db->qn('#__redshopb_order_item'))
					->where($db->qn('id') . ' = ' . $feeId);
				$db->setQuery($query)->execute();
				$total -= (float) $fee->price;
			}

			// Fee added/removed, let's update given order
			if (!is_null($fee))
			{
				$query->clear()
					->update($db->qn('#__redshopb_order'))
					->set($db->qn('total_price') . ' = ' . (float) $total)
					->where($db->qn('id') . ' = ' . (int) $orderId);
				$db->setQuery($query)->execute();
			}
		}
	}

	/**
	 * Method to get an array of data items. Overriden to add static cache support.
	 *
	 * @return  mixed  An array of data items on success, false on failure.
	 *
	 * @since   1.7
	 */
	public function getItems()
	{
		$items = parent::getItems();

		if (!$items)
		{
			return false;
		}

		foreach ($items as $item)
		{
			$this->getShippingRateName($item);

			if ($this->getState('csv', false) == true)
			{
				$customer               = RedshopbHelperOrder::getOrderCustomer($item->id);
				$customerEntity         = RedshopbHelperOrder::getEntityFromCustomer($customer->cid, $customer->ctype);
				$item->customer_email   = $item->customer_email ? $item->customer_email : $customerEntity->email;
				$item->total_item_price = $item->quantity * $item->price;
			}
		}

		return $items;
	}

	/**
	 * Method to add the shipping rate name to an order item
	 *
	 * @param   object  $item  order item
	 *
	 * @return boolean
	 *
	 * @throws Exception
	 */
	private function getShippingRateName($item)
	{
		if (empty($item->shipping_rate_id))
		{
			$item->shipping_code = null;

			return false;
		}

		$customer = RedshopbEntityCustomer::getInstance($item->customer_id, $item->customer_type);

		$deliveryAddress = $customer->getDeliveryAddress()->getExtendedData();
		$shippingMethods = RedshopbHelperOrder::getShippingMethods($item->company_id, $deliveryAddress, $item->total_price, $item->currency);

		foreach ($shippingMethods as $method)
		{
			$rateName = $this->getRateById($method->shippingRates, $item->shipping_rate_id);

			if (!is_null($rateName))
			{
				$item->shipping_code = $rateName;

				break;
			}
		}

		return true;
	}

	/**
	 * Method to return the name of a shipping rate based on rate id
	 *
	 * @param   array  $shippingRates  list of available rates
	 * @param   int    $rateId         shipping rate record id
	 *
	 * @return null
	 */
	private function getRateById($shippingRates, $rateId)
	{
		foreach ($shippingRates as $rate)
		{
			if ($rate->id == $rateId)
			{
				return $rate->name;
			}
		}

		return null;
	}

	/**
	 * Get concatenated orders comment.
	 *
	 * @param   array  $orderIds  Order ids.
	 *
	 * @return string Concatenated comment.
	 */
	public function getConcatenatedOrdersInfo($orderIds = array())
	{
		$db     = $this->getDbo();
		$query  = $db->getQuery(true);
		$result = array();

		$query->select(
			array(
				'GROUP_CONCAT(' . $db->qn('requisition') . ' SEPARATOR \'-\') AS ' . $db->qn('requisition')
			)
		)
			->from($db->qn('#__redshopb_order'))
			->where(
				$db->qn('requisition') . ' IS NOT NULL AND ' . $db->qn('requisition') . ' != \'\' AND ' .
				$db->qn('id') . ' IN (' . implode(',', $orderIds) . ')'
			);
		$result['requisition'] = $db->setQuery($query)->loadResult();

		$query->clear()
			->select(
				array(
					'GROUP_CONCAT(' . $db->qn('comment') . ' SEPARATOR \'. \') AS ' . $db->qn('comment')
				)
			)
			->from($db->qn('#__redshopb_order'))
			->where(
				$db->qn('comment') . ' IS NOT NULL AND ' . $db->qn('comment') . ' != \'\' AND ' .
				$db->qn('id') . ' IN (' . implode(',', $orderIds) . ')'
			);
		$result['comment'] = $db->setQuery($query)->loadResult();

		return $result;
	}

	/**
	 * Get the columns for the csv file.
	 *
	 * @param   bool  $import  Get columns for import or export
	 *
	 * @return  array  An associative array of column names as key and the title as value.
	 */
	public function getCsvColumns($import = false)
	{
		$columns = array(
			'created_date' => Text::_('COM_REDSHOPB_DATE'),
			'company_name' => Text::_('COM_REDSHOPB_COMPANY_LABEL'),
			'department_name' => Text::_('COM_REDSHOPB_DEPARTMENT_LABEL'),
			'customer_name' => Text::_('COM_REDSHOPB_NAME'),
			'customer_email' => Text::_('COM_REDSHOPB_EMAIL'),
			'order_id' => Text::_('COM_REDSHOPB_ORDER_ID_TITLE'),
			'product_name' => Text::_('COM_REDSHOPB_PRODUCT_DESC'),
			'product_sku' => Text::_('COM_REDSHOPB_PRODUCT_SKU'),
			'string_value' => Text::_('COM_REDSHOPB_PRODUCT_DESCRIPTION_COLOR'),
			'string_value2' => Text::_('COM_REDSHOPB_PRODUCT_DESCRIPTION_SIZE'),
			'quantity' => Text::_('COM_REDSHOPB_SHOP_QTY'),
			'price' => Text::_('COM_REDSHOPB_PRICE'),
			'total_item_price' => Text::_('COM_REDSHOPB_ORDER_TOTAL_PRICE'),
			'total_price' => Text::_('COM_REDSHOPB_ORDER_TOTAL_PRICE_DESC')
		);

		Factory::getApplication()->triggerEvent('onAECOrdersGetCsvColumns', array(&$columns));

		return $columns;
	}

	/**
	 * Method to get an array of data items prepared for the web service - including the external keys from sync
	 *
	 * @return  mixed  An array of data items on success, false on failure.
	 */
	public function getItemsWS()
	{
		$items = parent::getItemsWS();

		if (!$items)
		{
			return $items;
		}

		foreach ($items as $i => $item)
		{
			RedshopbHelperOrder::loadShippingDetails($item);
			RedshopbHelperOrder::loadShippingDetailsStockroom($item);
			RedshopbHelperOrder::loadAuthorEmployeeId($item);
		}

		return $items;
	}

	/**
	 * Get data for CSV export
	 *
	 * @param   string   $tableAlias   Aliased table name (usually the first letter)
	 * @param   string   $data         Array data in string format (from e.g. implode())
	 *
	 * @return   array|false
	 */
	public function getItemsCsv($tableAlias = null, $data = null)
	{
		// Get a storage key.
		$store = $this->getStoreId();

		// Try to load the data from internal storage.
		if (isset($this->cache[$store]))
		{
			return $this->cache[$store];
		}

		if (empty($data))
		{
			$items = $this->getItems();
		}
		else
		{
			$db = $this->getDbo();

			// Load the list items.
			$query = $this->getListQuery();

			$data = implode(',', $db->q($data));
			$query->where("{$db->qn('orders.id')} IN ({$data})");

			try
			{
				$items = $this->_getList($query, $this->getStart(), $this->getState('list.limit'));
			}
			catch (RuntimeException $e)
			{
				$this->setError($e->getMessage());

				return false;
			}
		}

		foreach ($items as $item)
		{
			$customer               = RedshopbHelperOrder::getOrderCustomer($item->id);
			$customerEntity         = RedshopbHelperOrder::getEntityFromCustomer($customer->cid, $customer->ctype);
			$item->customer_email   = $customerEntity->email;
			$item->total_item_price = $item->quantity * $item->price;
		}

		Factory::getApplication()->triggerEvent('onAECAfterOrdersGetItemsCsv', array(&$items));

		// Add the items to the internal cache.
		$this->cache[$store] = $items;

		return $items;
	}
}
