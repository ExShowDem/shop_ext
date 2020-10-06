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
 * Conversion table.
 *
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Tables
 * @since       1.0
 */
class RedshopbTableConversion extends RedshopbTable
{
	/**
	 * @var  integer
	 */
	public $id;

	/**
	 * @var  integer
	 */
	public $product_attribute_id;

	/**
	 * @var  string
	 */
	public $name;

	/**
	 * @var  string
	 */
	public $alias;

	/**
	 * Constructor
	 *
	 * @param   JDatabase  $db  A database connector object
	 *
	 * @throws  UnexpectedValueException
	 */
	public function __construct(&$db)
	{
		$this->_tableName = 'redshopb_conversion';
		$this->_tbl_key   = 'id';

		parent::__construct($db);
	}
}
