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

require_once __DIR__ . '/base.php';

/**
 * Get Field function.
 *
 * @package     Aesir.E-Commerce.Plugin
 * @subpackage  PIM
 * @since       1.0
 */
class PimGetField extends PimFunctionBase
{
	/**
	 * Name in the Sync Table
	 *
	 * @var  string
	 */
	public $syncName = 'erp.pim.field';

	/**
	 * Names used in the Sync Table. Currently used only for clearing Hashed Keys
	 *
	 * @var  string
	 */
	public $allUsedSyncNames = array('Field' => 'erp.pim.field', 'Field_Value' => 'erp.pim.field.cvl');

	/**
	 * @var string
	 */
	public $cronName = 'GetField';

	/**
	 * @var string
	 */
	public $tableClassName = 'Field';

	/**
	 * @var string
	 */
	public $settingsFile = 'ItemSettings.xml';

	/**
	 * @var string
	 */
	public $nameFieldWithData = 'Field';

	/**
	 * @var array
	 */
	public $translationAssociations = array(
		'title' => 'Name',
		'description' => 'Name'
	);

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
		$fieldAttributes      = $obj->attributes();
		$remoteId             = trim((string) $fieldAttributes->Name);
		$dataType             = (isset($fieldAttributes->DataType)) ? trim((string) $fieldAttributes->DataType) : 'String';
		$this->xmlFieldValues = $fieldAttributes;
		$row                  = array();

		if (isset($this->executed[$remoteId . '_']) || in_array($remoteId, $this->reservedFieldsInProduct))
		{
			// For reserved fields, it stores translations only
			$this->getTranslationValues($row, $fieldAttributes);

			return false;
		}

		$table->setOption('load.type_code', false);
		$isNew      = true;
		$existingId = false;
		$isExisting = false;
		$itemData   = $this->findSyncedId($this->syncName, $remoteId, '', true, $table);
		$hashedKey  = RedshopbHelperSync::generateHashKey($obj, 'xml');

		// We will not check for hashes fields with CVL values
		if (!isset($fieldAttributes->CVL) && !$this->isHashChanged($itemData, $hashedKey))
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

		// If we did not find sync relation, maybe it is one of the default fields so we dont create them again
		if (!$itemData || $isNew)
		{
			$field = RedshopbHelperField::getFieldByName($remoteId, 'product');

			if ($field)
			{
				$existingId = $field->id;
			}

			if ($existingId && $table->load($existingId))
			{
				$isNew      = false;
				$isExisting = true;
				$row['id']  = $existingId;
			}
		}

		$this->getTranslationValues($row, $fieldAttributes);
		$row['scope']              = 'product';
		$row['name']               = $remoteId;
		$serialize['showInFilter'] = 1;

		if ($isNew)
		{
			$row['state']               = 1;
			$row['searchable_frontend'] = 1;
			$row['searchable_backend']  = 1;
			$row['ordering']            = $table->getNextOrder($db->qn('scope') . '=' . $db->q('product'));
		}

		switch ($dataType)
		{
			case 'Double':
			case 'Float':
				$row['type_id'] = RedshopbHelperField::getType('Textbox - float')->id;
				break;
			case 'Boolean':
				$row['type_id'] = RedshopbHelperField::getType('Radio - boolean')->id;
				break;
			case 'Integer':
				$row['type_id'] = RedshopbHelperField::getType('Textbox - int')->id;
				break;
			case 'CVL':
				$row['type_id'] = RedshopbHelperField::getType('Dropdown - single')->id;
				break;
			case 'Date':
				$row['type_id'] = RedshopbHelperField::getType('Date')->id;
				break;

			case 'String':
			case 'LocaleString':
			default:
				$row['type_id'] = RedshopbHelperField::getType('Textbox - string')->id;
				break;
		}

		$fieldValueFieldName  = (isset($fieldAttributes->CVL)) ? trim((string) $fieldAttributes->CVL) . '_CVL' : '';
		$fieldValueFieldTitle = (isset($fieldAttributes->CVL)) ? trim((string) $fieldAttributes->CVL) : '';

		if (!empty($fieldValueFieldName))
		{
			$fieldValueField = $this->getSyncedFieldId($fieldValueFieldTitle, 'product', true, true);

			// Add a new field from CVL table if needed
			if (!$fieldValueField && $fieldValueFieldName != $row['name'])
			{
				$fieldValueField = $this->createFieldValueField(
					$fieldValueFieldName, 'product', RedshopbHelperField::getType('Dropdown - single')->id, true, $fieldValueFieldTitle, true
				);
			}

			if ($fieldValueField)
			{
				$parentCVL   = '';
				$syncedField = $this->findSyncedId($this->syncName . '.cvl', $fieldValueField->name, '', true);

				if ($syncedField)
				{
					$parentCVL = $syncedField->remote_parent_key;
					$fieldNew  = false;
				}
				else
				{
					$fieldNew = true;
				}

				// Mark the existing CVL field as checked (status 0) so it won't get deleted
				$this->recordSyncedId($this->syncName . '.cvl', $fieldValueField->name, $fieldValueField->id, $parentCVL, $fieldNew, 0, '', true);
				$row['field_value_xref_id'] = $fieldValueField->id;
			}
		}
		else
		{
			$row['field_value_xref_id'] = null;
		}

