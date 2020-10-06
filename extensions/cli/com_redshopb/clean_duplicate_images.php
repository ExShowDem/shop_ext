<?php
/**
 * @package     Aesir.E-Commerce.Cli
 * @subpackage  Sync
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */
error_reporting(0);
ini_set('display_errors', 0);

use Joomla\CMS\Factory;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Application\CliApplication;

// Initialize Joomla framework
require_once dirname(__DIR__) . '/com_redshopb/joomla_framework.php';

// Load Library language
$lang = Factory::getLanguage();

// Try the com_redshopb file in the current language (without allowing the loading of the file in the default language)
$lang->load('com_redshopb', JPATH_SITE, null, false, false)
// Fallback to the com_redshopb file in the default language
|| $lang->load('com_redshopb', JPATH_SITE, null, true);

/**
 * Clean products and category duplicate images cli application.
 * It accepts 2 arguments:
 * 1. Folder start -> number of folders to skip before is starts processing
 * 2. Folder limit -> number of folders to process at one script execution
 *
 * @package     Aesir.E-Commerce.Cli
 * @subpackage  Cleaner
 * @since       1.0
 */
class Clean_Duplicate_ImagesApplicationCli extends CliApplication
{
	/**
	 * @var array
	 */
	public $categories = array();

	/**
	 * @var array
	 */
	public $products = array();

	/**
	 * @var integer
	 */
	public $counter = 0;

	/**
	 * @var integer
	 */
	public $folderStart = 0;

	/**
	 * @var integer
	 */
	public $folderLimit = 0;

	/**
	 * Entry point for CLI script
	 *
	 * @return  void
	 */
	public function doExecute()
	{
		try
		{
			// Delete old languages files if necessary
			JLoader::import('joomla.filesystem.file');
			JLoader::import('joomla.filesystem.folder');
			$app = Factory::getApplication('site');
			$app->input->set('option', 'com_redshopb');
			define('JPATH_COMPONENT_ADMINISTRATOR', JPATH_ADMINISTRATOR . '/components/com_redshopb');
			JLoader::import('redshopb.library');

			// If folder limit is 0 we set it to very high number
			if ($this->folderLimit == 0)
			{
				$this->folderLimit = 9999999999;
			}

			$folderStart = $this->folderStart;
			$folderLimit = $this->folderLimit;

			// Load common and local language files
			$lang = Factory::getLanguage();

			// Load language file
			$lang->load('com_redshopb', JPATH_ADMINISTRATOR, null, true, false)
			|| $lang->load('com_redshopb', JPATH_ADMINISTRATOR . "/components/com_redshopb", null, true, false)
			|| $lang->load('com_redshopb', JPATH_ADMINISTRATOR, RTranslationHelper::getSiteLanguage(), true, true)
			|| $lang->load('com_redshopb', JPATH_ADMINISTRATOR . "/components/com_redshopb", RTranslationHelper::getSiteLanguage(), true, true);

			// Print a blank line.
			$this->out();
			$this->out('Clean product and category unrelated images');
			$this->out('============================');
			$this->out();

			// We will delete unused images for Categories
			$folder = array('folder' => array(JPATH_ROOT . '/media/com_redshopb/images/originals/categories'));
			$db     = Factory::getDbo();

			$query = $db->getQuery(true);
			$query->select($db->qn('image') . ',' . $db->qn('name'))
				->from($db->qn('#__redshopb_category'));
			$this->categories = $db->setQuery($query)->loadObjectList();
			$this->deleteImages($folder, 'categories');

			$this->out('Deleted: ' . $this->counter . ' category images');
			$this->out();
			$categoryImages    = $this->counter;
			$this->counter     = 0;
			$this->folderStart = $folderStart;
			$this->folderLimit = $folderLimit;

			// We will delete unused images for Products
			$folder = array('folder' => array(JPATH_ROOT . '/media/com_redshopb/images/originals/products'));

			$query = $db->getQuery(true);
			$query->select($db->qn('name', 'image') . ',' . $db->qn('product_id', 'name'))
				->from($db->qn('#__redshopb_media'));
			$this->products = $db->setQuery($query)->loadObjectList();
			$this->deleteImages($folder, 'products');

			$this->out('Deleted: ' . $categoryImages . ' category images');
			$this->out('Deleted: ' . $this->counter . ' product images');

			$messages           = $app->getMessageQueue();
			$return['messages'] = array();

			if (is_array($messages))
			{
				foreach ($messages as $msg)
				{
					switch ($msg['type'])
					{
						case 'message':
							$typeMessage = 'success';
							Log::add($msg['message'], Log::INFO, 'webservice');
							break;
						case 'notice':
							$typeMessage = 'info';
							Log::add($msg['message'], Log::NOTICE, 'webservice');
							break;
						case 'error':
							$typeMessage = 'important';
							Log::add($msg['message'], Log::ERROR, 'webservice');
							break;
						case 'warning':
							$typeMessage = 'warning';
							Log::add($msg['message'], Log::WARNING, 'webservice');
							break;
						default:
							$typeMessage = $msg['type'];
							Log::add($msg['message'], Log::DEBUG, 'webservice');
					}

					$return['messages'][] = array('message' => $msg['message'], 'type_message' => $typeMessage);
				}
			}

			if (count($return['messages']))
			{
				foreach ($return['messages'] as $message)
				{
					$this->out($message['type_message'] . ': ' . $message['message']);
				}
			}
		}
		catch (Exception $e)
		{
			// Display the error
			$this->out($e->getMessage());

			Log::add($e->getMessage(), Log::ERROR, 'webservice');
		}

		$this->out('Finished');

		// Print a blank line at the end.
		$this->out();
	}

