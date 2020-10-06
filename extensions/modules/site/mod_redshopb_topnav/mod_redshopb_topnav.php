<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  mod_redshopb_topnav
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;

JLoader::import('redshopb.library');

$redshopbConfig = RedshopbApp::getConfig();
RHtmlMedia::setFramework($redshopbConfig->getString('default_frontend_framework', 'bootstrap3'));

$lang = Factory::getLanguage();
$lang->load('mod_redshopb_topnav', __DIR__);

// Import the com_menus helper.
require_once realpath(JPATH_ADMINISTRATOR . '/components/com_menus/helpers/menus.php');

$app = Factory::getApplication();

require_once dirname(__FILE__) . '/helper.php';
$helper = new ModRedshopbTopnavHelper;

$menuItems = $helper->getMenuItems(($params->get('menu_include', '0') == '1' ? true : false), $params->get('menu', ''));

HTMLHelper::stylesheet('mod_redshopb_topnav/mod_redshopb_topnav.css', array('relative' => true));

/** @var RedshopbModelShop $model */
$model  = RModelAdmin::getInstance('Shop', 'RedshopbModel');
$offers = RedshopbHelperShop::areThereCampaignItems();

$moduleLayout = RModuleHelper::getLayoutPath('mod_redshopb_topnav');
require $moduleLayout;
