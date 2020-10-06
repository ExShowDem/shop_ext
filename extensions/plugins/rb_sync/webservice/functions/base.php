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
use Joomla\CMS\Table\Table;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Language\LanguageHelper;
use Joomla\String\StringHelper;

/**
 * Function base class.
 *
 * @package     Aesir.E-Commerce.Plugin
 * @subpackage  Client
 * @since       1.0
 */
abstract class WebserviceFunctionBase extends RedshopbHelperSync
{
	/**
	 * The client.
	 *
	 * @var  RedshopbClientWebservice
	 */
	protected $client;

	/**
	 * @var string
	 */
	public $pluginName = 'webservice';

	/**
	 * @var string
	 */
	public $cronName = '';

	/**
	 * @var string
	 */
	public $tableName = '';

	/**
	 * @var array
	 */
	public $listExecutedLangs = array();

	/**
	 * @var string
	 */
	public $translationTable;

	/**
	 * @var string
	 */
	public $tableClassName;

	/**
	 * @var array
	 */
	public $dependencies = array();

	/**
	 * @var string
	 */
	public $dependFieldName = 'type';

	/**
	 * @var string
	 */
	public $key = 'id';

	/**
	 * @var string
	 */
	public $postFilterKey = '';

	/**
	 * @var string
	 */
	public $productModelKey = 'related_sku';

	/**
	 * @var string
	 */
	public $productModelId = 'product_id';

	/**
	 * List of items with parent has not synced yet.
	 *
	 * @var  array
	 */
	public $syncParentItems = array();

	/**
	 * Url for sync list of categories
	 *
	 * @var  string
	 */
	public $readListUrl = '';

	/**
	 * If set to true, we will use next language on the list and set counters to 0 again
	 *
	 * @var  boolean
	 */
	public $setToNextLanguage = false;

	/**
	 * @var array
	 */
	public $postFields = array();

	/**
	 * @var boolean
	 */
	public $deleteItemsNotPresentInRemote = false;

	/**
	 * @var array
	 */
	public $executedKeys = array();

	/**
	 * @var string
	 */
	public $syncLangName = 'translate.';

	/**
	 * @var array
	 */
	public $products = array();

	/**
	 * @var string
	 */
	public $currentLang;

	/**
	 * @var RedshopbEntityConfig
	 */
	public $redshopbConfig;

	/**
	 * @var string
	 */
	public $enrichmentBase = '';

	/**
	 * @var string
	 */
	public $enrichmentSyncRef = '';

	/**
	 * @var boolean
	 */
	public $processStoreOtherIds = true;

	/**
	 * Constructor.
	 *
	 */
	public function __construct()
	{
		parent::__construct();
		$this->cronName = substr(get_class($this), 10);
		$table          = RTable::getInstance($this->tableClassName, 'RedshopbTable');
		$nestedClass    = (stripos(get_parent_class($table), 'nested') === false ? false : true);

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
		$this->syncLangName     = 'translate.' . $this->syncName;
		$this->redshopbConfig   = RedshopbEntityConfig::getInstance();
	}

	/**
	 * Set the client.
	 *
	 * @param   RedshopbClientWebservice  $client  The client
	 *
	 * @return  WebserviceFunctionBase
	 */
	public function setClient(RedshopbClientWebservice $client)
	{
		$this->client = $client;

		return $this;
	}

	/**
	 * Return the client.
	 *
	 * @return  RedshopbClientWebservice
	 */
	public function getClient()
	{
		return $this->client;
	}

	/**
	 * Method for load data, using cURL function with GET method
	 *
	 * @param   string  $url          URL of remote server
	 * @param   array   $postFields   Values for POST method
	 * @param   array   $httpHeaders  Contain http headers
	 *
	 * @return  object/boolean  JSON object of result if success. False otherwise.
	 */
	public function getDataFromRemoteServer($url, $postFields = array(), $httpHeaders = array())
	{
		// Check cURL lirabries exist
		if (!function_exists('curl_exec') || empty($url))
		{
			return false;
		}

		$curl = curl_init($url);

		if (count($postFields))
		{
			curl_setopt($curl, CURLOPT_POST, 1);
			curl_setopt($curl, CURLOPT_POSTFIELDS, $postFields);
		}

		// Set return transfer
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

		// Enable compression
		if ((bool) $this->pluginParams->get('enableCompression', 1))
		{
			curl_setopt($curl, CURLOPT_ENCODING, 1);

			$httpHeaders[] = 'Accept-Encoding: gzip, deflate';
			$httpHeaders[] = 'Content-Encoding: gzip, deflate';
		}

		if (count($httpHeaders))
		{
			curl_setopt($curl, CURLOPT_HTTPHEADER, $httpHeaders);
		}

		$result = curl_exec($curl);

		$httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		curl_close($curl);

		if ($httpCode == 200)
		{
			$result = json_decode($result);
		}
		else
		{
			$result = $httpCode;
		}

		return $result;
	}

