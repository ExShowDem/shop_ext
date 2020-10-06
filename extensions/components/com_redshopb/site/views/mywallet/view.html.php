<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Views
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
/**
 * My Wallet View
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Views
 * @since       1.0
 */
class RedshopbViewMyWallet extends RedshopbView
{
	/**
	 * Do we have to display a sidebar?
	 *
	 * @var  boolean
	 */
	protected $displaySidebar = false;

	/**
	 * Display method.
	 *
	 * @param   string  $tpl  The template name
	 *
	 * @return  void
	 */
	public function display($tpl = null)
	{
		$user = RedshopbEntityUser::loadActive();

		// Get wallet status
		$this->wallets = RedshopbHelperWallet::getUserWallet($user->getWebSafeProperty('id'));

		// Get user recent purchases
		$ordersModel = RedshopbModel::getInstance('Orders', 'RedshopbModel');
		$ordersModel->setState('filter.user_id', (int) $user->getWebSafeProperty('id'));

		$this->recent_purchases = $ordersModel->getItems();

		parent::display($tpl);
	}

	/**
	 * Get the view title.
	 *
	 * @return  string  The view title.
	 */
	public function getTitle()
	{
		return Text::_('COM_REDSHOPB_VIEW_MYWALLETS');
	}
}
