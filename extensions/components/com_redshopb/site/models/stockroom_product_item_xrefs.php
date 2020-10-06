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
 * Stockroom Product Item Xrefs Model
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 * @since       1.0
 */
class RedshopbModelStockroom_Product_Item_Xrefs extends RedshopbModelList
{
	/**
	 * Name of the filter form to load
	 *
	 * @var  string
	 */
	protected $filterFormName = 'filter_stockroom_product_item_xrefs';

	/**
	 * Limitstart field used by the pagination
	 *
	 * @var  string
	 */
	protected $limitField = 'stockroom_product_item_xrefs_limit';

	/**
	 * Main table query prefix
	 *
	 * @var  array
	 */
	protected $mainTablePrefix = 'spix';

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
				'product_item_id',
				'unlimited'
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
		parent::populateState('spix.id', 'asc');
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
			->select('spix.*')
			->from($db->qn('#__redshopb_stockroom_product_item_xref', 'spix'));

		$productitemid = $this->getState('filter.product_item_id');

		if (is_numeric($productitemid))
		{
			$query->where('spix.product_item_id = ' . (int) $productitemid);
		}

		$stockroomId = $this->getState('filter.stockroom_id');

		if (is_numeric($stockroomId))
		{
			$query->where('spix.stockroom_id = ' . (int) $stockroomId);
		}

		// Ordering
		$orderList     = $this->getState('list.ordering');
		$directionList = $this->getState('list.direction');

		$order     = !empty($orderList) ? $orderList : 'spix.id';
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
			$productId = (int) RedshopbEntityProduct_Item::getInstance($item->product_item_id)->getProduct()->get('id');

			$item->amount            = RedshopbHelperProduct::decimalFormat($item->amount, $productId);
			$item->stock_lower_level = RedshopbHelperProduct::decimalFormat($item->stock_lower_level, $productId);
			$item->stock_upper_level = RedshopbHelperProduct::decimalFormat($item->stock_upper_level, $productId);
		}

		return $items;
	}
}
