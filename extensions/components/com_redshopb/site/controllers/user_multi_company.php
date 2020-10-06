<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Controllers
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;

/**
 * User multi company Controller
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Controllers
 * @since       1.0
 */
class RedshopbControllerUser_Multi_Company extends RedshopbControllerForm
{
	/**
	 * User has selected one of the companies so he is Logged in as user for that specific company
	 *
	 * @return  void
	 */
	public function select()
	{
		$app   = Factory::getApplication();
		$input = $app->input;

		$companyId     = $input->getInt('company_id', null);
		$userRSid      = RedshopbHelperUser::getUserRSid();
		$vanirUser     = RedshopbEntityUser::getInstance($userRSid)->loadItem();
		$userCompanies = $vanirUser->getUserMultiCompanies();

		// Check again to see if the user has enough Permissions to be on this company
		foreach ($userCompanies as $userCompany)
		{
			if ($companyId == $userCompany->company_id)
			{
				$app->setUserState('shop.multi_company_id', $userCompany->company_id);
				$app->setUserState('shop.role_type_id', $userCompany->role_id);
				$item             = $vanirUser->getItem();
				$item->company_id = $userCompany->company_id;
				RedshopbHelperCart::clearCartFromSession(true);

				// Since we don't allow SSO when users haven't agreed to the terms, we'll run them now
				PluginHelper::importPlugin('user');
				$app->triggerEvent('setVanirUserDefaultValues');

				break;
			}
		}

		$user	 = Factory::getUser();
		$userId  = $user->get('id');
		$session = Factory::getSession();

		if ($session->get('prevSearched.' . $userId, '', 'redshopb'))
		{
			JLoader::register('RedshopbControllerShop', JPATH_ROOT . '/components/com_redshopb/controllers/shop.php');

			$controllerShop = new RedshopbControllerShop;

			$controllerShop->search();
		}

		$this->setRedirect('index.php');
	}
}
