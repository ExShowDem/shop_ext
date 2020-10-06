<?php
/**
 * @package     Aesir.E-Commerce.Site
 * @subpackage  mod_redshopb_vendor_contact_details
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\Registry\Registry;

/**
 * Helper for mod_redshopb_vendor_contact_details
 *
 * @package     Aesir.E-Commerce.Site
 * @subpackage  mod_redshopb_vendor_contact_details
 * @since       1.0.0
 */
class ModRedshopbVendorContactDetailsHelper
{
	/**
	 * Get a list of the menu items.
	 *
	 * @param   Registry  $params  Module params.
	 *
	 * @return  string  HTML data of vendor company contact info.
	 */
	public static function getData($params)
	{
		$showCurrent = $params->get('show_current_company', 0);
		$user        = RedshopbEntityUser::getInstance()->loadActive();
		$userComp    = $user->getCompany();

		$comp = $showCurrent ? $userComp->getItem() : $userComp->getVendor()->getItem();

		return !empty($comp) && $comp->contact_info ? $comp->contact_info : '';
	}
}
