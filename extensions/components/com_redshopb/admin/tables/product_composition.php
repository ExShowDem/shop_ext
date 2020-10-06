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
 * Product Composition table.
 *
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Tables
 * @since       1.0
 */
class RedshopbTableProduct_Composition extends RedshopbTable
{
	/**
	 * The table name without the prefix.
	 *
	 * @var  string
	 */
	protected $_tableName = 'redshopb_product_composition';

	/**
	 * @var integer
	 */
	public $id;

	/**
	 * @var integer
	 */
	public $product_id;

	/**
	 * @var integer
	 */
	public $flat_attribute_value_id;

	/**
	 * @var string
	 */
	public $type;

	/**
	 * @var [type]
	 */
	public $quality;
}
