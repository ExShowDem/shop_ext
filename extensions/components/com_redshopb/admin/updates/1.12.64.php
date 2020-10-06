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
 * @since       1.12.64
 */
class Com_RedshopbUpdateScript_1_12_64
{

	/**
	 * Performs the upgrade for this version
	 *
	 * @return  boolean
	 *
	 * @since 1.12.32
	 */
	public function execute()
	{
		// Moved to 1.12.67 because of bug in configuration update
		return true;
	}

}
