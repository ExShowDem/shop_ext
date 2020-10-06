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
 * Class CustomerRequired
 *
 * @package  Vanir\Access\Rules
 * @since    2.0
 */
class CustomerRequired extends Rule
{
	/**
	 * @var \stdClass
	 */
	protected $userState = null;

	/**
	 * Method to insure there is a valid $customerId/$customerType
	 *
	 * @param   \RedshopbEntityUser  $user   the user asking for permission
	 * @param   Input                $input  input variables to be used by the rules
	 *
	 * @return boolean
	 */
	protected function check($user, $input)
	{
		$layout           = $input->getCmd('layout', 'default');
		$limitedToLayouts = $this->getLayoutScope();
		$isInScope        = (empty($limitedToLayouts) || in_array($layout, $limitedToLayouts));

		$userState           = $this->getUserStates();
		$canIdentifyCustomer = (!empty($userState->customerType) && (!empty($userState->customerId)));

		if (!$isInScope || $canIdentifyCustomer)
		{
			return true;
		}

		$this->specificationContainer
			->addMessage(Text::_('PLG_REDSHOPB_NEED_IMPERSONATE'), 'warning')
			->setRedirect('index.php?option=com_redshopb&view=shop&layout=default' . $this->getReturn(), false);

		return false;
	}

	/**
	 * Method to get the user state
	 *
	 * this is a proxy to \RedshopbHelperShop::setUserStates
	 *
	 * @see \RedshopbHelperShop::setUserStates
	 * @return stdClass
	 */
	private function getUserStates()
	{
		if (!empty($this->userState))
		{
			return $this->userState;
		}

		$userState = new \stdClass;
		\RedshopbHelperShop::setUserStates($userState);

		$this->userState = $userState;

		return $this->userState;
	}
}
