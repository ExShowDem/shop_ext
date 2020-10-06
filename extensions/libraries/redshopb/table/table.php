<?php
/**
 * @package     Aesir.E-Commerce.Library
 * @subpackage  Base
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Log\Log;

/**
 * Redshopb Base Table
 *
 * @package     Aesir.E-Commerce
 * @subpackage  Base
 * @since       1.0
 */
class RedshopbTable extends RTable
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
	 * Constructor
	 *
	 * @param   JDatabase  $db  A database connector object
	 *
	 * @throws  UnexpectedValueException
	 */
	public function __construct(&$db)
	{
		parent::__construct($db);

		// Always import plugin group vanir
		PluginHelper::importPlugin('vanir');

		$this->initDefaultProperties();

		// Add any additional configuration to the table constructor
		RFactory::getDispatcher()->trigger('onRedshopbTableConstruct', array($this, &$db));
	}

	/**
	 * Called before delete().
	 *
	 * @param   mixed  $pk  An optional primary key value to delete. If not set the instance property value is used.
	 *
	 * @return boolean True on success.
	 */
	protected function beforeDelete($pk = null)
	{
		if ($this->isLockedByWebservice($pk))
		{
			Factory::getApplication()->enqueueMessage(Text::_('COM_REDSHOPB_ERROR_ITEM_RELATED_TO_WEBSERVICE'), 'error');

			return false;
		}

		return parent::beforeDelete($pk);
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
		$this->isNew = !$this->hasPrimaryKey();

		Log::add(Text::sprintf('COM_REDSHOPB_LOGS_BEFORE_STORE', $this->getKeysString(), $this->_tbl), Log::INFO, 'CRUD');

		// Store
		if (!parent::store($updateNulls))
		{
			$this->logsError();
			Log::add(Text::sprintf('COM_REDSHOPB_LOGS_ERROR_STORE', $this->getKeysString(), $this->_tbl), Log::ERROR, 'CRUD');

			return false;
		}

		Log::add(Text::sprintf('COM_REDSHOPB_LOGS_AFTER_STORE', $this->getKeysString(), $this->_tbl), Log::INFO, 'CRUD');

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
		Log::add(Text::sprintf('COM_REDSHOPB_LOGS_BEFORE_DELETE', $this->getKeysString($pk), $this->_tbl), Log::INFO, 'CRUD');

		// If a property "deleted" exists in the table, just sets it to 1 and executes before/after actions here
		if (property_exists($this, 'deleted'))
		{
			return $this->simulatedDelete($pk);
		}

		$db = $this->getDbo();
		$db->transactionStart();

		if (!$this->deleteErpRecords($pk))
		{
			$db->transactionRollback();

			return false;
		}

		if (!parent::delete($pk))
		{
			$db->transactionRollback();
			$this->logsError();

			return false;
		}

		$db->transactionCommit();

		Log::add(Text::sprintf('COM_REDSHOPB_LOGS_AFTER_DELETE', $this->getKeysString($pk), $this->_tbl), Log::INFO, 'CRUD');

		return true;
	}

	/**
	 * Method to simulate a deletion by setting the delete flag to 1
	 *
	 * @param   mixed  $pk  An optional primary key value to delete.  If not set the instance property value is used.
	 *
	 * @return boolean
	 */
	protected function simulatedDelete($pk = null)
	{
		$db = $this->getDbo();
		$db->transactionStart();

		// Before delete
		if (!$this->beforeDelete($pk))
		{
			$db->transactionRollback();
			$this->logsError();

			return false;
		}

		if (!$this->deleteErpRecords($pk))
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
	 * @param   mixed  $pk  An optional primary key value to delete.  If not set the instance property value is used.
	 *
	 * @return  boolean  True on success.
	 */
	protected function afterDelete($pk = null)
	{
		if (!parent::afterDelete($pk))
		{
			$this->logsError();
			Log::add(Text::sprintf('COM_REDSHOPB_LOGS_ERROR_DELETE', $this->getKeysString(), $this->_tbl), Log::ERROR, 'CRUD');

			return false;
		}

		$this->setSyncReferencesToDeleted();
		$this->removeTableDataLocks();

		return true;
	}

	/**
	 * Get a backend table instance
	 *
	 * @param   string  $name    The table name
	 * @param   array   $config  An optional array of configuration
	 * @param   string  $option  Component name, use for call table from another extension
	 *
	 * @return  RTable  The table
	 */
	public static function getAdminInstance($name, array $config = array(), $option = 'com_redshopb')
	{
		return parent::getAdminInstance($name, $config, $option);
	}

	/**
	 * Get a table instance.
	 *
	 * @param   string  $name    The table name
	 * @param   mixed   $client  The client. null = auto, 1 = admin, 0 = frontend
	 * @param   array   $config  An optional array of configuration
	 * @param   string  $option  Component name, use for call table from another extension
	 *
	 * @return  RTable  The table
	 *
	 * @throws  InvalidArgumentException
	 */
	public static function getAutoInstance($name, $client = null, array $config = array(), $option = 'com_redshopb')
	{
		return parent::getAutoInstance($name, $client, $config, $option);
	}

	/**
	 * Get a frontend table instance
	 *
	 * @param   string  $name    The table name
	 * @param   array   $config  An optional array of configuration
	 * @param   string  $option  Component name, use for call table from another extension
	 *
	 * @return  RTable  The table
	 */
	public static function getFrontInstance($name, array $config = array(), $option = 'com_redshopb')
	{
		return parent::getFrontInstance($name, $config, $option);
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
		$isPublishSuccess = parent::publish($pks, $state, $userId);

		if ($isPublishSuccess)
		{
			foreach ($pks as $tableId)
			{
				$this->load($tableId);
				RFactory::getDispatcher()->trigger('onAfterPublishRedshopb', array(&$this));
			}
		}

		return $isPublishSuccess;
	}
}
