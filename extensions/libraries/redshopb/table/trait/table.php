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
use Joomla\CMS\Filter\OutputFilter;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Utilities\ArrayHelper;

/**
 * Redshopb Table table trait
 *
 * @package     Aesir.E-Commerce.Library
 * @subpackage  Table
 * @since       1.0
 */
trait RedshopbTableTraitTable
{
	use RedshopbTableTraitLocks;

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
	 * Columns used to generate alias from
	 *
	 * @var  string
	 */
	protected $_aliasColumns = array('name');

	/**
	 * Keys to consider exclusive from the alias (appart from the alias field itself)
	 *
	 * @var  string
	 */
	protected $_aliasKeys = array();

	/**
	 * WS sync mapping
	 *
	 * @var  array
	 */
	protected $wsSyncMapPK = array();

	/**
	 * Sync cascade children deletion
	 *
	 * @var  array
	 */
	protected $syncCascadeChildren = array();

	/**
	 * WS sync mapping for other fields of the model table with other model pks
	 *
	 * @var  array
	 */
	protected $wsSyncMapFields = array();

	/**
	 * WS sync mapping for code fields with other model related data (alias, etc)
	 *
	 * @var  array
	 */
	protected $wsSyncMapCodeFields = array();

	/**
	 * WS sync mapping for other fields of the model table result with other model pks - using array of related ids
	 *
	 * @var  array
	 */
	protected $wsSyncMapFieldsMultiple = array();

	/**
	 * WS sync map of fields from string to boolean or viceversa
	 *
	 * @var  array
	 */
	protected $wsSyncMapBoolean = array();

	/**
	 * WS sync map of date fields to prevent invalid dates
	 *
	 * @var  array
	 */
	protected $wsSyncMapDate = array();

	/**
	 * Table properties after load executed
	 *
	 * @var array
	 */
	protected $propertiesAfterLoad = array();

	/**
	 * Table properties after for override system (API)
	 *
	 * @var array
	 */
	protected $oldWSProperties = array('WSReferences' => array());

	/**
	 * Properties after bind foreign data
	 *
	 * @var array
	 */
	protected $changedProperties = array();

	/**
	 * Flags for overridden
	 *
	 * @var array
	 */
	protected $wsFlags = array();

	/**
	 * @var array
	 */
	protected $dataBeforeBind = array();

	/**
	 * @var array
	 */
	protected $defaultProperties = array();

	/**
	 * Event name to trigger before bind().
	 *
	 * @var  string
	 */
	protected $_eventBeforeBind = 'onBeforeBindRedshopb';

	/**
	 * Flag for checking if we are updating or creating new row on store.
	 *
	 * @var  boolean
	 */
	protected $isNew = false;

	/**
	 * MVC context.
	 *
	 * @var  string
	 */
	protected $context = '';

	/**
	 * Clone magic function
	 *
	 * @return  void
	 */
	public function __clone()
	{
		$this->initDefaultProperties();
	}

	/**
	 * Loads the public properties of a table to get only its data
	 *
	 * @return array
	 */
	public function getTableProperties()
	{
		$vars = call_user_func('get_object_vars', $this);

		foreach ($vars as $key => $value)
		{
			$rp = new ReflectionProperty($this, $key);

			if (!$rp->isPublic())
			{
				unset($vars[$key]);
			}
		}

		return $vars;
	}

	/**
	 * Called before store().
	 *
	 * @param   boolean  $updateNulls  True to update null values as well.
	 *
	 * @return boolean True on success.
	 */
	protected function beforeStore($updateNulls = false)
	{
		if ($this->isLockedByWebservice(null))
		{
			$this->setError(Text::_('COM_REDSHOPB_ERROR_ITEM_RELATED_TO_WEBSERVICE'));

			return false;
		}

		// Import the plugin types
		$this->importPluginTypes();

		// Trigger the event
		$results = RFactory::getDispatcher()
			->trigger('onBeforeBindUserAndWSData', array($this, $updateNulls));

		if (count($results) && in_array(false, $results, true))
		{
			return false;
		}

		$this->unsetLockedColumns($updateNulls);

		if (property_exists($this, 'alias'))
		{
			$this->set('alias', $this->checkGenerateAlias());
		}

		return parent::beforeStore($updateNulls);
	}

