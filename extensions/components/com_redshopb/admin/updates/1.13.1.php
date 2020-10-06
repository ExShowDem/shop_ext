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
 * @since       1.12.78
 */
class Com_RedshopbUpdateScript_1_13_1
{
	/**
	 * Performs the upgrade for this version
	 *
	 * @return  boolean
	 *
	 * @since   1.12.78
	 */
	public function executeAfterUpdate()
	{
		$app = Factory::getApplication();
		$app->enqueueMessage(
			'Please execute script in cli folder: "php com_redshopb/patch_1_13_1_reapply_company_roles.php"',
			'message'
		);

		return true;
	}
}
