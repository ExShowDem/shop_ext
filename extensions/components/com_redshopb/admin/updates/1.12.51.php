<?php
/**
 * @package     Aesir.E-Commerce
 * @subpackage  Upgrade
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Application\CliApplication;

JLoader::import('redshopb.library');
RTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_redshopb/tables');

/**
 * Custom upgrade of Redshop b2b.
 *
 * @package     Aesir.E-Commerce
 * @subpackage  Upgrade
 * @since       1.12.51
 */
class Com_RedshopbUpdateScript_1_12_51
{
	/**
	 * Performs the upgrade for this version
	 *
	 * @param   object  $parent  Parent object
	 *
	 * @return  boolean
	 */
	public function execute($parent)
	{
		$tmpLocation = $parent->getParent()->getPath('source');

		if (JFile::exists($tmpLocation . '/cli/com_redshopb/update_image_ordering.php')
			&& JFile::copy(
				$tmpLocation . '/cli/com_redshopb/update_image_ordering.php',
				JPATH_ROOT . '/cli/com_redshopb/update_image_ordering.php'
			))
		{
			require_once JPATH_ROOT . '/cli/com_redshopb/update_image_ordering.php';
			$instance = CliApplication::getInstance('Update_Image_OrderingApplicationCli');
			$instance->execute();
		}

		return true;
	}
}
