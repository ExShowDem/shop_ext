<?php
/**
 * @package     Sh404sef_Observer
 * @subpackage  Install
 *
 * @copyright   Copyright (C) 2012 - 2019 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

// Find redCORE installer to use it as base system
if (!class_exists('Com_RedcoreInstallerScript'))
{
	$searchPaths = array(
		// Install
		dirname(__FILE__) . '/redCORE/extensions',
		// Discover install
		JPATH_ADMINISTRATOR . '/components/com_redcore'
	);

	$redcoreInstaller = JPath::find($searchPaths, 'install.php');

	if ($redcoreInstaller)
	{
		require_once $redcoreInstaller;
	}
}

/**
 * Custom installation of Sh404sef_Observer plugin
 *
 * @package     Sh404sef_Observer
 * @subpackage  Install
 * @since       2.6.0
 */
class PlgSystemSh404sef_ObserverInstallerScript extends Com_RedcoreInstallerScript
{
}
