<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  mod_redshopb_filter
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

JLoader::import('redshopb.library');

$redshopbConfig = RedshopbApp::getConfig();
RHtmlMedia::setFramework($redshopbConfig->getString('default_frontend_framework', 'bootstrap3'));

// Set the default separator
$moduleclassSfx = htmlspecialchars($params->get('moduleclass_sfx'), ENT_COMPAT, 'UTF-8');

$moduleLayout = RModuleHelper::getLayoutPath('mod_redshopb_impersonation_breadcrumbs', $params->get('layout', 'default'));
require $moduleLayout;
