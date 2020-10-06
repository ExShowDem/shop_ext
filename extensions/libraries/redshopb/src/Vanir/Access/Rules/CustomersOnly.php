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

use Joomla\CMS\Language\Text;
use Joomla\CMS\Input\Input;

/**
 * Class CustomersOnly
 *
 * @package  Vanir\Access\Rules
 * @since    2.0
 */
class CustomersOnly extends Rule
{
	/**
	 * Method to restrict access to main company
	 *
	 * @param   \RedshopbEntityUser  $user   the user asking for permission
	 * @param   Input                $input  input variables to be used by the rules
	 *
	 * @return boolean
	 */
	protected function check($user, $input)
	{
		if (!$user->isFromMainCompany())
		{
			return true;
		}

		$this->specificationContainer
			->addMessage(Text::sprintf('COM_REDSHOPB_VIEW_DISABLED_FOR_MAIN_COMPANY', $input->getCmd('view')), 'warning')
			->setRedirect('index.php?option=com_redshopb&view=dashboard', false);

		return false;
	}
}
