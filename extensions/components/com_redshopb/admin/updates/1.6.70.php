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
class Com_RedshopbUpdateScript_1_6_70
{
	/**
	 * Performs the upgrade for this version
	 *
	 * @return  true
	 */
	public function execute()
	{
		$wsTable = RTable::getAdminInstance('Webservice', array(), 'com_redcore');

		if ($wsTable->load(
			array(
				'name' => 'redshopb-field',
				'version' => '1.0.0',
				'path' => 'com_redshopb',
				'client' => 'site'
			)
		))
		{
			$wsTable->delete();
			$wsModelWebservices = RModel::getAdminInstance('Webservices', array(), 'com_redcore');
			$wsModelWebservices->deleteWebservice('site', 'redshopb-field', '1.0.0', 'com_redshopb');
		}

		return true;
	}
}
