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
 * Currencies Model
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 * @since       1.0
 */
class RedshopbModelCurrencies extends RedshopbModelList
{
	/**
	 * Name of the filter form to load
	 *
	 * @var  string
	 */
	protected $filterFormName = 'filter_currencies';

	/**
	 * Limitstart field used by the pagination
	 *
	 * @var  string
	 */
	protected $limitField = 'currency_limit';

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
				'id', 'c.id',
				'state', 'c.state',
				'name', 'c.name',
				'alpha3', 'c.alpha3',
				'numeric', 'c.numeric',
				'symbol', 'c.symbol',
				'created_by', 'c.created_by',
				'created_date', 'c.created_date',
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
		parent::populateState('c.alpha3', 'asc');
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return  JDatabaseQuery
	 */
	protected function getListQuery()
	{
		$db	= $this->getDbo();

		$query = $db->getQuery(true)
			->select(
				array(
					'c.*',
					$db->qn('alpha3', 'code'),
					$db->qn('u.name', 'author')
				)
			)
			->from($db->qn('#__redshopb_currency', 'c'))
			->join('left', $db->qn('#__users', 'u') . ' ON ' . $db->qn('u.id') . ' = ' . $db->qn('c.created_by'));

		// Filter search
		$search = $this->getState('filter.search_currencies', $this->getState('filter.search'));

		if (!empty($search))
		{
			$search = $db->quote('%' . $db->escape($search, true) . '%');

			$searchFields = array(
				'c.name LIKE ' . $search,
				'c.alpha3 LIKE ' . $search,
			);

			$query->where('(' . implode(' OR ', $searchFields) . ')');
		}

		// Ordering
		$orderList     = $this->getState('list.ordering');
		$directionList = $this->getState('list.direction');

		if ($this->getState('filter.order') != '')
		{
			$orderList = 'c.' . $this->getState('filter.order');
		}

		if ($this->getState('filter.order_Dir') != '')
		{
			$directionList = $this->getState('filter.order_Dir');
		}

		$order     = !empty($orderList) ? $orderList : 'c.alpha3';
		$direction = !empty($directionList) ? $directionList : 'ASC';
		$query->order($db->escape($order) . ' ' . $db->escape($direction));

		return $query;
	}
}
