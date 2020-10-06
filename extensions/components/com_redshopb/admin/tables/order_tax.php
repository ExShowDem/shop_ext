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
 * Order tax table.
 *
 * @since  1.0
 */
class RedshopbTableOrder_Tax extends RedshopbTable
{
	/**
	 * The name of the table with order taxes
	 *
	 * @var    string
	 * @since  1.0
	 */
	protected $_tableName = 'redshopb_order_tax';

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
	public $name;

	/**
	 * @var  float
	 */
	public $tax_rate;

	/**
	 * @var  float
	 */
	public $price;

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
		)
	);
}
