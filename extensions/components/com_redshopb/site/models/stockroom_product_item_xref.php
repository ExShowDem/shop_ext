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
 * Stockroom Product Item Xref Model
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 * @since       1.0
 */
class RedshopbModelStockroom_Product_Item_Xref extends RedshopbModelAdmin
{
	/**
	 * Method to get the row form.
	 *
	 * @param   int  $pk  Primary key
	 *
	 * @return	object
	 */
	public function getItem($pk = null)
	{
		$item = parent::getItem($pk);

		if (!$item)
		{
			return false;
		}

		// Format these number follow decimal position config.
		$productId = (int) RedshopbEntityProduct_Item::getInstance($item->product_item_id)->getProduct()->get('id');

		$item->amount            = RedshopbHelperProduct::decimalFormat($item->amount, $productId);
		$item->stock_lower_level = RedshopbHelperProduct::decimalFormat($item->stock_lower_level, $productId);
		$item->stock_upper_level = RedshopbHelperProduct::decimalFormat($item->stock_upper_level, $productId);

		return $item;
	}
}
