<?php
/**
 * @package     Aesir.E-Commerce.Libraries
 * @subpackage  Helper
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

/**
 * Field Helper
 *
 * @package     Aesir.E-Commerce
 * @subpackage  Helper
 * @since       1.0
 */
final class RedshopbHelperField
{
	/**
	 * List of fields from Redshopb table grouped by scope
	 *
	 * @var  array
	 */
	public static $fields;

	/**
	 * List of fields values from Redshopb table
	 *
	 * @var  array
	 */
	public static $fieldValues = array();

	/**
	 * List of types from Redshopb table
	 *
	 * @var  array
	 */
	public static $types;

	/**
	 * List of field groups from Redshopb table grouped by scope
	 *
	 * @var  array
	 */
	public static $fieldGroups;

	/**
	 * All field
	 */
	const FIELD_ALL = 0;

	/**
	 * Global fields only
	 */
	const FIELD_GLOBAL_ONLY = 1;

	/**
	 * Local fields only
	 */
	const FIELD_LOCAL_ONLY = 2;

	/**
	 * Gets fields for a specific scope
	 *
	 * @param   string  $scope         Only load specific scope
	 * @param   bool    $requiredOnly  Only load required fields.
	 *
	 * @return  array
	 */
	public static function getFields($scope = '', $requiredOnly = false)
	{
		if (!self::$fields)
		{
			$db = Factory::getDbo();

			// Get list executed in previous sync items
			$query = $db->getQuery(true)
				->select('f.*')
				->select('t.value_type, t.field_name, t.name as field_type_name, t.alias as type_alias, t2.alias AS filter_type_alias')
				->select('t2.field_name as filter_type_field_name, t2.name as filter_type_name')
				->select('COUNT(cxref.category_id) AS local')
				->from($db->qn('#__redshopb_field', 'f'))
				->leftJoin($db->qn('#__redshopb_type', 't') . ' ON t.id = f.type_id')
				->leftJoin($db->qn('#__redshopb_type', 't2') . ' ON t2.id = f.filter_type_id')
				->leftJoin($db->qn('#__redshopb_category_field_xref', 'cxref') . ' ON cxref.field_id = f.id')
				->group('f.id')
				->order($db->qn('f.ordering') . ' ASC')
				->order($db->qn('f.title') . ' ASC');

			$db->setQuery($query);
			$fields = $db->loadObjectList('id');

			self::$fields = array();

			if (!empty($fields))
			{
				$fieldIds = array_keys($fields);
				$query->clear()
					->select('*')
					->from('#__redshopb_category_field_xref')
					->where('field_id IN (' . implode(',', $fieldIds) . ')');

				$categoriesXref = $db->setQuery($query)
					->loadObjectList();

				if (!empty($categoriesXref))
				{
					foreach ($categoriesXref as $categoryXref)
					{
						if (!isset($fields[$categoryXref->field_id]->categories))
						{
							$fields[$categoryXref->field_id]->categories = array();
						}

						$fields[$categoryXref->field_id]->categories[] = $categoryXref->category_id;
					}
				}

				foreach ($fields as $field)
				{
					if (!isset($field->categories))
					{
						$field->categories = array();
					}

					self::$fields[$field->scope][$field->id] = $field;
				}
			}
		}

		if (!empty($scope) && !empty(self::$fields[$scope]))
		{
			if ($requiredOnly)
			{
				$tmpArray = array();

				foreach (self::$fields[$scope] as $id => $tmp)
				{
					if ($tmp->required)
					{
						$tmpArray[$id] = $tmp;
					}
				}

				return array($scope => $tmpArray);
			}

			return array($scope => self::$fields[$scope]);
		}

		if (empty($scope))
		{
			if ($requiredOnly)
			{
				$tmpArray = array();

				foreach (self::$fields as $fieldScope => $tmps)
				{
					if (empty($tmpArray[$fieldScope]))
					{
						$tmpArray[$fieldScope] = array();
					}

					foreach ($tmps as $id => $tmp)
					{
						if ($tmp->required)
						{
							$tmpArray[$fieldScope][$id] = $tmp;
						}
					}
				}

				return array($scope => $tmpArray);
			}

			return self::$fields;
		}

		return array();
	}

