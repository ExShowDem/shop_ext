<?php
/**
 * @package     RsbMedia
 * @subpackage  Upgrade
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

/**
 * Custom upgrade of Redshop B2B Media.
 *
 * @package     Aesir.E-Commerce
 * @subpackage  Upgrade
 * @since       1.6.55
 */
class Com_RsbmediaUpdateScript_1_6_55
{
	/**
	 * Performs the upgrade for this version
	 *
	 * @return  boolean
	 */
	public function execute()
	{
		// Delete old languages files if necessary
		JLoader::import('joomla.filesystem.file');

		$languageFolders = array(
			array('rsbmedia' => JPATH_SITE . '/components/com_rsbmedia/language', 'joomla' => JPATH_SITE . '/language'),
			array('rsbmedia' => JPATH_ADMINISTRATOR . '/components/com_rsbmedia/language', 'joomla' => JPATH_ADMINISTRATOR . '/language')
		);

		foreach ($languageFolders as $languageFolder)
		{
			$codes = JFolder::folders($languageFolder['rsbmedia'], '.', true);

			if (empty($codes))
			{
				continue;
			}

			foreach ($codes as $code)
			{
				$files = JFolder::files($languageFolder['rsbmedia'] . '/' . $code, '.ini');

				if (empty($files))
				{
					continue;
				}

				foreach ($files as $file)
				{
					if (JFile::exists($languageFolder['joomla'] . '/' . $code . '/' . $file))
					{
						JFile::delete($languageFolder['joomla'] . '/' . $code . '/' . $file);
					}
				}
			}
		}

		return true;
	}
}
