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
/**
 * Users Model
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 * @since       1.0
 */
class RedshopbModelSalesPersons extends RedshopbModelList
{
	/**
	 * Name of the filter form to load
	 *
	 * @var  string
	 */
	protected $filterFormName = 'filter_salespersons';

	/**
	 * Limitstart field used by the pagination
	 *
	 * @var  string
	 */
	protected $limitField = 'salesperson_limit';

	/**
	 * Limitstart field used by the pagination
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
				'id', 'u.id',
				'name', 'u.name',
				'username', 'j.username',
				'email', 'j.email',
				'company', 'u.company',
				'block', 'u.block',
				'salesperson_block',
				'user_role_type',
				'employee_number', 'u.employee_number'
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
		parent::populateState('u.name', 'asc');
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return  JDatabaseQuery
	 */
	protected function getListQuery()
	{
		$db	= $this->getDbo();

		// Filter by company
		$company = (int) $this->getState('filter.company', 0);

		$query = $db->getQuery(true)
			->select(
				array(
					$db->qn('u') . '.*',
					$db->qn('c.name', 'company'),
					$db->qn('j.name', 'name'),
					$db->qn('j.username', 'username'),
					'IF (u.use_company_email = 0, j.email, ' . $db->q('') . ') AS email',
					$db->qn('j.block', 'block'),
				)
			)
			->from($db->qn('#__redshopb_user', 'u'))
			->innerJoin($db->qn('#__redshopb_company_sales_person_xref', 'cspx') . ' ON ' . $db->qn('cspx.user_id') . ' = ' . $db->qn('u.id'))
			->innerJoin(
				$db->qn('#__redshopb_company', 'c') . ' ON ' . $db->qn('cspx.company_id') . ' = ' . $db->qn('c.id')
				. ' AND ' . $db->qn('c.deleted') . ' = 0'
			)
			->innerJoin($db->qn('#__users', 'j') . ' ON ' . $db->qn('u.joomla_user_id') . ' = ' . $db->qn('j.id'));

		// Check for available companies and departments for this user if not a system admin of the app
		if (!RedshopbHelperACL::isSuperAdmin())
		{
			$user = Factory::getUser();

			// Companies where user can see its users
			$availableCompanies = RedshopbHelperACL::listAvailableCompaniesByPermission($user->id, 'redshopb.user.view');

			if ($company == 0)
			{
				$query->where($db->qn('c.id') . ' IN (' . $availableCompanies . ')');
			}
		}

		// Filter by block status.
		$block = $this->getState('filter.salesperson_block');

		if (is_numeric($block))
		{
			$query->where($db->qn('j.block') . ' = ' . (int) $block);
		}

		// Filter search
		$search = $this->getState('filter.search_salespersons');

		if (!empty($search))
		{
			$search = $db->quote('%' . $db->escape($search, true) . '%');
			$query->where(
				'((j.name LIKE ' . $search . ')' .
				'OR (u.name1 LIKE ' . $search . ')' .
				'OR (u.name2 LIKE ' . $search . ')' .
				'OR (u.employee_number LIKE ' . $search . ')' .
				'OR (j.username LIKE ' . $search . ')' .
				'OR (u.use_company_email = 0 AND j.email LIKE ' . $search . '))'
			);
		}

		if ($company)
		{
			$query->where($db->qn('c.id') . ' = ' . $company);
		}

		// Ordering
		$orderList     = $this->getState('list.ordering');
		$directionList = $this->getState('list.direction');

		if ($orderList == 'u.company')
		{
			$orderList = 'c.name';
		}
		elseif ($orderList == 'u.block')
		{
			$orderList = 'j.block';
		}
		elseif ($orderList == 'u.name')
		{
			$orderList = 'j.name';
		}
		elseif ($orderList == 'u.role')
		{
			$orderList = 'rt.name';
		}

		$order     = !empty($orderList) ? $db->qn($orderList) : $db->qn('j.name');
		$direction = !empty($directionList) ? $directionList : 'ASC';
		$query->order($db->escape($order) . ' ' . $db->escape($direction));

		return $query;
	}
}