	/**
	 * Gets fields for a specific scope
	 *
	 * @param   string  $name   Name of the field
	 * @param   string  $scope  Only load specific scope
	 *
	 * @return object|false
	 */
	public static function getFieldByName($name, $scope = '')
	{
		$fields = self::getFields($scope);

		if ($scope && isset($fields[$scope]))
		{
			foreach ($fields[$scope] as $field)
			{
				if (strcasecmp($field->name, $name) == 0)
				{
					return $field;
				}
			}

			return false;
		}

		foreach ($fields as $scopeFields)
		{
			foreach ($scopeFields as $field)
			{
				if (strcasecmp($field->name, $name) == 0)
				{
					return $field;
				}
			}
		}

		return false;
	}

	/**
	 * Gets fields for a specific scope
	 *
	 * @param   string  $alias  Alias of the field
	 * @param   string  $scope  Only load specific scope
	 *
	 * @return object|false
	 */
	public static function getFieldByAlias($alias, $scope = '')
	{
		$fields = self::getFields($scope);

		if ($scope && isset($fields[$scope]))
		{
			foreach ($fields[$scope] as $field)
			{
				if (strcasecmp($field->alias, $alias) == 0)
				{
					return $field;
				}
			}

			return false;
		}

		foreach ($fields as $scopeFields)
		{
			foreach ($scopeFields as $field)
			{
				if (strcasecmp($field->alias, $alias) == 0)
				{
					return $field;
				}
			}
		}

		return false;
	}

	/**
	 * Gets fields for a specific scope
	 *
	 * @param   int     $id     Id of the field
	 * @param   string  $scope  Only load specific scope
	 *
	 * @return object|false
	 */
	public static function getFieldById($id, $scope = '')
	{
		$fields = self::getFields($scope);

		if ($scope)
		{
			if (isset($fields[$scope][$id]))
			{
				return $fields[$scope][$id];
			}

			return false;
		}

		foreach ($fields as $scopeFields)
		{
			foreach ($scopeFields as $field)
			{
				if ($field->id == $id)
				{
					return $field;
				}
			}
		}

		return false;
	}

