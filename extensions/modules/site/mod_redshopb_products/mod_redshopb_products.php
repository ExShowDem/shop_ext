<?php
/**
 * @package     Aesir.E-Commerce.Site
 * @subpackage  mod_redshopb_megamenu
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;

JLoader::import('redshopb.library');

$redshopbConfig = RedshopbApp::getConfig();
RHtmlMedia::setFramework($redshopbConfig->getString('default_frontend_framework', 'bootstrap3'));

$lang = Factory::getLanguage();
$lang->load('mod_redshopb_products', __DIR__);

// Include the syndicate functions only once
require_once __DIR__ . '/helper.php';

$session  = Factory::getSession();
$input    = Factory::getApplication()->input;
$moduleId = $input->getInt('module_id', 0);
$typeId   = $input->getCmd('type_id', '');

if ($moduleId && $typeId)
{
	$session->set('mod_redshopb_products.' . $moduleId, $typeId);
}

$types              = $params->get('types', array());
$currentType        = $session->get('mod_redshopb_products.' . $module->id, $types[0]);
$collectionProducts = ModRedshopbProduct::getList($params, $currentType);

if (count($collectionProducts) > 0 && count($collectionProducts[key($collectionProducts)]) > 0 || count($types) > 1)
{
	$config = RedshopbApp::getConfig();
	$isShop = RedshopbHelperPrices::displayPrices();
	$doc    = Factory::getDocument();
	HTMLHelper::stylesheet('mod_redshopb_products/mod_redshopb_products.css', array('relative' => true));
	HTMLHelper::script('com_redshopb/redshopb.shop.js', array('framework' => false, 'relative' => true));

	$moduleLayout = RModuleHelper::getLayoutPath('mod_redshopb_products', $params->get('layout', 'default'));
	require $moduleLayout;
}
