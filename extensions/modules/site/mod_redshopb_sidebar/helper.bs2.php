<?php
/**
 * @package     Redshopb.Frontend
 * @subpackage  mod_redshopb_sidebar
 *
 * @copyright   Copyright (C) 2012 - 2016 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

/**
 * Helper class for sidebar
 *
 * @package     Redshopb
 * @subpackage  Module.Sidebar
 * @since       1.0
 */
class ModRedshopbSidebarHelper
{
	/**
	 * Function for getting the sidebar types (menu items)
	 *
	 * @return  string  Sidebar menus
	 */
	public function getTypes()
	{
		$user      = Factory::getUser();
		$rsbUserId = RedshopbHelperUser::getUserRSid($user->id);

		$myPageMenu         = array();
		$returnOrdersGlobal = 0;
		$displayOrdersViews = RedshopbHelperACL::isSuperAdmin();

		if ($rsbUserId != 0 && $rsbUserId != '')
		{
			$company            = RedshopbEntityCompany::load(RedshopbEntityUser::getInstance($rsbUserId)->getCompany()->id);
			$contactInfo        = RedshopbHelperCommon::checkContactInfor($company);
			$displayPriceViews  = (RedshopbHelperPrices::displayPrices() || RedshopbHelperPrices::displayPrices() === false);
			$displayOrdersViews = (RedshopbHelperPrices::displayPrices()
				&& (RedshopbHelperACL::getPermission('view', 'order') || RedshopbHelperACL::getPermission('manage', 'order', array(), true)));
			$config             = RedshopbEntityConfig::getInstance();
			$returnOrdersGlobal = 1;

			if ($config)
			{
				$returnOrdersGlobal = $config->getInt('order_return', 1);
			}

			$myPageMenu = array();

			$myPageMenu[] = array(
				'heading' => true,
				'text'    => Text::_('COM_REDSHOPB_MYPAGE_HEADING'),
				'class'   => 'my-page'
			);

			$myPageMenu[] = array(
				'view'  => 'mypage',
				'icon'  => 'icon-home',
				'text'  => Text::_('COM_REDSHOPB_MYPAGE'),
				'class' => 'my-page'
			);

			$myPageMenu[] = array(
				'view'  => 'myprofile',
				'icon'  => 'icon-user',
				'text'  => Text::_('COM_REDSHOPB_MYPAGE_PROFILE'),
				'class' => 'my-profile'
			);

			if (!empty($contactInfo))
			{
				$myPageMenu[] = array(
					'view'  => 'contact_list',
					'icon'  => 'icon-group',
					'text'  => Text::_('COM_REDSHOPB_CONTACT_LIST_TITLE'),
					'class' => 'contact-list'
				);
			}

			if ($displayPriceViews && $company->get('use_wallets'))
			{
				$myPageMenu[] = array(
					'view'  => 'mywallet',
					'icon'  => 'icon-usd',
					'text'  => Text::_('COM_REDSHOPB_MYWALLET_TITLE'),
					'class' => 'my-wallet'
				);
			}

			if ($displayPriceViews && $displayOrdersViews)
			{
				$myPageMenu[] = array(
					'heading' => true,
					'text'    => Text::_('COM_REDSHOPB_ORDER_LIST_TITLE'),
					'class'   => 'order'
				);
				$myPageMenu[] = array(
					'view'  => 'quick_order',
					'icon'  => 'icon-shopping-cart',
					'text'  => Text::_('COM_REDSHOPB_QUICK_ORDER'),
					'class' => 'quick-order'
				);

				$myPageMenu[] = array(
					'view'  => 'orders',
					'icon'  => 'icon-book',
					'text'  => Text::_('COM_REDSHOPB_ORDER_LIST_TITLE'),
					'class' => 'orders'
				);

				$myPageMenu[] = array(
					'view'  => 'carts',
					'icon'  => 'icon-shopping-cart',
					'text'  => Text::_('COM_REDSHOPB_SAVED_CARTS_LIST_TITLE'),
					'class' => 'carts'
				);
				$myPageMenu[] = array(
					'view'  => 'myoffers',
					'icon'  => 'icon-certificate',
					'text'  => Text::_('COM_REDSHOPB_MYOFFERS_VIEW_DEFAULT_TITLE'),
					'class' => 'my-offers'
				);

				if ($returnOrdersGlobal)
				{
					$myPageMenu[] = array(
						'view'  => 'return_order',
						'icon'  => 'icon-truck',
						'text'  => Text::_('COM_REDSHOPB_RETURN_ORDERS_VIEW_DEFAULT_TITLE'),
						'class' => 'return-orders'
					);
				}
			}

			if ($displayPriceViews)
			{
				$myPageMenu[] = array(
					'heading' => true,
					'text'    => Text::_('COM_REDSHOPB_MYOFFER_PRODUCTS_LIST_PRODUCT'),
					'class'   => 'shop'
				);

				if (RedshopbHelperShop::inCollectionMode($company))
				{
					$myPageMenu[] = array(
						'view'  => 'mypage&layout=collections',
						'icon'  => 'icon-folder-close',
						'text'  => Text::_('COM_REDSHOPB_MY_COLLECTIONS_TITLE'),
						'class' => 'collections'
					);
				}

				$myPageMenu[] = array(
					'view'  => 'myfavoritelists',
					'icon'  => 'icon-star',
					'text'  => Text::_('COM_REDSHOPB_MYFAVORITELISTS_VIEW_DEFAULT_TITLE'),
					'class' => 'favorite-lists'
				);
				$myPageMenu[] = array(
					'view'  => 'mypage&tab=myPageMostPurchased',
					'icon'  => 'icon-book',
					'text'  => Text::_('COM_REDSHOPB_MYPAGE_MOST_PURCHASED'),
					'class' => 'most-purchased-products'
				);
				$myPageMenu[] = array(
					'view'  => 'mypage&tab=myPageRecentProducts',
					'icon'  => 'icon-book',
					'text'  => Text::_('COM_REDSHOPB_MYPAGE_RECENT_PRODUCTS'),
					'class' => 'recent-products'
				);
			}

			$myPageMenu[] = array(
				'heading' => true,
				'text'    => Text::_('COM_REDSHOPB_OTHERS'),
				'class'   => 'others'
			);
		}

		$otherMenus = array();

		$otherMenus[] = array(
			'view'  => 'dashboard',
			'icon'  => 'icon-dashboard',
			'text'  => Text::_('COM_REDSHOPB_DASHBOARD'),
			'class' => 'dashboard'
		);
		$otherMenus[] = array(
			'view'  => 'shop',
			'icon'  => 'icon-shopping-cart',
			'text'  => Text::_('COM_REDSHOPB_SHOP'),
			'class' => 'shop'
		);
		$otherMenus[] = array(
			'view'  => 'offers',
			'icon'  => 'icon-asterisk',
			'text'  => Text::_('COM_REDSHOPB_OFFER_LIST_TITLE'),
			'class' => 'offers'
		);
		$otherMenus[] = array(
			'view'  => 'carts',
			'icon'  => 'icon-shopping-cart',
			'text'  => Text::_('COM_REDSHOPB_SAVED_CARTS_LIST_TITLE'),
			'class' => 'carts'
		);
		$otherMenus[] = array(
			'view'  => 'users',
			'icon'  => 'icon-user',
			'text'  => Text::_('COM_REDSHOPB_USER_LIST_TITLE'),
			'class' => 'users'
		);
		$otherMenus[] = array(
			'view'  => 'addresses',
			'icon'  => 'icon-truck icon-flip-direction',
			'text'  => Text::_('COM_REDSHOPB_ADDRESS_LIST_TITLE'),
			'class' => 'addresses'
		);
		$otherMenus[] = array(
			'view'  => 'companies',
			'icon'  => 'icon-globe',
			'text'  => Text::_('COM_REDSHOPB_COMPANY_LIST_TITLE'),
			'class' => 'companies'
		);
		$otherMenus[] = array(
			'view'  => 'departments',
			'icon'  => 'icon-building',
			'text'  => Text::_('COM_REDSHOPB_DEPARTMENT_LIST_TITLE'),
			'class' => 'departments'
		);
		$otherMenus[] = array(
			'view'  => 'manufacturers',
			'icon'  => 'icon-suitcase',
			'text'  => Text::_('COM_REDSHOPB_MANUFACTURER_LIST_TITLE'),
			'class' => 'manufacturers'
		);
		$otherMenus[] = array(
			'view'  => 'collections',
			'icon'  => 'icon-briefcase',
			'text'  => Text::_('COM_REDSHOPB_COLLECTION_LIST_TITLE'),
			'class' => 'collections'
		);
		$otherMenus[] = array(
			'view'  => 'products',
			'icon'  => 'icon-barcode',
			'text'  => Text::_('COM_REDSHOPB_PRODUCT_LIST_TITLE'),
			'class' => 'products'
		);
		$otherMenus[] = array(
			'view'  => 'stockrooms',
			'icon'  => 'icon-archive',
			'text'  => Text::_('COM_REDSHOPB_STOCKROOMS_TITLE'),
			'class' => 'stockrooms'
		);
		$otherMenus[] = array(
			'view'   => 'all_discounts',
			'icon'   => 'icon-asterisk',
			'text'   => Text::_('COM_REDSHOPB_DISCOUNT_LIST_TITLE'),
			'class'  => 'all-discounts',
			'childs' => array(
				array(
					'view'  => 'all_discounts',
					'icon'  => 'icon-tags',
					'text'  => Text::_('COM_REDSHOPB_ALL_DISCOUNTS'),
					'class' => 'all-discounts'
				),
				array(
					'view'  => 'discount_debtor_groups',
					'icon'  => 'icon-list-alt',
					'text'  => Text::_('COM_REDSHOPB_DEBTOR_DISCOUNT_GROUPS'),
					'class' => 'discount-debtor-groups'
				),
				array(
					'view'  => 'product_discount_groups',
					'icon'  => 'icon-list-alt',
					'text'  => Text::_('COM_REDSHOPB_PRODUCT_DISCOUNT_GROUPS'),
					'class' => 'product-discount-groups'
				)
			)
		);
		$otherMenus[] = array(
			'view'   => 'all_prices',
			'icon'   => 'icon-tag',
			'text'   => Text::_('COM_REDSHOPB_PRICES'),
			'class'  => 'all-prices',
			'childs' => array(
				array(
					'view'  => 'all_prices',
					'icon'  => 'icon-tags',
					'text'  => Text::_('COM_REDSHOPB_PRODUCT_PRICE_ALL'),
					'class' => 'all-prices'
				),
				array(
					'view'  => 'price_debtor_groups',
					'icon'  => 'icon-list-alt',
					'text'  => Text::_('COM_REDSHOPB_DEBTOR_PRICE_GROUPS'),
					'class' => 'price-debtor-groups'
				)
			)
		);
		$otherMenus[] = array(
			'view'  => 'categories',
			'icon'  => 'icon-sitemap',
			'text'  => Text::_('COM_REDSHOPB_CATEGORY_LIST_TITLE'),
			'class' => 'categories'
		);

		if ($displayOrdersViews)
		{
			$otherMenus[] = array(
				'view'  => 'orders',
				'icon'  => 'icon-book',
				'text'  => Text::_('COM_REDSHOPB_ORDER_LIST_TITLE'),
				'class' => 'orders'
			);

			if ($returnOrdersGlobal)
			{
				$otherMenus[] = array(
					'view'  => 'return_orders',
					'icon'  => 'icon-book',
					'text'  => Text::_('COM_REDSHOPB_RETURN_ORDERS_LIST_TITLE'),
					'class' => 'return-orders'
				);
			}
		}

		$otherMenus[] = array(
			'view'  => 'layouts',
			'icon'  => 'icon-desktop',
			'text'  => Text::_('COM_REDSHOPB_LAYOUT_LIST_TITLE'),
			'class' => 'layouts'
		);
		$otherMenus[] = array(
			'view'  => 'tags',
			'icon'  => 'icon-tags',
			'text'  => Text::_('COM_REDSHOPB_TAG_LIST_TITLE'),
			'class' => 'tags'
		);
		$otherMenus[] = array(
			'view'  => 'wash_care_specs',
			'icon'  => 'icon-info-sign',
			'text'  => Text::_('COM_REDSHOPB_WASH_CARE_SPEC_LIST_TITLE'),
			'class' => 'wash-care-specs'
		);
		$otherMenus[] = array(
			'view'  => 'fields',
			'icon'  => 'icon-search',
			'text'  => Text::_('COM_REDSHOPB_FIELDS_LIST_TITLE'),
			'class' => 'fields'
		);
		$otherMenus[] = array(
			'view'  => 'field_groups',
			'icon'  => 'icon-link',
			'text'  => Text::_('COM_REDSHOPB_FIELD_GROUPS_LIST_TITLE'),
			'class' => 'field-groups'
		);

		$otherMenus[] = array(
			'view'  => 'filter_fieldsets',
			'icon'  => 'icon-filter',
			'text'  => Text::_('COM_REDSHOPB_FILTER_FIELDSET_LIST_TITLE'),
			'class' => 'filter-fieldsets'
		);
		$otherMenus[] = array(
			'view'   => 'newsletter_lists',
			'icon'   => 'icon-envelope',
			'text'   => Text::_('COM_REDSHOPB_NEWSLETTER_LIST_TITLE'),
			'class'  => 'newsletter-lists',
			'childs' => array(
				array(
					'view'  => 'newsletter_lists',
					'icon'  => 'icon-envelope',
					'text'  => Text::_('COM_REDSHOPB_NEWSLETTER_LIST_TITLE'),
					'class' => 'newsletter-lists'
				),
				array(
					'view'  => 'newsletters',
					'icon'  => 'icon-tag',
					'text'  => Text::_('COM_REDSHOPB_NEWSLETTERS_TITLE'),
					'class' => 'newsletters'
				)
			));
		$otherMenus[] = array(
			'view'  => 'shipping_rates',
			'icon'  => 'icon-truck',
			'text'  => Text::_('COM_REDSHOPB_SHIPPING_RATES_TITLE'),
			'class' => 'shipping-rates'
		);
		$otherMenus[] = array(
			'view'  => 'reports',
			'icon'  => 'icon-table',
			'text'  => Text::_('COM_REDSHOPB_REPORTS_TITLE'),
			'class' => 'reports'
		);
		$otherMenus[] = array(
			'view'  => 'table_locks',
			'icon'  => 'icon-lock',
			'text'  => Text::_('COM_REDSHOPB_TABLE_LOCKS_TITLE'),
			'class' => 'table_locks'
		);
		$otherMenus[] = array(
			'view'  => 'templates',
			'icon'  => 'icon-desktop',
			'text'  => Text::_('COM_REDSHOPB_TEMPLATE_LIST_TITLE'),
			'class' => 'templates'
		);
		$otherMenus[] = array(
			'view'  => 'unit_measures',
			'icon'  => 'icon-puzzle-piece',
			'text'  => Text::_('COM_REDSHOPB_UNIT_MEASURE_LIST_TITLE'),
			'class' => 'unit-measures'
		);
		$otherMenus[] = array(
			'view'  => 'words',
			'icon'  => 'icon-comments-alt',
			'text'  => Text::_('COM_REDSHOPB_WORD_LIST_TITLE'),
			'class' => 'words'
		);
		$otherMenus[] = array(
			'view'   => 'countries',
			'icon'   => 'icon-globe',
			'text'   => Text::_('COM_REDSHOPB_COUNTRY_LIST_TITLE'),
			'class'  => 'countries',
			'childs' => array(
				array(
					'view'  => 'countries',
					'icon'  => 'icon-globe',
					'text'  => Text::_('COM_REDSHOPB_COUNTRY_LIST_TITLE'),
					'class' => 'countries'
				),
				array(
					'view'  => 'states',
					'icon'  => 'icon-globe',
					'text'  => Text::_('COM_REDSHOPB_STATE_LIST_TITLE'),
					'class' => 'states'
				)
			)
		);
		$otherMenus[] = array(
			'view'   => 'taxes',
			'icon'   => 'icon-retweet',
			'text'   => Text::_('COM_REDSHOPB_TAX_LIST_TITLE'),
			'class'  => 'taxes',
			'childs' => array(
				array(
					'view'  => 'tax_groups',
					'icon'  => 'icon-retweet',
					'text'  => Text::_('COM_REDSHOPB_TAX_GROUP_LIST_TITLE'),
					'class' => 'tax-groups'
				),
				array(
					'view'  => 'taxes',
					'icon'  => 'icon-retweet',
					'text'  => Text::_('COM_REDSHOPB_TAX_LIST_TITLE'),
					'class' => 'taxes'
				)
			)
		);

		$types = array_merge($myPageMenu, $otherMenus);

		RFactory::getDispatcher()->trigger('onAfterModRedshopbSidebarHelperGetTypes', array(&$types));

		return $types;
	}

