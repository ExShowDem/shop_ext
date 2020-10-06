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
 * Products Model
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 * @since       1.0
 */
class RedshopbModelOrder_Items extends RedshopbModelList
{
	/**
	 * Name of the filter form to load
	 *
	 * @var  string
	 */
	protected $filterFormName = 'filter_order_items';

	/**
	 * Limitstart field used by the pagination
	 *
	 * @var  string
	 */
	protected $limitField = 'order_limit';

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
	protected $mainTablePrefix = 'oi';

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
				'id',
				'order_id',
				'product_id',
				'product_item_id',
				'collection_id',
				'currency_id',
				'currency_code',
				'stockroom_id'
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
		parent::populateState('oi.id', 'asc');
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
			->select('oi.*')
			->from($db->qn('#__redshopb_order_item', 'oi'));

		// Filter by order
		$filterOrderId = $this->getState('filter.order_id');

		if (is_numeric($filterOrderId))
		{
			$query->where('oi.order_id = ' . (int) $filterOrderId);
		}

		// Filter by product
		$filterProductId = $this->getState('filter.product_id');

		if (is_numeric($filterProductId))
		{
			$query->where('oi.product_id = ' . (int) $filterProductId);
		}

		// Filter by product item
		$filterProductItemId = $this->getState('filter.product_item_id');

		if (is_numeric($filterProductItemId))
		{
			$query->where('oi.product_item_id = ' . (int) $filterProductItemId);
		}

		// Filter by collection
		$filterCollectionId = $this->getState('filter.collection_id');

		if (is_numeric($filterCollectionId))
		{
			$query->where('oi.collection_id = ' . (int) $filterCollectionId);
		}

		// Filter by stockroom
		$filterStockroomId = $this->getState('filter.stockroom_id');

		if (is_numeric($filterStockroomId))
		{
			$query->where('oi.stockroom_id = ' . (int) $filterStockroomId);
		}

		// Ordering
		$orderList     = $this->getState('list.ordering');
		$directionList = $this->getState('list.direction');

		$order     = !empty($orderList) ? $orderList : 'oi.id';
		$direction = !empty($directionList) ? $directionList : 'ASC';
		$query->order($db->escape($order) . ' ' . $db->escape($direction));

		// Adds related web service data when requested
		$this->getListQueryWS($query);

		return $query;
	}
}
