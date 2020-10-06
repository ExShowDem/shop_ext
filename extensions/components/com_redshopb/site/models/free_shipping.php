<?php
/**
 * @package     Redshopb.Frontend
 * @subpackage  Models
 *
 * @copyright   Copyright (C) 2012 - 2016 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

/**
 * Free_Shipping Model.
 *
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Tables
 * @since       1.0
 */
class RedshopbModelFree_Shipping extends RedshopbModelAdmin
{
	/**
	 * Method to delete an entry in the 'free_shipping_threshold_purchases' database table.
	 *
	 * @param   array    $cids  Array of table IDs.
	 *
	 * @return  boolean  True on success.
	 */
	public function delete(&$cids)
	{
		/** @var   RedshopbTableFree_Shipping   $table */
		$table = RedshopbTable::getAdminInstance('Free_Shipping');

		return $table->remove($cids);
	}
}
