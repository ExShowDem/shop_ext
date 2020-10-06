<?php
/**
 * @package     Aesir.E-Commerce.Plugin
 * @subpackage  Client
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('JPATH_BASE') or die;

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
class WebserviceGetCategories extends WebserviceFunctionBase
{
	/**
	 * Name in the Sync Table
	 *
	 * @var  string
	 */
	public $syncName = 'erp.webservice.categories';

	/**
	 * @var string
	 */
	public $tableClassName = 'Category';

	/**
	 * Constructor.
	 *
	 */
	public function __construct()
	{
		parent::__construct();
		$this->readListUrl = 'index.php?webserviceClient=site&webserviceVersion=1.4.0&option=redshopb'
		. '&view=category&api=hal&list[ordering]=id&list[direction]=ASC'
		. '&filter[include_images]=true&filter[include_local_fields]=true';

		// Sets the enrichment base and reference to use them later when syncing
		$this->setEnrichmentBase();
	}

	/**
	 * Method for synchronize an single category
	 *
	 * @param   object  $category  Category object data
	 * @param   Table   $table     Table object
	 *
	 * @return  boolean
	 *
	 * @throws  \Exception
	 */
	public function synchronizeItem($category, $table)
	{
		$remoteId        = (int) $category->id;
		$remoteParentId  = $category->parent_id;
		$hasParentSynced = false;

		if (isset($this->executed[$remoteId . '_']))
		{
			return false;
		}

		// Trigger the event
		$results = RFactory::getDispatcher()
			->trigger('onSynchronizeItem', array(&$this, &$category, &$table));

		if (count($results) && in_array(false, $results, true))
		{
			return false;
		}

		// If parent ID has available in remote data, try to synchronize parent
		$parentId = $this->findSyncedId($this->syncName, $remoteParentId);

		if ($remoteParentId && $parentId)
		{
			$hasParentSynced = true;
		}

		$unSerialize = array('image' => '', 'modified_date' => '');
		$row         = array();
		$isNew       = true;
		$itemData    = $this->findSyncedId($this->syncName, $remoteId, '', true, $table);
		$hashedKey   = RedshopbHelperSync::generateHashKey($category, 'object');
		$isEnriched  = false;

		if (!$this->isHashChanged($itemData, $hashedKey))
		{
			// Hash key is the same so we will continue on the next item
			$this->skipItemUpdate($itemData);

			// Returning true so we can mark other stored sync IDs so we don't lose them
			return true;
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

				return false;
			}
			else
			{
				$this->deleteSyncedId($this->syncName, $remoteId);
			}
		}

		if (($itemData && $isNew) || !$itemData)
		{
			// If the category is not synced already, it looks if there is a current category with the saved enrichment base
			if ($this->enrichmentBase != '' && isset($category->id_others) && is_array($category->id_others))
			{
				foreach ($category->id_others as $otherIdFull)
				{
					if (strpos($otherIdFull, $this->enrichmentBase) === 0)
					{
						$otherId         = substr($otherIdFull, strlen($this->enrichmentBase));
						$itemDataSyncRef = $this->findSyncedId($this->enrichmentSyncRef, $otherId, '', true, $table);

						// Looks that the found enrichment item is not in use by other category already
						if ($itemDataSyncRef)
						{
							if (!$this->findSyncedLocalId($this->syncName, $itemDataSyncRef->local_id)
								&& !$itemDataSyncRef->deleted
								&& $table->load($itemDataSyncRef->local_id))
							{
								$unSerialize = RedshopbHelperSync::mbUnserialize($itemDataSyncRef->serialize);
								$isNew       = true;
								$isEnriched  = true;
								break;
							}
						}
					}
				}
			}
		}

		$this->setUserInformation($row, $isNew);

		$row['name']               = (string) $category->name;
		$row['alias']              = (string) $category->alias;
		$row['description']        = (string) $category->description;
		$row['state']              = (int) $category->state;
		$row['parent_id']          = $parentId ? $parentId : 1;
		$row['filter_fieldset_id'] = null;

		if ($table->get('parent_id') != $row['parent_id'] || !$table->get('id'))
		{
			// Reprocesses record, forcing it to alter its ACL
			$table->setLocation($row['parent_id'], 'last-child');
		}

		// We need to resync it when we get the parent again to get right location
		if (!$parentId)
		{
			$hashedKey = null;
		}

		if ((int) $category->filter_fieldset_id)
		{
			$row['filter_fieldset_id'] = $this->findSyncedId('erp.webservice.filterfieldset', (int) $category->filter_fieldset_id);
		}

		$category->image = isset($category->image) ? $category->image : '';

		if (!$table->save($row))
		{
			RedshopbHelperSync::addMessage($table->getError(), 'warning');
			$this->skipItemUpdate($itemData);

			return false;
		}

		$imageLocked = $table->isTableColumnLocked('image');

		// Download image if necessary
		if (!$imageLocked && (empty($category->modified_date) || $category->modified_date != $unSerialize['modified_date']))
		{
			$originalImageStatus          = $unSerialize['image'];
			$originalModifiedStatus       = $unSerialize['modified_date'];
			$unSerialize['image']         = $category->image;
			$unSerialize['modified_date'] = $category->modified_date;
			$row['image']                 = '';

			if ($category->image)
			{
				$row['image'] = RedshopbHelperThumbnail::savingImage($category->image, $category->image, $table->get('id'), true, 'categories');

				// If new image is valid we delete the old one if needed
				if ($row['image'])
				{
					if ($table->get('image') != '' && $table->get('image') != $row['image'])
					{
						RedshopbHelperThumbnail::deleteImage($table->get('image'), 1, 'categories');
					}
				}
				else
				{
					// We failed to save the image, so we need to sync again
					$hashedKey = null;

					// We must also restore the serialized values, so that the item can properly know the status of the image sync
					$unSerialize['image']         = $originalImageStatus;
					$unSerialize['modified_date'] = $originalModifiedStatus;

					RedshopbHelperSync::addMessage(
						Text::sprintf('PLG_RB_SYNC_WEBSERVICE_ERROR_CATEGORY_IMAGE_SAVE', $remoteId),
						'warning'
					);
				}
			}

			if (!$table->save($row))
			{
				// We will not update Hash key since this item needs to sync again
				$hashedKey = null;

				// We must also restore the serialized values, so that the item can properly know the status of the image sync
				$unSerialize['image']         = $originalImageStatus;
				$unSerialize['modified_date'] = $originalModifiedStatus;

				RedshopbHelperSync::addMessage($table->getError(), 'warning');
			}
		}

		// Save associated fields
		if ($category->local_fields)
		{
			$xrefTable = RTable::getInstance('Category_Field_Xref', 'RedshopbTable')
				->setOption('forceWebserviceUpdate', true)
				->setOption('lockingMethod', 'Sync');

			$xrefRow['category_id'] = $table->get('id');

			foreach ($category->local_fields as $fieldRemoteId)
			{
				$fieldId             = $this->findSyncedId('erp.webservice.fields', $fieldRemoteId);
				$xrefRow['field_id'] = $fieldId;

				$xrefTable->save($xrefRow);
			}
		}

		$serialize = serialize($unSerialize);
		$main      = $itemData ? $itemData->main_reference : 1;
		$isMain    = $isNew && $isEnriched ? 0 : $main;

		// Save this item ID to synced table
		$this->recordSyncedId(
			$this->syncName, $remoteId, $table->get('id'), '', $isNew, 0, $serialize,
			false, '', $table, (bool) $isMain, $hashedKey
		);

		// If this category has parent not synced yet and this is not ROOT category
		if (!$hasParentSynced && $remoteParentId)
		{
			if (!isset($this->syncParentItems[$remoteParentId]))
			{
				$this->syncParentItems[$remoteParentId] = array();
			}

			$this->syncParentItems[$remoteParentId][] = $remoteId;
		}

		return true;
	}
}
