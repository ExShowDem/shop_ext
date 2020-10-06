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
 * Country Model
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 * @since       1.0
 */
class RedshopbModelField_Groups extends RedshopbModelList
{
	/**
	 * Name of the filter form to load
	 *
	 * @var  string
	 */
	protected $filterFormName = 'filter_field_groups';

	/**
	 * Limit field used by the pagination
	 *
	 * @var  string
	 */
	protected $limitField = 'field_groups_limit';

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
	protected $mainTablePrefix = 'fg';

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
				'id',
				// Ordering
				'fg.id',
				'fg.name',
				'fg.scope',
				'fg.alias',
				'fg.ordering'
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
		parent::populateState('fg.ordering', 'asc');
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

		$query->select(
			array(
					'fg.*'
				)
		)
			->from($db->qn('#__redshopb_field_group', 'fg'));

		$filterFieldGroupsScope = $this->getState('filter.field_groups_scope', null);

		if (!is_null($filterFieldGroupsScope) && $filterFieldGroupsScope != '')
		{
			$query->where($db->qn('fg.scope') . ' = ' . $db->q($filterFieldGroupsScope));
		}

		// Filter search
		$search = $this->getState('filter.search_field_groups', $this->getState('filter.search'));

		if (!empty($search))
		{
			$search = $db->quote('%' . $db->escape($search, true) . '%');

			$query->where($db->qn('fg.name') . ' LIKE ' . $search);
		}

		// Ordering
		$orderList     = $this->getState('list.ordering');
		$directionList = $this->getState('list.direction');
		$order         = !empty($orderList) ? $orderList : 'fg.ordering';
		$direction     = !empty($directionList) ? $directionList : 'ASC';

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

		// Field web service is restricted to products (because of API scope)
		$this->setState('filter.field_groups_scope', 'product');

		return parent::getItemsWS();
	}
}
