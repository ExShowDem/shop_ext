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
 * Custom installation.
 *
 * @package     Aesir.E-Commerce
 * @subpackage  Install
 * @since       1.0
 */
class PlgVanir_SearchSolrInstallerScript extends Com_RedcoreInstallerScript
{
	/**
	 * Method to run after an install/update/uninstall method
	 *
	 * @param   object  $type    type of change (install, update or discover_install)
	 * @param   object  $parent  class calling this method
	 *
	 * @return  boolean
	 * @since 1.0.0
	 */
	public function postflight($type, $parent)
	{
		if (!JFile::exists(JPATH_PLUGINS . '/vanir_search/solr/config.xml'))
		{
			JFile::copy(
				JPATH_PLUGINS . '/vanir_search/solr/config.dist.xml',
				JPATH_PLUGINS . '/vanir_search/solr/config.xml'
			);
		}

		return true;
	}
}
