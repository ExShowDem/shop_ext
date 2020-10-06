<?php
/**
 * @package     Aesir.E-Commerce.Plugin
 * @subpackage  Filesync
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('JPATH_BASE') or die;

use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Factory;
use Joomla\Registry\Registry;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Language\Text;

/**
 * Class RbfilesyncHelperParser
 *
 * @since  1.6.24
 */
class RbfilesyncHelperParser extends RApiHalHal
{
	/**
	 * @var string
	 */
	public $fileFormat = 'xml';

	/**
	 * @var array
	 */
	public $references = array();

	/**
	 * Method to instantiate the file-based api call.
	 *
	 * @param   Registry|array  $options  Optional custom options to load.
	 *
	 * @throws Exception
	 */
	public function __construct($options = null)
	{
		$this->startTime = microtime(true);

		// Initialise / Load options
		$this->setOptions($options);

		// Load Library language
		$this->loadExtensionLanguage('lib_joomla', JPATH_ADMINISTRATOR);

		PluginHelper::importPlugin('redcore');

		$this->webserviceName     = $this->options->get('optionName', '');
		$this->optionName         = $this->options->get('optionName', '');
		$viewName                 = $this->options->get('viewName', '');
		$this->webserviceName    .= !empty($this->webserviceName) && !empty($viewName) ? '-' . $viewName : '';
		$this->client             = $this->options->get('webserviceClient', 'site');
		$this->webserviceVersion  = $this->options->get('webserviceVersion', '0');
		$this->fileFormat         = $this->options->get('fileFormat', 'xml');
		$this->webserviceParams   = $this->options->get('webserviceParams', new Registry);
		$this->lastExecuteVersion = $this->webserviceParams->get('lastExecuteVersion', $this->options->get('webserviceVersion', '0'));

		$this->webservicePath = $this->options->get('path', 'filesync');

		// Set initial status code
		$this->setStatusCode($this->statusCode);

		$this->sync                = new RedshopbHelperSync;
		$this->sync->pluginName    = 'filesync';
		$this->cronFunctionName    = $this->options->get('cronFunctionName', '');
		$this->numberOfFilesToLoad = $this->options->get('numberOfFilesToLoad', 300);
		$this->reference           = 'erp.filesync.' . $this->webserviceName;

		// Check for defined constants
		if (!defined('JSON_UNESCAPED_SLASHES'))
		{
			define('JSON_UNESCAPED_SLASHES', 64);
		}
	}

	/**
	 * Instance class RbfilesyncHelperParser
	 *
	 * @param   Joomla\Registry\Registry  $options  Plugin param
	 *
	 * @return  RbfilesyncHelperParser
	 */
	public static function getInstance($options = array())
	{
		// Get the options signature for the api connector.
		$signature = md5(serialize($options));

		if (!isset(self::$instances[$signature]))
		{
			self::$instances[$signature] = new RbfilesyncHelperParser($options);
		}

		return self::$instances[$signature];
	}