	/**
	 * Gets field values
	 *
	 * @param   int      $fieldId             Id of the field
	 * @param   array    $ids                 Array Ids of product
	 * @param   boolean  $showCount           Show count of result belong to filter values or not
	 * @param   int      $fieldDataId         In case "fieldId" use Parent Field for Filter Data.
	 * @param   bool     $skipCurrentSection  Skip current section
	 *
	 * @return  array
	 */
	public static function getFieldValues($fieldId, $ids = array(), $showCount = true, $fieldDataId = 0, $skipCurrentSection = false)
	{
		$fieldId = (int) $fieldId;
		$index   = $fieldId;

		if (!$index)
		{
			return array();
		}

		$fieldDataId = (int) $fieldDataId;
		$fieldDataId = (!$fieldDataId) ? $fieldId : $fieldDataId;

		if (!empty($ids) && is_array($ids))
		{
			$index = $fieldId . implode(':', $ids);
		}

		if (isset(self::$fieldValues[$index]))
		{
			return self::$fieldValues[$index];
		}

		$db = Factory::getDbo();

		$query = $db->getQuery(true)
			->select($db->qn('v.id', 'identifier'))
			->select($db->qn('v.value', 'data'))
			->select($db->qn('v.name'))
			->select($db->qn('v.default'))
			->from($db->qn('#__redshopb_field_value', 'v'))
			->where($db->qn('v.field_id') . ' = ' . $fieldId)
			->order($db->qn('v.ordering') . ' ASC')
			->order($db->qn('data') . ' ASC')
			->group($db->qn('identifier'));

		if ((!empty($ids) && is_array($ids)) || $showCount)
		{
			$query->leftJoin(
				$db->qn('#__redshopb_field_data', 'd')
				. ' ON ' . $db->qn('d.field_id') . ' = ' . $fieldDataId
				. ' AND ' . $db->qn('v.id') . ' = ' . $db->qn('d.field_value')
			);

			if ($showCount)
			{
				$skipSection = '';

				if ($skipCurrentSection)
				{
					$skipSection = $fieldId;
				}

				$query->select('COUNT(productIdsQuery.product_id) AS count');
				$productSearch      = new RedshopbDatabaseProductsearch;
				$productSearchQuery = $productSearch->getFilteredProductQuery(null, false, $skipSection);
				$query->leftJoin('(' . $productSearchQuery . ') AS productIdsQuery ON productIdsQuery.product_id = d.item_id');

				$query->select('COUNT(totalProductIds.product_id) AS totalCount');

				$totalQuery = $productSearch->getBaseSearchQuery();
				$totalQuery->clear('select');
				$totalQuery->select('p.id AS product_id');

				if ($productSearch->hasTerm())
				{
					$totalQuery->where($db->qn('p.id') . ' IN (' . $productSearch->getStoredSearch() . ')');
				}

				$query->leftJoin('(' . $totalQuery . ') AS totalProductIds ON totalProductIds.product_id = d.item_id');
			}

			if (!empty($ids) && is_array($ids))
			{
				$ids = ArrayHelper::toInteger($ids);

				$query->where($db->qn('d.item_id') . ' IN (' . implode(',', $ids) . ')');
			}
		}

		$db->setQuery($query);

		self::$fieldValues[$index] = $db->loadObjectList();

		return self::$fieldValues[$index];
	}

	/**
	 * Gets types
	 *
	 * @return array
	 */
	public static function getTypes()
	{
		if (!self::$types)
		{
			$db = Factory::getDbo();

			// Get list executed in previous sync items
			$query = $db->getQuery(true)
				->select('*')
				->from($db->qn('#__redshopb_type'));

			$db->setQuery($query);
			$types = $db->loadObjectList();

			if ($types)
			{
				self::$types = array();

				foreach ($types as $type)
				{
					self::$types[$type->name] = $type;
				}
			}
		}

		return self::$types;
	}

	/**
	 * Gets type object for a given Type name
	 *
	 * @param   string  $name  Name of the type
	 *
	 * @return object|false
	 */
	public static function getType($name)
	{
		$types = self::getTypes();

		if (isset($types[$name]))
		{
			return $types[$name];
		}

		return false;
	}

	/**
	 * Gets type
	 *
	 * @param   int  $id  Id of the type
	 *
	 * @return object|false
	 */
	public static function getTypeById($id)
	{
		$types = self::getTypes();

		foreach ($types as $type)
		{
			if ($type->id == $id)
			{
				return $type;
			}
		}

		return false;
	}