	/**
	 * Called after store().
	 *
	 * @param   boolean  $updateNulls  True to update null values as well.
	 *
	 * @return  boolean  True on success.
	 */
	protected function afterStore($updateNulls = false)
	{
		if (property_exists($this, 'path') && method_exists($this, 'rebuildPath'))
		{
			if (!$this->rebuildPath($this->get('id')))
			{
				return false;
			}
		}

		if (!parent::afterStore($updateNulls))
		{
			return false;
		}

		if (!$this->lockTableColumns($updateNulls))
		{
			return false;
		}

		if (!$this->unlockTableColumns())
		{
			return false;
		}

		$this->propertiesAfterLoad = array();

		return true;
	}

	/**
	 * Called after load().
	 *
	 * @param   mixed    $keys   An optional primary key value to load the row by, or an array of fields to match.  If not
	 *                           set the instance property value is used.
	 * @param   boolean  $reset  True to reset the default values before loading the new row.
	 *
	 * @return  boolean  True if successful. False if row not found.
	 */
	protected function afterLoad($keys = null, $reset = true)
	{
		if (!parent::afterLoad($keys, $reset))
		{
			return false;
		}

		$this->setPropertiesAfterLoad();

		return true;
	}

	/**
	 * Logging Error
	 *
	 * @return  void
	 */
	protected function logsError()
	{
		if ($this->getError())
		{
			Log::add($this->getError(), Log::ERROR, 'CRUD');
		}
	}

	/**
	 * Method to check a row out if the necessary properties/fields exist.  To
	 * prevent race conditions while editing rows in a database, a row can be
	 * checked out if the fields 'checked_out' and 'checked_out_time' are available.
	 * While a row is checked out, any attempt to store the row by a user other
	 * than the one who checked the row out should be held until the row is checked
	 * in again.
	 *
	 * @param   integer  $userId  The Id of the user checking out the row.
	 * @param   mixed    $pk      An optional primary key value to check out.  If not set
	 *                            the instance property value is used.
	 *
	 * @return  boolean  True on success.
	 *
	 * @throws  UnexpectedValueException
	 *
	 * @since   11.1
	 */
	public function checkOut($userId, $pk = null)
	{
		// If there is no checked_out or checked_out_time field, just return true.
		if (!property_exists($this, 'checked_out') || !property_exists($this, 'checked_out_time'))
		{
			return true;
		}

		// @todo *bump* Include security layer of canSave | canDelete | canEditState | ...

		if ($this->isLockedByWebservice($pk))
		{
			return true;
		}

		return parent::checkOut($userId, $pk);
	}

	/**
	 * Override the parent checkin method to set checked_out = null instead of 0 so the foreign key doesn't fail.
	 *
	 * @param   mixed  $pk  An optional primary key value to check out.  If not set the instance property value is used.
	 *
	 * @return  boolean  True on success.
	 *
	 * @throws  UnexpectedValueException
	 */
	public function checkIn($pk = null)
	{
		// If there is no checked_out or checked_out_time field, just return true.
		if (!property_exists($this, 'checked_out') || !property_exists($this, 'checked_out_time'))
		{
			return true;
		}

		// @todo *bump* Include security layer of canSave | canDelete | canEditState | ...

		if ($this->isLockedByWebservice($pk))
		{
			return true;
		}

		return parent::checkIn($pk);
	}

	/**
	 * Method to set the publishing state for a row or list of rows in the database
	 * table.  The method respects checked out rows by other users and will attempt
	 * to checkin rows that it can after adjustments are made.
	 *
	 * @param   mixed    $pks     An optional array of primary key values to update.
	 *                            If not set the instance property value is used.
	 * @param   integer  $state   The publishing state. eg. [0 = unpublished, 1 = published]
	 * @param   integer  $userId  The user id of the user performing the operation.
	 *
	 * @return  boolean  True on success; false if $pks is empty.
	 */
	public function publish($pks = null, $state = 1, $userId = 0)
	{
		// @todo *buuump* Include security layer of canSave | canDelete | canEditState | ...

		if ($this->isLockedByWebservice($pks))
		{
			Factory::getApplication()->enqueueMessage(Text::_('COM_REDSHOPB_ERROR_ITEM_RELATED_TO_WEBSERVICE'), 'error');

			return false;
		}

		if (!parent::publish($pks, $state, $userId))
		{
			return false;
		}

		if (!$this->updateWSAfterUpdate($pks, $state, $userId))
		{
			return false;
		}

		return true;
	}

