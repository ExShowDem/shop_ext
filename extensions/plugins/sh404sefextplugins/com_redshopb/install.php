<?php
/**
 * @package     Aesir.E-Commerce.Plugin
 * @subpackage  sh404sefextplugins_com_redshopb
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */
defined('_JEXEC') or die();

use Joomla\CMS\Factory;
use Joomla\Registry\Registry;
/**
 * PlgSh404sefextpluginsCom_Redshopb installer class.
 *
 * @package  Redshopb.Plugin
 * @since    1.6.55
 */
class PlgSh404sefextpluginsCom_RedshopbInstallerScript
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
					->where($db->qn('folder') . ' = ' . $db->quote('sh404sefextplugins'))
					->where($db->qn('element') . ' = ' . $db->quote('com_redshopb'))
			)
				->loadResult();

			if (!empty($version))
			{
				$version = new Registry($version);
				$version = $version->get('version');

				if (version_compare($version, '1.6.55', '<'))
				{
					$this->deleteOldLanguages();
				}

				if (version_compare($version, '1.9.1', '<'))
				{
					$this->purgeDuplicatesURLs();
				}
			}
		}
	}

	/**
	 * Purge URL's with duplicates
	 *
	 * @return  void
	 */
	protected function purgeDuplicatesURLs()
	{
		if (!class_exists('Sh404sefHelperCache'))
		{
			return;
		}

		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->select('COUNT(oldurl) as count_duplicates, oldurl')
			->from($db->qn('#__sh404sef_urls'))
			->group('oldurl')
			->having('count_duplicates > 1');

		$oldUrls = $db->setQuery($query)
			->loadAssocList(null, 'oldurl');

		if (!empty($oldUrls))
		{
			$oldUrls = implode(',', RHelperArray::quote($oldUrls));
			$query   = $db->getQuery(true)
				->select('newurl')
				->from($db->qn('#__sh404sef_urls'))
				->where('oldurl IN (' . $oldUrls . ')');

			$results = $db->setQuery($query)->loadColumn();

			if (!empty($results))
			{
				Sh404sefHelperCache::removeUrlFromCache($results);
				$query->clear()
					->delete($db->qn('#__sh404sef_urls'))
					->where('oldurl IN (' . $oldUrls . ')');

				$db->setQuery($query)->execute();
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