		if (isset($obj->Setting))
		{
			foreach ($obj->Setting as $fieldSetting)
			{
				if (isset($fieldSetting->Key) && (string) $fieldSetting->Key == 'FilterType')
				{
					$filterType = trim((string) $fieldSetting->Value);
					$typeId     = null;

					switch ($filterType)
					{
						case 'Checkbox':
							$typeId = RedshopbHelperField::getType('Checkbox');
							break;
						case 'Radio':
							$typeId = RedshopbHelperField::getType('Radio');
							break;
						case 'Select':
							$typeId = RedshopbHelperField::getType('Dropdown - single');
							break;
						case 'Multiselect':
							$typeId = RedshopbHelperField::getType('Dropdown - multiple');
							break;
						case 'Scale':
							$typeId = RedshopbHelperField::getType('Scale');
							break;
						default:
							$typeId = RedshopbHelperField::getType($filterType);
							break;
					}

					if (!empty($typeId))
					{
						$row['filter_type_id'] = $typeId->id;
					}
				}
				elseif (isset($fieldSetting->Key) && (string) $fieldSetting->Key == 'DataType')
				{
					$fieldScope = trim((string) $fieldSetting->Value);

					if ($fieldScope == 'Global')
					{
						$row['global'] = 1;
					}
				}
				elseif (isset($fieldSetting->Key) && (string) $fieldSetting->Key == 'ShowInFilter')
				{
					$filterShow = trim((string) $fieldSetting->Value);

					if ($filterShow == 'No')
					{
						$serialize['showInFilter'] = 0;
					}
				}
			}
		}

		if (empty($row['filter_type_id']))
		{
			$row['filter_type_id'] = $row['type_id'];
		}

		if (empty($row['filter_type_id']))
		{
			$row['filter_type_id'] = RedshopbHelperField::getType('Textbox - string')->id;
		}

		if (!$table->save($row))
		{
			RedshopbHelperSync::addMessage($table->getError(), 'warning');

			return false;
		}

		if ($fieldValueFieldName == $table->get('name') && $table->get('field_value_xref_id') != $table->get('id'))
		{
			$table->set('field_value_xref_id', $table->get('id'));

			if (!$table->store())
			{
				RedshopbHelperSync::addMessage($table->getError(), 'warning');

				return false;
			}
		}

		// Reset Fields container
		RedshopbHelperField::$fields = null;

		// Save this item ID to synced table
		$this->recordSyncedId(
			$this->syncName,
			$remoteId,
			$table->get('id'),
			'',
			$isExisting ? $isExisting : $isNew,
			$newSyncStatus = 0,
			$serialize     = serialize($serialize),
			$ignoreLocalId = true,
			$newLocalId    = $table->get('id'),
			$table,
			$mainReference = 1,
			$hashedKey
		);

		return true;
	}

	/**
	 * Set all existing rows as executed in sync table
	 *
	 * @param   string  $reference        Reference name
	 * @param   bool    $remoteParentKey  Is one product then bind it to a specific remote parent
	 * @param   bool    $usePartialSet    Use partial sets
	 *
	 * @return void
	 *
	 * @throws Exception
	 */
	public function setSyncRowsAsExecuted($reference, $remoteParentKey = null, $usePartialSet = false)
	{
		parent::setSyncRowsAsExecuted($reference, $remoteParentKey, $usePartialSet);
		parent::setSyncRowsAsExecuted($this->syncName . '.cvl', 'CVL', $usePartialSet);
	}

	/**
	 * Deletes rows both from Sync table and from original table
	 *
	 * @param   string  $reference      Reference name
	 * @param   string  $tableName      Table name
	 * @param   array   $statuses       Array selection statuses
	 * @param   bool    $useTableClass  Use table class for delete items
	 * @param   string  $keyName        Key name in table, where deletes items
	 *
	 * @return void
	 *
	 * @throws Exception
	 */
	public function deleteRowsNotPresentInRemote($reference, $tableName = '', $statuses = array(), $useTableClass = false, $keyName = 'id')
	{
		parent::deleteRowsNotPresentInRemote($reference, $tableName, $statuses, $useTableClass, $keyName);
		parent::deleteRowsNotPresentInRemote($this->syncName . '.cvl', $tableName, $statuses, $useTableClass, $keyName);
	}
}
