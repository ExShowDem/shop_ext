<?php
/**
 * @package     Aesir.E-Commerce.Library
 * @subpackage  Table
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Plugin\PluginHelper;

/**
 * Redshopb Table Locks trait
 *
 * @package     Aesir.E-Commerce.Library
 * @subpackage  Table Locks
 * @since       1.0
 */
trait RedshopbTableTraitLocks
{
	/**
	 * List of locked columns
	 *
	 * @var  array
	 */
	public $lockedColumns = null;

	/**
	 * List of columns to unlock
	 *
	 * @var  array
	 */
	protected $aECUnLockedColumns = null;

	/**
	 * Table specific restriction if we dont want to lock columns on this table
	 *
	 * @var  integer
	 */
	protected $isLockingSystemEnabled = null;

	/**
	 * List of columns that can be locked
	 *
	 * @var  array
	 */
	protected $tableColumnsForLocking = null;

	/**
	 * Returns a property of the object or the default value if the property is not set.
	 *
	 * @param   string  $property  The name of the property.
	 * @param   mixed   $default   The default value.
	 *
	 * @return  mixed    The value of the property.
	 *
	 * @since   11.1
	 *
	 * @see     CMSObject::getProperties()
	 */
	abstract public function get($property, $default = null);

	/**
	 * Modifies a property of the object, creating it if it does not already exist.
	 *
	 * @param   string  $property  The name of the property.
	 * @param   mixed   $value     The value of the property to set.
	 *
	 * @return  mixed  Previous value of the property.
	 *
	 * @since   11.1
	 */
	abstract public function set($property, $value = null);

	/**
	 * Gets table properties that are not lockable
	 *
	 * @return  array
	 */
	public function getTableLockRestrictedColumns()
	{
		// Fields not suited for locking
		$restrictedColumns = array(
			// Primary key
			$this->get('_tbl_key'),

			// Logging data
			'created_by',
			'created_date',
			'modified_by',
			'modified_date',
			'checked_out',
			'checked_out_time',

			// Calculated data
			'lft',
			'rgt',
			'level',
			'path',

			// Restricted fields
			'aECUnLockedColumns'
		);

		return $restrictedColumns;
	}

	/**
	 * Gets table properties that are lockable by parent
	 *
	 * @return  array
	 */
	public function getTableLockParentColumns()
	{
		// Fields that are locked if the key is locked
		$parentLockColumns = array(
			// Location
			'parent_id' => array('level', 'lft', 'rgt', 'path', '_location_id'),
		);

		if (property_exists($this, 'alias'))
		{
			$aliasColumns = empty($this->_aliasColumns) ? array('name') : $this->_aliasColumns;

			if (!empty($aliasColumns))
			{
				// We must add each alias column as a parent
				foreach ($aliasColumns as $aliasColumn)
				{
					$parentLockColumns[$aliasColumn] = array('alias');
				}
			}
		}

		return $parentLockColumns;
	}

	/**
	 * Gets table properties that are lockable
	 *
	 * @return  array
	 */
	public function getTableLockColumns()
	{
		if (is_null($this->tableColumnsForLocking))
		{
			$this->tableColumnsForLocking = array();
			$fields                       = $this->getFields();
			$restrictedColumns            = $this->getTableLockRestrictedColumns();

			// These fields can be locked on the item, they are used for storing multiple values
			if (!empty($this->wsSyncMapFieldsMultiple))
			{
				foreach ($this->wsSyncMapFieldsMultiple as $key => $value)
				{
					$this->tableColumnsForLocking[$key] = $key;
				}
			}

			foreach ($fields as $fieldName => $field)
			{
				if (!in_array($fieldName, $restrictedColumns))
				{
					$this->tableColumnsForLocking[$fieldName] = $fieldName;
				}
			}

			PluginHelper::importPlugin('rb_sync');
			RFactory::getDispatcher()
				->trigger('onRedshopbGetTableLockColumns', array($this));
		}

		return $this->tableColumnsForLocking;
	}

