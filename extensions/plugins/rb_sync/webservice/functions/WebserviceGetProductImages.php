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
use Joomla\CMS\Table\Table;
use Joomla\CMS\Language\Text;

require_once __DIR__ . '/base.php';

/**
 * Get Categories function.
 *
 * @package     Aesir.E-Commerce.Plugin
 * @subpackage  Webservice
 * @since       1.0
 */
class WebserviceGetProductImages extends WebserviceFunctionBase
{
	/**
	 * Name in the Sync Table
	 *
	 * @var  string
	 */
	public $syncName = 'erp.webservice.product_images';

	/**
	 * Names used in the Sync Table. Currently used only for clearing Hashed Keys
	 *
	 * @var  string
	 */
	public $allUsedSyncNames = array(
		'Media' => 'erp.webservice.product_images'
	);

	/**
	 * Url for sync list of categories
	 *
	 * @var  string
	 */
	public $readListUrl = 'index.php?webserviceClient=site&webserviceVersion=1.3.0&option=redshopb'
							. '&view=product_image&api=hal&list[ordering]=product_id&list[direction]=ASC&task=read';

	/**
	 * List of categories with parent has not synced yet.
	 *
	 * @var  array
	 */
	public $syncParentCategories = array();

	/**
	 * @var string
	 */
	public $cronName = 'ProductImages';

	/**
	 * @var string
	 */
	public $tableClassName = 'Media';

	/**
	 * @var string
	 */
	public $postFilterKey = 'product_id_array';

	/**
	 * @var string
	 */
	public $productModelKey = 'remote_key';

	/**
	 * @var boolean
	 */
	public $deleteItemsNotPresentInRemote = false;

	/**
	 * @var boolean
	 */
	public $processStoreOtherIds = false;

	/**
	 * Constructor.
	 *
	 */
	public function __construct()
	{
		parent::__construct();

		// Sets the enrichment base and reference to use them later when syncing
		$this->setEnrichmentBase();
	}

