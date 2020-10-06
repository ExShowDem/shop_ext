<?php
/**
 * @package     Aesir.E-Commerce.Libraries
 * @subpackage  Helper
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
/**
 * Filter Helper
 *
 * @package     Aesir.E-Commerce
 * @subpackage  Helper
 * @since       1.0
 */
final class RedshopbHelperFilter
{
	/**
	 * List of filter fields from Redshopb table grouped by fieldsets
	 *
	 * @var  array
	 */
	public static $filterFields;

	/**
	 * Filter Fields
	 *
	 * @param   array|string  $filterData  Filter data
	 *
	 * @return  array
	 *
	 * @since  1.13.0
	 */
	public static function filterFields($filterData)
	{
		if (is_array($filterData))
		{
			// If filter value is an array. Clean the empty element.
			$filterData = array_filter(
				$filterData,
				function ($k, $v)
				{
					return $k != '';
				},
				ARRAY_FILTER_USE_BOTH
			);
		}

		return $filterData;
	}

	/**
	 * Adds Filter Fieldset logic to the query using a specific fields and mapping
	 *
	 * @param   JDatabaseQuery  $query              The input query from getListQuery function
	 * @param   object          $filterFields       Filter Field values to check
	 * @param   string          $filterFieldPrefix  Prefix for the filter Registry container
	 * @param   string          $tablePrefix        Prefix in use for the table being queried
	 * @param   string          $filterFieldsScope  Defining a scope will check only specific group
	 *                                              of fields ex. 'product','order','category','company','department'
	 * @param   string          $skipSection        Skip section
	 *
	 * @return  boolean
	 */
	public static function addFilterFieldsetQuery(
		&$query, $filterFields, $filterFieldPrefix, $tablePrefix, $filterFieldsScope = 'product', $skipSection = ''
	)
	{
		$db = Factory::getDbo();

		if (empty($filterFields))
		{
			return false;
		}

		$groupedFields  = RedshopbHelperField::getFields($filterFieldsScope);
		$allFields      = array();
		$preparedFields = array();

		// We collect all fields from all scopes if more than one scope is provided
		foreach ($groupedFields as $fieldsInScope)
		{
			foreach ($fieldsInScope as $field)
			{
				$allFields[] = $field;
			}
		}

		if (!empty($allFields))
		{
			foreach ($allFields as $field)
			{
				if ($field->id == $skipSection)
				{
					continue;
				}

				$filterValue = $filterFields->get($filterFieldPrefix . $field->id, '');
				$filterValue = self::filterFields($filterValue);

				if (!empty($filterValue))
				{
					$field->filterValue         = $filterValue;
					$preparedFields[$field->id] = $field;
				}
			}
		}

		if (empty($preparedFields))
		{
			return false;
		}

		$queryFields = array();

		foreach ($preparedFields as $currentKey => $field)
		{
			$prefix = 'fd' . $currentKey;
			$query->leftJoin(
				$db->qn('#__redshopb_field_data', $prefix) . ' ON ' . $db->qn($prefix . '.item_id') . ' = ' . $db->qn('p.id')
				. ' AND' . $db->qn($prefix . '.state') . ' = 1 AND ' . $db->qn($prefix . '.field_id') . ' = ' . (int) $field->id
			);

			$filterQuery = '(';

			// For range filter.
			if ($field->filter_type_field_name == 'range')
			{
				if (is_array($field->filterValue))
				{
					$filterValue = reset($field->filterValue);
				}
				else
				{
					$filterValue = $field->filterValue;
				}

				$filterValue = explode('-', $filterValue);

				// If this is filter range need to check value data. Convert it if necessary
				$filterMinValue = isset($filterValue[0]) ? $filterValue[0] : null;
				$filterMaxValue = isset($filterValue[1]) ? $filterValue[1] : null;

				// Gets the right column depending if a scalar or an actual range
				if ($field->field_name == 'aesECRange')
				{
					$filterValueColDownOr = 'SUBSTRING(' . $db->qn($prefix . '.' . $field->value_type) . ', 1, ' .
						'LOCATE(' . $db->q('-') . ', ' . $db->qn($prefix . '.' . $field->value_type) . ') - 1)';
					$filterValueColUpOr   = 'SUBSTRING(' . $db->qn($prefix . '.' . $field->value_type) . ', ' .
						'LOCATE(' . $db->q('-') . ', ' . $db->qn($prefix . '.' . $field->value_type) . ') + 1)';
				}
				else
				{
					$filterValueColDownOr = $db->qn($prefix . '.' . $field->value_type);
					$filterValueColUpOr   = $db->qn($prefix . '.' . $field->value_type);
				}

				$filterValueColDown = $filterValueColDownOr;
				$filterValueColUp   = $filterValueColUpOr;

				// Make sure get correct value type.
				if ($field->value_type == 'int_value')
				{
					$filterMinValue = (int) $filterMinValue;
					$filterMaxValue = (int) $filterMaxValue;
				}
				elseif ($field->value_type == 'float_value')
				{
					$filterMinValue = (float) $filterMinValue;
					$filterMaxValue = (float) $filterMaxValue;
				}
				else
				{
					$filterMinValue = (float) $filterMinValue;
					$filterMaxValue = (float) $filterMaxValue;

					$filterValueColUp   = 'CAST(' . $filterValueColUpOr . ' AS DECIMAL(10,2))';
					$filterValueColDown = 'CAST(' . $filterValueColDownOr . ' AS DECIMAL(10,2))';
				}

				$filterQuery .= is_null($filterMinValue) ? '1' : '(' .
					'(' . $filterMinValue . ' >= ' . $filterValueColDown . ' OR ' . $filterValueColDownOr . ' = ' . $db->q('') . ')' .
					' AND ' .
					'(' . $filterMinValue . ' <= ' . $filterValueColUp . ' OR ' . $filterValueColUpOr . ' = ' . $db->q('') . ')' .
					')';

				$filterQuery .= ' OR ';

				$filterQuery .= is_null($filterMaxValue) ? '1' : '(' .
					'(' . $filterMaxValue . ' >= ' . $filterValueColDown . ' OR ' . $filterValueColDownOr . ' = ' . $db->q('') . ')' .
					' AND ' .
					'(' . $filterMaxValue . ' <= ' . $filterValueColUp . ' OR ' . $filterValueColUpOr . ' = ' . $db->q('') . ')' .
					')';

				$filterQuery .= ')';
			}
			else
			{
				if (is_array($field->filterValue))
				{
					if ($field->value_type == 'float_value')
					{
						$filterValues  = array();
						$decimalLength = 2;

						foreach ($field->filterValue as $value)
						{
							$newValue = strlen(substr(strrchr($value, "."), 1));

							if ($newValue > $decimalLength)
							{
								$decimalLength = $newValue;
							}
						}

						foreach ($field->filterValue as $value)
						{
							$filterValues[] = 'CAST(' . $db->q($value) . ' AS DECIMAL(10,' . $decimalLength . '))';
						}

						$filterQuery .= 'CAST(' . $prefix . '.' . $field->value_type . ' AS DECIMAL(10,' . $decimalLength . '))'
							. ' IN (' . implode(',', $filterValues) . '))';
					}
					else
					{
						$filterQuery .= $prefix . '.' . $field->value_type . ' IN ('
							. implode(',', array_map(array($db, 'q'), $field->filterValue)) . '))';
					}
				}
				else
				{
					if ($field->value_type == 'float_value')
					{
						$decimalLength = 2;
						$newValue      = strlen(substr(strrchr($field->filterValue, "."), 1));

						if ($newValue > $decimalLength)
						{
							$decimalLength = $newValue;
						}

						$filterQuery .= 'CAST(' . $prefix . '.' . $field->value_type . ' AS DECIMAL(10,' . $decimalLength . '))'
							. ' = CAST(' . $db->q($field->filterValue) . ' AS DECIMAL(10,' . $decimalLength . ')))';
					}
					else
					{
						$filterQuery .= $prefix . '.' . $field->value_type . ' = ' . $db->q($field->filterValue) . ')';
					}
				}
			}

			$queryFields[] = $filterQuery;
		}

		if (empty($queryFields))
		{
			return false;
		}

		$query->where('(' . implode(' AND ', $queryFields) . ')');

		return true;
	}

