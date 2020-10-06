<?php
/**
 * @package     Aesir.E-Commerce.Library
 * @subpackage  Base
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Access\Rules;

/**
 * Redshopb Base Table
 *
 * @package     Aesir.E-Commerce
 * @subpackage  Base
 * @since       1.0
 */
class RedshopbTableNested extends RTableNested
{
	use RedshopbTableTraitTable;

	/**
	 * Event name to trigger before store().
	 *
	 * @var  string
	 */
	protected $_eventBeforeStore = 'onBeforeStoreRedshopb';

	/**
	 * Event name to trigger after store().
	 *
	 * @var  string
	 */
	protected $_eventAfterStore = 'onAfterStoreRedshopb';

	/**
	 * An array of plugin types to import.
	 *
	 * @var  array
	 */
	protected $_pluginTypesToImport = array('rb_sync', 'kvasir_sync');

	/**
	 * Event name to trigger before delete().
	 *
	 * @var  string
	 */
	protected $_eventBeforeDelete = 'onBeforeDeleteRedshopb';

	/**
	 * Event name to trigger after load().
	 *
	 * @var  string
	 */
	protected $_eventAfterLoad = 'onAfterLoadRedshopb';

	/**
	 * Event name to trigger after delete().
	 *
	 * @var  string
	 */
	protected $_eventAfterDelete = 'onAfterDeleteRedshopb';

	/**
	 * Object property to hold the location type to use when storing the row.
	 * Possible values are: ['before', 'after', 'first-child', 'last-child'].
	 *
	 * @var    string
	 * @since  11.1
	 */
	protected $_location = 'last-child';

	/**
	 * Constructor
	 *
	 * @param   JDatabase  $db  A database connector object
	 *
	 * @throws  UnexpectedValueException
	 */
	public function __construct(&$db)
	{
		parent::__construct($db);

		$this->initDefaultProperties();

		// Add any additional configuration to the table constructor
		RFactory::getDispatcher()->trigger('onRedshopbTableConstruct', array($this, &$db));
	}

	/**
	 * Called before delete().
	 *
	 * @param   integer  $pk        The primary key of the node to delete.
	 * @param   boolean  $children  True to delete child nodes, false to move them up a level.
	 *
	 * @return  boolean  True on success.
	 */
	protected function beforeDelete($pk = null, $children = true)
	{
		// @todo *bump* Include security layer of canSave | canDelete | canEditState | ...

		if ($this->isLockedByWebservice($pk))
		{
			Factory::getApplication()->enqueueMessage(Text::_('COM_REDSHOPB_ERROR_ITEM_RELATED_TO_WEBSERVICE'), 'error');

			return false;
		}

		return parent::beforeDelete($pk, $children);
	}

