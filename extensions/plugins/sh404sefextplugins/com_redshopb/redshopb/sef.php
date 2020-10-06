<?php
/**
 * @package    Redshopb.sh404SEF
 *
 * @copyright  Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

// @codingStandardsIgnoreFile

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\String\StringHelper;

// ------------------  standard plugin initialize function - don't change ---------------------------
$sefConfig      = Sh404sefFactory::getConfig();
$shLangName     = '';
$shLangIso      = '';
$title          = array();
$shItemidString = '';
$dosef          = shInitializePlugin($lang, $shLangName, $shLangIso, $option);

if ($dosef == false)
{
	return;
}

$textPrefix = 'PLG_SH404SEFEXTPLUGINS_COM_REDSHOPB_';
$lang = Factory::getLanguage();

// Remove common URL from GET vars list, so that they don't show up as query string in the URL
shRemoveFromGETVarsList('option');
shRemoveFromGETVarsList('lang');

if (!empty($Itemid))
{
	shRemoveFromGETVarsList('Itemid');
}

if (!empty($limit))
{
	shRemoveFromGETVarsList('limit');
}

// Limitstart can be zero
if (isset($limitstart))
{
	shRemoveFromGETVarsList('limitstart');
}
else
{
	$limitstart = 0;
}

$view          = isset($view) ? $view : null;
$task          = isset($task) ? $task : null;
$layout        = isset($layout) ? $layout : null;
$Itemid        = isset($Itemid) ? $Itemid : null;
$id            = isset($id) ? $id : null;
$db            = Factory::getDbo();
$taskValues    = array();
$transformTask = false;

if (!$view)
{
	$taskValues = explode('.', $task);

	if (count($taskValues) == 2)
	{
		$view          = $taskValues[0];
		$transformTask = true;
	}
}

$textView = $textPrefix . strtoupper($view);

switch ($view)
{
	case 'shop':
		if (!$layout)
		{
			$layout = 'default';
		}

		$textKey = $textView . '_' . strtoupper($layout);

		switch ($layout)
		{
			case 'collection':
				shRemoveFromGETVarsList('view');
				shRemoveFromGETVarsList('layout');

				$title[] = Text::_('PLG_SH404SEFEXTPLUGINS_COM_REDSHOPB_SHOP_COLLECTION');

				if ($id)
				{
					$collection = RedshopbEntityCollection::getInstance($id);
					$title[]    = $collection->get('alias');

					shRemoveFromGETVarsList('id');
				}

				break;

			case 'category':
				shRemoveFromGETVarsList('view');
				shRemoveFromGETVarsList('layout');

				if ($id)
				{
					$query   = $db->getQuery(true)
						->select('parent.alias')
						->from($db->qn('#__redshopb_category', 'node'))
						->leftJoin($db->qn('#__redshopb_category', 'parent') . ' ON node.lft BETWEEN parent.lft AND parent.rgt')
						->where('node.id = ' . (int) $id)
						->where('parent.level > 0')
						->order($db->qn('parent.lft'));
					$results = $db->setQuery($query)->loadColumn();

					if ($results)
					{
						foreach ($results as $result)
						{
							$title[] = RedshopbHelperRoute::replaceNonURLSymbols($result);
						}

						shRemoveFromGETVarsList('id');
					}
				}
				else
				{
					$title[] = Text::_('PLG_SH404SEFEXTPLUGINS_COM_REDSHOPB_SHOP_CATEGORY');
				}

				break;

			case 'product':
				shRemoveFromGETVarsList('view');
				shRemoveFromGETVarsList('layout');

				$result = RedshopbHelperProduct::loadProduct($id);

				if ($id && $result)
				{
					$app            = Factory::getApplication();
					$isShowCategory = true;

					$isCollectionShopping = RedshopbHelperShop::inCollectionMode(RedshopbEntityCompany::getInstance(
						RedshopbHelperCompany::getCompanyIdByCustomer(
							$app->getUserState('shop.customer_id', 0),
							$app->getUserState('shop.customer_type', '')
						)
					));

					$collectionIncludeCategories = RedshopbApp::getConfig()->getBool('seo_collection_include_category', false);

					if ($isCollectionShopping && $collectionIncludeCategories === false)
					{
						$isShowCategory = false;
					}

					if ($isShowCategory && !empty($result->categories))
					{
						$categoryId = 0;

						// Get category_id from the current link
						if (isset($category_id) && in_array($category_id, $result->categories))
						{
							$categoryId = $category_id;
						}
						// Get first relate category - it is a main category
						else
						{
							$productCategories = (array) $result->categories;
							$categoryId = $productCategories[0];
						}

						if ($categoryId)
						{
							$query      = $db->getQuery(true)
								->select('parent.alias')
								->from($db->qn('#__redshopb_category', 'node'))
								->leftJoin($db->qn('#__redshopb_category', 'parent') . ' ON node.lft BETWEEN parent.lft AND parent.rgt')
								->where('node.id = ' . (int) $categoryId)
								->where('parent.level > 0')
								->order($db->qn('parent.lft'));
							$categories = $db->setQuery($query)->loadColumn();

							if ($categories)
							{
								foreach ($categories as $oneCategory)
								{
									$title[] = RedshopbHelperRoute::replaceNonURLSymbols($oneCategory);
								}
							}
						}
					}

					if (isset($collection) && $collection)
					{
						$query          = $db->getQuery(true)
							->select('c.name')
							->from($db->qn('#__redshopb_collection', 'c'))
							->leftJoin($db->qn('#__redshopb_collection_product_xref', 'cpx') . ' ON cpx.collection_id = c.id')
							->where('c.id = ' . $collection)
							->where('cpx.product_id = ' . (int) $id);
						$collectionName = $db->setQuery($query, 0, 1)->loadResult();

						if ($collectionName)
						{
							$title[] = RedshopbHelperRoute::replaceNonURLSymbols($collectionName);
						}
					}

					$alias = RedshopbHelperRoute::replaceNonURLSymbols($result->alias);

					// Make sure we still have a unique path for a product
					if (!$alias)
					{
						$alias = $id;
					}

					$title[] = $alias;
					shRemoveFromGETVarsList('id');
					shRemoveFromGETVarsList('category_id');
					shRemoveFromGETVarsList('collection');

					if (isset($print))
					{
						$title[] = Text::_('PLG_SH404SEFEXTPLUGINS_COM_REDSHOPB_PRODUCT_PRINT');
						shRemoveFromGETVarsList('print');
						shRemoveFromGETVarsList('tmpl');
					}
				}
				else
				{
					$title[] = Text::_('PLG_SH404SEFEXTPLUGINS_COM_REDSHOPB_SHOP_PRODUCT');
				}

				break;

			case 'manufacturer':
				$manufacturerListItem = Factory::getApplication()->getMenu()->getItems('link', 'index.php?option=com_redshopb&view=manufacturerlist', true);

				if (!empty($manufacturerListItem))
				{
					$title[] = RedshopbHelperRoute::replaceNonURLSymbols($manufacturerListItem->title);
				}
				else
				{
					$title[] = Text::_('PLG_SH404SEFEXTPLUGINS_COM_REDSHOPB_SHOP_MANUFACTURERS');
				}

				if ($id)
				{
					$manufacturer = RedshopbEntityManufacturer::getInstance($id);

					if ($manufacturer->isValid())
					{
						$title[] = RedshopbHelperRoute::replaceNonURLSymbols($manufacturer->get('alias'));

						shRemoveFromGETVarsList('id');
						shRemoveFromGETVarsList('layout');
						shRemoveFromGETVarsList('view');
					}
				}

				break;

			case 'productlist':
				if ($lang->hasKey($textKey))
				{
					$title[] = Text::_($textKey);
				}
				else
				{
					$title[] = $layout;
				}

				shRemoveFromGETVarsList('view');
				shRemoveFromGETVarsList('layout');

				break;

			case 'categories':
			case 'receipt':
			case 'pay':
			case 'delivery':
			case 'shipping':
			case 'payment':
			case 'confirm':
			case 'cart':
			case 'default':
			default:
				if ($lang->hasKey($textKey))
				{
					$title[] = Text::_($textKey);
				}
				else
				{
					$title[] = $layout;
				}

				shRemoveFromGETVarsList('view');
				shRemoveFromGETVarsList('layout');

				break;
		}

		if (isset($company_id))
		{
			$title[] = (int) $company_id;
			shRemoveFromGETVarsList('company_id');
		}

		if (isset($department_id))
		{
			$title[] = (int) $department_id;
			shRemoveFromGETVarsList('department_id');
		}

		if (isset($rsbuser_id))
		{
			$title[] = (int) $rsbuser_id;
			shRemoveFromGETVarsList('rsbuser_id');
		}

		break;

	case 'cart':
		$title[] = Text::_($textView);
		shRemoveFromGETVarsList('view');
		break;

	default:
		if ($lang->hasKey($textView))
		{
			$title[] = Text::_($textView);
			shRemoveFromGETVarsList('view');
		}
		elseif ($view)
		{
			$title[] = $view;
			shRemoveFromGETVarsList('view');

			if ($layout)
			{
				$title[] = $layout;
				shRemoveFromGETVarsList('layout');
			}
		}
		break;
}

if ($transformTask)
{
	$title[] = $taskValues[1];
	$title[] = Text::_('PLG_SH404SEFEXTPLUGINS_COM_REDSHOPB_PRODUCT_TASK');
	shRemoveFromGETVarsList('task');
}

if (count($title) == 0)
{
	$shSampleName = shGetComponentPrefix('com_redshopb');
	$shSampleName = empty($shSampleName) ?
		getMenuTitle($option, $task, $Itemid, null, $shLangName) : $shSampleName;
	$shSampleName = (empty($shSampleName) || $shSampleName == '/') ? 'redshopb' : $shSampleName;

	if (trim($shSampleName) != "")
	{
		$title[] = $shSampleName;
	}
}

if ($limitstart)
{
	if (!isset($limit))
	{
		// Get limit in Joomla configuration
		$limit = Factory::getApplication()->get('list_limit');
	}

	$title[] = 'results' . ($limitstart + 1) . '-' . ($limitstart + $limit);
}

// ------------------  standard plugin finalize function - don't change ---------------------------
if ($dosef)
{
	$string = shFinalizePlugin(
		$string, $title, $shAppendString, $shItemidString,
		(isset($limit) ? $limit : null), $limitstart,
		$shLangName,
		(isset($showall) ? $showall : null), $suppressPagination = true
	);

	$db    = Factory::getDbo();
	$query = $db->getQuery(true)
		->select('*')
		->from($db->qn('#__sh404sef_urls'))
		->where('oldurl = ' . $db->q($string))
		->order('rank ASC');

	$items = $db->setQuery($query)->loadObjectList();

	if (!empty($items) && count($items) > 1)
	{
		array_shift($items);

		// Omit duplicates, try to save each link separately
		foreach ($items as $item)
		{
			do
			{
				$item->oldurl = StringHelper::increment($item->oldurl, 'dash');

				$query  = $db->getQuery(true)
					->select('id')
					->from($db->qn('#__sh404sef_urls'))
					->where('oldurl = ' . $db->q($item->oldurl));

				$subQuery = $db->getQuery(true)
					->select('id')
					->from($db->qn('#__sh404sef_aliases'))
					->where('alias = ' . $db->q($item->oldurl));

				$query->union($subQuery);
			}
			while ($db->setQuery($query, 0, 1)->loadResult());

			$item->rank = 0;

			$db->updateObject('#__sh404sef_urls', $item, ['id']);

			Sh404sefHelperCache::removeUrlFromCache([$item->newurl]);
		}
	}
}

// ------------------  standard plugin finalize function - don't change ---------------------------