	/**
	 * Get filter fields
	 *
	 * @param   string  $filterFieldsScope      Defining a scope will get fields for only one specific group
	 *                                          of fields ex. 'product','order','category','company','department'
	 * @param   bool    $calculateProductCount  Calculate Number of items per field from field data table
	 *
	 * @return  array
	 */
	public static function getFilterFields($filterFieldsScope = 'product', $calculateProductCount = false)
	{
		if (self::$filterFields)
		{
			return self::$filterFields;
		}

		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->select('f.*')
			->select('ff.name as fieldset_name')
			->select('ff.id as fieldset_id')
			->select('t.value_type as value_type')
			->from($db->qn('#__redshopb_filter_fieldset', 'ff'))
			->leftJoin($db->qn('#__redshopb_filter_fieldset_xref', 'ffx') . ' ON ffx.fieldset_id = ff.id')
			->leftJoin($db->qn('#__redshopb_field', 'f') . ' ON f.id = ffx.field_id')
			->leftJoin($db->qn('#__redshopb_type', 't') . ' ON t.id = f.type_id')
			->where($db->qn('f.scope') . ' = ' . $db->q($filterFieldsScope))
			->where($db->qn('f.state') . ' = 1')
			->where($db->qn('ff.state') . ' = 1')
			->order($db->qn('f.ordering') . ' ASC');

		if ($calculateProductCount)
		{
			$subQuery = $db->getQuery(true)
				->select('count(*)')
				->from($db->qn('#__redshopb_field_data', 'fd'))
				->where('fd.field_id = f.id')
				->where('fd.state = 1')
				->group('fd.field_id')
				->group('fd.item_id');

			$query->select('(' . ((string) $subQuery) . ') as field_item_count');
		}

		$db->setQuery($query);
		$filterFields = $db->loadObjectList();

		if ($filterFields)
		{
			self::$filterFields = array();

			foreach ($filterFields as $filterField)
			{
				if (!isset(self::$filterFields[$filterField->fieldset_id]))
				{
					self::$filterFields[$filterField->fieldset_id] = array();
				}

				self::$filterFields[$filterField->fieldset_id][] = $filterField;
			}
		}

		return self::$filterFields;
	}

