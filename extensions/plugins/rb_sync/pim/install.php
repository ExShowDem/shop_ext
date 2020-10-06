<?php
/**
 * @package     RBSync
 * @subpackage  Install
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\Registry\Registry;

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
 * Custom installation of RBSync plugin
 *
 * @package     RBSync
 * @subpackage  Install
 * @since       1.0
 */
class PlgRB_SyncPIMInstallerScript extends Com_RedshopbInstallerScript
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
			return $this->postProcessCron();
		}

		return true;
	}

	/**
	 * Method to run before an install/update/uninstall method
	 *
	 * @param   string  $type    The type of change (install, update or discover_install)
	 * @param   object  $parent  Class of calling method
	 *
	 * @return  boolean
	 */
	public function preflight($type, $parent)
	{
		if ($type == 'update' || $type == 'discover_install')
		{
			// Reads current (old) version from manifest
			$db      = Factory::getDbo();
			$version = $db->setQuery(
				$db->getQuery(true)
					->select($db->qn('manifest_cache'))
					->from($db->qn('#__extensions'))
					->where($db->qn('type') . ' = ' . $db->quote('plugin'))
					->where($db->qn('folder') . ' = ' . $db->quote('rb_sync'))
					->where($db->qn('element') . ' = ' . $db->quote('pim'))
			)
				->loadResult();

			if (!empty($version))
			{
				$version = new Registry($version);
				$version = $version->get('version');

				if (version_compare($version, '1.6.78', '<'))
				{
					$this->deleteOldLanguages();
				}
			}
		}

		return parent::preflight($type, $parent);
	}

	/**
	 * Method for delete old languages files in core langauge folder of Joomla
	 *
	 * @return  void
	 */
	protected function deleteOldLanguages()
	{
		// Delete old languages files if necessary
		JLoader::import('joomla.filesystem.file');
		$languageFolder       = __DIR__ . '/language';
		$joomlaLanguageFolder = JPATH_ADMINISTRATOR . '/language';
		$codes                = JFolder::folders($languageFolder, '.', true);

		if (empty($codes))
		{
			return;
		}

		foreach ($codes as $code)
		{
			$files = JFolder::files($languageFolder . '/' . $code, '.ini');

			if (empty($files))
			{
				continue;
			}

			foreach ($files as $file)
			{
				if (!JFile::exists($joomlaLanguageFolder . '/' . $code . '/' . $file))
				{
					continue;
				}

				JFile::delete($joomlaLanguageFolder . '/' . $code . '/' . $file);
			}
		}
	}
}
