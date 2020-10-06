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
use Joomla\CMS\Language\Text;

JLoader::import('redshopb.library');

$redshopbConfig = RedshopbApp::getConfig();
RHtmlMedia::setFramework($redshopbConfig->getString('default_frontend_framework', 'bootstrap3'));

$lang            = Factory::getLanguage();
$app             = Factory::getApplication();
$currentView     = $app->input->getCmd('view', 'shop');
$currentLayout   = $app->input->getCmd('layout', $app->getUserState('shop.layout', ''));
$loadedFromAjax  = $app->input->getCmd('module', '');
$customerType    = $app->getUserState('shop.customer_type', '');
$customerId      = $app->getUserState('shop.customer_id', 0);
$filterFieldsets = $app->input->get('filter', array(), 'array');

// Limit this filter just work on "Shop" view and product list view.
if ($currentView != 'shop' || !in_array($currentLayout, array('category', 'productlist', 'manufacturer', 'productrecent', 'productfeatured')))
{
	return;
}

$lang->load('mod_redshopb_filter', __DIR__);

$resetEnabled        = (boolean) $params->get('show_reset', 1);
$categoryEnabled     = (boolean) $params->get('category_enable', 1);
$searchEnabled       = (boolean) $params->get('search_enable', 1);
$tagEnabled          = (boolean) $params->get('tag_enable', 1);
$manufacturerEnabled = (boolean) $params->get('manufacturer_enable', 1);
$campaignEnabled     = (boolean) $params->get('campaign_price_enable', 0);
$priceEnabled        = (boolean) $params->get('price_enable', 1);
$stockEnabled        = (boolean) $params->get('stock_enable', 0);
$moduleClassSuffix   = htmlspecialchars($params->get('moduleclass_sfx'));


require_once dirname(__FILE__) . '/helper.php';
$helper = new ModRedshopbFilterHelper;
$return = '';

$upperLimit = $lang->getUpperLimitSearchWord();
$maxlength  = $upperLimit;

$optionCount = (int) $params->get('number_options', 0);
$categoryId  = ($currentView == 'shop' && $currentLayout == 'category') ? $app->input->getInt('id', 0) : 0;

$itemKey = $app->getUserState('shop.itemKey', 0);

if (!$itemKey)
{
	$itemKey = $currentLayout . '_' . $app->input->getInt('id', 0);
}

if ($resetEnabled)
{
	$resetText = $params->get('reset_text', Text::_('MOD_REDSHOPB_FILTER_RESET_TEXT'));
}

$pinFilters    = array();
$normalFilters = array();

$searchString = $app->input->getString(
	'search',
	$app->getUserState('mod_filter.search.' . $itemKey,
		''
	)
);

// Category filter
if ($categoryEnabled)
{
	$category        = new stdClass;
	$category->title = $params->get('category_title', Text::_('MOD_REDSHOPB_FILTER_CATEGORY_TITLE'));

	if (!empty($searchString))
	{
		$category->categories = $helper->getCategories($params->get('default_category', 0));
	}
}

// Search box filter
if ($searchEnabled)
{
	$search        = new stdClass;
	$search->title = $params->get('search_title', Text::_('MOD_REDSHOPB_FILTER_SEARCHBOX_TITLE'));
	$search->hint  = $params->get('search_hint', Text::_('MOD_REDSHOPB_FILTER_SEARCHBOX_HINT'));
	$search->width = $params->get('search_width', 'auto');

	$search->value = $searchString;

	if ((boolean) $params->get('search_pin', 0))
	{
		$pinFilters[] = 'default_searchbox';
	}
	else
	{
		$normalFilters[] = 'default_searchbox';
	}
}
elseif ($itemKey == 'productlist_0')
{
	$search        = new stdClass;
	$search->value = $searchString;

	$normalFilters[] = 'default_searchbox';
}
else
{
	$app->setUserState('mod_filter.search.' . $itemKey, '');

	$search        = new stdClass;
	$search->value = '';

	if (count($normalFilters) > 0 && count($pinFilters) > 0)
	{
		$normalFilters[] = 'default_searchbox';
	}
}


// Tag filter
if ($tagEnabled)
{
	$tagTypeExclude = $params->get('tag_type_exclude', array());
	$tag            = new stdClass;
	$tag->title     = $params->get('tag_title', '');
	$tag->list      = $helper->getTags($tagTypeExclude);

	if (!empty($tag->list))
	{
		if ((boolean) $params->get('tag_pin', 0))
		{
			$pinFilters[] = 'default_tag';
		}
		else
		{
			$normalFilters[] = 'default_tag';
		}
	}
}

