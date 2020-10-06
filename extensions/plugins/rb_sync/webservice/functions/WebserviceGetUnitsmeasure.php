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
 * Get Units of measure function.
 *
 * @package     Aesir.E-Commerce.Plugin
 * @subpackage  Webservice
 * @since       1.0
 */
class WebserviceGetUnitsmeasure extends WebserviceFunctionBase
{
	/**
	 * Name in the Sync Table
	 *
	 * @var  string
	 */
	public $syncName = 'erp.webservice.units_measure';

	/**
	 * Url for sync list of tags
	 *
	 * @var  string
	 */
	public $readListUrl = 'index.php?webserviceClient=site&webserviceVersion=1.0.0&option=redshopb'
							. '&view=unit_measure&api=hal&list[ordering]=id&list[direction]=ASC';

	/**
	 * @var string
	 */
	public $tableClassName = 'Unit_Measure';

	/**
	 * @var string
	 */
	public $key = 'code';

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
		$remoteId = $item->{$this->key};

		// If another sync process for this tag is running. Skip this.
		if (isset($this->executed[$remoteId . '_']))
		{
			return false;
		}

		$row       = array();
		$isNew     = true;
		$itemData  = $this->findSyncedId($this->syncName, $remoteId, '', true, $table);
		$hashedKey = RedshopbHelperSync::generateHashKey($item, 'object');

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
				$isNew = false;
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

		$row['name']             = (string) $item->name;
		$row['alias']            = (string) $item->code;
		$row['description']      = (string) $item->description;
		$row['decimal_position'] = (int) $item->decimal_position;

		if (!$table->save($row))
		{
			RedshopbHelperSync::addMessage($table->getError(), 'warning');

			return false;
		}

		// Save this item ID to synced table
		$this->recordSyncedId(
			$this->syncName,
			$remoteId,
			$table->get('id'),
			$remoteParentKey = '',
			$isNew,
			$newSyncStatus   = 0,
			$serialize       = '',
			$ignoreLocalId   = false,
			$newLocalId      = '',
			$table,
			$mainReference   = 1,
			$hashedKey
		);

		return true;
	}
}
