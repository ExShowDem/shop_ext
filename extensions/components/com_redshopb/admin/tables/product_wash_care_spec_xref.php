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
 * Product Wash_Care_Spec Xref table.
 *
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Tables
 * @since       1.0
 */
class RedshopbTableProduct_Wash_Care_Spec_Xref extends RedshopbTable
{
	/**
	 * The table name without the prefix.
	 *
	 * @var  string
	 */
	protected $_tableName = 'redshopb_product_wash_care_spec_xref';

	/**
	 * Name of the primary key fields in the table.
	 *
	 * @var    array
	 * @since  12.2
	 */
	protected $_tbl_keys = array('product_id', 'wash_care_spec_id');

	/**
	 * @var  integer
	 */
	public $id;

	/**
	 * @var  integer
	 */
	public $product_id;

	/**
	 * @var  integer
	 */
	public $wash_care_spec_id;

	/**
	 * @var  integer
	 */
	public $ordering;
}
