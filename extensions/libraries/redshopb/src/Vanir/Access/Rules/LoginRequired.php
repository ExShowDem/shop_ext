<?php
/**
 * @package     Vanir.Library
 * @subpackage  Access
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

namespace Vanir\Access\Rules;

defined('_JEXEC') or die;

use Joomla\CMS\Input\Input;
use Joomla\CMS\Uri\Uri;
/**
 * Class LoginRequired
 *
 * @package  Vanir\Access\Rules
 * @since    2.0
 */
class LoginRequired extends Rule
{
	/**
	 * Method to require the user login to access the page
	 *
	 * @param   \RedshopbEntityUser  $user   the user asking for permission
	 * @param   Input                $input  input variables to be used by the rules
	 *
	 * @return boolean
	 */
	protected function check($user, $input)
	{
		if ($this->getPermission('manage', '', array(), true, 0, 'core'))
		{
			return true;
		}

		// In this instance we need to reroute the application, so we'll set the redirect
		$returnUri = Uri::getInstance();
		$returnUri->delVar('return');
		$return = '&return=' . base64_encode($returnUri->toString());

		$this->specificationContainer->setRedirect('index.php?option=com_redshopb&view=b2buserregister' . $return, false);

		return false;
	}
}
