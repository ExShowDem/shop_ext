<?php
/**
 * @package     Aesir.E-Commerce
 * @subpackage  Upgrade
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

/**
 * Custom upgrade of Redshop b2b.
 *
 * @package     Aesir.E-Commerce
 * @subpackage  Upgrade
 * @since       1.6.95
 */
class Com_RedshopbUpdateScript_1_6_95
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
			array('b2b' => JPATH_SITE . '/components/com_redshopb/language', 'joomla' => JPATH_SITE . '/language'),
			array('b2b' => JPATH_ADMINISTRATOR . '/components/com_redshopb/language', 'joomla' => JPATH_ADMINISTRATOR . '/language')
		);

		foreach ($languageFolders as $languageFolder)
		{
			$codes = JFolder::folders($languageFolder['b2b'], '.', true);

			if (empty($codes))
			{
				continue;
			}

			foreach ($codes as $code)
			{
				$files = JFolder::files($languageFolder['b2b'] . '/' . $code, '.ini');

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
