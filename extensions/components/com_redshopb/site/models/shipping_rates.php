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
 * Shipping rates Model
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 * @since       1.0
 */
class RedshopbModelShipping_Rates extends RedshopbModelList
{
	/**
	 * Name of the filter form to load
	 *
	 * @var  string
	 */
	protected $filterFormName = 'filter_shipping_rates';

	/**
	 * Limitstart field used by the pagination
	 *
	 * @var  string
	 */
	protected $limitField = 'shipping_rate_limit';

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
				'sr.id', 'sr.name', 'sr.state', 'shipping_name'
			);
		}

		parent::__construct($config);
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
			->select('sr.*')
			->from($db->qn('#__redshopb_shipping_rates', 'sr'))

			// Select the shipping configuration
			->select('sc.params, sc.shipping_name')
			->leftJoin($db->qn('#__redshopb_shipping_configuration', 'sc') . ' ON sc.id = sr.shipping_configuration_id')

			// Select the debtor group name
			->select('cpg.name as debtor_group_name')
			->leftJoin(
				$db->qn('#__redshopb_customer_price_group', 'cpg') . ' ON cpg.id = sc.owner_name AND sc.extension_name = ' . $db->q('com_redshopb')
			);

		// Filter search
		$search = $this->getState('filter.search_shipping_rates');

		if (!empty($search))
		{
			$search = $db->quote('%' . $db->escape($search, true) . '%');
			$query->where('(sr.name LIKE ' . $search . ')');
		}

		// Filter
		$filter = $this->getState('filter.shipping_configuration_id', '');

		if ($filter)
		{
			$query->where('sr.shipping_configuration_id = ' . (int) $filter);
		}

		$filter = $this->getState('filter.shipping_name', '');

		if ($filter)
		{
			$query->where('sc.shipping_name = ' . $db->q($filter));
		}

		// Filter by state
		$state = $this->getState('filter.shipping_rate_state');

		if (is_numeric($state))
		{
			$query->where('sr.state = ' . (int) $state);
		}

		// Ordering
		$orderList     = $this->getState('list.ordering');
		$directionList = $this->getState('list.direction');

		$order     = !empty($orderList) ? $orderList : 'sr.id';
		$direction = !empty($directionList) ? $directionList : 'ASC';
		$query->order($db->escape($order) . ' ' . $db->escape($direction));

		return $query;
	}
}