	/**
	 * Combine remote URL
	 *
	 * @return string
	 */
	public function combineUrl()
	{
		$limit = '&list[limit]=0';

		if ($this->postFilterKey == '')
		{
			// This is needed because if it is set to 0 it will ignore limitstart field
			$limitSize = $this->processItemsStep > 0 ? $this->processItemsStep : 9999999;
			$limit     = '&list[limit]=' . $limitSize . '&list[limitstart]=' . $this->processItemsCompleted;
		}

		return $this->client->serverUrl . $this->readListUrl
			// Limit for items, which related with products in function getProductInfoForSync
			. $limit
			. '&access_token=' . $this->client->getAccessToken();
	}

	/**
	 * Get result from webservice
	 *
	 * @param   string  $langTag              Language tag
	 * @param   string  $translationFallback  Translation Fallback flag
	 * @param   bool    $emptyResult          Link for getting empty result flag
	 *
	 * @return  boolean|object
	 */
	public function getResult($langTag, $translationFallback = 'true', &$emptyResult = false)
	{
		$result = false;

		$httpHeaders   = array();
		$httpHeaders[] = 'X-Webservice-Translation-Fallback: ' . $translationFallback;
		$httpHeaders[] = 'Accept-Language: ' . $langTag;

		$this->products = $this->getProductInfoForSync($this->productModelKey);

		if ($this->products || $this->postFilterKey == '')
		{
			$url    = $this->combineUrl();
			$result = $this->getDataFromRemoteServer($url, $this->postFields, $httpHeaders);

			if (!$result)
			{
				RedshopbHelperSync::addMessage(Text::_('PLG_RB_SYNC_WEBSERVICE_ERROR_CURL_FEATURE_NOT_AVAILABLE'), 'error');

				return false;
			}

			if (is_numeric($result))
			{
				if ($result == 204)
				{
					$emptyResult = true;
				}
				else
				{
					RedshopbHelperSync::addMessage(
						Text::sprintf(
							'PLG_RB_SYNC_WEBSERVICE_ERROR_CURL_RETURN_STATUS',
							$result . ' - ' . (isset(RApiBase::$statusTexts[$result]) ? RApiBase::$statusTexts[$result] : ''
							)
						),
						'error'
					);

					return false;
				}
			}
		}
		else
		{
			$emptyResult = true;
		}

		if (!$emptyResult)
		{
			// If error occur
			if (isset($result->_messages) && !empty($result->_messages))
			{
				foreach ($result->_messages as $message)
				{
					RedshopbHelperSync::addMessage($message->message, $message->type);
				}

				return false;
			}
		}

		return $result;
	}

