<?php
/**
 * @package     Aesir.E-Commerce.Plugin
 * @subpackage  Client
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('JPATH_BASE') or die;

use Joomla\CMS\Factory;
use Joomla\Registry\Registry;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Table\Table;

/**
 * Function base class.
 *
 * @package     Aesir.E-Commerce.Plugin
 * @subpackage  Client
 * @since       1.0
 */
abstract class PimFunctionBase extends RedshopbHelperSync
{
	/**
	 * The client.
	 *
	 * @var  RedshopbClientPim
	 */
	protected $client;

	/**
	 * @var string
	 */
	public $pluginName = 'pim';

	/**
	 * @var string
	 */
	public $cronName = '';

	/**
	 * @var [type]
	 */
	public $tableClassName;

	/**
	 * @var [type]
	 */
	public $translationTable;

	/**
	 * @var string
	 */
	public $tableName = '';

	/**
	 * @var string
	 */
	public $settingsFile = '';

	/**
	 * @var string
	 */
	public $nameFieldWithData = '';

	/**
	 * @var array
	 */
	public $translationAssociations = array();

	/**
	 * @var [type]
	 */
	public $xmlFieldValues;

	/**
	 * @var boolean
	 */
	public $useTableClassForDeletes = false;

	/**
	 * @var [type]
	 */
	public $redshopbConfig;

	/**
	 * @var integer
	 */
	public $counter = 0;

	/**
	 * Name in the Sync Table
	 *
	 * @var  string
	 */
	public $syncName = '';

	/**
	 * Process parameters
	 *
	 * @var  Registry
	 */
	public $params;

	/**
	 * The list of fields that are bind to product columns.
	 *
	 * @var  array
	 */
	public $reservedFieldsInProduct = array('ItemName', 'SalesPriceString', 'FieldSet');

	/**
	 * List of sync references using temporary file for marking records as executed (to delete the ones that are not in the end)
	 *
	 * @var  array
	 */
	public $syncRefsUsingFile = array();

	/**
	 * Auxiliary Redis DB for sync processing operations
	 *
	 * @var  RedshopbDatabaseRedis
	 */
	protected $redisDB = null;

	/**
	 * Prefix for Redis in-execution keys
	 *
	 * @var  string
	 */
	protected $redisExecutionPrefix = 'vanir_sync_execution';

