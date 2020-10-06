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
class Com_RedshopbUpdateScript_1_6_77
{
	/**
	 * Performs the upgrade for this version
	 *
	 * @return  boolean
	 */
	public function executeAfterUpdate()
	{
		$db = Factory::getDbo();

		// Load all product that came from PIM
		$query = $db->getQuery(true)
			->select('p.*')
			->from($db->qn('#__redshopb_product', 'p'));

		$db->setQuery($query);
		$items = $db->loadObjectList();

		// This is to clear up permissions container after we have filled it with plugin values
		RedshopbHelperWebservice_Permission::$permissions = array();

		return RedshopbHelperWebservice_Permission::savePermissionsForProduct($items);
	}
}
