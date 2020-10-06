<?php
/**
 * @package     Aesir.E-Commerce.Site
 * @subpackage  mod_redshopb_categories
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;

JLoader::import('redshopb.library');

$redshopbConfig = RedshopbApp::getConfig();
RHtmlMedia::setFramework($redshopbConfig->getString('default_frontend_framework', 'bootstrap3'));

$lang = Factory::getLanguage();
$lang->load('mod_redshopb_categories', __DIR__);

// Include the syndicate functions only once
require_once __DIR__ . '/helper.php';

$list = ModRedshopbCategoriesHelper::getList($params);

if (is_object($list) && isset($list->redShopBCategories))
{
	$classSfx     = htmlspecialchars($params->get('class_sfx'));
	$showAll      = $params->get('showAllChildren');
	$moduleLayout = RModuleHelper::getLayoutPath('mod_redshopb_categories', $params->get('layout', 'default'));
	require $moduleLayout;
}