	/**
	 * Generic load the fields related to this scope
	 *
	 * @param   string  $scope        Fields scope
	 * @param   int     $itemId       ID of item
	 * @param   int     $subItemId    ID of sub item
	 * @param   bool    $getFullInfo  Getting full field info
	 *
	 * @return  mixed   Object list on success, false otherwise
	 */
	public static function loadScopeFieldData($scope, $itemId, $subItemId = 0, $getFullInfo = false)
	{
		$funcArgs                      = get_defined_vars();
		$objectFullArgs                = $funcArgs;
		$objectFullArgs['getFullInfo'] = true;
		$fullInfoKey                   = serialize($objectFullArgs);
		$key                           = serialize($funcArgs);

		static $fields = array();

		if (array_key_exists($key, $fields))
		{
			return $fields[$key];
		}

		if (!array_key_exists($fullInfoKey, $fields))
		{
			$db    = Factory::getDbo();
			$query = $db->getQuery(true)
				->select('fd.id, fd.field_id, fd.params as field_data_params')
				->select('f.multiple_values, f.scope, f.name, f.title, f.alias as field_alias, f.description, f.unit_measure_id')
				->select('f.decimal_separator AS field_decimal_separator, f.thousand_separator AS field_thousand_separator')
				->select('f.decimal_position AS field_decimal_position')
				->select('um.name AS unit_measure_name, um.decimal_separator AS unit_measure_decimal_separator')
				->select('um.decimal_position AS unite_measure_decimal_position, um.thousand_separator AS unite_measure_thousand_separator')
				->select('f.prefix AS prefix, f.suffix AS suffix')
				->select('t.name as type_name, t.alias as type_alias, t.value_type, t.field_name as type_field_name, t.multiple AS type_multiple')
				->select('fv.id as field_value_id, fv.params as field_value_params')
				->select(
					'CASE t.value_type WHEN ' . $db->q('string_value') . ' THEN fd.string_value '
					. 'WHEN ' . $db->q('float_value')

					// Remove trailing zeros in decimal value
					. ' THEN TRIM(TRAILING ' . $db->q('.') . ' FROM TRIM(TRAILING ' . $db->q('0') . ' from fd.float_value)) '
					. 'WHEN ' . $db->q('int_value') . ' THEN fd.int_value '
					. 'WHEN ' . $db->q('text_value') . ' THEN fd.text_value '
					. 'WHEN ' . $db->q('field_value') . ' THEN fv.value '
					. 'ELSE ' . $db->q('') . ' END AS value'
				)
				->from($db->qn('#__redshopb_field_data', 'fd'))
				->leftJoin($db->qn('#__redshopb_field', 'f') . ' ON f.id = fd.field_id')
				->leftJoin($db->qn('#__redshopb_type', 't') . ' ON t.id = f.type_id')
				->leftJoin($db->qn('#__redshopb_field_value', 'fv') . ' ON fv.id = fd.field_value')
				->leftJoin($db->qn('#__redshopb_unit_measure', 'um') . ' ON um.id = f.unit_measure_id')
				->where($db->qn('f.scope') . ' = ' . $db->q($scope))
				->where('f.state = 1')
				->where('fd.state = 1')
				->where($db->qn('fd.item_id') . ' = ' . (int) $itemId)
				->order('f.ordering asc');

			if (!empty($subItemId))
			{
				$query->where($db->qn('fd.subitem_id') . ' = ' . (int) $subItemId);
			}

			$fields[$fullInfoKey] = $db->setQuery($query)->loadObjectList();
		}

		if ($getFullInfo)
		{
			return $fields[$fullInfoKey];
		}

		$fields[$key] = array();

		foreach ($fields[$fullInfoKey] as $field)
		{
			$fields[$key][$field->name] = $field->value;
		}

		return $fields[$key];
	}

