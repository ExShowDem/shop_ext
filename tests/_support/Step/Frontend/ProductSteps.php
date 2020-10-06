<?php
/**
 * @package  AcceptanceTester
 *
 * @since    1.4
 *
 * @link     http://codeception.com/docs/07-AdvancedUsage#StepObjects
 */

namespace Step\Frontend;
use Page\Frontend\ProductPage as ProductPage;

class ProductSteps extends  \Step\Acceptance\redshopb2b
{
	/**
	 * @param $name
	 * @param $sku
	 * @param $category
	 * @param $price
	 * @param $retailPrice
	 * @param $ownerCompany
	 * @param $function
	 * @param array $params
	 * @throws \Exception
	 */
	public function create($name, $sku, $category, $price, $retailPrice, $ownerCompany, $function, $params = [])
	{
		$I = $this;
		$I->amGoingTo('Navigate to Products page in Frontend');
		$I->amOnPage(ProductPage::$URLProducts);
		
		if ($ownerCompany != null)
		{
			if ($ownerCompany == 'Main Warehouse' || $ownerCompany == '(main) Main Company')
			{
				$I->comment('Just add main company or main warehoust for product');
			} else
			{
				$I->comment('See name of company');
				$ownerCompany = '- ' . $ownerCompany;
			}
		}
		$I->waitForElement(ProductPage::$newButton, 30);
		$I->click(ProductPage::$newButton);
		$I->reloadPage();
		$I->wait(2);
		$I->waitForElement(ProductPage::$productTag, 10);

		$I->comment('I am redirected to the form');
		$I->waitForElement(ProductPage::$adminForm, 30);
		$I->fillField(ProductPage::$nameID, $name);

		$I->comment('Wait for category lists to load');
		try {
			$I->waitForElement(ProductPage::$selectCategory, 10);
		} catch (\Exception $e) {
			$I->reloadPage();
			$I->waitForElement(ProductPage::$adminForm, 30);
			$I->fillField(ProductPage::$nameID, $name);
			$I->waitForElement(ProductPage::$selectCategory, 10);
		}
		$I->wait(1);
		if ($ownerCompany != null)
		{
			$I->waitForText(ProductPage::$ownerCompanyLabel, 60);
			$I->wait(0.5);
			$I->comment('Add Owner Company');
			$I->waitForElement(ProductPage::$ownerCompanyId, 30);
			$I->selectOptionInChosenXpath(ProductPage::$ownerCompanyJform, $ownerCompany);
		}
		$I->wait(0.5);
		if ($category != null)
		{
			$I->waitForText(ProductPage::$nameCategory, 60);
			$I->comment('Setup category for this product');
			$I->comment('Choice main Company for product');
			$I->wait(1);
			$I->waitForElement(ProductPage::$mainCategoryId, 30);
			$I->scrollTo(ProductPage::$mainCategoryId);
			$I->selectOptionInChosenXpath(ProductPage::$mainCategoryJform, $category);
			$I->selectOptionInChosenjs(ProductPage::$mainCategoryLabel, $category);
		}
		$I->wait(1);
		$I->fillField(ProductPage::$sku, $sku);
		$I->fillField(ProductPage::$price, $price);
		if ($retailPrice != null)
		{
			$I->fillField(ProductPage::$priceRetail,$retailPrice);
		}
		if (isset($params['As Service']))
		{
			$I->selectOptionInRadioField('As Service', $params['As Service']);
		}
		$I->waitForElementVisible(ProductPage::$saveButton, 30);
		$I->scrollTo(ProductPage::$saveButton);
		$I->wait(1);
		switch ($function)
		{
			case 'save':
				$I->click(ProductPage::$saveButton);
#				$I->waitForJS('return !!window.jQuery && window.jQuery.active == 0;', 30);
				$I->wait(1);
				try{
					$I->waitForText(ProductPage::$productSaveSuccess, 10, ProductPage::$messageSuccessID);
				}catch (\Exception $e)
				{
					$I->click(ProductPage::$saveButton);
					$I->waitForText(ProductPage::$productSaveSuccess, 30, ProductPage::$messageSuccessID);
				}
				$I->waitForElementVisible(ProductPage::$idCategory, 30);
				$I->waitForElementVisible(ProductPage::$saveCloseButton, 30);
				$I->wait(1);
				$I->click(ProductPage::$saveCloseButton);
				try{
					$I->waitForElementVisible(ProductPage::$searchProductID, 5);
				}catch (\Exception $e)
				{
					$I->click(ProductPage::$saveCloseButton);
				}
				$I->waitForElement(ProductPage::$searchProductID, 30);
				break;
			case 'save&close':
				$I->waitForElement(ProductPage::$saveCloseButton, 30);
				$I->click(ProductPage::$saveCloseButton);
				try{
					$I->waitForText(ProductPage::$productSaveSuccess, 10, ProductPage::$messageSuccessID);
				}catch (\Exception $e)
				{
					$I->click(ProductPage::$saveCloseButton);
					$I->waitForText(ProductPage::$productSaveSuccess, 30, ProductPage::$messageSuccessID);
				}
//				try
//                {
//					$I->waitForText(ProductPage::$productSaveSuccess, 10, ProductPage::$messageSuccessID);
//				}catch (\Exception $e)
//				{
//					$I->reloadPage();
//					$I->waitForElement(ProductPage::$newButton, 30);
//					$I->click(ProductPage::$newButton);
//					$I->reloadPage();
//					$I->wait(2);
//					$I->waitForElement(ProductPage::$productTag, 10);
//
//					$I->comment('I am redirected to the form');
//					$I->waitForElement(ProductPage::$adminForm, 30);
//					$I->fillField(ProductPage::$nameID, $name);
//
//					$I->comment('Wait for category lists to load');
//					$I->waitForElement(ProductPage::$selectCategory, 30);
//					$I->wait(0.5);
//					if ($ownerCompany != null)
//					{
//						$I->waitForText(ProductPage::$ownerCompanyLabel, 60);
//						$I->wait(0.5);
//						$I->wantTo('Add Owner Company');
//						$I->waitForElement(ProductPage::$ownerCompanyId, 30);
//						$I->selectOptionInChosenXpath(ProductPage::$ownerCompanyJform, $ownerCompany);
//					}
//					$I->wait(0.5);
//					if ($category != null)
//					{
//						$I->waitForText(ProductPage::$nameCategory, 60);
//						$I->wantTo('Setup category for this product');
//						$I->wantTo('Choice main Company for product');
//						$I->wait(1);
//						$I->waitForElement(ProductPage::$mainCategoryId, 30);
//						$I->scrollTo(ProductPage::$mainCategoryId);
//						$I->selectOptionInChosenXpath(ProductPage::$mainCategoryJform, $category);
//						$I->selectOptionInChosenjs(ProductPage::$mainCategoryLabel, $category);
//					}
//					$I->wait(1);
//					$I->fillField(ProductPage::$sku, $sku);
//					$I->fillField(ProductPage::$price, $price);
//					if ($retailPrice != null)
//					{
//						$I->fillField(ProductPage::$priceRetail,$retailPrice);
//					}
//					if (isset($params['As Service']))
//					{
//						$I->selectOptionInRadioField('As Service', $params['As Service']);
//					}
//					$I->waitForElementVisible(ProductPage::$saveButton, 30);
//					$I->scrollTo(ProductPage::$saveButton);
//					$I->wait(5);
//					$I->click(ProductPage::$saveCloseButton);
//					$I->wait(2);
//					$I->waitForText(ProductPage::$productSaveSuccess, 10, ProductPage::$messageSuccessID);
//				}
//				$I->waitForElement(ProductPage::$newButton, 30);
				$I->waitForElementVisible(ProductPage::$searchProductID, 30);
				$I->waitForElement(ProductPage::$searchProductID, 30);
				$I->waitForElement(['link' => $name], 30);
				break;
		}
	}

