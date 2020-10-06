<?php
/**
 * @package     Aesir.E-Commerce
 * @subpackage  Cest
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

class VerifyNoticesCest
{
	/**
	 * @param AcceptanceTester $I
	 * @throws Exception
	 */
	public function checkForNoticesAndWarningsInFrontend(\AcceptanceTester $I)
	{
		// Load the Step Object Page
		$allExtensionPages = array (
			'Home Screen FrontEnd'                      => 'index.php?option=com_redshopb',
			'Shop'                                      => 'index.php?option=com_redshopb&view=shop',
			'Saved Carts'                               => 'index.php?option=com_redshopb&view=carts',
			'Users Manager'                             => 'index.php?option=com_redshopb&view=users',
			'User Edit Screen'                          => 'index.php?option=com_redshopb&view=user&layout=edit',
			'Address Manager'                           => 'index.php?option=com_redshopb&view=addresses',
			'Address Edit Screen'                       => 'index.php?option=com_redshopb&view=address&layout=edit',
			'Companies Manager'                         => 'index.php?option=com_redshopb&view=companies',
			'Company Edit Screen'                       => 'index.php?option=com_redshopb&view=company&layout=edit',
			'Departments Manager'                       => 'index.php?option=com_redshopb&view=departments',
			'Department Edit Screen'                    => 'index.php?option=com_redshopb&view=department&layout=edit',
			'Manufacturers Manager'                     => 'index.php?option=com_redshopb&view=manufacturers',
			'Manufacturer Edit Screen'                  => 'index.php?option=com_redshopb&view=manufacturer&layout=edit',
			'Collections Manager'                       => 'index.php?option=com_redshopb&view=collections',
			'Collection Edit Screen'                    => 'index.php?option=com_redshopb&view=collection&layout=create',
			'Products Manager'                          => 'index.php?option=com_redshopb&view=products',
			'Product Edit Screen'                       => 'index.php?option=com_redshopb&view=product&layout=edit',
			'Discounts Manager'                         => 'index.php?option=com_redshopb&view=all_discounts',
			'Discount Edit Screen'                      => 'index.php?option=com_redshopb&view=all_discount&layout=edit',
			'Debtor Discount Groups Manager'            => 'index.php?option=com_redshopb&view=discount_debtor_groups',
			'Debtor Discount Group Edit Screen'         => 'index.php?option=com_redshopb&view=discount_debtor_group&layout=edit',
			'Product Discount Groups Manager'           => 'index.php?option=com_redshopb&view=product_discount_groups',
			'Product Discount Group Edit Screen'        => 'index.php?option=com_redshopb&view=product_discount_group&layout=edit',
			'All Prices Manager'                        => 'index.php?option=com_redshopb&view=all_prices',
			'All Price Edit Screen'                     => 'index.php?option=com_redshopb&view=all_price&layout=edit',
			'Debtor Group Manager'                      => 'index.php?option=com_redshopb&view=price_debtor_groups',
			'Debtor Group Edit Screen'                  => 'index.php?option=com_redshopb&view=price_debtor_group&layout=edit',
			'Categories Manager'                        => 'index.php?option=com_redshopb&view=categories',
			'Category Edit Screen'                      => 'index.php?option=com_redshopb&view=category&layout=edit',
			'Order Manager'                             => 'index.php?option=com_redshopb&view=orders',
			'Return Orders Manager'                     => 'index.php?option=com_redshopb&view=return_orders',
			'Layouts Manager'                           => 'index.php?option=com_redshopb&view=layouts',
			'Layout Edit Screen'                        => 'index.php?option=com_redshopb&view=layout&layout=edit',
			'Tags Manager'                              => 'index.php?option=com_redshopb&view=tags',
			'Tag Edit Screen'                           => 'index.php?option=com_redshopb&view=tag&layout=edit',
			'Wash and Care Manager'                     => 'index.php?option=com_redshopb&view=wash_care_specs',
			'Wash and Care Edit Screen'                 => 'index.php?option=com_redshopb&view=wash_care_spec&layout=edit',
			'Mailing List Manager'                      => 'index.php?option=com_redshopb&view=newsletter_lists',
			'Mailing List Edit Screen'                  => 'index.php?option=com_redshopb&view=newsletter_list&layout=edit',
			'Newsletters List Manager'                  => 'index.php?option=com_redshopb&view=newsletters',
			'Shipping Rates Manager'                    => 'index.php?option=com_redshopb&view=shipping_rates',
			'Shipping Rate Edit Screen'                 => 'index.php?option=com_redshopb&view=shipping_rate&layout=edit',
			'Reports Manager'                           => 'index.php?option=com_redshopb&view=reports',
			'Reports: Sales: Orders '                   => 'index.php?option=com_redshopb&view=report_sales_orders',
			'Reports: Sales: Shippings'                 => 'index.php?option=com_redshopb&view=report_sales_shipping',
			'Reports: Customers: New accounts'          => 'index.php?option=com_redshopb&view=report_customers_new',
			'Reports: Customers: Most orders'           => 'index.php?option=com_redshopb&view=report_customers_most_orders',
			'Reports: Products: Best selling products'  => 'index.php?option=com_redshopb&view=report_products_top_sellers',
			'Reports: Products: Most views'             => 'index.php?option=com_redshopb&view=report_products_top_views',
			'Reports: Products: Low Stock'              => 'index.php?option=com_redshopb&view=report_products_low_stock',
			'Reports: Products: In Cart'                => 'index.php?option=com_redshopb&view=report_products_in_carts',
			'Reports: General: Newsletter Delivery'     => 'index.php?option=com_redshopb&view=report_general_newsletter',
			'Template List Manager'                     => 'index.php?option=com_redshopb&view=templates',
			'Template Edit Screen'                      => 'index.php?option=com_redshopb&view=template&layout=edit',
			'My Page'                                   => 'index.php?option=com_redshopb&view=mypage',
			'Fields Manager'                            => 'index.php?option=com_redshopb&view=fields',
			'Fields Edit Screen'                        => 'index.php?option=com_redshopb&view=field&layout=edit',
			'Filter fieldsets Manager'                  => 'index.php?option=com_redshopb&view=filter_fieldsets',
			'Filter fieldsets Edit Screen'              => 'index.php?option=com_redshopb&view=filter_fieldset&layout=edit'
		);

		$I->doAdministratorLogin();
		$I->doFrontEndLogin();

		foreach ($allExtensionPages as $page => $url)
		{
			$I->checkForPhpNoticesOrWarnings($url);
		}
	}
}

