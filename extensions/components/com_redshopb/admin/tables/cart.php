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
 * Cart table.
 *
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Tables
 * @since       1.0
 */
class RedshopbTableCart extends RedshopbTable
{
	/**
	 * The table name without the prefix.
	 *
	 * @var  string
	 */
	protected $_tableName = 'redshopb_cart';

	/**
	 * @var  integer
	 */
	public $id;

	/**
	 * @var  string
	 */
	public $name;

	/**
	 * @var  integer
	 */
	public $state;

	/**
	 * @var  integer
	 */
	public $company_id = null;

	/**
	 * @var  integer
	 */
	public $department_id = null;

	/**
	 * @var  integer
	 */
	public $user_id = null;

	/**
	 * @var  integer
	 */
	public $visible_others;

	/**
	 * @var  integer
	 */
	public $user_cart;

	/**
	 * @var  integer
	 */
	public $checked_out = null;

	/**
	 * @var  string
	 */
	public $checked_out_time = '0000-00-00 00:00:00';

	/**
	 * @var  integer
	 */
	public $created_by = null;

	/**
	 * @var  string
	 */
	public $created_date = '0000-00-00 00:00:00';

	/**
	 * @var  integer
	 */
	public $modified_by = null;

	/**
	 * @var  string
	 */
	public $modified_date = '0000-00-00 00:00:00';
}