	/**
	 * Execute All
	 *
	 * @return boolean
	 */
	public function executeAll()
	{
		$webServices = RbfilesyncHelperHal::getNotExecutedWebServices(
			$this->client, $this->webserviceName, $this->lastExecuteVersion, $this->webservicePath . '/definitions'
		);

		if (count($webServices) > 0)
		{
			$versions = $webServices[$this->client][$this->webserviceName];
			$db       = Factory::getDbo();
			$db->transactionStart();
			$cronExecuted = true;

			// Check to see if we are already run Sync which is not finished (so we can have multiple parts if webservice is too big)
			if (!$this->sync->isCronExecuted($this->cronFunctionName))
			{
				$cronExecuted = false;
				$this->sync->setSyncRowsAsExecuted($this->reference);

				// We set cron as executed so we can have multipart process if the process takes too long
				$this->sync->setCronAsExecuted($this->cronFunctionName);
			}

			try
			{
				foreach ($versions as $version => $xml)
				{
					$this->webserviceVersion = $version;
					$this->configuration     = $xml;

					// Set option and view name
					$this->setOptionViewName($this->webserviceName, $this->configuration);
					$this->elementName = ucfirst(strtolower((string) $this->getConfig('config.name')));
					$this->executeOne('update', $cronExecuted, array(2));
					$this->executeOne('delete', $cronExecuted, array(3));

					if (!$this->sync->goToNextPart)
					{
						// Store number last execute version
						$this->webserviceParams->set('lastExecuteVersion', $version);
						$query = $db->getQuery(true)
							->update($db->qn('#__redshopb_cron'))
							->set('params = ' . $db->q((string) $this->webserviceParams))
							->where('plugin = ' . $db->q($this->sync->pluginName))
							->where('name = ' . $db->q($this->cronFunctionName));
						$db->setQuery($query)->execute();

						// Reset all reference items for next version sync
						$this->sync->setSyncRowsAsExecuted($this->reference);
					}
				}

				if (!$this->sync->goToNextPart)
				{
					// We are setting cron as finished (no more parts)
					$this->sync->setCronAsFinished($this->cronFunctionName);
				}

				$db->transactionCommit();
			}
			catch (Exception $e)
			{
				$db->transactionRollback();

				if ($e->getMessage())
				{
					RedshopbHelperSync::addMessage($e->getMessage(), 'error');
				}

				return false;
			}
		}

		if ($this->sync->goToNextPart)
		{
			RedshopbHelperSync::addMessage(Text::sprintf('PLG_RB_SYNC_FILESYNC_GOTO_NEXT_PART', $this->sync->counter, $this->sync->counterTotal));

			return array('parts' => $this->sync->counterTotal - $this->sync->counter, 'total' => $this->sync->counterTotal);
		}
		else
		{
			RedshopbHelperSync::addMessage(Text::_('PLG_RB_SYNC_FILESYNC_SYNCHRONIZE_SUCCESS'), 'success');

			return true;
		}
	}

	/**
	 * Execute one file
	 *
	 * @param   string  $operation     Name operation
	 * @param   bool    $cronExecuted  Flag cron executed
	 * @param   array   $statuses      Select items with current list status
	 *
	 * @return  void
	 */
	public function executeOne($operation, $cronExecuted = true, $statuses = array())
	{
		if ($this->sync->goToNextPart || $this->sync->isExecutionTimeExceeded())
		{
			$this->sync->goToNextPart = true;

			return;
		}

		if ($cronExecuted)
		{
			$this->sync->executed = $this->sync->getPreviousSyncExecutedList($this->reference, null, $statuses);
			$this->sync->counter += count($this->sync->executed);
		}

		$this->operationConfiguration = $this->getConfig('operations.' . $operation);
		$this->triggerFunction('api' . ucfirst($operation));
	}