	/**
	 * PimFunctionBase constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		$table       = RTable::getInstance($this->tableClassName, 'RedshopbTable');
		$nestedClass = (stripos(get_parent_class($table), 'nested') === false ? false : true);

		if ($nestedClass)
		{
			$this->avoidOverrideWSProperties = array_merge(
				$this->avoidOverrideWSProperties, array(
					'lft', 'rgt', 'level', 'path'
				)
			);
		}

		$this->tableName        = $table->get('_tbl');
		$this->translationTable = $this->getSyncTranslationTable($this->tableName);
		$this->default_lang     = ComponentHelper::getParams('com_languages')
			->get('site', 'en-GB');
		$this->redshopbConfig   = RedshopbEntityConfig::getInstance();

		// Gets the Redis database if available
		$this->redisDB = new RedshopbDatabaseRedis;
	}

	/**
	 * Read and store the data.
	 *
	 * @param   RTable     $webserviceData   Webservice object
	 * @param   Registry   $params           Parameters of the plugin
	 *
	 * @return  boolean
	 *
	 * @throws  Exception
	 */
	public function read(&$webserviceData, $params)
	{
		$db = Factory::getDbo();
		$this->setDefaultCronParams($webserviceData, $params);

		try
		{
			// Gets Data from the XML
			$source = array(
				'file' => array($this->client->localFolder . '/' . $this->settingsFile)
			);

			$db->unlockTables();

			if ($this->useTransaction)
			{
				$db->transactionStart();
			}

			// Check to see if we are already run Sync which is not finished (so we can have multiple parts if webservice is too big)
			if (!$this->isCronExecuted($this->cronName))
			{
				// We set them with a flag so we can delete the ones which are not present in the latest Sync xml
				$this->setSyncRowsAsExecuted($this->syncName, null, true);

				// We set cron as executed so we can have multipart process if the process takes too long
				$this->setCronAsExecuted($this->cronName);
			}
			else
			{
				// Get list executed in previous sync items because this is multipart process
				$this->executed = $this->getPreviousSyncExecutedList($this->syncName);
			}

			$this->params->set('processItemsCompleted', $this->processItemsCompleted);
			$this->params->set('processItemsStep', $this->processItemsStep);
			$this->params->set('groupLoadedFiles', false);
			$this->params->set('localFolder', $params->get('localFolder'));
			$xml = $this->client->getXmlData($source, $this->params);

			if (empty($xml))
			{
				throw new Exception(Text::sprintf('PLG_RB_SYNC_PIM_FAILED_TO_FETCH_ITEMS', json_encode($source)));
			}

			// Start sync with the new XML data
			$this->processData($xml);

			// Sets number of executed files in Cron
			$this->setProgressCounters($this->cronTable, null, $this->counterTotal);

			$this->counter     += $this->processItemsCompleted;
			$this->goToNextPart = false;

			if ($this->counter < $this->counterTotal)
			{
				$this->goToNextPart = true;
			}

			// We might be using nested tables so just in case we will unlock the table object if the store function have failed
			$db->unlockTables();

			if (!$this->goToNextPart)
			{
				try
				{
					// Remove items that were not present in the XML data
					if ($this->useTableClassForDeletes)
					{
						$this->deleteRowsNotPresentInRemote($this->syncName, $this->tableClassName, array(), true);
					}
					else
					{
						$this->deleteRowsNotPresentInRemote($this->syncName, $this->tableName);
					}
				}
				catch (Exception $e)
				{
					RedshopbHelperSync::addMessage($e->getMessage(), 'error');
				}

				// We might be using nested tables so just in case we will unlock the table object if the store function have failed
				$db->unlockTables();
			}

			if (!$this->goToNextPart)
			{
				// We are setting cron as finished (no more parts)
				$this->setCronAsFinished($this->cronName);
			}

			if ($this->useTransaction)
			{
				$db->transactionCommit();
			}

			$db->unlockTables();
		}
		catch (Exception $e)
		{
			if ($this->useTransaction)
			{
				$db->transactionRollback();
			}

			// We might be using nested tables so just in case we will unlock the table object if the store function have failed
			$db->unlockTables();
			RedshopbHelperSync::addMessage($e->getMessage(), 'error');

			return false;
		}

		return $this->outputResult();
	}

	/**
	 * Read and store the data.
	 *
	 * @param   SimpleXMLElement  $xml       XML element
	 * @param   string            $parentId  Parent id
	 *
	 * @return  boolean
	 */
	public function processData($xml, $parentId = '')
	{
		if (!isset($xml->{$this->nameFieldWithData}))
		{
			$this->goToNextPart = false;

			return false;
		}

		$this->counterTotal = count($xml->{$this->nameFieldWithData});
		$xml                = (array) $xml;

		if ($this->processItemsCompleted > 0)
		{
			$xml[$this->nameFieldWithData] = array_slice($xml[$this->nameFieldWithData], $this->processItemsCompleted);
		}

		foreach ($xml[$this->nameFieldWithData] as $item)
		{
			if ($this->goToNextPart == true || $this->isExecutionTimeExceeded() || $this->isOverTheStepLimit())
			{
				$this->goToNextPart = true;
				continue;
			}

			$this->preSyncItem($item, $parentId);
			$this->counter++;
		}

		return true;
	}

	/**
	 * Pre sync item
	 *
	 * @param   SimpleXMLElement  $item      XML element
	 * @param   string            $parentId  Parent id
	 *
	 * @return  void
	 */
	public function preSyncItem($item, $parentId = '')
	{
		if (empty($item))
		{
			return;
		}

		$table = RTable::getInstance($this->tableClassName, 'RedshopbTable')
			->setOption('forceWebserviceUpdate', true)
			->setOption('lockingMethod', 'Sync');

		if ($this->readXmlRecursive($item, $table, $parentId))
		{
			$this->translate($item, $table);
		}

		unset($table);
	}

