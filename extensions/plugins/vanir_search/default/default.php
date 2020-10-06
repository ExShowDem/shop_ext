<?php
/**
 * @package     Plugin.Vanir_Search
 * @subpackage  Default
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

use Joomla\Registry\Registry;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Factory;

// No direct access
defined('_JEXEC') or die;

/**
 * Class PlgVanirDefault_Search
 *
 * @since  0.0.1
 */
class PlgVanir_SearchDefault extends CMSPlugin
{
	/**
	 * @var Registry
	 */
	protected $vanirConfig;

	/**
	 * @var boolean
	 */
	protected $useDefault;

	/**
	 * Constructor
	 *
	 * @param   object  $subject  The object to observe
	 * @param   array   $config   An optional associative array of configuration settings.
	 *                            Recognized key values include 'name', 'group', 'params', 'language'
	 *                            (this list is not meant to be comprehensive).
	 *
	 * @since   1.5
	 */
	public function __construct(&$subject, $config = array())
	{
		parent::__construct($subject, $config);

		$this->vanirConfig = RedshopbApp::getConfig();
	}

	/**
	 * Method to search the database for a priority list of product ids
	 *
	 * @param   RedshopbDatabaseProductsearch   $productSearch  product search class
	 * @param   array                           $productIds     empty array of product ids
	 * @param   int                             $customerId     customer ID
	 * @param   string                          $customerType   customer type
	 *
	 * @return null
	 */
	public function onVanirGetProductIdsBySearchCriteria(RedshopbDatabaseProductsearch $productSearch, &$productIds, $customerId, $customerType)
	{
		if (!$this->useDefaultSearch())
		{
			return null;
		}

		$unionQueries = array();
		$db           = Factory::getDbo();

		if (!$productSearch->hasCriteria() || $productSearch->isSimpleSearch())
		{
			$simpleProductIds = $productSearch->simpleSearch($customerId, $customerType);

			foreach ($simpleProductIds AS $productId)
			{
				$productIds[] = $productId;
			}

			return null;
		}

		$clonedCriteria = $productSearch->getSearchCriteria();
		$stopIfFound    = (boolean) $this->vanirConfig->get('product_search_stop_if_found', false);

		foreach ($clonedCriteria as $priority => $fields)
		{
			$tablesNeeded = $this->getRequiredTable($fields);

			if (empty($tablesNeeded))
			{
				continue;
			}

			if (array_key_exists('extra_fields', $tablesNeeded))
			{
				$extraFieldsQuery = $this->getExtraFieldQuery($productSearch, $priority, $tablesNeeded['extra_fields']);

				if (!empty($extraFieldsQuery))
				{
					$unionQueries[] = $extraFieldsQuery;
				}

				unset($tablesNeeded['extra_fields']);
			}

			$searchFields = array();
			$firstQuery   = true;
			$searchQuery  = $productSearch->getBaseSearchQuery($customerId, $customerType);

			// Join product table conditions with first union sub query for little improve main query
			if (array_key_exists('product', $tablesNeeded))
			{
				foreach ($tablesNeeded['product'] as $tableField)
				{
					switch ($tableField->name)
					{
						case 'product_name':
							$tableField->field = 'p.name';
							$searchFields[]    = $tableField;
							break;
						case 'manufacturer_sku':
							$tableField->field = 'p.manufacturer_sku';
							$searchFields[]    = $tableField;
							break;
						case 'related_sku':
							$tableField->field = 'p.related_sku';
							$searchFields[]    = $tableField;
							break;
						case 'product_sku':
							$tableField->field = 'p.sku';
							$searchFields[]    = $tableField;
							break;
					}
				}

				unset($tablesNeeded['product']);
			}

			// Join manufacturer table conditions with first union sub query for little improve main query
			// Manufacturer table has 1 in 1 reference with product so join it with first query is okay
			if (array_key_exists('manufacturer', $tablesNeeded))
			{
				$searchQuery->leftJoin(
					$db->qn('#__redshopb_manufacturer', 'manufacturer')
					. ' FORCE INDEX(PRIMARY) ON p.manufacturer_id = manufacturer.id'
				);

				foreach ($tablesNeeded['manufacturer'] as $tableField)
				{
					switch ($tableField->name)
					{
						case 'manufacturer_name':
							$tableField->field = 'manufacturer.name';
							$searchFields[]    = $tableField;
							break;
					}
				}

				unset($tablesNeeded['manufacturer']);
			}

			if (!empty($tablesNeeded))
			{
				foreach ($tablesNeeded as $tableNeeded => $tableFields)
				{
					if (!$firstQuery)
					{
						$searchQuery  = $productSearch->getBaseSearchQuery($customerId, $customerType);
						$searchFields = array();
					}

					$firstQuery = false;

					switch ($tableNeeded)
					{
						case 'category':
							$searchQuery->leftJoin(
								$db->qn('#__redshopb_product_category_xref', 'cref') . ' ON ' . $db->qn('cref.product_id') . ' = ' . $db->qn('p.id')
							)
								->leftJoin(
									$db->qn('#__redshopb_category', 'cat') . ' ON ' . $db->qn('cat.id') . ' = ' . $db->qn('cref.category_id')
								);

							foreach ($tableFields as $tableField)
							{
								switch ($tableField->name)
								{
									case 'category_description':
										$tableField->field = 'cat.description';
										$searchFields[]    = $tableField;
										break;
									case 'category_name':
										$tableField->field = 'cat.name';
										$searchFields[]    = $tableField;
										break;
								}
							}

							break;
						case 'product_description':
							$searchQuery->leftJoin(
								$db->qn('#__redshopb_product_descriptions', 'pdesc') . ' ON ' . $db->qn('pdesc.product_id') . ' = ' . $db->qn('p.id')
							);

							foreach ($tableFields as $tableField)
							{
								switch ($tableField->name)
								{
									case 'product_description':
										$tableField->field = 'pdesc.description';
										$searchFields[]    = $tableField;
										break;
								}
							}

							break;
						case 'media':
							$searchQuery->leftJoin(
								$db->qn('#__redshopb_media', 'b2bmd') . ' FORCE INDEX (#__rs_media_fk1) ON '
								. '(' . $db->qn('p.id') . ' = ' . $db->qn('b2bmd.product_id')
								. ' AND ' . $db->qn('b2bmd.state') . ' = 1)'
							)
								->group($db->qn('p.id'));

							foreach ($tableFields as $tableField)
							{
								switch ($tableField->name)
								{
									case 'image_alt_text':
										$tableField->field = 'b2bmd.alt';
										$searchFields[]    = $tableField;
										break;
								}
							}

							break;
						case 'tag':
							$searchQuery->leftJoin(
								$db->qn('#__redshopb_product_tag_xref', 'ptx') . ' FORCE INDEX(PRIMARY) ON ptx.product_id = p.id'
							)
								->leftJoin(
									$db->qn('#__redshopb_tag', 't') . ' FORCE INDEX(PRIMARY) ON ptx.tag_id = t.id'
								)
								->group($db->qn('p.id'));

							foreach ($tableFields as $tableField)
							{
								switch ($tableField->name)
								{
									case 'tags':
										$tableField->field = 't.name';
										$searchFields[]    = $tableField;
										break;
								}
							}
							break;
						case 'product_attribute_value':
							$subQuery = $db->getQuery(true)
								->select(
									'(CASE '
									. 'WHEN prAtt.type_id = 1 THEN prAttVal.string_value '
									. ($productSearch->indexerQuery->hasSpecialCharacter ? 'collate utf8_bin ' : '')
									. 'WHEN prAtt.type_id = 2 THEN prAttVal.float_value '
									. 'WHEN prAtt.type_id = 3 THEN prAttVal.int_value '
									. 'WHEN prAtt.type_id = 4 THEN prAttVal.text_value '
									. ($productSearch->indexerQuery->hasSpecialCharacter ? 'collate utf8_bin ' : '')
									. 'END) AS value'
								)
								->select('prAttVal.product_attribute_id')
								->from($db->qn('#__redshopb_product_attribute_value', 'prAttVal'))
								->leftJoin($db->qn('#__redshopb_product_attribute', 'prAtt') . ' ON prAttVal.product_attribute_id = prAtt.id');

							$searchQuery->leftJoin($db->qn('#__redshopb_product_attribute', 'prAtt2') . ' ON prAtt2.product_id = p.id')
								->leftJoin('(' . $subQuery . ') AS prAttVal2 ON prAttVal2.product_attribute_id = prAtt2.id');

							foreach ($tableFields as $tableField)
							{
								switch ($tableField->name)
								{
									case 'product_attribute_value':
										$tableField->field     = 'prAttVal2.value';
										$tableField->isNumeric = true;
										$searchFields[]        = $tableField;
										break;
								}
							}
							break;
						case 'product_item_sku':
							$searchQuery->leftJoin($db->qn('#__redshopb_product_item', 'prItem') . ' ON prItem.product_id = p.id');

							foreach ($tableFields as $tableField)
							{
								switch ($tableField->name)
								{
									case 'product_item_sku':
										$tableField->field = 'prItem.sku';
										$searchFields[]    = $tableField;
										break;
								}
							}
							break;
					}

					if (empty($searchFields))
					{
						continue;
					}

					$searchQuery->where('(' . $productSearch->preparePartiallySearch($searchFields) . ')');
					$searchQuery->select($productSearch->preparePartiallySearchPriority($searchFields, $priority));
					$unionQueries[] = $searchQuery;
				}
			}
			elseif (!empty($searchFields))
			{
				$searchQuery->where('(' . $productSearch->preparePartiallySearch($searchFields) . ')');
				$searchQuery->select($productSearch->preparePartiallySearchPriority($searchFields, $priority));
				$unionQueries[] = $searchQuery;
			}

			if ($stopIfFound && !empty($unionQueries))
			{
				$query = array_shift($unionQueries);

				if (!empty($unionQueries))
				{
					foreach ($unionQueries as $unionQuery)
					{
						$query->unionDistinct($unionQuery);
					}
				}

				$results      = $db->setQuery($query)->loadAssocList('id', 'priority');
				$unionQueries = array();

				if (!empty($results))
				{
					foreach ($results as $productId => $priority)
					{
						if (!isset($productIds[$priority]))
						{
							$productIds[$priority] = $productId;
						}
						else
						{
							$productIds[$priority] .= ',' . $productId;
						}
					}

					break;
				}
			}
		}

		if (!$stopIfFound && !empty($unionQueries))
		{
			$query = array_shift($unionQueries);

			if (!empty($unionQueries))
			{
				foreach ($unionQueries as $unionQuery)
				{
					$query->unionDistinct($unionQuery);
				}
			}

			$mainQuery = $db->getQuery(true)
				->select('mainQuery.id, MIN(mainQuery.priority) AS priority')
				->from('(' . $query . ') AS mainQuery')
				->group($db->qn('mainQuery.id'))
				->order('priority ASC');

			$stemUsed = (int) $this->vanirConfig->get('stem', 0);

			if ($stemUsed)
			{
				// Getting mach from stemmers
				$mainQuery->select(
					'(CASE WHEN mainQuery.priority >= 200 AND mainQuery.priority < 300 THEN mainQuery.concatenation ELSE NULL END) AS concatenation'
				);
			}

			$results = $db->setQuery($mainQuery)->loadAssocList();

			$availableStem = array();

			foreach ($productSearch->indexerQuery->included as $item)
			{
				if (!$item->phrase && $item->stem && $item->term != $item->stem)
				{
					$availableStem[] = $item->stem;
				}
			}

			foreach ($results as $result)
			{
				// Found mach from stemmer
				if ($stemUsed && $result['concatenation'])
				{
					$keywords  = preg_split("/[\s,]+/", $result['concatenation'], -1, PREG_SPLIT_NO_EMPTY);
					$coincides = false;

					if (!empty($keywords))
					{
						foreach ($keywords as $keyword)
						{
							if (!is_numeric($keyword) && strlen($keyword) > 3)
							{
								if (in_array(RedshopbDatabaseIndexerHelper::stem($keyword, $productSearch->indexerQuery->language), $availableStem))
								{
									$coincides = true;
									break;
								}
							}
						}
					}

					if (!$coincides)
					{
						continue;
					}
				}

				if (!isset($productIds[$result['priority']]))
				{
					$productIds[$result['priority']] = $result['id'];
				}
				else
				{
					$productIds[$result['priority']] .= ',' . $result['id'];
				}
			}

			unset($results);
		}

		if (empty($productIds))
		{
			$productIds[] = 0;
		}
	}

