<?php
/**
 * @package     Aesir.E-Commerce.Libraries
 * @subpackage  HTML
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

/**
 * Redshopb HTML class.
 *
 * @package     Aesir.E-Commerce
 * @subpackage  Html
 * @since       0.8.0
 */
abstract class RedshopbHtml
{
	/**
	 * Returns correct attribute for Footable table columns
	 *
	 * @param   int     $currentColumn  Current column number
	 * @param   int     $phone          Breakpoint for phone users
	 * @param   int     $tablet         Breakpoint for tablet users
	 * @param   int     $desktop        Breakpoint for desktop users
	 * @param   string  $attributeName  Name of the attribute to add
	 *
	 * @return  string
	 */
	public static function getResponsiveBreakpointAttribute($currentColumn, $phone = 2, $tablet = null, $desktop = null, $attributeName = 'data-hide')
	{
		if ($desktop && $currentColumn >= $desktop)
		{
			return $attributeName . '="phone,tablet,default"';
		}
		elseif ($tablet && $currentColumn >= $tablet)
		{
			return $attributeName . '="phone,tablet"';
		}
		elseif ($phone && $currentColumn >= $phone)
		{
			return $attributeName . '="phone"';
		}

		return '';
	}

	/**
	 * Loads all javascript and css files for FooTable library
	 *
	 * @return  void
	 */
	public static function loadFooTable()
	{
		RHelperAsset::load('footable.core.min.css', 'com_redshopb');
		RHelperAsset::load('footable.metro.min.css', 'com_redshopb');
		RHelperAsset::load('footable.all.min.js', 'com_redshopb');
		RHelperAsset::load('footable.redshopb.init.js', 'com_redshopb');
	}
}
