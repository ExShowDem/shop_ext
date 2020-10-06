<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  mod_redshopb_sidebar
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
$lang->load('mod_redshopb_sidebar', __DIR__);

require_once dirname(__FILE__) . '/helper.php';
$helper = new ModRedshopbSidebarHelper;

HTMLHelper::stylesheet('mod_redshopb_sidebar/mod_redshopb_sidebar.css', array('relative' => true));

$moduleLayout = RModuleHelper::getLayoutPath('mod_redshopb_sidebar');
require $moduleLayout;
