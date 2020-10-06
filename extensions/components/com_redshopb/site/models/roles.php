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
 * Roles Model
 *
 * @since  2.0
 */
class RedshopbModelRoles extends RedshopbModelList
{
	/**
	 * Name of the filter form to load
	 *
	 * @var  string
	 */
	protected $filterFormName = null;

	/**
	 * Limitstart field used by the pagination
	 *
	 * @var  string
	 */
	protected $limitField = 'role_limit';

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
	protected $mainTablePrefix = 'r';

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
				'r.id', 'rt.name', 'rt.type', 'rt.company_role',
				'type',
				'allow_access'
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
		parent::populateState('rt.name', 'asc');
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return  JDatabaseQuery
	 */
	protected function getListQuery()
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		$query->select('r.*')
			->select(
				array(
					$db->qn('rt.id', 'role_type_id'),
					$db->qn('rt.name'),
					$db->qn('rt.company_role'),
					$db->qn('rt.allow_access'),
					$db->qn('rt.limited'),
					$db->qn('rt.allowed_rules'),
					$db->qn('rt.allowed_rules_main_company'),
					$db->qn('rt.allowed_rules_customers'),
					$db->qn('rt.allowed_rules_company'),
					$db->qn('rt.allowed_rules_own_company'),
					$db->qn('rt.allowed_rules_department'),
					$db->qn('rt.type')
				)
			)
			->from($db->qn('#__redshopb_role', 'r'))
			->innerJoin($db->qn('#__redshopb_role_type', 'rt') . ' ON rt.id = r.role_type_id AND rt.hidden = 0');

		$search = $this->getState('filter.search');

		if (!empty($search))
		{
			$search = $db->quote('%' . $db->escape($search, true) . '%');
			$query->where('(rt.name LIKE ' . $search . ')');
		}

		// Filter: return only roles of specific companies
		$companies = RedshopbHelperDatabase::filterInteger($this->getState('filter.company'));

		if ($companies)
		{
			$query->innerJoin('#__redshopb_company as c ON c.id = r.company_id');

			if (count($companies) == 1)
			{
				$query->where('c.id = ' . $companies[0]);
			}
			else
			{
				$query->where('c.id IN (' . implode(',', $companies) . ')');
			}
		}

		// Filter: return only roles of null company
		$nullCompany = $this->getState('filter.null_company');

		if ($nullCompany)
		{
			$query->innerJoin('#__redshopb_company as c ON c.id = r.company_id')
				->where('c.id IS NULL');
		}

		// Allow access filter
		$allowAccessfilter = $this->getState('filter.allow_access', null);

		if (!is_null($allowAccessfilter))
		{
			$query->where($db->qn('rt.allow_access') . ' = ' . (int) $allowAccessfilter);
		}

		// Type filter
		$typeFilter = $this->getState('filter.type', '');

		if (!empty($typeFilter))
		{
			$query->where($db->qn('rt.type') . ' = ' . $db->q($typeFilter));
		}

		// Ordering
		$orderList     = $this->getState('list.ordering');
		$directionList = $this->getState('list.direction');

		$order     = !empty($orderList) ? $orderList : 'rt.name';
		$direction = !empty($directionList) ? $directionList : 'ASC';

		$query->order($db->escape($order) . ' ' . $db->escape($direction));

		return $query;
	}
}
