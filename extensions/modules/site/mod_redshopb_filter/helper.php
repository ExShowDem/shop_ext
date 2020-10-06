<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  mod_redshopb_filter
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die();

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ModuleHelper;

/**
 * Helper class for filter
 *
 * @package     Aesir.E-Commerce
 * @subpackage  Module.Filter
 * @since       1.0
 */
class ModRedshopbFilterHelper
{
	/**
	 * Collection products.
	 *
	 * @var  array
	 */
	private static $collectionProducts = array();

	/**
	 * Method for get categories data for filter.
	 *
	 * @param   int  $defaultCategory  The default category to use if none are selected
	 *
	 * @return  array  List of categories.
	 */
	public function getCategories($defaultCategory)
	{
		$app                = Factory::getApplication();
		$input              = $app->input;
		$inputOption        = $input->getCmd('option', '');
		$inputView          = $input->getCmd('view', '');
		$inputLayout        = $input->getCmd('layout', '');
		$customerType       = $app->getUserState('shop.customer_type', '');
		$customerId         = $app->getUserState('shop.customer_id', 0);
		$collectionProducts = self::getCollectionProducts($customerId, $customerType);
		$collectionId       = array_filter($input->get('collection_id', array(), 'array'));

		/** @var RedshopbModelCategories $categoriesModel */
		$categoriesModel = RModel::getFrontInstance('Categories', array('ignore_request' => true), 'com_redshopb');
		$categoriesModel->setState('filter.current_view', $inputView);

		$id            = $input->getInt('id', 0);
		$productSearch = new RedshopbDatabaseProductsearch;

		if ($inputOption == 'com_redshopb' && $inputView == 'shop')
		{
			switch ($inputLayout)
			{
				case 'category':
					$categoriesModel->setState('filter.parent_id', $id);
					break;
				case 'manufacturer':
					$categoriesModel->setState('filter.manufacturer', $id);
					break;
			}
		}

		// Apply permission rules
		$availableCategories = RedshopbHelperACL::listAvailableCategories(
			Factory::getUser()->id,
			1,
			99,
			RedshopbHelperUser::getUserCompany(),
			$collectionId,
			'comma',
			'',
			'redshopb.category.view',
			0,
			0,
			true,
			true
		);

		$categoriesModel->setState('filter.ids', explode(',', $availableCategories));
		$categoriesModel->setState('list.countProductsSelect', true);

		$categories = $categoriesModel->getItems();

		if (empty($categories))
		{
			return array();
		}

		$app     = Factory::getApplication();
		$itemKey = $app->getUserState('shop.itemKey', 0);
		$value   = $app->getUserState('shop.categoryfilter.' . $itemKey, $defaultCategory);

		// If filter is not array, convert it to array
		if (!is_array($value))
		{
			$value = array($value);
		}

		// Clean up empty value
		$value = array_filter($value);

		$options = array();
		$ids     = array();

		foreach ($categories AS $category)
		{
			$ids[] = $category->id;
		}

		$skipSection = '';

		if (!empty($value))
		{
			$skipSection = 'category';
		}

		$productIds = $productSearch->getJustFilteredProductIds($collectionId, $skipSection);

		if (!empty($collectionProducts))
		{
			$tmp        = explode(',', $productIds);
			$tmp        = array_intersect($collectionProducts, $tmp);
			$productIds = implode(',', $tmp);
		}

		$db         = Factory::getDbo();
		$totalQuery = $db->getQuery(true);
		$totalQuery->select('COUNT(ptxCategory.product_id) AS productCount')
			->select('ptxCategory.category_id')
			->from($db->qn('#__redshopb_product_category_xref', 'ptxCategory'))
			->leftJoin('#__redshopb_product AS p ON p.id = ptxCategory.product_id')
			->where('ptxCategory.category_id IN (' . implode(',', $ids) . ')')
			->group('ptxCategory.category_id');

		if (empty($productIds))
		{
			$totalQuery->where('0 = 1');
		}
		else
		{
			$totalQuery->where($db->qn('ptxCategory.product_id') . ' IN (' . $productIds . ')');
		}

		$oldTranslate  = $db->translate;
		$db->translate = false;
		$productCount  = $db->setQuery($totalQuery)
			->loadAssocList('category_id', 'productCount');
		$db->translate = $oldTranslate;

		foreach ($categories as $category)
		{
			if (empty($category->totalCount))
			{
				continue;
			}

			$option           = new stdClass;
			$option->value    = $category->id;
			$option->text     = $category->name;
			$option->selected = false;

			if (array_key_exists($category->id, $productCount))
			{
				$option->count = $productCount[$category->id];
			}
			else
			{
				$option->count = 0;
			}

			$option->total = $category->totalCount;

			if (!empty($value) && in_array($category->id, $value))
			{
				$option->selected = true;
			}

			$options[] = $option;
		}

		return $options;
	}