	/**
	 * Method to get a list of join tables required for the search based on the fields being searched
	 *
	 * @param   array  $fields  of search criteria fields
	 *
	 * @return array
	 */
	protected function getRequiredTable($fields)
	{
		$requiredTables = array();

		foreach ($fields as $field)
		{
			switch ($field->name)
			{
				case 'product_name':
				case 'manufacturer_sku':
				case 'related_sku':
				case 'product_sku':
				default:
					$table = 'product';
					break;
				case 'category_name':
				case 'category_description':
					$table = 'category';
					break;
				case 'manufacturer_name':
					$table = 'manufacturer';
					break;
				case 'product_description':
					$table = 'product_description';
					break;
				case 'image_alt_text':
					$table = 'media';
					break;
				case 'tags':
					$table = 'tag';
					break;
				case 'product_item_sku':
					$table = 'product_item_sku';
					break;
				case 'product_attribute_value':
					$table = 'product_attribute_value';
					break;
				case 'additional_fields':
				case is_numeric($field->name):
					$table = 'extra_fields';
					break;
			}

			if (!array_key_exists($table, $requiredTables))
			{
				$requiredTables[$table] = array();
			}

			// Clone here require for avoid override configuration variables, because they use for generate md5 cache key
			$requiredTables[$table][$field->name] = clone $field;
		}

		return $requiredTables;
	}

