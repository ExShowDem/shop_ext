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
 * Class PricesEnabled
 *
 * @package  Vanir\Access\Rules
 * @since    2.0
 */
class PricesEnabled extends Rule
{
	/**
	 * Method to check if the price system is enabled
	 *
	 * @param   \RedshopbEntityUser  $user   the user asking for permission
	 * @param   Input                $input  input variables to be used by the rules
	 *
	 * @return boolean
	 */
	protected function check($user, $input)
	{
		$config           = \RedshopbApp::getConfig();
		$configShowPrices = ($config->getInt('show_price', 1) == 1);

		$isShop           = \RedshopbHelperPrices::displayPrices();
		$shouldShowPrices = ($isShop || $isShop === false);

		$canViewPrices = ($user->isFromMainCompany()) ? $configShowPrices : $shouldShowPrices;

		if ($canViewPrices)
		{
			return true;
		}

		$this->specificationContainer
			->addMessage(Text::sprintf('COM_REDSHOPB_VIEW_DISABLED', $input->getCmd('view')), 'warning')
			->setRedirect();

		return false;
	}
}
