<?php
/**
 * @package     Aesir.E-Commerce.Libraries
 * @subpackage  Helper
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
/**
 * Represents a menu node (link).
 *
 * @package     Aesir.E-Commerce
 * @subpackage  Menu
 * @since       1.0
 */
final class RedshopbHelperCommon
{
	/**
	 * @var array
	 */
	public static $users = array();

	/**
	 * Views not requiring a logged in user
	 *
	 * @return  array
	 *
	 * @deprecated this method is no longer needed with the new specification system
	 *               to add views that don't require login please update access.xml specifications
	 */
	public static function getNoLoginViews()
	{
		return array('b2buserregister', 'taglist', 'manufacturerlist');
	}

	/**
	 * Get list views, when use prices
	 *
	 * @return array
	 */
	public static function getPriceViews()
	{
		return array(
			'all_discount', 'all_discounts', 'all_price', 'all_prices', 'carts', 'currencies', 'currency',
			'discount_debtor_group', 'discount_debtor_groups', 'myoffer', 'myoffers', 'mywallet', 'offer', 'offers',
			'order', 'orders', 'payment_configuration', 'price_debtor_group', 'price_debtor_groups',
			'product_discount_group', 'product_discount_groups', 'quick_order', 'shipping_configuration', 'shipping_rate',
			'tax_groups', 'tax_group', 'taxes', 'tax'
		);
	}

	/**
	 * Method for get User object. Also check B2C Mode.
	 *
	 * @param   integer  $userId  ID of user
	 *
	 * @return  stdClass          User data
	 */
	public static function getUser($userId = 0)
	{
		$userId = (int) $userId;

		if (!array_key_exists($userId, self::$users))
		{
			if (!$userId)
			{
				$user = Factory::getUser();
			}
			else
			{
				$user = Factory::getUser($userId);
			}

			$userId = $user->id;

			self::$users[$userId]             = $user;
			self::$users[$userId]->b2cMode    = false;
			self::$users[$userId]->b2cCompany = 0;
			$b2cCompany                       = RedshopbHelperCompany::getCompanyB2C();

			if (self::$users[$userId]->guest && $b2cCompany)
			{
				self::$users[$userId]->b2cMode    = true;
				self::$users[$userId]->b2cCompany = $b2cCompany;
			}
		}

		return self::$users[$userId];
	}

	/**
	 * Method for check if contact_infor has been show or not.
	 *
	 * @param   RedshopbEntityCompany  $company  Company data
	 *
	 * @return  boolean  True if has contact infor. False otherwise.
	 */
	public static function checkContactInfor(RedshopbEntityCompany $company)
	{
		if (!$company || !$company->isLoaded())
		{
			return false;
		}

		// If Aesir - RedSHOPB2B sync plugin enabled. Render Contact Information by Aesir.
		if (PluginHelper::isEnabled('aesir', 'redshopb_twig')
			&& PluginHelper::isEnabled('extension', 'redshopb_aesir_sync')
			&& PluginHelper::isEnabled('kvasir_sync', 'vanir_company_user'))
		{
			return true;
		}

		$vendor      = $company->getVendor();
		$mainCompany = RedshopbApp::getMainCompany();

		return !$vendor->get('contact_info') ? $mainCompany->get('contact_info') : $vendor->get('contact_info');
	}

	/**
	 * Init Cart Script
	 *
	 * @return  boolean  Status cart script initialization
	 *
	 * @since 1.13.0
	 */
	public static function initCartScript()
	{
		static $init = null;

		if (!is_null($init))
		{
			return $init;
		}

		$init   = false;
		$config = RedshopbApp::getConfig();
		$isShop = $config->get('show_price', 1);
		$user   = self::getUser();

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

		if ($isShop && ($user->get('id') || $user->b2cMode))
		{
			$init = true;
			RHelperAsset::load('cart.css', 'com_redshopb');
			RHelperAsset::load('redshopb.cart.js', 'com_redshopb');
			Text::script('COM_REDSHOPB_NOTHING_SELECTED');
			Text::script('COM_REDSHOPB_MYFAVORITELIST_PRODUCT_ADDED_SUCESSFULY');
			Text::script('COM_REDSHOPB_MYFAVORITELIST_REMOVED_SUCCESSFULLY');
			Text::script('COM_REDSHOPB_MYFAVORITELIST_PRODUCT_SUCCESSFULLY_ADDED_TO');
		}

		return $init;
	}
}