	/**
	 * Translate
	 *
	 * @param   SimpleXMLElement  $item   XML element
	 * @param   Table             $table  Table object
	 *
	 * @return  void
	 */
	public function translate($item, $table)
	{
		if ($this->translationTable && count($this->lang_codes) > 0)
		{
			foreach ($this->lang_codes as $langCode => $langData)
			{
				$translationRow = array('id' => $table->get('id'));

				if ($this->getTranslationValues($translationRow, $this->xmlFieldValues, $langCode))
				{
					$result = $this->storeTranslation(
						$this->translationTable,
						$table,
						$langCode,
						$translationRow
					);

					if ($result !== true)
					{
						RedshopbHelperSync::addMessage($result, 'error');
					}
				}

				$result = $this->deleteNotSyncingLanguages($this->translationTable, $table);

				if ($result !== true)
				{
					RedshopbHelperSync::addMessage($result, 'error');
				}
			}
		}
	}

	/**
	 * Read and store the data.
	 *
	 * @param   SimpleXMLElement  $xml       XML element
	 * @param   Table             $table     Table object
	 * @param   string            $parentId  Parent id
	 *
	 * @return  boolean
	 */
	abstract public function readXmlRecursive($xml, &$table, $parentId = '');

	/**
	 * Set the client.
	 *
	 * @param   RedshopbClientPim  $client  The client
	 *
	 * @return  PimFunctionBase
	 */
	public function setClient(RedshopbClientPim $client)
	{
		$this->client = $client;

		return $this;
	}

	/**
	 * Creates new field with Field Value type
	 *
	 * @param   string   $fieldName   Field name
	 * @param   string   $scope       Scope name
	 * @param   int      $typeId      Type ID
	 * @param   boolean  $CVL         Append CVL parent for sync record
	 * @param   string   $fieldTitle  Title for the field (if not present it will use the field name)
	 * @param   boolean  $syncCVL     Appends a '.cvl' in the sync reference to create a different one
	 *
	 * @return object
	 *
	 * @throws Exception
	 */
	public function createFieldValueField($fieldName, $scope = 'product', $typeId = 1, $CVL = false, $fieldTitle = '', $syncCVL = false)
	{
		$db                         = Factory::getDbo();
		$field                      = RTable::getInstance('Field', 'RedshopbTable')
			->setOption('forceWebserviceUpdate', true)
			->setOption('lockingMethod', 'Sync')
			->setOption('load.type_code', false);
		$row                        = array();
		$row['state']               = 1;
		$row['scope']               = $scope;
		$row['name']                = $fieldName;
		$row['title']               = ($fieldTitle != '' ? $fieldTitle : $fieldName);
		$row['description']         = $fieldName;
		$row['searchable_frontend'] = 1;
		$row['searchable_backend']  = 1;
		$row['ordering']            = $field->getNextOrder($db->qn('scope') . '=' . $db->q('product'));
		$row['type_id']             = $typeId;
		$row['filter_type_id']      = $row['type_id'];

		if (!$field->save($row))
		{
			RedshopbHelperSync::addMessage($field->getError(), 'warning');
		}
		else
		{
			$this->recordSyncedId(
				'erp.pim.field' . ($syncCVL ? '.cvl' : ''), $fieldName, $field->id, ($CVL ? 'CVL' : ''), true, 0, '', false, '', null, 1
			);
		}

		RedshopbHelperField::$fields = null;

		return RedshopbHelperField::getFieldByName($fieldName, $scope);
	}