	/**
	 * Method to build a query for extra fields
	 *
	 * @param   RedshopbDatabaseProductsearch  $productSearch  product search instance
	 * @param   int                            $priority       priority
	 * @param   array                          $searchFields   list of fields to search
	 *
	 * @return JDatabaseQuery|null
	 */
	protected function getExtraFieldQuery($productSearch, $priority, $searchFields)
	{
		$extraFieldsObj = $productSearch->getSearchableExtraFields();
		$searchQuery    = $productSearch->getBaseSearchQuery();

		foreach ($extraFieldsObj as $extraFieldsObjKey => $extraFieldObj)
		{
			if (!array_key_exists($extraFieldObj->id, $searchFields))
			{
				unset($extraFieldsObj[$extraFieldsObjKey]);

				continue;
			}

			$extraFieldObj->synonym = $searchFields[$extraFieldObj->id]->synonym;
			$extraFieldObj->stem    = $searchFields[$extraFieldObj->id]->stem;
		}

		if (empty($extraFieldsObj))
		{
			return null;
		}

		$db = Factory::getDbo();

		$searchQuery->leftJoin(
			$db->qn('#__redshopb_field_data', 'search_fd') . ' ON '
			. $db->qn('search_fd.item_id') . ' = ' . $db->qn('p.id')
		);
		$searchFields  = array();
		$or            = array();
		$combineToType = array(
			'string_value' => array(),
			'int_value' => array(),
			'float_value' => array(),
			'text_value' => array()
		);

		foreach ($extraFieldsObj as $extraField)
		{
			$combineToType[$extraField->value_type][$extraField->id] = $extraField;
		}

		foreach ($combineToType as $typeName => $extraFields)
		{
			if ($typeName == 'field_value')
			{
				$searchQuery->leftJoin(
					$db->qn('#__redshopb_field_value', 'search_fv')
					. ' ON ' . $db->qn('search_fv.id') . ' = ' . $db->qn('search_fd.field_value')
					. ' AND ' . $db->qn('search_fv.field_id') . ' = ' . $db->qn('search_fd.field_id')
				);
			}

			foreach ($extraFields as $extraField)
			{
				if (in_array($typeName, array('int_value', 'float_value')))
				{
					$extraField->isNumeric = true;
				}

				if ($typeName != 'field_value')
				{
					$extraField->field = 'search_fd.' . $typeName;
					$or[]              = '(search_fd.field_id = ' . (int) $extraField->id . ' AND '
						. $productSearch->preparePartiallySearch(array($extraField)) . ')';
				}
				else
				{
					$extraField->field = 'search_fv.name';
					$or[]              = '(search_fv.field_id = ' . (int) $extraField->id . ' AND '
						. $productSearch->preparePartiallySearch(array($extraField)) . ')';
				}

				$searchFields[] = $extraField;
			}
		}

		$searchQuery->where('(' . implode(" OR ", $or) . ')');
		$searchQuery->group($db->qn('p.id'));
		$searchQuery->select($productSearch->preparePartiallySearchPriority($searchFields, $priority));

		return $searchQuery;
	}

