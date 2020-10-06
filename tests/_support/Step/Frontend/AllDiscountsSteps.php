<?php
/**
 * @package  AcceptanceTester
 *
 * @since    2.4
 *
 * @link     http://codeception.com/docs/07-AdvancedUsage#StepObjects
 */

namespace Step\Frontend;

use Page\Frontend\AllDiscountsPage as AllDiscountsPage;
use Step\Acceptance\redshopb2b;

class AllDiscountsSteps extends redshopb2b
{
	/**
	 * @param $productName
	 * @param $percent
	 * @param $discountType
	 * @param $salesType
	 * @param $status
	 * @param $curency
	 * @throws \Exception
	 */
	public function createDiscountPercent($productName, $percent, $discountType, $salesType, $status, $curency)
	{
		$I = $this;
		$I->amOnPage(AllDiscountsPage::$url);
		$I->checkForPhpNoticesOrWarnings();
		$I->waitForElement(AllDiscountsPage::$newButton, 30);
		$I->click(AllDiscountsPage::$newButton);
		$I->comment('I am redirected to the form');
		$I->waitForElement(AllDiscountsPage::$adminForm, 30);
		$I->selectOptionInChosenjs(AllDiscountsPage::$discountTypeLabel, $discountType);
		$I->waitForElementVisible(AllDiscountsPage::$productId, 60);
		$I->selectOptionInSelect2(AllDiscountsPage::$productLabel, $productName);
		$I->selectOptionInChosenjs(AllDiscountsPage::$salesTypeLabel, $salesType);
		$I->selectOptionInRadioField(AllDiscountsPage::$statusLabel, $status);
		$I->selectOptionInChosenjs(AllDiscountsPage::$currencyLabel, $curency);
		$I->waitForElement(AllDiscountsPage::$discountPercent, 30);
		$I->click(AllDiscountsPage::$discountPercent);
		$I->waitForElement(AllDiscountsPage::$percentId, 30);
		$I->fillField(AllDiscountsPage::$percentId, $percent);
		$I->waitForElement(AllDiscountsPage::$saveCloseButton, 30);
		$I->click(AllDiscountsPage::$saveCloseButton);
		$I->wait(1);
		$I->waitForText(AllDiscountsPage::$productLabel, 30);
	}

	/**
	 * @param $productName
	 * @param $total
	 * @param $discountType
	 * @param $salesType
	 * @param $status
	 * @param $curency
	 * @throws \Exception
	 */
	public function createDiscountTotal($productName, $total, $discountType, $salesType, $status, $curency)
	{
		$I = $this;
		$I->amOnPage(AllDiscountsPage::$url);
		$I->checkForPhpNoticesOrWarnings();
		$I->waitForElement(AllDiscountsPage::$newButton, 30);
		$I->wait(0.5);
		$I->click(AllDiscountsPage::$newButton);
		$I->comment('I am redirected to the form');
		$I->waitForElement(AllDiscountsPage::$adminForm, 30);
		$I->wait(0.5);
		$I->selectOptionInChosenjs(AllDiscountsPage::$discountTypeLabel, $discountType);
		$I->waitForElementVisible(AllDiscountsPage::$productId, 60);
		$I->selectOptionInSelect2(AllDiscountsPage::$productLabel, $productName);
		$I->selectOptionInChosenjs(AllDiscountsPage::$salesTypeLabel, $salesType);
		$I->selectOptionInRadioField(AllDiscountsPage::$statusLabel, $status);
		$I->selectOptionInChosenjs(AllDiscountsPage::$currencyLabel, $curency);
		$I->waitForElement(AllDiscountsPage::$discountTotal, 30);
		$I->click(AllDiscountsPage::$discountTotal);
		$I->waitForElement(AllDiscountsPage::$totalId, 30);
		$I->wait(1);
		$I->fillField(AllDiscountsPage::$totalId, $total);
		$I->waitForElement(AllDiscountsPage::$saveCloseButton, 30);
		$I->click(AllDiscountsPage::$saveCloseButton);
		$I->wait(1);
		$I->waitForText(AllDiscountsPage::$productLabel, 30);
		}
	/**
	 * @param $product
	 * @param $newProduct
	 * @throws \Exception
	 */
	public function editDiscountPercent($newProduct, $percent)
	{
		$I = $this;
		$I->am('Administrator');
		$I->wantToTest('Discount edit in Frontend');
		$I->amGoingTo('Navigate to Discounts page in frontend');
		$I->amOnPage(AllDiscountsPage::$url);
		$I->wait(0.2);
		$I->checkAllResults();
		$I->click(AllDiscountsPage::$editButton);
		$I->comment('I am redirected to the form');
		$I->waitForElement(AllDiscountsPage::$adminForm, 30);
		$I->waitForElementVisible(AllDiscountsPage::$productId, 60);
		$I->selectOptionInSelect2(AllDiscountsPage::$productLabel, $newProduct);
		$I->waitForElement(AllDiscountsPage::$percentId, 30);
		$I->fillField(AllDiscountsPage::$percentId, $percent);
		$I->waitForElement(AllDiscountsPage::$saveButton, 30);
		$I->wait(0.5);
		$I->click(AllDiscountsPage::$saveCloseButton);
		$I->waitForText(AllDiscountsPage::$productLabel, 30);
	}