// Manufacturer filter

if ($manufacturerEnabled)
{
	$manufacturerResult = $helper->getManufacturers();

	$manufacturer                = new stdClass;
	$manufacturer->title         = $params->get('manufacturer_title', '');
	$manufacturer->items         = $manufacturerResult->manufacturers;
	$manufacturer->selectedCount = $manufacturerResult->selectedCount;
	$manufacturer->value         = $app->getUserState('shop.manufacturer.' . $itemKey, array());

	unset($manufacturerResult);

	if (!empty($manufacturer->items))
	{
		if ((boolean) $params->get('manufacturer_pin', 0))
		{
			$pinFilters[] = 'default_manufacturer';
		}
		else
		{
			$normalFilters[] = 'default_manufacturer';
		}
	}
}

// Campaign Price filter

if ($campaignEnabled)
{
	$campaignPricesAvaiable = ModRedshopbFilterHelper::getCampaignPriceAvailability();

	if ($campaignPricesAvaiable)
	{
		$campaignPrice        = new stdClass;
		$campaignPrice->title = $params->get('campaign_price_title', '');
		$campaignPrice->value = $app->getUserState('shop.campaign_price.' . $itemKey, 0);

		if ((boolean) $params->get('campaign_price_pin', 0))
		{
			$pinFilters[] = 'default_campaignprice';
		}
		else
		{
			$normalFilters[] = 'default_campaignprice';
		}
	}
}

// Price filter
$shouldDisplayPrice = RedshopbHelperPrices::displayPrices();

if ($priceEnabled && $shouldDisplayPrice)
{
	$price        = new stdClass;
	$price->title = $params->get('price_title', Text::_('MOD_REDSHOPB_FILTER_PRICE_TITLE'));
	$price->min   = $params->get('price_min', null);
	$price->max   = $params->get('price_max', null);

	if (!$price->min && !$price->max)
	{
		$helper->getPriceRange($price);
	}

	$price->value = $app->getUserState('shop.price_range.' . $itemKey);

	if ((boolean) $params->get('price_pin', 0))
	{
		$pinFilters[] = 'default_price';
	}
	else
	{
		$normalFilters[] = 'default_price';
	}
}

// Stock filter
if ($stockEnabled)
{
	$stock        = new stdClass;
	$stock->title = $params->get('stock_title', '');
	$stock->value = $app->getUserState('shop.in_stock.' . $itemKey, 0);

	if ((boolean) $params->get('stock_pin', 0))
	{
		$pinFilters[] = 'default_stock';
	}
	else
	{
		$normalFilters[] = 'default_stock';
	}
}

// Filter fieldset
$hasFilterFieldset     = false;
$filterFieldsetEnable  = (boolean) $params->get('fieldset_enable', 1);
$oldCategories         = $app->getUserState('shop.categoryfilter.' . $itemKey, array());
$filterCategories      = $app->input->get('filter_category', $app->getUserState('shop.categoryfilter.' . $itemKey, array()), 'array');
$isMultipleCategories  = (count($filterCategories) > 1);
$isDifferentCategories = (json_encode($filterCategories) !== json_encode($oldCategories));

if (!$isMultipleCategories && !$isDifferentCategories)
{
	// Set filter data values in case client remove some filter.
	foreach ($filterFieldsets as $filter => $filterData)
	{
		$filterData = RedshopbHelperFilter::filterFields($filterData);
		RedshopbHelperFilter::setFilterDataToSession($filter, $filterData, 'filter.' . $itemKey);
	}
}

$canShow    = ($filterFieldsetEnable && $currentView == 'shop');
$shouldShow = (($currentLayout == 'category') || (count($filterCategories) == 1));

