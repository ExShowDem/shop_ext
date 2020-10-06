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
 * Get Field Values function.
 *
 * @package     Aesir.E-Commerce.Plugin
 * @subpackage  Webservice
 * @since       1.0
 */
class WebserviceGetFieldvalues extends WebserviceFunctionBase
{
	/**
	 * Name in the Sync Table
	 *
	 * @var  string
	 */
	public $syncName = 'erp.webservice.field_values';

	/**
	 * Url for sync list of tags
	 *
	 * @var  string
	 */
	public $readListUrl = 'index.php?webserviceClient=site&webserviceVersion=1.0.0&option=redshopb'
							. '&view=product_field_value&api=hal&list[ordering]=id&list[direction]=ASC';

	/**
	 * @var string
	 */
	public $tableClassName = 'Field_Value';

	/**
	 * Constructor.
	 */
	public function __construct()
	{
		$this->avoidOverrideWSProperties[] = 'default';
		parent::__construct();
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

		$row             = array();
		$row['field_id'] = $this->findSyncedId('erp.webservice.fields', (string) $item->field_id);

		if (!$row['field_id'])
		{
			RedshopbHelperSync::addMessage(
				Text::sprintf('PLG_RB_SYNC_WEBSERVICE_ERROR_FIELD_NOT_EXISTS', (string) $item->field_id, $remoteId),
				'warning'
			);

			return false;
		}

		$isNew     = true;
		$itemData  = $this->findSyncedId($this->syncName, $remoteId, '', true, $table);
		$hashedKey = RedshopbHelperSync::generateHashKey($item, 'object');
		$table->setOption('forceOrderingValues', true);

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

		$row['name']     = (string) $item->name;
		$row['value']    = (string) $item->value;
		$row['default']  = (int) $item->default;
		$row['ordering'] = (int) $item->ordering;

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
