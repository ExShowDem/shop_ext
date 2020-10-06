<?php
/**
 * @package  AcceptanceTester
 *
 * @since    1.4
 *
 * @link     http://codeception.com/docs/07-AdvancedUsage#StepObjects
 */

namespace Step\Frontend;

use Page\Frontend\AllPricePage as AllPricePage;
use Page\Frontend\ProductPage as ProductPage;
class AllPriceSteps extends \Step\Acceptance\redshopb2b
{
	/**
	 * @param $product
	 * @param $SKU
	 * @param $priceEdit
	 * @throws \Exception
	 */
	public function edit($product, $SKU, $priceEdit)
	{
		$I = $this;
		$I->am('Administrator');
		$I->wantToTest('Price edit in Frontend');
		$I->wait(1);
		$I->amGoingTo('Navigate to Prices page in frontend');
		$I->amOnPage(AllPricePage::$URL);
		$I->searchForItemInFrontend($product, ['search field locator id' => AllPricePage::$searchProduct]);
		$I->waitForElement(['link'=>$SKU], 30);
		$I->wait(1);
		$I->checkAllResults();
		$I->click(AllPricePage::$editButton);
		$I->wait(0.5);
		$I->comment('I am redirected to the form');
		$I->waitForElement(AllPricePage::$adminForm, 30);
		$I->wait(0.5);
		$I->waitForElement(ProductPage::$price,30);
		$I->fillField(ProductPage::$price, $priceEdit);
		$I->waitForElement(AllPricePage::$saveCloseButton, 30);
		$I->wait(1);
		$I->click(AllPricePage::$saveCloseButton);
		$I->waitForText(AllPricePage::$messageSaveSuccess, 30, AllPricePage::$messageSuccessID);
		$I->comment('I am redirected to the list');
		$I->searchForItemInFrontend($product, ['search field locator id' => AllPricePage::$searchProduct]);
		$I->waitForElement(['link' => $SKU], 30);
		$I->click(['link' => $SKU]);
		$I->waitForElement(ProductPage::$price,30);
		$I->seeInField(ProductPage::$price,$priceEdit);
	}
	
	/**
	 * @param $product
	 * @throws \Exception
	 */
	public function delete($product)
	{
		$I = $this;
		$I->wantToTest('Delete a department in Frontend');
		$I->amGoingTo('Navigate to Prices page in frontend');
		$I->amOnPage(AllPricePage::$URL);
		$I->searchForItemInFrontend($product, ['search field locator id' => AllPricePage::$searchProduct]);
		$I->waitForElement(AllPricePage::$deleteButton, 30);
		$I->checkAllResults();
		$I->click(AllPricePage::$deleteButton);

		$I->comment('I am redirected to the list');
		$I->waitForElement(['id' => AllPricePage::$searchProduct], 30);
		$I->dontSeeElement(['link' => $product]);
	}
}