	/**
	 * handle One Language Data
	 *
	 * @param   object  $result  WS object
	 *
	 * @return  boolean
	 */
	public function handleOneLanguageData($result)
	{
		if (empty($result->_embedded->item))
		{
			return false;
		}

		$items = $result->_embedded->item;

		// Get list executed in previous sync items because this is multipart process
		if ($this->currentLang)
		{
			$this->executed = $this->getPreviousSyncExecutedList($this->syncLangName, $this->currentLang);
		}
		else
		{
			$this->executed = $this->getPreviousSyncExecutedList($this->syncName);
		}

		if ($this->postFilterKey == '')
		{
			if (isset($result->totalItems) && !empty($result->totalItems))
			{
				$this->counterTotal = (int) $result->totalItems;
			}
			else
			{
				$this->counterTotal = count($items);
			}
		}

		// Start sync with the new XML data.
		$this->processData($items);

		return true;
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
		$this->counterTotal = (int) $webserviceData->get('items_total', 0);
		$syncProcessSkip    = false;

		try
		{
			$this->listExecutedLangs = (array) $this->params->get('listExecutedLangs', array());
			$emptyResult             = false;

			// Check to see if we are already run Sync which is not finished (so we can have multiple parts if webservice is too big)
			if (!$this->isCronExecuted($this->cronName))
			{
				// We set them with a flag so we can delete the ones which are not present in the latest Sync xml
				$counter = $this->setSyncRowsAsExecuted($this->syncName, null, true);

				if ($this->goToNextPart)
				{
					$syncProcessSkip = true;
					RedshopbHelperSync::addMessage(Text::sprintf('PLG_RB_SYNC_WEBSERVICE_RESET_SYNC_COUNTERS', $this->syncName, $counter));
				}
				else
				{
					$this->setSyncRowsAsExecuted($this->syncLangName, null, true);

					// We set cron as executed so we can have multipart process if the process takes too long
					$this->setCronAsExecuted($this->cronName);
				}
			}
		}
		catch (Exception $error)
		{
			if ($error->getMessage())
			{
				RedshopbHelperSync::addMessage($error->getMessage(), 'error');
			}

			return false;
		}

		try
		{
			if ($this->useTransaction)
			{
				$db->transactionStart();
			}

			if (!$this->goToNextPart)
			{
				// Here executing main language
				if (count($this->listExecutedLangs) == 0)
				{
					$defLangData = LanguageHelper::getLanguages('lang_code');
					$defLangData = $defLangData[$this->default_lang];
					$langCode    = $defLangData->lang_code . ',' . $defLangData->sef . ';q=1';
					$result      = $this->getResult($langCode, 'true', $emptyResult);

					if (!$emptyResult)
					{
						if (!$result)
						{
							throw new Exception(Text::sprintf('PLG_RB_SYNC_WEBSERVICE_HANDLE_LANG_DATA_EMPTY_RESULT', $this->currentLang));
						}

						if (!$this->handleOneLanguageData($result))
						{
							if ($this->useTransaction)
							{
								$db->transactionCommit();
							}

							$db->unlockTables();
							$this->counter += count($this->products);
							$this->counter += $this->processItemsCompleted;

							return $this->outputResult();
						}

						// We might be using nested tables so just in case we will unlock the table object if the store function have failed
						$db->unlockTables();
					}

					$this->counter += count($this->products);
					$this->counter += $this->processItemsCompleted;

					// If we not reached last item, then go to the next part
					if ($this->counter < $this->counterTotal)
					{
						$this->goToNextPart = true;
					}

					if (!$this->goToNextPart)
					{
						// Re-structure these synced items.
						$this->reSyncStructure();

						if ($this->isExecutionTimeExceeded())
						{
							$this->goToNextPart = true;
						}
					}

					if (!$this->goToNextPart && $this->translationTable && count($this->lang_codes))
					{
						$langCode                  = reset($this->lang_codes);
						$this->listExecutedLangs[] = $langCode->lang_code;
						$this->setToNextLanguage   = true;
						$this->goToNextPart        = true;
					}
				}
				elseif ($this->translationTable)
				{
					$this->currentLang = end($this->listExecutedLangs);
					$currentLangData   = $this->lang_codes[$this->currentLang];
					$langCode          = $currentLangData->lang_code . ',' . $currentLangData->sef . ';q=1';
					$result            = $this->getResult($langCode, 'false', $emptyResult);

					if ($this->processItemsCompleted == 0)
					{
						RedshopbHelperSync::addMessage(Text::sprintf('PLG_RB_SYNC_WEBSERVICE_HANDLE_LANG_DATA', $this->currentLang));
					}

					if (!$emptyResult)
					{
						if (!$result)
						{
							throw new Exception(Text::sprintf('PLG_RB_SYNC_WEBSERVICE_HANDLE_LANG_DATA_EMPTY_RESULT', $this->currentLang));
						}

						if (!$this->handleOneLanguageData($result))
						{
							if ($this->useTransaction)
							{
								$db->transactionCommit();
							}

							$db->unlockTables();
							$this->counter += count($this->products);
							$this->counter += $this->processItemsCompleted;

							return $this->outputResult();
						}

						// We might be using nested tables so just in case we will unlock the table object if the store function have failed
						$db->unlockTables();
					}

					$this->counter += count($this->products);
					$this->counter += $this->processItemsCompleted;

					// If we not reached last item, then go to the next part
					if ($this->counter < $this->counterTotal)
					{
						$this->goToNextPart = true;
					}

					if (!$this->goToNextPart)
					{
						foreach ($this->lang_codes as $langCode => $langData)
						{
							if (!in_array($langCode, $this->listExecutedLangs))
							{
								$this->listExecutedLangs[] = $langCode;
								$this->goToNextPart        = true;
								break;
							}
						}
					}

					if (!$this->goToNextPart)
					{
						$this->listExecutedLangs = array();
					}
				}
			}

			if ($this->useTransaction)
			{
				$db->transactionCommit();
			}

			if (!$this->goToNextPart)
			{
				if ($this->translationTable && count($this->lang_codes))
				{
					$translateTableName = RTranslationTable::getTranslationsTableName($this->translationTable->table, '');
					$this->deleteRowsNotPresentInRemote($this->syncLangName, $translateTableName, array(1, 2), false, 'rctranslations_id');

					if ($this->isExecutionTimeExceeded())
					{
						$this->goToNextPart = true;
					}

					// We might be using nested tables so just in case we will unlock the table object if the store function have failed
					$db->unlockTables();
				}

				if (!$this->goToNextPart)
				{
					if ($this->deleteItemsNotPresentInRemote == true)
					{
						$tableForDeleteItems = $this->tableClassName;
					}
					else
					{
						$tableForDeleteItems = '';
					}

					try
					{
						// Remove items that were not present in the XML data
						$this->deleteRowsNotPresentInRemote($this->syncName, $tableForDeleteItems, array(), true);
					}
					catch (Exception $e)
					{
						RedshopbHelperSync::addMessage($e->getMessage(), 'error');
					}

					// We are setting cron as finished (no more parts)
					$this->setCronAsFinished($this->cronName);

					// We might be using nested tables so just in case we will unlock the table object if the store function have failed
					$db->unlockTables();
				}
			}

			$this->params->set('listExecutedLangs', $this->listExecutedLangs);
			$this->cronTable->save(array('params' => $this->params->toString()));

			if (!$syncProcessSkip)
			{
				// Sets number of executed items in Cron
				$this->setProgressCounters($this->cronTable, null, $this->counterTotal);
			}

			$db->unlockTables();
		}
		catch (Exception $error)
		{
			if ($this->useTransaction)
			{
				$db->transactionRollback();
			}

			// We might be using nested tables so just in case we will unlock the table object if the store function have failed
			$db->unlockTables();

			if ($error->getMessage())
			{
				RedshopbHelperSync::addMessage($error->getMessage(), 'error');
			}

			return false;
		}

		if ($this->counter == 0)
		{
			$this->counter += $this->processItemsCompleted;
		}

		return $this->outputResult();
	}

