<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  mod_redshopb_status
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\Registry\Registry;
use Joomla\CMS\Router\Route;

JLoader::import('redshopb.library');

$redshopbConfig = RedshopbApp::getConfig();

RHtmlMedia::setFramework($redshopbConfig->getString('default_frontend_framework', 'bootstrap3'));
HTMLHelper::stylesheet('mod_redshopb_status/mod_redshopb_status.css', array('relative' => true));

$app    = Factory::getApplication();
$config = RedshopbApp::getConfig();
$lang   = Factory::getLanguage();

$lang->load('mod_redshopb_status', __DIR__);

$isShop = $config->get('show_price', 1);
$user   = RedshopbHelperCommon::getUser();

// Checks ACL for displaying prices
if ($isShop)
{
	if ($user->b2cMode)
	{
		$isShop = (int) RedshopbHelperACL::getGlobalB2CPermission('view', 'shopprice');
	}
	else
	{
		$isShop = (int) RedshopbHelperACL::getPermission('view', 'shopprice');
	}
}

$customerType = $app->getUserState('shop.customer_type', '');
$customerId   = $app->getUserState('shop.customer_id', 0);

if (empty($customerId))
{
	$customerType = 'employee';
	$customerId   = RedshopbHelperUser::getUserRSid();
}

$showTaxes = $config->getInt('show_taxes_in_cart_module', 1);

$cartData      = new RedshopbHelperCart_Object;
$numberOfItems = 0;
$cartTotal     = RedshopbHelperCart::getCustomerCartTotals($customerId, $customerType, $showTaxes);
$taxList       = RedshopbHelperCart::getCustomerCartTaxes($customerId, $customerType);

$lang = $app->input->getVar('lang', '');

if (RedshopbHelperCommon::initCartScript())
{
	$cartData      = RedshopbHelperCart::getCart($customerId, $customerType);
	$numberOfItems = RedshopbHelperCart::getCartItemQuantities($customerId, $customerType);
}

// Searchs for session data to see if return URL is available, to avoid generating it each time
$session = Factory::getSession();
$return  = $session->get('logoutReturn' . (!empty($lang) . '-' . $lang), '', 'com_redshopb');

if ($user->get('id'))
{
	if ($return == '')
	{
		$menus           = $app->getMenu()->getMenu();
		$currentLayoutId = RedshopbHelperLayout::getCurrentLayout();

		// Used for selecting the first Dashboard in case the current layout does not match any menu item selcted layout
		$defaultReturn = '';

		// Extracts all dashboard links to see what's the appropriate one for the current layout when logging out.
		foreach ($menus as $menu)
		{
			if (!empty($menu->query['option']) && $menu->query['option'] == 'com_redshopb' && $menu->query['view'] == 'dashboard' && $return == '')
			{
				if ($defaultReturn == '')
				{
					$defaultReturn = Route::_($menu->link . '&Itemid=' . $menu->id . (!empty($lang) ? '&lang=' . $lang : ''));
				}

				if ($currentLayoutId)
				{
					$menuparams = new Registry($menu->params);
					$companyId  = 0;
					$data       = $menuparams->toArray();

					$companyLayout = 0;

					$companyId = $menuparams->get('companyid', 0);

					if (!$companyId)
					{
						$data = $menuparams->get('data', null);

						if ($data)
						{
							$companyId = (int) $data->companyid;
						}
					}

					if ($companyId)
					{
						/**
						 * If the selected company's layout (in the menu item) is the current layout,
						 * then set this as the return address after logging out
						 */

						$companyLayout = RedshopbHelperLayout::getCompanyLayout($companyId);

						if ($companyLayout)
						{
							if ($companyLayout->id == $currentLayoutId)
							{
								$return = Route::_($menu->link . '&Itemid=' . $menu->id . (!empty($lang) ? '&lang=' . $lang : ''), false);
							}
						}
					}

					continue;
				}

				$return = Route::_($menu->link . '&Itemid=' . $menu->id . (!empty($lang) ? '&lang=' . $lang : ''), false);
			}
		}

		if ($return == '')
		{
			$return = $defaultReturn;
		}

		$session->set('logoutReturn' . (!empty($lang) . '-' . $lang), $return, 'com_redshopb');
	}

	$return = base64_encode($return);

	$rsbUserId = RedshopbHelperUser::getUserRSid($user->id);
}

$cartUpdateQuantity          = (boolean) $params->get('cart_update_quality', true);
$showRestoreSaveCartDropdown = (boolean) $params->get('show_restore_save_cart_dropdown', false);
$cartDisplaySKU              = (boolean) $params->get('cart_product_sku', true);
$canImpersonate              = RedshopbHelperACL::getPermissionInto('impersonate', 'order');
$companiesVendor             = RedshopbEntityConfig::getInstance()->get('vendor_of_companies', 'parent');
$displayImpersonationButtons = (bool) $params->get('shop_changecustomer_buttons', 0) && $canImpersonate;
$showDiscColumn              = (int) $params->get('cart_show_discount_column', 1);
$displayImportCsvButton      = (boolean) $params->get('display_import_csv_button', 0);

$app->setUserState('shop.cart.show_restore_save_cart_dropdown', $showRestoreSaveCartDropdown);
$app->setUserState('shop.cart.update_quantity', $cartUpdateQuantity);
$app->setUserState('shop.cart.display_sku', $cartDisplaySKU);

$moduleLayout = RModuleHelper::getLayoutPath('mod_redshopb_status');
require $moduleLayout;
