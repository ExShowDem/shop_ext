<?php
/**
 * @package     Aesir.E-Commerce.Plugin
 * @subpackage  Client
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\Registry\Registry;
use Joomla\CMS\Language\Text;

jimport('joomla.filesystem.folder');

/**
 * Redshopb Pim Client.
 *
 * @package     Aesir.E-Commerce
 * @subpackage  Client
 * @since       1.0
 */
class RedshopbClientPim
{
	/**
	 * The local folder.
	 *
	 * @var  string
	 */
	public $localFolder;

	/**
	 * Number of loaded files
	 *
	 * @var  integer
	 */
	public $loadedFiles = 0;

	/**
	 * Summed Number of files
	 *
	 * @var  integer
	 */
	public $numberOfFiles = 0;

	/**
	 * The client.
	 *
	 * @var  RedshopbClientPim
	 */
	protected $client;

	/**
	 * Combined string of XML files
	 *
	 * @var  string
	 */
	protected $xmlString;

	/**
	 * An array of instances.
	 *
	 * @var  RedshopbClientPim[]
	 */
	protected static $instance = array();

	/**
	 * An array of files and folders.
	 *
	 * @var  array
	 */
	protected static $listedFiles = array();

	/**
	 * Constructor.
	 *
	 * @param   string  $localFolder  Local folder for this client
	 * @param   array   $options      The options for the client
	 */
	private function __construct($localFolder, array $options = array())
	{
		$this->localFolder = $localFolder;
	}

	/**
	 * Get an instance or create it.
	 *
	 * @param   string  $url      The wsdl url
	 * @param   array   $options  The options for the soap client
	 *
	 * @return  RedshopbClientPim
	 */
	public static function getInstance($url, array $options = array())
	{
		$hash = md5($url . serialize($options));

		if (!isset(self::$instance[$hash]))
		{
			self::$instance[$hash] = new static($url, $options);
		}

		return self::$instance[$hash];
	}

	/**
	 * Init the instance variables
	 *
	 * @return  void
	 */
	public function initInstance()
	{
		$this->loadedFiles   = 0;
		$this->numberOfFiles = 0;
	}

	/**
	 * Get the whole source (xml) file list and store them in property and provided temp file.  Non-recursive through subfolders.
	 *
	 * @param   string  $tmpFileSourceList  Temporary file to store the file list
	 * @param   array   $folders            Folders to search through
	 *
	 * @return  array
	 */
	public function listAndStoreSourceFiles($tmpFileSourceList, $folders = array())
	{
		if (!empty(self::$listedFiles))
		{
			return self::$listedFiles;
		}

		foreach ($folders as $folder)
		{
			if (JFolder::exists($folder))
			{
				self::$listedFiles[$folder] = JFolder::files($folder, '.xml');
				$this->numberOfFiles       += count(self::$listedFiles[$folder]);
			}
		}

		$arrListedFiles = array(
			'totalFiles' => $this->numberOfFiles,
			'files' => self::$listedFiles
		);

		JFile::write($tmpFileSourceList, json_encode($arrListedFiles));

		return self::$listedFiles;
	}

	/**
	 * Get the agent list.
	 *
	 * @param   array      $sources  Folders or files to search through
	 * @param   Registry   $config   File modify date to search for
	 *
	 * @return  void
	 */
	public function readSource($sources = array(), $config = null)
	{
		$processItemsCompleted = $config->get('groupLoadedFiles', false) ? $config->get('processItemsCompleted', 0) : 0;
		$processItemsStep      = $config->get('processItemsStep', 0);
		$modifyDate            = $config->get('modifyDate', 0);
		$xmlTest               = false;

		foreach ($sources as $type => $source)
		{
			if ($type == 'file')
			{
				$folderCount          = count($source);
				$this->numberOfFiles += $folderCount;

				for ($i = $processItemsCompleted; $i < $folderCount; $i++)
				{
					$file = $source[$i];

					if (!empty($processItemsStep) && $this->loadedFiles >= $processItemsStep)
					{
						break;
					}

					if (!empty($modifyDate))
					{
						$fileModified = filemtime($file);

						if ($modifyDate >= $fileModified)
						{
							continue;
						}
					}

					if (is_readable($file))
					{
						$fileContent = file_get_contents($file);

						if (!empty($fileContent))
						{
							// Check if loaded file is really XML properly formatted file
							try
							{
								$xmlTest = new SimpleXMLElement($fileContent);
							}
							catch (Exception $e)
							{
								RedshopbHelperSync::addMessage(Text::sprintf('PLG_RB_SYNC_PIM_XML_LOAD_PROBLEM', $file), 'warning');
								$this->numberOfFiles--;

								continue;
							}

							// Remove first xml tag from all loaded files
							if (strpos(trim($fileContent), '<?xml') !== false)
							{
								$fileContent = substr($fileContent, (strpos($fileContent, '>') + 1));
							}

							$this->xmlString .= $fileContent;
							$this->loadedFiles++;
							unset($xmlTest);
							unset($fileContent);
						}
					}
				}
			}
			elseif ($type == 'folder')
			{
				foreach ($source as $folder)
				{
					$files = array();

					if (isset(self::$listedFiles[$folder]))
					{
						$files = self::$listedFiles[$folder];
					}
					else
					{
						if (JFolder::exists($folder))
						{
							$files                      = JFolder::files($folder, '.xml');
							self::$listedFiles[$folder] = $files;
						}
					}

					$folderSources = array();

					foreach ($files as $file)
					{
						$folderSources['file'][] = $folder . '/' . $file;
					}

					if (!empty($folderSources))
					{
						$this->readSource($folderSources, $config);
					}
				}
			}
		}

		unset($xmlTest);
	}

	/**
	 * Get Data from the source.
	 *
	 * @param   array      $sources  Folders or files to search through
	 * @param   Registry   $config   File modify date to search for
	 *
	 * @return boolean|SimpleXMLElement The result or FALSE.
	 *
	 * @throws Exception
	 */
	public function getXmlData($sources, $config = null)
	{
		if (is_null($config))
		{
			$config = new Registry;
		}

		$this->xmlString = '';
		$xml             = false;
		$this->readSource($sources, $config);

		if ($config->get('groupLoadedFiles', false))
		{
			$this->xmlString       = '<?xml version="1.0" encoding="utf-8"?><group>' . $this->xmlString . '</group>';
			$processItemsCompleted = $config->get('processItemsCompleted', 0);

			// This is the case when we were out of time while executing cleanup function and all files are already executed
			if ($processItemsCompleted > 0 && $this->numberOfFiles == $processItemsCompleted)
			{
				$xml = $processItemsCompleted;

				return $xml;
			}
		}

		if (!empty($this->xmlString))
		{
			$xml = new SimpleXMLElement($this->xmlString);

			if (!is_object($xml))
			{
				throw new Exception(Text::sprintf('PLG_RB_SYNC_PIM_FAILED_TO_FETCH_ITEMS', json_encode($sources)));
			}
		}

		unset($this->xmlString);
		self::$listedFiles = array();

		return $xml;
	}
}
