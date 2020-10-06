<?php
/**
 * @package     Aesir.E-Commerce.Libraries
 * @subpackage  Helper
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;

/**
 * AJAX helper.
 *
 * @since  1.7
 */
abstract class RedshopbHelperAjax
{
	/**
	 * Check if we have received an AJAX request for security reasons
	 *
	 * @return  boolean
	 *
	 * @since   1.7
	 */
	public static function isAjaxRequest()
	{
		return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');
	}

	/**
	 * Verify that an AJAX request has been received
	 *
	 * @param   string  $method  Method to validate the ajax request
	 *
	 * @return  void
	 *
	 * @throws  Exception  If request is not valid
	 *
	 * @since   1.7
	 */
	public static function validateAjaxRequest($method = 'post')
	{
		if (!Session::checkToken($method) || !static::isAjaxRequest())
		{
			throw new Exception(Text::_('JLIB_APPLICATION_ERROR_ACCESS_FORBIDDEN'), 403);
		}
	}
}
