<?php
/**
 * @package     Aesir.E-Commerce
 * @subpackage  Cept
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// Load the Step Object Page
$allExtensionPages = array (
	'Administrator Home Page'   => '/administrator/index.php?option=com_redshopb',
	'Configuration Manager'     => '/administrator/index.php?option=com_redshopb&task=config.edit',
	'Syncronization Manager'    => '/administrator/index.php?option=com_redshopb&view=sync',
	'Fees Manager'              => '/administrator/index.php?option=com_redshopb&view=fees',
	'Tools'                     => '/administrator/index.php?option=com_redshopb&view=tools',
);
$I = new AcceptanceTester($scenario);
$I->doAdministratorLogin();
$I->doFrontEndLogin();
foreach ($allExtensionPages as $page => $url)
{
	$I->checkForPhpNoticesOrWarnings($url);
}
