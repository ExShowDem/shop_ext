<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  mod_redshopb_switch_company
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;

JLoader::import('redshopb.library');

$redshopbConfig = RedshopbApp::getConfig();
RHtmlMedia::setFramework($redshopbConfig->getString('default_frontend_framework', 'bootstrap3'));

$lang = Factory::getLanguage();
$lang->load('mod_redshopb_switch_company', __DIR__);

$app       = Factory::getApplication();
$userRSid  = RedshopbHelperUser::getUserRSid();
$vanirUser = RedshopbEntityUser::getInstance($userRSid);

if (!$params->get('display_always', 0))
{
	$userCompanies = $vanirUser->getUserMultiCompanies();

	if (count($userCompanies) == 1)
	{
		return;
	}
}

$userCompany  = $vanirUser->getSelectedCompany();
$userRole     = $vanirUser->getRole();
$userRoleType = $userRole->getType();

$moduleLayout = RModuleHelper::getLayoutPath('mod_redshopb_switch_company');
require $moduleLayout;
