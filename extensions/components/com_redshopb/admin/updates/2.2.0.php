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
 * @since       2.2.0
 */
class Com_RedshopbUpdateScript_2_2_0
{
	/**
	 * Performs the upgrade for this version
	 *
	 * @return  boolean
	 *
	 * @since   2.2.0
	 * @throws Exception
	 */
	public function executeAfterUpdate()
	{
		$app = Factory::getApplication();
		$app->enqueueMessage(
			'Please stop cron sync process and execute script in cli folder: "php com_redshopb/patch_2_2_0_switch_locking_system.php"',
			'message'
		);

		return true;
	}
}
