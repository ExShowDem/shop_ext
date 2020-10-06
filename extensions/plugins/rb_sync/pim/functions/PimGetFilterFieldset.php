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
 * @subpackage  PIM
 * @since       1.0
 */
class PimGetFilterFieldset extends PimFunctionBase
{
	/**
	 * Name in the Sync Table
	 *
	 * @var  string
	 */
	public $syncName = 'erp.pim.filterFieldset';

	/**
	 * @var string
	 */
	public $cronName = 'GetFilterFieldset';

	/**
	 * @var string
	 */
	public $tableClassName = 'Filter_Fieldset';

	/**
	 * @var string
	 */
	public $settingsFile = 'Fieldset.xml';

	/**
	 * @var string
	 */
	public $nameFieldWithData = 'FieldSet';

	/**
	 * @var null
	 */
	public $fullXmlObject = null;

	/**
	 * Read and store the data.
	 *
	 * @param   SimpleXMLElement  $obj       XML element
	 * @param   Table             $table     Table object
	 * @param   string            $parentId  Parent id
	 *
	 * @return  boolean
	 *
	 * @throws  Exception
	 */
	public function readXmlRecursive($obj, &$table, $parentId = '')
	{
		$db = Factory::getDbo();

		// We use Key instead of $obj->Id because products are connected with string Key
		$filterAttributes = $obj->attributes();
		$remoteId         = trim((string) $filterAttributes->Name);

		if (isset($this->executed[$remoteId . '_'])
			|| (strlen($remoteId) > 1 && substr($remoteId, 0, 2) === 'CN'))
		{
			return false;
		}

		$row             = array();
		$isNew           = true;
		$filterFieldIds  = array();
		$filterNoShowIds = array();
		$itemData        = $this->findSyncedId($this->syncName, $remoteId, '', true, $table);
		$hashedKey       = RedshopbHelperSync::generateHashKey($obj, 'xml');

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
				$this->deleteSyncedId($this->syncName, $remoteId);
			}
		}

		$row['state'] = 1;
		$row['name']  = $remoteId;

		// Set Filter Fields
		if (isset($obj->Field))
		{
			foreach ($obj->Field as $fieldRow)
			{
				$filterFieldId = $this->getSyncedFieldId($fieldRow);

				$fieldSync          = $this->findSyncedLocalId('erp.pim.field', $filterFieldId, true);
				$fieldSyncSerialize = $fieldSync ? RedshopbHelperSync::mbUnserialize($fieldSync->serialize) : false;

				if ($fieldSyncSerialize && $fieldSyncSerialize['showInFilter'] == 0)
				{
					$filterNoShowIds[] = $filterFieldId;
					continue;
				}

				if ($filterFieldId)
				{
					$filterFieldIds[] = $filterFieldId;
				}
				else
				{
					// We will not update Hash key since this item needs to sync again
					$hashedKey = null;
					RedshopbHelperSync::addMessage(Text::sprintf('PLG_RB_SYNC_PIM_FILTER_FIELD_NOT_FOUND', (string) $fieldRow), 'warning');
				}
			}

			$row['fields'] = $filterFieldIds;
		}

		if (!$table->save($row))
		{
			RedshopbHelperSync::addMessage($table->getError(), 'warning');

			return false;
		}

		if (!empty($filterNoShowIds) && !$isNew)
		{
			$this->removeExtraFields($itemData->local_id, $filterNoShowIds);
		}

		// Save this item ID to synced table
		$this->recordSyncedId(
			$this->syncName, $remoteId, $table->get('id'), '', $isNew, 0,
			'', false, '', $table, 1, $hashedKey
		);

		return true;
	}

	/**
	 * Delete no-show fields that may have been saved to the filter fieldset
	 *
	 * @param   integer  $fieldsetId       Id of the filter fieldset
	 * @param   array    $filterNoShowIds  Array of field Ids to remove
	 *
	 * @return  void
	 *
	 * @since   1.14.0
	 */
	public function removeExtraFields($fieldsetId, $filterNoShowIds)
	{
		$db    = Factory::getDbo();
		$table = RTable::getInstance('Filter_Fieldset_Xref', 'RedshopbTable')
			->setOption('lockingMethod', 'Sync');

		$query = $db->getQuery(true)
			->select('id')
			->from($db->qn('#__redshopb_filter_fieldset_xref'))
			->where($db->qn('fieldset_id') . ' = ' . $db->q($fieldsetId))
			->where($db->qn('field_id') . ' IN (' . implode(', ', $filterNoShowIds) . ') ');
		$db->setQuery($query);
		$fieldsToRemove = $db->loadObjectList();

		foreach ($fieldsToRemove as $xrefId)
		{
			$table->delete($xrefId->id);
		}
	}
}
