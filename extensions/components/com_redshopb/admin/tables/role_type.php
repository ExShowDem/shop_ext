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
 * Role type table.
 *
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Tables
 * @since       1.0
 */
class RedshopbTableRole_Type extends RedshopbTable
{
	/**
	 * The table name without the prefix.
	 *
	 * @var  string
	 */
	protected $_tableName = 'redshopb_role_type';

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
	public $company_role;

	/**
	 * @var  integer
	 */
	public $allow_access;

	/**
	 * @var  string
	 */
	public $type;

	/**
	 * @var  integer
	 */
	public $limited;

	/**
	 * @var  string
	 */
	public $allowed_rules;

	/**
	 * @var  string
	 */
	public $allowed_rules_main_company;

	/**
	 * @var  string
	 */
	public $allowed_rules_customers;

	/**
	 * @var  string
	 */
	public $allowed_rules_company;

	/**
	 * @var  string
	 */
	public $allowed_rules_own_company;

	/**
	 * @var  string
	 */
	public $allowed_rules_department;

	/**
	 * @var  integer
	 */
	public $checked_out = null;

	/**
	 * @var  string
	 */
	public $checked_out_time = '0000-00-00 00:00:00';

	/**
	 * @var  string
	 */
	public $created_date = '0000-00-00 00:00:00';

	/**
	 * @var  integer
	 */
	public $created_by = null;

	/**
	 * @var  string
	 */
	public $modified_date = '0000-00-00 00:00:00';

	/**
	 * @var  integer
	 */
	public $modified_by = null;

	/**
	 * Table specific restriction if we dont want to lock columns on this table
	 *
	 * @var  integer
	 */
	protected $isLockingSystemEnabled = 0;
}