	/**
	 * @param $name
	 * @param $taxGroup
	 * @throws \Exception
	 */
	public function editProductWithTaxGroup($name, $taxGroup)
	{
		$I = $this;
		$I->amGoingTo('Navigate to Products page in Frontend');
		$I->amOnPage(ProductPage::$URLProducts);
		$I->searchForItemInFrontend($name, ['search field locator id' => ProductPage::$searchProduct]);
		$I->waitForElement(['link' => $name], 30);
		$I->click(['link' => $name]);
		$I->comment('I am redirected to the form');
		$I->waitForElement(ProductPage::$adminForm, 30);
		$I->comment('Wait for category lists to load');
		$I->waitForElementVisible(ProductPage::$idCategory, 30);
		$I->checkForPhpNoticesOrWarnings();
		if($taxGroup != null)
		{
			$I->waitForText(ProductPage::$vatTaxLabel, 30);
			$I->waitForElementVisible(ProductPage::$vatTaxGroupId, 30);
			$I->scrollTo(ProductPage::$vatTaxGroupId);
			$I->selectOptionInChosenXpath(ProductPage::$vatTaxGroupJform, $taxGroup);
			$I->selectOptionInChosenjs(ProductPage::$vatTaxLabel, $taxGroup);
			$I->waitForText($taxGroup, 30);
		}
		$I->waitForElementVisible(ProductPage::$saveButton, 30);
		$I->scrollTo(ProductPage::$saveButton);
		$I->waitForElement(ProductPage::$saveCloseButton, 30);
		$I->wait(1);
		$I->click(ProductPage::$saveCloseButton);
		$I->waitForElement(ProductPage::$iconClear, 30);
		$I->click(ProductPage::$iconClear);
		$I->waitForElement(ProductPage::$newButton, 30);
	}

