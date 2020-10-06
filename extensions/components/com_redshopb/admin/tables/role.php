<?php
/**
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Tables
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Table\Usergroup;
/**
 * Role table.
 *
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Tables
 * @since       1.0
 */
class RedshopbTableRole extends RedshopbTable
{
	/**
	 * The table name without the prefix.
	 *
	 * @var  string
	 */
	protected $_tableName = 'redshopb_role';

	/**
	 * @var  integer
	 */
	public $id;

	/**
	 * @var  integer
	 */
	public $role_type_id;

	/**
	 * @var  integer
	 */
	public $company_id = null;

	/**
	 * @var  integer
	 */
	public $joomla_group_id;

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
	 * @var  integer
	 */
	public $checked_out = null;

	/**
	 * @var  string
	 */
	public $checked_out_time = '0000-00-00 00:00:00';

	/**
	 * Table specific restriction if we dont want to lock columns on this table
	 *
	 * @var  integer
	 */
	protected $isLockingSystemEnabled = 0;

	/**
	 * Delete Roles
	 *
	 * @param   string/array  $pk  Array of company ids or ids comma separated
	 *
	 * @return boolean
	 */
	public function deleteRoles($pk)
	{
		// Initialise variables.
		$k = $this->_tbl_key;

		// Received an array of ids?
		if (is_array($pk))
		{
			// Sanitize input.
			$pk = ArrayHelper::toInteger($pk);
			$pk = RHelperArray::quote($pk);
			$pk = implode(',', $pk);
		}

		$pk = (is_null($pk)) ? $this->$k : $pk;

		// If no primary key is given, return false.
		if ($pk === null)
		{
			return false;
		}

		if (!is_array($pk))
		{
			$pk = array($pk);
		}

		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->select(array('id'))
			->from($db->qn('#__redshopb_role'))
			->where('company_id IN (' . implode(',', $pk) . ')');
		$db->setQuery($query);

		$roles = $db->loadColumn();

		if ($roles)
		{
			foreach ($roles as $roleId)
			{
				if ($this->load($roleId, true))
				{
					if (!$this->delete($roleId))
					{
						return false;
					}
				}
			}
		}

		return true;
	}

	/**
	 * Deletes this row in database (or if provided, the row of key $pk)
	 *
	 * @param   mixed  $pk  An optional primary key value to delete.  If not set the instance property value is used.
	 *
	 * @return  boolean  True on success.
	 */
	public function delete($pk = null)
	{
		$joomlaUserGroupId = 0;

		if ($this->load($pk, true))
		{
			if ($this->joomla_group_id)
			{
				$joomlaUserGroupId = $this->joomla_group_id;
			}
		}

		if ($joomlaUserGroupId)
		{
			/** @var Usergroup $groupTable */
			$groupTable = Table::getInstance('Usergroup');

			if ($groupTable->load($joomlaUserGroupId, true))
			{
				if (!$groupTable->delete($joomlaUserGroupId))
				{
					return false;
				}
			}
			else
			{
				return false;
			}
		}

		return parent::delete($pk);
	}
}