	/**
	 * Renderer Sidebar
	 *
	 * @param   array   $types    Data menu
	 * @param   string  $active   Name active view
	 * @param   bool    $isChild  Flag generate child menu
	 *
	 * @return array
	 */
	public function rendererSidebar($types, $active, $isChild = false)
	{
		if ($isChild == false)
		{
			$class = 'nav nav-list redcore';
		}
		else
		{
			$class = 'dropdown-menu';
		}

		$html        = '<ul class="' . $class . '">';
		$childActive = false;
		$config      = RedshopbEntityConfig::getInstance();
		$isShop      = $config->getInt('show_price', 1);

		// Checks ACL for displaying prices
		if ($isShop)
		{
			$user = RedshopbHelperCommon::getUser();

			if ($user->b2cMode)
			{
				$isShop = (int) RedshopbHelperACL::getGlobalB2CPermission('view', 'shopprice');
			}
			else
			{
				$isShop = (int) RedshopbHelperACL::getPermission('view', 'shopprice');
			}
		}

		$heading           = '';
		$isFromMainCompany = false;
		$rsbUserId         = RedshopbHelperUser::getUserRSid();

		if ($rsbUserId)
		{
			$isFromMainCompany = RedshopbHelperUser::isFromMainCompany($rsbUserId, 'employee');
		}

		$cGroup = 'group-submenu';

		foreach ($types as $type)
		{
			$view = '';

			if (isset($type['view']))
			{
				$view = explode('&', $type['view'])[0];
			}

			if (isset($type['heading']) && ($type['heading'] === true))
			{
				$icon = '';

				if (isset($type['icon']))
				{
					$icon = '<i class="' . $type['icon'] . '"></i> ';
				}

				$cGroup  = 'group-' . $type['class'];
				$heading = '<li class="' . $cGroup . '"><h3>' . $icon . $type['text'] . '</h3></li>';
			}
			elseif (($view == 'dashboard' && in_array(true, RedshopbHelperACL::getViewPermissions()))
				|| ($view != 'dashboard' && RedshopbHelperACL::allowDisplayView($view)))
			{
				// If shop use as catalog, then disable all view relate with checkout and prices
				if (!$isShop && in_array($view, RedshopbHelperCommon::getPriceViews()))
				{
					continue;
				}

				if ($isFromMainCompany
					&& (strpos($type['view'], 'tab=myPageMostPurchased') !== false || strpos($type['view'], 'tab=myPageRecentProducts') !== false))
				{
					continue;
				}

				$item       = 'item-' . $type['class'];
				$class      = 'class="' . $cGroup . ' ' . $item;
				$childsHtml = '';

				if (isset($type['childs']) && count($type['childs']) > 0)
				{
					$class       .= ' dropdown-submenu';
					$rendererData = $this->rendererSidebar($type['childs'], $active);
					$childsHtml   = $rendererData['html'];
					$childActive  = $rendererData['childActive'];
				}

				$class  .= '"';
				$html   .= $heading . '<li ' . $class . '>';
				$heading = '';
				$class   = '';
				$icon    = '';

				if ($active === $view)
				{
					$class       = ' class="active"';
					$childActive = true;
				}
				elseif (isset($type['childs']) && count($type['childs']) > 0 && $childActive == true)
				{
					$class       = ' class="active"';
					$childActive = false;
				}

				if (isset($type['icon']))
				{
					$icon = '<i class="' . $type['icon'] . '"></i> ';
				}

				$html .= '<a' . $class . ' href="' . RedshopbRoute::_('index.php?option=com_redshopb&view=' . $type['view']) . '">'
					. $icon . $type['text'] . '</a>' . $childsHtml;
				$html .= '</li>';
			}
		}

		return array('html' => $html . '</ul>', 'childActive' => $childActive);
	}
}
