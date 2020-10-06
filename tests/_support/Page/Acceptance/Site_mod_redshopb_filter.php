<?php

namespace Page\Acceptance;

class Site_mod_redshopb_filter extends \AcceptanceTester
{
	/**
	* @Then I should see the filter module in the shop under the :category category
	*/
	public function iShouldSeeTheFilterModuleInTheShop($category)
	{
		$I = $this;

		$productCategory = \Step\Acceptance\RedshopbCreate::$placeholders[$category];

		$I->amOnPage('index.php?option=com_redshopb&view=shop');
		$I->waitForElement(['link' => $productCategory], 30);
		$I->click(['link' => $productCategory]);
		$I->waitForText($productCategory, 30, ['class' => 'redshopb-shop-category-title']);

		$I->see('redSHOP B2B Filter', ['xpath' => "//div[@id='aside']/div[@class='well ']/h3"]);
		$I->see('Search products', ['xpath' => "//div[@id='aside']//div[@class='well ']//div[@class='mod_redshopb_filter_search']/h3"]);
		$I->see('Price', ['xpath' => "//div[@id='aside']//div[@class='well ']//div[@class='mod_redshopb_filter_price']/h3"]);
		$I->checkForPhpNoticesOrWarnings();
	}
}
