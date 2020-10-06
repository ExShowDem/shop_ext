<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.Vanir
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('JPATH_BASE') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
/**
 * System plugin for Vanir
 *
 * @package     Joomla.Plugin
 * @subpackage  System
 * @since       1.0
 */
class PlgSystemVanir extends CMSPlugin
{
	/**
	 * Method to load redshopb library.
	 *
	 * @return  void
	 */
	public function onAfterInitialise()
	{
		JLoader::import('redshopb.library');

		if (RedshopbEntityConfig::getInstance()->getInt('save_cart_for_logged_in', '0') == '1'
			&& Factory::getApplication()->isClient('administrator') == false && RedshopbHelperACL::isSuperAdmin() == false)
		{
			$user      = RedshopbHelperUser::getUser(Factory::getUser()->id, 'joomla');
			$cartTable = RedshopbTable::getAdminInstance('Cart');

			$row = array(
				'user_id' => $user->id,
				'company_id' => $user->company,
				'name' => $user->id . '_' . $user->username,
				'user_cart' => '1'
			);

			if (!$cartTable->load($row))
			{
				return;
			}
			else
			{
				$cartId = $cartTable->get('id');
			}

			$cartEntity = RedshopbEntityCart::getInstance($cartId);

			if ($cartEntity->removeNotAvailableProducts())
			{
				Factory::getApplication()->enqueueMessage(Text::_('COM_REDSHOPB_SAVED_CART_WARNING_SOME_PRODUCTS_ARE_NOT_AVAILABLE'), 'warning');
			}

			/** @var \RedshopbModelCart $model */
			$model = RedshopbModel::getFrontInstance('Cart');

			RedshopbHelperCart::$loadingCartFromDatabase = true;
			$model->loadCart($cartId);
			RedshopbHelperCart::$loadingCartFromDatabase = false;
		}
	}

	/**
	 * onAfterRoute
	 *
	 * @return  void
	 */
	public function onAfterRoute()
	{
		$app     = Factory::getApplication();
		$input   = $app->input;
		$isAdmin = Factory::getApplication()->isAdmin();

		// Don't continue further if we are in the backend
		if ($isAdmin)
		{
			return;
		}

		$option = $input->getString('option', '');
		$view   = $input->getString('view', '');
		$task   = $input->getString('task', '');

		// In specific case for user logout, skip check
		if ($option === 'com_redshopb' && ($view === 'user_select_company' || $task === 'user_multi_company.select'))
		{
			return;
		}

		$userRSid  = RedshopbHelperUser::getUserRSid();
		$vanirUser = RedshopbEntityUser::getInstance($userRSid)->loadItem();

		if (!$vanirUser->getId())
		{
			return;
		}

		$userCompany = $vanirUser->getSelectedCompany();

		if ($userCompany)
		{
			return;
		}

		$user	 = Factory::getUser();
		$userId  = $user->get('id');
		$session = Factory::getSession();

		$session->set('prevSearched.' . $userId, $input->post->get('search'), 'redshopb');

		$app->redirect('index.php?option=com_redshopb&view=user_select_company');
	}
}
