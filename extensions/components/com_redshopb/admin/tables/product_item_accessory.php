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
 * Product item accessory table.
 *
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Tables
 * @since       1.0
 */
class RedshopbTableProduct_Item_Accessory extends RedshopbTable
{
	/**
	 * The table name without the prefix.
	 *
	 * @var  string
	 */
	protected $_tableName = 'redshopb_product_item_accessory';

	/**
	 * @var  integer
	 */
	public $id;

	/**
	 * @var  integer
	 */
	public $attribute_value_id;

	/**
	 * @var  integer
	 */
	public $collection_id;

	/**
	 * @var  integer
	 */
	public $accessory_product_id;

	/**
	 * @var  string
	 */
	public $description;

	/**
	 * @var  integer
	 */
	public $hide_on_collection;

	/**
	 * @var  integer
	 */
	public $price;

	/**
	 * @var  integer
	 */
	public $selection;

	/**
	 * @var  integer
	 */
	public $state;
}