	/**
	 * Is Locked By Webservice
	 *
	 * @param   mixed  $pk  An optional array of primary key values.
	 *
	 * @return boolean
	 */
	public function isLockedByWebservice($pk)
	{
		if (isset($this->{$this->_tbl_key}))
		{
			$tableKey = $this->{$this->_tbl_key};
		}
		else
		{
			$tableKey = null;
		}

		if ($this->getOption('forceWebserviceUpdate', false) == false
			&& $this->isFromWebservice($this->getTableName(), $pk, $tableKey))
		{
			Log::add(Text::sprintf('COM_REDSHOPB_LOGS_ERROR_ITEM_RELATED_TO_WEBSERVICE', $this->getKeysString(), $this->_tbl), Log::ERROR, 'CRUD');

			return true;
		}

		return false;
	}

	/**
	 * Generates a unique alias for the current table using the $this->alias property and the given rules to generate it
	 *
	 * @return  string
	 */
	public function checkGenerateAlias()
	{
		if (!property_exists($this, 'alias'))
		{
			return '';
		}

		$db = Factory::getDbo();

		$aliasColumns   = empty($this->_aliasColumns) ? array('name') : $this->_aliasColumns;
		$keys           = $this->_aliasKeys;
		$inputAlias     = $this->get('alias');
		$parentKey      = property_exists($this, 'entityParentKey') ? $this->entityParentKey : null;
		$parentKeyValue = null;

		if ($parentKey)
		{
			$parentKeyValue = $this->{$parentKey};
		}

		// Store translations state
		$translationsTemp = $db->translate;

		// Disable translations for the execution of this function
		$db->translate = 0;

		$baseAlias = $inputAlias;
		$table     = $this->_tbl;
		$i         = 0;
		$tableId   = $this->get('id', 0);

		if ($baseAlias == '')
		{
			if (!empty($aliasColumns))
			{
				$baseName = array();

				foreach ($aliasColumns as $column)
				{
					if ($this->get($column) != '')
					{
						$baseName[] = $this->get($column);
					}
				}

				$baseAlias = implode('-', $baseName);
			}

			if (trim(str_replace('-', '', $baseAlias)) == '')
			{
				if ($tableId)
				{
					$baseAlias = $table . '-' . $tableId;
				}
				else
				{
					$baseAlias = $table;
				}
			}
		}

		$baseAlias = OutputFilter::stringURLSafe($baseAlias);
		$baseAlias = preg_replace("/[&'#]/", '', $baseAlias);

		$alias = $baseAlias;
		$query = $db->getQuery(true)
			->select($db->qn('id'))
			->from($db->qn($table));

		if ($tableId)
		{
			$query->where($db->qn('id') . ' <> ' . $db->q($tableId));
		}

		if ($parentKey && is_null($parentKeyValue))
		{
			$query->where($db->qn($parentKey) . ' IS NULL');
		}
		elseif ($parentKey)
		{
			$query->where($db->qn($parentKey) . ' = ' . $db->q($parentKeyValue));
		}

		if (!empty($keys))
		{
			foreach ($keys as $key)
			{
				if (is_null($this->get($key)))
				{
					$query->where($db->qn($key) . ' IS NULL');
				}
				else
				{
					$query->where($db->qn($key) . ' = ' . $db->q($this->get($key)));
				}
			}
		}

		$cloneQuery = clone $query;
		$cloneQuery->where($db->qn('alias') . ' = ' . $db->q($alias));

		while (($db->setQuery($cloneQuery, 0, 1)->loadResult()))
		{
			$i++;
			$alias      = $baseAlias . '-' . $i;
			$cloneQuery = clone $query;
			$cloneQuery->where($db->qn('alias') . ' = ' . $db->q($alias));
		}

		// Restore translations setting to its original value
		$db->translate = $translationsTemp;

		return $alias;
	}