if ($canShow && $shouldShow)
{
	$customerType = $app->getUserState('shop.customer_type', '');
	$customerId   = $app->getUserState('shop.customer_id', 0);
	$productIds   = ModRedshopbFilterHelper::getCollectionProducts($customerId, $customerType);

	if (empty($productIds))
	{
		$productSearch = new RedshopbDatabaseProductsearch;
		$productIds    = $productSearch->getCategoryProductIds();
	}

	// Looks out for the current category, to avoid showing this filter when no products are present in the view
	$hideNoProducts  = (boolean) $params->get('hide_noproducts', 1);
	$productCategory = $categoryId;

	if (empty($categoryId) && $currentLayout == 'manufacturer')
	{
		$productCategory = $filterCategories[0];
	}

	if ($hideNoProducts)
	{
		/** @var RedshopbModelShop $model */
		$model        = RModelAdmin::getInstance('Shop', 'RedshopbModel', array('ignore_request' => true));
		$customerType = $app->getUserState('shop.customer_type', '');
		$customerId   = $app->getUserState('shop.customer_id', 0);
		$model->setState('disable_user_states', true);

		if ($params->get('fieldset_load_sub_category', false))
		{
			$productCategories = RedshopbEntityCategory::load($productCategory)->getChildrenIds();
			array_push($productCategories, $productCategory);
			$model->setState('filter.product_category', $productCategories);
		}
		else
		{
			$model->setState('filter.product_category', $productCategory);
		}

		if (RedshopbHelperShop::inCollectionMode(
			RedshopbEntityCompany::getInstance(
				RedshopbHelperCompany::getCompanyIdByCustomer($customerId, $customerType)
			)
		)
		)
		{
			$model->setState('product_collection', RedshopbHelperCollection::getCustomerCollectionsForShop($customerId, $customerType));
		}

		$db = Factory::getDbo();
		$model->setState('isTotal', true);
		$query = $model->getListQueryOnly();
		$model->setState('isTotal', false);

		if ($query)
		{
			$query->clear('select')
				->clear('order')
				->clear('group')
				->select('p.id');

			if (!(int) $db->setQuery($query, 0, 1)->loadResult())
			{
				return;
			}
		}
	}

	// Get filter fieldsets
	if (!empty($productIds))
	{
		$filterFieldsets = RedshopbHelperFilter::prepareFiltersFromProducts(
			$productIds,
			$productCategory,
			'redSHOPB.shop.filters.filterProductList(event);',
			'filter.' . $itemKey
		);

		$hasFilterFieldset = true;

		$pinFieldsets = $params->get('filter_fieldset_pin', array());

		if (!empty($pinFieldsets))
		{
			foreach ($filterFieldsets as $var)
			{
				if (!empty($var->filters))
				{
					$pinFilters[] = 'default_filterfieldsetpin';
					break;
				}
			}

			$pinnedFilterFieldsets = array();

			foreach ($filterFieldsets as $index => $filterFieldset)
			{
				if (in_array($filterFieldset->id, $pinFieldsets))
				{
					$pinnedFilterFieldsets[] = $filterFieldset;
					unset($filterFieldsets[$index]);
				}
			}
		}

		foreach ($filterFieldsets as $var)
		{
			if (!empty($var->filters))
			{
				$normalFilters[] = 'default_filterfieldset';
				break;
			}
		}
	}
}

// Filter Attributes
$hasAttributesFieldset  = false;
$attributeFiltersEnable = (boolean) $params->get('attributes_enable', 1);
$canShowAttributes      = ($attributeFiltersEnable && $currentView == 'shop');
$shouldShowAttributes   = (($currentLayout == 'category') || (count($filterCategories) == 1));

if ($canShowAttributes && $shouldShowAttributes)
{
	$attributeFilters = ModRedshopbFilterHelper::getAtrributeFilters($itemKey);

	if (!empty($attributeFilters))
	{
		$normalFilters[] = 'default_filterattributes';
	}
}

$filters = array_merge($pinFilters, $normalFilters);

if (!$app->input->get('lazyloaded', false))
{
	if (count($normalFilters) || count($pinFilters))
	{
		$moduleLayout = RModuleHelper::getLayoutPath('mod_redshopb_filter', '_:default');
		require $moduleLayout;
	}

	return;
}

if ($currentView == 'shop' && in_array($currentLayout, array('category', 'productlist', 'manufacturer')))
{
	$return = 'index.php?option=com_redshopb&view=' . $currentView . '&layout=' . $currentLayout;

	if ($currentLayout == 'category' || $currentLayout == 'manufacturer')
	{
		$return .= '&id=' . $app->input->getCmd('id', '');
	}
}

if ($return == '')
{
	$return = 'index.php?option=com_redshopb&view=shop&layout=productlist';
}

$return            = base64_encode($return);
$moduleClassSuffix = htmlspecialchars($params->get('moduleclass_sfx'));

$layout = $params->get('layout', 'default');

if ($layout == '_:default')
{
	$layout = '_:default_form';
}

if (count($filters))
{
	$moduleLayout = RModuleHelper::getLayoutPath('mod_redshopb_filter', $layout);
	require $moduleLayout;
}
