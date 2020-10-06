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
 * @since       1.9.6
 */
class Com_RedshopbUpdateScript_1_9_6
{
	/**
	 * Performs the upgrade for this version
	 *
	 * @return  boolean
	 */
	public function execute()
	{
		$deletes = array(
			JPATH_ROOT . '/components/com_redshopb/controllers/taxs.php',
			JPATH_ROOT . '/components/com_redshopb/models/taxs.php',
			JPATH_ROOT . '/components/com_redshopb/models/forms/filter_taxs.xml',
			JPATH_ROOT . '/components/com_redshopb/layouts/price_debtor_group/tax_configurations.php',
			JPATH_ROOT . '/components/com_redshopb/layouts/price_debtor_group/tax_configurations_toolbar.php',
		);

		foreach ($deletes as $delete)
		{
			if (JFile::exists($delete))
			{
				JFile::delete($delete);
			}
		}

		return true;
	}
}