	/**
	 * Unsets data if that column is locked
	 *
	 * @return  void
	 */
	public function loadLockedColumns()
	{
		if (!$this->isLockingSystemEnabled())
		{
			return;
		}

		if (count($this->get('_tbl_keys')) > 1)
		{
			return;
		}

		if (is_null($this->lockedColumns))
		{
			$db                  = Factory::getDbo();
			$query               = $db->getQuery(true)
				->select('tl.*')
				->from($db->qn('#__redshopb_table_lock', 'tl'))
				->where($db->qn('tl.table_name') . ' = ' . $db->q($this->get('_tbl')))
				->where($db->qn('tl.table_id') . ' = ' . $db->q($this->{$this->get('_tbl_key')}));
			$this->lockedColumns = $db->setQuery($query)->loadObjectList('column_name');
		}
	}

	/**
	 * Mark as user override WS data
	 * Calls in afterStore
	 *
	 * @param   boolean  $updateNulls  True to update null values as well.
	 *
	 * @return boolean
	 */
	public function lockTableColumns($updateNulls = false)
	{
		if (!$this->isLockingSystemEnabled())
		{
			return true;
		}

		if (count($this->get('_tbl_keys')) > 1)
		{
			return true;
		}

		$lockingMethod = $this->getTableLockingMethod();
		$priorities    = $this->getLockingPriorities();

		// If current locking method is not defined in priorities then it does not lock the columns
		if (!in_array($lockingMethod, $priorities))
		{
			return true;
		}

		// If load function not executed before
		$this->populateAfterLoadForLocks();

		$columns = $this->getTableLockColumns();
		$changes = array();
		$this->setChangedProperties();

		foreach ($columns as $column)
		{
			if (array_key_exists($column, $this->changedProperties))
			{
				if (!$this->matchValues($this->propertiesAfterLoad[$column], $this->changedProperties[$column]))
				{
					// We wont lock null values if not needed
					if (!$updateNulls && is_null($this->changedProperties[$column]))
					{
						continue;
					}

					$changes[$column] = $column;
				}
			}
		}

		if (!empty($changes))
		{
			$this->loadLockedColumns();

			// First we will update existing rows
			if (!empty($this->lockedColumns))
			{
				foreach ($this->lockedColumns as $column)
				{
					if ($changes[$column->column_name])
					{
						// We need to check if we have higher priority than the existing lock
						if (!$this->isColumnLocked($column->locked_method, $lockingMethod))
						{
							$lockTable = RedshopbTable::getAdminInstance('Table_Lock ');

							if ($lockTable->load($column->id))
							{
								$data = array(
									'locked_method' => $lockingMethod,
									'locked_by'     => '',
									'locked_date'   => '',
								);

								if (!$lockTable->save($data))
								{
									return false;
								}
							}
						}

						unset($changes[$column->column_name]);
					}
				}
			}

			foreach ($changes as $change)
			{
				$lockTable = RedshopbTable::getAdminInstance('Table_Lock ');
				$data      = array(
					'table_name'    => $this->get('_tbl'),
					'table_id'      => $this->{$this->get('_tbl_key')},
					'column_name'   => $change,
					'locked_method' => $lockingMethod,
					'locked_by'     => '',
					'locked_date'   => '',
				);

				if (!$lockTable->save($data))
				{
					return false;
				}
			}

			// We reset locked Columns so that they get loaded again since we added new ones
			$this->lockedColumns = null;
		}

		return true;
	}

	/**
	 * Pupulate afterload for locks
	 *
	 * @return void
	 */
	public function populateAfterLoadForLocks()
	{
		if (!$this->isLockingSystemEnabled())
		{
			return;
		}

		if (count($this->get('_tbl_keys')) > 1)
		{
			return;
		}

		if (empty($this->propertiesAfterLoad))
		{
			if (isset($this->{$this->get('_tbl_key')}))
			{
				$tableKey = $this->{$this->get('_tbl_key')};

				if ($tableKey)
				{
					$cloneTable = clone $this;
					$cloneTable->load($tableKey);
					$this->propertiesAfterLoad = $cloneTable->setPropertiesAfterLoad($cloneTable, false);
				}
			}
		}
	}

