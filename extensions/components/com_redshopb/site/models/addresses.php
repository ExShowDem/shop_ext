<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;

/**
 * Addresses Model
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 * @since       1.0
 */
class RedshopbModelAddresses extends RedshopbModelList
{
	/**
	 * Name of the filter form to load
	 *
	 * @var  string
	 */
	protected $filterFormName = 'filter_addresses';

	/**
	 * Limit field used by the pagination
	 *
	 * @var  string
	 */
	protected $limitField = 'address_limit';

	/**
	 * Limit start field used by the pagination
	 *
	 * @var  string
	 */
	protected $limitstartField = 'auto';

	/**
	 * Main table query prefix
	 *
	 * @var  array
	 */
	protected $mainTablePrefix = 'addresses';

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
				'id', 'addresses.id',
				'address', 'addresses.address',
				'address2', 'addresses.address2',
				'entity', 'addresses.entity',
				'name', 'addresses.name',
				'country', 'addresses.country',
				'type', 'addresses.type',
				'types', 'addresses.types',
				'customer_type', 'addresses.customer_type',
				'customer_id', 'addresses.customer_id',
				'zip', 'addresses.zip',
				'city', 'addresses.city',
				'addresses.company_id', 'addresses.department_id',
				'addresses.default'
			);
		}

		parent::__construct($config);
	}

	/**
	 * Set queries restrictions (ACL checks)
	 *
	 * @param   JDatabaseQuery  $queryEmployee           Query for employees' addresses
	 * @param   JDatabaseQuery  $queryDepartment         Query for departments' addresses
	 * @param   JDatabaseQuery  $queryCompany            Query for companies' addresses
	 * @param   JDatabaseQuery  $queryDefaultUser        Query for users' addresses
	 * @param   JDatabaseQuery  $queryDefaultDepartment  Query for departments' addresses
	 * @param   JDatabaseQuery  $queryDefaultCompany     Query for companies' addresses
	 *
	 * @return  void
	 */
	protected function setQueryRestrictions(&$queryEmployee, &$queryDepartment, &$queryCompany,
		&$queryDefaultUser, &$queryDefaultDepartment, &$queryDefaultCompany
	)
	{
		$db = $this->getDbo();

		/**
		 * Defines if we will include additional data in user addresses.
		 */
		$includeCompanyData    = $this->getState('filter.user.include_company_data', 1);
		$includeDepartmentData = $this->getState('filter.user.include_department_data', 1);

		// Check for available companies and departments for this user if not a system admin of the app
		if (!RedshopbHelperACL::isSuperAdmin())
		{
			$user = Factory::getUser();

			$availableCompanies   = RedshopbHelperACL::listAvailableCompaniesByPermission($user->id, 'redshopb.address.manage');
			$availableDepartments = RedshopbHelperACL::listAvailableDepartmentsByPermission($user->id, 'redshopb.address.manage');

			$availableCompanies2   = $availableCompanies;
			$availableDepartments2 = $availableDepartments;

			if ($availableCompanies == '0')
			{
				$availableCompanies2 = '-1';
			}

			if ($availableDepartments == '0')
			{
				$availableDepartments2 = '-1';
			}

			$where = array();

			/**
			 * Join with departments and companies to check depts permissions
			 */
			if ($includeDepartmentData)
			{
				$where[] = $db->qn('d.id') . ' IN (' . $availableDepartments . ')';
			}

			if ($includeCompanyData)
			{
				$where[] = $db->qn('c.id') . ' IN (' . $availableCompanies . ')';
			}

			if (!empty($where))
			{
				$queryEmployee->where(
					'(' .
						$db->qn('a.customer_id') . ' = ' . RedshopbHelperUser::getUserRSid() . ' OR ' .
						implode(' OR ', $where) .
					')'
				);
				$queryDefaultUser->where(
					'(' .
						$db->qn('u.id') . ' = ' . RedshopbHelperUser::getUserRSid() . ' OR ' .
						implode(' OR ', $where) .
					')'
				);
			}
			else
			{
				$queryEmployee->where($db->qn('a.customer_id') . ' = ' . RedshopbHelperUser::getUserRSid());
				$queryDefaultUser->where($db->qn('u.id') . ' = ' . RedshopbHelperUser::getUserRSid());
			}

			// Checks for department specific permissions.  Applies to HODs and company admins too
			$queryDepartment->where($db->qn('a.customer_id') . ' IN (' . $availableDepartments . ')');

			$queryDefaultDepartment->where($db->qn('d.id') . ' IN (' . $availableDepartments2 . ')');

			// Searches for company-specific permission.  No HODs or users with department-specific permission will see this addresses
			$queryCompany->where($db->qn('a.customer_id') . ' IN (' . $availableCompanies . ')');

			$queryDefaultCompany->where($db->qn('c.id') . ' IN (' . $availableCompanies2 . ')');
		}
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return  JDatabaseQuery
	 */
	public function getListQuery()
	{
		$db = $this->getDbo();

		$departmentId = $this->getState('filter.ajax.departmentid', 0);

		if (!$departmentId)
		{
			$departmentId = $this->getState('filter.department_id', 0);
		}

		if ($departmentId)
		{
			$companyId = 0;
		}
		else
		{
			$companyId = $this->getState('filter.ajax.companyid', 0);

			if (!$companyId)
			{
				$companyId = $this->getState('filter.company_id', 0);
			}
		}

		$userId = $this->getState('filter.ajax.user_id', 0);

		if (!$userId)
		{
			$userId = $this->getState('filter.user_id', 0);
		}

		/**
		 * Defines if we will include additional data in user addresses.
		 */
		$includeCompanyData    = $this->getState('filter.user.include_company_data', 1);
		$includeDepartmentData = $this->getState('filter.user.include_department_data', 1);

		// If customer is Employee
		$queryEmployee = $db->getQuery(true)
			->select(
				array(
					$db->qn('a.id'),
					$db->qn('a.customer_type'),
					$db->qn('a.customer_id'),
					$db->qn('u.name1', 'customer_name'),
					$db->qn('u.id', 'customer_number_id'),
					$db->qn('a.type'),
					$db->qn('a.order'),
					$db->qn('a.name'),
					$db->qn('a.name', 'name1'),
					$db->qn('a.name2'),
					$db->qn('a.country_id'),
					$db->qn('a.address'),
					$db->qn('a.address2'),
					$db->qn('a.zip'),
					$db->qn('a.city'),
					$db->qn('a.email'),
					$db->qn('a.phone'),
					$db->qn('a.checked_out'),
					$db->qn('a.checked_out_time'),
					$db->qn('a.created_by'),
					$db->qn('a.created_date'),
					$db->qn('a.modified_by'),
					$db->qn('a.modified_date'),
					$db->qn('a.code'),
					$db->qn('co.name', 'country'),
					$db->qn('co.alpha2', 'country_code'),
					'concat(' . $db->qn('u.name1') . ',' . $db->q(' ') . ',' . $db->qn('u.name2') . ') AS entity',
					$db->qn('u.id', 'delivery_for_user_id'),
					'IF (' . $db->qn('a.type') . ' = 3, 1, 0) AS ' . $db->qn('delivery_default')
				)
			)
			->select($db->quoteName('a.address', 'address_line1'))
			->select($db->quoteName('a.address2', 'address_line2'))
			->from($db->qn('#__redshopb_address', 'a'))
			->innerJoin($db->qn('#__redshopb_user', 'u') . ' ON ' . $db->qn('u.id') . ' = ' . $db->qn('a.customer_id'))
			->leftJoin($db->qn('#__redshopb_country', 'co') . ' ON ' . $db->qn('co.id') . ' = ' . $db->qn('a.country_id'))
			->where($db->qn('a.customer_type') . ' = ' . $db->q('employee'));

		if ($includeCompanyData)
		{
			$queryEmployee->select($db->qn('c.id', 'delivery_for_company_id'))
				->leftJoin($db->qn('#__redshopb_user_multi_company', 'umc') . ' ON umc.user_id = u.id')
				->innerJoin(
					$db->qn('#__redshopb_company', 'c') . ' ON ' . $db->qn('c.id') . ' = umc.company_id ' .
					' AND ' . $db->qn('c.deleted') . ' = 0'
				);
		}

		if ($includeDepartmentData)
		{
			$queryEmployee->select($db->qn('d.id', 'delivery_for_department_id'))
				->leftJoin(
					$db->qn('#__redshopb_department', 'd') . ' ON ' . $db->qn('u.department_id') . ' = ' .
					$db->qn('d.id') . ' AND ' . $db->qn('d.deleted') . ' = 0 AND ' . $db->qn('d.state') . ' = 1'
				);
		}

		if ($departmentId && $includeDepartmentData)
		{
			$queryEmployee->where($db->qn('d.id') . '=' . (int) $departmentId);
		}
		elseif ($companyId && $includeCompanyData)
		{
			$queryEmployee->where($db->qn('c.id') . '=' . (int) $companyId);
		}

		if ($userId)
		{
			$queryEmployee->where($db->qn('u.id') . '=' . (int) $userId);
		}

		// If customer is Department
		$queryDepartment = $db->getQuery(true)
			->select(
				array (
					$db->qn('a.id'),
					$db->qn('a.customer_type'),
					$db->qn('a.customer_id'),
					$db->qn('d.name', 'customer_name'),
					$db->qn('d.id', 'customer_number_id'),
					$db->qn('a.type'),
					$db->qn('a.order'),
					$db->qn('a.name'),
					$db->qn('a.name', 'name1'),
					$db->qn('a.name2'),
					$db->qn('a.country_id'),
					$db->qn('a.address'),
					$db->qn('a.address2'),
					$db->qn('a.zip'),
					$db->qn('a.city'),
					$db->qn('a.email'),
					$db->qn('a.phone'),
					$db->qn('a.checked_out'),
					$db->qn('a.checked_out_time'),
					$db->qn('a.created_by'),
					$db->qn('a.created_date'),
					$db->qn('a.modified_by'),
					$db->qn('a.modified_date'),
					$db->qn('a.code'),
					$db->qn('co.name', 'country'),
					$db->qn('co.alpha2', 'country_code'),
					'concat(' . $db->qn('d.name') . ',' . $db->q(' ') . ',' . $db->qn('d.name2') . ') AS entity',
					$db->q('') . ' AS ' . $db->qn('delivery_for_user_id'),
					'IF (' . $db->qn('a.type') . ' = 3, 1, 0) AS ' . $db->qn('delivery_default')
				)
			)
			->select($db->quoteName('a.address', 'address_line1'))
			->select($db->quoteName('a.address2', 'address_line2'))
			->select($db->quoteName('c.id', 'delivery_for_company_id'))
			->select($db->quoteName('d.id', 'delivery_for_department_id'))
			->from($db->qn('#__redshopb_address', 'a'))
			->leftJoin($db->qn('#__redshopb_country', 'co') . ' ON ' . $db->qn('co.id') . ' = ' . $db->qn('a.country_id'))
			->innerJoin(
				$db->qn('#__redshopb_department', 'd') . ' ON ' . $db->qn('a.customer_id') . ' = ' .
				$db->qn('d.id') . ' AND ' . $db->qn('d.deleted') . ' = 0 AND ' . $db->qn('d.state') . ' = 1'
			)
			->innerJoin(
				$db->qn('#__redshopb_company', 'c') . ' ON ' . $db->qn('d.company_id') . ' = ' .
				$db->qn('c.id') . ' AND ' . $db->qn('c.deleted') . ' = 0'
			)
			->where($db->qn('a.customer_type') . ' = ' . $db->q('department'));

		if ($departmentId)
		{
			$queryDepartment->where($db->qn('d.id') . '=' . (int) $departmentId);
		}
		elseif ($companyId)
		{
			$queryDepartment->where($db->qn('c.id') . '=' . (int) $companyId);
		}

		// If customer is Company
		$queryCompany = $db->getQuery(true)
			->select(
				array (
					$db->qn('a.id'),
					$db->qn('a.customer_type'),
					$db->qn('a.customer_id'),
					$db->qn('c.name', 'customer_name'),
					$db->qn('c.customer_number', 'customer_number_id'),
					$db->qn('a.type'),
					$db->qn('a.order'),
					$db->qn('a.name'),
					$db->qn('a.name', 'name1'),
					$db->qn('a.name2'),
					$db->qn('a.country_id'),
					$db->qn('a.address'),
					$db->qn('a.address2'),
					$db->qn('a.zip'),
					$db->qn('a.city'),
					$db->qn('a.email'),
					$db->qn('a.phone'),
					$db->qn('a.checked_out'),
					$db->qn('a.checked_out_time'),
					$db->qn('a.created_by'),
					$db->qn('a.created_date'),
					$db->qn('a.modified_by'),
					$db->qn('a.modified_date'),
					$db->qn('a.code'),
					$db->qn('co.name', 'country'),
					$db->qn('co.alpha2', 'country_code'),
					'concat(' . $db->qn('c.name') . ',' . $db->q(' ') . ',' . $db->qn('c.name2') . ') AS entity',
					$db->q('') . ' AS ' . $db->qn('delivery_for_user_id'),
					'IF (' . $db->qn('a.type') . ' = 3, 1, 0) AS ' . $db->qn('delivery_default')
				)
			)
			->select($db->quoteName('a.address', 'address_line1'))
			->select($db->quoteName('a.address2', 'address_line2'))
			->select($db->quoteName('c.id', 'delivery_for_company_id'))
			->select($db->q('') . ' AS ' . $db->qn('delivery_for_department_id'))
			->from($db->qn('#__redshopb_address', 'a'))
			->leftJoin($db->qn('#__redshopb_country', 'co') . ' ON ' . $db->qn('co.id') . ' = ' . $db->qn('a.country_id'))
			->innerJoin(
				$db->qn('#__redshopb_company', 'c') . ' ON ' . $db->qn('a.customer_id') . ' = ' .
				$db->qn('c.id') . ' AND ' . $db->qn('c.deleted') . ' = 0'
			)
			->where($db->qn('a.customer_type') . ' = ' . $db->q('company'));

		if ($companyId || $departmentId)
		{
			$queryCompany->where($db->qn('c.id') . '=' . (int) $companyId);
		}

		// Own default addresses (user)
		$queryDefaultUser = $db->getQuery(true)
			->select(
				array (
					$db->qn('a.id'),
					$db->q('employee') . ' AS ' . $db->qn('customer_type'),
					$db->qn('a.customer_id'),
					$db->qn('u.name1', 'customer_name'),
					$db->qn('u.id', 'customer_number_id'),
					$db->qn('a.type'),
					$db->qn('a.order'),
					$db->qn('a.name'),
					$db->qn('a.name', 'name1'),
					$db->qn('a.name2'),
					$db->qn('a.country_id'),
					$db->qn('a.address'),
					$db->qn('a.address2'),
					$db->qn('a.zip'),
					$db->qn('a.city'),
					$db->qn('a.email'),
					$db->qn('a.phone'),
					$db->qn('a.checked_out'),
					$db->qn('a.checked_out_time'),
					$db->qn('a.created_by'),
					$db->qn('a.created_date'),
					$db->qn('a.modified_by'),
					$db->qn('a.modified_date'),
					$db->qn('a.code'),
					$db->qn('co.name', 'country'),
					$db->qn('co.alpha2', 'country_code'),
					'concat(' . $db->qn('u.name1') . ',' . $db->q(' ') . ',' . $db->qn('u.name2') . ') AS entity',
					$db->qn('u.id', 'delivery_for_user_id'),
					'IF (' . $db->qn('a.type') . ' = 3, 1, 0) AS ' . $db->qn('delivery_default')
				)
			)
			->select($db->quoteName('a.address', 'address_line1'))
			->select($db->quoteName('a.address2', 'address_line2'))
			->from($db->qn('#__redshopb_address', 'a'))
			->leftJoin($db->qn('#__redshopb_country', 'co') . ' ON ' . $db->qn('co.id') . ' = ' . $db->qn('a.country_id'))
			->innerJoin($db->qn('#__redshopb_user', 'u') . ' ON ' . $db->qn('a.id') . ' = ' . $db->qn('u.address_id'))
			->where($db->qn('a.customer_type') . ' = ' . $db->q(''));

		if ($includeCompanyData)
		{
			$queryDefaultUser->select($db->qn('c.id', 'delivery_for_company_id'))
				->innerJoin($db->qn('#__redshopb_user_multi_company', 'umc') . ' ON umc.user_id = u.id')
				->innerJoin(
					$db->qn('#__redshopb_company', 'c') . ' ON ' . $db->qn('c.id') . ' = umc.company_id ' .
					' AND ' . $db->qn('c.deleted') . ' = 0'
				);
		}

		if ($includeDepartmentData)
		{
			$queryDefaultUser->select($db->qn('d.id', 'delivery_for_department_id'))
				->leftJoin(
					$db->qn('#__redshopb_department', 'd') . ' ON ' . $db->qn('u.department_id') . ' = ' .
					$db->qn('d.id') . ' AND ' . $db->qn('d.deleted') . ' = 0 AND ' . $db->qn('d.state') . ' = 1'
				);
		}

		if ($departmentId && $includeDepartmentData)
		{
			$queryDefaultUser->where($db->qn('d.id') . '=' . (int) $departmentId);
		}
		elseif ($companyId && $includeCompanyData)
		{
			$queryDefaultUser->where($db->qn('c.id') . '=' . (int) $companyId);
		}

		if ($userId)
		{
			$queryDefaultUser->where($db->qn('u.id') . '=' . (int) $userId);
		}

		// Own default addresses (department)
		$queryDefaultDepartment = $db->getQuery(true)
			->select(
				array (
					$db->qn('a.id'),
					$db->q('department') . ' AS ' . $db->qn('customer_type'),
					$db->qn('a.customer_id'),
					$db->qn('d.name', 'customer_name'),
					$db->qn('d.id', 'customer_number_id'),
					$db->qn('a.type'),
					$db->qn('a.order'),
					$db->qn('a.name'),
					$db->qn('a.name', 'name1'),
					$db->qn('a.name2'),
					$db->qn('a.country_id'),
					$db->qn('a.address'),
					$db->qn('a.address2'),
					$db->qn('a.zip'),
					$db->qn('a.city'),
					$db->qn('a.email'),
					$db->qn('a.phone'),
					$db->qn('a.checked_out'),
					$db->qn('a.checked_out_time'),
					$db->qn('a.created_by'),
					$db->qn('a.created_date'),
					$db->qn('a.modified_by'),
					$db->qn('a.modified_date'),
					$db->qn('a.code'),
					$db->qn('co.name', 'country'),
					$db->qn('co.alpha2', 'country_code'),
					'concat(' . $db->qn('d.name') . ',' . $db->q(' ') . ',' . $db->qn('d.name2') . ') AS entity',
					$db->q('') . ' AS ' . $db->qn('delivery_for_user_id'),
					'IF (' . $db->qn('a.type') . ' = 3, 1, 0) AS ' . $db->qn('delivery_default')
				)
			)
			->select($db->quoteName('a.address', 'address_line1'))
			->select($db->quoteName('a.address2', 'address_line2'))
			->select($db->quoteName('c.id', 'delivery_for_company_id'))
			->select($db->quoteName('d.id', 'delivery_for_department_id'))
			->from($db->qn('#__redshopb_address', 'a'))
			->leftJoin($db->qn('#__redshopb_country', 'co') . ' ON ' . $db->qn('co.id') . ' = ' . $db->qn('a.country_id'))
			->innerJoin(
				$db->qn('#__redshopb_department', 'd') . ' ON ' . $db->qn('d.address_id') . ' = ' .
				$db->qn('a.id') . ' AND ' . $db->qn('d.deleted') . ' = 0 AND ' . $db->qn('d.state') . ' = 1'
			)
			->innerJoin(
				$db->qn('#__redshopb_company', 'c') . ' ON ' . $db->qn('d.company_id') . ' = ' .
				$db->qn('c.id') . ' AND ' . $db->qn('c.deleted') . ' = 0'
			)
			->where($db->qn('a.customer_type') . ' = ' . $db->q(''));

		if ($departmentId)
		{
			$queryDefaultDepartment->where($db->qn('d.id') . '=' . (int) $departmentId);
		}
		elseif ($companyId)
		{
			$queryDefaultDepartment->where($db->qn('c.id') . '=' . (int) $companyId);
		}

		// Own default addresses (company)
		$queryDefaultCompany = $db->getQuery(true)
			->select(
				array (
					$db->qn('a.id'),
					$db->q('company') . ' AS ' . $db->qn('customer_type'),
					$db->qn('a.customer_id'),
					$db->qn('c.name', 'customer_name'),
					$db->qn('c.customer_number', 'customer_number_id'),
					$db->qn('a.type'),
					$db->qn('a.order'),
					$db->qn('a.name'),
					$db->qn('a.name', 'name1'),
					$db->qn('a.name2'),
					$db->qn('a.country_id'),
					$db->qn('a.address'),
					$db->qn('a.address2'),
					$db->qn('a.zip'),
					$db->qn('a.city'),
					$db->qn('a.email'),
					$db->qn('a.phone'),
					$db->qn('a.checked_out'),
					$db->qn('a.checked_out_time'),
					$db->qn('a.created_by'),
					$db->qn('a.created_date'),
					$db->qn('a.modified_by'),
					$db->qn('a.modified_date'),
					$db->qn('a.code'),
					$db->qn('co.name', 'country'),
					$db->qn('co.alpha2', 'country_code'),
					'concat(' . $db->qn('c.name') . ',' . $db->q(' ') . ',' . $db->qn('c.name2') . ') AS entity',
					$db->q('') . ' AS ' . $db->qn('delivery_for_user_id'),
					'IF (' . $db->qn('a.type') . ' = 3, 1, 0) AS ' . $db->qn('delivery_default')
				)
			)
			->select($db->quoteName('a.address', 'address_line1'))
			->select($db->quoteName('a.address2', 'address_line2'))
			->select($db->quoteName('c.id', 'delivery_for_company_id'))
			->select($db->q('') . ' AS ' . $db->qn('delivery_for_department_id'))
			->from($db->qn('#__redshopb_address', 'a'))
			->leftJoin(
				$db->qn('#__redshopb_country', 'co') . ' ON ' . $db->qn('co.id') . ' = ' .
				$db->qn('a.country_id')
			)
			->innerJoin(
				$db->qn('#__redshopb_company', 'c') . ' ON ' . $db->qn('c.address_id') . ' = ' .
				$db->qn('a.id') . ' AND ' . $db->qn('c.deleted') . ' = 0'
			)
			->where($db->qn('a.customer_type') . ' = ' . $db->q(''));

		if ($companyId || $departmentId)
		{
			$queryDefaultCompany->where($db->qn('c.id') . '=' . (int) $companyId);
		}

		$this->setQueryRestrictions(
			$queryEmployee, $queryDepartment, $queryCompany, $queryDefaultUser, $queryDefaultDepartment, $queryDefaultCompany
		);

		$query = $db->getQuery(true);
		$query->select(
			array(
				'addresses.*'
			)
		)->group('addresses.id');

		$queryFrom = '';

		if ($userId)
		{
			$queryFrom = '(' . $queryEmployee . ') UNION (' . $queryDefaultUser . ')';
		}
		else
		{
			$queryFrom = '(' . $queryEmployee . ') UNION (' . $queryCompany . ') UNION (' . $queryDepartment . ') UNION (' . $queryDefaultUser
				. ') UNION (' . $queryDefaultDepartment . ') UNION (' . $queryDefaultCompany . ')';
		}

		if ($this->getState('filter.anonymous', false))
		{
			// Anonymous addresses
			$queryAnonymous = $db->getQuery(true)
				->select(
					array (
						$db->qn('a.id'),
						$db->qn('a.customer_type'),
						$db->qn('a.customer_id'),
						'NULL AS ' . $db->qn('customer_name'),
						'NULL AS ' . $db->qn('customer_number_id'),
						$db->qn('a.type'),
						$db->qn('a.order'),
						$db->qn('a.name'),
						$db->qn('a.name', 'name1'),
						$db->qn('a.name2'),
						$db->qn('a.country_id'),
						$db->qn('a.address'),
						$db->qn('a.address2'),
						$db->qn('a.zip'),
						$db->qn('a.city'),
						$db->qn('a.email'),
						$db->qn('a.phone'),
						$db->qn('a.checked_out'),
						$db->qn('a.checked_out_time'),
						$db->qn('a.created_by'),
						$db->qn('a.created_date'),
						$db->qn('a.modified_by'),
						$db->qn('a.modified_date'),
						$db->qn('a.code'),
						$db->qn('co.name', 'country'),
						$db->qn('co.alpha2', 'country_code'),
						'NULL AS entity',
						$db->q('') . ' AS ' . $db->qn('delivery_for_user_id'),
					'IF (' . $db->qn('a.type') . ' = 3, 1, 0) AS ' . $db->qn('delivery_default')
					)
				)
				->select($db->quoteName('a.address', 'address_line1'))
				->select($db->quoteName('a.address2', 'address_line2'))
				->select($db->q('') . ' AS ' . $db->qn('delivery_for_company_id'))
				->select($db->q('') . ' AS ' . $db->qn('delivery_for_department_id'))
				->from($db->qn('#__redshopb_address', 'a'))
				->leftJoin(
					$db->qn('#__redshopb_country', 'co') . ' ON ' . $db->qn('co.id') . ' = ' .
					$db->qn('a.country_id')
				)
				->where('(' . $db->qn('a.customer_type') . ' IS NULL OR ' . $db->qn('a.customer_type') . ' = ' . $db->q('') . ')')
				->where($db->qn('a.customer_id') . ' = 0');

			$queryFrom .= ' UNION (' . $queryAnonymous . ')';
		}

		$query->from('(' . $queryFrom . ') AS ' . $db->qn('addresses'));

		$customerType = $this->getState('filter.customer_type', '');

		if ($customerType != '')
		{
			$query->where($db->qn('addresses.customer_type') . ' = ' . $db->q($customerType));
		}

		$type = $this->getState('filter.type', 0);

		if ($type)
		{
			$query->where($db->qn('addresses.type') . ' = ' . (int) $type);
		}

		$filterTypes = $this->getState('filter.types', null);

		if (!empty($filterTypes) && is_array($filterTypes) && count($filterTypes))
		{
			$query->where($db->qn('addresses.type') . ' IN (' . implode(',', $filterTypes) . ')');
		}

		$id = $this->getState('filter.id');

		if (is_numeric($id) && $id > 0)
		{
			$query->where($db->qn('addresses.id') . ' = ' . (int) $id);
		}

		$zip = $this->getState('filter.zip', null);

		if (!empty($zip))
		{
			$query->where($db->qn('addresses.zip') . ' = ' . $db->quote($zip));
		}

		$city = $this->getState('filter.city', null);

		if (!empty($city))
		{
			$query->where($db->qn('addresses.city') . ' = ' . $db->quote($city));
		}

		$countryId = $this->getState('filter.country_id');

		if (is_numeric($countryId))
		{
			$query->where($db->qn('addresses.country_id') . ' = ' . (int) $countryId);
		}

		$countryCode = $this->getState('filter.country_code', null);

		if (!empty($countryCode))
		{
			$query->where($db->qn('addresses.country_code') . ' = ' . $db->q($countryCode));
		}

		$search = $this->getState('filter.search_addresses', $this->getState('filter.search'));

		if ($search != '')
		{
			$query->where(
				'(' .
				$db->qn('addresses.name') . ' LIKE ' . $db->q('%' . $search . '%') . ' OR ' .
				$db->qn('addresses.name2') . ' LIKE ' . $db->q('%' . $search . '%') . ' OR ' .
				$db->qn('addresses.address') . ' LIKE ' . $db->q('%' . $search . '%') . ' OR ' .
				$db->qn('addresses.address2') . ' LIKE ' . $db->q('%' . $search . '%') . ' OR ' .
				$db->qn('addresses.zip') . ' LIKE ' . $db->q('%' . $search . '%') . ' OR ' .
				$db->qn('addresses.city') . ' LIKE ' . $db->q('%' . $search . '%') . ' OR ' .
				$db->qn('addresses.country') . ' LIKE ' . $db->q('%' . $search . '%') . ' OR ' .
				$db->qn('addresses.country_code') . ' LIKE ' . $db->q('%' . $search . '%') . ' OR ' .
				$db->qn('addresses.entity') . ' LIKE ' . $db->q('%' . $search . '%') .
				')'
			);
		}

		// Ordering
		$orderList     = $this->getState('list.ordering');
		$directionList = $this->getState('list.direction');
		$order         = !empty($orderList) ? $orderList : 'addresses.type';
		$direction     = !empty($directionList) ? $directionList : 'DESC';
		$query->order($db->escape($order) . ' ' . $db->escape($direction));

		// Adds related web service data when requested
		$this->getListQueryWS($query);

		return $query;
	}

	/**
	 * Method to get an array of data items prepared for the web service - including the external keys from sync
	 *
	 * @return  mixed  An array of data items on success, false on failure.
	 */
	public function getItemsWS()
	{
		$this->getState();
		$this->setState('filter.types', array(1, 2, 3));
		$this->setState('filter.anonymous', true);

		return parent::getItemsWS();
	}

	/**
	 * Method to change the filter form name
	 *
	 * @param   string  $filterFormName  Name of the filter form to set for the model
	 *
	 * @return  void
	 */
	public function setFilterForm($filterFormName)
	{
		$this->filterFormName = $filterFormName;
	}

	/**
	 * Get the columns for the csv file.
	 *
	 * @return  array  An associative array of column names as key and the title as value.
	 */
	public function getCsvColumns()
	{
		return array(
			'CRUD' => Text::_('COM_REDSHOPB_CRUD'),
			'name' => Text::_('COM_REDSHOPB_NAME'),
			'address' => Text::_('COM_REDSHOPB_ADDRESS_LABEL'),
			'address2' => Text::_('COM_REDSHOPB_ADDRESS2_LABEL'),
			'zip' => Text::_('COM_REDSHOPB_ZIP_LABEL'),
			'city' => Text::_('COM_REDSHOPB_CITY_LABEL'),
			'country' => Text::_('COM_REDSHOPB_COUNTRY_LABEL'),
			'type' => Text::_('COM_REDSHOPB_TYPE'),
			'customer_type' => Text::_('COM_REDSHOPB_ADDRESS_CUSTOMER_TYPE'),
			'customer_name' => Text::_('COM_REDSHOPB_ADDRESS_CUSTOMER_NAME'),
			'customer_number_id' => Text::_('COM_REDSHOPB_EXPORT_CUSTOMER_NUMBER_ID'),
			'id' => Text::_('COM_REDSHOPB_EXPORT_ADDRESS_SYSTEM_ID'),
		);
	}

	/**
	 * Method to get an array of data items.
	 *
	 * @return  mixed  An array of data items on success, false on failure.
	 *
	 * @since   12.2
	 */
	public function getItems()
	{
		if ($this->getState('streamOutput', '') == 'csv')
		{
			return $this->getItemsCsv();
		}
		else
		{
			return parent::getItems();
		}
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
		$db	= $this->getDbo();

		// Get a storage key.
		$store = $this->getStoreId();

		// Try to load the data from internal storage.
		if (isset($this->cache[$store]))
		{
			return $this->cache[$store];
		}

		// Load the list items.
		$query = $this->getListQuery();

		$query->select($db->q('UPDATE') . ' AS CRUD');

		if (null !== $data)
		{
			$data = implode(',', $db->q($data));
			$query->where("{$db->qn('addresses.id')} IN ({$data})");
		}

		try
		{
			$items = $this->_getList($query, $this->getStart(), $this->getState('list.limit'));
		}
		catch (RuntimeException $e)
		{
			$this->setError($e->getMessage());

			return false;
		}

		// Add the items to the internal cache.
		$this->cache[$store] = $items;

		return $this->cache[$store];
	}

	/**
	 * Import Addresses
	 *
	 * @param   array  $importData  Data received from CSV file
	 *
	 * @return  mixed
	 */
	public function import($importData)
	{
		$result  = array();
		$columns = $this->getCsvColumns();

		if (is_array($importData))
		{
			$allowedIds = $this->getAllowedAddresses();

			foreach ($importData as $rowNumber => $row)
			{
				if (!is_array($row))
				{
					$result['error'][] = Text::sprintf(
						'COM_REDSHOPB_ADDRESSES_UNSUCCESSFULLY_IMPORTED', Text::sprintf('COM_REDSHOPB_IMPORT_ERROR_COLUMNS_MISSING', $rowNumber + 2)
					);
					continue;
				}

				$data = array();

				// Prepare data with same columns
				foreach ($columns as $columnKey => $columnValue)
				{
					$data[$columnKey] = $row[strtolower($columnValue)];
				}

				$data['CRUD'] = !empty($data['CRUD']) ? strtoupper($data['CRUD']) : '';

				// Check if address can be modified
				if (in_array($data['CRUD'], array('UPDATE', 'DELETE')))
				{
					if (!in_array($data['id'], $allowedIds))
					{
						$result['error'][] = Text::_('COM_REDSHOPB_ADDRESS_ERROR_PERMISSIONS')
							. RedshopbHelperImport::getErrorRowOutput($columns, $row, $rowNumber);
						continue;
					}
				}

				$missingRequiredField = array();

				if (empty($data['customer_type']))
				{
					$missingRequiredField['customer_type'] = $columns['customer_type'];
				}

				if (empty($data['customer_number_id']))
				{
					$missingRequiredField['customer_number_id'] = $columns['customer_number_id'];
				}

				if (!empty($missingRequiredField))
				{
					$result['error'][] = Text::sprintf(
						'COM_REDSHOPB_ADDRESSES_IMPORT_ERROR_MISSING_REQUIRED_FIELDS', implode(', ', $missingRequiredField)
					)
						. RedshopbHelperImport::getErrorRowOutput($columns, $row, $rowNumber);
					continue;
				}

				if ($data['customer_type'] == 'employee')
				{
					if (!empty($data['customer_number_id']))
					{
						$user                = RedshopbHelperUser::getUser((int) $data['customer_number_id']);
						$data['customer_id'] = $user->id;
					}

					if (empty($data['customer_id']))
					{
						$result['error'][] = Text::_('COM_REDSHOPB_ADDRESSES_UNSUCCESSFULLY_IMPORTED_EMPLOYEE_NUMBER')
							. RedshopbHelperImport::getErrorRowOutput($columns, $row, $rowNumber);
						continue;
					}
				}
				elseif ($data['customer_type'] == 'department')
				{
					if (!empty($data['customer_number_id']))
					{
						$data['customer_id'] = RedshopbHelperDepartment::getDepartmentById((int) $data['customer_number_id']);
					}

					if (empty($data['customer_id']))
					{
						$result['error'][] = Text::_('COM_REDSHOPB_ADDRESSES_UNSUCCESSFULLY_IMPORTED_DEPARTMENT_NUMBER')
							. RedshopbHelperImport::getErrorRowOutput($columns, $row, $rowNumber);
						continue;
					}
				}
				elseif ($data['customer_type'] == 'company')
				{
					$customer = null;

					if (!empty($data['customer_number_id']))
					{
						$customer = RedshopbHelperCompany::getCompanyByCustomerNumber($data['customer_number_id']);
					}

					if ($customer)
					{
						$data['customer_id'] = $customer->id;
					}

					if (empty($data['customer_id']))
					{
						$result['error'][] = Text::_('COM_REDSHOPB_ADDRESSES_UNSUCCESSFULLY_IMPORTED_COMPANY_NUMBER')
							. RedshopbHelperImport::getErrorRowOutput($columns, $row, $rowNumber);
						continue;
					}
				}

				if (!empty($data['country']))
				{
					$data['country_id'] = RedshopbEntityCountry::loadFromName($data['country'])->id;
				}

				$data['country_id'] = !empty($data['country_id']) ? $data['country_id'] : '';
				$data['country']    = !empty($data['country_id']) ? $data['country'] : '';

				$missingRequiredField = array();
				$requiredFields       = array('country', 'address', 'zip', 'city');

				foreach ($requiredFields as $requiredField)
				{
					if (empty($data[$requiredField]))
					{
						$missingRequiredField[$requiredField] = $columns[$requiredField];
					}
				}

				if (!empty($missingRequiredField))
				{
					$result['error'][] = Text::sprintf(
						'COM_REDSHOPB_ADDRESSES_IMPORT_ERROR_MISSING_REQUIRED_FIELDS', implode(', ', $missingRequiredField)
					) . RedshopbHelperImport::getErrorRowOutput($columns, $row, $rowNumber);
					continue;
				}

				if ($data['CRUD'] == 'CREATE')
				{
					$data['id'] = 0;
				}

				/** @var RedshopbModelAddress $model */
				$model = RedshopbModelAdmin::getInstance('Address', 'RedshopbModel', array('ignore_request' => true));

				if ($data['CRUD'] == 'UPDATE' || $data['CRUD'] == 'CREATE')
				{
					if (!$model->save($data))
					{
						$result['error'][] = Text::sprintf('COM_REDSHOPB_ADDRESSES_UNSUCCESSFULLY_IMPORTED', $model->getError())
							. RedshopbHelperImport::getErrorRowOutput($columns, $row, $rowNumber);
					}
					else
					{
						$result['success'][$data['CRUD']][] = 1;
					}
				}
				elseif ($data['CRUD'] == 'DELETE')
				{
					$id = (int) $data['id'];

					if (!$model->delete($id))
					{
						$result['error'][] = Text::sprintf('COM_REDSHOPB_ADDRESSES_UNSUCCESSFULLY_DELETED', $model->getError())
							. RedshopbHelperImport::getErrorRowOutput($columns, $row, $rowNumber);
					}
					else
					{
						$result['success'][$data['CRUD']][] = 1;
					}
				}
				else
				{
					$result['error'][] = Text::sprintf(
						'COM_REDSHOPB_ADDRESSES_UNSUCCESSFULLY_IMPORTED',
						Text::_('COM_REDSHOPB_MISSING_VALUES') . ': ' . Text::_('COM_REDSHOPB_CRUD')
					)
						. RedshopbHelperImport::getErrorRowOutput($columns, $row, $rowNumber);
				}
			}
		}

		return $result;
	}

	/**
	 * Get list of ids of allowed addresses
	 *
	 * @return  array
	 */
	public function getAllowedAddresses()
	{
		$db	= $this->getDbo();
		/** @var RedshopbModelAddresses $addressesModel */
		$addressesModel = RedshopbModelAdmin::getInstance('Addresses', 'RedshopbModel', array('ignore_request' => true));
		/** @var JDatabaseQuery $itemsQuery */
		$itemsQuery = $addressesModel->getListQuery();
		$itemsQuery->clear('select');
		$itemsQuery->select('addresses.id');

		$db->setQuery($itemsQuery);

		$items = $db->loadColumn();

		return !empty($items) ? $items : array();
	}
}