	/**
	 * Read and store the data.
	 *
	 * @param   array  $items  Array of categories object
	 *
	 * @return  boolean
	 */
	public function processData($items)
	{
		if (empty($items))
		{
			return false;
		}

		foreach ($items as $key => $item)
		{
			if ($this->isExecutionTimeExceeded())
			{
				$this->goToNextPart = true;
				break;
			}

			$this->preSyncItem($item);

			if ($this->postFilterKey == '')
			{
				$this->counter++;
			}
		}

		return true;
	}

	/**
	 * Pre sync item
	 *
	 * @param   object  $item  Item object data
	 *
	 * @return  void
	 */
	public function preSyncItem($item)
	{
		if (empty($item))
		{
			return;
		}

		$table = RTable::getInstance($this->tableClassName, 'RedshopbTable')
			->setOption('forceWebserviceUpdate', true)
			->setOption('lockingMethod', 'Sync');

		if (!$this->currentLang)
		{
			if ($this->synchronizeItem($item, $table))
			{
				if ($this->processStoreOtherIds)
				{
					$this->storeOtherIds($item, $table);
				}
			}
		}
		else
		{
			$this->synchronizeTranslationItem($item, $table);
		}
	}

	/**
	 * Method for synchronize an single category
	 *
	 * @param   object  $item   Item object data
	 * @param   Table   $table  Table object
	 *
	 * @return  boolean
	 */
	public function synchronizeTranslationItem($item, $table)
	{
		$remoteId = $item->{$this->key};

		// If another sync process for this tag is running. Skip this.
		if (isset($this->executed[$remoteId . '_' . $this->currentLang]))
		{
			return false;
		}

		$id = $this->findSyncedId($this->syncName, $remoteId);

		if (!$id || !$table->load($id))
		{
			return true;
		}

		$isNew             = true;
		$foundTranslations = false;
		$translateValues   = array();

		foreach ($this->translationTable->columns as $column)
		{
			if (isset($item->{$column}) && !in_array($column, $this->translationTable->primaryKeys))
			{
				if ($item->{$column} != '')
				{
					$translateValues[$column] = $item->{$column};
					$foundTranslations        = true;
				}
				else
				{
					$translateValues[$column] = null;
				}
			}
		}

		$allData = $this->findSyncedId($this->syncLangName, $remoteId, $this->currentLang, true);

		if ($allData)
		{
			$isNew = false;

			if (!$foundTranslations)
			{
				$result = $this->deleteTranslation($this->translationTable, $table, $this->currentLang);

				if ($result !== true)
				{
					RedshopbHelperSync::addMessage($result, 'error');

					return false;
				}
			}
		}

		foreach ($this->translationTable->primaryKeys as $primaryKey)
		{
			$translateValues[$primaryKey] = $table->get($primaryKey);
		}

		if (!$foundTranslations && !is_null($allData))
		{
			$translationId = $allData->local_id;

			// Just store an empty record for have right items count, thats fields will deletes after finish all parts
			$this->recordSyncedId($this->syncLangName, $remoteId, $translationId, $this->currentLang, $isNew, 2);

			return true;
		}

		$translationId = 0;
		$result        = $this->storeTranslation($this->translationTable, $table, $this->currentLang, $translateValues, $translationId);

		if ($result !== true)
		{
			RedshopbHelperSync::addMessage($result, 'error');

			return false;
		}

		if (!$translationId)
		{
			return false;
		}

		$this->recordSyncedId($this->syncLangName, $remoteId, $translationId, $this->currentLang, $isNew);

		return true;
	}