	/**
	 * Mark as user override WS data
	 * Calls in afterStore
	 *
	 * @return boolean
	 */
	public function unLockTableColumns()
	{
		if (count($this->get('_tbl_keys')) > 1)
		{
			return true;
		}

		if (!empty($this->aECUnLockedColumns) && !empty($this->{$this->get('_tbl_key')}))
		{
			$lockingTable = RedshopbTable::getAdminInstance('Table_Lock');

			foreach ($this->aECUnLockedColumns as $key => $id)
			{
				$lockingTable->delete($id);
			}

			// We reset locked Columns so that they get loaded again since we deleted some
			$this->lockedColumns = null;
		}

		return true;
	}

	/**
	 * Remove all table data locks
	 *
	 * @return boolean
	 */
	public function removeTableDataLocks()
	{
		if (count($this->get('_tbl_keys')) > 1)
		{
			return true;
		}

		$db            = Factory::getDbo();
		$query         = $db->getQuery(true)
			->select('tl.*')
			->from($db->qn('#__redshopb_table_lock', 'tl'))
			->where($db->qn('tl.table_name') . ' = ' . $db->q($this->get('_tbl')))
			->where($db->qn('tl.table_id') . ' = ' . $db->q($this->{$this->get('_tbl_key')}));
		$lockedColumns = $db->setQuery($query)->loadObjectList('column_name');

		if (!empty($lockedColumns))
		{
			$lockingTable = RedshopbTable::getAdminInstance('Table_Lock');

			foreach ($lockedColumns as $column)
			{
				$lockingTable->delete($column->id);
			}

			// We reset locked Columns so that they get loaded again if needed
			$this->lockedColumns = null;
		}

		return true;
	}

	/**
	 * Set Sync References To Deleted so they wont sync again
	 *
	 * @return boolean
	 * @throws Exception
	 */
	public function setSyncReferencesToDeleted()
	{
		if (!$this->isLockingSystemEnabled())
		{
			return true;
		}

		if (count($this->get('_tbl_keys')) > 1)
		{
			return true;
		}

		$lockingMethod = $this->getTableLockingMethod();
		$priorities    = $this->getLockingPriorities();

		// If current locking method is not defined in priorities then it does not lock the columns
		if (!in_array($lockingMethod, $priorities))
		{
			return true;
		}

		$wsMap = $this->get('wsSyncMapPK');

		if (!empty($wsMap))
		{
			$db = Factory::getDbo();

			foreach ($wsMap as $key => $wsSyncMapFieldsRefs)
			{
				$query = $db->getQuery(true);

				try
				{
					$query
						->update($db->qn('#__redshopb_sync', 's'))
						->set($db->qn('s.deleted') . ' = ' . $db->q('1'))
						->where($db->qn('s.reference') . ' = ' . $db->q($wsSyncMapFieldsRefs))
						->where($db->qn('s.local_id') . ' = ' . $db->q($this->{$this->get('_tbl_key')}));
					$db->setQuery($query)->execute();
				}
				catch (Exception $e)
				{
					Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');
					Log::add(Text::sprintf('REDCORE_ERROR_QUERY', $query->dump()), Log::ERROR, $this->_logPrefix . 'Queries');

					continue;
				}
			}
		}

		return true;
	}

	/**
	 * Called before load().
	 *
	 * @param   mixed    $keys   An optional primary key value to load the row by, or an array of fields to match.  If not
	 *                           set the instance property value is used.
	 * @param   boolean  $reset  True to reset the default values before loading the new row.
	 *
	 * @return  boolean  True if successful. False if row not found.
	 */
	protected function beforeLoad($keys = null, $reset = true)
	{
		$this->lockedColumns = null;

		return parent::beforeLoad($keys, $reset);
	}

	/**
	 * Locking priorities
	 *
	 * @return  array
	 */
	public function getLockingPriorities()
	{
		$priorities = (array) RedshopbApp::getConfig()->get('locking_system_priority', array('Webservice'));

		foreach ($priorities as $key => $priority)
		{
			// If we have multiple priorities of the same level we will add them on the end so we can check for locking method in the priority list
			if ($priority == 'User_Webservice')
			{
				$priorities[] = 'User';
				$priorities[] = 'Webservice';
				break;
			}
		}

		return $priorities;
	}

