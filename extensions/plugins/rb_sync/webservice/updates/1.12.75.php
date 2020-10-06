<?php
/**
 * @package     Vanir
 * @subpackage  Upgrade
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;

/**
 * Custom upgrade of Vanir.
 *
 * @package     Aesir.E-Commerce
 * @subpackage  Upgrade
 * @since       1.9
 */
class WebserviceUpdateScript_1_12_75
{
	/**
	 * Performs the upgrade for this version
	 *
	 * @return  boolean
	 */
	public function execute()
	{
		$app = Factory::getApplication();
		$app->enqueueMessage(
			'Please execute script in cli folder: "php com_redshopb/patch_plugin_webservice_1_12_75_same_product_sync.php" or execute it in <a href="index.php?option=com_redshopb&view=tools">Vanir Tools</a>',
			'message'
		);

		return true;
	}
}
