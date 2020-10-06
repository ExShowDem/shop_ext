<?php
/**
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Tables
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

/**
 * Table lock table.
 *
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Tables
 * @since       1.0
 */
class RedshopbTableTable_Lock extends RedshopbTable
{
	/**
	 * The table name without the prefix.
	 *
	 * @var  string
	 */
	protected $_tableName = 'redshopb_table_lock';

	/**
	 * @var  integer
	 */
	public $id;

	/**
	 * @var  string
	 */
	public $table_name;

	/**
	 * @var  integer
	 */
	public $table_id;

	/**
	 * @var  string
	 */
	public $column_name;

	/**
	 * @var  string
	 */
	public $locked_date = '0000-00-00 00:00:00';

	/**
	 * @var  integer
	 */
	public $locked_by = null;

	/**
	 * @var  string
	 */
	public $locked_method;

	/**
	 * Table specific restriction if we dont want to lock columns on this table
	 *
	 * @var  integer
	 */
	protected $isLockingSystemEnabled = 0;

	/**
	 * WS sync mapping
	 *
	 * @var  array
	 */
	protected $wsSyncMapPK = array(
		'pim' => array(
			'erp.pim.table_lock'
		),
		'erp' => array(
			'ws.table_lock'
		),
		'b2b' => array(
			'erp.webservice.table_locks'
		),
		'fengel' => array(
			'fengel.table_lock'
		)
	);

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
		$this->table_name    = trim($this->table_name);
		$this->table_id      = (int) $this->table_id;
		$this->column_name   = trim($this->column_name);
		$this->locked_method = trim($this->locked_method);

		if (empty($this->table_name))
		{
			$this->setError(Text::sprintf(Text::_('COM_REDSHOPB_MISSING_FIELD'), Text::_('COM_REDSHOPB_TABLE_LOCK_TABLE_NAME_LABEL')));

			return false;
		}

		if (empty($this->table_id))
		{
			$this->setError(Text::sprintf(Text::_('COM_REDSHOPB_MISSING_FIELD'), Text::_('COM_REDSHOPB_TABLE_LOCK_TABLE_ID_LABEL')));

			return false;
		}

		if (empty($this->column_name))
		{
			$this->setError(Text::sprintf(Text::_('COM_REDSHOPB_MISSING_FIELD'), Text::_('COM_REDSHOPB_TABLE_LOCK_COLUMN_NAME_LABEL')));

			return false;
		}

		if (!in_array($this->locked_method, array('User', 'Webservice', 'Sync', 'Other')))
		{
			$this->locked_method = 'User';
		}

		if (empty($this->locked_by))
		{
			$this->locked_by = Factory::getUser()->id;

			if ($this->locked_by <= 0)
			{
				$this->locked_by = null;
			}
		}

		if (empty($this->locked_date) || $this->locked_date == '0000-00-00 00:00:00')
		{
			$this->locked_date = date('Y-m-d H:i:s');
		}

		return true;
	}
}