	/**
	 * @param $name
	 * @param $nameEdit
	 * @param $priceEdit
	 * @throws \Exception
	 */
	public function edit($name, $nameEdit, $priceEdit)
	{
		$I = $this;
		$I->amGoingTo('Navigate to Products page in Frontend');
		$I->amOnPage(ProductPage::$URLProducts);
		$I->wait(0.5);
		$I->searchForItemInFrontend($name, ['search field locator id' => ProductPage::$searchProduct]);
		$I->waitForElement(['link' => $name], 30);
		$I->click(['link' => $name]);
		$I->wait(1);
		$I->comment('I am redirected to the form');
		$I->waitForElement(ProductPage::$adminForm, 30);
		$I->comment('Wait for category lists to load');
		$I->waitForElementVisible(ProductPage::$idCategory, 30);
		$I->checkForPhpNoticesOrWarnings();

		$I->fillField(ProductPage::$nameID, $nameEdit);
		$I->wait(0.5);
		$I->fillField(ProductPage::$price, $priceEdit);
		$I->waitForElementVisible(ProductPage::$price, 30);
		$I->wait(1);
		$I->click(ProductPage::$saveCloseButton);
		$I->comment('Wait for category lists to load');
		$I->searchForItemInFrontend($nameEdit, ['search field locator id' => ProductPage::$searchProduct]);
		$I->waitForText($nameEdit, 30);
	}

	/**
	 * @param $name
	 * @param $nameAttribute
	 * @param $attributeType
	 * @throws \Exception
	 */
	public function createAttribute($name,$nameAttribute, $attributeType)
	{
		$I = $this;
		$I->amGoingTo('Navigate to Products page in Frontend');
		$I->amOnPage(ProductPage::$URLProducts);
		$I->click(['link' => $name]);
		$I->wait(3);
		$I->comment('I am redirected to the form');
		$I->waitForElement(ProductPage::$adminForm, 30);
		$I->comment('Wait for category lists to load');
		try
		{
			$I->waitForElementVisible(ProductPage::$idCategory, 5);
		}catch (\Exception $e)
		{
			$I->reloadPage();
		}
		$I->waitForElementVisible(ProductPage::$idCategory, 60);
		$I->checkForPhpNoticesOrWarnings();
		$I->waitForElement(ProductPage::$attributeType,60);
		$I->click(ProductPage::$attributeType);
		$I->waitForElement(ProductPage::$newAttribute, 60);
		$I->click(ProductPage::$newAttribute);
		$I->waitForElement(ProductPage::$attributeTab,60);
		$I->fillField(ProductPage::$nameAttribute,$nameAttribute);

		if($attributeType != null)
		{

			$I->selectOptionInChosenjs(ProductPage::$attributeTypeLabel,$attributeType);
		}
		$I->wait(1);
		$I->click(ProductPage::$saveCloseButton);
		try
		{
			$I->waitForElement(ProductPage::$messageSuccessID,10);
		}catch (\Exception $e)
		{
			$I->click(ProductPage::$saveCloseButton);
			$I->waitForElement(ProductPage::$messageSuccessID,10);
		}
		$I->wait(1);
		$I->click(ProductPage::$saveCloseButton);
	}

