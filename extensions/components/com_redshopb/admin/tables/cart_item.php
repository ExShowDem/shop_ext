<?php
/**
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Tables
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

/**
 * Cart Item table.
 *
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Tables
 * @since       1.0
 */
class RedshopbTableCart_Item extends RedshopbTable
{
	/**
	 * The table name without the prefix.
	 *
	 * @var  string
	 */
	protected $_tableName = 'redshopb_cart_item';

	/**
	 * @var  integer
	 */
	public $id;

	/**
	 * @var  integer
	 */
	public $cart_id;

	/**
	 * @var  integer
	 */
	public $product_id;

	/**
	 * @var  integer
	 */
	public $product_item_id = null;

	/**
	 * @var  integer
	 */
	public $parent_cart_item_id = null;

	/**
	 * @var  integer
	 */
	public $collection_id = null;

	/**
	 * @var  integer
	 */
	public $quantity;

	/**
	 * @var  integer
	 */
	public $state;

	/**
	 * @var  integer
	 */
	public $created_by = null;

	/**
	 * @var  string
	 */
	public $created_date = '0000-00-00 00:00:00';

	/**
	 * @var  integer
	 */
	public $modified_by = null;

	/**
	 * @var  string
	 */
	public $modified_date = '0000-00-00 00:00:00';
}
