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
use Joomla\CMS\Language\Text;

require_once __DIR__ . '/base.php';

/**
 * Get Category Image class.
 *
 * @package     Aesir.E-Commerce.Plugin
 * @subpackage  PIM
 * @since       1.0
 */
class PimGetCategoryImage extends PimFunctionBase
{
	/**
	 * @var string
	 */
	public $pluginName = 'pim';

	/**
	 * @var string
	 */
	public $syncName = 'erp.pim.category.image';

	/**
	 * Names used in the Sync Table. Currently used only for clearing Hashed Keys
	 *
	 * @var  string
	 */
	public $allUsedSyncNames = array('Category' => 'erp.pim.category.image');

	/**
	 * @var string
	 */
	public $cronName = 'GetCategoryImage';

	/**
	 * @var string
	 */
	public $tableClassName = 'Category';

	/**
	 * Should base methods use database transaction
	 *
	 * @var  boolean
	 */
	public $useTransaction = true;

	/**
	 * Constructor.
	 */
	public function __construct()
	{
		$this->avoidOverrideWSProperties = array_merge(
			$this->avoidOverrideWSProperties,
			array(
				'alias', 'description', 'template_id', 'product_grid_template_id', 'product_list_template_id'
			)
		);

		parent::__construct();
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
				'folder' => array($this->client->localFolder . '/Resource')
			);

			// Check to see if we are already run Sync which is not finished (so we can have multiple parts if webservice is too big)
			if (!$this->isCronExecuted($this->cronName))
			{
				// We set them with a flag so we can delete the ones which are not present in the latest Sync xml
				$this->setSyncRowsAsExecuted($this->syncName);

				// We set cron as executed so we can have multipart process if the process takes too long
				$this->setCronAsExecuted($this->cronName);

				$this->processItemsCompleted = 0;
			}
			else
			{
				// Get list executed in previous sync items because this is multipart process
				$this->executed = $this->getPreviousSyncExecutedList($this->syncName);
			}

			// Set webservice parameters
			$this->params->set('processItemsCompleted', $this->processItemsCompleted);
			$this->params->set('processItemsStep', $this->processItemsStep);
			$this->params->set('groupLoadedFiles', true);
			$this->params->set('localFolder', $params->get('localFolder'));

			// Get data from files
			$xml = $this->client->getXmlData($source, $this->params);

			if (empty($xml))
			{
				throw new Exception(Text::sprintf('PLG_RB_SYNC_PIM_FAILED_TO_FETCH_ITEMS', json_encode($source)));
			}

			$db->unlockTables();

			if ($this->useTransaction)
			{
				$db->transactionStart();
			}

			// Sets number of executed items in Cron
			RedshopbHelperSync::setProgressCounters($this->cronTable, null, $this->client->numberOfFiles);

			// Start sync with the new XML data
			$this->readXml($xml);

			if ($this->useTransaction)
			{
				$db->transactionCommit();
			}

			$this->counterTotal = $this->client->numberOfFiles;
			$this->counter     += $this->processItemsCompleted;
			$this->goToNextPart = false;

			if ($this->counter < $this->counterTotal)
			{
				$this->goToNextPart = true;
			}

			if (!$this->goToNextPart)
			{
				$this->deleteFilesNotPresentInRemote($this->syncName);

				if ($this->isExecutionTimeExceeded())
				{
					$this->goToNextPart = true;
				}
			}

			if (!$this->goToNextPart)
			{
				$this->deleteRowsNotPresentInRemote($this->syncName);

				if ($this->isExecutionTimeExceeded())
				{
					$this->goToNextPart = true;
				}
			}

			if (!$this->goToNextPart)
			{
				// We are setting cron as finished (no more parts)
				$this->setCronAsFinished($this->cronName);
			}