	/**
	 * Unsets data if that column is locked
	 *
	 * @param   boolean  $updateNulls  True to update null values as well.
	 *
	 * @return  void
	 * @throws Exception
	 */
	public function unsetLockedColumns($updateNulls = false)
	{
		if (!$this->isLockingSystemEnabled())
		{
			$this->lockedColumns = array();

			return;
		}

		// If load function not executed before
		$this->populateAfterLoadForLocks();
		$this->loadLockedColumns();

		if (!empty($this->lockedColumns))
		{
			$lockingMethod  = $this->getTableLockingMethod();
			$lockedByParent = $this->getTableLockParentColumns();
			$lockedColumns  = array();

			foreach ($this->lockedColumns as $column)
			{
				if ($this->isColumnLocked($column->locked_method, $lockingMethod))
				{
					if (isset($this->propertiesAfterLoad[$column->column_name]))
					{
						$this->{$column->column_name} = $this->propertiesAfterLoad[$column->column_name];
					}
					else
					{
						unset($this->{$column->column_name});
					}

					$lockedColumns[] = $column->column_name;

					// Apply dependant columns original values
					if (isset($lockedByParent[$column->column_name]))
					{
						foreach ($lockedByParent[$column->column_name] as $ignoreColumn)
						{
							if (isset($this->propertiesAfterLoad[$ignoreColumn]))
							{
								$this->{$ignoreColumn} = $this->propertiesAfterLoad[$ignoreColumn];
							}
							else
							{
								unset($this->{$ignoreColumn});
							}
						}
					}
				}
			}

			if (!empty($lockedColumns))
			{
				Factory::getApplication()->enqueueMessage(
					Text::sprintf('COM_REDSHOPB_TABLE_LOCK_COLUMNS_LOCKED_MESSAGE', implode(',', $lockedColumns)), 'warning'
				);
			}
		}
	}

	/**
	 * Returns locking method for the current user
	 *
	 * @return  string
	 */
	public function getTableLockingMethod()
	{
		$lockingMethod = $this->getOption('lockingMethod', 'User');

		// For legacy reasons we make this check. In reality this will never happen for the user but it may happen for older sync scripts
		if ($lockingMethod === 'User' && $this->getOption('forceWebserviceUpdate', false) === true)
		{
			$lockingMethod = 'Sync';
		}

		return $lockingMethod;
	}

	/**
	 * Checks to see if the locking system is enabled
	 *
	 * @return  integer
	 */
	public function isLockingSystemEnabled()
	{
		if (!is_null($this->isLockingSystemEnabled))
		{
			return $this->isLockingSystemEnabled;
		}

		return (int) RedshopbApp::getConfig()->get('enable_locking_system', 1);
	}

	/**
	 * Checks if the Column is locked according to locking priorities
	 *
	 * @param   string  $currentMethod     Current Locking Method
	 * @param   string  $contestingMethod  Contesting Locking method
	 *
	 * @return  boolean
	 */
	public function isColumnLocked($currentMethod, $contestingMethod)
	{
		$priorities = $this->getLockingPriorities();

		foreach ($priorities as $priority)
		{
			if (strpos($priority, $contestingMethod) !== false)
			{
				return false;
			}
			elseif (strpos($priority, $currentMethod) !== false)
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * Checks if the Column is locked according to locking priorities
	 *
	 * @param   string  $columnName  Column Name
	 *
	 * @return  boolean
	 */
	public function isTableColumnLocked($columnName)
	{
		if (!$this->isLockingSystemEnabled())
		{
			return false;
		}

		$this->loadLockedColumns();

		if (!empty($this->lockedColumns))
		{
			$lockingMethod = $this->getTableLockingMethod();

			foreach ($this->lockedColumns as $column)
			{
				if ($columnName == $column->column_name)
				{
					if ($this->isColumnLocked($column->locked_method, $lockingMethod))
					{
						return true;
					}

					return false;
				}
			}
		}

		return false;
	}
}
