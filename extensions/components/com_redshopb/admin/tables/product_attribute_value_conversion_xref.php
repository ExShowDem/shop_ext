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
 * Product Attribute value conversion table.
 *
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Tables
 * @since       1.0
 */
class RedshopbTableProduct_Attribute_Value_Conversion_Xref extends RedshopbTable
{
	/**
	 * The table name without the prefix.
	 *
	 * @var  string
	 */
	protected $_tableName = 'redshopb_product_attribute_value_conv_xref';

	/**
	 * Name of the primary key fields in the table.
	 *
	 * @var    array
	 * @since  12.2
	 */
	protected $_tbl_keys = array('value_id', 'conversion_set_id');

	/**
	 * @var  integer
	 */
	public $value_id;

	/**
	 * @var  integer
	 */
	public $conversion_set_id;

	/**
	 * @var  string
	 */
	public $value;

	/**
	 * @var  string
	 */
	public $image;
}