	/**
	 * Delete images from the folder that are not found in Category table
	 *
	 * @param   array   $sources     Folders or files to search through
	 * @param   string  $folderType  Type of folder
	 *
	 * @return  boolean
	 */
	public function deleteImages($sources, $folderType)
	{
		foreach ($sources as $type => $source)
		{
			if ($type == 'file')
			{
				$folderCount = count($source);

				for ($i = 0; $i < $folderCount; $i++)
				{
					$file     = $source[$i];
					$fileName = basename($file);
					$delete   = true;

					if ($this->{$folderType})
					{
						foreach ($this->{$folderType} as $i2 => $category)
						{
							if ($category->image == $fileName)
							{
								$delete = false;
								$this->out('Skipping item: ' . $category->name . ' image: ' . $file);
								unset($this->{$folderType}[$i2]);
								break;
							}
						}
					}

					if ($delete)
					{
						$this->counter++;
						$this->out('Deleting image: ' . $file);
						RedshopbHelperThumbnail::deleteImage($fileName, 1, $folderType);
					}
				}
			}
			elseif ($type == 'folder')
			{
				foreach ($source as $folder)
				{
					$this->folderStart--;
					$files   = array();
					$folders = array();

					if (JFolder::exists($folder))
					{
						if ($this->folderStart <= 0)
						{
							$files = JFolder::files($folder, '.png');
							$this->folderLimit--;

							if ($this->folderLimit <= 0)
							{
								return false;
							}
						}

						$folders = JFolder::folders($folder);
					}

					$folderSources = array();

					foreach ($files as $file)
					{
						$folderSources['file'][] = $folder . '/' . $file;
					}

					foreach ($folders as $subFolder)
					{
						$folderSources['folder'][] = $folder . '/' . $subFolder;
					}

					if ($this->folderStart <= 0)
					{
						$this->out('Found ' . count($folders) . ' folders and ' . count($files) . ' files in folder: ' . $folder);
					}

					if (!empty($folderSources))
					{
						if (!$this->deleteImages($folderSources, $folderType))
						{
							return false;
						}
					}
				}
			}
		}

		return true;
	}
}

$folderStart = !empty($argv[1]) ? (int) $argv[1] : 0;
$folderLimit = !empty($argv[2]) ? (int) $argv[2] : 0;

$instance              = CliApplication::getInstance('Clean_Duplicate_ImagesApplicationCli');
$instance->folderStart = $folderStart;
$instance->folderLimit = $folderLimit;
$instance->execute();
