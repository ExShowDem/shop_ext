<?php
/**
 * @package     Aesir.E-Commerce
 * @subpackage  Tests
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

class SiteRedshopbTemplate100StructureCest
{
	public function readItemAndCheckItsStructure(ApiTester $I)
	{
		$I->wantTo("GET an existing template with its internal id");
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET(
			'index.php'
			. '?option=redshopb&view=template'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.0.0'
			. "&code=generic-mail-template"
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();

		$baseUrl = $I->getWebserviceBaseUrl();

		$I->seeResponseContainsJson(
			[
			'_links' =>
				[
					'curies' =>
						[
						0 =>
							[
								'href' => "$baseUrl/index.php?option=com_redshopb&view=template&webserviceVersion=1.0.0&webserviceClient=site&format=doc&api=Hal#{rel}",
								'title' => 'Documentation',
								'name' => 'redshopb-template',
								'templated' => true,
							],
						],
					'base' =>
						[
						'href' => "$baseUrl/?api=Hal",
						'title' => 'Default page',
						],
					'redshopb-template:list' =>
						[
						'href' => "$baseUrl/index.php?option=com_redshopb&view=template&webserviceVersion=1.0.0&webserviceClient=site&api=Hal",
						],
					'redshopb-template:self' =>
						[
						'href' => "$baseUrl/index.php?option=com_redshopb&view=template&webserviceVersion=1.0.0&webserviceClient=site&code=generic-mail-template&api=Hal",
						],
				],
			'code' => 'generic-mail-template',
			'name' => 'Generic mail template',
			'group' => 'email',
			'scope' => 'email',
		]
		);
	}
}
