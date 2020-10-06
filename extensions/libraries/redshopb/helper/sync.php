<?php
/**
 * @package     Aesir.E-Commerce.Library
 * @subpackage  Helpers
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\{
	Utilities\ArrayHelper,
	Registry\Registry
};
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Language\LanguageHelper;
/**
 * Sync helper.
 *
 * @package     Aesir.E-Commerce.Library
 * @subpackage  Helpers
 * @since       1.6
 */
class RedshopbHelperSync
{
	/**
	 * Start time of the execution
	 *
	 * @var  integer
	 */
	public $startTime = 0;

	/**
	 * Maximum execution time of the script
	 *
	 * @var  integer
	 */
	public $maxExecutionTime = 0;

	/**
	 * Should we proceed to the next part of the execution
	 *
	 * @var  boolean
	 */
	public $goToNextPart = false;

	/**
	 * Already executed rows in previous Sync
	 *
	 * @var  array
	 */
	public $executed = array();

	/**
	 * Number of executed rows in this Sync
	 *
	 * @var  integer
	 */
	public $counter = 0;

	/**
	 * Total number of rows to execute in this Sync
	 *
	 * @var  integer
	 */
	public $counterTotal = 0;

	/**
	 * Number of files to download / process / fetch
	 *
	 * @var  integer
	 */
	public $processItemsStep = 0;

	/**
	 * Number of files already processed
	 *
	 * @var  integer
	 */
	public $processItemsCompleted = 0;

	/**
	 * Cron Table object
	 *
	 * @var  RTable
	 */
	public $cronTable = null;

	/**
	 * List of fields from redshopb table grouped by scope
	 *
	 * @var  array
	 */
	public $fields = array();

	/**
	 * List of types from redshopb table
	 *
	 * @var  array
	 */
	public $types = array();

	/**
	 * Stored Languages
	 *
	 * @var  array
	 */
	public static $storedLanguages = array();

	/**
	 * Cron parameters
	 *
	 * @var  object
	 */
	public $params;

	/**
	 * Sync reference key
	 *
	 * @var  string
	 */
	public $syncName = '';

	/**
	 * Should base methods use database transaction
	 *
	 * @var  boolean
	 */
	public $useTransaction = true;

	/**
	 * If set to True it will continue in the same process session in cron job process
	 *
	 * @var  boolean
	 */
	public $isContinuous = true;

	/**
	 * Sets the number of deadlock retries of the deadlock process error before it gives up
	 *
	 * @var  integer
	 */
	public $deadlockRetries = 3;

	/**
	 * Replace Symbols
	 *
	 * @var  array
	 */
	public $replaceSymbols = array(' ', '<', '>', '\\', '"_QQ_"', ';', '(', ')', '&');

	/**
	 * @var string
	 */
	public $pluginName = '';

	/**
	 * @var array
	 */
	public $avoidOverrideWSProperties = array('id', 'checked_out', 'checked_out_time', 'created_by', 'created_date', 'modified_by', 'modified_date');

	/**
	 * @var Factory
	 */
	public $app;

	/**
	 * @var array
	 */
	public $lang_codes = array();

	/**
	 * @var string
	 */
	public $default_lang = 'en-GB';

	/**
	 * @var Registry
	 */
	public $pluginParams;

	/**
	 * Constructor.
	 *
	 */
	public function __construct()
	{
		$this->maxExecutionTime = ini_get("max_execution_time");
		$this->maxExecutionTime = empty($this->maxExecutionTime) ? 9999999 : $this->maxExecutionTime;
		$this->startTime        = microtime(1);
		$this->app              = Factory::getApplication();
	}

	/**
	 * Lookup in sync table for already synced data
	 *
	 * @param   string       $reference        the reference used for this kind of data (e.g: fengel.customer)
	 * @param   string       $remoteKey        the remote key used to identify the data
	 * @param   string       $remoteParentKey  the remote parent key used to identify the data, if isset
	 * @param   bool         $getAllData       Flag select all data
	 * @param   null|RTable  $table            Table item class
	 *
	 * @return object|integer|null 			   Local id if found or null
	 */
	public function findSyncedId($reference, $remoteKey, $remoteParentKey = '', $getAllData = false, &$table = null)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->select('*')
			->from($db->qn('#__redshopb_sync'))
			->where($db->qn('reference') . ' = ' . $db->q($reference))
			->where($db->qn('remote_key') . ' = ' . $db->q($remoteKey));

		if ($remoteParentKey)
		{
			$query->where($db->qn('remote_parent_key') . ' = ' . $db->q($remoteParentKey));
		}

		// This will ensure that we get latest local ID
		$query->order('local_id DESC');

		$result = $db->setQuery($query, 0, 1)
			->loadObject();

		if ($result && $result->metadata)
		{
			$result->metadata = self::mbUnserialize($result->metadata);
		}

		if ($getAllData)
		{
			return $result;
		}

		if ($result)
		{
			return $result->local_id;
		}

