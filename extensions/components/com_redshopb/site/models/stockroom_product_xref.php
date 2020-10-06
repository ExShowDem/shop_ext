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
 * Stockroom Product Xref Model
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 * @since       1.0
 */
class RedshopbModelStockroom_Product_Xref extends RedshopbModelAdmin
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
		$item->amount            = RedshopbHelperProduct::decimalFormat($item->amount, $item->product_id);
		$item->stock_lower_level = RedshopbHelperProduct::decimalFormat($item->stock_lower_level, $item->product_id);
		$item->stock_upper_level = RedshopbHelperProduct::decimalFormat($item->stock_upper_level, $item->product_id);

		return $item;
	}
}
