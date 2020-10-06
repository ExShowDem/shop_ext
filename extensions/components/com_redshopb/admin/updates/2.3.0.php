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
 * @since       2.3.0
 */
class Com_RedshopbUpdateScript_2_3_0
{
	/**
	 * Performs the upgrade for this version
	 *
	 * @return  boolean
	 *
	 * @since   2.3.0
	 * @throws Exception
	 */
	public function executeAfterUpdate()
	{
		$app = Factory::getApplication();
		$app->enqueueMessage(
			'Please make sure you update the ePay redpayment plugin to the latest version.',
			'message'
		);

		return true;
	}
}
