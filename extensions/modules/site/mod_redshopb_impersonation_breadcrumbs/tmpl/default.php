<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  mod_redshopb_filter
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

$componentParams = RedshopbApp::getConfig();
$cloneParams     = clone $componentParams;
$cloneParams->set('breadcrumbs_here', 'COM_REDSHOPB_CONFIG_IMPERSONATION_BREADCRUMBS_HERE');
$cloneParams->set('showLast', 1);
$cloneParams->merge($params);
$separator = RedshopbBrowserBreadcrumbs::setSeparator($cloneParams->get('separator'));

$impersonationBreadcrumbs = RedshopbBrowserBreadcrumbs::getImpersonationBreadcrumbs();

if (!empty($impersonationBreadcrumbs))
{
	echo RedshopbLayoutHelper::render('browser.impersonationbreadcrumb', array(
			'breadcrumbs' => $impersonationBreadcrumbs,
			'params' => $cloneParams,
			'class_sfx' => $moduleclassSfx,
			'separator' => $separator)
	);
}
