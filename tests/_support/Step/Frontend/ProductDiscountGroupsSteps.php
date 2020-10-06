<?php
/**
 * @package  AcceptanceTester
 *
 * @since    2.2
 *
 * @link     http://codeception.com/docs/07-AdvancedUsage#StepObjects
 */

namespace Step\Frontend;
use Page\Frontend\ProductDiscountGroupsPage as ProductDiscountGroupsPage;
use PHPUnit\Runner\Exception;
use Step\Acceptance\redshopb2b as redshopb2b;
class ProductDiscountGroupsSteps extends redshopb2b
{
	/**
	 * @param $name
	 * @param $code
	 * @param $products
	 * @throws \Exception
	 */
	public function create($company, $name, $code, $products)
	{
		$I = $this;
		$I->amOnPage(ProductDiscountGroupsPage::$URL);
		$I->click(ProductDiscountGroupsPage::$newButton);
		$I->waitForElementVisible(ProductDiscountGroupsPage::$adminForm,30);
		$I->selectOptionInChosenjs(ProductDiscountGroupsPage::$labelOwnerCompany, $company);
		$I->waitForElementVisible(ProductDiscountGroupsPage::$nameID, 30);
		$I->fillField(ProductDiscountGroupsPage::$nameID, $name);
		$I->waitForElementVisible(ProductDiscountGroupsPage::$codeId, 30);
		$I->fillField(ProductDiscountGroupsPage::$codeId, $code);

		$I->comment('Add multi product');
		if ($products != null)
		{
			foreach($products as $product )
			{
				$I->comment($product);
				$I->waitForElementVisible(ProductDiscountGroupsPage::$productInput, 30);
				$I->fillField(ProductDiscountGroupsPage::$productInput, $product);
				$I->wait(2);
				$I->pressKey(ProductDiscountGroupsPage::$productInput, \Facebook\WebDriver\WebDriverKeys::ENTER);
			}
		}
		$I->wait(1);
		$I->click(ProductDiscountGroupsPage::$saveCloseButton);
		$I->searchForItemInFrontend($name,['search field locator id' => ProductDiscountGroupsPage::$searchId]);
	}
	
	/**pau
	 *
	 *
	 * @param $name
	 * @param $nameEdit
	 * @throws \Exception
	 */
	public function update($name, $nameEdit)
	{
		$I = $this;
		$I->amOnPage(ProductDiscountGroupsPage::$URL);
		$I->searchForItemInFrontend($name,['search field locator id' => ProductDiscountGroupsPage::$searchId]);
		$I->waitForElementVisible(['link' =>$name], 30);
		$I->wait(0.5);
		$I->click(['link' => $name]);
		$I->wait(1);
		$I->waitForJS('return !!window.jQuery && window.jQuery.active == 0;', 30);
		$I->waitForElementVisible(ProductDiscountGroupsPage::$adminForm,30);
		$I->fillField(ProductDiscountGroupsPage::$nameID, $nameEdit);
		$I->wait(1);
		$I->click(ProductDiscountGroupsPage::$saveCloseButton);
		$I->searchForItemInFrontend($nameEdit,['search field locator id' => ProductDiscountGroupsPage::$searchId]);
		$I->dontSeeElement(['link' => $name]);
	}
	
	/**
	 * @param $name
	 * @throws \Exception
	 */
	public function delete($name)
	{
		$I = $this;
		$I->amOnPage(ProductDiscountGroupsPage::$URL);
		$I->searchForItemInFrontend($name,['search field locator id' => ProductDiscountGroupsPage::$searchId]);
		$I->waitForElementVisible(['link'=>$name], 30);
		$I->checkAllResults();
		$I->click(ProductDiscountGroupsPage::$deleteButton);
		$I->searchForItemInFrontend($name,['search field locator id' => ProductDiscountGroupsPage::$searchId]);
		$I->dontSeeElement(['link'=> $name]);
	}
}
