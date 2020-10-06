<?php
/**
 * @package     Vanir
 * @subpackage  Install
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

// Find redCORE installer to use it as base system
if (!class_exists('Com_RedshopbInstallerScript'))
{
	$searchPaths = array(
		// Install
		dirname(__FILE__) . '/redSHOPB2B',
		// Discover install
		JPATH_ADMINISTRATOR . '/components/com_redshopb'
	);

	$redshopbInstaller = JPath::find($searchPaths, 'install.php');

	if ($redshopbInstaller)
	{
		require_once $redshopbInstaller;
	}
}

/**
 * Custom installation of Vanir self shipping plugin
 *
 * @package     Vanir
 * @subpackage  Install
 * @since       1.0
 */
class PlgSystemRedshopb_Self_ShippingInstallerScript extends Com_RedshopbInstallerScript
{
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
		if ($type == 'install' || $type == 'discover_install' || $type == 'update')
		{
			return $this->proccessTemplateFiles();
		}

		return true;
	}

	/**
	 * Method for process template files.
	 *
	 * @return  boolean  True on success. False otherwise.
	 */
	protected function proccessTemplateFiles()
	{
		// Copy all webservice files
		JFolder::copy(
			__DIR__ . '/extensions/webservices',
			JPATH_ROOT . '/media/redcore/webservices/',
			'',
			true
		);

		return true;
	}
}