	/**
	 * @param $name
	 * @throws \Exception
	 */
	public function deleteAttribute($name)
	{
		$I = $this;
		$I->amGoingTo('Navigate to Products page in Frontend');
		$I->amOnPage(ProductPage::$URLProducts);
		$I->click(['link' => $name]);
		$I->comment('I am redirected to the form');
		$I->waitForElement(ProductPage::$adminForm, 30);
		$I->comment('Wait for category lists to load');
		$I->waitForElementVisible(ProductPage::$idCategory, 30);
		$I->checkForPhpNoticesOrWarnings();
		$I->seeInField(ProductPage::$nameID, $name);
		$I->waitForElement(ProductPage::$attributeType,30);
		$I->click(ProductPage::$attributeType);
		$I->waitForElementVisible(ProductPage::$buttonDeleteAttribute, 30);
		$I->scrollTo(ProductPage::$buttonDeleteAttribute);
		$I->click(ProductPage::$buttonDeleteAttribute);
	}

	/**
	 * @param $productName
	 * @param $xpathAttribute
	 * @param $sku
	 * @param $value
	 * @param $defaultSelect
	 * @param $status
	 * @throws \Exception
	 */
	public function createAttributeValue($productName,$xpathAttribute,$sku,$value, $defaultSelect,$status)
	{
		$I = $this;
		$I->amGoingTo('Navigate to Products page in Frontend');
		$I->amOnPage(ProductPage::$URLProducts);
		$I->searchForItemInFrontend($productName, ['search field locator id' => ProductPage::$searchProduct]);
		$I->wait(0.5);
		$I->checkAllResults();
		$I->click(ProductPage::$editButton);
		$I->waitForElement(ProductPage::$attributeType, 30);
		$I->wait(0.2);
		$I->click(ProductPage::$attributeType);
		$useproductPage = new ProductPage();
		$I->waitForElement($useproductPage->getValueAttribute($xpathAttribute),30);
		$I->click($useproductPage->getValueAttribute($xpathAttribute));
		try
		{
			$I->waitForElementVisible(ProductPage::$sku, 5);
		}catch (\Exception $e)
		{
			$I->click($useproductPage->getValueAttribute($xpathAttribute));
			$I->waitForElement(ProductPage::$sku, 30);
		}
		$I->wait(0.2);
		$I->fillField(ProductPage::$sku,$sku);
		$I->fillField(ProductPage::$valueAttribute,$value);
		$I->comment('Setup Default Selected ');
		$I->wait(0.2);
		$I->selectOptionInRadioField(ProductPage::$labelDefaultSelect, $defaultSelect);
		$I->comment('Setup Status Selected ');
		$I->selectOptionInRadioField(ProductPage::$labelStatus, $status);
		$I->wait(0.2);
		$I->waitForElement(ProductPage::$saveCloseButton, 60);
		$I->click(ProductPage::$saveCloseButton);
		$I->waitForText(ProductPage::$attributeValueValueSubmitted,60);
		$I->waitForElement(ProductPage::$messageSuccessID, 60);
		$I->waitForElement(ProductPage::$saveCloseButton, 60);
		$I->wait(0.2);
		$I->waitForElementVisible(ProductPage::$saveCloseButton, 60);
		$I->click(ProductPage::$saveCloseButton);
	}

