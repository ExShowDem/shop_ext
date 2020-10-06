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
 * Config table.
 *
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Tables
 * @since       1.13.0
 */
class RedshopbTableConfig extends RedshopbTable
{
	/**
	 * The table name without the prefix.
	 *
	 * @var  string
	 */
	protected $_tableName = 'redshopb_config';

	/**
	 * Table key
	 *
	 * @var  string
	 */
	protected $_tableKey = 'id';

	/**
	 * Name of config
	 *
	 * @var  string
	 */
	public $name;

	/**
	 * Value of config
	 *
	 * @var  string
	 */
	public $value;

	/**
	 * Table specific restriction if we dont want to lock columns on this table
	 *
	 * @var  integer
	 */
	protected $isLockingSystemEnabled = 0;
}