	/**
	 * Method for synchronize an single category
	 *
	 * @param   object  $item   Item object data
	 * @param   Table   $table  Table object
	 *
	 * @return  boolean
	 *
	 * @throws  Exception
	 */
	public function synchronizeItem($item, $table)
	{
		$remoteId = (int) $item->id;

		if (isset($this->executed[$remoteId . '_']))
		{
			return true;
		}

		if (!$item->image)
		{
			return false;
		}

		$productIds = $this->getProductsByRelatedId($item->product_id);

		if (empty($productIds))
		{
			return true;
		}

		$row       = array();
		$hashedKey = RedshopbHelperSync::generateHashKey($item, 'object');

		$row['alt']   = (string) $item->alt;
		$row['view']  = (string) $item->view;
		$row['state'] = (int) $item->state;

		foreach ($productIds as $productId)
		{
			// If product does not exist anymore then we skip it
			if (!RedshopbEntityProduct::getInstance((int) $productId)->isValid())
			{
				continue;
			}

			$table                         = RTable::getInstance($this->tableClassName, 'RedshopbTable')
				->setOption('forceWebserviceUpdate', true)
				->setOption('lockingMethod', 'Sync');
			$productImageRow               = $row;
			$unSerialize                   = array('image' => '', 'modified_date' => '');
			$productImageRow['product_id'] = $productId;
			$isNew                         = true;
			/** @var object $itemData */
			$itemData = $this->findSyncedId($this->syncName, $remoteId, $productId, true, $table);

			if (!$this->isHashChanged($itemData, $hashedKey))
			{
				// Hash key is the same so we will continue on the next item
				$this->skipItemUpdate($itemData);

				continue;
			}

			if ($itemData)
			{
				if (!$itemData->deleted && $table->load($itemData->local_id))
				{
					$unSerialize = RedshopbHelperSync::mbUnserialize($itemData->serialize);
					$isNew       = false;
				}
				// If item not exists, then user delete it, so lets skip it
				elseif ($itemData->deleted)
				{
					$this->skipItemUpdate($itemData);

					continue;
				}
				else
				{
					$this->deleteSyncedId($this->syncName, $remoteId, $productId);
				}
			}

			if (($itemData && $isNew) || !$itemData)
			{
				// Get product images and check if they have wildcard
				$db         = Factory::getDbo();
				$query      = $db->getQuery(true)
					->select('s.remote_key')
					->from($db->qn('#__redshopb_media', 'm'))
					->leftJoin($db->qn('#__redshopb_sync', 's') . ' ON s.local_id = m.id AND s.reference = ' . $db->q($this->enrichmentSyncRef))
					->where('m.product_id = ' . $productId)
					->where('s.remote_key IS NOT NULL');
				$remoteKeys = $db->setQuery($query)->loadColumn();

				if (!empty($remoteKeys))
				{
					foreach ($remoteKeys as $remoteKey)
					{
						// We stop with enrichment for this item all together if the wildcard is found
						if (strpos($remoteKey, '*') !== false)
						{
							continue 2;
						}
					}
				}
			}

			if (empty($item->modified_date) || $item->modified_date != $unSerialize['modified_date'])
			{
				$unSerialize['image']         = $item->image;
				$unSerialize['modified_date'] = $item->modified_date;

				$image = array('image' => $item->image);
				$image = RedshopbHelperWebservices::getTempImageURL($image, 'remoteProductImage');

				$destinationFolderPath = JPATH_SITE . '/media/com_redshopb/' . $this->params->get('remoteImageFolder', 'b2b/images');
				$destinationPath       = $destinationFolderPath . '/' . $image['image_file']['remoteProductImage']['name'];
				$fullPath              = (string) $image['image_file']['remoteProductImage']['tmp_name'];

				if (!$image || !is_file($fullPath))
				{
					// Download broke, we will sync it next time
					$this->skipItemUpdate($itemData);
					$hashedKey = null;

					continue;
				}

				if (!RedshopbHelperMedia::makeFolder($destinationFolderPath))
				{
					$this->skipItemUpdate($itemData);
					$hashedKey = null;

					continue;
				}

				if (!JFile::copy($fullPath, $destinationPath))
				{
					// Copy failed, we will sync it next time
					$this->skipItemUpdate($itemData);
					$hashedKey = null;
					RedshopbHelperSync::addMessage(
						Text::sprintf('COM_REDSHOPB_THUMBNAIL_ERROR_MOVING_FILE_TO_DIRECTORY', $fullPath, $destinationPath),
						'warning'
					);

					continue;
				}

				// Remove image from tmp folder
				JFile::delete($fullPath);

				$productImageRow['name']        = $image['image_file']['remoteProductImage']['name'];
				$productImageRow['remote_path'] = 'media/com_redshopb/' . $this->params->get('remoteImageFolder', 'b2b/images');

				if ($table->get('name') != '')
				{
					// Delete thumbnails
					RedshopbHelperThumbnail::deleteImage($table->get('name'), 1, 'products', $table->remote_path);

					if ($table->get('name') != $productImageRow['name'])
					{
						// Delete media file if it is the only one
						$this->deleteRemoteImage($table);
					}
				}
			}

			$this->setUserInformation($productImageRow, $isNew);

			if (!$table->save($productImageRow))
			{
				RedshopbHelperSync::addMessage($table->getError(), 'warning');
				$hashedKey = null;
				$this->skipItemUpdate($itemData);

				continue;
			}

			$serialize = serialize($unSerialize);

			// Save this item ID to synced table
			$this->recordSyncedId(
				$this->syncName, $remoteId, $table->get('id'), $productId, $isNew, 0, $serialize,
				false, '', $table, 1, $hashedKey
			);

			$this->storeOtherIds($item, $table);
		}

		return true;
	}
}