	/**
	 * Generic store for scope fields
	 *
	 * @param   string  $scope                Fields scope
	 * @param   int     $itemId               ID of item
	 * @param   int     $subItemId            ID of sub item
	 * @param   array   $fields               Array of fields to save
	 * @param   bool    $deleteMissingFields  Disable missing fields not sent to the item / scope
	 * @param   string  $lockMethod           Locking method
	 *
	 * @return  boolean  True on success, false otherwise
	 * @throws Exception
	 */
	public static function storeScopeFieldData($scope, $itemId, $subItemId = 0, $fields = array(), $deleteMissingFields = false, $lockMethod = 'User')
	{
		if (!isset($fields) || empty($fields))
		{
			return true;
		}

		$db       = Factory::getDbo();
		$files    = Factory::getApplication()->input->files->get('jform', array(), 'Array');
		$filesRaw = null;

		// Get current Product Fields
		$query = $db->getQuery(true)
			->select('fd.*, f.name')
			->from($db->qn('#__redshopb_field_data', 'fd'))
			->leftJoin($db->qn('#__redshopb_field', 'f') . ' ON ' . $db->qn('f.id') . ' = ' . $db->qn('fd.field_id'))
			->where('f.scope = ' . $db->q($scope))
			->where($db->qn('fd.item_id') . ' = ' . $db->q($itemId));

		if (!empty($subItemId))
		{
			$query->where($db->qn('fd.subitem_id') . ' = ' . (int) $subItemId);
		}

		$currentProductFields = $db->setQuery($query)->loadObjectList();

		// Get scope fields with value type definition
		$scopeFields = self::getFields($scope);
		$scopeFields = $scopeFields[$scope];

		$fieldsContainer  = array();
		$removeFieldsCont = array();

		if (is_array($fields) && count($fields) > 0)
		{
			foreach ($fields as $fieldName => $fieldValue)
			{
				// When sending in form we replace prefix
				$fieldId = str_replace('scope_field_', '', $fieldName);

				if (!empty($scopeFields[$fieldId]))
				{
					$fieldItem = array(
						'id'       => 0,
						'item_id'  => $itemId,
						'field_id' => $fieldId,
						'state'    => '1'
					);

					// It received field is array then we are dealing with multiple values or values with parameters
					if (is_array($fieldValue))
					{
						// If this is single field data we change it to array so we can process it
						if (isset($fieldValue['params']))
						{
							$fieldValue = array($fieldValue);
						}

						// We need to save each of this fields to a separate field data row
						foreach ($fieldValue as $fieldDataId => $multipleFieldData)
						{
							$newField = $fieldItem;

							// We will check if this field have param key which differentiate it from the rest of the fields
							if (!isset($multipleFieldData['params']))
							{
								$newField[$scopeFields[$fieldId]->value_type] = $multipleFieldData;

								$newField['id'] = self::checkForExistingFieldItem(
									$currentProductFields, $scopeFields, $newField, true
								);
							}
							else
							{
								$newField[$scopeFields[$fieldId]->value_type] = $multipleFieldData['name'];
								$newField['params']                           = $multipleFieldData['params'];
								$newField['state']                            = $multipleFieldData['state'];
								$newField['id']                               = is_numeric($fieldDataId) ? (int) $fieldDataId : 0;

								// We need to get media files with files type from raw or they will be filtered out
								$scopeFields[$fieldId]->type_alias = 'files';

								if ($scopeFields[$fieldId]->type_alias)
								{
									if (is_null($filesRaw))
									{
										$filesRaw = Factory::getApplication()->input->files->get('jform', array(), 'raw');

										if (!is_array($filesRaw))
										{
											$filesRaw = (array) $filesRaw;
										}
									}

									$filesConteiner = $filesRaw;
								}
								else
								{
									$filesConteiner = $files;
								}

								// File is added from field form in separate input field called internal
								$newField['file'] = isset($filesConteiner['extrafields'][$fieldName][$fieldDataId]['file'])
								&& !empty($filesConteiner['extrafields'][$fieldName][$fieldDataId]['file']['name'])
									? $filesConteiner['extrafields'][$fieldName][$fieldDataId]['file']
									: null;

								// If there are just upload file without any input information. Force use filename as name
								if (!$newField['id'] && !is_null($newField['file']) && $newField[$scopeFields[$fieldId]->value_type] == '')
								{
									$newField[$scopeFields[$fieldId]->value_type] = JFile::stripExt($newField['file']['name']);
								}

								// We reduce number of fields that are not set at all in the database by removing them
								if ($newField['id'] && $newField[$scopeFields[$fieldId]->value_type] == '')
								{
									$removeFieldsCont[] = $newField['id'];

									continue;
								}
								elseif ($newField[$scopeFields[$fieldId]->value_type] == '')
								{
									continue;
								}
							}

							$fieldsContainer[] = $newField;
						}

						continue;
					}

					if (!isset($fieldItem[$scopeFields[$fieldId]->value_type]))
					{
						$fieldItem[$scopeFields[$fieldId]->value_type] = $fieldValue;
					}

					// This is hidden Field data identifier from the form
					if (!$fieldItem['id'] && isset($fields[$fieldName . '_field_data_id']))
					{
						$fieldItem['id'] = (int) $fields[$fieldName . '_field_data_id'];
					}
					// If data is not sent from form we must find it manually
					elseif (!$fieldItem['id'] && $currentProductFields)
					{
						$fieldItem['id'] = self::checkForExistingFieldItem($currentProductFields, $scopeFields, $fieldItem, false);
					}

					// We reduce number of fields that are not set at all in the database by removing them
					if ($fieldItem['id'] && $fieldItem[$scopeFields[$fieldId]->value_type] == '')
					{
						$removeFieldsCont[] = $fieldItem['id'];

						continue;
					}
					elseif ($fieldItem[$scopeFields[$fieldId]->value_type] == '')
					{
						continue;
					}
					elseif (in_array($scopeFields[$fieldId]->value_type, array('float_value', 'int_value'))
						&& !is_numeric($fieldItem[$scopeFields[$fieldId]->value_type]))
					{
						$fieldItem[$scopeFields[$fieldId]->value_type] = str_replace(
							array(',', ' '),
							array('.', ''),
							$fieldItem[$scopeFields[$fieldId]->value_type]
						);

						if (!is_numeric($fieldItem[$scopeFields[$fieldId]->value_type]))
						{
							continue;
						}
					}

					if (!empty($fieldItem))
					{
						$fieldsContainer[] = $fieldItem;
					}
				}
			}
		}

		/** @var RedshopbTableField_Data $xrefTable */
		$xrefTable = RedshopbTable::getAdminInstance('Field_Data')->setOption('lockingMethod', $lockMethod);

		// Store the new items
		foreach ($fieldsContainer as $field)
		{
			$xrefTable->reset();
			$xrefTable->id = null;

			$updateNulls = isset($field['item_id']) ? true : false;

			// If we are only storing the state then we do not allow nulls
			$xrefTable->setOption('storeNulls', $updateNulls);

			if (!$xrefTable->save($field))
			{
				return false;
			}
		}

		if ($deleteMissingFields && !empty($currentProductFields))
		{
			foreach ($currentProductFields as $currentProductField)
			{
				$itemFound      = false;
				$valueType      = $scopeFields[$currentProductField->field_id]->value_type;
				$type           = RedshopbEntityType::load($scopeFields[$currentProductField->field_id]->type_id);
				$multipleValues = (boolean) $type->get('multiple');
				$multipleValues = ($multipleValues && $scopeFields[$currentProductField->field_id]->multiple_values == '1') ? true : false;

				foreach ($fieldsContainer as $field)
				{
					if ($currentProductField->field_id == $field['field_id'])
					{
						if ($multipleValues)
						{
							if ($currentProductField->{$valueType} == $field[$valueType])
							{
								$itemFound = true;
								break;
							}
						}
						elseif (isset($field['id']) && $currentProductField->id == $field['id'])
						{
							$itemFound = true;
							break;
						}
					}
				}

				if (!$itemFound)
				{
					$removeFieldsCont[] = $currentProductField->id;
				}
			}
		}

		// Remove empty field data rows
		foreach ($removeFieldsCont as $field)
		{
			$xrefTable->reset();

			if (!$xrefTable->delete($field))
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * Method check if field data is already saved for the scope
	 *
	 * @param   array   $currentProductFields  List of field data values
	 * @param   array   $scopeFields           List of fields for the scope
	 * @param   string  $newFieldValue         Field to check if exists
	 * @param   bool    $checkValue            Should check also be preformed on values
	 *
	 * @return  integer  It will return field data ID or 0
	 */
	public static function checkForExistingFieldItem($currentProductFields, $scopeFields, $newFieldValue, $checkValue = false)
	{
		if (!empty($currentProductFields))
		{
			$valueType = $scopeFields[$newFieldValue['field_id']]->value_type;

			foreach ($currentProductFields as $key => $currentProductField)
			{
				if ($currentProductField->field_id == $newFieldValue['field_id'])
				{
					if ($checkValue)
					{
						if ($currentProductField->{$valueType} != $newFieldValue[$valueType])
						{
							continue;
						}
					}

					return $currentProductField->id;
				}
			}
		}

		return 0;
	}

	/**
	 * Method to set clean default values of a certain field, when some value has been set as default
	 *
	 * @param   int  $fieldId  Field ID
	 * @param   int  $valueId  Value ID
	 *
	 * @return  mixed
	 */
	public static function cleanDefaultFieldValue($fieldId, $valueId)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);

		$query->update('#__redshopb_field_value')
			->set($db->qn('default') . '=' . $db->q('0'))
			->where($db->qn('field_id') . '=' . (int) $fieldId)
			->where($db->qn('id') . '!=' . (int) $valueId);
		$db->setQuery($query);

		return $db->execute();
	}

