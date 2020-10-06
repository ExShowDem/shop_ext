<?php
/**
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Controllers
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Session\Session;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

/**
 * Config controller
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Controllers
 * @since       1.13.0
 */
class RedshopbControllerConfig extends RedshopbControllerForm
{
	/**
	 * Method to cancel an edit.
	 *
	 * @param   string  $key  The name of the primary key of the URL variable.
	 *
	 * @return  boolean  True if access level checks pass, false otherwise.
	 */
	public function cancel($key = null)
	{
		Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

		$this->setRedirect(Route::_('index.php?option=com_redshopb'));

		return true;
	}
}
