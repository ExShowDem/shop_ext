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
 * @since       1.6.113
 */
class Com_RedshopbUpdateScript_1_6_113
{
	/**
	 * Performs the upgrade for this version
	 *
	 * @return  boolean
	 */
	public function execute()
	{
		$joomlaTemplateList = RedshopbHelperTemplate::getJoomlaTemplateList();
		$files              = array(
			'templates/product-list-style/grid.php' => 'shop/products/grid.php',
			'templates/product-list-style/list.php' => 'shop/products/list.php'
		);

		foreach ($joomlaTemplateList as $joomlaTemplate)
		{
			foreach ($files as $newPath => $oldPath)
			{
				$path = JPATH_THEMES . '/' . $joomlaTemplate . '/html/layouts/com_redshopb/' . $oldPath;

				if (JFile::exists($path))
				{
					JFile::move($path, JPATH_THEMES . '/' . $joomlaTemplate . '/html/layouts/com_redshopb/' . $newPath);
				}
			}
		}

		$files[] = 'tags/category/show_as.php';

		foreach ($files as $file)
		{
			if (JFile::exists(JPATH_BASE . '/components/com_redshopb/layouts/' . $file))
			{
				JFile::delete(JPATH_BASE . '/components/com_redshopb/layouts/' . $file);
			}
		}

		return true;
	}
}
