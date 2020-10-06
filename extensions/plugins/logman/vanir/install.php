<?php
/**
 * @package     Vanir.Plugin
 * @subpackage  logman_vanir
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */
defined('_JEXEC') or die();

/**
 * PlgLogmanVanir installer class.
 *
 * @package  Vanir.Plugin
 * @since    1.12.65
 */
class PlgLogmanVanirInstallerScript
{
	/**
	 * Method to run before an install/update/uninstall method
	 *
	 * @param   string  $type       The type of change (install, update or discover_install)
	 * @param   object  $installer  Class of calling method
	 *
	 * @return  boolean
	 *
	 * @since   1.12.65
	 */
	public function preflight($type, $installer)
	{
		return true;
	}
}