	/**
	 * Method for get tags data for filter.
	 *
	 * @param   array  $excludeType  List of tag type which will be exclude from filter
	 *
	 * @return  array   List of tags.
	 */
	public function getTags($excludeType = array())
	{
		$app                 = Factory::getApplication();
		$input               = $app->input;
		$inputOption         = $input->getCmd('option', '');
		$inputView           = $input->getCmd('view', '');
		$inputLayout         = $input->getCmd('layout', '');
		$isProductListLayout = false;
		$categoryId          = 0;
		$collectionId        = array_filter($input->get('collection_id', array(), 'array'));

		$tagsModel = RModel::getFrontInstance('Tags', array('ignore_request' => true), 'com_redshopb');
		$tagsModel->setState('filter.current_view', $inputView);
		$tagsModel->setState('filter.type_exclude', $excludeType);
		$tagsModel->setState('list.countProductsSelect', true);
		$id = $input->getInt('id', 0);

		if ($inputOption == 'com_redshopb' && $inputView == 'shop')
		{
			switch ($inputLayout)
			{
				case 'category':
					$categoryId = $id;
					break;
				case 'productlist':
				case 'productfeatured':
				case 'productrecent':
					$isProductListLayout = true;
					break;
				case 'manufacturer':
					$tagsModel->setState('filter.manufacturer', $id);
					break;
			}
		}

		$customerType       = $app->getUserState('shop.customer_type', '');
		$customerId         = $app->getUserState('shop.customer_id', 0);
		$productSearch      = new RedshopbDatabaseProductsearch;
		$collectionProducts = self::getCollectionProducts($customerId, $customerType);

		// If has term, then available categories already filtered in product search list
		if ($productSearch->hasTerm())
		{
			if ($categoryId)
			{
				$companyId = RedshopbHelperCompany::getCompanyIdByCustomer($customerId, $customerType);

				$availableCategories = RedshopbHelperACL::listAvailableCategories(
					Factory::getUser()->id, false, 100, $companyId, $collectionId, 'comma', ''
				);

				$availableCategories = explode(',', $availableCategories);

				if (!in_array($categoryId, $availableCategories))
				{
					$categoryId = 0;
				}

				$tagsModel->setState('filter.category', $categoryId);
			}
		}
		else
		{
			$companyId = RedshopbHelperCompany::getCompanyIdByCustomer($customerId, $customerType);

			$availableCategories = RedshopbHelperACL::listAvailableCategories(
				Factory::getUser()->id, false, 100, $companyId, $collectionId, 'comma', ''
			);

			$availableCategories = explode(',', $availableCategories);
			$availableCategories = (!$categoryId ? $availableCategories : array_intersect($availableCategories, array($categoryId)));

			if (empty($availableCategories))
			{
				$availableCategories[] = 0;
			}

			$tagsModel->setState('filter.category', $availableCategories);
		}

		if ($isProductListLayout)
		{
			if (!empty($collectionProducts))
			{
				$tagsModel->setState('filter.product_ids', explode(',', $collectionProducts));
			}
			else
			{
				$productIds = explode(',', RedshopbHelperShop::getFilteredProductIds(false));
				$tagsModel->setState('filter.product_ids', $productIds);
			}
		}

		$tags = $tagsModel->getAllItems();

		if (empty($tags))
		{
			return array();
		}

		$app     = Factory::getApplication();
		$itemKey = $app->getUserState('shop.itemKey', 0);
		$value   = $app->getUserState('shop.tag.' . $itemKey);

		// If filter is not array, convert it to array
		if (!is_array($value))
		{
			$value = array($value);
		}

		// Clean up empty value
		$value = array_filter($value);

		$ids = array();

		foreach ($tags as $tag)
		{
			$ids[] = $tag->id;
		}

		$skipSection = '';

		if (!empty($value))
		{
			$skipSection = 'tag';
		}

		$productIds = $productSearch->getJustFilteredProductIds($collectionId, $skipSection);

		if (!empty($collectionProducts))
		{
			$tmp        = explode(',', $productIds);
			$tmp        = array_intersect($collectionProducts, $tmp);
			$productIds = implode(',', $tmp);
		}

		$db         = Factory::getDbo();
		$totalQuery = $db->getQuery(true);
		$totalQuery->select('COUNT(ptxTag.product_id) AS productCount')
			->select('ptxTag.tag_id')
			->from($db->qn('#__redshopb_product_tag_xref', 'ptxTag'))
			->leftJoin('#__redshopb_product AS p ON p.id = ptxTag.product_id')
			->where('ptxTag.tag_id IN (' . implode(',', $ids) . ')')
			->group('ptxTag.tag_id');

		if (empty($productIds))
		{
			$totalQuery->where('0 = 1');
		}
		else
		{
			$totalQuery->where($db->qn('ptxTag.product_id') . ' IN (' . $productIds . ')');
		}

		$oldTranslate  = $db->translate;
		$db->translate = false;
		$productCount  = $db->setQuery($totalQuery)
			->loadObjectList('tag_id');
		$db->translate = $oldTranslate;

		$results = array();

		foreach ($tags as $tag)
		{
			$tagGroup = $tag->type;

			if (!isset($results[$tagGroup]))
			{
				$newTagGroup               = new stdClass;
				$newTagGroup->count        = 0;
				$newTagGroup->totalCount   = 0;
				$newTagGroup->selectedTags = array();
				$newTagGroup->tags         = array();

				$results[$tagGroup] = $newTagGroup;
			}

			$option             = new stdClass;
			$option->value      = $tag->id;
			$option->text       = str_repeat('<span class="gi">|&mdash;</span>', $tag->level - 1) . ' ' . $tag->name;
			$option->selected   = false;
			$option->totalCount = $tag->totalCount;

			if (array_key_exists($tag->id, $productCount))
			{
				$option->count = $productCount[$tag->id]->productCount;
			}
			else
			{
				$option->count = 0;
			}

			if (!empty($value) && in_array($tag->id, $value))
			{
				$option->selected                   = true;
				$results[$tagGroup]->selectedTags[] = $option;
			}
			else
			{
				$results[$tagGroup]->tags[] = $option;
			}

			$results[$tagGroup]->count      += $option->count;
			$results[$tagGroup]->totalCount += $option->totalCount;
		}

		foreach ($results as &$result)
		{
			$result->selectedCount = count($result->selectedTags);
			$result->tags          = array_merge($result->selectedTags, $result->tags);
			unset($result->selectedTags);
		}

		return $results;
	}

