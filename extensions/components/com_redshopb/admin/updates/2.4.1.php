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
 * @since       2.4.1
 */
class Com_RedshopbUpdateScript_2_4_1
{
	/**
	 * Performs the upgrade for this version
	 *
	 * @return  boolean
	 *
	 * @since   2.4.1
	 * @throws Exception
	 */
	public function executeAfterUpdate()
	{
		$app = Factory::getApplication();

		// Reset Categories structure
		$app->enqueueMessage('Rebuilding Category structure and paths...', 'message');
		$table = RedshopbTable::getAdminInstance('Category');
		$table->rebuild();
		$table->rebuildPath();
		$app->enqueueMessage('Category structure and paths rebuild is successful', 'message');

		// Reset Tags structure
		$app->enqueueMessage('Rebuilding Category Tag and paths...', 'message');
		$table = RedshopbTable::getAdminInstance('Tag');
		$table->rebuild();
		$table->rebuildPath();
		$app->enqueueMessage('Tag structure and paths rebuild is successful', 'message');

		// Reset Manufacturers structure
		$app->enqueueMessage('Rebuilding Manufacturer Tag and paths...', 'message');
		$table = RedshopbTable::getAdminInstance('Manufacturer');
		$table->rebuild();
		$table->rebuildPath();
		$app->enqueueMessage('Manufacturer structure and paths rebuild is successful', 'message');

		return true;
	}
}
