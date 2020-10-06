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
 * Collection department table.
 *
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Tables
 * @since       1.0
 */
class RedshopbTableCollection_Department_Xref extends RedshopbTable
{
	/**
	 * The table name without the prefix.
	 *
	 * @var  string
	 */
	protected $_tableName = 'redshopb_collection_department_xref';

	/**
	 * Name of the primary key fields in the table.
	 *
	 * @var    array
	 * @since  12.2
	 */
	protected $_tbl_keys = array('collection_id', 'department_id');

	/**
	 * @var  integer
	 */
	public $id;

	/**
	 * @var  integer
	 */
	public $collection_id;

	/**
	 * @var  integer
	 */
	public $department_id;
}