	/**
	 * Method for get filter fieldsets from list of product Ids.
	 *
	 * @param   array  $productIds  Array of product Ids.
	 *
	 * @return  mixed               Array of fieldset Id if success. False otherwise.
	 */
	public static function getAvailableFilterFieldsFromProducts($productIds = array())
	{
		if (empty($productIds))
		{
			return false;
		}

		$db = Factory::getDbo();

		$query = $db->getQuery(true)
			->select($db->qn('ff.id'))
			->from($db->qn('#__redshopb_filter_fieldset', 'ff'))
			->leftJoin($db->qn('#__redshopb_product', 'p') . ' ON p.filter_fieldset_id = ff.id')
			->where($db->qn('p.id') . ' IN (' . implode(',', $productIds) . ')')
			->group($db->qn('ff.id'));
		$db->setQuery($query);

		return $db->loadColumn();
	}

	/**
	 * Method for get filter fieldsets from list of category Ids.
	 *
	 * @param   array  $categoryIds  Array of category Ids.
	 *
	 * @return  mixed                Array of fieldset Id if success. False otherwise.
	 */
	public static function getAvailableFilterFieldsFromCategories($categoryIds = array())
	{
		if (empty($categoryIds))
		{
			return false;
		}

		$db = Factory::getDbo();

		$query = $db->getQuery(true)
			->select($db->qn('ff.id'))
			->from($db->qn('#__redshopb_filter_fieldset', 'ff'))
			->leftJoin($db->qn('#__redshopb_category', 'c') . ' ON c.filter_fieldset_id = ff.id')
			->where($db->qn('c.id') . ' IN (' . implode(',', $categoryIds) . ')')
			->group($db->qn('ff.id'));
		$db->setQuery($query);

		return $db->loadColumn();
	}