	/**
	 * Creates new field with Field Value type
	 *
	 * @param   string  $fieldName   Field name
	 * @param   string  $scope       Scope name
	 * @param   bool    $isCvl       Is this field CVL field
	 * @param   bool    $getAllData  Flag select all data
	 *
	 * @return integer
	 *
	 * @throws Exception
	 */
	public function getSyncedFieldId($fieldName, $scope = 'product', $isCvl = false, $getAllData = false)
	{
		$fieldId = null;

		if (!$isCvl)
		{
			$fieldId = $this->findSyncedId('erp.pim.field', trim((string) $fieldName));
		}

		// If that field is not found we will search for it in the CVL fields
		if (!$fieldId)
		{
			$fieldId = $this->findSyncedId('erp.pim.field.cvl', trim((string) $fieldName) . ($isCvl ? '' : '_CVL'));
		}

		// If local ID is found
		if ($fieldId)
		{
			$field = RedshopbHelperField::getFieldById($fieldId, $scope);

			// If local ID is found and we requested full data
			if ($field && $getAllData)
			{
				return $field;
			}

			// If the local id was in sync table but field by that ID does not exist
			if (!$field)
			{
				$fieldId = null;
			}
		}

		// If the field does not exist in the sync table but exists from previous creations
		if (!$fieldId && !$isCvl)
		{
			$field = RedshopbHelperField::getFieldByName(trim((string) $fieldName), $scope);

			if ($field)
			{
				if ($getAllData)
				{
					return $field;
				}

				return $field->id;
			}
		}

		// If the CVL field does not exist in the sync table but exists from previous creations
		if (!$fieldId)
		{
			$field = RedshopbHelperField::getFieldByName(trim((string) $fieldName) . '_CVL', $scope);

			if ($field)
			{
				if ($getAllData)
				{
					return $field;
				}

				return $field->id;
			}
		}

		return $fieldId;
	}

	/**
	 * Updates Field data
	 *
	 * @param   array  $row  Field data
	 *
	 * @return boolean
	 */
	public function updateRedshopbField($row)
	{
		$field = RTable::getInstance('Field', 'RedshopbTable')
			->setOption('forceWebserviceUpdate', true)
			->setOption('lockingMethod', 'Sync')
			->setOption('load.type_code', false)
			->setOption('storeNulls', false);

		if (!empty($row['id']))
		{
			$field->load($row['id']);
		}

		if (!$field->save($row))
		{
			RedshopbHelperSync::addMessage($field->getError(), 'warning');
		}

		RedshopbHelperField::$fields = null;

		return $field;
	}

	/**
	 * Get translation values
	 *
	 * @param   array             $row       Table row
	 * @param   SimpleXMLElement  $xml       XML element
	 * @param   string            $language  Language needed
	 *
	 * @return  boolean
	 */
	public function getTranslationValues(&$row, $xml, $language = '')
	{
		$translationFound = false;

		if (!$language)
		{
			$language = $this->default_lang;
		}

		$explodeLang  = explode('-', $language);
		$shortTag     = reset($explodeLang);
		$translations = array();

		if (!$xml)
		{
			return $translationFound;
		}

		foreach ($xml as $name => $oneValue)
		{
			if ((string) $oneValue == '')
			{
				continue;
			}

			foreach ($this->translationAssociations as $tableFieldName => $xmlFieldName)
			{
				$explodeName = explode('_', $name);
				$length      = strlen($xmlFieldName) + 1;

				if (count($explodeName) > 1 && substr($name, 0, $length) == $xmlFieldName . '_')
				{
					$langTag = end($explodeName);
					$langTag = explode('-', $langTag);
					$langTag = reset($langTag);

					if (!isset($translations[$xmlFieldName]))
					{
						$translations[$xmlFieldName] = array();
					}

					$translations[$xmlFieldName][$langTag] = trim((string) $oneValue);
				}
			}
		}

		foreach ($this->translationAssociations as $tableFieldName => $xmlFieldName)
		{
			if (isset($translations[$xmlFieldName][$shortTag]))
			{
				$row[$tableFieldName] = $translations[$xmlFieldName][$shortTag];
				$translationFound     = true;
			}
			else
			{
				$row[$tableFieldName] = trim((string) $xml->{$xmlFieldName});
			}
		}

		return $translationFound;
	}

