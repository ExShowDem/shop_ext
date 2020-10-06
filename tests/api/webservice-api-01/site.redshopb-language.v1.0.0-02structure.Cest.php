<?php
/**
 * @package     Aesir.E-Commerce
 * @subpackage  Tests
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

class SiteRedshopbLanguage100StructureCest
{
	public function readItemAndCheckItsStructure(ApiTester $I)
	{
		$I->wantTo("GET an existing language with its internal id");
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET(
				'index.php'
				. '?option=redshopb&view=language'
				. '&api=Hal'
				. '&webserviceClient=site'
				. '&webserviceVersion=1.0.0'
				. "&code=en-GB"
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
											'href' => "$baseUrl/index.php?option=com_redshopb&view=language&webserviceVersion=1.0.0&webserviceClient=site&format=doc&api=Hal#{rel}",
											'title' => 'Documentation',
											'name' => 'redshopb-language',
											'templated' => true,
										]
								],
						'base' =>
								[
									'href' => "$baseUrl/?api=Hal",
									'title' => 'Default page',
								],
						'redshopb-language:list' =>
								[
									'href' => "$baseUrl/index.php?option=com_redshopb&view=language&webserviceVersion=1.0.0&webserviceClient=site&api=Hal",
								],
						'redshopb-language:self' =>
								[
									'href' => "$baseUrl/index.php?option=com_redshopb&view=language&webserviceVersion=1.0.0&webserviceClient=site&code=en-GB&api=Hal",
								]
					],
				'fields' =>
					[
						'code' => 'en-GB',
						'name' => 'English (en-GB)',
					]
			]
		);
	}
}
