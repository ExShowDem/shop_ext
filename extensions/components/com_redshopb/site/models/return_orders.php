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
 * Return Orders Model
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 * @since       1.0
 */
class RedshopbModelReturn_Orders extends RedshopbModelList
{
	/**
	 * Name of the filter form to load
	 *
	 * @var  string
	 */
	protected $filterFormName = 'filter_return_orders';

	/**
	 * Limitstart field used by the pagination
	 *
	 * @var  string
	 */
	protected $limitField = 'return_orders_limit';

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
				'id', 'p.name', 'search_return_orders', 'search'
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
		$db    = $this->getDbo();
		$query = $db->getQuery(true)
			->select('ro.*')
			->from($db->qn('#__redshopb_return_orders', 'ro'))
			->select('oi.product_name as product_name, oi.product_sku as product_sku, oi.product_id, oi.product_item_id')
			->leftJoin($db->qn('#__redshopb_order_item', 'oi') . ' ON oi.id = ro.order_item_id')
			->select('o.customer_type, o.customer_id, o.customer_name')
			->leftJoin($db->qn('#__redshopb_order', 'o') . ' ON o.id = ro.order_id');

		if (!RedshopbHelperACL::isSuperAdmin())
		{
			$userFilter = (int) $this->getState('filter.user_id', 0);

			if ($userFilter)
			{
				$query->where($db->qn('ro.created_by') . ' = ' . $userFilter);
			}
		}

		// Filter search
		$search = $this->getState('filter.search_return_orders', $this->getState('filter.search'));

		if (!empty($search))
		{
			$search = $db->quote('%' . $db->escape($search, true) . '%');
			$query->where('oi.product_name LIKE ' . $search);
		}

		// Ordering
		$orderList     = $this->getState('list.ordering');
		$directionList = $this->getState('list.direction');

		$order     = !empty($orderList) ? $orderList : 'ro.created_date';
		$direction = !empty($directionList) ? $directionList : 'DESC';
		$query->order($db->escape($order) . ' ' . $db->escape($direction));

		return $query;
	}
}
