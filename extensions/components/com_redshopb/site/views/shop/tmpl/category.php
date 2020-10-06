<?php
/**
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Views
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;

HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('vnrbootstrap.tooltip');
HTMLHelper::_('rholder.holder');
HTMLHelper::_('rjquery.chosen', 'select');
HTMLHelper::_('rsortablelist.main');
RHelperAsset::load('shop.css', 'com_redshopb');

$app = Factory::getApplication();

$action             = RedshopbRoute::_('index.php?option=com_redshopb&view=shop&layout=category&id=' . $this->category->id);
$showAs             = $app->getUserState('shop.show.category.ProductsAs', 'list');
$productsPerPage    = (int) $app->input->getInt(
	'product_shop_limit', $app->getUserState('shop.productLimit', RedshopbApp::getConfig()->get('shop_products_per_page', 12))
);
$productsPagesCount = ceil($this->productsCount / $productsPerPage);

if (!empty($this->collections))
	:
	$productsPagesCount = ceil($this->maxPWCollections / $productsPerPage);
endif;

$xml                  = $this->filterForm->getXml();
$allowedOrderByFields = (array) RedshopbApp::getConfig()->get('category_allowed_order_by', array('name', 'sku', 'price'));

/** @var FormField $sortByField */
$sortByField = $this->filterForm->getField('sort_by');

/** @var FormField $sortDirField */
$sortDirField = $this->filterForm->getField('sort_dir');

/** @var FormField $productShowField */
$productShowField = $this->filterForm->getField('product_category_limit');

if (!empty($productsPerPage))
	:
	$productShowField->setValue($productsPerPage);
endif;

$extThis           = $this;
$cartPrefix        = 'inCat' . $this->category->id;
$fieldsData        = RedshopbHelperField::loadScopeFieldData('category', $this->category->id, 0, true);
$categoriesPerPage = RedshopbApp::getConfig()->get('shop_categories_per_page', 12);
$collectionId      = $app->input->getInt('collection_id', 0);

// @TODO: make sure that $this->categoriesCount is stable and is always from the same type
if (empty($categoriesPerPage) || !$categoriesPerPage || is_array($this->categoriesCount))
{
	$categoriesPagesCount = 1;
}
else
{
	$categoriesPagesCount = ceil($this->categoriesCount / $categoriesPerPage);
}

// Main entity to determine how to use the template
$mainTemplateEntity = RedshopbEntityCategory::load($this->category->id);
echo RedshopbHelperTemplate::renderTemplate('category', 'shop', $this->category->template_id, compact(array_keys(get_defined_vars())));
