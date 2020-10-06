<?php
/**
 * @package     Aesir.E-Commerce
 * @subpackage  Tests
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

class SiteRedshopbCompany120StructureCest
{
	public function readItemAndCheckItsStructure(ApiTester $I)
	{
		$I->wantTo("GET an existing company with its internal id");
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET(
				'index.php'
				. '?option=redshopb&view=company'
				. '&api=Hal'
				. '&webserviceClient=site'
				. '&webserviceVersion=1.2.0'
				. "&id=2"
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();

		$baseUrl = $I->getWebserviceBaseUrl();

		$I->seeResponseContainsJson(
			[
				'_links'            => [
					'curies'                   => [
							0 => [
									'href'      => "$baseUrl/index.php?option=com_redshopb&view=company&webserviceVersion=1.2.0&webserviceClient=site&format=doc&api=Hal#{rel}",
									'title'     => 'Documentation',
									'name'      => 'redshopb-company',
									'templated' => true
							]
					],
					'base'                     => [
							'href'  => "$baseUrl/?api=Hal",
							'title' => 'Default page',
					],
					'redshopb-company:list'   => [
							'href' => "$baseUrl/index.php?option=com_redshopb&view=company&webserviceVersion=1.2.0&webserviceClient=site&api=Hal",
					],
					'redshopb-company:self'   => [
							'href' => "$baseUrl/index.php?option=com_redshopb&view=company&webserviceVersion=1.2.0&webserviceClient=site&id=2&api=Hal",
					],
					'redshopb-country' =>
						[
							'href' => "$baseUrl/index.php?option=redshopb&view=country&webserviceVersion=1.0.0&webserviceClient=site&code=&api=Hal",
							'title' => 'Aesir E-Commerce - Country Webservice',
							'templated' => true,
						],
					'redshopb-currency' =>
						[
							'href' => "$baseUrl/index.php?option=redshopb&view=currency&webserviceVersion=1.0.0&webserviceClient=site&code=&api=Hal",
							'title' => 'Aesir E-Commerce - Currency Webservice',
							'templated' => true,
						],
					'redshopb-company' => [
							'href'      => "$baseUrl/index.php?option=redshopb&view=company&webserviceVersion=1.2.0&webserviceClient=site&id=0&api=Hal",
							'title'     => 'Aesir E-Commerce - Parent Company Webservice',
							'templated' => true
					]
				],
				'id'                => 2,
				'id_others'         => [0 => 'erp.main'],
				'name'              => 'Main Company',
				'name2' => '',
				'alias' => 'main',
				'image' => null,
				'parent_id' => 0,
				'parent_id_others' => null,
				'address_name1' => '',
				'address_name2' => '',
				'address_line1' => '',
				'address_line2' => '',
				'zip' => '',
				'city' => '',
				'country_code' => '',
				'employee_mandatory' => false,
				'show_stock_as' => 'not_set',
				'stockroom_verification' => true,
				'contact_info' => '',
				'currency_code' => '',
				'order_approval' => false,
				'use_wallets' => true,
				'use_collections' => false,
				'language_code' => '',
				'send_mail_on_order' => true,
				'b2c' => false,
				'customer_price_groups' => [],
				'customer_price_groups_other_ids' => [],
				'customer_discount_groups' => [],
				'customer_discount_groups_other_ids' => [],
				'state' => true,
				'delivery_addresses' => [],
				'delivery_addresses_other_ids' => [],
				'sales_persons' => [],
				'url' => '',
				'sales_persons_other_ids' => [],
			]
		);
	}
}
