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
 * Table Locks Model
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 * @since       1.0
 */
class RedshopbModelTable_Locks extends RedshopbModelList
{
	/**
	 * Name of the filter form to load
	 *
	 * @var  string
	 */
	protected $filterFormName = 'filter_table_locks';

	/**
	 * Limitstart field used by the pagination
	 *
	 * @var  string
	 */
	protected $limitField = 'table_lock_limit';

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
				'id', 't.id',
				'table_name', 't.table_name',
				'table_id', 't.table_id',
				'column_name', 't.column_name',
				'locked_date', 't.locked_date',
				'locked_by', 't.locked_by',
				'locked_method', 't.locked_method'
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
		parent::populateState('t.table_name', 'asc');
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return  JDatabaseQuery
	 */
	public function getListQuery()
	{
		$db	= $this->getDbo();

		$query = $db->getQuery(true)
			->select('t.*')
			->from($db->qn('#__redshopb_table_lock', 't'))
			->select('u1.name as locked_by_name')
			->leftJoin($db->qn('#__users', 'u1') . ' ON u1.id = t.locked_by');

		// Filter search
		$search = $this->getState('filter.search_table_locks', $this->getState('filter.search'));

		if (!empty($search))
		{
			$search = $db->quote('%' . $db->escape($search, true) . '%');
			$query->where('(t.table_name LIKE ' . $search . ' OR t.column_name LIKE ' . $search . ')');
		}

		$filter = $this->getState('filter.table_name');

		if ($filter)
		{
			$query->where($db->qn('t.table_name') . ' = ' . $db->q($filter));
		}

		$filter = $this->getState('filter.column_name');

		if ($filter)
		{
			$query->where($db->qn('t.column_name') . ' = ' . $db->q($filter));
		}

		$filter = $this->getState('filter.locked_by');

		if ($filter)
		{
			$query->where($db->qn('t.locked_by') . ' = ' . (int) $filter);
		}

		$filter = $this->getState('filter.locked_method');

		if ($filter)
		{
			$query->where($db->qn('t.locked_method') . ' = ' . $db->q($filter));
		}

		// Ordering
		$orderList     = $this->getState('list.ordering');
		$directionList = $this->getState('list.direction');

		$order     = !empty($orderList) ? $orderList : 't.table_name';
		$direction = !empty($directionList) ? $directionList : 'ASC';
		$query->order($db->escape($order) . ' ' . $db->escape($direction));

		// Adds related web service data when requested
		$this->getListQueryWS($query);

		return $query;
	}
}
