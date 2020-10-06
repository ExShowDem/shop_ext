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
 * @since       1.6
 */
class Com_RedshopbUpdateScript_1_6_9
{
	/**
	 * Performs the upgrade for this version
	 *
	 * @return  true
	 */
	public function execute()
	{
		jimport('redshopb.table.table');
		jimport('redshopb.table.nested');
		jimport('redshopb.table.nested.asset');
		jimport('redshopb.table.webservices');

		// @var RedshopbTableCron $cronTable

		$cronTable = RedshopbTable::getAdminInstance('Cron', array(), 'com_redshopb');
		$cronTable->rebuild();

		return true;
	}
}