	/**
	 * Init Default Class Properties
	 *
	 * @return  void
	 */
	public function initDefaultProperties()
	{
		$fields                  = $this->getFields();
		$this->defaultProperties = array();
		$this->context           = strtolower(str_replace('RedshopbTable', '', get_class($this)));

		foreach ($fields as $fieldName => $field)
		{
			if (property_exists($this, $fieldName) && !is_null($this->{$fieldName}))
			{
				$this->defaultProperties[$fieldName] = $this->{$fieldName};
			}
			else
			{
				$this->defaultProperties[$fieldName] = $field->Default;
			}
		}

		PluginHelper::importPlugin('rb_sync');
		RFactory::getDispatcher()
			->trigger('onRedshopbEnrichWsSyncMapPK', array($this));
	}

	/**
	 * Method to bind an associative array or object to the Table instance.This
	 * method only binds properties that are publicly accessible and optionally
	 * takes an array of properties to ignore when binding.
	 *
	 * @param   mixed  $src     An associative array or object to bind to the Table instance.
	 * @param   mixed  $ignore  An optional array or space separated list of properties to ignore while binding.
	 *
	 * @return  boolean  True on success.
	 *
	 * @throws  InvalidArgumentException
	 */
	public function bind($src, $ignore = array())
	{
		if (!$this->webserviceBind($src, $ignore))
		{
			return false;
		}

		return parent::bind($src, $ignore);
	}

	/**
	 * Method to reset class properties to the defaults set in the class
	 * definition. It will ignore the primary key as well as any private class
	 * properties (except $_errors).
	 *
	 * @return  void
	 */
	public function reset()
	{
		$this->resetWebserviceProperties();

		parent::reset();
	}