	/**
	 * @param $newProduct
	 * @param $total
	 * @throws \Exception
	 */
	public function editDiscountTotal($newProduct, $total)
	{
		$I = $this;
		$I->am('Administrator');
		$I->wantToTest('Discount edit in Frontend');
		$I->amGoingTo('Navigate to Discounts page in frontend');
		$I->amOnPage(AllDiscountsPage::$url);
		$I->wait(0.2);
		$I->checkAllResults();
		$I->click(AllDiscountsPage::$editButton);
		$I->wait(0.5);
		$I->comment('I am redirected to the form');
		$I->waitForElement(AllDiscountsPage::$adminForm, 30);
		$I->waitForElementVisible(AllDiscountsPage::$productId, 60);
		$I->selectOptionInSelect2(AllDiscountsPage::$productLabel, $newProduct);
		$I->waitForElement(AllDiscountsPage::$totalId, 30);
		$I->wait(0.5);
		$I->fillField(AllDiscountsPage::$totalId, $total);
		$I->waitForElement(AllDiscountsPage::$saveButton, 30);
		$I->wait(0.5);
		$I->click(AllDiscountsPage::$saveCloseButton);
		$I->waitForText(AllDiscountsPage::$productLabel, 30);
	}

	/**
	 * @param $productName
	 * @param $percent
	 * @param $discountType
	 * @param $salesType
	 * @param $status
	 * @param $currency
	 *
	 * @throws \Exception
	 * @since 2.8.0
	 */
	public function createAllDiscountTypeProductDiscountGroup($discountType, $productDiscountGroup, $salesType, $status, $discount, $currency, $discountApplication)
	{
		$I = $this;
		$I->amOnPage(AllDiscountsPage::$url);
		$I->waitForElementVisible(AllDiscountsPage::$newButton, 30);
		$I->click(AllDiscountsPage::$newButton);
		$I->comment('I am redirected to the form');
		$I->waitForElementVisible(AllDiscountsPage::$adminForm, 30);
		$I->selectOptionInChosenjs(AllDiscountsPage::$discountTypeLabel, $discountType);
		$I->selectOptionInChosenjs(AllDiscountsPage::$productDiscountGroupLabel, $productDiscountGroup);
		$I->selectOptionInChosenjs(AllDiscountsPage::$salesTypeLabel, $salesType);
		$I->selectOptionInRadioField(AllDiscountsPage::$statusLabel, $status);
		$I->selectOptionInChosenjs(AllDiscountsPage::$currencyLabel, $currency);
		switch ($discountApplication)
		{
			case 'Percent':
				$I->waitForElementVisible(AllDiscountsPage::$discountPercent, 30);
				$I->click(AllDiscountsPage::$discountPercent);
				$I->waitForElementVisible(AllDiscountsPage::$percentId, 30);
				$I->fillField(AllDiscountsPage::$percentId, $discount);
				break;
			case 'Total':
				$I->waitForElementVisible(AllDiscountsPage::$discountTotal, 30);
				$I->click(AllDiscountsPage::$discountTotal);
				$I->waitForElementVisible(AllDiscountsPage::$totalId, 30);
				$I->fillField(AllDiscountsPage::$totalId, $discount);
		}
		$I->waitForElementVisible(AllDiscountsPage::$saveCloseButton, 30);
		$I->click(AllDiscountsPage::$saveCloseButton);
		$I->waitForText(AllDiscountsPage::$messageSuccess, 30, AllDiscountsPage::$messageSuccessID);
	}

	/**
	 * @throws \Exception
	 */
	public function deleteDiscount ()
	{
		$I = $this;
		$I->amGoingTo('Navigate to Discounts page in frontend');
		$I->amOnPage(AllDiscountsPage::$url);
		$I->checkAllResults();
		$I->waitForElementVisible(AllDiscountsPage::$deleteButton, 30);
		$I->click(AllDiscountsPage::$deleteButton);
		$I->waitForElementVisible(AllDiscountsPage::$alertMessage, 30);
	}
}
