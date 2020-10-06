<?php
/**
 * @package     Aesir.E-Commerce.Plugin
 * @subpackage  product_custom_text
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

/**
 * Vanir - Product Custom Text installer class
 *
 * @package     Aesir.E-Commerce.Plugin
 * @subpackage  product_custom_text
 * @since       1.0.0
 */
class PlgVanirProduct_Custom_TextInstallerScript
{
	/**
	 * Method to run before an install/update/uninstall method
	 *
	 * @param   string  $type  The type of change (install, update or discover_install)
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	public function preflight($type)
	{
		$files = array(
			array(
				'src'  => __DIR__ . '/layouts/tags/product/custom_text.php',
				'dest' => JPATH_ROOT . '/components/com_redshopb/layouts/tags/product/custom_text.php'
			),
			array(
				'src'  => __DIR__ . '/layouts/tags/product-list/product/custom_text.php',
				'dest' => JPATH_ROOT . '/components/com_redshopb/layouts/tags/product-list/product/custom_text.php'
			),
			array(
				'src'  => __DIR__ . '/layouts/checkout/products/customText.php',
				'dest' => JPATH_ROOT . '/components/com_redshopb/layouts/checkout/products/customText.php'
			)
		);

		if ($type != 'uninstall')
		{
			foreach ($files as $file)
			{
				if (JFile::exists($file['dest']))
				{
					JFile::delete($file['dest']);
				}

				JFile::copy($file['src'], $file['dest']);
			}
		}
		else
		{
			foreach ($files as $file)
			{
				if (JFile::exists($file['dest']))
				{
					JFile::delete($file['dest']);
				}
			}
		}
	}
}