	/**
	 * Execute the Api Update operation.
	 *
	 * @return  mixed  RApi object with information on success, boolean false on failure.
	 */
	public function apiUpdate()
	{
		$model                    = $this->triggerFunction('loadModel', $this->elementName, $this->operationConfiguration);
		$functionName             = RbfilesyncHelperHal::attributeToString($this->operationConfiguration, 'functionName', 'save');
		$allData                  = $this->triggerFunction('getData', 'update');
		$this->sync->counterTotal = $this->sync->counterTotal + count($allData);

		foreach ($allData as $data)
		{
			if ($this->sync->goToNextPart || $this->sync->isExecutionTimeExceeded() || $this->sync->counter > $this->numberOfFilesToLoad)
			{
				$this->sync->goToNextPart = true;
				break;
			}

			$this->sync->counter++;
			$this->options->set('data', $data);
			$dataResult            = $this->triggerFunction('processPostData', $data, $this->operationConfiguration);
			$data                  = $dataResult->default;
			$primaryKeys           = array();
			$primaryKeysFromFields = RApiHalHelper::getFieldsArray($this->operationConfiguration, true);

			if ($primaryKeysFromFields)
			{
				foreach ($primaryKeysFromFields as $key => $primaryKeysFromField)
				{
					$primaryKeys[$key] = (string) $data[$key];
				}
			}

			// Without primary key we can`t store
			if (!count($primaryKeys))
			{
				continue;
			}

			$typeName  = $this->getTypeName($primaryKeys);
			$remoteKey = serialize($primaryKeys);

			if (isset($this->sync->executed[$remoteKey . '_' . $typeName]))
			{
				continue;
			}

			list($data, $optionalRemoteKeys) = $this->triggerFunction('getRemoteOptionalIds', $data, $this->operationConfiguration);
			$data                            = $this->triggerFunction('validatePostData', $model, $data, $this->operationConfiguration);

			if ($data === false)
			{
				// Not Acceptable
				$this->setStatusCode(406);
				$this->triggerFunction('displayErrors', $model);
				$this->setData('result', $data);

				return false;
			}

			// Prepare parameters for the function
			$result             = null;
			$isNew              = true;
			$serializeId        = '';
			$oldSerializeValues = '';
			$syncData           = $this->sync->findSyncedId($this->reference, $remoteKey, $typeName, true);

			if ($syncData)
			{
				$isNew              = false;
				$serializeId        = $syncData->local_id;
				$oldSerializeValues = $syncData->serialize;

				// Data exists, so update only new values
				$data = $dataResult->strict;
			}

			$oldUnSerializeValues              = RedshopbHelperSync::mbUnserialize($oldSerializeValues);
			list($data, $oldUnSerializeValues) = $this->triggerFunction('storeFiles', $data, $this->operationConfiguration, $oldUnSerializeValues);
			$oldSerializeValues                = serialize($oldUnSerializeValues);
			$args                              = $this->buildFunctionArgs($this->operationConfiguration, $data);

			if ($isNew)
			{
				foreach ($primaryKeys as $key => $primaryKey)
				{
					unset($args[0][$key]);
				}
			}
			else
			{
				$unSerializeId = RedshopbHelperSync::mbUnserialize($serializeId);

				foreach ($primaryKeys as $key => $primaryKey)
				{
					$args[0][$key] = $unSerializeId[$key];
				}
			}

			// Checks if that method exists in model class and executes it
			if (method_exists($model, $functionName))
			{
				$result = $this->triggerCallFunction($model, $functionName, $args);
			}
			else
			{
				$this->setStatusCode(400);
			}

			if (method_exists($model, 'getState'))
			{
				if (!empty($this->viewName))
				{
					$elementName = $this->viewName;
				}
				else
				{
					$elementName = $this->elementName;
				}

				$id = $model->getState(strtolower($elementName) . '.id');
				$this->setData('id', $id);

				if ($id && $isNew)
				{
					$table       = $model->getTable();
					$key         = $table->getKeyName();
					$serializeId = serialize(array($key => (string) $id));
				}
			}

			$this->setData('result', $result);
			$this->triggerFunction('displayErrors', $model);

			if ($result !== false && $serializeId)
			{
				$this->sync->recordSyncedId($this->reference, $remoteKey, $serializeId, $typeName, $isNew, 2, $oldSerializeValues);

				if ($isNew)
				{
					$ownKey        = $primaryKeys;
					$unSerializeId = RedshopbHelperSync::mbUnserialize($serializeId);

					foreach ($unSerializeId as $key => $keyId)
					{
						$ownKey[$key] = (string) $keyId;
					}

					$this->sync->recordSyncedId($this->reference, serialize($ownKey), $serializeId, 'OWN', $isNew, 2, $oldSerializeValues);
				}

				$excludeTypes = array();

				if (count($optionalRemoteKeys) > 0)
				{
					$relationRemoteKeys = $this->findSyncedRemoteId($this->reference, $serializeId, $excludeTypes);

					foreach ($optionalRemoteKeys as $optionTypeName => $typeValues)
					{
						$optionRemoteKey = $primaryKeys;

						foreach ($typeValues as $keyName => $keyValue)
						{
							$optionRemoteKey[$keyName] = (string) $keyValue;
						}

						$optionRemoteKey = serialize($optionRemoteKey);

						if (isset($relationRemoteKeys[$optionTypeName]))
						{
							$this->sync->deleteSyncedId(
								$this->reference,
								$relationRemoteKeys[$optionTypeName]->remote_key,
								$relationRemoteKeys[$optionTypeName]->remote_parent_key
							);
						}

						$this->sync->recordSyncedId(
							$this->reference, $optionRemoteKey, $serializeId, $optionTypeName, true, 2, $oldSerializeValues
						);
					}

					$excludeTypes = array_keys($optionalRemoteKeys);
				}

				$excludeTypes[]     = $typeName;
				$relationRemoteKeys = $this->findSyncedRemoteId($this->reference, $serializeId, $excludeTypes);

				// Check existing other remote keys for the same item, if have - store the same parameters
				if ($relationRemoteKeys)
				{
					foreach ($relationRemoteKeys as $relationRemoteKey)
					{
						$this->sync->recordSyncedId(
							$this->reference,
							$relationRemoteKey->remote_key,
							$serializeId,
							$relationRemoteKey->remote_parent_key,
							false,
							2,
							$oldSerializeValues
						);
					}
				}
			}

			if ($this->statusCode < 400)
			{
				if ($result === false)
				{
					// If update failed then we set it to Internal Server Error status code
					$this->setStatusCode(500);
				}
			}
		}
	}

