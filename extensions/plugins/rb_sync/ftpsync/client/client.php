<?php
/**
 * @package     Aesir.E-Commerce.Plugin
 * @subpackage  FTPSync
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('JPATH_BASE') or die;

use Joomla\CMS\Factory;
use Joomla\Registry\Registry;
use Joomla\CMS\Language\Text;

jimport('joomla.filesystem.folder');
JLoader::import('redshopb.library');

/**
 * FTP Sync Client class
 *
 * @package     Aesir.E-Commerce.Plugin
 * @subpackage  FTPSync
 * @since       1.0
 */
class RedshopbClientFtpsync extends RedshopbHelperSync
{
	/**
	 * Name in the Sync Table
	 *
	 * @var  string
	 */
	public $syncName = 'erp.ftpsync.file';

	/**
	 * @var string
	 */
	public $syncFolderName = 'erp.ftpsync.folders';

	/**
	 * Names used in the Sync Table. Currently used only for clearing Hashed Keys
	 *
	 * @var  string
	 */
	public $allUsedSyncNames = array('erp.ftpsync.file', 'erp.ftpsync.folders');

	/**
	 * @var string
	 */
	public $pluginName = 'ftpsync';

	/**
	 * @var string
	 */
	public $cronName = 'FTPSync';

	/**
	 * Client
	 *
	 * @var RedshopbClientFtp
	 */
	protected $client;

	/**
	 * Configuration from plugin
	 *
	 * @var  object
	 */
	protected $config;

	/**
	 * Number of loaded files
	 *
	 * @var  integer
	 */
	public $loadedFiles = 0;

	/**
	 * Number of fetched files
	 *
	 * @var  integer
	 */
	public $fetchedFiles = 0;

	/**
	 * Summed Number of files
	 *
	 * @var  integer
	 */
	public $numberOfFiles = 0;

	/**
	 * Output values
	 *
	 * @var string
	 */
	public $output = array();

	/**
	 * Joomla temp folder
	 *
	 * @var  string
	 */
	public $tempFolder = '';

	/**
	 * Get data and update relevant tables
	 *
	 * @param   RTable     $webserviceData   All current webservice data
	 * @param   Registry   $params           Plugin parameters
	 *
	 * @return  boolean  true on success
	 *
	 * @throws  RuntimeException
	 */
	public function onFTPSync(&$webserviceData, $params)
	{
		/**
		 * Raises memory and time limit.
		 * Max. plugin execution time still 20sec., but we raise PHP time execution,
		 * for avoid fatal error for hard operations
		 */
		if (RedshopbHelperSync::returnBytesFromIniValue(ini_get('memory_limit')) < (1024 * 1024 * 1024))
		{
			@ini_set('memory_limit', '1024M');
		}

		$max = ini_get('max_execution_time');

		if ($max != 0 && $max < 60)
		{
			@ini_set('max_execution_time', 60);
		}

		$this->tempFolder = Factory::getConfig()->get('tmp_path', JPATH_ROOT . '/tmp');
		$this->setDefaultCronParams($webserviceData, $params);
		$this->config = $params;

		if (empty($this->config)
			|| !$this->config->get('ftp_host', '')
			|| !$this->config->get('ftp_port', '')
			|| !$this->config->get('ftp_user', '')
			|| !$this->config->get('ftp_pass', ''))
		{
			RedshopbHelperSync::addMessage(Text::_('COM_REDSHOPB_SYNC_FTP_SYNC_MISSING_CONFIGURATION'));

			return false;
		}

		$lang = Factory::getLanguage();
		$lang->load('com_redshopb', JPATH_SITE);

		$this->output = array(
			'createdFolders' => 0,
			'createdFiles'   => 0,
		);

		$db = Factory::getDbo();

		try
		{
			// Disable FTP Native if it causes trouble with MLSD command
			if ($this->config->get('ftp_native', 0) == 0)
			{
				define('FTP_NATIVE', 0);
			}

			$this->setFtpClient($this->config->get('ftp_host', ''), $this->config->get('ftp_port', '21'));

			if (!empty($this->client))
			{
				$remoteExcluded = $this->config->get('remoteExcluded', '');
				$remoteExcluded = explode("\r\n", $remoteExcluded);

				if ($remoteExcluded[0] == '')
				{
					unset($remoteExcluded[0]);
				}

				// Excluded OS files
				$remoteExcluded = array_merge(
					$remoteExcluded, array(
						'.',
						'..',
						'.DS_Store',
						'._*',
						'.Spotlight-V100',
						'.Trashes',
						'Thumbs.db',
						'Desktop.ini',
					)
				);

				// Check to see if we are already run Sync which is not finished (so we can have multiple parts if webservice is too big)
				if (!$this->isCronExecuted($this->cronName))
				{
					// We set them with a flag so we can delete the ones which are not present in the latest Sync xmls
					$this->setSyncRowsAsExecuted($this->syncName, null, true);
					$this->setSyncRowsAsExecuted($this->syncFolderName);

					// We set cron as executed so we can have multipart process if the process takes too long
					$this->setCronAsExecuted($this->cronName);
				}

				if ($this->isExecutionTimeExceeded())
				{
					$this->goToNextPart = true;
				}

				$db->unlockTables();
				$db->transactionStart();

				$result       = array(
					'parts' => 0,
					'total' => 0
				);
				$remoteFolder = '/' . ltrim($this->config->get('remoteFolder', ''), '/');
				$localFolder  = $this->config->get('localFolder', 'ftpsync');

				if (!$this->goToNextPart)
				{
					$this->getPrepareFolderData($localFolder, $remoteFolder, $remoteExcluded);

					// Sets number of executed files in Cron
					$this->setProgressCounters($this->cronTable, null, $this->numberOfFiles);
				}

				if (!$this->goToNextPart)
				{
					$result = $this->syncFolders(
						$localFolder,
						$remoteFolder,
						$remoteExcluded,
						true
					);
				}

				$db->transactionCommit();
				$db->unlockTables();

				$this->counter += $this->processItemsCompleted;

				if (!$this->goToNextPart)
				{
					$this->deleteTempDirectories();

					if ($this->isExecutionTimeExceeded())
					{
						$this->goToNextPart = true;
					}
				}

				if (!$this->goToNextPart)
				{
					$deletedFileRows = $this->deleteRowsNotPresentInRemote($this->syncName, '', array(1, 2));

					if ($deletedFileRows && count($deletedFileRows))
					{
						$this->deleteFilesNotPresentInRemote($localFolder, $deletedFileRows);
					}

					if ($this->isExecutionTimeExceeded())
					{
						$this->goToNextPart = true;
					}
				}

				if (!$this->goToNextPart)
				{
					// Deletes folders that were removed from the parent
					$deletedFolderRows = $this->deleteRowsNotPresentInRemote($this->syncFolderName);

					if ($deletedFolderRows && count($deletedFolderRows))
					{
						$this->deleteFilesNotPresentInRemote($localFolder, $deletedFolderRows);
					}
				}

				if (!$this->goToNextPart)
				{
					// We are setting cron as finished (no more parts)
					$this->setCronAsFinished($this->cronName);
				}

				// Try to close FTP connection
				try
				{
					$this->client->quit();
				}
				catch (Exception $ex)
				{
				}

				return $result;
			}
		}
		catch (Exception $e)
		{
			$db->transactionRollback();
			$db->unlockTables();

			if ($this->client instanceof RedshopbClientFtp)
			{
				// Try to close FTP connection
				try
				{
					$this->client->quit();
				}
				catch (Exception $ex)
				{
				}
			}

			RedshopbHelperSync::addMessage(Text::sprintf('COM_REDSHOPB_SYNC_FTP_SYNC_ERROR_IN_EXECUTING', $e->getMessage()), 'error');

			return false;
		}

		return false;
	}