	/**
	 * Method for get fields for B2C Users.
	 *
	 * @return  array  List of fields
	 *
	 * @since  1.9.16
	 */
	public static function getB2CUserFields()
	{
		$fields = self::getFields('user');

		if (empty($fields))
		{
			return array();
		}

		foreach ($fields['user'] as $id => $field)
		{
			if (!$field->b2c && !$field->required)
			{
				unset($fields[$id]);
			}
		}

		return $fields;
	}

	/**
	 * Method to get all field groups
	 *
	 * @return  array  Array of all field groups
	 *
	 * @since  1.12.33
	 */
	public static function getFieldGroups()
	{
		if (!self::$fieldGroups)
		{
			$db = Factory::getDbo();

			// Get list executed in previous sync items
			$query = $db->getQuery(true)
				->select('*')
				->from($db->qn('#__redshopb_field_group'))
				->order($db->qn('ordering') . ' ASC')
				->order($db->qn('name') . ' ASC');

			$db->setQuery($query);

			$fieldGroups  = $db->loadObjectList();
			self::$fields = array();

			foreach ($fieldGroups as $key => $fieldGroup)
			{
				self::$fieldGroups[$fieldGroup->id] = $fieldGroup;
			}
		}

		return self::$fieldGroups;
	}

	/**
	 * Method to sort field data into groups.
	 *
	 * @param   array  $fieldsData  Array of field data objects
	 * @param   int    $mode        Mode for get fields data.
	 * @param   int    $productId   Product ID just use when mode is FIELD_LOCAL_ONLY.
	 *
	 * @return  array               Field data sorted by groups
	 *
	 * @since  1.12.33
	 */
	public static function getFieldDataFieldGroups($fieldsData, $mode = self::FIELD_ALL, $productId = 0)
	{
		$fieldDataGrouped = array();
		$fieldGroups      = self::getFieldGroups();

		// Key dedicated to un-grouped entries
		$fieldDataGrouped[0] = array();

		if (isset($fieldGroups) && count($fieldGroups))
		{
			foreach ($fieldGroups as $fieldGroup)
			{
				$fieldDataGrouped[$fieldGroup->name] = null;
			}
		}

		$localFields = array();

		if ($mode == self::FIELD_LOCAL_ONLY && $productId)
		{
			$categories = RedshopbEntityProduct::getInstance($productId)->getCategories()->ids();

			if (!empty($categories))
			{
				$db    = Factory::getDbo();
				$query = $db->getQuery(true)
					->select($db->qn('field_id'))
					->from($db->qn('#__redshopb_category_field_xref'))
					->where($db->qn('category_id') . ' IN (' . implode(',', $categories) . ')');

				$localFields = $db->setQuery($query)->loadColumn();
			}
		}

		foreach ($fieldsData as $fieldData)
		{
			$field = self::getFieldById($fieldData->field_id);

			if (($mode == self::FIELD_GLOBAL_ONLY && $field->global != 1)
				|| ($mode == self::FIELD_LOCAL_ONLY && ($field->global != 0 || !in_array($field->id, $localFields))))
			{
				continue;
			}

			$fieldGroupName = ($field->field_group_id ? $fieldGroups[$field->field_group_id]->name : '');

			if (!$fieldGroupName)
			{
				$fieldDataGrouped[0][] = $fieldData;
			}
			else
			{
				$fieldDataGrouped[$fieldGroupName][] = $fieldData;
			}
		}

		$fieldDataGrouped = array_filter($fieldDataGrouped);

		return $fieldDataGrouped;
	}