	/**
	 * Method for prepare filter fieldsets base on list of products.
	 *
	 * @param   array   $productIds         List of product Ids.
	 * @param   int     $categoryId         ID of category
	 * @param   string  $jsCallback         Javascript function callback for filter when change value. Put empty for leave it.
	 * @param   string  $filterFieldPrefix  The session variable to prefix search for values in.
	 *
	 * @return  mixed                       List of fieldsets if success. False otherwise.
	 */
	public static function prepareFiltersFromProducts($productIds = array(), $categoryId = 0, $jsCallback = '', $filterFieldPrefix = 'filter')
	{
		// Check product ids
		if (empty($productIds) || !is_array($productIds))
		{
			return false;
		}

		// Get filter fields if not available.
		if (!self::$filterFields)
		{
			self::getFilterFields();
		}

		// Decide filter fieldsets would get from product Ids or Category
		$filterFieldsetId = RedshopbEntityCategory::load($categoryId)->get('filter_fieldset_id', 0);

		if ($filterFieldsetId)
		{
			$availableFieldsets = array($filterFieldsetId);
		}
		else
		{
			$availableFieldsets = self::getAvailableFilterFieldsFromProducts($productIds);
		}

		if (empty($availableFieldsets))
		{
			return array();
		}

		$filterFieldsets = array();
		$visibleFilters  = array();
		$fieldsData      = array();

		foreach ($availableFieldsets as $fieldsetId)
		{
			if (empty(self::$filterFields[$fieldsetId]))
			{
				continue;
			}

			foreach (self::$filterFields[$fieldsetId] as $filter)
			{
				// If filter render type is Dropdown, Checkbox or Radio. Need to get prepared values.
				if (in_array($filter->filter_type_id, self::getFilterTypesNeedPrepareValues()))
				{
					// If not. Prepare values from field data.
					if (!in_array($filter->type_id, self::getFilterTypesNeedPrepareValues()))
					{
						$fieldsData[$filter->id] = $filter;
					}
				}
			}
		}

		if (count($fieldsData) > 0)
		{
			$db            = Factory::getDbo();
			$valueType     = 'CASE field_id';
			$fieldId       = array();
			$foundSelected = array();

			foreach ($fieldsData as $fieldData)
			{
				if ($fieldData->value_type == 'float_value')
				{
					// Remove trailing zeros in decimal value
					$valueType .= ' WHEN ' . (int) $fieldData->id . ' THEN TRIM(TRAILING ' . $db->q('.')
						. ' FROM TRIM(TRAILING ' . $db->q('0') . ' from d.' . $fieldData->value_type . '))';
				}
				else
				{
					$valueType .= ' WHEN ' . (int) $fieldData->id . ' THEN ' . $db->qn('d.' . $fieldData->value_type);
				}

				$fieldId[] = $fieldData->id;

				if (count(self::getFilterDataFromSession($fieldData->id, $filterFieldPrefix)) > 0)
				{
					$foundSelected[] = $fieldData->id;
				}
			}

			$valueType .= ' END';
			$query      = $db->getQuery(true)
				->select($valueType . ' AS name')
				->select($valueType . ' AS data')
				->select($valueType . ' AS identifier')
				->select('d.field_id')
				->select('CONCAT_WS(' . $db->q('_') . ', d.field_id, (' . $valueType . ')) AS groupKey')
				->from($db->qn('#__redshopb_field_data', 'd'))
				->where('d.field_id IN (' . implode(',', $fieldId) . ')')
				->where($db->qn('d.state') . ' = 1')
				->where($db->qn('d.item_id') . ' IN (' . implode(',', $productIds) . ')')
				->group('groupKey');

			$query->select('COUNT(DISTINCT(productIdsQuery.product_id)) AS count');
			$productSearch = new RedshopbDatabaseProductsearch;

			if (count($foundSelected) > 0)
			{
				$productSearchQuery = $productSearch->getBaseSearchQuery();
				$productSearchQuery->clear('select');
				$productSearchQuery->select('p.id AS product_id, d2.id AS field_data_id');
				$productSearchQuery->select('CONCAT_WS(' . $db->q('_') . ', d2.field_id, d2.item_id) AS groupProductSearchQuery');
				$or = array();

				foreach ($foundSelected as $oneFieldId)
				{
					$or[] = '(d2.field_id = ' .
						(int) $oneFieldId . ' AND p.id IN (' .
						$productSearch->getJustFilteredProductIds($oneFieldId) . '))';
				}

				$or[] = 'p.id IN (' . $productSearch->getJustFilteredProductIds() . ')';
				$productSearchQuery->leftJoin($db->qn('#__redshopb_field_data', 'd2') . ' ON d2.item_id = p.id')
					->where('(' . implode(' OR ', $or) . ')')
					->group('groupProductSearchQuery');

				$query->leftJoin(
					'(' . $productSearchQuery . ') AS productIdsQuery ON
					productIdsQuery.product_id = d.item_id AND productIdsQuery.field_data_id = d.id'
				);
			}
			else
			{
				$productSearchQuery = $productSearch->getFilteredProductQuery();
				$query->leftJoin('(' . $productSearchQuery . ') AS productIdsQuery ON productIdsQuery.product_id = d.item_id');
			}

			$totalQuery = $productSearch->getBaseSearchQuery();
			$totalQuery->clear('select');
			$totalQuery->select('p.id AS product_id');

			if ($productSearch->hasTerm())
			{
				$totalQuery->where($db->qn('p.id') . ' IN (' . $productSearch->getStoredSearch() . ')');
			}

			if (Factory::getApplication()->getUserState('shop.layout', '') == 'manufacturer')
			{
				$productSearch->filterByManufactures($totalQuery);
			}

			$query->select('COUNT(DISTINCT(totalProductIds.product_id)) AS totalCount');

			$query->leftJoin('(' . $totalQuery . ') AS totalProductIds ON totalProductIds.product_id = d.item_id');

			$query->where('totalProductIds.product_id > 0');

			// *bump* @todo: Need enable translation for filter after find parser bug - ajax request lost comma in query
			$oldTranslate  = $db->translate;
			$db->translate = false;
			$filterValues  = $db->setQuery($query)
				->loadObjectList();
			$db->translate = $oldTranslate;

			if ($filterValues)
			{
				foreach ($filterValues as $filterValue)
				{
					if (!isset($fieldsData[$filterValue->field_id]->results))
					{
						$fieldsData[$filterValue->field_id]->results = array();
					}

					$fieldsData[$filterValue->field_id]->results[$filterValue->data] = $filterValue;
				}
			}
		}

		foreach ($fieldsData AS $index => $fieldData)
		{
			if (isset($fieldsData[$index]->results))
			{
				ksort($fieldsData[$index]->results);
			}
		}

		// Prepare filter fieldsets
		foreach ($availableFieldsets as $fieldsetId)
		{
			if (empty(self::$filterFields[$fieldsetId]))
			{
				continue;
			}

			$filterFieldset          = new stdClass;
			$filterFieldset->id      = $fieldsetId;
			$filterFieldset->filters = array();
			$filterFieldset->name    = isset(self::$filterFields[$fieldsetId][0]) ? self::$filterFields[$fieldsetId][0]->fieldset_name : '';

			// Prepare filter in each of fieldsets
			foreach (self::$filterFields[$fieldsetId] as $filter)
			{
				// Make sure filter just display 1 times even if it available in many filter fieldsets
				if (in_array($filter->id, $visibleFilters))
				{
					continue;
				}

				$filterValues = array();

				// If filter render type is Dropdown, Checkbox or Radio. Need to get prepared values.
				if (in_array($filter->filter_type_id, self::getFilterTypesNeedPrepareValues()))
				{
					// If this field is Dropdown, Checkbox or Radio. Get prepared values.
					if (in_array($filter->type_id, self::getFilterTypesNeedPrepareValues()))
					{
						$fieldRef           = !empty($filter->field_value_xref_id) ? $filter->field_value_xref_id : $filter->id;
						$skipCurrentSection = false;

						if (count(self::getFilterDataFromSession($filter->id, $filterFieldPrefix)) > 0)
						{
							$skipCurrentSection = true;
						}

						$filterValues = RedshopbHelperField::getFieldValues(
							$fieldRef, ($filter->only_available ? $productIds : array()), true, $filter->id, $skipCurrentSection
						);
					}
					// If not. Prepare values from field data.
					else
					{
						if (isset($fieldsData[$filter->id]->results))
						{
							$filterValues = $fieldsData[$filter->id]->results;
						}
						else
						{
							$filterValues = self::getCommonFieldsData($filter, $productIds, $filterFieldPrefix);
						}
					}

					if ($filter->{'field_value_ordering'} == 1)
					{
						$filterValues = self::getNaturalSort($filterValues);
					}
				}
				// For Range filter.
				elseif ($filter->filter_type_id == 9)
				{
					$filterValues = array('min' => null, 'max' => 5000);

					$db    = Factory::getDbo();
					$query = $db->getQuery(true)
						->from($db->qn('#__redshopb_field_data'))
						->where($db->qn('field_id') . ' = ' . (int) $filter->id)
						->where($db->qn('state') . ' = 1')
						->where($db->qn('item_id') . ' IN (' . implode(',', $productIds) . ')');

					// If range filter is build from normal integer number
					if (in_array($filter->value_type, array('int_value', 'float_value')))
					{
						$query->select('MIN(' . $db->qn($filter->value_type) . ') AS ' . $db->qn('min'))
							->select('MAX(' . $db->qn($filter->value_type) . ') AS ' . $db->qn('max'));
					}
					elseif ($filter->type_id == 19)
					{
						// Range type
						$query->select(
							'MIN(CAST(SUBSTRING(' . $db->qn($filter->value_type) . ', 1, ' .
							'locate(' . $db->q('-') . ', ' . $db->qn($filter->value_type) . ') - 1) AS DECIMAL(10, 2))) AS ' . $db->qn('min')
						)
							->select(
								'MAX(CAST(SUBSTRING(' . $db->qn($filter->value_type) . ', ' .
								'locate(' . $db->q('-') . ', ' . $db->qn($filter->value_type) . ') + 1) AS DECIMAL(10, 2))) AS ' . $db->qn('max')
							);
					}
					else
					{
						// Range filter value is build not from normal number, get all of available values and convert it.
						$query->select('MIN(CAST(' . $db->qn($filter->value_type) . ' AS DECIMAL(10,2))) AS ' . $db->qn('min'))
							->select('MAX(CAST(' . $db->qn($filter->value_type) . ' AS DECIMAL(10,2))) AS ' . $db->qn('max'));
					}

					$db->setQuery($query);
					$result = $db->loadObject();

					$filterValues['min'] = floor($result->min);
					$filterValues['max'] = ceil($result->max);
				}
				// For image filter.
				elseif ($filter->filter_type_id == 14)
				{
					$db    = Factory::getDbo();
					$query = $db->getQuery(true)
						->select($db->qn($filter->value_type, 'title'))
						->select($db->qn('d.params', 'params'))
						->from($db->qn('#__redshopb_field_data', 'd'))
						->where($db->qn('d.field_id') . ' = ' . (int) $filter->id)
						->where($db->qn('d.state') . ' = 1')
						->where($db->qn('d.item_id') . ' IN (' . implode(',', $productIds) . ')')
						->group($db->qn($filter->value_type));

					$query->select('COUNT(productIdsQuery.product_id) AS count');
					$productSearch      = new RedshopbDatabaseProductsearch;
					$productSearchQuery = $productSearch->getFilteredProductQuery();

					$query->leftJoin('(' . $productSearchQuery . ') AS productIdsQuery ON productIdsQuery.product_id = d.item_id');

					$totalQuery = $productSearch->getBaseSearchQuery();
					$totalQuery->clear('select');
					$totalQuery->select('DISTINCT(p.id) AS product_id');

					if ($productSearch->hasTerm())
					{
						$totalQuery->where($db->qn('p.id') . ' IN (' . $productSearch->getStoredSearch() . ')');
					}

					$query->select('COUNT(totalProductIds.product_id) AS totalCount');

					$query->leftJoin('(' . $totalQuery . ') AS totalProductIds ON totalProductIds.product_id = d.item_id');

					$filterValues = $db->setQuery($query)->loadObjectList();
				}

				$dataValue = self::getFilterDataFromSession($filter->id, $filterFieldPrefix);

				$layoutOptions = array(
					'filter'     => $filter,
					'value'      => $dataValue,
					'jsCallback' => $jsCallback
				);

				$layoutFile = 'filters.';

				switch ($filter->filter_type_id)
				{
					// Dropdown - Single
					case 5:
						$layoutFile                   .= 'dropdown';
						$layoutOptions['filterValues'] = $filterValues;
						$layoutOptions['multiple']     = false;
						break;

					// Dropdown - Multiple
					case 6:
						$layoutFile                   .= 'dropdown';
						$layoutOptions['filterValues'] = $filterValues;
						$layoutOptions['multiple']     = true;
						break;

					// Checkbox
					case 7:
						$layoutFile                   .= 'checkbox';
						$layoutOptions['filterValues'] = $filterValues;
						break;

					// Radio
					case 8:
						$layoutFile                   .= 'radio';
						$layoutOptions['filterValues'] = $filterValues;
						break;

					// Range
					case 9:
					case 19:
						if ($filterValues['min'] == $filterValues['max'])
						{
							if (isset($fieldsData[$filter->id]->results))
							{
								$filterValues = $fieldsData[$filter->id]->results;
							}
							else
							{
								$filterValues = self::getCommonFieldsData($filter, $productIds, $filterFieldPrefix);
							}

							$layoutFile                   .= 'checkbox';
							$layoutOptions['filterValues'] = $filterValues;
						}
						else
						{
							$layoutFile                .= 'range';
							$layoutOptions['filterMin'] = $filterValues['min'];
							$layoutOptions['filterMax'] = $filterValues['max'];
						}
						break;

					// Date picker
					case 10:
						$layoutFile                   .= 'date';
						$layoutOptions['filterValues'] = $filterValues;
						break;

					case 14:
						$layoutFile                   .= 'images';
						$layoutOptions['filterValues'] = $filterValues;
						break;

					// Div elements - single
					case 15:
						$layoutFile                   .= 'divelements';
						$layoutOptions['filterValues'] = $filterValues;
						$layoutOptions['multiple']     = false;
						break;

					// Div elements - multiple
					case 16:
						$layoutFile                   .= 'divelements';
						$layoutOptions['filterValues'] = $filterValues;
						$layoutOptions['multiple']     = true;
						break;

					default:
						$layoutFile .= 'text';
						break;
				}

				if (empty($dataValue) && empty($filterValues) && in_array($filter->filter_type_id, self::getFilterTypesNeedPrepareValues()))
				{
					// If there are no value available for filter and filter in case need prepare values, skip it.
					continue;
				}

				$filter->input             = RedshopbLayoutHelper::render($layoutFile, $layoutOptions);
				$filterFieldset->filters[] = $filter;

				// Make sure filter just display 1 times even if it available in many filter fieldsets
				$visibleFilters[] = $filter->id;
			}

			$filterFieldsets[] = $filterFieldset;
		}

		unset($visibleFilters);

		return $filterFieldsets;
	}