	/**
	 * Method to get a list of category id based on the search terms
	 *
	 * @param   RedshopbDatabaseProductsearch   $productSearch  Product search class
	 * @param   array                           $categoryIds    Empty array of category ids
	 *
	 * @return null
	 */
	public function onVanirGetCategories(RedshopbDatabaseProductsearch $productSearch, &$categoryIds)
	{
		if (!$this->useDefaultSearch())
		{
			return null;
		}

		$searchFields = array();

		$criteria = $productSearch->getSearchCriteria();

		foreach ($criteria as $fields)
		{
			foreach ($fields as $field)
			{
				switch ($field->name)
				{
					case 'category_name':
						$searchField        = clone $field;
						$searchField->field = 'name';
						$searchFields[]     = $searchField;
						break;
					case 'category_description':
						$searchField        = clone $field;
						$searchField->field = 'description';
						$searchFields[]     = $searchField;
						break;
				}
			}
		}

		if (empty($searchFields))
		{
			return null;
		}

		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->select('id')
			->from($db->qn('#__redshopb_category'))
			->where('(' . $productSearch->preparePartiallySearch($searchFields) . ')');

		// Limit by available categories
		$companyId           = RedshopbHelperCompany::getCompanyIdByCustomer($productSearch->getCustomerId(), $productSearch->getCustomerType());
		$availableCategories = RedshopbHelperACL::listAvailableCategories(
			Factory::getUser()->id, false, 100, $companyId, false, 'comma', '', '', 0, 0, false, true
		);

		if (empty($availableCategories))
		{
			$availableCategories = '0';
		}

		$query->where('id IN(' . $availableCategories . ')');

		$results = $db->setQuery($query)->loadColumn();

		foreach ($results as $categoryId)
		{
			$categoryIds[] = $categoryId;
		}
	}

	/**
	 * Method to check if we should use the default search
	 *
	 * @return boolean
	 */
	private function useDefaultSearch()
	{
		if (!is_null($this->useDefault))
		{
			return $this->useDefault;
		}

		$useDefault = true;
		Factory::getApplication()->triggerEvent('onBeforeVanirSearchDefaultSearch', array(&$useDefault));

		$this->useDefault = $useDefault;

		return $useDefault;
	}
}
