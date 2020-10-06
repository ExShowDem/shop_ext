<?php
/**
 * @package     Aesir.E-Commerce.Site
 * @subpackage  mod_redshopb_category_list
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;

JLoader::import('redshopb.library');

$redshopbConfig = RedshopbApp::getConfig();
RHtmlMedia::setFramework($redshopbConfig->getString('default_frontend_framework', 'bootstrap3'));

$lang = Factory::getLanguage();
$lang->load('mod_redshopb_category_list', __DIR__);

// Include the syndicate functions only once
require_once __DIR__ . '/helper.php';

$categories = ModRedshopbCategoryListHelper::getList($params);

if (!empty($categories))
{
	$moduleClassSuffix = htmlspecialchars($params->get('moduleclass_sfx'));

	$moduleLayout = RModuleHelper::getLayoutPath('mod_redshopb_category_list', $params->get('layout', 'default'));
	require $moduleLayout;
}