	/**
	 * Method to store a node in the database table.
	 *
	 * @param   boolean  $updateNulls  True to update null values as well.
	 *
	 * @return  boolean  True on success.
	 */
	public function store($updateNulls = false)
	{
		Log::add(Text::sprintf('COM_REDSHOPB_LOGS_BEFORE_STORE', $this->getKeysString(), $this->_tbl), Log::INFO, 'CRUD');

		// Before store
		if (!$this->beforeStore($updateNulls))
		{
			$this->logsError();

			return false;
		}

		$key = $this->_tbl_key;

		// Implement JObservableInterface: Pre-processing by observers
		// 2.5 upgrade issue - check if property_exists before executing
		if (property_exists($this, '_observers'))
		{
			$this->_observers->update('onBeforeStore', array($updateNulls, $key));
		}

		// @codeCoverageIgnoreStart
		if ($this->_debug)
		{
			echo "\n" . get_class($this) . "::store\n";
			$this->_logtable(true, false);
		}

		// @codeCoverageIgnoreEnd

		/*
		 * If the primary key is empty, then we assume we are inserting a new node into the
		 * tree.  From this point we would need to determine where in the tree to insert it.
		 */
		if (empty($this->{$key}))
		{
			/*
			 * We are inserting a node somewhere in the tree with a known reference
			 * node.  We have to make room for the new node and set the left and right
			 * values before we insert the row.
			 */
			if ($this->_location_id >= 0)
			{
				// Lock the table for writing.
				if (!$this->_lock())
				{
					$this->logsError();

					// Error message set in lock method.
					return false;
				}

				// We are inserting a node relative to the last root node.
				if ($this->_location_id == 0)
				{
					// Get the last root node as the reference node.
					$query = $this->_db->getQuery(true)
						->select($this->_tbl_key . ', parent_id, level, lft, rgt')
						->from($this->_tbl)
						->where('parent_id IS NULL')
						->order('lft DESC');
					$this->_db->setQuery($query, 0, 1);
					$reference = $this->_db->loadObject();

					// @codeCoverageIgnoreStart
					if ($this->_debug)
					{
						$this->_logtable(false);
					}

					// @codeCoverageIgnoreEnd
				}
				// We have a real node set as a location reference.
				else
				{
					// Get the reference node by primary key.
					$reference = $this->_getNode($this->_location_id);

					if (!$reference)
					{
						// Error message set in getNode method.
						$this->_unlock();
						$this->logsError();

						return false;
					}
				}

				// Get the reposition data for shifting the tree and re-inserting the node.
				$repositionData = $this->_getTreeRepositionData($reference, 2, $this->_location);

				if (!$repositionData)
				{
					// Error message set in getNode method.
					$this->_unlock();
					$this->logsError();

					return false;
				}

				// Create space in the tree at the new location for the new node in left ids.
				$query = $this->_db->getQuery(true)
					->update($this->_tbl)
					->set('lft = lft + 2')
					->where($repositionData->left_where);
				$this->_runQuery($query, 'JLIB_DATABASE_ERROR_STORE_FAILED');

				// Create space in the tree at the new location for the new node in right ids.
				$query->clear()
					->update($this->_tbl)
					->set('rgt = rgt + 2')
					->where($repositionData->right_where);
				$this->_runQuery($query, 'JLIB_DATABASE_ERROR_STORE_FAILED');

				// Set the object values.
				$this->parent_id = $repositionData->new_parent_id;
				$this->level     = $repositionData->new_level;
				$this->lft       = $repositionData->new_lft;
				$this->rgt       = $repositionData->new_rgt;
			}
			else
			{
				// Negative parent ids are invalid
				$ex = new UnexpectedValueException(sprintf('%s::store() used a negative _location_id', get_class($this)));
				$this->setError($ex);
				$this->logsError();

				return false;
			}
		}
		/*
		 * If we have a given primary key then we assume we are simply updating this
		 * node in the tree.  We should assess whether or not we are moving the node
		 * or just updating its data fields.
		 */
		else
		{
			// If the location has been set, move the node to its new location.
			if ($this->_location_id > 0)
			{
				if (!$this->moveByReference($this->_location_id, $this->_location, $this->{$key}))
				{
					$this->logsError();

					// Error message set in move method.
					return false;
				}
			}

			// Lock the table for writing.
			if (!$this->_lock())
			{
				$this->logsError();

				// Error message set in lock method.
				return false;
			}
		}

		$currentAssetId = 0;

		if (!empty($this->asset_id))
		{
			$currentAssetId = $this->asset_id;
		}

		// The asset id field is managed privately by this class.
		if ($this->_trackAssets)
		{
			unset($this->asset_id);
		}

		// Before store but with located position (parent_id)
		if (!$this->beforeStoreLocated($updateNulls))
		{
			if ($this->_locked)
			{
				$this->_unlock();
			}

			$this->logsError();

			return false;
		}

		// If a primary key exists update the object, otherwise insert it.
		if ($this->hasPrimaryKey())
		{
			$result = $this->_db->updateObject($this->_tbl, $this, $this->_tbl_keys, $updateNulls);
		}
		else
		{
			$result = $this->_db->insertObject($this->_tbl, $this, $this->_tbl_keys[0]);
		}

		// If the table is not set to track assets return true.
		if ($this->_trackAssets)
		{
			if ($this->_locked)
			{
				$this->_unlock();
			}

			/*
			 * Asset Tracking
			 */
			$parentId = $this->_getAssetParentId();
			$name     = $this->_getAssetName();
			$title    = $this->_getAssetTitle();

			$asset = self::getInstance('Asset', 'JTable', array('dbo' => $this->getDbo()));
			$asset->loadByName($name);

			// Re-inject the asset id.
			$this->asset_id = $asset->id;

			// Check for an error.
			$error = $asset->getError();

			if ($error)
			{
				$this->setError($error);
				$this->logsError();

				return false;
			}
			else
			{
				// Specify how a new or moved node asset is inserted into the tree.
				if (empty($this->asset_id) || $asset->parent_id != $parentId)
				{
					$asset->setLocation($parentId, 'last-child');
				}

				// Prepare the asset to be stored.
				$asset->parent_id = $parentId;
				$asset->name      = $name;
				$asset->title     = $title;

				if ($this->_rules instanceof Rules)
				{
					$asset->rules = (string) $this->_rules;
				}

				if (!$asset->check() || !$asset->store(false))
				{
					$this->setError($asset->getError());
					$this->logsError();

					return false;
				}
				else
				{
					// Create an asset_id or heal one that is corrupted.
					if (empty($this->asset_id) || ($currentAssetId != $this->asset_id && !empty($this->asset_id)))
					{
						// Update the asset_id field in this table.
						$this->asset_id = (int) $asset->id;

						$query = $this->_db->getQuery(true)
							->update($this->_db->quoteName($this->_tbl))
							->set('asset_id = ' . (int) $this->asset_id);
						$this->appendPrimaryKeys($query);
						$this->_db->setQuery($query)->execute();
					}
				}
			}
		}

		if ($result)
		{
			// @codeCoverageIgnoreStart
			if ($this->_debug)
			{
				$this->_logtable();
			}

			// @codeCoverageIgnoreEnd
		}

		// Unlock the table for writing.
		$this->_unlock();

		// Implement JObservableInterface: Post-processing by observers
		// 2.5 upgrade issue - check if property_exists before executing
		if (property_exists($this, '_observers'))
		{
			$this->_observers->update('onAfterStore', array(&$result));
		}

		// After store
		if (!$this->afterStore($updateNulls))
		{
			$this->logsError();

			return false;
		}

		if (!$result)
		{
			$this->logsError();
			Log::add(Text::sprintf('COM_REDSHOPB_LOGS_ERROR_STORE', $this->getKeysString(), $this->_tbl), Log::ERROR, 'CRUD');
		}
		else
		{
			Log::add(Text::sprintf('COM_REDSHOPB_LOGS_AFTER_STORE', $this->getKeysString(), $this->_tbl), Log::INFO, 'CRUD');
		}

		return $result;
	}

