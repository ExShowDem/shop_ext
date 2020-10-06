<?php
/**
 * @package     Aesir.E-Commerce
 * @subpackage  Toolbar
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('JPATH_REDCORE') or die;

/**
 * Class helping to build toolbars.
 *
 * @package     Aesir.E-Commerce
 * @subpackage  Toolbar
 * @since       1.0
 */
final class RedshopbToolbarBuilder
{
	/**
	 * Create an alert button.
	 *
	 * @param   string  $text       The button text.
	 * @param   string  $alert      The alert text.
	 * @param   string  $iconClass  The icon class.
	 * @param   string  $class      The button class.
	 *
	 * @return  RToolbarButtonStandard  The button.
	 */
	public static function createAlertButton($text, $alert, $iconClass, $class = '')
	{
		return new RedshopbToolbarButtonAlert($text, $alert, $iconClass, $class);
	}
}
