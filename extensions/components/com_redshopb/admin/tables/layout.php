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
 * Layout table.
 *
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Tables
 * @since       1.0
 */
class RedshopbTableLayout extends RedshopbTable
{
	/**
	 * The table name without the prefix.
	 *
	 * @var  string
	 */
	protected $_tableName = 'redshopb_layout';

	/**
	 * Layout id.
	 *
	 * @var  integer
	 */
	public $id;

	/**
	 * Layout name.
	 *
	 * @var  string
	 */
	public $name;

	/**
	 * Layout home.
	 *
	 * @var  integer
	 */
	public $home;

	/**
	 * Layout params in JSON format.
	 *
	 * @var  integer
	 */
	public $params;

	/**
	 * Layout created date.
	 *
	 * @var  integer
	 */
	public $created_date;

	/**
	 * Layout created by user id.
	 *
	 * @var  integer
	 */
	public $created_by;

	/**
	 * Layout date modified.
	 *
	 * @var  integer
	 */
	public $modified_date;

	/**
	 * Layout modified by user id.
	 *
	 * @var  integer
	 */
	public $modified_by;
}
