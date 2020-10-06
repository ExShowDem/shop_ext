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
 * @since       1.6.99
 */
class Com_RedshopbUpdateScript_1_6_101
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

		$files = array(
			JPATH_ROOT . '/components/com_redshopb/layouts/field_value/form.php',
			JPATH_ROOT . 'components/com_redshopb/views/field/tmpl/edit_modal.php'
		);

		foreach ($files as $file)
		{
			if (JFile::exists($file))
			{
				JFile::delete($file);
			}
		}

		return true;
	}
}