	/**
	 * Check is foreign key exists
	 *
	 * @param   string  $propKey  Property name
	 *
	 * @return  boolean
	 */
	public function foreignIdExists($propKey)
	{
		$foreignKeys = $this->getForeignKeys();

		if (!array_key_exists($propKey, $foreignKeys))
		{
			return false;
		}

		$db     = Factory::getDbo();
		$query  = $db->getQuery(true)
			->select($db->qn($foreignKeys[$propKey]->REFERENCED_COLUMN_NAME))
			->from($db->qn($foreignKeys[$propKey]->REFERENCED_TABLE_NAME))
			->where($db->qn($foreignKeys[$propKey]->REFERENCED_COLUMN_NAME) . ' = ' . $db->q($this->{$propKey}));
		$result = $db->setQuery($query, 0, 1)
			->loadResult();

		if ($result)
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Check values are match or not
	 *
	 * @param   mixed  $value1  First value
	 * @param   mixed  $value2  Second value
	 *
	 * @return boolean
	 */
	protected function matchValues($value1, $value2)
	{
		if (is_array($value1)
			&& is_array($value2))
		{
			$value1 = json_decode(json_encode($value1), true);
			$value2 = json_decode(json_encode($value2), true);

			if (count(array_diff($value1, $value2)) > 0
				|| count(array_diff($value2, $value1)) > 0)
			{
				return false;
			}
		}
		else
		{
			if ($value1 != $value2)
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * Load webservice relation
	 *
	 * @param   int  $pk  Id item
	 *
	 * @return  boolean
	 */
	public function loadWebserviceRelation($pk = null)
	{
		if (empty($this->wsSyncMapPK) || count($this->_tbl_keys) > 1)
		{
			return false;
		}

		$db         = Factory::getDbo();
		$references = array();

		foreach ($this->wsSyncMapPK as $wsSyncMapPK)
		{
			foreach ($wsSyncMapPK as $reference)
			{
				$references[] = $db->q($reference);
			}
		}

		if (!$pk)
		{
			$pk = $this->{$this->_tbl_key};
		}

		if (empty($references) || !$pk)
		{
			return false;
		}

		$query = $db->getQuery(true)
			->select('*')
			->from($db->qn('#__redshopb_sync'))
			->where('reference IN (' . implode(',', $references) . ')')
			->where('local_id = ' . $db->q($pk))
			->where('main_reference = 1');

		$syncData = $db->setQuery($query)
			->loadObjectList();

		// Import the plugin types
		$this->importPluginTypes();

		// Trigger the event
		$results = RFactory::getDispatcher()
			->trigger('onLoadWebserviceRelation', array(&$this, &$syncData, $pk));

		if (count($results) && in_array(false, $results, true))
		{
			return false;
		}

		if (!$syncData)
		{
			return false;
		}

		return true;
	}

	/**
	 * Method to bind an associative array or object to the Table instance.This
	 * method only binds properties that are publicly accessible and optionally
	 * takes an array of properties to ignore when binding.
	 *
	 * @param   mixed  $src     An associative array or object to bind to the Table instance.
	 * @param   mixed  $ignore  An optional array or space separated list of properties to ignore while binding.
	 *
	 * @return  boolean  True on success.
	 *
	 * @throws  InvalidArgumentException
	 */
	public function webserviceBind($src, $ignore = array())
	{
		$this->dataBeforeBind = $src;

		if ($this->_eventBeforeBind)
		{
			// Import the plugin types
			$this->importPluginTypes();

			// Trigger the event
			$results = RFactory::getDispatcher()
				->trigger($this->_eventBeforeBind, array(&$this, $src, $ignore));

			if (count($results) && in_array(false, $results, true))
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * Import the plugin types.
	 *
	 * @return  void
	 */
	protected function importPluginTypes()
	{
		foreach ($this->_pluginTypesToImport as $type)
		{
			PluginHelper::importPlugin($type);
		}
	}

	/**
	 * Method to set the publishing state for a row or list of rows in the database
	 * table. The method respects checked out rows by other users and will attempt
	 * to checkin rows that it can after adjustments are made.
	 *
	 * @param   mixed    $pks     An optional array of primary key values to update.
	 * If not set the instance property value is used.
	 * @param   integer  $state   The publishing state. eg. [0 = unpublished, 1 = published]
	 * @param   integer  $userId  The user id of the user performing the operation.
	 *
	 * @return boolean True on success; false if $pks is empty.
	 */
	public function updateWSAfterUpdate($pks = null, $state = 1, $userId = 0)
	{
		return true;
	}

	/**
	 * Method to reset webservice properties
	 *
	 * @return  void
	 */
	public function resetWebserviceProperties()
	{
		$this->propertiesAfterLoad = array();
		$this->changedProperties   = array();
		$this->wsFlags             = array();
		$this->dataBeforeBind      = array();
		$this->lockedColumns       = null;
		$this->initDefaultProperties();
	}

	/**
	 * Search sync table and return true if item is fetched from webservice
	 *
	 * @param   string  $tableName          Table name is used as a cross reference with customer named web services
	 * @param   mixed   $pk                 An optional primary key value
	 * @param   mixed   $defaultPrimaryKey  Default primary key on the table object
	 *
	 * @return  boolean  True on success.
	 */
	public function isFromWebservice($tableName = '', $pk = null, $defaultPrimaryKey = null)
	{
		// We support only one-primary-key tables so far
		if (count($this->_tbl_keys) > 1)
		{
			return false;
		}

		PluginHelper::importPlugin('rb_sync');

		if (empty($tableName))
		{
			$tableName = $this->getTableName();
		}

		if (empty($defaultPrimaryKey) && isset($this->{$this->_tbl_key}))
		{
			$defaultPrimaryKey = $this->{$this->_tbl_key};
		}

		// Trigger the event
		$results = RFactory::getDispatcher()
			->trigger('isFromWebservice', array($tableName, $pk, $defaultPrimaryKey));

		if (count($results) && in_array(true, $results, true))
		{
			return true;
		}

		$pk     = (is_null($pk)) ? $defaultPrimaryKey : $pk;
		$entity = str_replace('#__redshopb_', '', $tableName);

		// If no primary key is given, return false.
		if (empty($pk))
		{
			return false;
		}

		$pk               = (array) $pk;
		$lockedReferences = array();

		// Trigger the event
		RFactory::getDispatcher()
			->trigger('onGetLockedSyncEntities', array($entity, &$lockedReferences, 'redshopb'));

		if (!empty($lockedReferences))
		{
			$db    = Factory::getDbo();
			$query = $db->getQuery(true)
				->select('local_id')
				->from($db->qn('#__redshopb_sync'))
				->where('reference IN (' . implode(',', RHelperArray::quote($lockedReferences)) . ')')
				->where('local_id IN (' . implode(',', RHelperArray::quote($pk)) . ')');

			if ($db->setQuery($query, 0, 1)->loadResult())
			{
				return true;
			}
		}

		// We gonna check if parent entity has lock status, 'order' table is the exception
		if (!empty($this->wsSyncMapFields)
			&& $this->getTableName() != '#__redshopb_order')
		{
			$clonedTable = clone $this;

			foreach ($this->wsSyncMapFields as $mapField => $mapFieldData)
			{
				$fieldModel = RedshopbModel::getFrontInstance($mapFieldData['model']);

				foreach ($pk as $id)
				{
					if (empty($id)
						|| !$clonedTable->load($id)
						|| empty($clonedTable->get($mapField)))
					{
						continue;
					}

					/** @var   RedshopbTable   $table */
					$table = $fieldModel->getTable();

					if ($table->isLockedByWebservice($clonedTable->get($mapField)))
					{
						return true;
					}
				}
			}
		}

		return false;
	}

	/**
	 * Get Foreign keys
	 *
	 * @return boolean|array
	 */
	public function getForeignKeys()
	{
		static $foreignKeys = false;

		if ($foreignKeys === false)
		{
			$conf  = Factory::getConfig();
			$db    = Factory::getDbo();
			$query = $db->getQuery(true)
				->select('k.COLUMN_NAME, k.REFERENCED_TABLE_NAME, k.REFERENCED_COLUMN_NAME')
				->from($db->qn('information_schema.KEY_COLUMN_USAGE', 'k'))
				->where('k.REFERENCED_TABLE_SCHEMA = ' . $db->q($conf->get('db')))
				->where('k.REFERENCED_TABLE_NAME IS NOT NULL')
				->where('k.TABLE_NAME = ' . $db->q(str_replace('#__', $db->getPrefix(), $this->getTableName())));

			$results = $db->setQuery($query)
				->loadObjectList('COLUMN_NAME');

			if ($results)
			{
				$foreignKeys = $results;
			}
			else
			{
				$foreignKeys = array();
			}
		}

		return $foreignKeys;
	}

	/**
	 * Method to get a flat array of all references in the wsSyncMapPk array
	 *
	 * @return array
	 */
	public function getAllWsSyncMapPk()
	{
		$syncMapPk = array();

		foreach ($this->wsSyncMapPK AS $mapPks)
		{
			foreach ($mapPks AS $mapPk)
			{
				if (!in_array($mapPk, $syncMapPk))
				{
					$syncMapPk[] = $mapPk;
				}
			}
		}

		return $syncMapPk;
	}

	/**
	 * Set changed properties on the table object
	 *
	 * @param   object  $table        Table object
	 * @param   bool    $setProperty  Set property on the Table object
	 *
	 * @return  mixed
	 */
	public function setChangedProperties($table = null, $setProperty = true)
	{
		$table             = is_null($table) ? $this : $table;
		$changedProperties = call_user_func('get_object_vars', $table);
		$this->removeRestrictedProperties($changedProperties);

		if ($setProperty)
		{
			$this->changedProperties = $changedProperties;
		}

		return $changedProperties;
	}

	/**
	 * Set properties after load on the table object
	 *
	 * @param   object  $table        Table object
	 * @param   bool    $setProperty  Set property on the Table object
	 *
	 * @return  mixed
	 */
	public function setPropertiesAfterLoad($table = null, $setProperty = true)
	{
		$table               = is_null($table) ? $this : $table;
		$propertiesAfterLoad = call_user_func('get_object_vars', $table);
		$this->removeRestrictedProperties($propertiesAfterLoad);

		if ($setProperty)
		{
			$this->propertiesAfterLoad = $propertiesAfterLoad;
		}

		return $propertiesAfterLoad;
	}

	/**
	 * Set properties after load on the table object
	 *
	 * @param   mixed  $property  Property
	 *
	 * @return  void
	 */
	public function removeRestrictedProperties(&$property)
	{
		foreach ($property as $name => $item)
		{
			// Delete system properties
			if (($name[0] == '_' && !array_key_exists($name, $this->defaultProperties))
				|| in_array(
					$name, array(
						'defaultProperties', 'dataBeforeBind', 'changedProperties', 'wsFlags', 'oldWSProperties', 'wsSyncMapDate',
						'wsSyncMapBoolean', 'wsSyncMapFieldsMultiple', 'wsSyncMapCodeFields', 'wsSyncMapFields', 'syncCascadeChildren',
						'wsSyncMapPK', 'propertiesAfterLoad'
					)
				))
			{
				unset($property[$name]);
			}
		}
	}

	/**
	 * Get keys as string
	 *
	 * @param   null  $keys  Keys
	 *
	 * @return  string
	 */
	protected function getKeysString($keys = null)
	{
		$keysString = array();

		if (empty($keys))
		{
			$empty = true;
			$keys  = array();

			foreach ($this->_tbl_keys as $key)
			{
				$empty      = $empty && empty($this->$key);
				$keys[$key] = $this->$key;
			}

			if ($empty)
			{
				return Text::_('COM_REDSHOPB_LOGS_EMPTY_KEY');
			}
		}
		elseif (!is_array($keys))
		{
			$keyCount = count($this->_tbl_keys);

			if ($keyCount == 1)
			{
				$keys = array($this->getKeyName() => $keys);
			}
			else
			{
				return Text::_('COM_REDSHOPB_LOGS_EMPTY_KEY');
			}
		}

		foreach ($keys as $key => $keyValue)
		{
			$keysString[] = $key . ' = ' . $keyValue;
		}

		return Text::sprintf('COM_REDSHOPB_LOGS_KEYS_VALUES', implode(Text::_('COM_REDSHOPB_LOGS_KEYS_VALUES_COMBINE'), $keysString));
	}

	/**
	 * Get the name of the current entity type
	 *
	 * @return  string
	 */
	public function getInstanceName()
	{
		$class = get_class($this);
		$name  = strstr($class, 'Table');
		$name  = str_replace('Table', '', $name);

		return strtolower($name);
	}

	/**
	 * Method to delete erp sync records
	 *
	 * @param   mixed    $localIds  array or comma delimited string of record ids
	 * @param   boolean  $children  True to delete child nodes, false to move them up a level or cause error when using deleted field
	 *
	 * @return boolean
	 */
	protected function deleteErpRecords($localIds, $children = true)
	{
		$noSync              = empty($this->wsSyncMapPK);
		$noCascadingChildren = empty($this->syncCascadeChildren);

		if ($noSync && $noCascadingChildren)
		{
			return true;
		}

		if (is_array($localIds))
		{
			// Sanitize input.
			$localIds = implode(
				',',
				RHelperArray::quote(
					ArrayHelper::toInteger($localIds)
				)
			);
		}

		$isNestedClass = (stripos(get_parent_class($this), 'nested') === false ? false : true);

		if ($isNestedClass && $children && !empty($localIds))
		{
			$db    = Factory::getDbo();
			$query = $db->getQuery(true)
				->select('c.id')
				->from($db->qn($this->_tbl, 'c'))
				->leftJoin($db->qn($this->_tbl, 'cp') . ' ON c.lft BETWEEN cp.lft AND cp.rgt')
				->where($db->qn('cp.id') . ' IN (' . $localIds . ')')
				->where('c.id NOT IN (' . $localIds . ')');

			$localIds = implode(',', array_merge(explode(',', $localIds), $db->setQuery($query)->loadColumn()));
		}

		$references = array();
		$conditions = array();
		$entities   = array();

		if (!$noSync)
		{
			$references[] = $this->getAllWsSyncMapPk();
			$conditions[] = $localIds;
			$entities[]   = $this->getInstanceName();
		}

		foreach ($this->syncCascadeChildren AS $table => $allConditions)
		{
			$extraConditions = array();
			$extraJoins      = array();

			if (is_array($allConditions) && isset($allConditions['_key']))
			{
				$foreignKey      = $allConditions['_key'];
				$extraConditions = (isset($allConditions['_conditions']) ? $allConditions['_conditions'] : '');
				$extraJoins      = (isset($allConditions['_extrajoins']) ? $allConditions['_extrajoins'] : '');
			}
			else
			{
				$foreignKey = $allConditions;
			}

			/** @var   RedshopbTable   $childTable */
			$childTable = RedshopbTable::getAdminInstance($table);

			$entities[]   = $childTable->getInstanceName();
			$references[] = $childTable->getAllWsSyncMapPk();
			$conditions[] = $this->getChildConditions($childTable, $foreignKey, $localIds, $extraConditions, $extraJoins);
		}

		/** @var RedshopbTableSync $syncTable */
		$syncTable = RedshopbTable::getAdminInstance('sync');

		if (!$syncTable->deleteSyncRecords($references, $conditions))
		{
			$this->setError('Sync records error: ' . $syncTable->getError());

			return false;
		}

		return true;
	}

	/**
	 * Method to load a row from the database by primary key and bind the fields
	 * to the Table instance properties.
	 *
	 * @param   mixed    $keys   An optional primary key value to load the row by, or an array of fields to match.  If not
	 *                           set the instance property value is used.
	 * @param   boolean  $reset  True to reset the default values before loading the new row.
	 *
	 * @return  boolean  True if successful. False if row not found.
	 */
	public function load($keys = null, $reset = true)
	{
		// Load
		if (!parent::load($keys, $reset))
		{
			$this->logsError();

			return false;
		}

		Log::add(Text::sprintf('COM_REDSHOPB_LOGS_AFTER_LOAD', $this->getKeysString($keys), $this->_tbl), Log::INFO, 'CRUD');

		return true;
	}

	/**
	 * Method to get a conditional query that returns all ids for a child that has a foreign key in the local ids
	 *
	 * @param   RedshopbTable  $childTable       Table instance
	 * @param   mixed          $foreignKeys      Array or string containing the name of the foreign key
	 * @param   string         $localIds         Array or comma delimited string of record ids
	 * @param   array          $extraConditions  Array of extra conditions to perform in the child table
	 * @param   array          $extraJoins       Array of extra joins to perform on the query
	 *
	 * @return JDatabaseQuery
	 */
	private function getChildConditions($childTable, $foreignKeys, $localIds, $extraConditions = array(), $extraJoins = array())
	{
		if (!is_array($foreignKeys))
		{
			$foreignKeys = array($foreignKeys);
		}

		$db    = $this->getDbo();
		$query = $db->getQuery(true);
		$query->select($db->qn('_table.' . $childTable->getKeyName()))
			->from($db->qn($childTable->getTableName(), '_table'));

		if (empty($localIds))
		{
			$localIds = $db->q('');
		}

		if (is_array($extraConditions) && !empty($extraConditions))
		{
			foreach ($extraConditions as $extraKey => $extraCondition)
			{
				$query->where($db->qn($extraKey) . ' = ' . $db->q($extraCondition));
			}
		}

		if (is_array($extraJoins) && !empty($extraJoins))
		{
			foreach ($extraJoins as $extraTable => $extraJoin)
			{
				$query->join('inner', $db->qn('#__redshopb_' . $extraTable, $extraTable) . ' ON ' . $extraJoin);
			}
		}

		if (!empty($foreignKeys))
		{
			$whereArray = array();

			foreach ($foreignKeys as $foreignKey)
			{
				$whereArray[] = $db->qn($foreignKey) . ' IN (' . $localIds . ')';
			}

			$query->where('(' . implode(' OR', $whereArray) . ')');
		}

		return $query;
	}
}
