<?php
/**
 * @package     Aesir.E-Commerce.Plugin
 * @subpackage  user_redshopb
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */
defined('_JEXEC') or die();

use Joomla\CMS\Factory;
use Joomla\Registry\Registry;
/**
 * PlgUserRedshopb installer class.
 *
 * @package  Redshopb.Plugin
 * @since    1.6.58
 */
class PlgUserRedshopbInstallerScript
{
	/**
	 * Method to run before an install/update/uninstall method
	 *
	 * @param   string  $type    The type of change (install, update or discover_install)
	 * @param   object  $parent  Class of calling method
	 *
	 * @return  void
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
					->where($db->qn('folder') . ' = ' . $db->quote('user'))
					->where($db->qn('element') . ' = ' . $db->quote('redshopb'))
			)
				->loadResult();

			if (!empty($version))
			{
				$version = new Registry($version);
				$version = $version->get('version');

				if (version_compare($version, '1.6.58', '<'))
				{
					$this->deleteOldLanguages();
				}
			}
		}
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
