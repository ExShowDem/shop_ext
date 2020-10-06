<?php
/**
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Table
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

/**
 * Order item attributes table
 *
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Table
 * @since       0.8.0.4
 */
class RedshopbTableOrder_Item_Attribute extends RedshopbTable
{
	/**
	 * The name of the table.
	 *
	 * @var    string
	 * @since  2.0
	 */
	protected $_tableName = 'redshopb_order_item_attribute';

	/**
	 * The primary key of the table.
	 *
	 * @var    string
	 * @since  2.0
	 */
	protected $_tableKey = 'id';
}
