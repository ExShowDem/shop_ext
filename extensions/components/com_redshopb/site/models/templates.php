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
class RedshopbModelTemplates extends RedshopbModelList
{
	/**
	 * Name of the filter form to load
	 *
	 * @var  string
	 */
	protected $filterFormName = 'filter_templates';

	/**
	 * Limit field used by the pagination
	 *
	 * @var  string
	 */
	protected $limitField = 'template_limit';

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
				'template_state',
				'search_templates',
				'template_group',
				'group',
				// Ordering
				't.id',
				't.name',
				't.alias',
				't.scope',
				't.state',
				't.template_group'
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
		parent::populateState('t.alias', 'asc');
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
					't.*',
					// Web service aliases
					$db->qn('t.alias', 'code'),
					$db->qn('t.template_group', 'group')
				)
			)
			->from($db->qn('#__redshopb_template', 't'));

		// Filter search
		$search = $this->getState('filter.search_templates', $this->getState('filter.search'));

		if (!empty($search))
		{
			$search = $db->quote('%' . $db->escape($search, true) . '%');

			$searchFields = array(
				't.name LIKE ' . $search,
				't.alias LIKE ' . $search
			);

			$query->where('(' . implode(' OR ', $searchFields) . ')');
		}

		$scope = $this->getState('filter.scope', '');

		if ($scope)
		{
			$query->where('t.scope = ' . $db->q($scope));
		}

		$templateGroup = $this->getState('filter.template_group', $this->getState('filter.group'));

		if ($templateGroup)
		{
			$query->where('t.template_group = ' . $db->q($templateGroup));
		}

		$state = $this->getState('filter.template_state', '');

		if (is_numeric($state))
		{
			$query->where('t.state = ' . $db->q($state));
		}

		// Ordering
		$orderList     = $this->getState('list.ordering');
		$directionList = $this->getState('list.direction');
		$order         = !empty($orderList) ? $orderList : 't.alias';
		$direction     = !empty($directionList) ? $directionList : 'ASC';
		$query->order($db->escape($order) . ' ' . $db->escape($direction));

		return $query;
	}
}
