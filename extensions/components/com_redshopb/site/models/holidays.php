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
class RedshopbModelHolidays extends RedshopbModelList
{
	/**
	 * Name of the filter form to load
	 *
	 * @var  string
	 */
	protected $filterFormName = 'filter_holidays';

	/**
	 * Limit field used by the pagination
	 *
	 * @var  string
	 */
	protected $limitField = 'holiday_limit';

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
				// Ordering
				'h.id',
				'h.day',
				'h.month',
				'h.year',
				'h.country',
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
		parent::populateState('h.month', 'asc');
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return  JDatabaseQuery
	 */
	public function getListQuery()
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(true)
			->select(
				array(
					'h.*'
				)
			)
			->from($db->qn('#__redshopb_holiday', 'h'));

		$query->select('c.name AS country, c.company_id')
			->join('LEFT', '#__redshopb_country AS c ON c.id=h.country_id');

		// Filter search
		$search = $this->getState('filter.search_holidays', $this->getState('filter.search'));

		if (!empty($search))
		{
			$search = $db->quote('%' . $db->escape($search, true) . '%');

			$searchFields = array(
				$db->qn('h.name') . ' LIKE ' . $search
			);

			$query->where('(' . implode(' OR ', $searchFields) . ')');
		}

		$query->select('1 AS editAllowed');

		// Ordering
		$orderList     = $this->getState('list.ordering');
		$directionList = $this->getState('list.direction');
		$order         = !empty($orderList) ? $orderList : 'country';
		$direction     = !empty($directionList) ? $directionList : 'ASC';
		$query->order($db->escape($order) . ' ' . $db->escape($direction));

		return $query;
	}
}