	/**
	 * Gets (and optionally creates) the a temporary file to be used during different relaunches of the process
	 *
	 * @param   string  $fileName        File name (for registry and physic file name append)
	 * @param   string  $fileNameAppend  File name append
	 * @param   bool    $create          Creates the file (and delete any existing stored one)
	 *
	 * @return string | false
	 */
	protected function getTmpFile($fileName, $fileNameAppend = '', $create = false)
	{
		$fileName = $fileName . ($fileNameAppend == '' ? '' : '_' . str_replace('.', '_', $fileNameAppend));
		$tmpFile  = $this->params->get($fileName, null);

		// There is a temp file stored
		if (!is_null($tmpFile))
		{
			// File is referenced but doesn't exist (and it doesn't have to be created)
			if (!JFile::exists($tmpFile) && !$create)
			{
				static::addMessage(Text::_('COM_REDSHOPB_SYNC_TMP_FILE_CORRPUTED', 'warning'));

				return false;
			}

			// Does not need to create it - it returns it
			if (!$create)
			{
				return $tmpFile;
			}

			// (Else) it's asked to create one - deletes the existing first
			if (JFile::exists($tmpFile))
			{
				if (!JFile::delete($tmpFile))
				{
					static::addMessage(Text::_('COM_REDSHOPB_SYNC_CANNOT_CREATE_TMP_FILE'), 'error');

					return false;
				}

				$this->params->set($fileName, null);
				$tmpFile = null;

				static::addMessage(Text::_('COM_REDSHOPB_SYNC_DELETED_TMP_FILE'), 'warning');

				// Continues below to create the file
			}
		}

		// Needs to create the file
		if ($create)
		{
			$tmpPath = Factory::getConfig()->get('tmp_path', null);

			if (is_null($tmpPath))
			{
				static::addMessage(Text::_('COM_REDSHOPB_SYNC_CANNOT_CREATE_TMP_FILE'), 'error');

				return false;
			}

			if (!JFolder::create($tmpPath . '/com_redshopb'))
			{
				static::addMessage(Text::_('COM_REDSHOPB_SYNC_CANNOT_CREATE_TMP_FILE'), 'error');

				return false;
			}

			$tmpFile = $tmpPath . '/com_redshopb/' . $this->cronName . '_' . $fileName . '_' . date('YmdHis') . '.txt';

			if (!touch($tmpFile))
			{
				static::addMessage(Text::_('COM_REDSHOPB_SYNC_CANNOT_CREATE_TMP_FILE'), 'error');

				return false;
			}

			$this->params->set($fileName, $tmpFile);

			return $tmpFile;
		}

		// If no file is found and it's not being asked to re-create, it warns about the process being in need to restart.
		static::addMessage(Text::_('COM_REDSHOPB_SYNC_NO_TMP_FILE_RESTART'), 'warning');

		return false;
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
		// If the two advanced parameters are used, it uses the regular version of this method (via DB)
		if (!is_null($remoteParentKey) || !empty($statuses) || !$this->redisDB->getIndex() || !in_array($reference, $this->syncRefsUsingFile))
		{
			return parent::getPreviousSyncExecutedList($reference, $remoteParentKey, $statuses);
		}

		// If using Redis, there is no need to get the list
		return array();
	}

	/**
	 * Cleans up a temporary file
	 *
	 * @param   string  $fileName        File name (for registry and physic file name append)
	 * @param   string  $fileNameAppend  File name append
	 *
	 * @return void
	 */
	protected function cleanupTmpFile($fileName, $fileNameAppend = '')
	{
		$fileName = $fileName . ($fileNameAppend == '' ? '' : '_' . str_replace('.', '_', $fileNameAppend));
		$tmpFile  = $this->params->get($fileName, null);

		if (!is_null($tmpFile))
		{
			JFile::delete($tmpFile);
		}

		$this->params->set($fileName, null);
	}

	/**
	 * Set all existing rows as executed in sync table (or file, depending on the parameters)
	 *
	 * @param   string  $reference        Reference name
	 * @param   bool    $remoteParentKey  Is one product then bind it to a specific remote parent
	 * @param   bool    $usePartialSet    Use partial sets
	 *
	 * @return boolean
	 *
	 * @throws Exception
	 */
	public function setSyncRowsAsExecuted($reference, $remoteParentKey = null, $usePartialSet = false)
	{
		// If the two advanced parameters are used, it uses the regular version of this method (via DB).  Also if no Redis DB available
		if (!is_null($remoteParentKey) || !$this->redisDB->getIndex() || !in_array($reference, $this->syncRefsUsingFile))
		{
			return parent::setSyncRowsAsExecuted($reference, $remoteParentKey, $usePartialSet);
		}

		// If only reference is sent and if available, it uses Redis database to mark all the current records as in-execution
		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->select('local_id')
			->from($db->qn('#__redshopb_sync'))
			->where($db->qn('reference') . ' = ' . $db->q($reference));
		$db->setQuery($query);
		$ids = $db->loadColumn();

		if (!$ids)
		{
			return true;
		}

		// Sets all keys to 1 to mark them as in-execution
		return $this->redisDB->setKeysSingleValue($ids, '1', $this->redisExecutionPrefix . '_' . $reference);
	}

