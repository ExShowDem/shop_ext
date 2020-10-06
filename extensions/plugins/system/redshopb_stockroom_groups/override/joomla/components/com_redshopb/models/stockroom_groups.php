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
 * Stockroom Groups Model
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 * @since       1.0
 */
class RedshopbModelStockroom_Groups extends RedshopbModelList
{
	/**
	 * Name of the filter form to load
	 *
	 * @var  string
	 */
	protected $filterFormName = 'filter_stockroom_groups';

	/**
	 * Limitstart field used by the pagination
	 *
	 * @var  string
	 */
	protected $limitField = 'stockroom_groups_limit';

	/**
	 * Main table query prefix
	 *
	 * @var  array
	 */
	protected $mainTablePrefix = 'sg';

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
				'name', 'sg.name',
				'ordering', 'sg.ordering',
				'id', 'sg.id',
				'state', 'sg.state'
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
		parent::populateState('sg.ordering', 'asc');
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return  JDatabaseQuery
	 */
	protected function getListQuery()
	{
		$db	= $this->getDbo();

		// Stockroom names
		$subQuery = $db->getQuery(true)
			->select('GROUP_CONCAT(s.name SEPARATOR ' . $db->q(', ') . ')')
			->from($db->qn('#__redshopb_stockroom', 's'))
			->where($db->qn('sgsx.stockroom_group_id') . ' = sg.id')
			->leftJoin($db->qn('#__redshopb_stockroom_group_stockroom_xref', 'sgsx') . ' ON sgsx.stockroom_id = s.id');

		$query = $db->getQuery(true)
			->select('sg.*')
			->from($db->qn('#__redshopb_stockroom_group', 'sg'))
			->select('(' . $subQuery . ') AS stockroom_names');

		$id = $this->getState('filter.id');

		if (is_numeric($id) && $id > 0)
		{
			$query->where('sg.id = ' . (int) $id);
		}

		// Filter by state.
		$state = $this->getState('filter.state');

		if ($state == '0' || $state == 'false')
		{
			$query->where($db->qn('sg.state') . ' = 0');
		}
		elseif ($state == '1' || $state == 'true')
		{
			$query->where($db->qn('sg.state') . ' = 1');
		}

		// Filter search
		$search = $this->getState('filter.search_stockroom_groups', $this->getState('filter.search'));

		if (!empty($search))
		{
			$search = $db->quote('%' . $db->escape($search, true) . '%');

			$searchFields = array(
				$db->qn('sg.name') . ' LIKE ' . $search,
				$db->qn('sg.description') . ' LIKE ' . $search
			);

			$query->where('(' . implode(' OR ', $searchFields) . ')');
		}

		// Ordering
		$orderList     = $this->getState('list.ordering');
		$directionList = $this->getState('list.direction');

		$order     = !empty($orderList) ? $orderList : 'sg.ordering';
		$direction = !empty($directionList) ? $directionList : 'ASC';
		$query->order($db->escape($order) . ' ' . $db->escape($direction));

		// Adds related web service data when requested
		$this->getListQueryWS($query);

		return $query;
	}

	/**
	 * Method to get a store id based on model configuration state.
	 *
	 * This is necessary because the model is used by the component and
	 * different modules that might need different sets of data or different
	 * ordering requirements.
	 *
	 * @param   string  $id  A prefix for the store id.
	 *
	 * @return	string  A store id.
	 */
	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id	.= ':' . $this->getState('filter.id');
		$id	.= ':' . $this->getState('filter.search');

		return parent::getStoreId($id);
	}
}
