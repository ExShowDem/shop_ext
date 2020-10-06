<?php
/**
 * @package     Aesir.E-Commerce.Library
 * @subpackage  Base
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Router\Route;

/**
 * Redshopb Route
 *
 * @package     Aesir.E-Commerce
 * @subpackage  Base
 * @since       1.0
 */
class RedshopbRoute extends Route
{
	/**
	 * Translates an internal Joomla URL to a humanly readible URL.
	 *
	 * @param   string   $url        Absolute or Relative URI to Joomla resource.
	 * @param   boolean  $xhtml      Replace & by &amp; for XML compilance.
	 * @param   integer  $ssl        Secure state for the resolved URI.
	 *                                 1: Make URI secure using global secure site URI.
	 *                                 2: Make URI unsecure using the global unsecure site URI.
	 * @param   boolean  $absolute   Return an absolute URL
	 *
	 * @return  string  The translated humanly readible URL.
	 */
	public static function _($url, $xhtml = true, $ssl = null, $absolute = false)
	{
		$url = RedshopbHelperRoute::getRoute($url);

		return parent::_($url, $xhtml, $ssl, $absolute);
	}
}
