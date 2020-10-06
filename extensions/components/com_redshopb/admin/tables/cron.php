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
 * Cron table.
 *
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Tables
 * @since       1.0
 */
class RedshopbTableCron extends RedshopbTableNested
{
	/**
	 * The table name without the prefix.
	 *
	 * @var  string
	 */
	protected $_tableName = 'redshopb_cron';

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
	public $parent_id = null;

	/**
	 * @var  integer
	 */
	public $state = 1;

	/**
	 * @var  datetime
	 */
	public $start_time = '0000-00-00 00:00:00';

	/**
	 * @var  datetime
	 */
	public $finish_time = '0000-00-00 00:00:00';

	/**
	 * @var  datetime
	 */
	public $next_start = '0000-00-00 00:00:00';

	/**
	 * @var  integer
	 */
	public $lft;

	/**
	 * @var  integer
	 */
	public $rgt;

	/**
	 * @var  integer
	 */
	public $level;

	/**
	 * @var  string
	 */
	public $alias;

	/**
	 * @var  string
	 */
	public $path = '';

	/**
	 * @var  integer
	 */
	public $execute_sync = 0;

	/**
	 * @var  string
	 */
	public $mask_time = 'Y-m-d H:00:00';

	/**
	 * @var  string
	 */
	public $offset_time = '+1 hour';

	/**
	 * @var  integer
	 */
	public $is_continuous = 1;

	/**
	 * @var  integer
	 */
	public $items_process_step = 0;

	/**
	 * @var  integer
	 */
	public $items_processed = 0;

	/**
	 * @var  integer
	 */
	public $items_total = 0;

	/**
	 * @var  string
	 */
	public $last_status_messages;

	/**
	 * @var  string
	 */
	public $params;

	/**
	 * @var  integer
	 */
	public $checked_out = null;

	/**
	 * @var  datetime
	 */
	public $checked_out_time = '0000-00-00 00:00:00';

	/**
	 * Table specific restriction if we dont want to lock columns on this table
	 *
	 * @var  integer
	 */
	protected $isLockingSystemEnabled = 0;
}
