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
class Com_RedshopbUpdateScript_1_6_12
{
	/**
	 * Performs the upgrade for this version
	 *
	 * @return  void
	 */
	public function execute()
	{
		$db = Factory::getDbo();

		$query = 'CALL #__redshopb_sp_upgrade_1_0_to_1_6_12()';
		$db->setQuery($query);
		$db->execute();

		$query = 'DROP PROCEDURE #__redshopb_sp_upgrade_1_0_to_1_6_12';
		$db->setQuery($query);
		$db->execute();
	}
}