	/**
	 * Saves a reference to sync table
	 *
	 * @param   string       $reference        the reference used for this kind of data (e.g: fengel.customer)
	 * @param   string       $remoteKey        the remote key used to identify the data
	 * @param   string       $localId          the local id on our system
	 * @param   string       $remoteParentKey  the remote key used to identify the data
	 * @param   bool         $isNew            Flag is New Item
	 * @param   integer      $newSyncStatus    New status for the sync record (default = 0)
	 * @param   string       $serialize        Text serializing from current item
	 * @param   bool         $ignoreLocalId    Ignores the local id when updating
	 * @param   string       $newLocalId       Sends a new local id when updating
	 * @param   null|RTable  $table            Table item object
	 * @param   bool         $mainReference    Flag is it main reference
	 * @param   string       $hashedKey        Hashed Key of the synced Item
	 *
	 * @return boolean
	 *
	 * @throws Exception
	 */
	public function recordSyncedId(
		$reference, $remoteKey, $localId, $remoteParentKey = '', $isNew = true, $newSyncStatus = 0, $serialize = '',
		$ignoreLocalId = false, $newLocalId = '', $table = null, $mainReference = null, $hashedKey = null
	)
	{
		// First executes the parent function to do the actual record of the sync row
		if (!parent::recordSyncedId(
			$reference, $remoteKey, $localId, $remoteParentKey, $isNew, $newSyncStatus, $serialize,
			$ignoreLocalId, $newLocalId, $table, $mainReference, $hashedKey
		))
		{
			return false;
		}

		// Afterwards checks if extra processing is needed for temp files (sync status set to 0)
		if (!in_array($reference, $this->syncRefsUsingFile) || $newSyncStatus != 0 || !$this->redisDB->getIndex())
		{
			return true;
		}

		$this->redisDB->delKey($localId, $this->redisExecutionPrefix . '_' . $reference);

		return true;
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
		// Checks if deletion control is done in the db or via Redis control
		if (!$this->redisDB->getIndex() || !in_array($reference, $this->syncRefsUsingFile))
		{
			return parent::deleteRowsNotPresentInRemote($reference, $tableName, $statuses, $useTableClass, $keyName);
		}

		$keys = $this->redisDB->getKeysPattern($reference . '_*', $this->redisExecutionPrefix);

		if (count($keys))
		{
			$removedIds = array();
			$table      = RTable::getInstance($tableName, 'RedshopbTable')
				->setOption('forceWebserviceUpdate', true)
				->setOption('lockingMethod', 'Sync');

			// Splits keys in groups of 1000 to make it friendlier with the database
			$keysSplit = array_chunk($keys, 1000);

			foreach ($keysSplit as $keyPart)
			{
				foreach ($keyPart as $key)
				{
					if (!preg_match('/' . $this->redisExecutionPrefix . '_' . str_replace('.', '\.', $reference) . '_([0-9]+)/', $key, $matches))
					{
						continue;
					}

					$id = (int) $matches[1];

					if (!$id)
					{
						continue;
					}

					if ($this->isExecutionTimeExceeded())
					{
						$this->goToNextPart = true;
						self::addMessage(
							Text::sprintf('COM_REDSHOPB_SYNC_WEBSERVICE_DELETING_ROWS_TIME_EXCEEDED', count($removedIds), count($keysSplit))
						);

						return $removedIds;
					}

					if ($table->delete($id))
					{
						$this->redisDB->delKey($id, $this->redisExecutionPrefix . '_' . $reference);
						$removedIds[] = $id;
						$this->deleteSyncedLocalId($reference, $id);
					}
					else
					{
						self::addMessage($table->getError(), 'warning');

						$this->goToNextPart = true;

						return $removedIds;
					}
				}
			}

			return $removedIds;
		}

		return array();
	}