	/**
	 * Method for synchronize an single category
	 *
	 * @param   object  $item   Item object data
	 * @param   Table   $table  Table object
	 *
	 * @return  boolean
	 */
	abstract public function synchronizeItem($item, $table);

	/**
	 * Store other ids
	 *
	 * @param   object  $item   Item values
	 * @param   Table   $table  Table for current item
	 *
	 * @throws  Exception
	 *
	 * @return  void
	 */
	public function storeOtherIds($item, $table)
	{
		if (isset($item->id_others) && is_array($item->id_others) && count($item->id_others) > 0)
		{
			$wsSyncMapPK = $table->get('wsSyncMapPK', array());
			$id          = $table->get('id');

			/**
			 * If the item is skipped because of the hashed keyes or for some other reason,
			 * then this table item is not loaded therefore we will simply skip it
			 */
			if ($id)
			{
				foreach ($item->id_others as $idOther)
				{
					$result   = explode('.', $idOther);
					$prefix   = $result[0];
					$remoteId = $result[1];

					if (array_key_exists($prefix, $wsSyncMapPK))
					{
						if (isset($item->{$this->dependFieldName}) && array_key_exists($item->{$this->dependFieldName}, $this->dependencies))
						{
							$syncName = $this->dependencies[$item->{$this->dependFieldName}];
						}
						else
						{
							$syncName = $wsSyncMapPK[$prefix][0];
						}

						$otherItem      = $this->findSyncedId($syncName, $remoteId);
						$isNewOtherData = true;

						if ($otherItem)
						{
							$isNewOtherData = false;
						}

						if ($otherItem != $id)
						{
							$this->deleteSyncedId($syncName, $remoteId);
							$isNewOtherData = true;
						}

						$this->recordSyncedId($syncName, $remoteId, $id, '', $isNewOtherData);
					}
				}
			}
		}
	}