	/**
	 * Delete Files Not Present In Remote
	 *
	 * @param   string  $localFolder  Local folder
	 * @param   array   $deletedRows  Deleted rows in sync table
	 *
	 * @return  boolean
	 *
	 * @throws  Exception
	 */
	protected function deleteFilesNotPresentInRemote($localFolder, $deletedRows)
	{
		if ($deletedRows && count($deletedRows))
		{
			foreach ($deletedRows as $syncRow)
			{
				if (!$this->goToNextPart)
				{
					if ($this->isExecutionTimeExceeded())
					{
						$this->goToNextPart = true;
						break;
					}
				}

				$filePath = JPATH_ROOT . '/media/com_redshopb/' .
					($syncRow->remote_parent_key != '' ? $syncRow->remote_parent_key . '/' : '') .
					$syncRow->remote_key;

				if (is_dir($filePath))
				{
					RedshopbHelperSync::addMessage('Deleting folder: ' . $filePath);
					JFile::delete($filePath . '/index.html');
					JFolder::delete($filePath);
				}
				else
				{
					JFile::delete($filePath);
				}
			}
		}

		return true;
	}

	/**
	 * Delete Temp Directories
	 *
	 * @return  void
	 */
	protected function deleteTempDirectories()
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->select('remote_key')
			->from($db->qn('#__redshopb_sync'))
			->where('reference = ' . $db->q($this->syncFolderName));

		$paths = $db->setQuery($query)->loadColumn();

