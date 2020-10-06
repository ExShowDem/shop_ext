<?php
/**
 * @package     Aesir.E-Commerce
 * @subpackage  Tests
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir.E-Commerce All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

/**
 * Class SiteRedshopbType110CrudCest
 * @since 2.5.0
 */
class SiteRedshopbType110CrudCest
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
	public function readItem(ApiTester $I)
	{
		$I->wantTo("GET an existing type");
		$this->code     = "textboxstring";
		$this->codename = "Textbox - string";
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=type'
			. '&api=hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
			. "&code=$this->code"
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->seeResponseContains('"name":', "$this->codename");
	}

	/**
	 * @param ApiTester $I
	 * @throws Exception
	 * @since 2.5.0
	 */
	public function readList(ApiTester $I)
	{
		$I->wantTo("Get a list of type");
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=type'
			. '&api=hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
		);

		$I->seeResponsecontainsJson(['code' => $this->code]);
		$I->seeResponseContainsJson(['name' => $this->codename]);
	}
}