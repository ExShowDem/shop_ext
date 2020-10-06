<?php
/**
 * @package     Aesir.E-Commerce
 * @subpackage  Tests
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir.E-Commerce All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

/**
 * Class SiteRedshopbType120StructureCest
 * @since 2.5.0
 */
class SiteRedshopbType120StructureCest
{
	/**
	 * @var
	 * @since 2.5.0
	 */
	protected $code;

	/**
	 * @var
	 * @since 2.5.0
	 */
	protected $codename;

	/**
	 * @param ApiTester $I
	 * @since 2.5.0
	 */
	public function readItemAndCheckItsStructure(ApiTester $I)
	{
		$I->wantTo("GET an existing type and check the structure");
		$this->code = "textboxstring";
		$this->codename = "Textbox - string";
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=type'
			. '&api=hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.2.0'
			. "&code=$this->code"
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();

		$baseUrl = $I->getWebserviceBaseurl();

		$I->seeResponseContainsJson(
			[
				'_links' => [
					'curies' => [
						0 => [
							'href'      => "$baseUrl/index.php?option=com_redshopb&view=type&webserviceVersion=1.2.0&webserviceClient=site&format=doc&api=Hal#{rel}",
							'title'     => 'Documentation',
							'name'      => 'redshopb-type',
							'templated' => true
						]
					],
					'base' => [
						'href'  => "$baseUrl/?api=Hal",
						'title' => 'Default page',
					],
					'redshopb-type:list' => [
						'href'  => "$baseUrl/index.php?option=com_redshopb&view=type&webserviceVersion=1.2.0&webserviceClient=site&api=Hal",
					],
					'redshopb-type:self' => [
						'href'  => "$baseUrl/index.php?option=com_redshopb&view=type&webserviceVersion=1.2.0&webserviceClient=site&code=$this->code&api=Hal",
					]
				],
				'code'      => $this->code,
				'name'      => $this->codename,
				'multiple'  => true,
				'values'    => false
			]
		);
	}
}