	/**
	 * Method for re-structure these tags if they are still missing parent-child relation in sync process.
	 *
	 * @return  boolean  True on success. False otherwise.
	 */
	public function reSyncStructure()
	{
		// If all tags has synced success.
		if (empty($this->syncParentItems))
		{
			return true;
		}

		foreach ($this->syncParentItems as $remoteParentId => $remoteChildrenIds)
		{
			$localParentId = $this->findSyncedId($this->syncName, $remoteParentId);

			foreach ($remoteChildrenIds as $remoteChildId)
			{
				$localChildId = $this->findSyncedId($this->syncName, $remoteChildId);

				// Update structure on local
				$table = RTable::getInstance($this->tableClassName, 'RedshopbTable')
					->setOption('forceWebserviceUpdate', true)
					->setOption('lockingMethod', 'Sync')
					->load($localChildId)
					->setLocation($localParentId, 'last-child');

				if ($table->store())
				{
					// Update sync reference table data
					$this->recordSyncedId($this->syncName, $remoteChildId, $localChildId, '', false);
				}
			}
		}

		return true;
	}

	/**
	 * Get unit id
	 *
	 * @param   string  $unitCode   Unit code
	 * @param   string  $table      Table name
	 * @param   string  $fieldName  Field name for condition
	 *
	 * @return  mixed
	 */
	public function getUnitId($unitCode, $table, $fieldName = 'alias')
	{
		if (!$unitCode)
		{
			return null;
		}

		static $unitCodes = array();
		$key              = $table . '_' . $fieldName;

		if (!isset($unitCodes[$key]))
		{
			$unitCodes[$key] = array();
		}

		if (!array_key_exists($unitCode, $unitCodes[$key]))
		{
			$db                         = Factory::getDbo();
			$query                      = $db->getQuery(true)
				->select('id')
				->from($db->qn($table))
				->where($db->qn($fieldName) . ' = ' . $db->q($unitCode));
			$unitCodes[$key][$unitCode] = $db->setQuery($query, 0, 1)
				->loadResult();
		}

		return $unitCodes[$key][$unitCode];
	}

	/**
	 * Get Unique UserGroup Name
	 *
	 * @param   string  $name       Current name
	 * @param   int     $currentId  Current id
	 * @param   string  $tableName  Table name
	 * @param   string  $fieldName  Field name for condition
	 *
	 * @return  string
	 */
	public function getUniqueFieldName($name = '', $currentId = 0, $tableName = '', $fieldName = 'name')
	{
		if (!$tableName)
		{
			$tableName = $this->tableName;
		}

		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->select($db->qn($fieldName))
			->from($db->qn($tableName))
			->where($db->qn($fieldName) . ' = ' . $db->q(trim($name)))
			->where('id <> ' . (int) $currentId);

		while ($result = $db->setQuery($query, 0, 1)->loadResult())
		{
			$name = StringHelper::increment($result, 'dash');
			$query->clear('where')
				->where($db->qn($fieldName) . ' = ' . $db->q(trim($name)))
				->where('id <> ' . (int) $currentId);
		}

		return $name;
	}

	/**
	 * Set the criteria for product info sync query
	 *
	 * @param   JDatabaseQuery  $query   Product sync query from getProductInfoForSync()
	 * @param   string          $base    Base for the product sync (related_sku / remote_key)
	 *
	 * @return void
	 */
	public function setProductSyncCriteria(&$query, $base = 'related_sku')
	{
		$db = Factory::getDbo();

		switch ($base)
		{
			case 'related_sku':
				$query->where($db->qn('p.related_sku') . ' <> ' . $db->q(''));
				break;

			case 'remote_key':
				$query->join(
					'inner',
					$db->qn('#__redshopb_sync', 's') .
					' ON ' . $db->qn('s.local_id') . ' = ' . $db->qn('p.id') .
					' AND ' . $db->qn('s.reference') . ' = ' . $db->q('erp.webservice.products')
				);
				break;
		}
	}