	/**
	 * @param $name
	 * @param $attributeFirst
	 * @param $attributeSecond
	 * @throws \Exception
	 */
	public function generateCombinations($name, $attributeFirst, $attributeSecond)
	{
		$I = $this;
		$I->amGoingTo('Navigate to Products page in Frontend');
		$I->amOnPage(ProductPage::$URLProducts);
		$I->click(['link' => $name]);
		$I->wait(0.3);
		$usedPage= new ProductPage();
		$I->comment('I am redirected to the form');
		$I->waitForElement(ProductPage::$adminForm, 30);
		$I->comment('Wait for category lists to load');
		$I->waitForElementVisible(ProductPage::$idCategory, 30);
		$I->checkForPhpNoticesOrWarnings();
		$I->click(ProductPage::$attributeType);
		$I->waitForElement($usedPage->getNameAttribute($attributeFirst), 30);
		$I->waitForElement($usedPage->getNameAttribute($attributeSecond), 30);
		$I->wait(1);
		$I->waitForElement(ProductPage::$generateItems, 30);
		$I->click(ProductPage::$generateItems);

		try
		{
			$I->waitForText(ProductPage::$generateItemSuccess, 30,  ProductPage::$messageSuccessID);
		}catch (\Exception $e)
		{
			$I->click(ProductPage::$generateItems);
			$I->waitForText(ProductPage::$generateItemSuccess, 30,  ProductPage::$messageSuccessID);
		}

		$I->wait(1);
		$I->waitForElement(ProductPage::$combinationsPrices, 30);
		$I->click(ProductPage::$combinationsPrices);
		$I->wait(1);
		$I->waitForElement($usedPage->getValueCombinations(1), 30);
		$I->waitForElement($usedPage->getValueCombinations(4), 30);
		$I->scrollTo($usedPage->getValueCombinations(4));
		$I->waitForElement($usedPage->getValueCombinations(1), 30);
		$I->waitForElement($usedPage->getValueCombinations(2), 30);
		$I->waitForElement($usedPage->getValueCombinations(3), 30);
		$I->waitForElement($usedPage->getValueCombinations(4), 30);
		$I->waitForElementVisible(ProductPage::$saveCloseButton, 30);
		$I->waitForElement(ProductPage::$messageSuccessID, 30);
		$I->wait(1);
		$I->click(ProductPage::$saveCloseButton);
	}

	/**
	 * @param $name
	 * @param $price1
	 * @param $price2
	 * @param $price3
	 * @param $price4
	 * @throws \Exception
	 */
	public function editPriceAttribute($name,$price1,$price2,$price3,$price4)
	{
		$I = $this;
		$I->amGoingTo('Navigate to Products page in Frontend');
		$I->amOnPage(ProductPage::$URLProducts);
		$I->click(['link' => $name]);
		$I->wait(1);
		$usedPage = new ProductPage;
		$I->comment('I am redirected to the form');
		$I->waitForElement(ProductPage::$adminForm, 30);
		$I->wait(1);
		$I->comment('Wait for category lists to load');
		$I->waitForElementVisible(ProductPage::$idCategory, 30);
		$I->checkForPhpNoticesOrWarnings();
		$I->waitForElement(ProductPage::$combinationsPrices, 30);
		$I->click(ProductPage::$combinationsPrices);
		$I->waitForElementVisible($usedPage->getValueCombinations(1), 30);
		$I->fillField($usedPage->getValueCombinations(1),$price1);
		$I->fillField($usedPage->getValueCombinations(2),$price2);
		$I->scrollTo($usedPage->getValueCombinations(3));
		$I->fillField($usedPage->getValueCombinations(3),$price3);
		$I->fillField($usedPage->getValueCombinations(4),$price4);
		$I->scrollUp();
		$I->wait(1);
		$I->waitForElementVisible(ProductPage::$saveCloseButton, 30);
		$I->click(ProductPage::$saveCloseButton);
		try
		{
			$I->waitForElement(ProductPage::$messageSuccessID, 30);
		}catch (\Exception $e)
		{
			$I->click(ProductPage::$saveCloseButton);
			$I->waitForElement(ProductPage::$messageSuccessID, 30);
		}
	}

	/**
	 * @param $employeeWithLogin
	 * @param $category
	 * @param $product
	 * @param $attributeFirst
	 * @param $attributeSecond
	 * @param $valueFirst
	 * @param $valueSecond
	 * @param $price1
	 * @param $bootstrap
	 * @throws \Exception
	 */
	public function checkPriceEmployeeWithLogin($employeeWithLogin,$category, $product,$attributeFirst, $attributeSecond,$valueFirst,$valueSecond,$price1, $bootstrap)
	{
		$I = $this;
		$I->amGoingTo('Navigate to Products page in Frontend');
		$I->doFrontEndLogin($employeeWithLogin, $employeeWithLogin);
		$I->amOnPage(ProductPage::$URLShop);
		$userPage = new ProductPage();
		$I->waitForElement(['link' => $category], 30);
		$I->click(['link' => $category]);
		$I->waitForText($category, 30, ProductPage::$categoryClass);
		$I->waitForElement(['link' => $product], 30);
		$I->click(['link' => $product]);
		$I->wait(0.3);
		$I->waitForText($product, 30, ProductPage::$productNameH1);

		if ($bootstrap == '2')
		{
			$I->waitForElement($userPage->attribute(1),30);
			$I->fillField($userPage->attribute(1),1);
			$I->waitForElement($userPage->attribute(2),30);
			$I->fillField($userPage->attribute(2),1);
		}else
		{
			$I->comment('Choice option with bootstrap3');
			$I->selectOptionInChosenjs($attributeFirst,$valueFirst);
			$I->selectOptionInChosenjs($attributeFirst,$valueFirst);
			$I->comment('Wait for load value');
			$I->wait(1);
			$I->selectOptionInChosenjs($attributeSecond,$valueSecond);
			$I->selectOptionInChosenjs($attributeSecond,$valueSecond);
			$I->comment('Wait for load price');
			$I->wait(1);
			$I->waitForElement(ProductPage::$productPrice,30);
		}

		$price1 =  $price1 .',00 €' ;
		$I->comment('I get price');
		$I->comment($price1);
		$I->waitForText($price1, 30);
	}
	
