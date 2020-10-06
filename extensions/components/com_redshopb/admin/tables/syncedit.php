<?php
/**
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Tables
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

/**
 * Cron table.
 *
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Tables
 * @since       1.0
 */
class RedshopbTableSyncEdit extends RedshopbTableNested
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
	public $state = 1;

	/**
	 * @var integer
	 */
	public $parent_id;

	/**
	 * @var  string
	 */
	public $start_time = '0000-00-00 00:00:00';

	/**
	 * @var  string
	 */
	public $finish_time = '0000-00-00 00:00:00';

	/**
	 * @var  string
	 */
	public $next_start = '0000-00-00 00:00:00';

	/**
	 * @var  string
	 */
	public $mask_time = 'Y-m-d 00:00:00';

	/**
	 * @var  string
	 */
	public $offset_time = '+1 day';

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
	 * @var [type]
	 */
	protected $fullSync;

	/**
	 * Table specific restriction if we dont want to lock columns on this table
	 *
	 * @var  integer
	 */
	protected $isLockingSystemEnabled = 0;

	/**
	 * Checks that the object is valid and able to be stored.
	 *
	 * This method checks that the parent_id is non-zero and exists in the database.
	 * Note that the root node (parent_id = 0) cannot be manipulated with this class.
	 *
	 * @return  boolean  True if all checks pass.
	 */
	public function check()
	{
		$this->name = trim($this->name);

		if (empty($this->name))
		{
			$this->setError(Text::_('COM_REDSHOPB_TITLE_CANNOT_BE_EMPTY'));

			return false;
		}

		return true;
	}
}
