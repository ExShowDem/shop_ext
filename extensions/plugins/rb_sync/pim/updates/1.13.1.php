<?php
/**
 * @package     Aesir.E-Commerce
 * @subpackage  Upgrade
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;

/**
 * Custom upgrade of Redshop b2b.
 *
 * @package     Aesir.E-Commerce
 * @subpackage  Upgrade
 * @since       1.6
 */
class PimUpdateScript_1_13_1
{
	/**
	 * Performs the upgrade for this version
	 *
	 * @return  boolean
	 */
	public function execute()
	{
		Factory::getApplication()->enqueueMessage(
			'Please execute script in cli folder: "php com_redshopb/patch_plugin_pim_1_13_1_add_pimid_field_data.php" or execute it in <a href="index.php?option=com_redshopb&view=tools">Vanir Tools</a>',
			'message'
		);

		return true;
	}
}
