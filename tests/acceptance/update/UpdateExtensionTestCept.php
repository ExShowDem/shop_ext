<?php
/**
 * @package     Aesir.E-Commerce
 * @subpackage  Cept
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// Before executing this tests configuration.php is removed at tests/_groups/InstallationGroup.php

// Load the Step Object Page
$I = new AcceptanceTester($scenario);
$I->wantTo('Test Update Extension');
$I->doAdministratorLogin();
$I->wantTo('Install redSHOPB2B from develop branch');
$I->installExtensionFromUrl($I->getConfig('redshopb packages url') . 'redshopb.zip');

if ($I->getConfig('install demo data') == 'Yes')
{
	$I->click("//button[@id='installdemo']");
	$I->waitForText('data installed successful', 10, '#system-message-container');
}

$I->installExtensionFromUrl($I->getConfig('redshopb packages url') . 'redshopb.zip');

