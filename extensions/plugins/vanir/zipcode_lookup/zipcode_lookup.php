<?php
/**
 * @package     Aesir.E-Commerce.Plugin.ZipcodeLookup
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\CMSPlugin;

JLoader::import('redshopb.library');

/**
 * Injects JS used for DAWA zipcode integration
 *
 * @since       1.0.0
 */
class PlgVanirZipcode_Lookup extends CMSPlugin
{
	/**
	 * Injects the JS needed for the zipcode field
	 *
	 * @return   null
	 */
	public function onBeforeCompileHead()
	{
		if (!$this->checkAccess())
		{
			return null;
		}

		$doc = Factory::getDocument();

		$jsPath = 'plugins/vanir/zipcode_lookup/js/';

		$doc->addScript($jsPath . 'dawa.js');
		$doc->addScript($jsPath . 'lookup.js');
	}

	/**
	 * Checks if we are on the right view/layout
	 *
	 * @return   boolean
	 */
	private function checkAccess()
	{
		$request = Factory::getApplication()->input;

		switch ($request->get('view'))
		{
			default:
				return false;
			case 'shop':
				$layout = $request->get('layout');

				return $layout === 'cart' || $layout === 'delivery';
			case 'b2buserregister':
				return true;
		}
	}
}
