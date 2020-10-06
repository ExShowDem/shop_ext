<?php
/**
 * @package     RedSHOP.Backend
 * @subpackage  Table
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

/**
 * Order item table.
 *
 * @since  1.0
 */
class RedshopbTableOrder_Item extends RedshopbTable
{
	/**
	 * The name of the table with currencies
	 *
	 * @var    string
	 * @since  1.0
	 */
	protected $_tableName = 'redshopb_order_item';

	/**
	 * The primary key of the table
	 *
	 * @var    string
	 * @since  1.0
	 */
	protected $_tableKey = 'id';

	/**
	 * @var  integer
	 */
	public $id;

	/**
	 * @var  integer
	 */
	public $order_id;

	/**
	 * @var  integer
	 */
	public $product_id;

	/**
	 * @var  string
	 */
	public $product_name;

	/**
	 * @var  string
	 */
	public $product_sku;

	/**
	 * @var  integer
	 */
	public $product_item_id;

	/**
	 * @var  string
	 */
	public $product_item_sku;

	/**
	 * @var  string
	 */
	public $currency;

	/**
	 * @var  integer
	 */
	public $currency_id;

	/**
	 * @var  float
	 */
	public $discount;

	/**
	 * @var  float
	 */
	public $price_without_discount;

	/**
	 * @var  float
	 */
	public $price;

	/**
	 * @var  float
	 */
	public $quantity;

	/**
	 * @var  integer
	 */
	public $parent_id;

	/**
	 * @var  integer
	 */
	public $collection_id;

	/**
	 * @var  string
	 */
	public $collection_name;

	/**
	 * @var  string
	 */
	public $collection_erp_id;

	/**
	 * @var  integer
	 */
	public $stockroom_id;

	/**
	 * @var  string
	 */
	public $stockroom_name;

	/**
	 * @var  integer
	 */
	public $offer_id;

	/**
	 * WS sync mapping for other fields of the model table with other model pks
	 *
	 * @var  array
	 */
	protected $wsSyncMapFields = array(
		'order_id' => array(
			'model' => 'Orders'
		),
		'product_id' => array(
			'model' => 'Products'
		),
		'product_item_id' => array(
			'model' => 'Product_Items'
		),
		'collection_id' => array(
			'model' => 'Collections'
		),
		'stockroom_id' => array(
			'model' => 'Stockrooms'
		)
	);

	/**
	 * WS sync mapping for code fields with other model related data (alias, etc)
	 *
	 * @var  array
	 */
	protected $wsSyncMapCodeFields = array(
		'currency_code' => 'Currencies'
	);
}