	/**
	 * @param $name
	 * @throws \Exception
	 */
	public function deleteProduct($name)
	{
		$client = $this;
		$client->amGoingTo('Navigate to Products page in Frontend');
		$client->amOnPage(ProductPage::$URLProducts);
		$client->searchForItemInFrontend($name, ['search field locator id' => ProductPage::$searchProduct]);
		$client->checkAllResults();
		$client->click(ProductPage::$deleteButton);
		$client->dontSeeLink('link', $name);
	}

	/**
	 * @param $product
	 *
	 * @throws \Exception
	 * @since 2.8.0
	 */
	public function editProductWithStockroom($product, $stockName)
	{
		$I = $this;
		$I->amGoingTo('Navigate to Products page in Frontend');
		$I->amOnPage(ProductPage::$URLProducts);
		$I->searchForItemInFrontend($product['name'], ['search field locator id' => ProductPage::$searchProduct]);
		$I->checkAllResults();
		$I->click(ProductPage::$editButton);
		$I->waitForElementVisible(ProductPage::$stock, 30);
		$I->wait(0.2);
		$I->click(ProductPage::$stock);
		$userPage = new ProductPage();
		$I->waitForElementVisible($userPage->getStock($stockName), 30);
		$I->click($userPage->getStock($stockName));
		$I->waitForElementVisible(ProductPage::$stockNumber, 60);
		try
		{
			$I->wait(0.5);
			$I->fillField(ProductPage::$stockNumber, $product['stockNumber']);
			$I->seeInField(ProductPage::$stockNumber, $product['stockNumber']);
		} catch (\Exception $e)
		{
			$I->wait(0.5);
			$I->click(ProductPage::$stockNumber);
			$I->fillField(ProductPage::$stockNumber, $product['stockNumber']);
			$I->seeInField(ProductPage::$stockNumber, $product['stockNumber']);
		}
		$I->waitForElementVisible(ProductPage::$saveCloseButton, 30);
		$I->click(ProductPage::$saveCloseButton);
		try
		{
			$I->waitForElement(ProductPage::$messageSuccessID, 30);
		}catch (\Exception $e)
		{
			$I->click(ProductPage::$saveCloseButton);
			$I->waitForElement(ProductPage::$messageSuccessID, 30);
		}
	}

	/**
	 * @param $product
	 *
	 * @throws \Exception
	 * @since 2.8.0
	 */
	public function checkNumberOfStockroomAfterCheckout($product, $stockName)
	{
		$I = $this;
		$I->amGoingTo('Navigate to Products page in Frontend');
		$I->amOnPage(ProductPage::$URLProducts);
		$I->searchForItemInFrontend($product['name'], ['search field locator id' => ProductPage::$searchProduct]);
		$I->checkAllResults();
		$I->click(ProductPage::$editButton);
		$I->waitForElementVisible(ProductPage::$stock, 60);
		$I->click(ProductPage::$stock);
		$I->wait(1);
		$userPage = new ProductPage();
		$I->waitForElementVisible($userPage->getStock($stockName), 60);
		$I->wait(0.5);
		$I->click($userPage->getStock($stockName));
		$I->wait(1);
		$I->waitForElementVisible(ProductPage::$stockNumber, 60);
		$numberOfStockroom = (int) $product['stockNumber'] - $product['quantity'];
		$I->seeInField(ProductPage::$stockNumber, $numberOfStockroom);
		$I->waitForElementVisible(ProductPage::$closeButton, 30);
		$I->click(ProductPage::$closeButton);
	}
}
