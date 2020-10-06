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
 * Currency Model
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 * @since       1.6
 */
class RedshopbModelTypes extends RedshopbModelList
{
	/**
	 * Name of the filter form to load
	 *
	 * @var  string
	 */
	protected $filterFormName = 'filter_types';

	/**
	 * Limit field used by the pagination
	 *
	 * @var  string
	 */
	protected $limitField = 'type_limit';

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
				'scope',
				// Ordering
				't.id',
				't.name',
				't.alias',
				't.scope',
				'code'
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
		parent::populateState('t.name', 'asc');
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return  JDatabaseQuery
	 */
	public function getListQuery()
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		$filterScope = $this->getState('filter.scope', null);

		$query->select(
			array(
					't.*',
					$db->qn('t.alias', 'code'),
					'CASE t.value_type WHEN ' . $db->q('field_value') . ' THEN 1 ELSE 0 END AS ' . $db->qn('values')
				)
		)
			->from($db->qn('#__redshopb_type', 't'));

		// Filter search
		$search = $this->getState('filter.search_types', $this->getState('filter.search'));

		if (!empty($search))
		{
			$search = $db->quote('%' . $db->escape($search, true) . '%');

			$searchFields = array(
				't.name LIKE ' . $search,
			);

			$query->where('(' . implode(' OR ', $searchFields) . ')');
		}

		// Filter by multiple
		$multipleFilter = $this->getState('filter.multiple', null);

		if ($multipleFilter == '0' || $multipleFilter == 'false')
		{
			$query->where($db->qn('t.multiple') . ' = 0');
		}
		elseif ($multipleFilter == '1' || $multipleFilter == 'true')
		{
			$query->where($db->qn('t.multiple') . ' = 1');
		}

		// Ordering
		$orderList     = $this->getState('list.ordering');
		$directionList = $this->getState('list.direction');
		$order         = !empty($orderList) ? $orderList : 't.name';
		$direction     = !empty($directionList) ? $directionList : 'ASC';
		$query->order($db->escape($order) . ' ' . $db->escape($direction));

		return $query;
	}
}
