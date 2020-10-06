<?php
/**
 * @package    Redshopb.SEF
 *
 * @copyright  Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
/**
 * Redshopb sh404SEF extension plugin.
 *
 * Note that the same mechanism applies for meta ext plugin. See the
 * source code file for the Sh404sefClassBaseextplugin class
 *
 * Note also that:
 *    - you do not need to include or require the Sh404sefClassBaseextplugin class file,
 *    sh404SEF autoloading mechanism takes care of everything
 *    - likewise, this plugin does not need to be published, it will
 *    be used even if unpublished. To deactivate it, just uninstall it
 *
 * @since  1.6.8
 */
class Sh404sefExtpluginCom_Redshopb extends Sh404sefClassBaseextplugin
{
	/**
	 * Standard constructor don't change
	 *
	 * @param   object  $option  Option
	 * @param   string  $config  Config
	 */
	public function __construct($option, $config)
	{
		$this->_extName = 'com_redshopb';
		parent::__construct($option, $config);

		$extension = 'plg_sh404sefextplugins_com_redshopb';
		$lang      = Factory::getLanguage();
		$lang->load(strtolower($extension), JPATH_ADMINISTRATOR, null, false, true)
		|| $lang->load(strtolower($extension), JPATH_PLUGINS . '/sh404sefextplugins/com_redshopb', null, false, true);

		$this->_pluginType = Sh404sefClassBaseextplugin::TYPE_SH404SEF_ROUTER;
	}

	/**
	 * Adjust returned path to your own plugin. This method will be used to find the exact
	 * and full path to your plugin main file. The location used below is just a sample.
	 * Your plugin can be stored anywhere, and use as many files as you need. sh404SEF only
	 * needs to know about the main entry point.
	 *
	 * @param   array  $nonSefVars  An array of key=>values representing the non-sef vars of the url
	 *                              we are trying to SEFy. You can adjust the plugin used depending on the
	 *                              request being made (or other elements). For instance, you could use
	 *                              a different plugin based on the currently installed version of the extension
	 *
	 * @return  void
	 */
	protected function _findSefPluginPath($nonSefVars = array())
	{
		JLoader::import('redshopb.library');
		$this->_sefPluginPath = JPATH_ROOT . '/plugins/sh404sefextplugins/com_redshopb/redshopb/sef.php';
	}

	/**
	 * Adjust returned path to your own plugin. This method will be used to find the exact
	 * and full path to your plugin main file. The location used below is just a sample.
	 * Your plugin can be stored anywhere, and use as many files as you need. sh404SEF only
	 * needs to know about the main entry point.
	 *
	 * @param   array  $nonSefVars  An array of key=>values representing the non-sef vars of the url
	 *                              we are trying to SEFy. You can adjust the plugin used depending on the
	 *                              request being made (or other elements). For instance, you could use
	 *                              a different plugin based on the currently installed version of the extension
	 *
	 * @return  mixed
	 */
	protected function _findMetaPluginPath($nonSefVars = array())
	{
		$this->_metaPluginPath = JPATH_ROOT . '/plugins/sh404sefextplugins/com_redshopb/redshopb/meta.php';
	}
}