		if ($paths)
		{
			$localFolderPath = JPATH_ROOT . '/media/com_redshopb/';

			foreach ($paths as $path)
			{
				$this->counterFromSkippedFolders($path);

				if (JFolder::exists($localFolderPath . $path . '/.tmp'))
				{
					JFolder::delete($localFolderPath . $path . '/.tmp');
				}
			}
		}
	}

	/**
	 * Check path for skipped folders
	 *
	 * @param   string  $path  Path to the local folder
	 *
	 * @return  void
	 */
	protected function counterFromSkippedFolders($path)
	{
		// Check for .skipFolder.tmp file in that folder so we can add file count to the final counter
		$folderPath = JPATH_ROOT . '/media/com_redshopb/' . $path . '/.tmp';
		$skipFile   = $folderPath . '/.skipFolder.tmp';
		$tmpFile    = $folderPath . '/.folderItems.tmp';

		if (JFile::exists($skipFile) && JFile::exists($tmpFile))
		{
			$cwdFolders     = RedshopbHelperSync::mbUnserialize(file_get_contents($tmpFile));
			$this->counter += count($cwdFolders->files);
			unset($cwdFolders);
		}
	}

	/**
	 * Init and return FTP Client object
	 *
	 * @param   string  $host  FTP Host
	 * @param   string  $port  FTP Port
	 *
	 * @return RedshopbClientFtp
	 */
	protected function setFtpClient($host, $port)
	{
		if (!$this->client)
		{
			$max = ini_get('default_socket_timeout');

			if ($max != 0 && $max < 60000)
			{
				@ini_set('default_socket_timeout', 60000);
			}

			/**
			 * Connect and login to the FTP server.
			 * Use binary transfer mode to be able to compare files.
			 * Try to set Timeout to very large number to escape timeouts in requests
			 */
			$ftp = @RedshopbClientFtp::getInstance($host, $port, array('type' => FTP_BINARY, 'timeout' => 9999999));

			// Check to make sure FTP is connected and authenticated.
			if (!$ftp->isConnected())
			{
				Factory::getApplication()->enqueueMessage($host . ':' . $port . ' ' . Text::_('COM_REDSHOPB_SYNC_FTP_SYNC_NOCONNECT'), 'error');

				return null;
			}

			// Can we login with this account?
			if (!$ftp->login($this->config->get('ftp_user', ''), $this->config->get('ftp_pass', '')))
			{
				RedshopbHelperSync::addMessage(Text::_('COM_REDSHOPB_SYNC_FTP_SYNC_NOLOGIN'), 'error');

				return null;
			}

			$this->client = $ftp;
		}

		return $this->client;
	}

	/**
	 * Return logged in FTP Client object
	 *
	 * @return RedshopbClientFtp
	 */
	protected function openClientConnection()
	{
		// Set instance if it is not set before
		if (!$this->setFtpClient($this->config->get('ftp_host', ''), $this->config->get('ftp_port', '21')))
		{
			return null;
		}

		// Check to make sure FTP is connected and authenticated.
		if (!$this->client->isConnected())
		{
			$this->reconnectClientConnection();
		}

		// Check if it is still not connected then we have a problem
		if (!$this->client->isConnected())
		{
			RedshopbHelperSync::addMessage(
				$this->config->get('ftp_host', '') . ':' . $this->config->get('ftp_port', '21')
				. ' ' . Text::_('COM_REDSHOPB_SYNC_FTP_SYNC_NOCONNECT'), 'error'
			);

			return null;
		}

		return $this->client;
	}

	/**
	 * Return logged in FTP Client object
	 *
	 * @return RedshopbClientFtp
	 */
	protected function reconnectClientConnection()
	{
		$this->client->quit();
		$this->client->connect($this->config->get('ftp_host', ''), $this->config->get('ftp_port', '21'));

		// Can we login with this account?
		if (!$this->client->login($this->config->get('ftp_user', ''), $this->config->get('ftp_pass', '')))
		{
			RedshopbHelperSync::addMessage(Text::_('COM_REDSHOPB_SYNC_FTP_SYNC_NOLOGIN'), 'error');

			return null;
		}
	}

	/**
	 * Get Prepare Folder Data
	 *
	 * @param   string  $localFolder     Local folder location
	 * @param   string  $remoteFolder    Remote folder location
	 * @param   array   $remoteExcluded  Folders we do not want to sync
	 *
	 * @return boolean
	 *
	 * @throws Exception
	 */
	private function getPrepareFolderData($localFolder, $remoteFolder, $remoteExcluded)
	{
		if ($this->goToNextPart || $this->isExecutionTimeExceeded())
		{
			$this->goToNextPart = true;

			return false;
		}

		$localFolderPath = JPATH_ROOT . '/media/com_redshopb/' . $localFolder;
		$localTmpFolder  = $localFolderPath . '/.tmp';

		// Check if local folder exists
		$this->createFolderIfNotExists($localFolderPath);
		$skipHashCheck = false;
		$hashedKey     = null;

		if (!JFile::exists($localTmpFolder . '/.folderItems.tmp'))
		{
			if (!empty($remoteFolder))
			{
				if (!$this->openClientConnection())
				{
					$this->goToNextPart = true;

					return false;
				}

				if (!$this->client->chdir($remoteFolder))
				{
					// Reconnect and try again
					if (!$this->reconnectClientConnection() || !$this->client->chdir($remoteFolder))
					{
						RedshopbHelperSync::addMessage(Text::_('COM_REDSHOPB_SYNC_FTP_SYNC_REMOTE_FOLDER_DOES_NOT_EXIST'), 'error');

						return false;
					}
				}

				RedshopbHelperSync::addMessage(Text::sprintf('PLG_RB_SYNC_FTPSYNC_GETTING_INFO_FROM_FOLDER', $remoteFolder));
			}

			if (!$this->openClientConnection())
			{
				$this->goToNextPart = true;

				return false;
			}

			// Get a list of folders in the current working directory.
			$cwdFolders = $this->client->listDetailsMlsd();

			// If we got false then we might be in a timeout
			if ($cwdFolders === false)
			{
				// Reconnect and try again
				$this->reconnectClientConnection();
				$cwdFolders = $this->client->listDetailsMlsd();
			}

			if ($cwdFolders === false || count($cwdFolders) == 0)
			{
				RedshopbHelperSync::addMessage(Text::_('COM_REDSHOPB_SYNC_FTP_SYNC_NODIRECTORYLISTING'));

				return false;
			}

			// If tmp folder not exist, create it.
			$this->createFolderIfNotExists($localTmpFolder);
			$serializedFolder = serialize($cwdFolders);

			file_put_contents($localTmpFolder . '/.folderItems.tmp', $serializedFolder);
			$hashedKey = RedshopbHelperSync::generateHashKey($serializedFolder, 'string');
			unset($serializedFolder);
		}
		else
		{
			// If we are reading this from the folder then this is the next part of the sync so new hash key should not be checked
			$skipHashCheck = true;
			$cwdFolders    = RedshopbHelperSync::mbUnserialize(file_get_contents($localTmpFolder . '/.folderItems.tmp'));
		}

		$this->numberOfFiles += count($cwdFolders->files);

		// Unset file list before inner deeper folder, for exclude overflow memory
		unset($cwdFolders->files);

		$itemData  = $this->findSyncedId($this->syncFolderName, $localFolder, '', true);
		$hashedKey = $itemData && $skipHashCheck ? $itemData->hash_key : $hashedKey;

		if (!$skipHashCheck && !$this->isHashChanged($itemData, $hashedKey))
		{
			// Hash key is the same so we will continue on the next item
			$this->skipItemUpdateFolder($itemData);
		}

		if (!$itemData)
		{
			$this->recordSyncedId(
				$this->syncFolderName, $localFolder, '', '', true, 1, '', false, '', null, null
			);
		}

		if ($this->goToNextPart || $this->isExecutionTimeExceeded())
		{
			$this->goToNextPart = true;

			return false;
		}

		// Process folders
		foreach ($cwdFolders->folders as $folder)
		{
			$folderName = ($remoteFolder == '/' ? '' : $remoteFolder) . '/' . ltrim($folder['name'], '/');

			// If it is in excluded list then we skip it
			if (in_array($folderName, $remoteExcluded))
			{
				continue;
			}

			if ($this->goToNextPart || $this->isExecutionTimeExceeded())
			{
				$this->goToNextPart = true;

				break;
			}

			if (!$this->getPrepareFolderData(
				$localFolder . '/' . $folder['name'],
				$remoteFolder . '/' . $folder['name'],
				$remoteExcluded
			))
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * Check if the item needs to be updated or not
	 *
	 * @param   object  $sync  Sync object
	 *
	 * @return  boolean
	 */
	public function skipItemUpdateFolder($sync)
	{
		// Skip files in folders
		$db         = Factory::getDbo();
		$countQuery = $db->getQuery(true)
			->select('COUNT(*)')
			->from($db->qn('#__redshopb_sync'))
			->where($db->qn('reference') . ' = ' . $db->q($this->syncName))
			->where($db->qn('remote_parent_key') . ' = ' . $db->q($sync->remote_key))
			->where($db->qn('execute_sync') . ' != 0');

		while ($counter = $db->setQuery($countQuery, 0, 1)->loadResult())
		{
			if ($this->isExecutionTimeExceeded())
			{
				RedshopbHelperSync::addMessage(Text::sprintf('PLG_RB_SYNC_FTPSYNC_RESET_SYNC_COUNTERS', $counter));
				$this->goToNextPart = true;
				break;
			}

			$query = $db->getQuery(true)
				->update($db->qn('#__redshopb_sync'))
				->set($db->qn('execute_sync') . ' = 0')
				->where($db->qn('reference') . ' = ' . $db->q($this->syncName))
				->where($db->qn('remote_parent_key') . ' = ' . $db->q($sync->remote_key))
				->where($db->qn('execute_sync') . ' != 0');

			$this->executeQueryWithRetries($query . ' LIMIT 2000');
		}

		if (!$this->goToNextPart)
		{
			// Skip folder with parent method
			parent::skipItemUpdate($sync);

			// Create empty .skipFolder file in that folder so we can skip it in next iterations
			$folderPath = JPATH_ROOT . '/media/com_redshopb/' . $sync->remote_key . '/.tmp';
			$skipFile   = $folderPath . '/.skipFolder.tmp';

			if (JFolder::exists($folderPath))
			{
				file_put_contents($skipFile, '<!DOCTYPE html><title></title>');
			}
		}

		return true;
	}

	/**
	 * Sync remote folder with the local folder
	 *
	 * @param   string  $localFolder     Local folder location
	 * @param   string  $remoteFolder    Remote folder location
	 * @param   array   $remoteExcluded  Folders we do not want to sync
	 * @param   bool    $isRootFolder    Is this the root folder
	 *
	 * @return  mixed                    Boolean if executed successfully or array if it is in multiple parts
	 *
	 * @throws  Exception
	 */
	protected function syncFolders($localFolder, $remoteFolder, $remoteExcluded, $isRootFolder = false)
	{
		// Get configuration for optimize image feature
		$localFolderPath     = JPATH_ROOT . '/media/com_redshopb/' . $localFolder;
		$tempFolderPath      = $this->tempFolder . '/media/com_redshopb/' . $localFolder;
		$optimizeImageMethod = $this->config->get('big_image_method', 'resize');
		$optimizeImageWidth  = (int) $this->config->get('max_width', 0);
		$optimizeImageHeight = (int) $this->config->get('max_height', 0);
		$localTmpFolder      = $localFolderPath . '/.tmp';
		$data                = $this->findSyncedId($this->syncFolderName, $localFolder, '', true);
		$currentFolderItems  = 0;
		$serializedFolder    = file_get_contents($localTmpFolder . '/.folderItems.tmp');
		$cwdFolders          = RedshopbHelperSync::mbUnserialize($serializedFolder);
		$hashedKey           = RedshopbHelperSync::generateHashKey($serializedFolder, 'string');
		$folderSkipped       = false;

		if (!$isRootFolder && $data && $data->execute_sync == 0)
		{
			if (!JFile::exists($localTmpFolder . '/.skipFolder.tmp'))
			{
				$this->fetchedFiles += count($cwdFolders->files);
				unset($cwdFolders);

				return true;
			}

			$folderSkipped = true;
		}

		if (!$this->goToNextPart)
		{
			$currentFolderItems = count($cwdFolders->files);
			unset($cwdFolders->files);

			// Process folders
			foreach ($cwdFolders->folders as $folder)
			{
				$folderName = ($remoteFolder == '/' ? '' : $remoteFolder) . '/' . ltrim($folder['name'], '/');

				// If it is in excluded list then we skip it
				if (in_array($folderName, $remoteExcluded))
				{
					continue;
				}

				if ($this->goToNextPart || $this->isExecutionTimeExceeded())
				{
					$this->goToNextPart = true;

					break;
				}

				$folderSync = $this->syncFolders(
					$localFolder . '/' . $folder['name'],
					$remoteFolder . '/' . $folder['name'],
					$remoteExcluded
				);

				if ($folderSync === false)
				{
					return false;
				}
				elseif (is_array($folderSync))
				{
					return $folderSync;
				}
			}

			if ($folderSkipped)
			{
				return true;
			}

			// Process Files
			$cwdFolders = RedshopbHelperSync::mbUnserialize($serializedFolder);
			unset($serializedFolder);
			unset($cwdFolders->folders);
			$imageFileTypes = $this->getSupportImageFileTypes();
			$imageMimeTypes = $this->getSupportImageFileMimes();

			// Get list executed in previous sync items because this is multipart process
			$this->executed    = $this->getPreviousSyncExecutedList($this->syncName, $localFolder);
			$loadedFiles       = ($this->processItemsCompleted + $this->counter) - $this->fetchedFiles;
			$this->loadedFiles = $loadedFiles;

			for ($i = $loadedFiles; $i < $currentFolderItems; $i++)
			{
				if ($this->goToNextPart || $this->isExecutionTimeExceeded())
				{
					$this->goToNextPart = true;

					break;
				}

				if (!empty($this->processItemsStep) && $this->counter >= $this->processItemsStep)
				{
					$this->goToNextPart = true;
					break;
				}

				$file = $cwdFolders->files[$i];
				$this->counter++;
				$this->loadedFiles++;
				$originalFileName = $file['name'];
				$fileName         = JFile::makeSafe($file['name']);

				// If it is in excluded list then we skip it
				if (in_array($originalFileName, $remoteExcluded))
				{
					continue;
				}

				if ($this->executed && isset($this->executed[$fileName . '_' . $localFolder]))
				{
					continue;
				}

				$isNew                   = true;
				$reUpload                = false;
				$serialize               = array(
					'isModified' => false,
					'modify'     => '',
					'size'       => ''
				);
				$downloadPath            = $tempFolderPath . '/' . $originalFileName;
				$downloadPathDestination = $localFolderPath . '/' . $originalFileName;

				$status        = 0;
				$hashedKeyFile = RedshopbHelperSync::generateHashKey($file, 'array');
				$itemData      = $this->findSyncedId($this->syncName, $fileName, $localFolder, true);

				if ($itemData)
				{
					$isNew = false;

					if (!$this->isHashChanged($itemData, $hashedKeyFile))
					{
						// Hash key is the same so we will continue on the next item
						$this->skipItemUpdate($itemData);

						continue;
					}

					$serialize = RedshopbHelperSync::mbUnserialize($itemData->serialize);

					// If file not from media/category and not exist, then re-upload
					if (!JFile::exists($downloadPath))
					{
						$reUpload = true;
					}
				}

				// Check modified date of the file
				if ($isNew || $reUpload || $serialize['modify'] != $file['modify'] || $serialize['size'] != $file['size'])
				{
					// Check file type
					if (is_array($imageFileTypes) && in_array(strtolower(JFile::getExt($fileName)), $imageFileTypes))
					{
						$isImage = true;
					}
					else
					{
						$isImage = false;
					}

					if (!$this->openClientConnection())
					{
						// If there is a connection problem we need to restart this process
						throw new Exception(Text::_('COM_REDSHOPB_SYNC_FTP_SYNC_NOCONNECT'));
					}

					// Create tmp folder
					JFolder::create($tempFolderPath);

					// Download file
					if (!$this->client->get($downloadPath, $remoteFolder . '/' . $originalFileName))
					{
						// Reconnect and try again
						if (!$this->reconnectClientConnection() || !$this->client->get($downloadPath, $remoteFolder . '/' . $originalFileName))
						{
							if (JFile::exists($downloadPath))
							{
								RedshopbHelperSync::addMessage(
									Text::sprintf(
										'PLG_RB_SYNC_FTPSYNC_SYNC_UNABLE_TO_FETCH_ITEM', $downloadPath, $remoteFolder . '/' . $originalFileName
									)
								);
								JFile::delete($downloadPath);
								$hashedKey = null;

								// Error in downloading file we will not delete this file because of that
								$this->skipItemUpdate($itemData);

								continue;
							}
						}
					}

					$downloadedFileSize = filesize($downloadPath);

					if ($isImage)
					{
						// Checks if the filesize is big enough, otherwise it's not even an image
						if (filesize($downloadPath) > 11)
						{
							// Check the mime types of downloaded image.
							$imageType = exif_imagetype($downloadPath);
						}
						else
						{
							$imageType = '';
						}

						// File mime is correct.
						if (in_array($imageType, $imageMimeTypes))
						{
							$imageInfo = getimagesize($downloadPath);

							if (!filesize($downloadPath) || !$imageInfo || !$imageInfo[0] || !$imageInfo[1])
							{
								// This image was broken, remember path for delete at the end
								$status = 2;
							}
							else
							{
								// Call the optimize image process if this is image.
								if (!$this->optimizeImageProcess(
									$file, $downloadPath, $optimizeImageMethod, $optimizeImageWidth, $optimizeImageHeight
								))
								{
									// Something went wrong in the optimization, maybe the downloaded image was incomplete.
									$hashedKey     = null;
									$hashedKeyFile = null;
								}
							}
						}
						else
						{
							$status = 2;
						}
					}

					// We will set modification time of the file to the one as is on the server
					if (!empty($file['modify']))
					{
						$fileTime = substr($file['modify'], 0, 4) . '-' . substr($file['modify'], 4, 2) . '-' . substr($file['modify'], 6, 2)
							. ' ' . substr($file['modify'], 8, 2) . ':' . substr($file['modify'], 10, 2) . ':' . substr($file['modify'], 12, 2);
						touch($downloadPath, strtotime($fileTime));
					}

					// If file saved with zero size, then re-upload it in the next sync part
					if (JFile::exists($downloadPath)
						&& $file['size'] > 0
						&& $file['size'] != $downloadedFileSize)
					{
						RedshopbHelperSync::addMessage(
							Text::sprintf(
								'PLG_RB_SYNC_FTPSYNC_SYNC_FILE_DOWNLOAD_SIZE_PROBLEM',
								$downloadPath,
								$remoteFolder . '/' . $originalFileName,
								$downloadedFileSize,
								$file['size']
							)
						);
						JFile::delete($downloadPath);

						// Error in downloading file we will not delete this file because of that
						$this->skipItemUpdate($itemData);

						continue;
					}
				}
				else
				{
					$downloadPath = $downloadPathDestination;
				}

				/**
				 * If the file is already marked as modified and not processed,
				 * we will not update this field because we will set it to false on the second sync
				 */
				if (!$serialize['isModified'] || ((int) $serialize['isModified']) == 2)
				{
					$serialize['isModified'] = (!empty($serialize['modify']) && $serialize['modify'] != $file['modify'])
						|| (!empty($serialize['size']) && $serialize['size'] != $file['size']);
				}

				if ($downloadPath != $downloadPathDestination && !JFile::move($downloadPath, $downloadPathDestination))
				{
					RedshopbHelperSync::addMessage(
						Text::sprintf(
							'PLG_RB_SYNC_FTPSYNC_SYNC_UNABLE_TO_MOVE_ITEM',
							$downloadPath,
							$remoteFolder . '/' . $originalFileName,
							$downloadPathDestination
						)
					);
					JFile::delete($downloadPath);
					$hashedKey = null;

					// Error in moving file we will not delete this file because of that
					$this->skipItemUpdate($itemData);

					continue;
				}

				$serialize['modify'] = $file['modify'];
				$serialize['size']   = $file['size'];
				$serialize           = serialize($serialize);

				// Save this item ID to synced table - it ignores the local id when updating because it may vary because of other sync's dependencies
				$this->recordSyncedId(
					$this->syncName, $fileName,	'',	$localFolder, $isNew, $status, $serialize, true, 'none', null, null, $hashedKeyFile
				);
			}

			$this->fetchedFiles += $currentFolderItems;
		}

		if ($this->goToNextPart)
		{
			RedshopbHelperSync::addMessage(
				Text::sprintf(
					'COM_REDSHOPB_SYNC_FTP_GOTO_NEXT_PART',
					$this->counter + $this->processItemsCompleted, $this->numberOfFiles, $localFolder, $this->loadedFiles, $currentFolderItems
				),
				'info'
			);

			return array(
				'parts' => $this->numberOfFiles - ($this->counter + $this->processItemsCompleted),
				'total' => $this->numberOfFiles
			);
		}
		else
		{
			$this->recordSyncedId(
				$this->syncFolderName, $localFolder, '', '', false, 0, '', false, '', null, null, $hashedKey
			);

			RedshopbHelperSync::addMessage(
				Text::sprintf('COM_REDSHOPB_SYNC_FTP_SYNC_OUTPUT_RESULTS', $this->loadedFiles, $localFolder), 'success'
			);

			return true;
		}
	}

	/**
	 * create Folder If Not Exists
	 *
	 * @param   string  $folderPath  Folder Path
	 *
	 * @return  void
	 */
	protected function createFolderIfNotExists($folderPath)
	{
		if (!JFolder::exists($folderPath))
		{
			JFolder::create($folderPath, 0750);

			// Create empty index.html file in that folder as security measure
			$indexFile = $folderPath . '/index.html';
			file_put_contents($indexFile, '<!DOCTYPE html><title></title>');
		}
	}

	/**
	 * Method for optimize image.
	 *
	 * @param   array   $file       Array of file detail
	 * @param   string  $imagePath  Local path of downloaded image.
	 * @param   string  $method     Method for process on bigger image.
	 * @param   int     $width      Max width of image.
	 * @param   int     $height     Max height of image.
	 *
	 * @return  boolean
	 */
	protected function optimizeImageProcess($file, $imagePath, $method = 'resize', $width = 0, $height = 0)
	{
		$followWidth  = false;
		$followHeight = false;
		$followRatio  = false;
		$resizedRatio = 0.0;

		$imageData = array(
			'name' => $file['name'],
			'size' => $file['size'],
			'date' => $file['modify']
		);

		// If width & height not set. Do not optimize image.
		if (!$width && !$height)
		{
			return true;
		}
		elseif (!$width)
		{
			// Max width not available. Resize follow max height.
			$followHeight = true;
		}
		elseif (!$height)
		{
			// Max height not available. Resize follow max width.
			$followWidth = true;
		}
		else
		{
			// Both max width & height available. Resize base on ratio.
			$followRatio  = true;
			$resizedRatio = (float) $width / (float) $height;
		}

		// Get detail information of downloaded image.
		list($imageWidth, $imageHeight) = @getimagesize($imagePath);

		// If method is skip bigger image
		if ($method != 'resize')
		{
			// If method is not resize. Skip this (delete downloaded file.)
			if (JFile::exists($imagePath))
			{
				JFile::delete($imagePath);
			}

			return true;
		}

		// Get ratio of image
		$imageRatio = (float) $imageWidth / (float) $imageHeight;

		/*
		 * If resize by follow max width and current image has width larger than max width.
		 * Or current image ratio is larger than resized image ratio.
		 * Resize image follow max width.
		*/
		if (($followWidth && ($imageWidth > $width)) || ($followRatio && ($imageRatio >= $resizedRatio)))
		{
			$imageWidth  = $width;
			$imageHeight = (int) ($imageWidth / $imageRatio);
		}

		/*
		 * If resize by follow max height and current image has height larger than max height.
		 * Or current image ratio is smaller than resized image ratio.
		 * Resize image follow max height.
		*/
		elseif (($followHeight && ($imageHeight > $height)) || ($followRatio && ($imageRatio < $resizedRatio)))
		{
			$imageHeight = $height;
			$imageWidth  = (int) ($imageHeight * $imageRatio);
		}

		// Resize image.
		if (!$this->makeImage($imagePath, $imageWidth, $imageHeight))
		{
			return false;
		}

		// Clear stat cache of this file for get new file size
		clearstatcache();

		// Modify new size information for image data
		$imageData['size'] = filesize($imagePath);

		return true;
	}

	/**
	 * Generating thumb file
	 *
	 * @param   string  $sourceFile  Path original image
	 * @param   int     $width       With thumb
	 * @param   int     $height      Height thumb
	 * @param   int     $crop        Method cropping thumb (0 - not crop, 1 - use swap, 2 - crop)
	 * @param   int     $quality     Image quality
	 *
	 * @return  boolean  True if file generated or false
	 */
	protected function makeImage($sourceFile, $width, $height, $crop = 0, $quality = 100)
	{
		if (!JFile::exists($sourceFile))
		{
			return false;
		}

		// File optimization
		$data     = file_get_contents($sourceFile);
		$resource = null;

		if (!is_string($data))
		{
			RedshopbHelperSync::addMessage(
				Text::sprintf(
					'PLG_RB_SYNC_FTPSYNC_MAKE_IMAGE_PROBLEM',
					Text::_('PLG_RB_SYNC_FTPSYNC_MAKE_IMAGE_PROBLEM_IMAGE_EMPTY'),
					$sourceFile
				),
				'error'
			);

			return false;
		}

		try
		{
			$resource = imagecreatefromstring($data);
		}
		catch (Exception $e)
		{
			RedshopbHelperSync::addMessage(Text::sprintf('PLG_RB_SYNC_FTPSYNC_MAKE_IMAGE_PROBLEM', $e->getMessage(), $sourceFile), 'error');

			return false;
		}

		if (!$resource)
		{
			RedshopbHelperSync::addMessage(
				Text::sprintf(
					'PLG_RB_SYNC_FTPSYNC_MAKE_IMAGE_PROBLEM',
					Text::_('PLG_RB_SYNC_FTPSYNC_MAKE_IMAGE_PROBLEM_IMAGE_NOT_CREATED'),
					$sourceFile
				),
				'error'
			);

			return false;
		}

		$storedMaxWidth  = RedshopbApp::getConfig()->get('stored_max_width', null);
		$storedMaxHeight = RedshopbApp::getConfig()->get('stored_max_height', null);

		if ($storedMaxWidth || $storedMaxHeight)
		{
			$widthR  = $storedMaxWidth / $width;
			$heightR = $storedMaxHeight / $height;
			$ratio   = min($widthR, $heightR);
			$width   = ceil($width * $ratio);
			$height  = ceil($height * $ratio);
		}

		$imagine = new Imagine\Gd\Imagine;
		$imagine = new Imagine\Gd\Image($resource, new Imagine\Image\Palette\RGB, $imagine->getMetadataReader()->readFile($sourceFile));
		$box     = new Imagine\Image\Box($width, $height);
		$size    = $imagine->getSize();

		if ($size->getWidth() > $box->getWidth() || $size->getHeight() > $box->getHeight())
		{
			$options = array(
				'quality' => $quality
			);

			if ($crop)
			{
				$mode = Imagine\Image\ImageInterface::THUMBNAIL_OUTBOUND;
			}
			else
			{
				$mode = Imagine\Image\ImageInterface::THUMBNAIL_INSET;
			}

			$imagine->thumbnail($box, $mode)
				->save($sourceFile, $options);
		}

		unset($imagine);

		// Extra file optimization - depends on binary files to work properly

		if (RedshopbApp::getConfig()->get('auto_optimize_stored', '1') == '1'
			|| ($this->config->get('image_optimization', 'no') != 'no'))
		{
			$factory   = new \ImageOptimizer\OptimizerFactory;
			$optimizer = $factory->get();

			$optimizer->optimize($sourceFile);
		}

		return true;
	}

	/**
	 * Method for get supported image file types base on plugin configuration
	 *
	 * @return  array/boolean      Array of supported image file types if success. False otherwise.
	 */
	protected function getSupportImageFileTypes()
	{
		$fileTypes = (array) $this->config->get('image_types', array());

		if (empty($fileTypes))
		{
			return false;
		}

		if (in_array('jpg', $fileTypes))
		{
			$fileTypes[] = 'jpeg';
		}

		return $fileTypes;
	}

	/**
	 * Method for get supported image file mimes base on plugin configuration
	 *
	 * @return  array/boolean      Array of supported image file mimes if success. False otherwise.
	 */
	protected function getSupportImageFileMimes()
	{
		static $fileMimes = null;

		if (is_null($fileMimes))
		{
			$supportTypes = (array) $this->config->get('image_types', array());

			if (empty($supportTypes))
			{
				return false;
			}

			$mimes = array(
				'bmp' => IMAGETYPE_BMP,
				'gif' => IMAGETYPE_GIF,
				'jpg' => IMAGETYPE_JPEG,
				'png' => IMAGETYPE_PNG
			);

			foreach ($supportTypes as $supportType)
			{
				if (isset($mimes[$supportType]))
				{
					$fileMimes[] = $mimes[$supportType];
				}
			}
		}

		return $fileMimes;
	}
}
