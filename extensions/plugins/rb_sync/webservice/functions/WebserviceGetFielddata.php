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
 * Get Filter Fieldset function.
 *
 * @package     Aesir.E-Commerce.Plugin
 * @subpackage  Webservice
 * @since       1.0
 */
class WebserviceGetFielddata extends WebserviceFunctionBase
{
	/**
	 * Name in the Sync Table
	 *
	 * @var  string
	 */
	public $syncName = 'erp.webservice.field_data';

	/**
	 * Names used in the Sync Table. Currently used only for clearing Hashed Keys
	 *
	 * @var  string
	 */
	public $allUsedSyncNames = array('Field_Data' => 'erp.webservice.field_data');

	/**
	 * @var string
	 */
	public $tableClassName = 'Field_Data';

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
		$this->readListUrl = 'index.php?webserviceClient=site&webserviceVersion=1.2.0&option=redshopb' .
			'&view=product_field_data&api=hal&filter[display_params]=true&list[ordering]=product_id&list[direction]=ASC&task=read';
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
			return true;
		}

		$productIds = $this->getProductsByRelatedId($item->product_id);

		if (empty($productIds))
		{
			return true;
		}

		$hashedKey       = RedshopbHelperSync::generateHashKey($item, 'object');
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

		$fieldName = $this->getValueType($row['field_id']);

		if (!$fieldName)
		{
			RedshopbHelperSync::addMessage(Text::sprintf('PLG_RB_SYNC_WEBSERVICE_ERROR_TYPE_NOT_EXISTS', '', $remoteId), 'warning');

			return false;
		}

		$row['state']    = (int) $item->state;
		$row['params']   = (string) $item->params;
		$row[$fieldName] = $item->value;

		if ($fieldName == 'field_value')
		{
			$row[$fieldName] = (int) $this->findSyncedId('erp.webservice.field_values', $item->product_field_value_id);

			if (!$row[$fieldName])
			{
				RedshopbHelperSync::addMessage(
					Text::sprintf('PLG_RB_SYNC_WEBSERVICE_ERROR_FIELD_VALUE_NOT_EXISTS', $item->product_field_value_id),
					'warning'
				);

				return false;
			}
		}

		foreach ($productIds as $productId)
		{
			// If product does not exist anymore then we skip it
			if (!RedshopbEntityProduct::getInstance((int) $productId)->isValid())
			{
				continue;
			}

			$table = RTable::getInstance($this->tableClassName, 'RedshopbTable')
				->setOption('forceWebserviceUpdate', true)
				->setOption('lockingMethod', 'Sync');
			/** @var object $itemData */
			$itemData    = $this->findSyncedId($this->syncName, $remoteId, $productId, true, $table);
			$isNew       = true;
			$unSerialize = array();

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

			// Load rows with existing data on the field that is trying to fill, to avoid duplicate fields
			if ($isNew)
			{
				$table->load(
					array(
						'item_id'  => $productId,
						'field_id' => $row['field_id']
					)
				);
			}

			$fieldRow            = $row;
			$fieldRow['item_id'] = $productId;

			if (!$table->save($fieldRow))
			{
				RedshopbHelperSync::addMessage($table->getError(), 'warning');
				$this->skipItemUpdate($itemData);
				$hashedKey = null;

				continue;
			}

			$serialize = serialize($unSerialize);

			// Save this item ID to synced table
			$this->recordSyncedId(
				$this->syncName, $remoteId, $table->get('id'), $productId, $isNew,
				0, $serialize, false, '', $table, 1, $hashedKey
			);

			$this->storeOtherIds($item, $table);
		}

		return true;
	}

	/**
	 * Get value type
	 *
	 * @param   int  $fieldId  Field id
	 *
	 * @return  mixed
	 */
	public function getValueType($fieldId)
	{
		static $fields = array();

		if (!array_key_exists($fieldId, $fields))
		{
			$db               = Factory::getDbo();
			$query            = $db->getQuery(true)
				->select('t.value_type')
				->from($db->qn('#__redshopb_type', 't'))
				->leftJoin($db->qn('#__redshopb_field', 'f') . ' ON t.id = f.type_id')
				->where('f.id = ' . (int) $fieldId);
			$fields[$fieldId] = $db->setQuery($query, 0, 1)
				->loadResult();
		}

		return $fields[$fieldId];
	}
}