	/**
	 * Method for count sub-categories of an category
	 *
	 * @param   int  $id  ID of category
	 *
	 * @return  integer       Count of sub-categories
	 */
	public static function checkChildExists($id)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->select('COUNT(id)')
			->from($db->qn('#__redshopb_category'))
			->where($db->qn('state') . ' = 1')
			->where($db->qn('parent_id') . ' = ' . (int) $id);

		return $db->setQuery($query)->loadResult();
	}

	/**
	 * Method for get manufacturers data for filter.
	 *
	 * @return  object  List of manufacturers.
	 */
	public function getManufacturers()
	{
		$app                   = Factory::getApplication();
		$input                 = $app->input;
		$result                = new stdClass;
		$result->selectedCount = 0;
		$result->manufacturers = array();
		$result                = new stdClass;
		$result->selectedCount = 0;
		$result->manufacturers = array();
		$collectionId          = array_filter($input->get('collection_id', array(), 'array'));

		$inputOption = $input->getCmd('option', '');
		$inputView   = $input->getCmd('view', '');
		$inputLayout = $input->getCmd('layout', '');

		$isProductListLayout = false;
		$categoryId          = 0;

		if ($inputOption == 'com_redshopb' && $inputView == 'shop' && $inputLayout == 'category')
		{
			$categoryId = $input->getInt('id', 0);
		}
		elseif ($inputOption == 'com_redshopb'
			&& $inputView == 'shop'
			&& in_array($inputLayout, array('productlist', 'productrecent', 'productfeatured'))
		)
		{
			$isProductListLayout = true;
		}

		$itemKey = $app->getUserState('shop.itemKey', 0);
		$value   = $app->getUserState('shop.manufacturer.' . $itemKey, array());

		/** @var RedshopbModelManufacturers $manufacturersModel */
		$manufacturersModel = RModel::getFrontInstance('Manufacturers', array('ignore_request' => true), 'com_redshopb');

		$manufacturersModel->setState('filter.current_view', $inputView);
		$manufacturersModel->setState('filter.state', 1);
		$manufacturersModel->setState('list.countProductsSelect', true);
		$manufacturersModel->setState('list.ordering', 'm.parent_id, m.name');

		$customerType       = $app->getUserState('shop.customer_type', '');
		$customerId         = $app->getUserState('shop.customer_id', 0);
		$productSearch      = new RedshopbDatabaseProductsearch;
		$collectionProducts = self::getCollectionProducts($customerId, $customerType);

		// If has term, then available categories already filtered in product search list
		if ($productSearch->hasTerm())
		{
			if ($categoryId)
			{
				$companyId = RedshopbHelperCompany::getCompanyIdByCustomer($customerId, $customerType);

				$availableCategories = RedshopbHelperACL::listAvailableCategories(
					Factory::getUser()->id, false, 100, $companyId, $collectionId, 'comma', ''
				);

				$availableCategories = explode(',', $availableCategories);

				if (!in_array($categoryId, $availableCategories))
				{
					$categoryId = 0;
				}

				$manufacturersModel->setState('filter.category', $categoryId);
			}
		}
		else
		{
			$companyId = RedshopbHelperCompany::getCompanyIdByCustomer($customerId, $customerType);

			$availableCategories = RedshopbHelperACL::listAvailableCategories(
				Factory::getUser()->id, false, 100, $companyId, $collectionId, 'comma', ''
			);

			$availableCategories = explode(',', $availableCategories);
			$availableCategories = (!$categoryId ? $availableCategories : array_intersect($availableCategories, array($categoryId)));

			if (empty($availableCategories))
			{
				$availableCategories[] = 0;
			}

			$manufacturersModel->setState('filter.category', $availableCategories);
		}

		if ($isProductListLayout)
		{
			if (!empty($collectionProducts))
			{
				$manufacturersModel->setState('filter.product_ids', $collectionProducts);
			}
			else
			{
				$productIds = explode(',', RedshopbHelperShop::getFilteredProductIds(false));
				$manufacturersModel->setState('filter.product_ids', $productIds);
			}
		}
		else
		{
			if (!empty($collectionProducts))
			{
				$manufacturersModel->setState('filter.product_ids', $collectionProducts);
			}

			$manufacturersModel->setState('filter.category', $categoryId);
		}

		$manufacturers = $manufacturersModel->getItems();

		if (empty($manufacturers))
		{
			return $result;
		}

		// If filter is not array, convert it to array
		if (!is_array($value))
		{
			$value = array($value);
		}

		// Clean up empty value
		$value = array_filter($value);

		$options = array();
		$ids     = array();

		foreach ($manufacturers as $manufacturer)
		{
			$ids[] = $manufacturer->id;
		}

		$db            = Factory::getDbo();
		$productSearch = new RedshopbDatabaseProductsearch;
		$skipSection   = '';

		if (!empty($value))
		{
			$skipSection = 'manufacturer';
		}

		$query      = $productSearch->getBaseSearchQuery();
		$productIds = $productSearch->getJustFilteredProductIds($collectionId, $skipSection);

		if (!empty($collectionProducts))
		{
			$tmp        = explode(',', $productIds);
			$tmp        = array_intersect($collectionProducts, $tmp);
			$productIds = implode(',', $tmp);
		}

		$query->clear('select')
			->select('COUNT(p.id) AS productCount')
			->select('p.manufacturer_id')
			->where('p.manufacturer_id IN (' . implode(',', $ids) . ')')
			->group('p.manufacturer_id');

		if (empty($productIds))
		{
			$query->where('0 = 1');
		}
		else
		{
			$query->where($db->qn('p.id') . ' IN (' . $productIds . ')');
		}

		$oldTranslate  = $db->translate;
		$db->translate = false;
		$productCount  = $db->setQuery($query)
			->loadObjectList('manufacturer_id');
		$db->translate = $oldTranslate;

		foreach ($manufacturers as $key => $manufacturer)
		{
			$option             = new stdClass;
			$option->value      = $manufacturer->id;
			$option->text       = str_repeat('<span class="gi">|&mdash;</span>', $manufacturer->level - 1) . ' ' . $manufacturer->name;
			$option->selected   = false;
			$option->totalCount = $manufacturer->totalCount;

			if (array_key_exists($manufacturer->id, $productCount))
			{
				$option->count = $productCount[$manufacturer->id]->productCount;
			}
			else
			{
				$option->count = 0;
			}

			if (!empty($value) && in_array($manufacturer->id, $value))
			{
				$option->selected = true;
			}

			$options[] = $option;
		}

		$result->manufacturers = $options;

		return $result;
	}

	/**
	 * Method for get price range from current product list.
	 *
	 * @param   object  $price  Price object data
	 *
	 * @return  boolean
	 */
	public function getPriceRange($price)
	{
		if (!$price || !is_object($price))
		{
			return false;
		}

		$productSearch = new RedshopbDatabaseProductsearch;
		$productIds    = $productSearch->getProductIdsForProductPrices();
		$customerId    = Factory::getApplication()->getUserState('shop.customer_id', 0);
		$customerType  = Factory::getApplication()->getUserState('shop.customer_type', '');
		$prices        = array();

		if (empty($productIds))
		{
			return false;
		}
		else
		{
			$collectionProducts = self::getCollectionProducts($customerId, $customerType);

			if (!empty($collectionProducts))
			{
				$productIds = array_intersect($collectionProducts, $productIds);
			}
		}

		if (!empty($productIds))
		{
			$prices = RedshopbHelperPrices::getProductsPrice($productIds, $customerId, $customerType);
		}

		if (empty($prices))
		{
			return false;
		}

		$priceValues = array();

		foreach ($prices as $productPrice)
		{
			$priceValues[] = (float) $productPrice->price;
		}

		$price->max = max($priceValues);
		$price->min = min($priceValues);
		$price->min = ($price->min == $price->max) ? 0 : $price->min;

		return true;
	}

	/**
	 * Method to get the filter module via ajax
	 *
	 * @return stdClass
	 */
	public static function updateFiltersAjax()
	{
		// Get module parameters
		jimport('joomla.application.module.helper');
		$module = ModuleHelper::getModule('redshopb_filter');
		$params = json_decode($module->params, true);
		$data   = new stdClass;

		ob_start();
		echo ModuleHelper::renderModule($module, $params);
		$data->html = ob_get_contents();
		ob_end_clean();

		return $data;
	}

	/**
	 * Method to get attribute filters
	 *
	 * @param   string  $itemKey  Session key postfix used to store values for this view
	 *
	 * @return  array             List of relative attribute filters
	 */
	public static function getAtrributeFilters($itemKey)
	{
		$app                = Factory::getApplication();
		$uniqueAttributes   = array();
		$customerType       = $app->getUserState('shop.customer_type', '');
		$customerId         = $app->getUserState('shop.customer_id', 0);
		$collectionProducts = self::getCollectionProducts($customerId, $customerType);
		$collectionId       = array_filter($app->input->get('collection_id', array(), 'array'));
		$productSearch      = new RedshopbDatabaseProductsearch;
		$productIds         = $productSearch->getCategoryProductIds();
		$filteredProductIds = $productSearch->getJustFilteredProductIds($collectionId);

		if (!empty($collectionProducts))
		{
			$productIds = $collectionProducts;
		}

		$db    = Factory::getDbo();
		$query = $db->getQuery(true);

		if (!empty($productIds))
		{
			$query->select('DISTINCT(name), type_id')
				->from('#__redshopb_product_attribute')
				->where('product_id IN (' . implode(',', $productIds) . ')');
			$uniqueAttributes = $db->setQuery($query)->loadObjectList();
		}

		$attributeFilters         = array();
		$selectedAttributeFilters = $app->getUserState('shop.attributefilter.' . $itemKey, array());

		if (!empty($uniqueAttributes) && !empty($productIds))
		{
			foreach ($uniqueAttributes AS $attribute)
			{
				$valueType = RedshopbEntityType::getInstance($attribute->type_id)->get('value_type', 'string_value');
				$valueType = ($valueType == 'field_value') ? 'string_value' : $valueType;

				$subQuery = $db->getQuery(true);

				$subQuery->select('id')
					->from('#__redshopb_product_attribute')
					->where('name =' . $db->q($attribute->name));

				$query->clear();
				$query->select('DISTINCT(' . $db->qn('a.' . $valueType) . ')')
					->from('#__redshopb_product_attribute_value AS a')
					->innerJoin('#__redshopb_product_item_attribute_value_xref AS ax ON a.id = ax.product_attribute_value_id')
					->innerJoin('#__redshopb_product_item AS pi ON ax.product_item_id = pi.id')
					->where('a.product_attribute_id IN (' . $subQuery . ')')
					->where('pi.product_id IN (' . implode(',', $productIds) . ')');

				$values                             = $db->setQuery($query)->loadColumn();
				$attributeFilters[$attribute->name] = array();

				$query->clear();
				$query->select($db->qn('av.' . $valueType, 'value') . ', COUNT(a.product_id) AS totalCount')
					->from($db->qn('#__redshopb_product_attribute_value', 'av'))
					->innerJoin($db->qn('#__redshopb_product_attribute', 'a') . ' ON av.product_attribute_id = a.id')
					->where('a.product_id IN (' . implode(',', $productIds) . ')')
					->where($db->qn('a.name') . ' = ' . $db->q($attribute->name))
					->group($db->qn('av.' . $valueType));

				$totalCount = $db->setQuery($query)->loadObjectList('value');

				$query->clear('where');
				$query->where($db->qn('a.name') . ' = ' . $db->q($attribute->name))
					->where('a.product_id IN (' . $filteredProductIds . ')');

				$count = $db->setQuery($query)->loadObjectList('value');

				foreach ($values as $value)
				{
					$valueCount      = 0;
					$valueTotalCount = 0;

					if (!empty($totalCount) && isset($totalCount[$value]))
					{
						$valueTotalCount = $totalCount[$value]->totalCount;
					}

					if (!empty($count) && isset($count[$value]))
					{
						$valueCount = $count[$value]->totalCount;
					}

					$selected = (!empty($selectedAttributeFilters[$attribute->name])
						&& in_array($value, $selectedAttributeFilters[$attribute->name]));

					$attributeFilters[$attribute->name][] = (object) array(
						'value' => $value,
						'count' => $valueCount,
						'total' => $valueTotalCount,
						'selected' => $selected);
				}
			}
		}

		return $attributeFilters;
	}

	/**
	 * Collection products setter.
	 *
	 * @param   int     $customerId    Customer id.
	 * @param   string  $customerType  Customer type.
	 *
	 * @return  array   Collection products.
	 */
	public static function getCollectionProducts($customerId, $customerType)
	{
		$input       = Factory::getApplication()->input;
		$collections = array_filter($input->get('collection_id', array(), 'array'));

		if (empty($collections))
		{
			$collections = RedshopbHelperCollection::getCustomerCollectionsForShop($customerId, $customerType);
		}

		if (!empty($collections) && !empty($customerId) && !empty($customerType)
			&& RedshopbHelperShop::inCollectionMode(
				RedshopbEntityCompany::getInstance(
					RedshopbHelperCompany::getCompanyIdByCustomer($customerId, $customerType)
				)
			)
		)
		{
			$hash = md5($customerId . '_' . $customerType);
			$db   = Factory::getDbo();

			if (empty(self::$collectionProducts[$hash]))
			{
				$productSearch = new RedshopbDatabaseProductsearch;
				$query         = $productSearch->getBaseSearchQuery($customerId, $customerType)
					->clear('select')
					->select(array($db->qn('p.id', 'pid'), $db->qn('wpx.collection_id', 'cid')))
					->innerJoin($db->qn('#__redshopb_collection_product_xref', 'wpx') . ' ON ' . $db->qn('wpx.product_id') . ' = ' . $db->qn('p.id'))
					->where($db->qn('wpx.collection_id') . ' IN (' . implode(',', $collections) . ')')
					->group($db->qn('p.id'));
				$productSearch->filterByCategory($query, $customerId, $customerType);

				self::$collectionProducts[$hash] = $db->setQuery($query)->loadObjectList();
			}

			$products = array();

			if (!empty(self::$collectionProducts[$hash]))
			{
				foreach (self::$collectionProducts[$hash] as $product)
				{
					$products[] = $product->pid;
				}
			}

			return $products;
		}

		return array();
	}

	/**
	 * Method to figure out if the campaign price filter should be available
	 *
	 * @return  boolean
	 */
	public static function getCampaignPriceAvailability()
	{
		$app          = Factory::getApplication();
		$customerType = $app->getUserState('shop.customer_type', '');
		$customerId   = $app->getUserState('shop.customer_id', 0);
		$productIds   = self::getCollectionProducts($customerId, $customerType);

		if (empty($productIds))
		{
			$productSearch = new RedshopbDatabaseProductsearch;
			$productIds    = $productSearch->getCategoryProductIds();
		}

		if (empty($productIds))
		{
			return false;
		}

		$db = Factory::getDbo();

		$query = $db->getQuery(true)
			->select('count(*)')
			->from($db->qn('#__redshopb_product'))
			->where($db->qn('campaign') . ' = ' . $db->q(1))
			->where($db->qn('id') . ' IN (' . implode(',', $productIds) . ')');

		$campaignPriceNumber = $db->setQuery($query)->loadResult();

		return $campaignPriceNumber > 0;
	}
}