	/**
	 * Returns modified float number depend on units of measure
	 *
	 * @param   stdClass  $fieldData  Id of the field value
	 *
	 * @return  string
	 * @since   1.13.2
	 */
	public static function getFloatFieldValue(&$fieldData)
	{
		// Range treatment
		if ($fieldData->type_alias == 'range')
		{
			if (preg_match('/^([0-9]*)(\.[0-9]+)?\-([0-9]*)(\.[0-9]+)?$/', $fieldData->value, $matches))
			{
				$value1st = $matches[1] . (isset($matches[2]) ? $matches[2] : '');
				$value2st = $matches[3] . (isset($matches[4]) ? $matches[4] : '');

				$value1 = (empty($value1st) ? '' : (double) ($value1st));
				$value2 = (empty($value2st) ? '' : (double) ($value2st));

				// Set value back (not relying on the getFloatFieldSingleValue when it comes to range field)
				$fieldData->value = $value1 . '-' . $value2;

				$value2fmt = '';

				if (!empty($value2st))
				{
					$value2fmt = static::getFloatFieldSingleValue($value2, $fieldData);
				}

				if (!empty($value1st) && !empty($value2st))
				{
					$value1fmt = static::getFloatFieldSingleValue($value1, $fieldData, false);

					return $value1fmt . '-' . $value2fmt;
				}

				if (!empty($value1))
				{
					return Text::sprintf('COM_REDSHOPB_FIELD_VALUE_RANGE_ANDUP', static::getFloatFieldSingleValue($value1, $fieldData, false));
				}

				return Text::sprintf('COM_REDSHOPB_FIELD_VALUE_RANGE_UPTO', $value2fmt);
			}

			return '';
		}

		return static::getFloatFieldSingleValue($fieldData->value, $fieldData);
	}

