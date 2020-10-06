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
 * Get Fields function.
 *
 * @package     Aesir.E-Commerce.Plugin
 * @subpackage  Webservice
 * @since       1.0
 */
class WebserviceGetFields extends WebserviceFunctionBase
{
	/**
	 * Name in the Sync Table
	 *
	 * @var  string
	 */
	public $syncName = 'erp.webservice.fields';

	/**
	 * Url for sync list of tags
	 *
	 * @var  string
	 */
	public $readListUrl = 'index.php?webserviceClient=site&webserviceVersion=1.2.0&option=redshopb'
							. '&view=product_field&api=hal&list[ordering]=id&list[direction]=ASC';

	/**
	 * @var string
	 */
	public $tableClassName = 'Field';

	/**
	 * Constructor.
	 */
	public function __construct()
	{
		$this->avoidOverrideWSProperties[] = 'alias';
		$this->avoidOverrideWSProperties[] = 'multiple_values';
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

		$row            = array();
		$row['type_id'] = $this->getUnitId((string) $item->type_code, '#__redshopb_type');

		if (!$row['type_id'])
		{
			RedshopbHelperSync::addMessage(
				Text::sprintf('PLG_RB_SYNC_WEBSERVICE_ERROR_TYPE_NOT_EXISTS', (string) $item->type_code, $remoteId),
				'warning'
			);

			return false;
		}

		$isNew     = true;
		$itemData  = $this->findSyncedId($this->syncName, $remoteId, '', true, $table);
		$hashedKey = RedshopbHelperSync::generateHashKey($item, 'object', 2);

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

		$row['name']                = $this->getUniqueFieldName((string) $item->name, $table->get('id'));
		$row['state']               = (int) $item->state;
		$row['title']               = (string) $item->title;
		$row['alias']               = (string) $item->alias;
		$row['description']         = (string) $item->description;
		$row['default_value']       = (string) $item->default_value;
		$row['params']              = (string) $item->params;
		$row['multiple_values']     = (int) $item->multiple_values;
		$row['ordering']            = (int) $item->ordering;
		$row['searchable_frontend'] = (int) $item->searchable_frontend;
		$row['searchable_backend']  = (int) $item->searchable_backend;
		$row['global']              = (int) $item->global;

		if ((string) $item->values_field_id != $item->{$this->key})
		{
			$row['field_value_xref_id'] = $this->findSyncedId($this->syncName, (string) $item->values_field_id);
		}

		if ((string) $item->filter_type_code)
		{
			$row['filter_type_id'] = $this->getUnitId((string) $item->filter_type_code, '#__redshopb_type');
		}

		$newSyncStatus = 0;

		if ($this->isExecutionTimeExceeded() || $this->goToNextPart)
		{
			$this->goToNextPart = true;
			--$this->counter;
			$newSyncStatus = 2;
		}

		// Remove all ghost related records(without relation in sync table)
		if (!$isNew && !$this->goToNextPart)
		{
			$db       = Factory::getDbo();
			$subQuery = $db->getQuery(true)
				->select('CONVERT(s.local_id, SIGNED)')
				->from($db->qn('#__redshopb_sync', 's'))
				->where('s.reference = ' . $db->q('erp.webservice.field_values'));
			$query    = $db->getQuery(true)
				->select('fv.id')
				->from($db->qn('#__redshopb_field_value', 'fv'))
				->where('fv.field_id = ' . (int) $table->get('id'))
				->where('fv.id NOT IN(' . $subQuery . ')');

			$results = $db->setQuery($query)
				->loadColumn();

			if (!empty($results))
			{
				$tableFieldValue = RTable::getInstance('Field_Value', 'RedshopbTable')
					->setOption('forceWebserviceUpdate', true)
					->setOption('lockingMethod', 'Sync');

				foreach ($results as $result)
				{
					if (!$tableFieldValue->delete($result))
					{
						RedshopbHelperSync::addMessage($tableFieldValue->getError(), 'warning');
						$hashedKey = null;

						break;
					}

					if ($this->isExecutionTimeExceeded())
					{
						$this->goToNextPart = true;
						--$this->counter;
						$newSyncStatus = 2;

						break;
					}
				}
			}

			if (!$this->goToNextPart)
			{
				$subQuery = $db->getQuery(true)
					->select('CONVERT(s.local_id, SIGNED)')
					->from($db->qn('#__redshopb_sync', 's'))
					->where('s.reference = ' . $db->q('erp.webservice.field_data'));
				$query    = $db->getQuery(true)
					->select('d.id')
					->from($db->qn('#__redshopb_field_data', 'd'))
					->where('d.field_id = ' . (int) $table->get('id'))
					->where('d.id NOT IN (' . $subQuery . ')');

				$results = $db->setQuery($query)
					->loadColumn();

				if (!empty($results))
				{
					$tableFieldData = RTable::getInstance('Field_Data', 'RedshopbTable')
						->setOption('forceWebserviceUpdate', true)
						->setOption('lockingMethod', 'Sync');

					foreach ($results as $result)
					{
						if (!$tableFieldData->delete($result))
						{
							RedshopbHelperSync::addMessage($tableFieldData->getError(), 'warning');
							$hashedKey = null;

							break;
						}

						if ($this->isExecutionTimeExceeded())
						{
							$this->goToNextPart = true;
							--$this->counter;
							$newSyncStatus = 2;

							break;
						}
					}
				}
			}
		}

		if (!$table->save($row))
		{
			RedshopbHelperSync::addMessage($table->getError(), 'warning');

			return false;
		}

		if ((string) $item->values_field_id == $item->{$this->key})
		{
			$row = array(
				'field_value_xref_id' => $table->get('id')
			);

			if (!$table->save($row))
			{
				RedshopbHelperSync::addMessage($table->getError(), 'warning');

				return false;
			}
		}

		// Save this item ID to synced table
		$this->recordSyncedId(
			$this->syncName, $remoteId, $table->get('id'), '', $isNew,
			$newSyncStatus, '', false, '', $table, 1, $hashedKey
		);

		if ($newSyncStatus == 0)
		{
			return true;
		}
		else
		{
			return false;
		}
	}
}
