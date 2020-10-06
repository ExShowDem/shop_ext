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

// Load module language
$lang = Factory::getLanguage();
$lang->load('mod_redshopb_megamenu', __DIR__);

// Include the syndicate functions only once
require_once __DIR__ . '/helper.php';

$active = ModRedshopbMegaMenuHelper::getActive();
$list   = ModRedshopbMegaMenuHelper::getList($params);

if (count($list))
{
	$activeId = $active->id;
	$path     = $active->tree;
	$classSfx = htmlspecialchars($params->get('class_sfx'));
	HTMLHelper::stylesheet('mod_redshopb_megamenu/mega.css', array('relative' => true));
	HTMLHelper::script('mod_redshopb_megamenu/mega.js', array('framework' => false, 'relative' => true));

	$moduleLayout = RModuleHelper::getLayoutPath('mod_redshopb_megamenu', $params->get('layout', 'default'));
	require $moduleLayout;
}
