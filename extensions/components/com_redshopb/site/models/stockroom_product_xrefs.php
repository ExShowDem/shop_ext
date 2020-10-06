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
 * Stockroom Product Xrefs Model
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 * @since       1.0
 */
class RedshopbModelStockroom_Product_Xrefs extends RedshopbModelList
{
	/**
	 * Name of the filter form to load
	 *
	 * @var  string
	 */
	protected $filterFormName = 'filter_stockroom_product_xrefs';

	/**
	 * Limitstart field used by the pagination
	 *
	 * @var  string
	 */
	protected $limitField = 'stockroom_product_xrefs_limit';

	/**
	 * Main table query prefix
	 *
	 * @var  array
	 */
	protected $mainTablePrefix = 'spx';

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
				'stockroom_id',
				'product_id',
				'unlimited',
				'product_ids',
				'stockroom_ids'
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
		parent::populateState('spx.id', 'asc');
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
			->select('spx.*')
			->from($db->qn('#__redshopb_stockroom_product_xref', 'spx'));

		$productid = $this->getState('filter.product_id');

		if (is_numeric($productid))
		{
			$query->where('spx.product_id = ' . (int) $productid);
		}

		$stockroomId = $this->getState('filter.stockroom_id');

		if (is_numeric($stockroomId))
		{
			$query->where('spx.stockroom_id = ' . (int) $stockroomId);
		}

		// Filter by multiple product ids
		$productIds = $this->getState('filter.product_ids', null);

		if (!is_null($productIds))
		{
			$query->where($db->qn('spx.product_id') . ' IN (' . implode(',', $db->q($productIds)) . ')');
		}

		// Filter by multiple stockroom ids
		$stockroomIds = $this->getState('filter.stockroom_ids', null);

		if (!is_null($stockroomIds))
		{
			$query->where($db->qn('spx.stockroom_id') . ' IN (' . implode(',', $db->q($stockroomIds)) . ')');
		}

		// Ordering
		$orderList     = $this->getState('list.ordering');
		$directionList = $this->getState('list.direction');

		$order     = !empty($orderList) ? $orderList : 'spx.id';
		$direction = !empty($directionList) ? $directionList : 'ASC';
		$query->order($db->escape($order) . ' ' . $db->escape($direction));

		// Adds related web service data when requested
		$this->getListQueryWS($query);

		return $query;
	}

	/**
	 * Method to get an array of data items.
	 *
	 * @return  mixed  An array of data items on success, false on failure.
	 *
	 * @since   11.1
	 */
	public function getItems()
	{
		$items = parent::getItems();

		if (!$items)
		{
			return false;
		}

		foreach ($items as $item)
		{
			// Format these number follow decimal position config.
			$item->amount            = RedshopbHelperProduct::decimalFormat($item->amount, $item->product_id);
			$item->stock_lower_level = RedshopbHelperProduct::decimalFormat($item->stock_lower_level, $item->product_id);
			$item->stock_upper_level = RedshopbHelperProduct::decimalFormat($item->stock_upper_level, $item->product_id);
		}

		return $items;
	}
}