	/**
	 * Lookup in sync table for already synced data
	 *
	 * @param   string  $reference               the reference used for this kind of data (e.g: fengel.customer)
	 * @param   string  $localId                 the local id used to identify the data
	 * @param   array   $excludeRemoteParentKey  the array remote parent key used to identify the data which must not have relation, if needed
	 *
	 * @return null|array
	 */
	public function findSyncedRemoteId($reference, $localId, $excludeRemoteParentKey = array())
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->select('*')
			->from($db->qn('#__redshopb_sync'))
			->where('reference = ' . $db->q($reference))
			->where('local_id = ' . $db->q($localId));

		if (is_array($excludeRemoteParentKey) && count($excludeRemoteParentKey) > 0)
		{
			$excludeRemoteParentKey = RHelperArray::quote($excludeRemoteParentKey);
			$query->where('remote_parent_key NOT IN (' . implode(',', $excludeRemoteParentKey) . ')');
		}

		return $db->setQuery($query)->loadObjectList('remote_parent_key');
	}

	/**
	 * Get type name
	 *
	 * @param   array  $remoteKeys  Remote keys
	 *
	 * @return  string
	 */
	public function getTypeName(&$remoteKeys)
	{
		// Empty type name = own ids getting
		$typeName = 'OWN';

		foreach ($remoteKeys as $name => $value)
		{
			$values = explode('.', $value);

			if (count($values) > 1)
			{
				$typeName          = strtoupper($values[0]);
				$remoteKeys[$name] = (string) $values[1];
			}
		}

		return $typeName;
	}

	/**
	 * Execute the Api Delete operation.
	 *
	 * @return  mixed  RApi object with information on success, boolean false on failure.
	 */
	public function apiDelete()
	{
		// Delete function requires references and not values like we use in call_user_func_array so we use List delete function
		$this->apiDynamicModelClassName = 'RApiHalModelList';
		$model                          = $this->triggerFunction('loadModel', $this->elementName, $this->operationConfiguration);
		$functionName                   = RbfilesyncHelperHal::attributeToString($this->operationConfiguration, 'functionName', 'delete');
		$allData                        = $this->triggerFunction('getData', 'delete');
		$this->sync->counterTotal       = $this->sync->counterTotal + count($allData);

		foreach ($allData as $data)
		{
			if ($this->sync->goToNextPart || $this->sync->isExecutionTimeExceeded() || $this->sync->counter > $this->numberOfFilesToLoad)
			{
				$this->sync->goToNextPart = true;
				break;
			}

			$this->sync->counter++;
			$this->options->set('data', $data);

			$result                = $this->triggerFunction('processPostData', $data, $this->operationConfiguration);
			$data                  = $result->default;
			$primaryKeys           = array();
			$primaryKeysFromFields = RApiHalHelper::getFieldsArray($this->operationConfiguration, true);

			if ($primaryKeysFromFields)
			{
				foreach ($primaryKeysFromFields as $key => $primaryKeysFromField)
				{
					$primaryKeys[$key] = (string) $data[$key];
				}
			}

			// Without primary key we can`t store
			if (!count($primaryKeys))
			{
				continue;
			}

			$typeName  = $this->getTypeName($primaryKeys);
			$remoteKey = serialize($primaryKeys);

			if (isset($this->sync->executed[$remoteKey . '_' . $typeName]))
			{
				continue;
			}

			$data = $this->triggerFunction('validatePostData', $model, $data, $this->operationConfiguration);

			if ($data === false)
			{
				// Not Acceptable
				$this->setStatusCode(406);
				$this->triggerFunction('displayErrors', $model);
				$this->setData('result', $data);

				return false;
			}

			$result = null;
			$args   = $this->buildFunctionArgs($this->operationConfiguration, $data);

			$serializeId        = '';
			$oldSerializeValues = '';
			$syncData           = $this->sync->findSyncedId($this->reference, $remoteKey, $typeName, true);

			if ($syncData)
			{
				$serializeId        = $syncData->local_id;
				$oldSerializeValues = $syncData->serialize;
				$unSerializeId      = RedshopbHelperSync::mbUnserialize($serializeId);

				foreach ($primaryKeys as $key => $primaryKey)
				{
					$args[0][$key] = $unSerializeId[$key];
				}
			}

			// Prepare parameters for the function
			if (strtolower(RbfilesyncHelperHal::attributeToString($this->operationConfiguration, 'dataMode', 'model')) == 'table')
			{
				if (!empty($primaryKeys))
				{
					$result = $model->{$functionName}($primaryKeys);
				}
				else
				{
					$result = $model->{$functionName}($args);
				}
			}
			else
			{
				// Checks if that method exists in model class file and executes it
				if (method_exists($model, $functionName))
				{
					$result = $this->triggerCallFunction($model, $functionName, $args);
				}
				else
				{
					$this->setStatusCode(400);
				}
			}

			$this->setData('result', $result);

			$this->triggerFunction('displayErrors', $model);

			if ($serializeId)
			{
				$oldUnSerializeValues = RedshopbHelperSync::mbUnserialize($oldSerializeValues);
				$this->triggerFunction('deleteFiles', $oldUnSerializeValues);
				$this->sync->recordSyncedId($this->reference, $remoteKey, $serializeId, $typeName, false, 3);

				// Check existing other remote keys for the same item, if have - store the same parameters
				$relationRemoteKeys = $this->findSyncedRemoteId($this->reference, $serializeId, array($typeName));

				if ($relationRemoteKeys)
				{
					foreach ($relationRemoteKeys as $relationRemoteKey)
					{
						$this->sync->recordSyncedId(
							$this->reference, $relationRemoteKey->remote_key, $serializeId, $relationRemoteKey->remote_parent_key, false, 3
						);
					}
				}
			}

			if ($this->statusCode < 400)
			{
				if ($result === false)
				{
					// If delete failed then we set it to Internal Server Error status code
					$this->setStatusCode(500);
				}
			}
		}

		if (!$this->sync->goToNextPart)
		{
			$this->sync->deleteRowsNotPresentInRemote($this->reference, '', array(3));
		}
	}

	/**
	 * Get data
	 *
	 * @param   string  $operation  Task name
	 *
	 * @return array|Registry
	 */
	public function getData($operation)
	{
		$fileName = $this->client . '.' . $this->webserviceName . '.' . $this->webserviceVersion . '.' . $operation . '.' . $this->fileFormat;
		$filePath = JPATH_SITE . '/media/com_redshopb/' . $this->webservicePath . '/files/' . $fileName;
		$data     = array();

		if (JFile::exists($filePath))
		{
			switch ($this->fileFormat)
			{
				case 'json':
					$json = file_get_contents($filePath);
					$data = json_decode($json);
					break;

				case 'csv':
					$handle = fopen($filePath, 'r');

					if ($handle !== false)
					{
						setlocale(LC_ALL, 'en_GB.UTF-8');
						$firstRow = true;
						$header   = null;

						while (($row = fgetcsv($handle, 10000, ',')) !== false) // @codingStandardsIgnoreLine
						{
							if ($firstRow)
							{
								$firstRow = false;

								// Remove BOM from first row
								if (substr($row[0], 0, 3) == pack("CCC", 0xef, 0xbb, 0xbf))
								{
									$row[0] = substr($row[0], 3);
								}
							}

							if (!$header)
							{
								$header = array_map('mb_strtolower', $row);
							}
							else
							{
								if (is_array($row) && (count($row) > 1))
								{
									$data[] = array_combine($header, array_map('trim', $row));
								}
							}
						}

						fclose($handle);
					}
					break;

				case 'xml':
				default:
					$xml = file_get_contents($filePath);
					$xml = new SimpleXMLElement($xml, LIBXML_PARSEHUGE);

					if ($xml)
					{
						foreach ($xml->tag as $oneItem)
						{
							$data[] = (array) $oneItem;
						}
					}
					break;
			}
		}

		return $data;
	}

	/**
	 * Gets instance of helper object class if exists
	 *
	 * @return  mixed It will return Api helper class or false if it does not exists
	 */
	public function getHelperObject()
	{
		if (!empty($this->apiHelperClass))
		{
			return $this->apiHelperClass;
		}

		$version    = $this->options->get('webserviceVersion', '');
		$helperFile = RbfilesyncHelperHal::getWebserviceFile(
			$this->client, strtolower($this->webserviceName), $version, 'php', $this->webservicePath . '/definitions'
		);

		if (file_exists($helperFile))
		{
			require_once $helperFile;
		}

		$webserviceName  = preg_replace('/[^A-Z0-9_\.]/i', '', $this->webserviceName);
		$helperClassName = 'RApiHalHelper' . ucfirst($this->client) . ucfirst(strtolower($webserviceName));

		if (class_exists($helperClassName))
		{
			$this->apiHelperClass = new $helperClassName;
		}

		return $this->apiHelperClass;
	}

	/**
	 * Get Remote Optional Ids
	 *
	 * @param   array             $data           Raw Posted data
	 * @param   SimpleXMLElement  $configuration  Configuration for displaying object
	 *
	 * @return array
	 */
	public function getRemoteOptionalIds($data, $configuration)
	{
		$optionalRemoteKeys = array();

		if (!empty($data) && !empty($configuration->fields))
		{
			foreach ($configuration->fields->field as $field)
			{
				$fieldAttributes = RApiHalHelper::getXMLElementAttributes($field);

				if (isset($fieldAttributes['optionalRemoteKey']) && $fieldAttributes['optionalRemoteKey'] == 'true'
					&& isset($data[$fieldAttributes['name']]) && $data[$fieldAttributes['name']] != '')
				{
					// Remove _id and transform ot upper case the field name
					$values = explode('.', $fieldAttributes['name']);

					if (count($values) == 2)
					{
						$typeName = strtoupper($values[0]);
						$keyName  = $values[1];

						if (!isset($optionalRemoteKeys[$typeName]))
						{
							$optionalRemoteKeys[$typeName] = array();
						}

						$optionalRemoteKeys[$typeName][$keyName] = $data[$fieldAttributes['name']];
					}

					unset($data[$fieldAttributes['name']]);
					continue;
				}
			}
		}

		return array($data, $optionalRemoteKeys);
	}

	/**
	 * Store files
	 *
	 * @param   array             $data           Raw Posted data
	 * @param   SimpleXMLElement  $configuration  Configuration for displaying object
	 * @param   array|string      $oldValues      Old values for check
	 *
	 * @return  array
	 */
	public function storeFiles($data, $configuration, $oldValues = array())
	{
		if (!empty($data) && !empty($configuration->fields))
		{
			foreach ($configuration->fields->field as $field)
			{
				$fieldAttributes = RApiHalHelper::getXMLElementAttributes($field);

				// Transform image
				if ($fieldAttributes['transform'] == 'image')
				{
					if (!isset($data[$fieldAttributes['name']]) || $data[$fieldAttributes['name']] == '')
					{
						$data[$fieldAttributes['name']] = '';
						continue;
					}

					if (!isset($fieldAttributes['imageSection']))
					{
						$fieldAttributes['imageSection'] = 'products';
					}

					if (!isset($fieldAttributes['imageIncrement']))
					{
						$fieldAttributes['imageIncrement'] = 'id';
					}

					if (isset($data[$fieldAttributes['imageIncrement']]))
					{
						$imageIncrement = $data[$fieldAttributes['imageIncrement']];
					}
					else
					{
						$imageIncrement = 0;
					}

					$values = array(
						'fullPath' => $data[$fieldAttributes['name']],
						'section' => $fieldAttributes['imageSection'],
						'id' => $imageIncrement
					);

					$needTransform = true;

					if (isset($oldValues[$fieldAttributes['name']]) && is_array($oldValues[$fieldAttributes['name']]))
					{
						$test = $oldValues[$fieldAttributes['name']];

						if (isset($test['type']) && $test['type'] == 'image'
							&& isset($test['section']) && $test['section'] == $fieldAttributes['imageSection']
							&& isset($test['fullPath']) && $test['fullPath'] == $data[$fieldAttributes['name']]
							&& isset($test['newName']))
						{
							if (JFile::exists(JPATH_ROOT . RedshopbHelperThumbnail::getFullImagePath($test['newName'], $test['section'])))
							{
								$needTransform = false;
							}
						}
					}

					if ($needTransform)
					{
						if (!is_array($oldValues))
						{
							$oldValues = array();
						}

						$newImageName                        = $this->transformField($fieldAttributes['transform'], $values, false);
						$oldValues[$fieldAttributes['name']] = array(
							'section' => $fieldAttributes['imageSection'],
							'fullPath' => $data[$fieldAttributes['name']],
							'newName' => $newImageName,
							'type' => 'image'
						);
						$data[$fieldAttributes['name']]      = $newImageName;
					}
					else
					{
						$data[$fieldAttributes['name']] = $oldValues[$fieldAttributes['name']]['newName'];
					}
				}
			}
		}

		return array($data, $oldValues);
	}

	/**
	 * Delete files
	 *
	 * @param   array|string  $oldValues  Old values for check
	 *
	 * @return  void
	 */
	public function deleteFiles($oldValues = array())
	{
		if (is_array($oldValues))
		{
			foreach ($oldValues as $oldValue)
			{
				// Delete image
				if (isset($oldValue['type']) && $oldValue['type'] == 'image' && isset($oldValue['newName']) && isset($oldValue['section']))
				{
					RedshopbHelperThumbnail::deleteImage(
						$oldValue['newName'], 1, $oldValue['section'], isset($oldValue['remote_path']) ? $oldValue['remote_path'] : ''
					);
				}
			}
		}
	}

	/**
	 * Process posted data from json or object to array
	 *
	 * @param   array             $data           Raw Posted data
	 * @param   SimpleXMLElement  $configuration  Configuration for displaying object
	 *
	 * @return  mixed  Object with posted data.
	 */
	public function processPostData($data, $configuration)
	{
		if (is_object($data))
		{
			$data = ArrayHelper::fromObject($data);
		}

		if (!is_array($data))
		{
			$data = (array) $data;
		}

		$dataFields = array();

		if (!empty($data) && !empty($configuration->fields))
		{
			foreach ($configuration->fields->field as $field)
			{
				$fieldAttributes                   = RApiHalHelper::getXMLElementAttributes($field);
				$fieldAttributes['transform']      = !is_null($fieldAttributes['transform']) ? $fieldAttributes['transform'] : 'string';
				$fieldAttributes['defaultValue']   = !is_null($fieldAttributes['defaultValue']) ? $fieldAttributes['defaultValue'] : '';
				$fieldAttributes['isPrimaryField'] = !is_null($fieldAttributes['isPrimaryField']) ? $fieldAttributes['isPrimaryField'] : 'false';

				if (!isset($data[$fieldAttributes['name']]))
				{
					$data[$fieldAttributes['name']] = null;
				}

				if (isset($fieldAttributes['relationService']) && !is_null($data[$fieldAttributes['name']]))
				{
					$primaryKeys                      = array();
					list($reference, $primaryKeyName) = explode('.', $fieldAttributes['relationService']);
					list($optionName, $viewName)      = explode('-', $reference);
					$primaryKeysName                  = explode(',', $primaryKeyName);
					$params                           = new Registry;
					$params->set('optionName', $optionName);
					$params->set('webserviceClient', $this->client);
					$params->set('viewName', $viewName);
					$params->set('cronFunctionName', $this->cronFunctionName);
					$params->set('webserviceParams', $this->webserviceParams);
					$parent                = self::getInstance($params);
					$primaryKeysFromFields = RApiHalHelper::getFieldsArray($parent->operationConfiguration, true);

					if ($primaryKeysFromFields)
					{
						$idValue  = $data[$fieldAttributes['name']];
						$innerIds = array();

						// Support multiple ids in the one field
						if (strpos(',', $data[$fieldAttributes['name']]) === false)
						{
							$ids = array($idValue);
						}
						else
						{
							$ids = explode(',', $idValue);
						}

						foreach ($ids as $id)
						{
							foreach ($primaryKeysFromFields as $key => $primaryKeysFromField)
							{
								if (in_array($key, $primaryKeysName))
								{
									$primaryKeys[$key] = $id;
								}
							}

							$typeName    = $this->getTypeName($primaryKeys);
							$serializeId = $this->sync->findSyncedId('erp.filesync.' . $reference,  serialize($primaryKeys), $typeName);

							if ($serializeId)
							{
								$unSerializeId = RedshopbHelperSync::mbUnserialize($serializeId);

								if (isset($unSerializeId[$primaryKeyName]))
								{
									$innerIds[] = $unSerializeId[$primaryKeyName];
								}
							}
						}

						if (count($innerIds) > 0)
						{
							$data[$fieldAttributes['name']] = implode(',', $innerIds);
						}
					}
				}
				elseif ($fieldAttributes['transform'] != 'image' && $fieldAttributes['isPrimaryField'] != 'true')
				{
					$data[$fieldAttributes['name']] = $this->transformField($fieldAttributes['transform'], $data[$fieldAttributes['name']], false);
				}

				if (is_null($data[$fieldAttributes['name']]))
				{
					$data[$fieldAttributes['name']] = $fieldAttributes['defaultValue'];
				}

				$dataFields[$fieldAttributes['name']] = $data[$fieldAttributes['name']];
			}

			if (RApiHalHelper::isAttributeTrue($configuration, 'strictFields'))
			{
				$data = $dataFields;
			}
		}

		// Common functions are not checking this field so we will
		$data['params']       = isset($data['params']) ? $data['params'] : null;
		$data['associations'] = isset($data['associations']) ? $data['associations'] : array();

		$result          = new stdClass;
		$result->default = $data;
		$result->strict  = $dataFields;

		return $result;
	}

	/**
	 * Transform a source field data value.
	 *
	 * Calls the static toExternal method of a transform class.
	 *
	 * @param   string   $fieldType          Field type.
	 * @param   string   $definition         Field definition.
	 * @param   boolean  $directionExternal  Transform direction
	 *
	 * @return mixed Transformed data.
	 */
	public function transformField($fieldType, $definition, $directionExternal = true)
	{
		// Get the transform class name.
		$className = $this->getTransformClass($fieldType);

		// Execute the transform.
		if ($className instanceof RApiHalTransformInterface)
		{
			return $directionExternal ? $className::toExternal($definition) : $className::toInternal($definition);
		}
		else
		{
			return $definition;
		}
	}

	/**
	 * Get the name of the transform class for a given field type.
	 *
	 * First looks for the transform class in the /transform directory
	 * in the same directory as the web service file.  Then looks
	 * for it in the /api/transform directory.
	 *
	 * @param   string  $fieldType  Field type.
	 *
	 * @return string  Transform class name.
	 */
	protected function getTransformClass($fieldType)
	{
		$fieldType = !empty($fieldType) ? $fieldType : 'string';

		// Cache for the class names.
		static $classNames = array();

		// If we already know the class name, just return it.
		if (isset($classNames[$fieldType]))
		{
			return $classNames[$fieldType];
		}

		$className = 'RbfilesyncHelperTransform' . ucfirst($fieldType);

		if (class_exists($className))
		{
			$classInstance = new $className;

			// Cache it for later.
			$classNames[$fieldType] = $classInstance;

			return $classNames[$fieldType];
		}

		// Construct the name of the class to do the transform (default is RApiHalTransformString).
		$className = 'RApiHalTransform' . ucfirst($fieldType);

		if (class_exists($className))
		{
			$classInstance = new $className;

			// Cache it for later.
			$classNames[$fieldType] = $classInstance;

			return $classNames[$fieldType];
		}

		return $this->getTransformClass('string');
	}
}