		return null;
	}

	/**
	 * Check existing main reference
	 *
	 * @param   array   $references  Array available references
	 * @param   integer $localId     Local id
	 *
	 * @return  boolean
	 */
	public function mainReferenceExists(array $references, $localId)
	{
		$references = RHelperArray::quote($references);
		$db         = Factory::getDbo();
		$query      = $db->getQuery(true)
			->select('main_reference')
			->from($db->qn('#__redshopb_sync'))
			->where('local_id = ' . $db->q($localId))
			->where('main_reference = 1')
			->where('reference IN (' . implode(',', $references) . ')');

		return (bool) $db->setQuery($query, 0, 1)
			->loadResult();
	}

	/**
	 * Lookup in sync table for remote sync data
	 *
	 * @param   string  $reference   the reference used for this kind of data (e.g: fengel.customer)
	 * @param   string  $localId     the local id used to identify the data
	 * @param   bool    $getAllData  Flag select all data
	 *
	 * @return string|object|null local id if found or null
	 */
	public function findSyncedLocalId($reference, $localId, $getAllData = false)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->from($db->qn('#__redshopb_sync'))
			->where('reference = ' . $db->q($reference))
			->where('local_id = ' . $db->q($localId));

		if ($getAllData)
		{
			$query->select('*');

			return $db->setQuery($query, 0, 1)
				->loadObject();
		}
		else
		{
			$query->select('remote_key');

			return $db->setQuery($query, 0, 1)
				->loadResult();
		}
	}

	/**
	 * Delete a reference to sync table
	 *
	 * @param   string     $reference        the reference used for this kind of data (e.g: fengel.customer)
	 * @param   string     $remoteKey        the remote key used to identify the data
	 * @param   string     $remoteParentKey  the remote key used to identify the data
	 * @param   boolean    $markAsDeleted    Not deleted, but marked reference as deleted
	 *
	 * @return true
	 *
	 * @throws Exception
	 */
	public function deleteSyncedId($reference, $remoteKey, $remoteParentKey = '', $markAsDeleted = false)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);

		if ($markAsDeleted)
		{
			$query->update($db->qn('#__redshopb_sync'))
				->set('metadata = NULL')
				->set('deleted = 1')
				->set('local_id = ' . $db->q(''));
		}
		else
		{
			$query->delete($db->qn('#__redshopb_sync'));
		}

		$query->where('reference = ' . $db->q($reference))
			->where('remote_key = ' . $db->q($remoteKey));

		if ($remoteParentKey)
		{
			$query->where('remote_parent_key = ' . $db->q($remoteParentKey));
		}

		return $this->executeQueryWithRetries($query);
	}

	/**
	 * Delete a reference to sync table
	 *
	 * @param   string  $reference  the reference used for this kind of data (e.g: fengel.customer)
	 * @param   string  $localId    the local id
	 *
	 * @return true
	 *
	 * @throws Exception
	 */
	public function deleteSyncedLocalId($reference, $localId)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->delete($db->qn('#__redshopb_sync'))
			->where('reference = ' . $db->q($reference))
			->where('local_id = ' . $db->q($localId));

		return $this->executeQueryWithRetries($query);
	}

	/**
	 * Delete all references from a record according to its table information
	 *
	 * @param   RedshopbTable  $table  the table with the loaded information
	 *
	 * @return  true
	 */
	public function deleteAllReferences($table)
	{
		$pkField = $table->getKeyName();
		$wsMap   = $table->get('wsSyncMapPK');

		if (count($wsMap))
		{
			foreach ($wsMap as $refs)
			{
				foreach ($refs as $ref)
				{
					try
					{
						$this->deleteSyncedLocalId($ref, $table->$pkField);
					}
					catch (Exception $e)
					{
						return false;
					}
				}
			}
		}

		return true;
	}

	/**
	 * Saves a reference to sync table
	 *
	 * @param   string          $reference        the reference used for this kind of data (e.g: fengel.customer)
	 * @param   string          $remoteKey        the remote key used to identify the data
	 * @param   string          $localId          the local id on our system
	 * @param   string          $remoteParentKey  the remote key used to identify the data
	 * @param   boolean         $isNew            Flag is New Item
	 * @param   integer         $newSyncStatus    New status for the sync record (default = 0)
	 * @param   string          $serialize        Text serializing from current item
	 * @param   boolean         $ignoreLocalId    Ignores the local id when updating
	 * @param   string          $newLocalId       Sends a new local id when updating
	 * @param   null|RTable     $table            Table item object
	 * @param   boolean         $mainReference    Flag is it main reference
	 * @param   string          $hashedKey        Hashed Key of the synced Item
	 *
	 * @return  true
	 *
	 * @throws  Exception
	 */
	public function recordSyncedId(
		$reference, $remoteKey, $localId, $remoteParentKey = '', $isNew = true, $newSyncStatus = 0, $serialize = '',
		$ignoreLocalId = false, $newLocalId = '', $table = null, $mainReference = null, $hashedKey = null
	)
	{
		$db       = Factory::getDbo();
		$query    = $db->getQuery(true);
		$metaData = 'NULL';

		$values = array(
			'execute_sync' => (int) $newSyncStatus,
			'serialize' => $db->q($serialize)
		);

		if (!is_null($hashedKey))
		{
			$values['hash_key'] = $db->q($hashedKey);
		}

		if (!is_null($mainReference))
		{
			$values['main_reference'] = (int) $mainReference;
		}

		$conditions = array(
			'reference' => $db->q($reference),
			'remote_key' => $db->q($remoteKey),
			'remote_parent_key' => $db->q($remoteParentKey)
		);

		$values['metadata'] = $metaData;

		if ($isNew)
		{
			$conditions['local_id'] = $db->q($localId);
			$columns                = array_merge($values, $conditions);
			$columnKeys             = array_map(array($db, 'quoteName'), array_keys($columns));
			$query->insert($db->qn('#__redshopb_sync'))
				->columns(implode(', ', $columnKeys))
				->values(implode(', ', array_values($columns)));
		}
		else
		{
			$query->update($db->qn('#__redshopb_sync'));

			if (!$ignoreLocalId)
			{
				$conditions['local_id'] = $db->q($localId);
			}

			if ($newLocalId != '')
			{
				$values['local_id'] = $db->q($newLocalId);
			}

			foreach ($values as $key => $value)
			{
				$query->set($db->qn($key) . ' = ' . $value);
			}

			foreach ($conditions as $key => $condition)
			{
				$query->where($db->qn($key) . ' = ' . $condition);
			}
		}

		return $this->executeQueryWithRetries($query);
	}

	/**
	 * treats the Table from recordSyncedId
	 *
	 * @param   object $table the table object
	 *
	 * @return mixed          the metadata.
	 */
	protected function treatTable($table)
	{
		$changedProperties = $table->get('changedProperties', array());

		if (!empty($changedProperties))
		{
			$table->removeRestrictedProperties($changedProperties);

			foreach ($this->avoidOverrideWSProperties as $notSyncField)
			{
				unset($changedProperties[$notSyncField]);
			}

			return array("changedProperties" => $changedProperties);
		}

		return null;
	}

	/**
	 * Checks if the specific cron has been executed
	 *
	 * @param   string  $cronName  Cron name
	 *
	 * @return boolean
	 */
	public function isCronExecuted($cronName)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->select('execute_sync')
			->from($db->qn('#__redshopb_cron'))
			->where('name = ' . $db->q($cronName));

		if ($this->pluginName)
		{
			$query->where('plugin = ' . $db->q($this->pluginName));
		}

		return $db->setQuery($query)->loadResult();
	}

	/**
	 * Sets the specific cron as execute (in progress)
	 *
	 * @param   string     $cronName       Cron name
	 * @param   boolean    $executionFlag  Flag to be set: true = executed, false = not executed
	 *
	 * @return true
	 *
	 * @throws Exception
	 */
	public function setCronAsExecuted($cronName, $executionFlag = true)
	{
		if (!empty($this->cronTable))
		{
			$this->cronTable->set('execute_sync', ($executionFlag ? 1 : 0));
			$this->cronTable->set('items_processed', 0);
			$this->cronTable->set('last_status_messages', '');

			return $this->cronTable->store();
		}

		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->update($db->qn('#__redshopb_cron'))
			->set('execute_sync = ' . ($executionFlag ? '1' : '0'))
			->set('items_processed = 0')
			->set('last_status_messages = ' . $db->q(''))
			->where('name = ' . $db->q($cronName));

		if ($this->pluginName)
		{
			$query->where('plugin = ' . $db->q($this->pluginName));
		}

		return $this->executeQueryWithRetries($query);
	}

	/**
	 * Get the value of a cron parameter
	 *
	 * @param   string  $cronName  Cron name
	 * @param   string  $param     Parameter
	 *
	 * @return mixed | false
	 *
	 * @throws Exception
	 */
	public function getCronParameter($cronName, $param)
	{
		$db = Factory::getDbo();

		// Get params
		$query = $db->getQuery(true)
			->select('params')
			->from($db->qn('#__redshopb_cron'))
			->where('name = ' . $db->q($cronName));

		if ($this->pluginName)
		{
			$query->where('plugin = ' . $db->q($this->pluginName));
		}

		$params = json_decode($db->setQuery($query)->loadResult());

		if (!is_object($params))
		{
			$params = new stdClass;
		}

		if (property_exists($params, $param))
		{
			return $params->$param;
		}

		return false;
	}

	/**
	 * Sets the value of a cron param
	 *
	 * @param   object      $cronTable            Cron table
	 * @param   integer     $numOfProcessedItems  Number of processed items
	 * @param   integer     $numberOfItems        Total number of items
	 * @param   boolean     $saveMessages         Save messages
	 *
	 * @return boolean
	 */
	public static function setProgressCounters(&$cronTable, $numOfProcessedItems = null, $numberOfItems = null, $saveMessages = false)
	{
		if (is_null($numOfProcessedItems) && is_null($numberOfItems) && !$saveMessages)
		{
			return true;
		}

		if ($saveMessages)
		{
			$app      = Factory::getApplication('site');
			$messages = $app->getMessageQueue();

			$lastStatusFile = self::saveStatusMessageFile($cronTable->name, $messages, $cronTable->start_time);

			$cronTable->last_status_messages = $lastStatusFile;
		}

		if (!is_null($numOfProcessedItems))
		{
			$cronTable->items_processed = (int) $numOfProcessedItems;
		}

		if (!is_null($numberOfItems))
		{
			$cronTable->items_total = (int) $numberOfItems;
		}

		if (is_object($cronTable->params) || is_array($cronTable->params))
		{
			$cronTable->params = is_object($cronTable->params) ? $cronTable->params->toString() : json_encode($cronTable->params);
		}

		return $cronTable->store();
	}

	/**
	 * Sets the value of a cron param
	 *
	 * @param   string  $cronName  Cron name
	 * @param   string  $param     Parameter
	 * @param   string  $value     Value
	 *
	 * @return true
	 *
	 * @throws Exception
	 */
	public function setCronParameter($cronName, $param, $value)
	{
		$db = Factory::getDbo();

		// Get params
		$query = $db->getQuery(true)
			->select('params')
			->from($db->qn('#__redshopb_cron'))
			->where('name = ' . $db->q($cronName));

		if ($this->pluginName)
		{
			$query->where('plugin = ' . $db->q($this->pluginName));
		}

		$params = new Registry($db->setQuery($query)->loadResult());
		$params->set($param, $value);

		$query = $db->getQuery(true)
			->update($db->qn('#__redshopb_cron'))
			->set('params = ' . $db->q($params->toString()))
			->where('name = ' . $db->q($cronName));

		if ($this->pluginName)
		{
			$query->where('plugin = ' . $db->q($this->pluginName));
		}

		$this->executeQueryWithRetries($query);

		$this->params = $params;

		return true;
	}

	/**
	 * Sets the specific cron as finished
	 *
	 * @param   string  $cronName    Cron name
	 * @param   string  $emptyParam  Param to empty
	 *
	 * @return boolean
	 *
	 * @throws Exception
	 */
	public function setCronAsFinished($cronName, $emptyParam = '')
	{
		if (isset($this->cronTable))
		{
			$this->cronTable->execute_sync = 0;

			if ($emptyParam != '')
			{
				$params = $this->cronTable->params;

				if (!is_object($params))
				{
					$params = new Registry($params);
				}

				$params = $params->toArray();

				unset($params[$emptyParam]);

				$this->cronTable->params = json_encode($params);
			}

			return $this->cronTable->store();
		}

		$db = Factory::getDbo();

		// Get params
		$query = $db->getQuery(true)
			->select('params')
			->from($db->qn('#__redshopb_cron'))
			->where('name = ' . $db->q($cronName));

		if ($this->pluginName)
		{
			$query->where('plugin = ' . $db->q($this->pluginName));
		}

		$params = json_decode($db->setQuery($query)->loadResult());

		if (!is_object($params))
		{
			$params = new stdClass;
		}

		if ($emptyParam != '' && property_exists($params, $emptyParam))
		{
			unset($params->$emptyParam);
		}

		if (property_exists($params, 'executionStartTime'))
		{
			unset($params->executionStartTime);
		}

		$query->clear()
			->update($db->qn('#__redshopb_cron'))
			->set(
				array (
					'execute_sync = 0',
					'params = ' . $db->q(json_encode($params))
				)
			)
			->where('name = ' . $db->q($cronName));

		if ($this->pluginName)
		{
			$query->where('plugin = ' . $db->q($this->pluginName));
		}

		return $this->executeQueryWithRetries($query);
	}

	/**
	 * Set all existing rows as executed in sync table
	 *
	 * @param   string     $reference        Reference name
	 * @param   boolean    $remoteParentKey  Is one product then bind it to a specific remote parent
	 * @param   boolean    $usePartialSet    Use partial sets
	 *
	 * @return mixed|true
	 *
	 * @throws Exception
	 */
	public function setSyncRowsAsExecuted($reference, $remoteParentKey = null, $usePartialSet = false)
	{
		$db = Factory::getDbo();

		if ($usePartialSet)
		{
			$countQuery = $db->getQuery(true)
				->select('COUNT(*)')
				->from($db->qn('#__redshopb_sync') . ' FORCE INDEX (' . $db->qn('idx_execute_sync') . ')')
				->where($db->qn('reference') . ' = ' . $db->q($reference))
				->where('execute_sync <> 1');

			if (!empty($remoteParentKey))
			{
				$countQuery->where('remote_parent_key = ' . $db->q($remoteParentKey));
			}

			$counter = $db->setQuery($countQuery, 0, 1)->loadResult();

			while ($counter > 0)
			{
				if ($this->isExecutionTimeExceeded())
				{
					$this->goToNextPart = true;

					return $counter;
				}

				$query = $db->getQuery(true)
					->update($db->qn('#__redshopb_sync'))
					->set('execute_sync = 1')
					->where($db->qn('reference') . ' = ' . $db->q($reference))
					->where('execute_sync <> 1');

				if (!empty($remoteParentKey))
				{
					$query->where('remote_parent_key = ' . $db->q($remoteParentKey));
				}

				$this->executeQueryWithRetries($query . ' LIMIT 5000');

				$counter -= 5000;
			}
		}
		else
		{
			// Fix flag from all old items as not synced
			$query = $db->getQuery(true)
				->update($db->qn('#__redshopb_sync'))
				->set('execute_sync = 1')
				->where($db->qn('reference') . ' = ' . $db->q($reference))
				->where('execute_sync <> 1');

			if (!empty($remoteParentKey))
			{
				$query->where('remote_parent_key = ' . $db->q($remoteParentKey));
			}

			$this->executeQueryWithRetries($query);
		}

		return true;
	}

	/**
	 * Get list executed in previous sync items
	 *
	 * @param   string  $reference        Reference name
	 * @param   bool    $remoteParentKey  Is one product then bind it to a specific remote parent
	 * @param   array   $statuses         Array selection statuses
	 *
	 * @return array
	 *
	 * @throws Exception
	 */
	public function getPreviousSyncExecutedList($reference, $remoteParentKey = null, $statuses = array())
	{
		$db = Factory::getDbo();

		// Get list executed in previous sync items
		$query = $db->getQuery(true)
			->select('CONCAT_WS(' . $db->q('_') . ', remote_key, remote_parent_key) AS concat_id')
			->from($db->qn('#__redshopb_sync'))
			->where($db->qn('reference') . ' = ' . $db->q($reference));

		if (!empty($remoteParentKey))
		{
			$query->where('remote_parent_key = ' . $db->q($remoteParentKey));
		}

		if (count($statuses) > 0)
		{
			$statuses = ArrayHelper::toInteger($statuses);
			$query->where('execute_sync IN (' . implode(',', $statuses) . ')');
		}
		else
		{
			$query->where('execute_sync IN (0,2)');
		}

		$db->setQuery($query);

		return $db->loadObjectList('concat_id');
	}

	/**
	 * Deletes rows both from Sync table and from original table
	 *
	 * @param   string  $reference      Reference name
	 * @param   string  $tableName      Table name
	 * @param   array   $statuses       Array selection statuses
	 * @param   bool    $useTableClass  Use table class for delete items
	 * @param   string  $keyName        Key name in table, where deletes items
	 *
	 * @return array of deleted sync rows
	 *
	 * @throws Exception
	 */
	public function deleteRowsNotPresentInRemote($reference, $tableName = '', $statuses = array(), $useTableClass = false, $keyName = 'id')
	{
		$db         = Factory::getDbo();
		$removedIds = array();

		// Remove items that were not present in the remote
		if ($tableName && $tableName != '')
		{
			$subQuery = $db->getQuery(true)
				->select('local_id')
				->from($db->qn('#__redshopb_sync') . ' FORCE INDEX (' . $db->qn('idx_execute_sync') . ')')
				->where('reference = ' . $db->q($reference))
				->where('deleted = 0');

			if (count($statuses) > 0)
			{
				$statuses = ArrayHelper::toInteger($statuses);
				$subQuery->where('execute_sync IN (' . implode(',', $statuses) . ')');
			}
			else
			{
				$subQuery->where('execute_sync = 1');
			}

			if ($useTableClass)
			{
				$ids = $db->setQuery($subQuery)
					->loadColumn();

				if ($ids && count($ids))
				{
					$table = RTable::getInstance($tableName, 'RedshopbTable')
						->setOption('forceWebserviceUpdate', true)
						->setOption('lockingMethod', 'Sync');

					foreach ($ids as $i => $id)
					{
						if ($id)
						{
							$table->delete($id);
						}

						$removedIds[] = $db->q($id);

						if ($this->isExecutionTimeExceeded())
						{
							$this->goToNextPart = true;
							self::addMessage(Text::sprintf('COM_REDSHOPB_SYNC_WEBSERVICE_DELETING_ROWS_TIME_EXCEEDED', $i, count($ids)));

							return array();
						}
					}
				}
			}
			else
			{
				// Used string query here, because JDatabaseQuery not support yet usage DELETE and LEFT JOIN together
				// and join work much more improve here than sub query
				$query = 'DELETE a'
					. ' FROM ' . $db->qn($tableName, 'a')
					. ' LEFT JOIN ' . $db->qn('#__redshopb_sync', 's') . ' ON CONVERT(s.local_id, SIGNED) = ' . $db->qn('a.' . $keyName)
					. ' WHERE s.reference = ' . $db->q($reference)
					. ' AND deleted = 0';

				if (count($statuses) > 0)
				{
					$statuses = ArrayHelper::toInteger($statuses);
					$query   .= ' AND s.execute_sync IN (' . implode(',', $statuses) . ')';
				}
				else
				{
					$query .= ' AND s.execute_sync = 1';
				}

				$this->executeQueryWithRetries($query);
			}
		}

		// Remove items from Sync table that were not present in the remote
		$queryItems = $db->getQuery(true)
			->select('*')
			->from($db->qn('#__redshopb_sync'))
			->where('reference = ' . $db->q($reference))
			->where('deleted = 0');

		// Remove items from Sync table that were not present in the remote
		$query = $db->getQuery(true)
			->delete($db->qn('#__redshopb_sync'))
			->where('reference = ' . $db->q($reference))
			->where('deleted = 0');

		if (count($removedIds) > 0)
		{
			$query->where('local_id IN (' . implode(',', $removedIds) . ')');
			$queryItems->where('local_id IN (' . implode(',', $removedIds) . ')');
		}

		if (count($statuses) > 0)
		{
			$statuses = ArrayHelper::toInteger($statuses);
			$query->where('execute_sync IN (' . implode(',', $statuses) . ')');
			$queryItems->where('execute_sync IN (' . implode(',', $statuses) . ')');
		}
		else
		{
			$query->where('execute_sync = 1');
			$queryItems->where('execute_sync = 1');
		}

		$deletedItems = $db->setQuery($queryItems)
			->loadObjectList();

		$this->executeQueryWithRetries($query);

		return $deletedItems;
	}

	/**
	 * Deletes rows both from Sync table and from original table
	 *
	 * @param   int  $maxTime  If not defined then PHP maximum execution time will be used -10 sec
	 *
	 * @return array
	 */
	public function isExecutionTimeExceeded($maxTime = 0)
	{
		if (empty($maxTime))
		{
			$maxTime = $this->maxExecutionTime - ($this->maxExecutionTime / 3);
		}

		return ((microtime(1) - $this->startTime) >= $maxTime);
	}

	/**
	 * Check Translate Existing
	 *
	 * @param   object            $translationTable  Table parameters
	 * @param   array|object      $original          Values original item
	 * @param   string            $languageTag       Language tag
	 * @param   array             $translateValues   Translate values
	 * @param   integer           $translationId     Translation id link, using for getting it after function executed
	 *
	 * @return string|true
	 */
	public function storeTranslation($translationTable, $original, $languageTag, $translateValues, &$translationId = 0)
	{
		$original                = (array) $original;
		$rcTranslationsOriginals = RTranslationTable::createOriginalValueFromColumns($original, $translationTable->columns);
		$now                     = Date::getInstance();
		$nowFormatted            = $now->toSql();
		$uniqueKey               = array();
		$translateTableName      = RTranslationTable::getTranslationsTableName($translationTable->table, '');
		$db                      = Factory::getDbo();
		$query                   = $db->getQuery(true)
			->select('rctranslations_id')
			->from($db->qn($translateTableName))
			->where('rctranslations_language = ' . $db->q($languageTag));

		foreach ($translationTable->primaryKeys as $primaryKey)
		{
			$uniqueKey[$primaryKey] = $original[$primaryKey];
			$query->where($db->qn($primaryKey) . ' = ' . $db->q($original[$primaryKey]));
		}

		$translationId = $db->setQuery($query)->loadResult();

		if ($translationId)
		{
			$query->clear()
				->update($db->qn($translateTableName))
				->where('rctranslations_id = ' . (int) $translationId)
				->set('rctranslations_modified = ' . $db->q($nowFormatted))
				->set('rctranslations_originals = ' . $db->q($rcTransOriginals))
				->set('rctranslations_language = ' . $db->q($languageTag));

			foreach ($translationTable->columns as $column)
			{
				if (isset($translateValues[$column]))
				{
					$query->set($db->qn($column) . ' = ' . $db->q($translateValues[$column]));
				}
				else
				{
					$query->set($db->qn($column) . ' = NULL');
				}
			}

			$db->setQuery($query);

			try
			{
				$db->execute();
			}
			catch (Exception $e)
			{
				return $e->getMessage();
			}
		}
		else
		{
			$values = array();

			foreach ($translateValues as $translateValue)
			{
				if (isset($translateValue))
				{
					$values[] = $db->q($translateValue);
				}
				else
				{
					$values[] = 'NULL';
				}
			}

			$query->clear()
				->insert($db->qn($translateTableName))
				->columns(
					implode(', ', array_keys($translateValues))
					. ', rctranslations_modified, rctranslations_state, rctranslations_originals, rctranslations_language'
				)
				->values(
					implode(', ', $values)
					. ', ' . $db->q($nowFormatted) . ', 1'
					. ', ' . $db->q($rcTransOriginals)
					. ', ' . $db->q($languageTag)
				);
			$db->setQuery($query);

			try
			{
				$db->execute();
			}
			catch (Exception $e)
			{
				return $e->getMessage();
			}

			$translationId = $db->insertid();
		}

		$uniqueKey = json_encode($uniqueKey);

		if (!isset(self::$storedLanguages[$translateTableName]))
		{
			self::$storedLanguages[$translateTableName] = array();
		}

		if (!isset(self::$storedLanguages[$translateTableName][$uniqueKey]))
		{
			self::$storedLanguages[$translateTableName][$uniqueKey] = array();
		}

		self::$storedLanguages[$translateTableName][$uniqueKey][] = $languageTag;

		return true;
	}

	/**
	 * Delete translation
	 *
	 * @param   object        $translationTable  Table parameters
	 * @param   array|object  $original          Values original item
	 * @param   string        $languageTag       Language tag
	 *
	 * @return string|true
	 */
	public function deleteTranslation($translationTable, $original, $languageTag)
	{
		$original           = (array) $original;
		$translateTableName = RTranslationTable::getTranslationsTableName($translationTable->table, '');
		$db                 = Factory::getDbo();
		$query              = $db->getQuery(true)
			->delete($translateTableName)
			->where('rctranslations_language = ' . $db->q($languageTag));

		foreach ($translationTable->primaryKeys as $primaryKey)
		{
			$query->where($db->qn($primaryKey) . ' = ' . $db->q($original[$primaryKey]));
		}

		try
		{
			$db->setQuery($query)->execute();
		}
		catch (Exception $e)
		{
			return $e->getMessage();
		}

		return true;
	}

	/**
	 * delete Not Syncing Languages from current item
	 *
	 * @param   object        $translationTable  Table parameters
	 * @param   array|object  $original          Values original item
	 *
	 * @return  string|true
	 */
	public function deleteNotSyncingLanguages($translationTable, $original)
	{
		$original           = (array) $original;
		$translateTableName = RTranslationTable::getTranslationsTableName($translationTable->table, '');
		$uniqueKey          = array();

		foreach ($translationTable->primaryKeys as $primaryKey)
		{
			$uniqueKey[$primaryKey] = $original[$primaryKey];
		}

		$uniqueKey = json_encode($uniqueKey);

		if (isset(self::$storedLanguages[$translateTableName][$uniqueKey])
			&& is_array(self::$storedLanguages[$translateTableName][$uniqueKey])
			&& self::$storedLanguages[$translateTableName][$uniqueKey] > 0)
		{
			$languages = array();
			$db        = Factory::getDbo();
			$query     = $db->getQuery(true)
				->delete($translateTableName);

			foreach (self::$storedLanguages[$translateTableName][$uniqueKey] as $oneLang)
			{
				$languages[] = $db->q($oneLang);
			}

			$query->where('rctranslations_language NOT IN ('
				. implode(', ', $languages) . ')'
			);

			$uniqueKey = json_decode($uniqueKey);

			foreach ($uniqueKey as $key => $value)
			{
				$query->where($db->qn($key) . ' = ' . $db->q($value));
			}

			$db->setQuery($query);

			try
			{
				$db->execute();
			}
			catch (Exception $e)
			{
				return $e->getMessage();
			}
		}

		return true;
	}

	/**
	 * Gets translation table from the list of the installed translation tables
	 *
	 * @param   string  $tableName  Table name
	 *
	 * @return string|object
	 */
	public function getSyncTranslationTable($tableName = '')
	{
		$translationTables = RTranslationHelper::getInstalledTranslationTables();

		return isset($translationTables[$tableName]) ? $translationTables[$tableName] : '';
	}

	/**
	 * Unserializes string with multibyte content
	 *
	 * @param   string  $string  String
	 *
	 * @return mixed
	 */
	public static function mbUnserialize($string)
	{
		$string = preg_replace_callback('!s:(\d+):"(.*?)";!s',
			function ($mb)
			{
				return ($mb[1] == strlen($mb[2])) ? $mb[0] : 's:' . strlen($mb[2]) . ':"' . $mb[2] . '";';
			},
			$string
		);

		return @unserialize($string);
	}

	/**
	 * Set default parameters for this sync
	 *
	 * @param   object     $webserviceData   Webservice object
	 * @param   Registry   $pluginParams     Parameters of the plugin
	 *
	 * @return  void
	 */
	public function setDefaultCronParams(&$webserviceData, $pluginParams)
	{
		$params = $webserviceData->get('params');

		if (!is_object($params))
		{
			$params = new Registry($params);
		}

		if (!is_object($pluginParams))
		{
			$pluginParams = new Registry($pluginParams);
		}

		// We are checking if the maximum execution time parameter is set to the plugin
		$maxExecutionTime = (int) $pluginParams->get('maxExecutionTime');

		if ($maxExecutionTime)
		{
			if ($this->maxExecutionTime > $maxExecutionTime)
			{
				$this->maxExecutionTime = $maxExecutionTime;
			}
		}

		$this->params       = $params;
		$this->pluginParams = $pluginParams;

		// Set other Defaults
		$this->processItemsStep      = (int) $webserviceData->get('items_process_step', 0);
		$this->processItemsCompleted = (int) $webserviceData->get('items_processed', 0);
		$this->isContinuous          = (bool) $webserviceData->get('is_continuous', 1);
		$this->cronTable             = $webserviceData;

		if ($pluginParams->get('enableLanguageSync', 1) == 1)
		{
			$this->lang_codes = LanguageHelper::getLanguages('lang_code');
			unset($this->lang_codes[$this->default_lang]);
		}
	}

	/**
	 * Returns true if the current item processed count is over the allowed step
	 *
	 * @return  boolean
	 */
	public function isOverTheStepLimit()
	{
		if ($this->processItemsStep <= 0)
		{
			return false;
		}

		if ($this->counter >= $this->processItemsStep)
		{
			return true;
		}

		return false;
	}

	/**
	 * Output result of sync operation
	 *
	 * @return array|boolean
	 */
	public function outputResult()
	{
		if ($this->goToNextPart)
		{
			static::addMessage(Text::sprintf('COM_REDSHOPB_SYNC_WEBSERVICE_GOTO_NEXT_PART', $this->counter, $this->counterTotal), 'info');

			return array('parts' => $this->counterTotal - $this->counter, 'total' => $this->counterTotal, 'isContinuous' => $this->isContinuous);
		}
		else
		{
			static::addMessage(
				Text::sprintf('COM_REDSHOPB_SYNC_WEBSERVICE_SYNCHRONIZE_SUCCESS', $this->counter, $this->counterTotal),
				'success'
			);

			return true;
		}
	}

	/**
	 * Check if the item needs to be updated or not
	 *
	 * @param   object  $sync     Sync object
	 * @param   string  $hashKey  Hashed key
	 *
	 * @return boolean
	 */
	public function isHashChanged($sync, $hashKey = '')
	{
		if (!$sync || !$sync->hash_key || !$hashKey)
		{
			return true;
		}

		if ($sync->hash_key === $hashKey)
		{
			return false;
		}

		return true;
	}

	/**
	 * Check if the item needs to be updated or not
	 *
	 * @param   object  $sync  Sync object
	 *
	 * @return boolean
	 */
	public function skipItemUpdate($sync)
	{
		if (!$sync)
		{
			return true;
		}

		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->update($db->qn('#__redshopb_sync'))
			->set($db->qn('execute_sync') . ' = 0')
			->where($db->qn('reference') . ' = ' . $db->q($sync->reference))
			->where($db->qn('remote_key') . ' = ' . $db->q($sync->remote_key))
			->where($db->qn('remote_parent_key') . ' = ' . $db->q($sync->remote_parent_key));

		return $this->executeQueryWithRetries($query);
	}

	/**
	 * Clear all Hashed Keys from this cron sync
	 *
	 * @return boolean
	 */
	public function clearHashKeys()
	{
		if (!empty($this->syncName))
		{
			$db    = Factory::getDbo();
			$query = $db->getQuery(true)
				->update($db->qn('#__redshopb_sync'))
				->set($db->qn('hash_key') . ' = ' . $db->q(''));

			if (property_exists($this, 'allUsedSyncNames') && !empty($this->allUsedSyncNames))
			{
				$query->where($db->qn('reference') . ' IN (' . implode(',', array_map(array($db, 'quote'), $this->allUsedSyncNames)) . ')');
			}
			else
			{
				$query->where($db->qn('reference') . ' = ' . $db->q($this->syncName));
			}

			$this->executeQueryWithRetries($query);
		}

		return true;
	}

	/**
	 * Deletes image if it is the only one in the media table
	 *
	 * @param   object  $image  Image object
	 *
	 * @return  void
	 */
	public function deleteRemoteImage($image)
	{
		// We delete remote images for the existing media rows
		if ($image && $image->id && $image->name && $image->remote_path)
		{
			$db    = Factory::getDbo();
			$query = $db->getQuery(true)
				->select('m.id')
				->from($db->qn('#__redshopb_media', 'm'))
				->where($db->qn('remote_path') . ' = ' . $db->q($image->remote_path))
				->where($db->qn('name') . ' = ' . $db->q($image->name))
				->where($db->qn('id') . ' <> ' . $image->id);

			$db->setQuery($query);

			if (!$db->loadResult())
			{
				$path = RedshopbHelperMedia::getMediaRemotePath($image->name, $image->remote_path);

				if (JFile::exists($path))
				{
					JFile::delete($path);
				}
			}
		}
	}

	/**
	 * This function will try to execute same query several times before giving up.
	 *
	 * @param   JDatabaseQuery|string  $query  Query to execute
	 *
	 * @return boolean
	 *
	 * @throws  Exception
	 */
	public function executeQueryWithRetries($query)
	{
		$db = Factory::getDbo();
		$db->setQuery($query);
		$counter = $this->deadlockRetries;

		// @NOTE if there is a more appropriate way to handle this, please feel free to refactor it
		if ($counter < 0)
		{
			throw new Exception("Property RedshopbHelperSync::deadlockRetries cannot be smaller than zero");
		}

		while ($counter >= 0)
		{
			try
			{
				return $db->execute();
			}
			catch (Exception $e)
			{
				$counter--;

				if ($counter <= 0)
				{
					throw $e;
				}
			}
		}
	}

	/**
	 * Returns value in bytes from the ini value string (ex: memory_limit)
	 *
	 * @param   string  $sizeString  Size string
	 *
	 * @return  integer
	 */
	public static function returnBytesFromIniValue($sizeString)
	{
		switch (strtolower(substr($sizeString, strlen($sizeString) - 1, 1)))
		{
			case 'm':
				return ((int) $sizeString) * 1048576;
			case 'k':
				return ((int) $sizeString) * 1024;
			case 'g':
				return ((int) $sizeString) * 1073741824;
			default:
				return $sizeString;
		}
	}

	/**
	 * Returns value in bytes from the ini value string (ex: memory_limit)
	 *
	 * @param   mixed   $value   Value to hash
	 * @param   string  $type    Size string
	 * @param   string  $prefix  Use when need change hash
	 *
	 * @return  string
	 */
	public static function generateHashKey($value, $type = 'string', $prefix = '1')
	{
		switch ($type)
		{
			case 'string':
				return md5($value . $prefix);

			case 'xml':
				return md5(json_encode($value) . $prefix);

			case 'object':
				// We might have an access Token in the _links container so the hash key will always be different because of that so we remove it.
				$valueProcessed = clone $value;
				unset($valueProcessed->_links);

				return md5(json_encode($valueProcessed) . $prefix);

			case 'array':
				// We might have an access Token in the _links container so the hash key will always be different because of that so we remove it.
				unset($value['_links']);

				return md5(json_encode($value) . $prefix);

			default:
				return md5($value . $prefix);
		}
	}

	/**
	 * Sets Message with Timestamp in the message queue
	 *
	 * @param   mixed   $message  Message to store
	 * @param   string  $type     Size string
	 *
	 * @return void
	 */
	public static function addMessage($message, $type = 'message')
	{
		$message = Date::getInstance()->format('Y.m.d H:i:s') . ' - ' . $message;
		Factory::getApplication()->enqueueMessage($message, $type);
	}

	/**
	 * Gets the base for enrichment during sync, querying the rb_sync plugins to see which one is using enrichment (uses the first one available)
	 *
	 * @param   JModel  $model  Model to get the exact reference
	 *
	 * @return string
	 */
	public static function getEnrichmentBase($model = null)
	{
		PluginHelper::importPlugin('rb_sync');
		$dispatcher    = RFactory::getDispatcher();
		$pluginReturns = $dispatcher->trigger('onFuncGetEnrichmentBase', array());

		if (!count($pluginReturns))
		{
			return '';
		}

		$base = $pluginReturns[0];

		if (is_null($model))
		{
			return $base;
		}

		$table         = $model->getTable();
		$fieldMapping  = $table->get('wsSyncMapPK');
		$syncReference = '';

		if (is_array($fieldMapping) && isset($fieldMapping[$base]) && is_array($fieldMapping[$base]))
		{
			$syncReference = $fieldMapping[$base][0];
		}

		return $syncReference;
	}

	/**
	 * Clear hash key for a sync entry
	 *
	 * @param   string  $reference   The reference used for this kind of data (e.g: fengel.customer)
	 * @param   string  $remoteKey   The remote key used to identify the data
	 * @param   string  $localId     The local id on our system
	 *
	 * @return boolean
	 */
	public function clearHashKey($reference, $remoteKey, $localId)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->update($db->qn('#__redshopb_sync'))
			->set($db->qn('hash_key') . ' = ' . $db->q(''))
			->where($db->qn('reference') . ' = ' . $db->q($reference))
			->where($db->qn('remote_key') . ' = ' . $db->q($remoteKey))
			->where($db->qn('local_id') . ' = ' . $db->q($localId));

		return $this->executeQueryWithRetries($query);
	}

	/**
	 * Saves a file with the latest status message
	 *
	 * @param   string  $syncName   Name of the current sync
	 * @param   array   $messages   Array of messages to save
	 * @param   string  $startTime  Start time of the sync
	 *
	 * @return string
	 */
	public static function saveStatusMessageFile($syncName, $messages, $startTime)
	{
		$tmpPath       = JPATH_ROOT . '/tmp';
		$tmpSubFolders = 'com_redshopb/sync';
		$fullLogPath   = $tmpPath . '/' . $tmpSubFolders;

		$startTimeObject = DateTime::createFromFormat('Y-m-d H:i:s', $startTime);

		$folderTree = $startTimeObject->format('Y/m/d');

		$fullLogPath = $fullLogPath . '/' . $folderTree;

		JFolder::create($fullLogPath);

		$fileName = $syncName . ' ' . $startTimeObject->format('YmdHis');
		$filePath = $fullLogPath . '/' . $fileName . '.log';

		$fullFileContent = '';

		if (JFile::exists($filePath))
		{
			$fullFileContent = file_get_contents($filePath);
		}

		foreach ($messages as $message)
		{
			$messageLine = $message['message'] . ' - ' . $message['type'] . "\n";

			$fullFileContent = $fullFileContent . $messageLine;
		}

		JFile::write($filePath, $fullFileContent);

		return $filePath;
	}

	/**
	 * Get all the saved status messages
	 *
	 * @param   string  $filePath  The path to the message log file
	 *
	 * @return array
	 */
	public static function getStatusMessages($filePath)
	{
		if (file_exists($filePath))
		{
			$fullFileContent = file_get_contents($filePath);
			$messages        = explode("\n", $fullFileContent);

			// Last element will be empty, as the log ends with \n
			array_pop($messages);

			return $messages;
		}

		return array();
	}
}
