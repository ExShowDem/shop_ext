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
 * Class OffersEnabled
 *
 * @package  Vanir\Access\Rules
 * @since    2.0
 */
class OffersEnabled extends Rule
{
	/**
	 * Method to check if offer system is enabled
	 *
	 * @param   \RedshopbEntityUser  $user   the user asking for permission
	 * @param   Input                $input  input variables to be used by the rules
	 *
	 * @return boolean
	 */
	protected function check($user, $input)
	{
		$config = \RedshopbApp::getConfig();

		if ($config->getInt('enable_offer', 1) == 1)
		{
			return true;
		}

		$this->specificationContainer
			->addMessage(Text::sprintf('COM_REDSHOPB_VIEW_DISABLED', $input->getCmd('view')), 'warning')
			->setRedirect();

		return false;
	}
}
