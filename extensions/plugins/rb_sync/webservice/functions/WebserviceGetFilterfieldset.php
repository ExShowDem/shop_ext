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
 * Get Filter Fieldset function.
 *
 * @package     Aesir.E-Commerce.Plugin
 * @subpackage  Webservice
 * @since       1.0
 */
class WebserviceGetFilterfieldset extends WebserviceFunctionBase
{
	/**
	 * Name in the Sync Table
	 *
	 * @var  string
	 */
	public $syncName = 'erp.webservice.filterfieldset';

	/**
	 * Url for sync list of tags
	 *
	 * @var  string
	 */
	public $tableClassName = 'Filter_Fieldset';

	/**
	 * Constructor.
	 *
	 */
	public function __construct()
	{
		parent::__construct();
		$this->readListUrl = 'index.php?webserviceClient=site&webserviceVersion=1.1.0&option=redshopb&view=filter_fieldset' .
			'&api=hal&list[ordering]=id&list[direction]=ASC&filter[include_fields]=true';
	}

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

		$row      = array();
		$isNew    = true;
		$itemData = $this->findSyncedId($this->syncName, $remoteId, '', true, $table);
		$table->setOption('fields_relate.store', true);
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

		$this->setUserInformation($row, $isNew);

		$row['name']  = (string) $item->name;
		$row['state'] = (int) $item->state;

		if (isset($item->fields) && is_array($item->fields) && count($item->fields) > 0)
		{
			$row['fields'] = array();

			foreach ($item->fields as $field)
			{
				$fieldId = $this->findSyncedId('erp.webservice.fields', (string) $field);

				if ($fieldId)
				{
					$row['fields'][] = $fieldId;
				}
				else
				{
					// Something went wrong so we should re-sync it
					$hashedKey = null;
				}
			}
		}

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
			'',
			$isNew,
			$newSyncStatus = 0,
			$serialize     = '',
			$ignoreLocalId = false,
			$newLocalId    = '',
			$table,
			$mainReference = 1,
			$hashedKey
		);

		return true;
	}
}
