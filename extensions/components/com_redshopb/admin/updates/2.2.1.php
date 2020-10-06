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
 * @since       2.2.1
 */
class Com_RedshopbUpdateScript_2_2_1
{
	/**
	 * Performs the upgrade for this version
	 *
	 * @return  boolean
	 *
	 * @since   2.2.1
	 */
	public function executeAfterUpdate()
	{
		$installer = Joomla\CMS\Installer\Installer::getInstance();
		$row       = Joomla\CMS\Table\Table::getInstance('extension');
		$deleted   = false;

		if ($row->load(['type' => 'library', 'element' => 'mpdf']))
		{
			$result = $installer->uninstall($row->get('type'), $row->get('extension_id'));

			if ($result !== false)
			{
				$deleted = true;
			}
		}

		if (!$deleted)
		{
			$folder = JPATH_LIBRARIES . '/mpdf';

			if (JFolder::exists($folder))
			{
				JFolder::delete($folder);
			}
		}

		return true;
	}
}