			$db->unlockTables();
		}
		catch (Exception $e)
		{
			if ($this->useTransaction)
			{
				$db->transactionRollback();
			}

			RedshopbHelperSync::addMessage($e->getMessage(), 'error');

			return false;
		}

		return $this->outputResult();
	}

	/**
	 * Read and store the data.
	 *
	 * @param   SimpleXMLElement  $xml  XML element
	 *
	 * @return  boolean
	 *
	 * @throws  \Exception
	 */
	public function readXml($xml)
	{
		if (empty($xml->Resource))
		{
			return true;
		}

		$localFolder = $this->params->get('localFolder', '');

		foreach ($xml->Resource as $obj)
		{
			$this->counter++;
			$attributes       = $obj->attributes();
			$remoteId         = (string) $attributes['id'];
			$resourceFileName = $remoteId . '.' . JFile::getExt((string) $obj->Filename);
			$resourceFilePath = (string) $this->client->localFolder . '/Pics/' . $resourceFileName;

			if (empty($obj->Assortments))
			{
				continue;
			}

			// We need to check for the timestamp of the image too
			$hashedKey     = RedshopbHelperSync::generateHashKey($xml, 'xml');
			$fileTimeStamp = filemtime($resourceFilePath);
			$hashedKey    .= RedshopbHelperSync::generateHashKey($fileTimeStamp, 'string');

			foreach ($obj->Assortments->Assortment as $assortment)
			{
				$catAttributes = $assortment->attributes();
				$remoteCatId   = (string) $catAttributes['id'];

				if (isset($this->executed[$remoteCatId . '_' . $resourceFileName]))
				{
					continue;
				}

				/** @var RedshopbTableCategory $table */
				$table                 = RTable::getInstance('Category', 'RedshopbTable')
					->setOption('forceWebserviceUpdate', true)
					->setOption('lockingMethod', 'Sync');
				$row                   = array();
				$isNew                 = true;
				$idIsLoad              = false;
				$fileToDelete          = null;
				$serialize             = array();
				$itemDataCategoryImage = $this->findSyncedId($this->syncName, $remoteCatId, $resourceFileName, true);
				$isModified            = $this->isResourceChanged($itemDataCategoryImage, $resourceFileName, $serialize);

				if (!file_exists($resourceFilePath) || !$this->isResourceImage($obj))
				{
					// Failed to copy the file because it does not exist
					RedshopbHelperSync::addMessage(
						Text::sprintf(
							'PLG_RB_SYNC_PIM_CATEGORY_IMAGE_NOT_FOUND', (string) $resourceFilePath, $localFolder . '/Pics', $remoteId
						),
						'warning'
					);
					$this->skipItemUpdate($itemDataCategoryImage);
					$hashedKey = null;
					continue;
				}

				if (!$isModified && !$this->isHashChanged($itemDataCategoryImage, $hashedKey))
				{
					// Hash key is the same so we will continue on the next item
					$this->skipItemUpdate($itemDataCategoryImage);

					continue;
				}

				if ($itemDataCategoryImage)
				{
					$isNew = false;
				}

				$itemDataCategory = $this->findSyncedId('erp.pim.category', $remoteCatId, '', true, $table);

				if ($itemDataCategory)
				{
					if (!$itemDataCategory->deleted && $table->load($itemDataCategory->local_id))
					{
						$idIsLoad = true;
					}

					// If item not exists, then user delete it, so lets skip it
					elseif ($itemDataCategory->deleted)
					{
						continue;
					}
				}

				// Category relation not found
				if (!$idIsLoad)
				{
					// Something went wrong so we should re-sync it
					$hashedKey = null;
					continue;
				}

				if (!$isModified && !$isNew)
				{
					$checkedImage = JPATH_SITE . '/' . RedshopbHelperThumbnail::getFullImagePath($table->get('image'), 'categories');

					if (!JFile::exists($checkedImage))
					{
						$isModified = true;
					}
				}

				if ($isNew || $isModified)
				{
					$row['image'] = RedshopbHelperThumbnail::savingImage(
						$resourceFilePath,
						$resourceFilePath,
						$table->get('id'),
						false,
						'categories'
					);

					if (!$row['image'])
					{
						$this->deleteSyncedId($this->syncName, $remoteCatId, $resourceFileName);
						$hashedKey = null;

						continue;
					}

					// Delete old image if it exists
					if ($table->image && $table->image != $row['image'])
					{
						$fileToDelete = $table->image;
					}

					$row['filter_fieldset_id'] = $table->get('filter_fieldset_id');

					if (!$table->save($row))
					{
						// We will not update Hash key since this item needs to sync again
						$hashedKey = null;
						RedshopbHelperSync::addMessage($table->getError(), 'warning');
					}

					// If the category's image is not overridden by sync we want to delete the new image instead
					if ($table->get('image') != $row['image'])
					{
						$fileToDelete = $row['image'];
					}

					if ($fileToDelete)
					{
						RedshopbHelperThumbnail::deleteImage($fileToDelete, 1, 'categories');
					}

					if (!$table->get('image'))
					{
						$hashedKey = null;
					}

					$this->recordSyncedId(
						'erp.pim.category', $remoteCatId, $table->id, $itemDataCategory->remote_parent_key, false,
						0, '', false, '', $table
					);
				}

				$this->recordSyncedId(
					$this->syncName, $remoteCatId, $table->id, $resourceFileName, $isNew,
					0, serialize($serialize), false, '', null, 1, $hashedKey
				);
			}
		}

		return true;
	}

	/**
	 * Checks if the resource is an image
	 *
	 * @param   SimpleXMLElement  $xml  XML element
	 *
	 * @return boolean
	 */
	public function isResourceImage($xml)
	{
		if (!empty($xml->ResourceType) && (string) $xml->ResourceType == 'Picture')
		{
			return true;
		}

		// PIM changed ResourceType so it may be empty so we have this workaround
		if (!empty($xml->Filename))
		{
			$ext = strtolower(JFile::getExt((string) $xml->Filename));

			if (in_array($ext, array('jpeg', 'jpg', 'gif', 'png')))
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * Deletes images not present in remote
	 *
	 * @param   string  $reference  Reference name
	 *
	 * @return array
	 *
	 * @throws Exception
	 */
	public function deleteFilesNotPresentInRemote($reference)
	{
		$db       = Factory::getDbo();
		$subQuery = $db->getQuery(true)
			->select('s2.local_id')
			->from($db->qn('#__redshopb_sync', 's2'))
			->where('s2.reference = ' . $db->q($reference))
			->where('s2.execute_sync = 1');
		$query    = $db->getQuery(true)
			->select('c.image, c.id, s.remote_key, s.remote_parent_key, s.serialize')
			->from($db->qn('#__redshopb_category', 'c'))
			->leftJoin(
				$db->qn('#__redshopb_sync', 's') . ' ON s.reference = ' . $db->q($this->syncName)
				. ' AND s.local_id = CONCAT(' . $db->q('category.') . ', c.id)'
			)
			->where('c.id IN (' . $subQuery . ')')
			->where('c.image <> ' . $db->q(''));
		$results  = $db->setQuery($query)->loadObjectList();

		if ($results)
		{
			foreach ($results as $result)
			{
				if ($result->remote_key)
				{
					$resourceFilePath = JPATH_SITE . '/' . RedshopbHelperThumbnail::getFullImagePath($result->image, 'categories');
					$dest             = JPATH_ROOT . '/media/com_redshopb/' . $result->remote_parent_key . '/' . $result->remote_key;

					if (JFile::exists($resourceFilePath) && !JFile::exists($dest))
					{
						JFile::copy($resourceFilePath, $dest);
					}
				}

				$query->clear()
					->update($db->qn('#__redshopb_category'))
					->set('image = ' . $db->q(''))
					->where('id = ' . (int) $result->id);
				$db->setQuery($query)->execute();

				RedshopbHelperThumbnail::deleteImage($result->image, 1, 'categories');

				if ($this->isExecutionTimeExceeded())
				{
					$this->goToNextPart = true;

					return true;
				}
			}
		}

		return true;
	}

	/**
	 * Read and store the data.
	 *
	 * @param   SimpleXMLElement  $xml       XML element
	 * @param   Table             $table     Table object
	 * @param   string            $parentId  Parent id
	 *
	 * @return  void
	 */
	public function readXmlRecursive($xml, &$table, $parentId = '')
	{
	}
}