	/**
	 * Get Related SKU product info
	 *
	 * @param   string  $base  Base for the product sync (related_sku / remote_key)
	 *
	 * @return mixed
	 */
	public function getProductInfoForSync($base = 'related_sku')
	{
		// If we are not grouping by Item then we do not need to use this feature
		if ($this->postFilterKey == '')
		{
			return array();
		}

		$db    = Factory::getDbo();
		$query = $db->getQuery(true);

		// Query # 1: Total counter query
		$query->select('COUNT(*)')
			->from($db->qn('#__redshopb_product', 'p'));
		$this->setProductSyncCriteria($query, $base);
		$this->counterTotal = (int) $db->setQuery($query, 0, 1)
			->loadResult();

		// Query # 3: Sync data query ($products)
		$query->clear()
			->select($db->qn('p.id'));

		switch ($base)
		{
			case 'related_sku':
				$query->select($db->qn('p.related_sku'));
				break;

			case 'remote_key':
				$query->select($db->qn('s.remote_key'));
				break;
		}

		$query->from($db->qn('#__redshopb_product', 'p'))
			->order('p.id ASC');
		$this->setProductSyncCriteria($query, $base);
		$products = $db->setQuery($query, $this->processItemsCompleted, $this->processItemsStep)
			->loadObjectList($base);

		$postFields = array(0);

		if ($products)
		{
			if ($this->postFilterKey)
			{
				$postFields = array();

				foreach (array_keys($products) as $key => $sku)
				{
					$postFields[$key] = $sku;
				}
			}
		}

		if ($this->postFilterKey)
		{
			$this->postFields['filter[' . $this->postFilterKey . ']'] = json_encode($postFields);
		}

		return $products;
	}

	/**
	 * Set user info for create and modify
	 *
	 * @param   array  $row    Current row array
	 * @param   bool   $isNew  Is the record new
	 *
	 * @return void
	 */
	public function setUserInformation(&$row, $isNew)
	{
		$userId = Factory::getUser()->id;
		$date   = Factory::getDate()->toSql();

		if ($isNew)
		{
			if ($userId)
			{
				$row['created_by'] = $userId;
			}

			$row['created_date'] = $date;
		}
		else
		{
			if ($userId)
			{
				$row['modified_by'] = $userId;
			}

			$row['modified_date'] = $date;
		}
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
			$counter = $this->counter;
			$type    = 'info';

			if ($this->setToNextLanguage)
			{
				$this->setToNextLanguage = false;
				$this->counter           = 0;
				$type                    = 'success';
			}

			static::addMessage(Text::sprintf('COM_REDSHOPB_SYNC_WEBSERVICE_GOTO_NEXT_PART', $counter, $this->counterTotal), $type);

			return array('parts' => $this->counterTotal - $this->counter, 'total' => $this->counterTotal, 'isContinuous' => $this->isContinuous);
		}

		return parent::outputResult();
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
		parent::setDefaultCronParams($webserviceData, $pluginParams);

		$this->deleteItemsNotPresentInRemote = (bool) $this->params->get('deleteItemsNotPresentInRemote', 0);
	}

	/**
	 * Set enrichment base
	 *
	 * @return  void
	 */
	public function setEnrichmentBase()
	{
		// Sets the enrichment base and reference to use them later when syncing
		$enrichmentBase = RedshopbHelperSync::getEnrichmentBase();

		if ($enrichmentBase != '' && $enrichmentBase != 'b2b')
		{
			$this->enrichmentBase = $enrichmentBase . '.';
			$table                = RedshopbTable::getAdminInstance($this->tableClassName);
			$fieldMapping         = $table->get('wsSyncMapPK');

			if (is_array($fieldMapping) && isset($fieldMapping[$enrichmentBase]) && is_array($fieldMapping[$enrichmentBase]))
			{
				$this->enrichmentSyncRef = $fieldMapping[$enrichmentBase][0];
			}
		}
	}

	/**
	 * Read and store the data.
	 *
	 * @param   string  $sku  Related Sku
	 *
	 * @return  array
	 */
	public function getProductsByRelatedSku($sku)
	{
		$db = Factory::getDbo();

		// Get all products with same related_sku
		$query = $db->getQuery(true)
			->select('p.id')
			->from($db->qn('#__redshopb_product', 'p'))
			->where($db->qn('p.related_sku') . ' = ' . $db->q((string) $sku));

		$productIds = $db->setQuery($query)->loadColumn();

		return $productIds;
	}

	/**
	 * Read and store the data.
	 *
	 * @param   string  $relatedId  Related Id
	 *
	 * @return  array
	 */
	public function getProductsByRelatedId($relatedId)
	{
		$db = Factory::getDbo();

		// Get all products with same related_sku
		$query = $db->getQuery(true)
			->select('s.local_id')
			->from($db->qn('#__redshopb_sync', 's'))
			->where($db->qn('s.remote_key') . ' = ' . $db->q((string) $relatedId))
			->where($db->qn('s.reference') . ' = ' . $db->q('erp.webservice.products'));

		$productIds = $db->setQuery($query)->loadColumn();

		return $productIds;
	}
}
