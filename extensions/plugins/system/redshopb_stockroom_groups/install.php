<?php
/**
 * @package     Aesir.E-Commerce
 * @subpackage  Install
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

// Find redCORE installer to use it as base system
if (!class_exists('Com_RedcoreInstallerScript'))
{
	$searchPaths = array(
		// Install
		dirname(__FILE__) . '/redSHOPB2B/redCORE/extensions',
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
 * Custom installation of Unit Pricing plugin.
 *
 * @package     Aesir.E-Commerce
 * @subpackage  Install
 * @since       1.0
 */
class PlgSystemRedshopb_Stockroom_GroupsInstallerScript extends Com_RedcoreInstallerScript
{
	/**
	 * Method to run before an install/update/uninstall method
	 *
	 * @param   object  $type    type of change (install, update or discover_install)
	 * @param   object  $parent  class calling this method
	 *
	 * @return  boolean
	 */
	public function preflight($type, $parent)
	{
		if (method_exists('Com_RedcoreInstallerScript', 'preflight') && !parent::preflight($type, $parent))
		{
			return false;
		}

		$this->installWebservices($parent);

		return true;
	}

	/**
	 * Method to run after an install/update/uninstall method
	 *
	 * @param   object  $type    type of change (install, update or discover_install)
	 * @param   object  $parent  class calling this method
	 *
	 * @return  boolean
	 */
	public function postflight($type, $parent)
	{
		if ($type != 'uninstall')
		{
			// Move Joomla overrides to their path
			$this->copyRecursive(JPATH_SITE . '/plugins/system/redshopb_stockroom_groups/override/joomla', JPATH_SITE);
		}

		return true;
	}

	/**
	 * Recursively copy the contents of some folder into another one
	 *
	 * @param   string  $folder  Origin folder
	 * @param   string  $dest    Destination folder (will be created if it doesn't exist)
	 *
	 * @return  void
	 */
	protected function copyRecursive($folder, $dest)
	{
		if (!file_exists($dest))
		{
			mkdir($dest);
		}

		$files = glob($folder . '/*');

		foreach ($files as $file)
		{
			$destfile = $dest . str_replace($folder, '', $file);

			if (is_dir($file))
			{
				$this->copyRecursive($file, $destfile);
			}
			else
			{
				copy($file, $destfile);
			}
		}
	}
}