	/**
	 * Called before updating the database, but after the record has been located - with parent_id.
	 *
	 * @param   boolean  $updateNulls  True to update null values as well.
	 *
	 * @return  boolean  True on success.
	 */
	protected function beforeStoreLocated($updateNulls = false)
	{
		if (property_exists($this, 'alias'))
		{
			$this->alias = $this->checkGenerateAlias();
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
	 * Method to delete a node and, optionally, its child nodes from the table.
	 *
	 * @param   integer  $pk        The primary key of the node to delete.
	 * @param   boolean  $children  True to delete child nodes, false to move them up a level or cause error when using deleted field
	 *
	 * @return  boolean  True on success.
	 */
	public function delete($pk = null, $children = true)
	{
		// Load the specified record (if necessary)
		if (!empty($pk))
		{
			$this->load($pk);
		}

		Log::add(Text::sprintf('COM_REDSHOPB_LOGS_BEFORE_DELETE', $this->getKeysString($this->id), $this->_tbl), Log::INFO, 'CRUD');

		// If a property "deleted" exists in the table, just sets it to 1 and executes before/after actions here
		if (property_exists($this, 'deleted'))
		{
			return $this->simulatedDelete($pk, $children);
		}

		$db = $this->getDbo();
		$db->transactionStart();

		if (!$this->deleteErpRecords($pk, $children))
		{
			$db->transactionRollback();

			return false;
		}

		if (!parent::delete($pk, $children))
		{
			$db->transactionRollback();
			$this->logsError();

			return false;
		}

		$db->transactionCommit();

		Log::add(Text::sprintf('COM_REDSHOPB_LOGS_AFTER_DELETE', $this->getKeysString($this->id), $this->_tbl), Log::INFO, 'CRUD');

		return true;
	}

	/**
	 * Method to simulate a deletion by setting the delete flag to 1
	 *
	 * @param   mixed    $pk        An optional primary key value to delete.  If not set the instance property value is used.
	 * @param   boolean  $children  True to delete child nodes, false to move them up a level or cause error when using deleted field
	 *
	 * @return boolean
	 */
	protected function simulatedDelete($pk = null, $children = true)
	{
		$db = $this->getDbo();
		$db->transactionStart();

		// Dealing with children records
		$childrenTable     = clone $this;
		$childrenTable->id = null;
		$childrenTable->reset();

		while ($childrenTable->load(array('parent_id' => $this->id, 'deleted' => 0)))
		{
			if ($children)
			{
				$childrenTable->delete($childrenTable->id, $children);
			}
			else
			{
				$this->setError(Text::_('COM_REDSHOPB_DELETE_CHILDREN_EXIST'));
				$this->logsError();

				return false;
			}

			$childrenTable->id = null;
			$childrenTable->reset();
		}

		// Before delete
		if (!$this->beforeDelete($pk))
		{
			$db->transactionRollback();
			$this->logsError();

			return false;
		}

		if (!$this->deleteErpRecords($pk, $children))
		{
			$db->transactionRollback();

			return false;
		}

		// Disables post-actions when saving the record again to avoid post-processing for seting the deleted field
		$this->setOption('disableOnBeforeRedshopb', true);

		if (!$this->save(array('deleted' => 1)))
		{
			$db->transactionRollback();
			$this->logsError();

			return false;
		}

		// After delete
		if (!$this->afterDelete($pk))
		{
			$db->transactionRollback();
			$this->logsError();

			return false;
		}

		$db->transactionCommit();

		Log::add(Text::sprintf('COM_REDSHOPB_LOGS_AFTER_SIMULATED_DELETE', $this->getKeysString($pk), $this->_tbl), Log::INFO, 'CRUD');

		return true;
	}

	/**
	 * Called after delete().
	 *
	 * @param   integer  $pk        The primary key of the node to delete.
	 * @param   boolean  $children  True to delete child nodes, false to move them up a level.
	 *
	 * @return  boolean  True on success.
	 */
	protected function afterDelete($pk = null, $children = true)
	{
		if (!parent::afterDelete($pk, $children))
		{
			$this->logsError();
			Log::add(Text::sprintf('COM_REDSHOPB_LOGS_ERROR_DELETE', $this->getKeysString(), $this->_tbl), Log::ERROR, 'CRUD');

			return false;
		}

		$this->setSyncReferencesToDeleted();
		$this->removeTableDataLocks();

		return true;
	}
}