	/**
	 * Check if the item needs to be updated or not
	 *
	 * @param   stdClass  $sync  Sync object with reference and local_id
	 *
	 * @return boolean
	 */
	public function skipItemUpdate($sync)
	{
		// Checks if a temp file is in use, depending on the reference name.  If it's not, it uses the regular parent function.
		if (!$this->redisDB->getIndex() || !in_array($sync->reference, $this->syncRefsUsingFile))
		{
			if (!$sync)
			{
				return true;
			}

			$db       = Factory::getDbo();
			$query    = $db->getQuery(true)
				->set($db->qn('execute_sync') . ' = 0')
				->where($db->qn('reference') . ' = ' . $db->q($sync->reference));
			$useIndex = ' FORCE INDEX (' . $db->qn('PRIMARY') . ')';

			if (property_exists($sync, 'remote_parent_key'))
			{
				$query->where($db->qn('remote_parent_key') . ' = ' . $db->q($sync->remote_parent_key));
			}

			if (property_exists($sync, 'remote_key'))
			{
				$query->where($db->qn('remote_key') . ' = ' . $db->q($sync->remote_key));
			}
			elseif (property_exists($sync, 'local_id'))
			{
				$useIndex = ' FORCE INDEX (' . $db->qn('idx_local_id') . ')';
			}

			$query->update($db->qn('#__redshopb_sync') . $useIndex);

			if (property_exists($sync, 'local_id'))
			{
				$query->where($db->qn('local_id') . ' = ' . $db->q($sync->local_id));
			}

			return $this->executeQueryWithRetries($query);
		}

		$this->redisDB->delKey($sync->local_id, $this->redisExecutionPrefix . '_' . $sync->reference);

		return true;
	}

	/**
	 * Read full xml file and transforms it into XML object
	 *
	 * @param   string  $xmlFile  Name of xml file to get.
	 *
	 * @return  SimpleXMLElement
	 */
	public function getFullXmlObject($xmlFile)
	{
		if (empty($this->fullXmlObject))
		{
			// Gets Data from the XML
			$xml = @file_get_contents($this->client->localFolder . '/' . $xmlFile);

			$this->fullXmlObject = @new SimpleXMLElement($xml);
		}

		return $this->fullXmlObject;
	}

	/**
	 * Read the data from original XML document and search for a specific name
	 *
	 * @param   string  $name     Filter FieldSet name.
	 * @param   string  $xmlFile  Name of xml file. If none is given, defaults to the settings file.
	 *
	 * @return  SimpleXMLElement
	 */
	public function getSpecificFilterFieldSet($name, $xmlFile = '')
	{
		if (!$xmlFile)
		{
			$xmlFile = $this->settingsFile;
		}

		$fieldSets = $this->getFullXmlObject($xmlFile);

		if (isset($fieldSets->FieldSet))
		{
			foreach ($fieldSets->FieldSet as $fieldSet)
			{
				if (isset($fieldSet['Name']) && $fieldSet['Name'] == $name)
				{
					return $fieldSet;
				}
			}
		}
	}

	/**
	 * Check if the Resource file is changed
	 *
	 * @param   object  $itemData          Item Data
	 * @param   string  $resourceFileName  Resource File Name
	 * @param   array   $serialize         Serialized data
	 *
	 * @return  boolean
	 */
	public function isResourceChanged($itemData, $resourceFileName, &$serialize)
	{
		$fullPath    = (string) $this->client->localFolder . '/Pics/' . $resourceFileName;
		$currentFile = $itemData ? RedshopbHelperSync::mbUnserialize($itemData->serialize) : array();
		$serialize   = array(
			'modify'     => date("Y-m-d H:i:s", filemtime($fullPath)),
			'size'       => filesize($fullPath),
			'image'      => $resourceFileName,
		);

		if (!$itemData)
		{
			return true;
		}

		if (empty($currentFile['modify']) || ($serialize['modify'] != $currentFile['modify'] || $serialize['size'] != $currentFile['size']))
		{
			return true;
		}

		if ($currentFile['image'] != $resourceFileName)
		{
			return true;
		}

		return false;
	}
}
