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
 * Get Field Values function.
 *
 * @package     Aesir.E-Commerce.Plugin
 * @subpackage  PIM
 * @since       1.0
 */
class PimGetFieldValues extends PimFunctionBase
{
	/**
	 * Name in the Sync Table
	 *
	 * @var  string
	 */
	public $syncName = 'erp.pim.field.value';

	/**
	 * @var string
	 */
	public $cronName = 'GetFieldValues';

	/**
	 * @var string
	 */
	public $tableClassName = 'Field_Value';

	/**
	 * @var string
	 */
	public $settingsFile = 'Cvl.xml';

	/**
	 * @var string
	 */
	public $nameFieldWithData = 'CVLValue';

	/**
	 * @var integer
	 */
	public $fieldId = 0;

	/**
	 * @var [type]
	 */
	public $parentId;

	/**
	 * Read and store the data.
	 *
	 * @param   SimpleXMLElement  $xml       XML element
	 * @param   Table             $table     Table object
	 * @param   string            $parentId  Parent id
	 *
	 * @return  boolean
	 *
	 * @throws  Exception
	 */
	public function readXmlRecursive($xml, &$table, $parentId = '')
	{
		$db = Factory::getDbo();

		// We use Key instead of $xml->Id because products are connected with string Key
		$remoteId = trim((string) $xml->Key);
		$parentId = $this->parentId;

		if (isset($this->executed[$remoteId . '_' . $parentId]))
		{
			return false;
		}

		$row       = array();
		$isNew     = true;
		$itemData  = $this->findSyncedId($this->syncName, $remoteId, $parentId, true, $table);
		$hashedKey = RedshopbHelperSync::generateHashKey($xml, 'xml');
		$table->setOption('forceOrderingValues', true);

		if (!$this->isHashChanged($itemData, $hashedKey))
		{
			// Hash key is the same so we will continue on the next item
			$this->skipItemUpdate($itemData);

			// Returning false so we do not go to the next step of inserting translations
			return false;
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
				$this->deleteSyncedId($this->syncName, $remoteId, $parentId);
			}
		}

		$row['state']    = 1;
		$row['default']  = 0;
		$row['field_id'] = $this->fieldId;
		$row['name']     = $remoteId;
		$row['value']    = trim((string) $xml->Value);
		$row['ordering'] = (int) trim((string) $xml->Index);

		if (!$table->save($row))
		{
			RedshopbHelperSync::addMessage($table->getError(), 'warning');

			return false;
		}

		// Save this item ID to synced table
		$this->recordSyncedId(
			$this->syncName, $remoteId, $table->get('id'), $parentId, $isNew,
			0, '', false, '', $table, 1, $hashedKey
		);

		return true;
	}

	/**
	 * Read and store the data.
	 *
	 * @param   SimpleXMLElement  $xml       XML element
	 * @param   string            $parentId  Parent id
	 *
	 * @return  boolean
	 */
	public function processData($xml, $parentId = '')
	{
		$processedItems = 0;

		// First we count total items
		foreach ($xml->group as $obj)
		{
			if (!isset($obj->{$this->nameFieldWithData}))
			{
				continue;
			}

			$this->counterTotal += count($obj->{$this->nameFieldWithData});
		}

		foreach ($xml->group as $obj)
		{
			if (!isset($obj->{$this->nameFieldWithData}))
			{
				continue;
			}

			$this->parentId = trim((string) $obj['id']) . '_CVL';
			$fieldId        = $this->getSyncedFieldId((string) $obj['id'], 'product', true);

			// Add a new field from CVL table
			if (!$fieldId)
			{
				$field   = $this->createFieldValueField(
					$this->parentId, 'product', RedshopbHelperField::getType('Dropdown - single')->id, false, '', true
				);
				$fieldId = $field->id;
			}

			if ($fieldId)
			{
				$this->fieldId  = $fieldId;
				$thisGroupItems = count($obj->{$this->nameFieldWithData});

				if ($this->processItemsCompleted > 0 && $this->processItemsCompleted >= $processedItems)
				{
					$processedItems += $thisGroupItems;
				}

				if ($this->processItemsCompleted > 0 && $this->processItemsCompleted >= $processedItems)
				{
					continue;
				}

				$obj = (array) $obj;

				if ($this->processItemsCompleted > 0)
				{
					$obj[$this->nameFieldWithData] = array_slice(
						$obj[$this->nameFieldWithData], $this->processItemsCompleted + $thisGroupItems - $processedItems
					);
				}

				foreach ($obj[$this->nameFieldWithData] as $item)
				{
					if ($this->goToNextPart == true || $this->isExecutionTimeExceeded() || $this->isOverTheStepLimit())
					{
						$this->goToNextPart = true;
						break 2;
					}

					$this->preSyncItem($item, $parentId);
					$this->counter++;
				}
			}
			else
			{
				RedshopbHelperSync::addMessage(Text::sprintf('COM_REDSHOPB_SYNC_FIELD_MISSING', $this->parentId), 'warning');
			}
		}

		return true;
	}
}