	/**
	 * Get Common Fields Data
	 *
	 * @param   object  $filter             Filter variables
	 * @param   array   $productIds         List of product Ids.
	 * @param   string  $filterFieldPrefix  The session variable to prefix search for values in.
	 *
	 * @return  array|mixed
	 *
	 * @since  1.13.0
	 */
	public static function getCommonFieldsData($filter, $productIds = array(), $filterFieldPrefix = 'filter')
	{
		if (empty($productIds))
		{
			return array();
		}

		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->select($db->qn($filter->value_type, 'data'))
			->select($db->qn($filter->value_type, 'identifier'))
			->from($db->qn('#__redshopb_field_data', 'd'))
			->where($db->qn('d.field_id') . ' = ' . (int) $filter->id)
			->where($db->qn('d.state') . ' = 1')
			->where($db->qn('d.item_id') . ' IN (' . implode(',', $productIds) . ')')
			->group($db->qn($filter->value_type));

		$productSearch = new RedshopbDatabaseProductsearch;

		if (count(self::getFilterDataFromSession($filter->id, $filterFieldPrefix)) > 0)
		{
			$productSearchQuery = $productSearch->getBaseSearchQuery();
			$productSearchQuery->clear('select')
				->select('DISTINCT(p.id) AS product_id')
				->leftJoin($db->qn('#__redshopb_field_data', 'd2') . ' ON d2.item_id = p.id')
				->where('p.id IN (' . $productSearch->getJustFilteredProductIds($filter->id) . ')');
		}
		else
		{
			$productSearchQuery = $productSearch->getFilteredProductQuery();
		}

		$query->select('COUNT(productIdsQuery.product_id) AS count')
			->leftJoin('(' . $productSearchQuery . ') AS productIdsQuery ON productIdsQuery.product_id = d.item_id');

		$totalQuery = $productSearch->getBaseSearchQuery();
		$totalQuery->clear('select');
		$totalQuery->select('DISTINCT(p.id) AS product_id');

		if ($productSearch->hasTerm())
		{
			$totalQuery->where($db->qn('p.id') . ' IN (' . $productSearch->getStoredSearch() . ')');
		}

		$query->select('COUNT(totalProductIds.product_id) AS totalCount')
			->leftJoin('(' . $totalQuery . ') AS totalProductIds ON totalProductIds.product_id = d.item_id');

		return $db->setQuery($query)
			->loadObjectList();
	}

