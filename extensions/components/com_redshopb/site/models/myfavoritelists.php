<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

/**
 * My favorite lists Model
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 * @since       1.0
 */
class RedshopbModelMyfavoritelists extends RedshopbModelList
{
	/**
	 * Name of the filter form to load
	 *
	 * @var  string
	 */
	protected $filterFormName = 'filter_myfavoritelists';

	/**
	 * Limit field used by the pagination
	 *
	 * @var  string
	 */
	protected $limitField = 'myfavoritelist_limit';

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
				'id', 'fl.id',
				'name', 'fl.name',
				'alias', 'fl.alias',
				'company_id', 'fl.company_id',
				'department_id', 'fl.department_id',
				'user_id', 'fl.user_id',
				'filter_user_id',
				'username', 'usr.name1',
				'created_date', 'fl.created_date',
				'visible_others', 'fl.visible_others',
				'product_find'
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
		$ordering  = is_null($ordering) ? 'fl.name' : $ordering;
		$direction = is_null($direction) ? 'ASC' : $direction;

		parent::populateState($ordering, $direction);
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return  JDatabaseQuery
	 */
	public function getListQuery()
	{
		$db = $this->getDbo();

		$query = $db->getQuery(true)
			->select(
				array(
					'fl.*',
					$db->qn('comp.name', 'company_name'),
					$db->qn('dep.name', 'department_name'),
					$db->qn('usr.name1', 'username'),
					'DATE_FORMAT(' . $db->qn('fl.created_date') . ', ' . $db->q('%d/%m/%Y') . ') AS ' . $db->qn('created_date_formatted')
				)
			)
			->from($db->qn('#__redshopb_favoritelist', 'fl'))
			->leftJoin($db->qn('#__redshopb_company', 'comp') . ' ON ' . $db->qn('comp.id') . ' = ' . $db->qn('fl.company_id'))
			->leftJoin($db->qn('#__redshopb_department', 'dep') . ' ON ' . $db->qn('dep.id') . ' = ' . $db->qn('fl.department_id'))
			->leftJoin($db->qn('#__redshopb_user', 'usr') . ' ON ' . $db->qn('usr.id') . ' = ' . $db->qn('fl.user_id'));

		// Filter by user id
		$userId = $this->getState('filter.user_id', 0);

		if ($userId)
		{
			$query->where($db->qn('fl.user_id') . ' = ' . $userId);
		}

		// Filter by company id
		$filterCompanyId = $this->getState('filter.company_id', 0);

		if ($filterCompanyId)
		{
			$query->where($db->qn('fl.company_id') . ' = ' . $filterCompanyId);
		}

		// Filter by department ids
		$filterDepartmentId = $this->getState('filter.department_id', 0);

		if ($filterDepartmentId)
		{
			$query->where($db->qn('fl.department_id') . ' = ' . $filterDepartmentId);
		}

		$filterShared = $this->getState('filter.showshared', 0);

		// Filter search
		$search = $this->getFilterSearch($filterShared);

		if (!empty($search))
		{
			$query->where($db->qn('fl.name') . ' LIKE ' . $db->q('%' . $db->escape($search, true) . '%'));
		}

		$productId = $this->getState('filter.product_id', 0);

		if (!empty($productId))
		{
			$favoriteListProducts = $db->getQuery(true)
				->select('favoritelist_id')
				->from('#__redshopb_favoritelist_product_xref')
				->where($db->qn('product_id') . ' = ' . $db->q($productId));

			$query->where('fl.id IN(' . $favoriteListProducts . ')');
		}

		// Super Admin can see everything
		if (RedshopbHelperUser::isRoot())
		{
			return $query;
		}

		$user = RedshopbHelperUser::getUser();

		if (!$user)
		{
			// No user means no sharing
			$query->where('0 = 1');

			return $query;
		}

		$availableCompanies = RedshopbHelperACL::listAvailableCompanies(
			$user->joomla_user_id, 'comma', $user->company, '', 'redshopb.company.view', '', true
		);

		$query->where($db->qn('fl.company_id') . ' IN (' . $availableCompanies . ', ' . (int) $user->company . ')');

		if (!empty($user->department))
		{
			$availableDepartments = RedshopbHelperACL::listAvailableDepartments(
				$user->joomla_user_id,
				'comma',
				$user->company
			);

			$query->where($db->qn('fl.department_id') . ' IN (' . $availableDepartments . ', ' . (int) $user->department . ')');
		}

		// Filter by visible others
		$filterVisibleOthers = $this->getState('filter.visible_others', 'NONE');

		if ($filterVisibleOthers != 'NONE')
		{
			$query->where($db->qn('fl.visible_others') . ' = ' . (int) $filterVisibleOthers);
		}

		$roleConditions = $this->getUserRoleConditions($user, $filterShared);

		if (!empty($roleConditions))
		{
			$query->where($roleConditions);
		}

		if ($filterShared == 1)
		{
			// Make sure we limit it to only shared lists
			$query->where($db->qn('fl.visible_others') . ' = 1');
			$query->where($db->qn('fl.user_id') . ' != ' . (int) $user->id);

			if (!empty($user->company))
			{
				$query->where($db->qn('fl.company_id') . ' = ' . (int) $user->company);
			}

			if (!empty($user->department))
			{
				$query->where(
					'(' . $db->qn('fl.department_id') . ' = ' . (int) $user->department . ' OR ' . $db->qn('fl.department_id') . ' IS NULL)'
				);
			}
		}

		// Ordering
		$orderList     = $this->getState('list.ordering');
		$directionList = $this->getState('list.direction');

		$order     = !empty($orderList) ? $db->qn($orderList) : $db->qn('fl.name');
		$direction = !empty($directionList) ? $directionList : 'ASC';
		$query->order($db->escape($order) . ' ' . $db->escape($direction));

		return $query;
	}

	/**
	 * Method to get the filter search from the state
	 *
	 * @param   int  $filterShared  Toggle for shared lists
	 *
	 * @return object
	 */
	protected function getFilterSearch($filterShared)
	{
		if ($filterShared == 1)
		{
			return $this->getState('filter.search_sharedfavoritelists', '');
		}

		return $this->getState('filter.search_myfavoritelists', '');
	}

	/**
	 * Method to set special conditions for employee types
	 * This allows child classes to use implement their own rule
	 *
	 * @param   object  $user          current user
	 * @param   int     $filterShared  Toggle for shared lists
	 *
	 * @return string
	 */
	protected function getUserRoleConditions($user, $filterShared)
	{
		if (RedshopbHelperUser::isEmployee($user->id, $user->company) && $filterShared == 0)
		{
			$db = $this->getDbo();

			return $db->qn('fl.user_id') . ' = ' . (int) $user->id;
		}

		return '';
	}

	/**
	 * Method to get an array of data items.
	 * Overridden to always return an array, even when no records exist.
	 *
	 * @return  array An array of data items.
	 */
	public function getItems()
	{
		$items = parent::getItems();

		return !$items ? array() : $items;
	}
}
