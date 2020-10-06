<?php
/**
 * @package     Aesir.E-Commerce.Frontend
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
 * My Offers Model
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 * @since       1.0
 */
class RedshopbModelMyoffers extends RedshopbModelList
{
	/**
	 * Name of the filter form to load
	 *
	 * @var  string
	 */
	protected $filterFormName = 'filter_myoffers';

	/**
	 * Limit field used by the pagination
	 *
	 * @var  string
	 */
	protected $limitField = 'myoffers_limit';

	/**
	 * Limit start field used by the pagination
	 *
	 * @var  string
	 */
	protected $limitstartField = 'auto';

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
				'id', 'off.id',
				'name', 'off.name',
				'company_id', 'off.company_id',
				'department_id', 'off.department_id',
				'user_id', 'off.user_id',
				'customer_type', 'off.customer_type',
				'customer_name', 'filter_status'
			);
		}

		parent::__construct($config);
	}

	/**
	 * Get the associated Table
	 *
	 * @param   string  $name    Table name
	 * @param   string  $prefix  Table prefix
	 * @param   array   $config  Configuration array
	 *
	 * @return  Table
	 */
	public function getTable($name = 'offer', $prefix = '', $config = array())
	{
		if ($name == '')
		{
			$name = 'offer';
		}

		return parent::getTable($name, $prefix, $config);
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return  JDatabaseQuery
	 */
	public function getListQuery()
	{
		$now     = Date::getInstance()->toSql();
		$db      = $this->getDbo();
		$query   = $db->getQuery(true);
		$isTotal = $this->getState('list.isTotal', false);

		if ($isTotal)
		{
			$query->select('COUNT(*)');
		}
		else
		{
			$query->select(
				array(
					'off.*',
					'COALESCE(SUM(offitem.quantity), 0) AS count_products'
					)
			)
				->leftJoin(
					$db->qn('#__redshopb_offer_item_xref', 'offitem')
					. ' ON off.id = offitem.offer_id'
				)
				->group('off.id');
		}

		$query->from($db->qn('#__redshopb_offer', 'off'))
			->where(
				'(off.expiration_date >= STR_TO_DATE(' . $db->q($now) . ', ' . $db->q('%Y-%m-%d %H:%i:%s')
				. ') OR off.expiration_date = ' . $db->q($db->getNullDate()) . ' OR off.expiration_date IS NULL)'
			)
			->where('off.status != ' . $db->q('created'))
			->where('off.state = 1');

		// Filter by status
		$status = $this->getState('filter.status', 0);

		if ($status)
		{
			$query->where($db->qn('off.status') . ' = ' . $db->q($status));
		}

		// Filter by status
		$customerType = $this->getState('filter.customer_type', '');

		if ($customerType)
		{
			$query->where('off.customer_type = ' . $db->q($customerType));
		}

		// Filter by status
		$dateFrom = $this->getState('filter.date_from', 0);
		$dateTo   = $this->getState('filter.date_to', 0);

		if (!empty($dateFrom))
		{
			$query->where($db->qn('off.expiration_date') . ' >= STR_TO_DATE(' . $db->q($dateFrom) . ', ' . $db->q('%Y-%m-%d') . ')');
		}

		if (!empty($dateTo))
		{
			$query->where($db->qn('off.expiration_date') . ' <= STR_TO_DATE(' . $db->q($dateTo) . ', ' . $db->q('%Y-%m-%d') . ')');
		}

		$userId = $this->getState('filter.user_id', 0);

		if ($this->getState('filter.impersonate', false))
		{
			$companiesCount   = RedshopbHelperACL::listAvailableCompanies($userId, 'comma', 0, '', 'redshopb.order.impersonate');
			$departmentsCount = RedshopbHelperACL::listAvailableDepartments($userId, 'comma', 0, false, 0, '', 'redshopb.order.impersonate');
			$employeeCount    = RedshopbHelperACL::listAvailableEmployees(0, 0, 'comma', '', '', 0, 0, 'redshopb.order.impersonate');

			$companiesCount   = $companiesCount ? $companiesCount : 0;
			$departmentsCount = $departmentsCount ? $departmentsCount : 0;
			$employeeCount    = $employeeCount ? $employeeCount : 0;

			$query->select('CASE off.customer_type WHEN ' . $db->q('company') . ' THEN comp.name '
				. 'WHEN ' . $db->q('department') . ' THEN dep.name '
					. 'WHEN ' . $db->q('employee') . ' THEN usr.name1 '
				. 'ELSE NULL END AS customer_name'
			)
				->leftJoin($db->qn('#__redshopb_company', 'comp') . ' ON comp.id = off.company_id')
				->leftJoin($db->qn('#__redshopb_department', 'dep') . ' ON dep.id = off.department_id')
				->leftJoin($db->qn('#__redshopb_user', 'usr') . ' ON usr.id = off.user_id')
				->where('CASE off.customer_type WHEN ' . $db->q('company') . ' THEN off.company_id IN (' . $companiesCount . ') '
					. 'WHEN ' . $db->q('department') . ' THEN off.department_id IN (' . $departmentsCount . ') '
					. 'WHEN ' . $db->q('employee') . ' THEN off.user_id IN (' . $employeeCount . ') ELSE FALSE END'
				);
		}
		/*
		 * Select my offers: if customer_type = company => role = admin;
		 * if customer_type = department => role = hod;
		 * if customer_type = employee => just use user_id condition
		 */
		elseif ($userId)
		{
			$userRSid    = RedshopbHelperUser::getUserRSid();
			$vanirUser   = RedshopbEntityUser::getInstance($userRSid)->loadItem();
			$userCompany = $vanirUser->getSelectedCompany();

			$query->leftJoin(
				$db->qn('#__redshopb_user', 'ru') . ' ON CASE off.customer_type WHEN ' . $db->q('company') . ' THEN off.company_id = '
				. $userCompany->get('id') . ' '
				. 'WHEN ' . $db->q('department') . ' THEN off.department_id = ru.department_id '
				. 'WHEN ' . $db->q('employee') . ' THEN off.user_id = ru.id ELSE FALSE END'
			)
				->innerJoin($db->qn('#__users', 'ju') . ' ON ' . $db->qn('ru.joomla_user_id') . ' = ' . $db->qn('ju.id'))
				->innerJoin($db->qn('#__user_usergroup_map', 'ug') . ' ON ' . $db->qn('ju.id') . ' = ' . $db->qn('ug.user_id'))
				->innerJoin($db->qn('#__redshopb_role', 'r') . ' ON ' . $db->qn('r.joomla_group_id') . ' = ' . $db->qn('ug.group_id'))
				->innerJoin($db->qn('#__redshopb_role_type', 'rt') . ' ON ' . $db->qn('r.role_type_id') . ' = ' . $db->qn('rt.id'))
				->where('CASE off.customer_type WHEN ' . $db->q('company') . ' THEN rt.type = ' . $db->q('admin') . ' '
					. 'WHEN ' . $db->q('department') . ' THEN rt.type = ' . $db->q('hod') . ' '
					. 'WHEN ' . $db->q('employee') . ' THEN TRUE ELSE FALSE END'
				)
				->where('ju.id = ' . (int) $userId);
		}

		// Filter search
		$search = $this->getState('filter.search_myoffers');

		if (!empty($search))
		{
			$search = $db->quote('%' . $db->escape($search, true) . '%');
			$query->where('off.name LIKE ' . $search);
		}

		if (!$isTotal)
		{
			// Ordering
			$orderList     = $this->getState('list.ordering', 'off.id');
			$directionList = $this->getState('list.direction', 'desc');
			$query->order($db->escape($orderList) . ' ' . $db->escape($directionList));
		}

		return $query;
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
	protected function populateState($ordering = 'off.id', $direction = 'desc')
	{
		parent::populateState($ordering, $direction);

		$this->setState('filter.impersonate', RedshopbHelperACL::getPermissionInto('impersonate', 'order'));
		$this->setState('filter.user_id', Factory::getUser()->id);
	}
}
