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

require_once __DIR__ . '/base.php';

/**
 * Get Manufacturers function.
 *
 * @package     Aesir.E-Commerce.Plugin
 * @subpackage  Webservice
 * @since       1.0
 */
class WebserviceGetManufacturers extends WebserviceFunctionBase
{
	/**
	 * Name in the Sync Table
	 *
	 * @var  string
	 */
	public $syncName = 'erp.webservice.manufacturers';

	/**
	 * Url for sync list of tags
	 *
	 * @var  string
	 */
	public $readListUrl = 'index.php?webserviceClient=site&webserviceVersion=1.1.0&option=redshopb'
							. '&view=manufacturer&api=hal&list[ordering]=id&list[direction]=ASC'
							. '&filter[include_images]=true';

	/**
	 * @var string
	 */
	public $tableClassName = 'Manufacturer';

	/**
	 * Method for synchronize an single tag
	 *
	 * @param   object  $item   Tag object data
	 * @param   Table   $table  Table object
	 *
	 * @return  boolean
	 *
	 * @throws  \Exception
	 */
	public function synchronizeItem($item, $table)
	{
		$remoteId = $item->id;

		// If another sync process for this tag is running. Skip this.
		if (isset($this->executed[$remoteId . '_']))
		{
			return false;
		}

		$remoteParentId  = $item->parent_id;
		$parentId        = 1;
		$hasParentSynced = false;

		// If parent ID has available in remote data, try to synchronize parent
		if ($remoteParentId)
		{
			$parentId = $this->findSyncedId($this->syncName, $remoteParentId);

			if ($parentId)
			{
				$hasParentSynced = true;
			}
		}

		$unSerialize = array('image' => '');
		$oldImage    = '';
		$row         = array();
		$isNew       = true;
		$itemData    = $this->findSyncedId($this->syncName, $remoteId, '', true, $table);
		$hashedKey   = RedshopbHelperSync::generateHashKey($item, 'object');

		if (!$this->isHashChanged($itemData, $hashedKey))
		{
			// Hash key is the same so we will continue on the next item
			$this->skipItemUpdate($itemData);

			// Returning true so we can mark other stored sync IDs so we don't loose them
			return true;
		}

		if ($itemData)
		{
			if (!$itemData->deleted && $table->load($itemData->local_id))
			{
				$unSerialize = RedshopbHelperSync::mbUnserialize($itemData->serialize);
				$oldImage    = $table->get('image');
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

		$this->setUserInformation($row, $isNew);

		$row['name']      = (string) $item->name;
		$row['alias']     = (string) $item->alias;
		$row['state']     = (int) $item->state;
		$row['parent_id'] = $parentId;
		$item->image      = $item->image ? $item->image : '';

		if ($table->get('parent_id') != $row['parent_id'] || !$table->get('id'))
		{
			// Reprocesses record, forcing it to alter its ACL
			$table->setLocation($row['parent_id'], 'last-child');
		}

		if (!$table->save($row))
		{
			RedshopbHelperSync::addMessage($table->getError(), 'warning');

			return false;
		}

		// Download image if necessary
		if ($item->image != $unSerialize['image'])
		{
			$unSerialize['image'] = $item->image;
			$row['image']         = '';

			if ($table->get('image') != '' && $oldImage == $table->get('image'))
			{
				RedshopbHelperThumbnail::deleteImage($table->get('image'), 1, 'manufacturers');
			}

			if ($item->image)
			{
				$row['image'] = RedshopbHelperThumbnail::savingImage($item->image, $item->image, $table->get('id'), true, 'manufacturers');
			}

			if (!$table->save($row))
			{
				// We will not update Hash key since this item needs to sync again
				$hashedKey = null;
				RedshopbHelperSync::addMessage($table->getError(), 'warning');
			}
		}

		$serialize = serialize($unSerialize);

		// Save this item ID to synced table
		$this->recordSyncedId(
			$this->syncName,
			$remoteId, $table->get('id'),
			'',
			$isNew,
			0,
			$serialize,
			false,
			'',
			$table,
			1,
			$hashedKey
		);

		// If this tag has parent not synced yet and this is not ROOT tag
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
