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
use Joomla\CMS\Language\Text;

/**
 * Custom upgrade of Redshop b2b.
 *
 * @package     Aesir.E-Commerce
 * @subpackage  Upgrade
 * @since       1.6.17
 */
class Com_RedshopbUpdateScript_1_6_117
{
	/**
	 * Performs the upgrade for this version
	 *
	 * @return  boolean
	 */
	public function execute()
	{
		Factory::getApplication()->enqueueMessage(Text::_('COM_REDSHOPB_UPDATE_1_6_117_NEW_ACL'));

		return true;
	}
}
