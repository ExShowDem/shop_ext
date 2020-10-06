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
 * @since       1.12.72
 */
class Com_RedshopbUpdateScript_1_12_72
{
	/**
	 * Performs the upgrade for this version
	 *
	 * @return  boolean
	 *
	 * @since   1.12.72
	 */
	public function executeAfterUpdate()
	{
		$app = Factory::getApplication();
		$app->enqueueMessage(
			'Please execute script in cli folder: "php com_redshopb/patch_1_12_71_user_multi_company.php"',
			'message'
		);

		return true;
	}
}
