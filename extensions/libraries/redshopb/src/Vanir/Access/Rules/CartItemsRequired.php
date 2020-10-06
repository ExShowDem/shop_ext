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
 * Class CartItemsRequired
 *
 * @package  Vanir\Access\Rules
 * @since    2.0
 */
class CartItemsRequired extends Rule
{
	/**
	 * Method to check if there are items in the cart
	 *
	 * @param   \RedshopbEntityUser  $user   the user asking for permission
	 * @param   Input                $input  input variables to be used by the rules
	 *
	 * @return boolean
	 */
	protected function check($user, $input)
	{
		$cartItemQuantities = \RedshopbHelperCart::getCartItemQuantities();
		$scope              = $this->getLayoutScope();
		$layout             = $input->get('layout', 'default');

		$isInScope = (empty($scope) || in_array($layout, $scope));

		if (!$isInScope || !empty($cartItemQuantities))
		{
			return true;
		}

		$this->specificationContainer
			->addMessage(Text::_('COM_REDSHOPB_CART_PLEASE_ADD_A_PRODUCT_TO_PROCEED_TO_CHECKOUT'), 'warning')
			->setRedirect('index.php?option=com_redshopb&view=shop', false);

		return false;
	}
}