	/**
	 * Get natural sort for fields
	 *
	 * @param   array   $filterValues   Filter values
	 * @param   string  $sortFieldName  Sort field name
	 *
	 * @return array
	 *
	 * @since  1.13.0
	 */
	public static function getNaturalSort($filterValues, $sortFieldName = 'data')
	{
		if (empty($filterValues))
		{
			return $filterValues;
		}

		$naturalSort = array();

		foreach ($filterValues as $key => $filterValue)
		{
			if (is_object($filterValue))
			{
				$naturalSort[$key] = $filterValue->{$sortFieldName};
			}
			else
			{
				$naturalSort[$key] = $filterValue;
			}
		}

		natsort($naturalSort);
		$naturalSortFilterValues = array();

		foreach ($naturalSort as $key => $variable)
		{
			$naturalSortFilterValues[] = $filterValues[$key];
		}

		return $naturalSortFilterValues;
	}

	/**
	 * Method for load filter data from session
	 *
	 * @param   string  $name               Name of filter.
	 * @param   string  $filterFieldPrefix  Filter field prefix.
	 * @param   mixed   $default            Default data if this value not set yet.
	 *
	 * @return  mixed                       Data of filter.
	 */
	public static function getFilterDataFromSession($name = '', $filterFieldPrefix = 'filter', $default = null)
	{
		if (empty($name))
		{
			return false;
		}

		$data = Factory::getSession()->get('registry');

		return $data->get($filterFieldPrefix . '.' . $name, $default);
	}

	/**
	 * Method for set filter data to session
	 *
	 * @param   string  $name               Name of filter.
	 * @param   mixed   $value              Value for store.
	 * @param   string  $filterFieldPrefix  Filter field prefix.
	 *
	 * @return  boolean                     True if success. False otherwise.
	 */
	public static function setFilterDataToSession($name, $value, $filterFieldPrefix = 'filter')
	{
		if (empty($name))
		{
			return false;
		}

		$data = Factory::getSession()->get('registry');

		return $data->set($filterFieldPrefix . '.' . $name, $value);
	}

	/**
	 * Method for get all field types need prepare values when render filter.
	 *
	 * @return  array  Array of all field types Id
	 */
	public static function getFilterTypesNeedPrepareValues()
	{
		/*
		 * Dropdown - Single / Dropdown - Multiple / Checkbox / Radio / Radio Boolean / Divelements - Single / Divelements - Multiple
		 * Range
		 */
		return array(5,6,7,8,11,15,16,19);
	}
}