	/**
	 * Returns modified (single) float number depend on units of measure
	 *
	 * @param   string    $value      Single value to be formatted
	 * @param   stdClass  $fieldData  Id of the field value
	 * @param   boolean   $append     Append prefix/suffix and uom
	 *
	 * @return  string
	 * @since   1.13.2
	 */
	public static function getFloatFieldSingleValue(&$value, $fieldData, $append = true)
	{
		if ($fieldData->unit_measure_id == null
			&& $fieldData->field_decimal_separator == ''
			&& $fieldData->field_thousand_separator == ''
			&& $fieldData->field_decimal_position == '0')
		{
			return ($append ? $fieldData->prefix . ' ' : '') . $value . ($append ? ' ' . $fieldData->suffix : '');
		}

		if (is_string($value))
		{
			$value = (double) $value;
		}

		if ($fieldData->unit_measure_id !== null)
		{
			$formattedFloat = number_format($value, $fieldData->unite_measure_decimal_position,
				$fieldData->unit_measure_decimal_separator, $fieldData->unite_measure_thousand_separator
			);

			return ($append ? $fieldData->prefix . ' ' : '') . $formattedFloat . ($append ? ' ' . $fieldData->suffix : '') .
				' ' . ($append ? $fieldData->unit_measure_name : '');
		}

		$formattedFloat = number_format($value, $fieldData->field_decimal_position,
			$fieldData->field_decimal_separator, $fieldData->field_thousand_separator
		);

		return ($append ? $fieldData->prefix . ' ' : '') . $formattedFloat . ($append ? ' ' . $fieldData->suffix : '');
	}
}
