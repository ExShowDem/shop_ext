<?php
namespace Step\Acceptance;
use Page\Frontend\CategoryPage as CategoryPage;
use Page\Frontend\CheckoutPage as CheckoutPage;
use Page\Frontend\DepartmentPage as DepartmentPage;
use Page\Frontend\DebtorGroupsPage as DebtorGroupsPage;
use Page\Frontend\ProductPage as ProductPage;
use Page\Frontend\ShippingMethodPage as ShippingMethodPage;
use Page\Frontend\CompanyPage as CompanyPage;
use Page\Frontend\UserPage as UserPage;
use Page\Frontend\Redshopb2bPage as Redshopb2bPage;
class redshopb2b extends \AcceptanceTester
{
	/**
	 * @param      $name
	 * @param null $options
	 * @throws \Exception
	 */
	public function searchForItemInFrontend($name, $options = null)
	{
		$I = $this;

		if (!isset($options['search field locator id']))
		{
			// If no search field locator provided it will use the common Joomla administrator search field id
			$options['search field locator id'] = "filter_search";
		}

		if (!isset($options['search submit locator']))
		{
			// If no search submit locator provided it will use the common Joomla administrator search button xpath
			$options['search submit locator'] = ['xpath' => "//button[@type='submit' and @data-original-title='Search']"];
		}

		if (!isset($options['clear search locator']))
		{
			// If no clear search locator provided it will use the common Joomla administrator clear search button xpath
			$options['clear search locator'] = ['xpath' => "//button[@type='submit' and @data-original-title='Search']"];
		}

		if ($name)
		{
			$I->comment("Searching for $name");
			$I->waitForElementVisible(['id' => $options['search field locator id']], 30);
			$I->waitForElementVisible(Redshopb2bPage::$iconClear, 30);
			$I->click(Redshopb2bPage::$iconClear);
			$I->waitForElement(['id' => $options['search field locator id']], 30);
			$I->fillField(['id' => $options['search field locator id']], $name);
			try
			{
				$I->click($options['search submit locator']);
				$I->waitForElementVisible(['xpath' => "//input[@id='" . $options['search field locator id'] . "' and @value='" . $name . "']"], 60);
			} catch (\Exception $exception)
			{
				$I->waitForElementVisible($options['search submit locator'], 30);
				$I->click($options['search submit locator']);
				$I->waitForElementVisible(['xpath' => "//input[@id='" . $options['search field locator id'] . "' and @value='" . $name . "']"], 60);
			}
		}
		else
		{
			$I->comment('clearing search filter');
			$I->click($options['clear search locator']);
		}
	}

	/**
	 * @param $name
	 * @param null $options
	 * @throws \Exception
	 * @since 2.5.0
	 */
	public function searchForCompany($name, $options = null)
	{
		$I = $this;

		if (!isset($options['search field locator id']))
		{
			// If no search field locator provided it will use the common Joomla administrator search field id
			$options['search field locator id'] = "filter_search";
		}

		if (!isset($options['search submit locator']))
		{
			// If no search submit locator provided it will use the common Joomla administrator search button xpath
			$options['search submit locator'] = ['xpath' => "//button[@type='submit' and @data-original-title='Search']"];
		}

		if (!isset($options['clear search locator']))
		{
			// If no clear search locator provided it will use the common Joomla administrator clear search button xpath
			$options['clear search locator'] = ['xpath' => "//button[@type='submit' and @data-original-title='Search']"];
		}

		if ($name)
		{
			$I->comment("Searching for $name");
			$I->waitForElementVisible(['id' => $options['search field locator id']], 30);
			$I->waitForElement(['id' => $options['search field locator id']], 30);
			$I->fillField(['id' => $options['search field locator id']], $name);
			$I->click($options['search submit locator']);
			$I->waitForElement(['xpath' => "//input[@id='" . $options['search field locator id'] . "' and @value='" . $name . "']"], 60);
		}
		else
		{
			$I->comment('clearing search filter');
			$I->click($options['clear search locator']);
		}
	}
	/**
	 * @param $name
	 * @param null $company
	 * @param array $params
	 * @throws \Exception
	 */
	public function createRedshopbCategory($name, $company = null, $params = [])
	{
		$I = $this;
		$I->comment('I navigate to Categories page in frontend');
		$I->amOnPage(CategoryPage::$URLCategories);
		$I->waitForElement(CategoryPage::$newButton, 30);
		$I->click(CategoryPage::$newButton);
		$I->comment('I am redirected to the form category detail');
		$I->waitForElementVisible(CategoryPage::$nameID, 30);
		$I->fillField(CategoryPage::$nameID, $name);
		$I->wait(0.5);
		if (!is_null($company))
		{
			$I->selectOptionInChosenjs('Category company', $company);
		}

		if (isset($params['Description']))
		{
			$I->click(CategoryPage::$editor);
			$I->waitForElementVisible(CategoryPage::$description, 30);
			$I->fillField(CategoryPage::$description, $params['Description']);
			$I->click(CategoryPage::$editor);
		}

		$I->waitForElementVisible(CategoryPage::$saveButton, 30);
		$I->click(CategoryPage::$saveButton);
		$I->waitForText(CategoryPage::$saveCategorySuccess, 30, CategoryPage::$messageSuccessID);
		$I->click(CategoryPage::$closeButton);
		$I->searchForItemInFrontend($name, ['search field locator id' => CategoryPage::$searchCategory]);
		$usepage = new CategoryPage();
		$I->waitForElement($usepage->getXpathItem($name), 30);
	}

	/**
	 * @param $name
	 *
	 * @throws \Exception
	 */
	public function deleteRedshopbCategory($name)
	{
		$I = $this;

		$I->comment('I navigate to Categories page in frontend');
		$I->deleteObject($name, CategoryPage::$URLCategories, CategoryPage::$searchCategory);
	}

	/**
	 * @param $name
	 * @throws \Exception
	 */
	public function createRedshopbDepartment($name, $company)
	{
		$I = $this;
		$I->amGoingTo('Navigate to Departments page in frontend');
		$I->amOnPage(DepartmentPage::$URLDepartments);

		$I->waitForElement(DepartmentPage::$newButton, 30);
		$I->click(DepartmentPage::$newButton);

		$I->comment('I am redirected to the form');
		$I->waitForElement(DepartmentPage::$adminForm, 30);
		$I->checkForPhpNoticesOrWarnings();
		$I->fillField(DepartmentPage::$nameID, $name);
		$I->selectOptionInChosenjs('Company', $company);

		$I->click(DepartmentPage::$saveButton);
		$I->waitForElement(DepartmentPage::$messageSuccessID);
		$I->waitForText(DepartmentPage::$saveDepartmentSuccess, 30, DepartmentPage::$messageSuccessID);
		$I->click(DepartmentPage::$saveCloseButton);
		$I->seeElement(['link' => $name]);
	}

	/**
	 * @param $name
	 * @throws \Exception
	 */
	public function deleteRedshopbDepartment($name)
	{
		$I = $this;
		$I->comment('I Navigate to Departments page in frontend');
		$I->amOnPage(DepartmentPage::$URLDepartments);
		$I->searchForItemInFrontend($name, ['search field locator id' => DepartmentPage::$searchDepartment]);
		$I->waitForElement(DepartmentPage::$deleteButton, 30);
		$I->checkAllResults();
		$I->click(DepartmentPage::$deleteButton);
		$I->waitForElementVisible(DepartmentPage::$departmentModal);
		$I->wait(1);
		$I->click(DepartmentPage::$deleteDepartment);
		$I->waitForElementNotVisible(DepartmentPage::$departmentModal);

		$I->comment('I am redirected to the list');
		$I->waitForElement(['id' => DepartmentPage::$searchDepartment], 30);
		$I->dontSeeElement(['link' => $name]);
	}

	/**
	 * @param $name
	 * @param $code
	 * @param null $ownerCompany
	 * @param $companies
	 * @throws \Exception
	 */
	public function createRedshopbDebtorGroup($name, $code, $ownerCompany = null, $companies)
	{
		$I = $this;

		$I->amGoingTo('Navigate to Debtor page in frontend');
		$I->amOnPage(DebtorGroupsPage::$URLDebtorGroups);

		$I->waitForElement(DebtorGroupsPage::$newButton, 30);
		$I->click(DebtorGroupsPage::$newButton);

		$I->comment('I am redirected to the form');
		$I->waitForElement(DebtorGroupsPage::$adminForm, 30);
		$I->checkForPhpNoticesOrWarnings();

		$I->fillField(DebtorGroupsPage::$nameID, $name);
		$I->fillField(DebtorGroupsPage::$codeId, $code);

		if (!is_null($ownerCompany))
		{
			$I->selectOptionInChosenjs('Owner Company', $ownerCompany);
		}

		$I->waitForElement(DebtorGroupsPage::$customerInput, 30);
		$I->wait(1);
		$I->waitForElementVisible(DebtorGroupsPage::$customerInput, 30);
		$I->click(DebtorGroupsPage::$customerInput);
		$I->fillField(DebtorGroupsPage::$customerInput, $companies);
		$I->pressKey(DebtorGroupsPage::$customerInput, \Facebook\WebDriver\WebDriverKeys::ENTER);
		$I->waitForElementVisible(DebtorGroupsPage::$saveCloseButton, 30);
		$I->click(DebtorGroupsPage::$saveCloseButton);
		$I->waitForElementVisible(DebtorGroupsPage::$messageSuccessID, 60);
		$I->waitForText(DebtorGroupsPage::$debtorGroupSuccess, 30, DebtorGroupsPage::$messageSuccessID);
		$I->waitForElementVisible(['link' => $name], 60);
		$I->seeElement(['link' => $name]);
	}

	/**
	 * @param $name
	 * @throws \Exception
	 */
	public function deleteRedshopbDebtorGroup($name)
	{
		$I = $this;
		$I->comment('I Navigate to Departments page in frontend');
		$I->deleteObject($name, DebtorGroupsPage::$URLDebtorGroups, DebtorGroupsPage::$searchPriceDebtorGroup);
	}

	/**
	 * @param string $shippingPlugin
	 * @param $debtorGroup
	 * @param $title
	 * @throws \Exception
	 */
	public function createRedshopbShippingMethod($shippingPlugin = 'Default shipping', $debtorGroup, $title)
	{
		$I = $this;
		$I->amGoingTo('Navigate to Debtor page in frontend');
		$I->amOnPage(DebtorGroupsPage::$URLDebtorGroups);
		$I->waitForElement(DebtorGroupsPage::$newButton, 30);
		$I->click(['link' => $debtorGroup]);
		$I->comment('I am redirected to the Debtor Edit Form');
		$I->waitForElement(DebtorGroupsPage::$adminForm, 60);
		$I->click(DebtorGroupsPage::$shippingMethod);
		$I->waitForElement(DebtorGroupsPage::$shippingMethodForm, 60);
		$I->checkForPhpNoticesOrWarnings();
		$I->click(DebtorGroupsPage::$newShipping);
		$I->comment('I am redirected to the shipping form');
		$I->waitForElement(DebtorGroupsPage::$adminForm, 60);
		$I->selectOptionInChosenjs('Shipping plugin', $shippingPlugin);
		$I->waitForElementVisible(DebtorGroupsPage::$shippingTitle, 30);
		$I->wait(1);
		$I->fillField(DebtorGroupsPage::$shippingTitle, "");
		$I->fillField(DebtorGroupsPage::$shippingTitle, $title);
		$I->wait(1);
		$I->click(DebtorGroupsPage::$saveSuccessButton);
		$I->waitForText(DebtorGroupsPage::$saveSuccess, 30, DebtorGroupsPage::$messageSuccessID);
		$I->wait(1);
		$I->click(DebtorGroupsPage::$closeDangerButton);
		$I->waitForElement(DebtorGroupsPage::$shippingMethodForm, 60);
		$I->waitForElement(['link' => $title], 30);
	}

	/**
	 * Create a redSHOPB2B Shipping Rate
	 *
	 * @param   string  $shippingTitle     The associated Shipping Method
	 * @param   string  $shippingRateName  The Title for the shipping rate
	 * @param   array   $products          Array of products that will supprort the shipping rate
	 * @throws \Exception
	 */
	public function createRedshopbShippingRate($shippingMethod, $shippingRateName, $products, $params = array())
	{
		$I = $this;

		$I->amGoingTo('Navigate to Shipping Rates page in frontend');
		$I->amOnPage(ShippingMethodPage::$URLShippingRate);

		$I->waitForElement(ShippingMethodPage::$newSuccessButton, 30);
		$I->click(ShippingMethodPage::$newSuccessButton);
		$I->waitForElement(ShippingMethodPage::$adminForm, 30);

		$I->comment('I am redirected to the Shipping Rate Edit Form');
		$I->selectOptionInChosenjs('Shipping method', $shippingMethod);
		$I->fillField(ShippingMethodPage::$nameID, $shippingRateName);

		foreach ($products as $product)
		{
			$I->selectOptionsInSelect2('On product', [$product]);
		}

		if (isset($params['Price']))
		{
			$I->fillField(ShippingMethodPage::$priceShipping, $params['Price']);
		}
		$I->waitForElementVisible(ShippingMethodPage::$saveSuccessButton, 30);
		$I->click(ShippingMethodPage::$saveSuccessButton);
		try
		{
			$I->waitForText(ShippingMethodPage::$saveSuccess, 10, ShippingMethodPage::$messageSuccessID);
		}catch (\Exception $exception)
		{
			$I->waitForElementVisible(ShippingMethodPage::$saveSuccessButton, 30);
			$I->click(ShippingMethodPage::$saveSuccessButton);
			$I->waitForText(ShippingMethodPage::$saveSuccess, 10, ShippingMethodPage::$messageSuccessID);
		}
		$I->waitForElementVisible(ShippingMethodPage::$closeDangerButton, 30);
		$I->click(ShippingMethodPage::$closeDangerButton);
		$I->waitForElement(ShippingMethodPage::$newSuccessButton, 30);
		$I->seeElement(['link' => $shippingRateName]);
	}

	/**
	 * @param $name
	 * @throws \Exception
	 */
	public function deleteRedshopbShippingRate($name)
	{
		$I = $this;

		$I->comment('I navigate to Shipping_rates page in frontend');
		$I->deleteObject($name, ShippingMethodPage::$URLShippingRate, ShippingMethodPage::$searchShippingRate);
	}

	/**
	 * @param $debtorGroup
	 * @param $paymentMethod
	 * @param $account
	 * @param $title
	 * @throws \Exception
	 */
	public function createRedshopbPaymentMethod($debtorGroup, $paymentMethod, $account, $title)
	{
		$I = $this;

		$I->amGoingTo('Navigate to Debtor page in frontend');
		$I->amOnPage(DebtorGroupsPage::$URLDebtorGroups);

		$I->waitForElement(['link' => $debtorGroup], 60);
		$I->click(['link' => $debtorGroup]);

		$I->comment('I am redirected to the Debtor Edit Form');
		$I->waitForElement(DebtorGroupsPage::$adminForm, 60);
		$I->click(DebtorGroupsPage::$paymentMethod);
		$I->waitForElement(DebtorGroupsPage::$paymentConfiguration, 60);
		$I->checkForPhpNoticesOrWarnings();
		$I->click(DebtorGroupsPage::$newPaymentButton);

		$I->comment('I am redirected to the Payment form');
		$I->waitForElement(DebtorGroupsPage::$adminForm, 30);
		$I->selectOptionInChosenjs(DebtorGroupsPage::$paymentOption, $paymentMethod);

		if ($paymentMethod == 'Paypal')
		{
			$I->waitForElementVisible(DebtorGroupsPage::$paymentPlugin);
			$I->fillField(DebtorGroupsPage::$paymentAccount, $account);
			$I->fillField(DebtorGroupsPage::$paymentSandboxAccount, $account);
			$I->selectOptionInRadioField(DebtorGroupsPage::$usedSandbox, 'Yes');
		}

		$I->click(['link' => "Extra"]);
		$I->fillField(DebtorGroupsPage::$title, $title);

		$I->click(DebtorGroupsPage::$saveSuccessButton);
		$I->waitForText(DebtorGroupsPage::$saveSuccess,30, DebtorGroupsPage::$messageSuccessID);
		$I->click(DebtorGroupsPage::$closeDangerButton);
		$I->waitForElement(DebtorGroupsPage::$shippingMethodForm, 30);
		$I->seeElement(['link' => $title]);
	}

	/**
	 * @param $name
	 * @param $category
	 * @param $sku
	 * @param null $ownerCompany
	 * @param $price
	 * @param array $params
	 * @throws \Exception
	 */
	public function createRedshopbProduct($name, $category, $sku, $ownerCompany = null, $price, $params = [])
	{
		$I = $this;

		$I->amGoingTo('Navigate to Products page in frontend');
		$I->amOnPage(ProductPage::$URLProducts);

		$I->waitForElement(ProductPage::$newButton, 30);
		$I->click(ProductPage::$newButton);

		$I->waitForElementVisible(ProductPage::$productTag, 10);

		$I->comment('I am redirected to the form');
		$I->waitForElement(ProductPage::$adminForm, 30);
		$I->fillField(ProductPage::$nameID, $name);

		$I->comment('Wait for category lists to load');
		$I->waitForElement(ProductPage::$selectCategory);

		// @todo: uncomment following line once RSBTB-3220 gets solved
		$I->checkForPhpNoticesOrWarnings();

		$I->fillField(ProductPage::$sku, $sku);
		$I->fillField(ProductPage::$priceRetail, $price);
		$I->fillField(ProductPage::$price, $price);

		if (!is_null($ownerCompany))
		{
			$I->selectOptionInChosenjs('Owner Company', $ownerCompany);
			$I->waitForElementVisible(ProductPage::$idCategory, 30);
			$I->selectOptionInChosenjs(ProductPage::$nameCategory, $category);
		}

		if (isset($params['As Service']))
		{
			$I->selectOptionInRadioField('As Service', $params['As Service']);
		}

		$I->waitForElementVisible(ProductPage::$saveButton, 30);
		$I->click(ProductPage::$saveButton);
		$I->waitForText(ProductPage::$productSaveSuccess, 30, ProductPage::$messageSuccessID);
		$I->waitForElementVisible(ProductPage::$idCategory, 30);
		$I->waitForElementVisible(ProductPage::$closeButton, 30);
		$I->wait(1);
		$I->click(ProductPage::$closeButton);
		$I->waitForElement(ProductPage::$newButton, 30);
		$I->waitForElementVisible(ProductPage::$searchProductID, 30);
		$I->seeElement(ProductPage::$searchProductID);
		$I->searchForItemInFrontend($name, ['search field locator id' => ProductPage::$searchProduct]);
		$I->waitForElementVisible(['link' => $name]);
	}

	/**
	 * @param $name
	 * @throws \Exception
	 */
	public function deleteRedshopbProduct($name)
	{
		$I = $this;
		$I->comment('I navigate to Products page in frontend');
		$I->deleteObject($name, ProductPage::$URLProducts, ProductPage::$searchProduct);
	}

	/**
	 * @param $name
	 * @param $customerNumber
	 * @param $address
	 * @param $zipcode
	 * @param $city
	 * @param $country
	 * @param $customerAt
	 * @param array $params
	 * @throws \Exception
	 */
	public function createRedshopbCompany($name, $customerNumber, $address, $zipcode, $city, $country, $customerAt, $params = [])
	{
		$I = $this;

		$I->amGoingTo('Navigate to Companies page in frontend');
		$I->amOnPage(CompanyPage::$URLCompanies);

		$I->waitForElementVisible(CompanyPage::$newButton, 30);
		$I->click(CompanyPage::$newButton);

		$I->comment('I am redirected to the form');
		$I->wait(1);
		$I->waitForElement(CompanyPage::$adminForm, 30);
		$I->waitForElementVisible(CompanyPage::$customerNumber, 30);
		$I->fillField(CompanyPage::$customerNumber, $customerNumber);
		$I->fillField(CompanyPage::$nameID, $name);
		$I->fillField(CompanyPage::$addressField, $address);
		$I->fillField(CompanyPage::$zipCodeField, $zipcode);
		$I->fillField(CompanyPage::$cityField, $city);
		$I->selectOptionInChosenjs(CompanyPage::$countryField, $country);
		$I->selectOptionInChosenjs(CompanyPage::$customerAt, $customerAt);
		if (isset($params['Name Second Line']))
		{
			$I->fillField(['id' => 'jform_name2'], $params['Name Second Line']);
		}

		if (isset($params['Company Currency']))
		{
			$I->selectOptionInChosenjs('Company Currency', $params['Company Currency']);
		}
		$I->wait(1);
		$I->waitForElementVisible(CompanyPage::$saveCloseButton, 30);
		$I->click(CompanyPage::$saveCloseButton);
		$I->waitForText(CompanyPage::$saveCompanySuccess, 30, CompanyPage::$messageSuccessID);
		$I->waitForElementVisible(CompanyPage::$newButton, 30);
		$I->searchForItemInFrontend($name, ['search field locator id' => CompanyPage::$searchCompanies]);
		$I->waitForText($name, 60);
		$I->waitForElementVisible(CompanyPage::$iconClear, 30);
		$I->click(CompanyPage::$iconClear);
	}

	/**
	 * @param $name
	 * @param $taxGroup
	 * @throws \Exception
	 */
	public function editRedshopbCompanyWithVAT($name, $taxGroup, $taxVatGroupBasedOn)
	{
		$I = $this;
		$I->amGoingTo('Navigate to Companies page in frontend');
		$I->amOnPage(CompanyPage::$URLCompanies);
		$I->searchForItemInFrontend($name, ['search field locator id' => CompanyPage::$searchCompanies]);
		$I->waitForElementVisible(CompanyPage::$editButton, 30);
		$I->checkAllResults();
		$I->click(CompanyPage::$editButton);
		$I->scrollTo(CompanyPage::$taxConfiguration);
		$I->waitForText(CompanyPage::$taxVatGroupLbl, 30);
		$I->waitForElementVisible(CompanyPage::$taxVatGroupId, 30);
		$I->scrollTo(CompanyPage::$taxVatGroupId);
		$I->selectOptionInChosenXpath(CompanyPage::$taxVatGroupJform, $taxGroup);
		$I->selectOptionInChosenjs(CompanyPage::$taxVatGroupLbl, $taxGroup);
		$I->waitForText(CompanyPage::$taxVatBasedOnLbl, 30);
		$I->waitForElementVisible(CompanyPage::$taxVatBasedOnId, 30);
		$I->selectOptionInChosenXpath(CompanyPage::$taxVatBasedOnJform, $taxVatGroupBasedOn);
		$I->selectOptionInChosenjs(CompanyPage::$taxVatBasedOnLbl, $taxVatGroupBasedOn);
		$I->waitForElementVisible(CompanyPage::$saveCloseButton, 30);
		$I->click(CompanyPage::$saveCloseButton);
		$I->waitForText(CompanyPage::$editCompanySuccess, 30, CompanyPage::$messageSuccessID);
	}

	/**
	 * @param $name
	 * @throws \Exception
	 */
	public function deleteRedshopbCompany($name)
	{
		$I = $this;
		$I->amGoingTo('Navigate to Companies page in frontend');
		$I->amOnPage(CompanyPage::$URLCompanies);
		$I->searchForItemInFrontend($name, ['search field locator id' => CompanyPage::$searchCompanies]);
		$I->waitForElement(CompanyPage::$deleteButton, 30);
		$I->checkAllResults();
		$I->waitForElementVisible(CompanyPage::$deleteButton, 30);
		$I->click(CompanyPage::$deleteButton);

		$I->waitForElementVisible(['id' => 'companiesModal']);
		$I->waitForElementVisible(CompanyPage::$deleteButtonCompany);
		$I->wait(1);
		$I->click(CompanyPage::$deleteButtonCompany);

		$I->comment('I am redirected to the list');
		$I->waitForElement(['id' => CompanyPage::$searchCompanies], 30);
		$I->dontSeeElement(['link' => $name]);
	}

	/**
	 * @param $name
	 * @param $email
	 * @param $company
	 * @param array $params
	 * @throws \Exception
	 */
	public function createRedshopbUserEmployeeWithLogin($name, $email, $company, $params = array())
	{
		$I = $this;
		$I->amGoingTo('Navigate to Users page in frontend');
		$I->amOnPage(UserPage::$URLUsers);

		$I->waitForElement(UserPage::$newButton, 30);
		$I->click(UserPage::$newButton);


		$I->comment('I am redirected to the form');
		$I->waitForElement(UserPage::$adminForm, 30);
		$I->waitForElementVisible(UserPage::$idRole, 30);
		$I->selectOptionInChosenjs('Role', '05 :: Employee with login');
		$I->fillField(UserPage::$name1, $name);
		$I->fillField(UserPage::$loginNameId, $name);
		$I->fillField(UserPage::$passwordId, $name);
		$I->fillField(UserPage::$passwordConfirmId, $name);

		// @todo the following likes are a bypass for the issue RSBTB-2383. Remove when fixed
		$I->selectOptionInRadioField('Has Email?', 'No');
		$I->selectOptionInRadioField('Has Email?', 'Yes');

		$I->fillField(UserPage::$emailId, $email);
		$I->selectOptionInChosenjs(UserPage::$company, $company);

		$link = $name;

		if (isset($params['Name Second Line']))
		{
			$I->fillField(UserPage::$name2Id, $params['Name Second Line']);
			$link = $name . ' ' . $params['Name Second Line'];
		}

		$I->click(UserPage::$saveButton);
		$I->waitForElement(UserPage::$messageSuccessID);
		$I->waitForText(UserPage::$saveUserSuccess, 30, UserPage::$messageSuccessID);
		$I->click(UserPage::$closeButton);
		$I->seeElement(['link' => $link]);
	}

	/**
	 * @param $name
	 * @param $currency
	 * @param $credit
	 * @throws \Exception
	 */
	public function addCreditToEmployeeWithLogin($name, $currency, $credit)
	{
		$I = $this;
		$I->amGoingTo('Navigate to Users page in frontend');
		$I->amOnPage(UserPage::$URLUsers);

		$I->waitForElement(UserPage::$newButton, 30);
		$I->searchForItemInFrontend($name, ['search field locator id' => 'filter_search_users']);
		$I->waitForElement(['link' => $name], 30);
		$I->click(['link' => $name]);

		$I->comment('I am redirected to the form');
		$I->waitForElement(UserPage::$adminForm, 30);
		$I->click(UserPage::$walletOfUser);

		$I->waitForElementVisible(UserPage::$currencyOfUser, 30);
		$I->selectOptionInChosenjs(UserPage::$currencyField, $currency);
		$I->fillField(UserPage::$amountField, $credit);
		$I->click(UserPage::$addAmountButton);
		$I->waitForText(UserPage::$addMoneySuccess, 30, UserPage::$userWalletId);
		$I->waitForElementVisible(UserPage::$saveCloseButton, 30);
		$I->click(UserPage::$saveCloseButton);
		$I->waitForElement(['link' => $name], 30);
	}

	/**
	 * @param $category
	 * @param $cart
	 * @throws \Exception
	 */
	public function saveCart($category,$cart)
	{
		$I = $this;
		$I->amOnPage(Redshopb2bPage::$URLShop);
		$I->waitForElement(['link' => $category], 30);
		$I->click(['link' => $category]);
		$I->wait(0.5);
		$I->waitForText($category, 30, Redshopb2bPage::$categoryTitle);
		$I->waitForElement(Redshopb2bPage::$buttonAddToCart, 30);
		$I->wait(0.5);
		$I->click(Redshopb2bPage::$buttonAddToCart);
		try {
			$I->waitForElementVisible(Redshopb2bPage::$addToCartModal, 60);
		} catch (\Exception $e) {
			$I->reloadPage();
			$I->waitForElement(Redshopb2bPage::$buttonAddToCart, 30);
			$I->click(Redshopb2bPage::$buttonAddToCart);
			$I->waitForElementVisible(Redshopb2bPage::$addToCartModal, 60);
		}
		$I->wait(1);
		$I->waitForText(Redshopb2bPage::$messageAddToCartSuccess, 30);
		$I->waitForElement(Redshopb2bPage::$buttonCloseModalCart, 30);
		$I->waitForElement(Redshopb2bPage::$buttonGoesCheckout, 30);
		$I->click(Redshopb2bPage::$buttonGoesCheckout);
		$I->waitForElement(Redshopb2bPage::$linkCartFirst, 30);
		$I->click(Redshopb2bPage::$buttonSaveCart);
		$I->comment('I Wait for Save Cart modal to appear');
		$I->waitForText(Redshopb2bPage::$labelSaveCartLabel, 30, Redshopb2bPage::$labelSaveCartXpath);
		$I->waitForElementVisible(Redshopb2bPage::$nameCartField, 60);
		$I->wait(1);
		$I->fillField(Redshopb2bPage::$nameCartField, $cart);
		$I->waitForElement(Redshopb2bPage::$saveCloseButton, 30);
		$I->click(Redshopb2bPage::$saveCloseButton);
		$I->comment('I Wait for Save Cart modal to close');
		$I->waitForText(Redshopb2bPage::$messageSaveCartSuccess, 60, Redshopb2bPage::$systemContainer);
		$I->amOnPage(Redshopb2bPage::$URLCart);
		$I->waitForElement(Redshopb2bPage::$cartTable, 30);
		$I->searchForItemInFrontend($cart, ['search field locator id' => Redshopb2bPage::$searchCart]);
		$I->waitForText($cart, 30, Redshopb2bPage::$cartTable);
	}

	/**
	 * @param $category1
	 * @param $category2
	 * @param $cart
	 * @throws \Exception
	 */
	public function saveCartWithFunctionSaveToCartBy($category1, $category2, $cart, $product1, $product2, $saveToCartBy = array())
	{
		$I = $this;
		$I->amOnPage(Redshopb2bPage::$URLShop);
		$I->waitForElementVisible(['link' => $category1], 30);
		$I->click(['link' => $category1]);
		$I->waitForText($category1, 30, Redshopb2bPage::$categoryTitle);
		$I->waitForElementVisible(Redshopb2bPage::$buttonAddToCart, 30);
		$I->click(Redshopb2bPage::$buttonAddToCart);
		try
		{
			$I->waitForElementVisible(Redshopb2bPage::$addToCartModal, 60);
		} catch (\Exception $e)
		{
			$I->reloadPage();
			$I->waitForElementVisible(Redshopb2bPage::$buttonAddToCart, 30);
			$I->click(Redshopb2bPage::$buttonAddToCart);
			$I->waitForElementVisible(Redshopb2bPage::$addToCartModal, 60);
		}
		$I->waitForText(Redshopb2bPage::$messageAddToCartSuccess, 30);
		$I->waitForElementVisible(Redshopb2bPage::$buttonCloseModalCart, 30);
		$I->waitForElementVisible(Redshopb2bPage::$buttonGoesCheckout, 30);
		$I->click(Redshopb2bPage::$buttonGoesCheckout);
		$I->waitForElementVisible(Redshopb2bPage::$linkCartFirst, 30);
		$I->waitForElementVisible(Redshopb2bPage::$buttonSaveCart, 30);
		$I->click(Redshopb2bPage::$buttonSaveCart);
		$I->comment('I wait for Save Cart modal to approved');
		try
		{
			$I->waitForText(Redshopb2bPage::$labelSaveCartLabel, 30, Redshopb2bPage::$labelSaveCartXpath);
		} catch (\Exception $e)
		{
			$I->reloadPage();
			$I->waitForElementVisible(Redshopb2bPage::$linkCartFirst, 30);
			$I->waitForElementVisible(Redshopb2bPage::$buttonSaveCart, 30);
			$I->click(Redshopb2bPage::$buttonSaveCart);
			$I->waitForText(Redshopb2bPage::$labelSaveCartLabel, 30, Redshopb2bPage::$labelSaveCartXpath);
		}
		$I->waitForElementVisible(Redshopb2bPage::$nameCartField, 30);
		$I->fillField(Redshopb2bPage::$nameCartField, $cart);
		$I->waitForElementVisible(Redshopb2bPage::$saveCloseButton, 30);
		$I->click(Redshopb2bPage::$saveCloseButton);
		$I->comment('I wait for Save Cart modal to close');
		try
		{
			$I->waitForText(Redshopb2bPage::$messageSaveCartSuccess, 10, Redshopb2bPage::$systemContainer);
		}catch (\Exception $exception)
		{
			$I->reloadPage();
			$I->waitForElementVisible(Redshopb2bPage::$buttonSaveCart, 30);
			$I->click(Redshopb2bPage::$buttonSaveCart);
			$I->comment('I wait for Save Cart modal to approved');
			$I->waitForText(Redshopb2bPage::$labelSaveCartLabel, 30, Redshopb2bPage::$labelSaveCartXpath);
			$I->waitForElementVisible(Redshopb2bPage::$nameCartField, 30);
			$I->fillField(Redshopb2bPage::$nameCartField, $cart);
			$I->waitForElement(Redshopb2bPage::$saveCloseButton, 30);
			$I->click(Redshopb2bPage::$saveCloseButton);
			$I->comment('I wait for Save Cart modal to close again');
			$I->waitForText(Redshopb2bPage::$messageSaveCartSuccess, 10, Redshopb2bPage::$systemContainer);
		}
		$I->waitForElementVisible(Redshopb2bPage::$removeProductFromCart, 30);
		$I->wait(0.2);
		$I->click(Redshopb2bPage::$removeProductFromCart);
		$I->reloadPage();
		$I->amOnPage(Redshopb2bPage::$URLShop);
		$I->waitForElementVisible(['link' => $category2], 30);
		$I->click(['link' => $category2]);
		$I->waitForText($category2, 30, Redshopb2bPage::$categoryTitle);
		$I->waitForElementVisible(Redshopb2bPage::$buttonAddToCart, 30);
		$I->click(Redshopb2bPage::$buttonAddToCart);
		try
		{
			$I->waitForElementVisible(Redshopb2bPage::$addToCartModal, 60);
		} catch (\Exception $e)
		{
			$I->reloadPage();
			$I->waitForElementVisible(Redshopb2bPage::$buttonAddToCart, 30);
			$I->click(Redshopb2bPage::$buttonAddToCart);
			$I->waitForElementVisible(Redshopb2bPage::$addToCartModal, 60);
		}
		$I->waitForText(Redshopb2bPage::$messageAddToCartSuccess, 30);
		$I->waitForElementVisible(Redshopb2bPage::$buttonCloseModalCart, 30);
		$I->waitForElementVisible(Redshopb2bPage::$buttonGoesCheckout, 30);
		$I->click(Redshopb2bPage::$buttonGoesCheckout);
		$I->waitForElementVisible(Redshopb2bPage::$linkCartFirst, 30);
		$I->click(Redshopb2bPage::$buttonSaveCart);
		$I->comment('I wait for Save Cart modal to appear');
		$I->waitForText(Redshopb2bPage::$labelSaveCartLabel, 30, Redshopb2bPage::$labelSaveCartXpath);
		$I->wait(0.5);
		$I->selectOptionInChosenXpath(Redshopb2bPage::$saveCartAs, $cart);
		$I->waitForElementVisible(Redshopb2bPage::$saveCloseButton, 30);
		$I->click(Redshopb2bPage::$saveCloseButton);
		$I->comment('I wait for Save Cart modal to close');
		$I->waitForText(Redshopb2bPage::$messageSaveCartSuccess, 60, Redshopb2bPage::$systemContainer);
		$I->amOnPage(Redshopb2bPage::$URLCart);
		$I->waitForElementVisible(Redshopb2bPage::$cartTable, 30);
		$I->searchForItemInFrontend($cart, ['search field locator id' => Redshopb2bPage::$searchCart]);
		$I->waitForText($cart, 30, Redshopb2bPage::$cartTable);
		$I->waitForElementVisible(['link' => $cart], 30);
		$I->click(['link' => $cart]);
		if($saveToCartBy == 'Add to cart')
		{
			$I->waitForText($product1, 30);
			$I->waitForText($product2, 30);
		}
		if($saveToCartBy == 'Overwrite Cart')
		{
			$I->waitForText($product2, 30);
		}
	}

	/**
	 * @param $cart
	 * @throws \Exception
	 */
	public function saveCartCheckout($cart)
	{
		$I = $this;
		$I->amOnPage(Redshopb2bPage::$URLCart);
		$I->waitForElementVisible(Redshopb2bPage::$cartTable, 30);
		$I->searchForItemInFrontend($cart, ['search field locator id' => Redshopb2bPage::$searchCart]);
		$I->waitForElementVisible(ProductPage::$btnCheckoutCart, 30);
		$I->click(ProductPage::$btnCheckoutCart);
		$I->waitForElementVisible(ProductPage::$linkCartFirst, 30);
		$I->click(ProductPage::$nextButton);
		$I->waitForElementVisible(ProductPage::$nextButton, 30);
		$I->wait(0.5);
		$I->click(ProductPage::$nextButton);

		$I->waitForElementVisible(ProductPage::$completeOderButton, 30);
		$I->click(ProductPage::$completeOderButton);
		$I->waitForText(ProductPage::$messageCartToOrder, 30, ProductPage::$systemContainer);
		$I->see(ProductPage::$messageCartToOrder, ProductPage::$systemContainer);
	}

	/**
	 * @param $name
	 * @throws \Exception
	 */
	public function deleteRedshopbUser($name)
	{
		$I = $this;
		$I->comment('I Navigate to Departments page in frontend');
		$I->deleteObject($name, UserPage::$URLUsers, UserPage::$searchUser);
	}

	/**
	 * @param $cart
	 * @throws \Exception
	 */
	public function deleteSaveCart($cart)
	{
		$I = $this;
		$I->comment('I delete save cart');
		$I->amOnPage(Redshopb2bPage::$URLCart);
		$I->waitForElement(Redshopb2bPage::$cartTable, 30);
		$I->searchForItemInFrontend($cart, ['search field locator id' => Redshopb2bPage::$searchCart]);
		$I->waitForElement(Redshopb2bPage::$deleteSaveCart, 30);
		$I->click(Redshopb2bPage::$deleteSaveCart);
		$I->wait(0.5);
		$I->searchForItemInFrontend($cart, ['search field locator id' => Redshopb2bPage::$searchCart]);
		$I->dontSee($cart, Redshopb2bPage::$cartTable);
	}

	/**
	 * @param $name
	 * @param $URL
	 * @param $filter
	 * @throws \Exception
	 */
	public function deleteObject($name,$URL,$filter)
	{
		$I = $this;
		$I->comment('I Navigate to Departments page in frontend');
		$I->amOnPage($URL);
		$I->comment('I search this user');
		$I->searchForItemInFrontend($name, ['search field locator id' => $filter]);
		$I->wait(0.5);
		$I->checkAllResults();
		$I->waitForElement(Redshopb2bPage::$deleteButton, 30);
		$I->click(Redshopb2bPage::$deleteButton);
		$I->comment('I am redirected to the list');
		$I->waitForElement(['id' => $filter], 30);
		$I->dontSeeElement(['link' => $name]);
	}

	/**
	 * @param $employeeWithLogin
	 * @param $category
	 * @param $currencySeparator
	 * @param $currency
	 * @param array $products
	 * @throws \Exception
	 */
	public function checkout($user ,$category, $currencySeparator, $currency, $products = array(), $shippingRate = array())
	{
		$I = $this;
		$I->doFrontEndLogin($user['username'], $user['username']);
		$I->amOnPage(Redshopb2bPage::$URLShop);
		$I->waitForElement(['link' => $category], 30);
		$I->click(['link' => $category]);
		$I->waitForText($category, 30, Redshopb2bPage::$categoryClass);
		try
		{
			$I->waitForElement(['link' => $products['name']], 30);
		}catch (\Exception $e)
		{
			$I->click(['link' => $category]);
			$I->waitForText($category, 30, Redshopb2bPage::$categoryClass);
			$I->waitForElement(['link' => $products['name']], 30);
		}
		$I->waitForElementVisible(Redshopb2bPage::$buttonAddToCart, 30);
		$I->click(Redshopb2bPage::$buttonAddToCart);
		$I->waitForElementVisible(Redshopb2bPage::$addToCartModal, 30);
		$I->waitForText(Redshopb2bPage::$messageAddToCartSuccess, 30);
		$I->waitForText($products['name'], 30);
		$I->waitForElementVisible(Redshopb2bPage::$btnGoesToCheckout, 30);
		$I->click(Redshopb2bPage::$btnGoesToCheckout);
		$I->waitForElementVisible(Redshopb2bPage::$linkCartFirst, 30);
		$totalWithQuantity = (int) $products['price'];
		$totalPrices = (string) $totalWithQuantity . $currencySeparator . '00 ' . $currency;
		$I->waitForText($totalPrices, 30, Redshopb2bPage::$priceTotalFinal);
		$I->waitForElementVisible(Redshopb2bPage::$nextButton, 30);
		$I->click(Redshopb2bPage::$nextButton);
		$I->wait(0.5);
		$I->waitForText(Redshopb2bPage::$deliveryInfoContent, 30, Redshopb2bPage::$deliveryInfo);
//		$I->waitForElement(Redshopb2bPage::$userBillingInfor, 30);
//		$I->click(Redshopb2bPage::$userBillingInfor);
//		$I->waitForElement(Redshopb2bPage::$emailBilling, 30);
//		$I->wait(1);
//		$I->fillField(Redshopb2bPage::$emailBilling, $user['email']);
//		$I->fillField(Redshopb2bPage::$nameBilling, $user['name']);
//		$I->fillField(Redshopb2bPage::$name2Billing, $user['name2']);
//		$I->fillField(Redshopb2bPage::$phoneBilling, $user['phone']);
//		$I->waitForElement(Redshopb2bPage::$updateButtonBilling, 30);
//		$I->wait(3);
//		try
//		{
//			$I->click(Redshopb2bPage::$updateButtonBilling);
//			$I->wait(1);
//			$I->scrollTo(Redshopb2bPage::$emailBilling);
//			$I->waitForText($user['email'], 30);
//		} catch (\Exception $e)
//		{
//			$I->waitForElement(Redshopb2bPage::$updateButtonBilling, 30);
//			$I->wait(1);
//			$I->click(Redshopb2bPage::$updateButtonBilling);
//			$I->wait(1);
//			$I->reloadPage();
//			$I->waitForElement(Redshopb2bPage::$userBillingInfor, 30);
//			$I->click(Redshopb2bPage::$userBillingInfor);
//			$I->scrollTo(Redshopb2bPage::$emailBilling);
//			$I->waitForText($user['email'], 30);
//		}
		$I->waitForElementVisible(Redshopb2bPage::$nextButton, 30);
		$I->click(Redshopb2bPage::$nextButton);
		$I->waitForElementVisible(Redshopb2bPage::$completeOderButton, 30);
		$I->click(Redshopb2bPage::$completeOderButton);
		$I->waitForElementVisible(Redshopb2bPage::$messageSuccessID, 30);
		$I->waitForText(Redshopb2bPage::$messageOrderSuccess, 30, Redshopb2bPage::$messageSuccessID);
		$I->doFrontendLogout();
	}

	/**
	 * @param       $employeeWithLogin
	 * @param       $category
	 * @param       $totalPrice
	 * @param       $products
	 * @param array $billingAddress
	 * @throws \Exception
	 */
	public function checkoutWithAesirECStatusModule($user, $category, $totalPrice, $products)
	{
		$I = $this;
		$I->doFrontEndLogin($user['username'], $user['username']);
		$I->amOnPage(Redshopb2bPage::$URLShop);
		$I->waitForElement(['link' => $category], 30);
		$I->click(['link' => $category]);
		$I->waitForText($category, 30, Redshopb2bPage::$categoryTitle);
		$I->waitForElement(Redshopb2bPage::$buttonAddToCart, 30);
		$I->click(Redshopb2bPage::$buttonAddToCart);
		try
		{
			$I->waitForElementVisible(Redshopb2bPage::$addToCartModal, 60);
		} catch (\Exception $e)
		{
			$I->reloadPage();
			$I->waitForElement(Redshopb2bPage::$buttonAddToCart, 30);
			$I->click(Redshopb2bPage::$buttonAddToCart);
			$I->waitForElementVisible(Redshopb2bPage::$addToCartModal, 60);
		}
		$I->waitForText(Redshopb2bPage::$messageAddToCartSuccess, 30);
		$I->waitForText($products, 30);
		$I->waitForElement(Redshopb2bPage::$buttonCloseModalCart, 30);
		$I->click(Redshopb2bPage::$buttonCloseModalCart);
		$I->waitForText($category, 30, Redshopb2bPage::$categoryTitle);

		$I->comment("I try to checkout with Aesir E-Commerce Status Module");
		$I->waitForElement(Redshopb2bPage::$statusModule, 30);
		$I->click(Redshopb2bPage::$statusModule);
		$I->waitForElement(Redshopb2bPage::$cartStatusModule, 30);
		$I->waitForElement(Redshopb2bPage::$checkoutStatusModule, 30);
		$I->click(Redshopb2bPage::$checkoutStatusModule);
		$I->waitForElement(Redshopb2bPage::$linkCartFirst, 30);
		$I->waitForText($totalPrice, 30, Redshopb2bPage::$priceTotalFinal);
		$I->waitForElementVisible(Redshopb2bPage::$nextButton, 30);
		$I->click(Redshopb2bPage::$nextButton);
		$I->waitForText(Redshopb2bPage::$deliveryInfoContent, 30, Redshopb2bPage::$deliveryInfo);
//		$I->waitForElement(Redshopb2bPage::$userBillingInfor, 30);
//		$I->click(Redshopb2bPage::$userBillingInfor);
//		$I->waitForElement(Redshopb2bPage::$emailBilling, 30);
//		$I->wait(1);
//		$I->fillField(Redshopb2bPage::$emailBilling, $user['email']);
//		$I->fillField(Redshopb2bPage::$nameBilling, $user['name']);
//		$I->fillField(Redshopb2bPage::$name2Billing, $user['name2']);
//		$I->fillField(Redshopb2bPage::$phoneBilling, $user['phone']);
//		$I->waitForElement(Redshopb2bPage::$updateButtonBilling, 30);
//		$I->wait(1);
//		$I->click(Redshopb2bPage::$updateButtonBilling);
//		$I->wait(1);
//		$I->reloadPage();
//		$I->waitForElement(Redshopb2bPage::$userBillingInfor, 30);
//		$I->click(Redshopb2bPage::$userBillingInfor);
//		$I->scrollTo(Redshopb2bPage::$emailBilling);
//		$I->waitForText($user['email'], 30);
		$I->waitForElementVisible(Redshopb2bPage::$nextButton, 30);
		$I->click(Redshopb2bPage::$nextButton);
//		$I->waitForText($user['email'], 30);
		$I->waitForElementVisible(Redshopb2bPage::$completeOderButton, 30);
		$I->click(Redshopb2bPage::$completeOderButton);
		$I->waitForElementVisible(Redshopb2bPage::$messageSuccessID, 30);
		$I->waitForText(Redshopb2bPage::$messageOrderSuccess, 30, Redshopb2bPage::$messageSuccessID);
		$I->doFrontendLogout();
	}
	/**
	 * @param $user
	 * @param $category
	 * @param $totalPrice
	 * @param $products
	 * @throws \Exception
	 */
	public function checkoutUpdateProductOnCartModule($user, $category1, $category2, $products1 = array(), $products2 = array(), $currencySeparator, $currency)
	{
		$I = $this;
		$I->doFrontEndLogin($user['username'], $user['username']);
		$I->amOnPage(Redshopb2bPage::$URLShop);
		$I->waitForElementVisible(['link' => $category1], 30);
		$I->click(['link' => $category1]);
		$I->waitForText($category1, 30, Redshopb2bPage::$categoryClass);
		try
		{
			$I->waitForElementVisible(['link' => $products1['name']], 30);
		}catch (\Exception $e)
		{
			$I->click(['link' => $category1]);
			$I->waitForText($category1, 30, Redshopb2bPage::$categoryClass);
			$I->waitForElementVisible(['link' => $products1['name']], 30);
		}
		$I->waitForElementVisible(Redshopb2bPage::$buttonAddToCart, 30);
		$I->click(Redshopb2bPage::$buttonAddToCart);
		$I->waitForElementVisible(Redshopb2bPage::$addToCartModal, 30);
		$I->waitForText(Redshopb2bPage::$messageAddToCartSuccess, 30);
		$I->waitForElementVisible(Redshopb2bPage::$btnGoesToCheckout, 30);
		$I->click(Redshopb2bPage::$btnGoesToCheckout);
		$I->waitForElementVisible(Redshopb2bPage::$linkCartFirst, 30);

//		$I->comment('Try to change quantity of product');
//		$I->waitForElementVisible(Redshopb2bPage::$quantity, 30);
//		$I->pressKey(Redshopb2bPage::$quantity, \Facebook\WebDriver\WebDriverKeys::DELETE);
//		$I->pressKey(Redshopb2bPage::$quantity, \Facebook\WebDriver\WebDriverKeys::DELETE);
//		$I->pressKey(Redshopb2bPage::$quantity, \Facebook\WebDriver\WebDriverKeys::BACKSPACE);
//		$I->pressKey(Redshopb2bPage::$quantity, \Facebook\WebDriver\WebDriverKeys::BACKSPACE);
//		$I->waitForElementVisible(Redshopb2bPage::$quantity, 60);
//		$I->fillField(Redshopb2bPage::$quantity, $products1['quantity']);
//		$I->pressKey(Redshopb2bPage::$quantity, \Facebook\WebDriver\WebDriverKeys::ENTER);
//		$I->pressKey(Redshopb2bPage::$quantity, \Facebook\WebDriver\WebDriverKeys::ENTER);
//		$I->pressKey(Redshopb2bPage::$quantity, \Facebook\WebDriver\WebDriverKeys::ENTER);
//		$I->waitForElementVisible(Redshopb2bPage::$nextButton, 30);
//		$I->click(Redshopb2bPage::$nextButton);
//		$I->waitForElementVisible(Redshopb2bPage::$backButton, 30);
//		$I->click(Redshopb2bPage::$backButton);
//		$I->comment('Try to check total price');
//		$totalPrice1WithQuantity = (int) $products1['price'] * $products1['quantity'];
//		$totalPrice = (string) $totalPrice1WithQuantity . $currencySeparator . '00 ' . $currency;
//		$I->waitForText($totalPrice, 30, Redshopb2bPage::$priceTotalFinal);

		$I->comment("Try to add to cart more product for checkout");
		$I->amOnPage(Redshopb2bPage::$URLShop);
		$I->waitForElementVisible(['link' => $category2], 30);
		$I->click(['link' => $category2]);
		$I->waitForText($category2, 30, Redshopb2bPage::$categoryClass);
		try
		{
			$I->waitForElementVisible(['link' => $products2['name']], 30);
		}catch (\Exception $e)
		{
			$I->click(['link' => $category2]);
			$I->waitForText($category2, 30, Redshopb2bPage::$categoryClass);
			$I->waitForElementVisible(['link' => $products2['name']], 30);
		}
		$I->waitForElementVisible(Redshopb2bPage::$buttonAddToCart, 30);
		$I->click(Redshopb2bPage::$buttonAddToCart);
		$I->waitForElementVisible(Redshopb2bPage::$addToCartModal, 30);
		$I->waitForText(Redshopb2bPage::$messageAddToCartSuccess, 30);
		$I->waitForElementVisible(Redshopb2bPage::$btnGoesToCheckout, 30);
		$I->click(Redshopb2bPage::$btnGoesToCheckout);
		$I->waitForElementVisible(Redshopb2bPage::$linkCartFirst, 30);

		$I->comment('Try to delete product form cart');
		$usePage = new CheckoutPage();
		$I->reloadPage();
		$I->waitForElementVisible(Redshopb2bPage::$statusModule, 30);
		$I->click(Redshopb2bPage::$statusModule);
		$I->waitForElementVisible(Redshopb2bPage::$cartStatusModule, 30);
		$I->waitForElementVisible($usePage->returnIconDelete($products2['position']),60);
		$I->click($usePage->returnIconDelete($products2['position']));
		$I->waitForElementVisible(Redshopb2bPage::$linkCartFirst, 30);

		$I->comment('Try to check total price');
//		$totalPrice1WithQuantity = (int) $products1['price'] * $products1['quantity'];
//		$totalPrice = (string) $totalPrice1WithQuantity . $currencySeparator . '00 ' . $currency;
		$totalPrice = (string) $products1['price'] . $currencySeparator . '00 ' . $currency;
		$I->waitForText($totalPrice, 30, Redshopb2bPage::$priceTotalFinal);
		$I->waitForElementVisible(Redshopb2bPage::$nextButton, 30);
		$I->click(Redshopb2bPage::$nextButton);
		$I->waitForText(Redshopb2bPage::$deliveryInfoContent, 30, Redshopb2bPage::$deliveryInfo);
		$I->waitForElementVisible(Redshopb2bPage::$nextButton, 30);
		$I->click(Redshopb2bPage::$nextButton);
		$I->waitForElementVisible(Redshopb2bPage::$completeOderButton, 30);
		$I->click(Redshopb2bPage::$completeOderButton);
		$I->waitForElementVisible(Redshopb2bPage::$messageSuccessID, 30);
		$I->waitForText(Redshopb2bPage::$messageOrderSuccess, 30, Redshopb2bPage::$messageSuccessID);
	}

	/**
	 * @param       $user
	 * @param       $category
	 * @param array $products1
	 * @param       $currencySeparator
	 * @param       $currency
	 * @throws \Exception
	 */
	public function checkoutWithUpdatePhoneNumber($user, $category, $product = array(), $currencySeparator, $currency)
	{
		$I = $this;
		$I->doFrontEndLogin($user['username'], $user['username']);

		$I->comment('I try to change phone number');
		$I->amOnPage(Redshopb2bPage::$urlMyProfile);
		$I->waitForElementVisible(Redshopb2bPage::$editBilling, 30);
		$I->click(Redshopb2bPage::$editBilling);
		$I->waitForElementVisible(Redshopb2bPage::$phoneID, 30);
		$I->fillField(Redshopb2bPage::$phoneID, $user['phone']);
		$I->waitForElementVisible(Redshopb2bPage::$saveCloseButton, 30);
		$I->click(Redshopb2bPage::$saveCloseButton);

		$I->comment('I try to add product to cart and checkout');
		$I->amOnPage(Redshopb2bPage::$URLShop);
		$I->waitForElementVisible(['link' => $category], 30);
		$I->click(['link' => $category]);
		$I->waitForText($category, 30, Redshopb2bPage::$categoryClass);
		try
		{
			$I->waitForElementVisible(['link' => $product['name']], 30);
		}catch (\Exception $e)
		{
			$I->click(['link' => $category]);
			$I->waitForText($category, 30, Redshopb2bPage::$categoryClass);
			$I->waitForElementVisible(['link' => $product['name']], 30);
		}
		$I->waitForElementVisible(Redshopb2bPage::$buttonAddToCart, 30);
		$I->click(Redshopb2bPage::$buttonAddToCart);
		$I->waitForElementVisible(Redshopb2bPage::$addToCartModal, 30);
		$I->waitForText(Redshopb2bPage::$messageAddToCartSuccess, 30);
		$I->waitForElementVisible(Redshopb2bPage::$btnGoesToCheckout, 30);
		$I->wait(0.5);
		$I->click(Redshopb2bPage::$btnGoesToCheckout);
		$I->waitForElementVisible(Redshopb2bPage::$linkCartFirst, 30);

		$I->comment('I try to check total prices');
		$totalPrice = (int) $product['price'];
		$totalPriceFinal = (string) $totalPrice . $currencySeparator . '00 ' . $currency;
		$I->waitForText($totalPriceFinal, 30, Redshopb2bPage::$priceTotalFinal);
		$I->waitForElementVisible(Redshopb2bPage::$nextButton, 30);
		$I->wait(0.5);
		$I->click(Redshopb2bPage::$nextButton);
		$I->click(Redshopb2bPage::$userBillingInfor);
		$I->waitForElement(Redshopb2bPage::$emailBilling, 30);
		$I->fillField(Redshopb2bPage::$emailBilling, $user['email']);
		$I->fillField(Redshopb2bPage::$nameBilling, $user['name']);
		$I->fillField(Redshopb2bPage::$name2Billing, $user['name2']);
		$I->fillField(Redshopb2bPage::$phoneBilling, $user['phone']);
		$I->waitForElementVisible(Redshopb2bPage::$saveAsNewButton, 30);
		$I->click(Redshopb2bPage::$saveAsNewButton);
		$I->wait(0.5);
		$I->waitForElementVisible(Redshopb2bPage::$nextButton, 30);
		$I->click(Redshopb2bPage::$nextButton);

		try
		{
			$I->waitForElementNotVisible(Redshopb2bPage::$emailBilling, 10);
		}catch (\Exception $e)
		{
			$I->waitForElementVisible(Redshopb2bPage::$nextButton, 30);
			$I->click(Redshopb2bPage::$nextButton);
		}

		$I->comment('I try to check phone number');
		$use = new CheckoutPage();
		$I->waitForElement($use->getXpathValue($user['phone']),60);
		$I->wait(0.5);
		$I->waitForElementVisible(Redshopb2bPage::$completeOderButton, 30);
		$I->wait(0.5);
		$I->click(Redshopb2bPage::$completeOderButton);
		$I->waitForElementVisible(Redshopb2bPage::$messageSuccessID, 30);
		$I->waitForText(Redshopb2bPage::$messageOrderSuccess, 30, Redshopb2bPage::$messageSuccessID);
		$I->doFrontendLogout();
	}

	/**
	 * @param array $user
	 * @param $category
	 * @param $currencySeparator
	 * @param $currency
	 * @param array $products
	 * @param array $shippingRate
	 * @throws \Exception
	 */
	public function checkoutWithShippingAndPayment($user, $category, $currencySeparator, $currency, $products = array(), $shippingRate = array())
	{
		$I = $this;
		$I->wantToTest('Product checkout in Frontend with Shipping and Payment method');
		$I->doFrontEndLogin($user['username'], $user['username']);
		$I->amOnPage(Redshopb2bPage::$URLShop);
		$I->waitForElementVisible(['link' => $category], 30);
		$I->click(['link' => $category]);
		$I->waitForText($category, 30, Redshopb2bPage::$categoryClass);
		try
		{
			$I->waitForElementVisible(['link' => $products['name']], 30);
		}catch (\Exception $e)
		{
			$I->click(['link' => $category]);
			$I->waitForText($category, 30, Redshopb2bPage::$categoryClass);
			$I->waitForElementVisible(['link' => $products['name']], 30);
		}
		$I->waitForElementVisible(Redshopb2bPage::$buttonAddToCart, 30);
		$I->click(Redshopb2bPage::$buttonAddToCart);
		$I->waitForElementVisible(Redshopb2bPage::$addToCartModal, 30);
		$I->waitForText(Redshopb2bPage::$messageAddToCartSuccess, 30);
		$I->waitForText($products['name'], 30);
		$I->click(Redshopb2bPage::$btnGoesToCheckout);
		$I->waitForElementVisible(Redshopb2bPage::$linkCartFirst, 30);
		$totalWithQuantity = (int) $products['price'];
		$totalWithQuantity = (string) $totalWithQuantity . $currencySeparator . '00 ' . $currency;
		$I->waitForElementVisible(Redshopb2bPage::$nextButton, 30);
		$I->click(Redshopb2bPage::$nextButton);
		$I->waitForText(Redshopb2bPage::$deliveryInfoContent, 30, Redshopb2bPage::$deliveryInfo);
		$I->waitForElementVisible(Redshopb2bPage::$userBillingInfor, 30);
		$I->click(Redshopb2bPage::$userBillingInfor);
		$I->waitForElementVisible(Redshopb2bPage::$emailBilling, 30);
		$I->fillField(Redshopb2bPage::$emailBilling, $user['email']);
		$I->fillField(Redshopb2bPage::$nameBilling, $user['name']);
		$I->fillField(Redshopb2bPage::$name2Billing, $user['name2']);
		$I->fillField(Redshopb2bPage::$phoneBilling, $user['phone']);
		$I->waitForElementVisible(Redshopb2bPage::$saveAsNewButton, 30);
		$I->wait(0.5);
		$I->click(Redshopb2bPage::$saveAsNewButton);
		$I->wait(0.5);
		$I->waitForText($user['phone'], 30);
		$I->waitForElementVisible(Redshopb2bPage::$nextButton, 30);
		$I->click(Redshopb2bPage::$nextButton);
		$I->waitForText(Redshopb2bPage::$selectShippingMethodContent, 30);
		$I->waitForElementVisible(Redshopb2bPage::$shippingRateId, 30);
		$I->click(Redshopb2bPage::$shippingRateId);
		$I->comment('I check that shipping rate price is shown');
		$I->waitForText($shippingRate['name'] . " (" . $currency . " 2,55)", 30);
		$I->waitForElementVisible(Redshopb2bPage::$nextButton, 30);
		$I->click(Redshopb2bPage::$nextButton);
		$I->waitForText($currency . ' 3,55', 30);
		$I->waitForElementVisible(Redshopb2bPage::$completeOderButton, 30);
		$I->click(Redshopb2bPage::$completeOderButton);
	}

	/**
	 * @param       $employeeWithLogin
	 * @param       $category
	 * @param       $priceDiscount
	 * @param       $totalPrice
	 * @param array $products
	 * @throws \Exception
	 */
	public function checkoutAllDiscount($employeeWithLogin, $category, $discount, $totalPrice,  $productsPrice)
	{
		$I = $this;
		$I->doFrontEndLogin($employeeWithLogin, $employeeWithLogin);
		$I->amOnPage(Redshopb2bPage::$URLShop);
		$I->waitForElementVisible(['link' => $category], 30);
		$I->click(['link' => $category]);
		$I->waitForElementVisible(Redshopb2bPage::$buttonAddToCart, 30);
		$I->click(Redshopb2bPage::$buttonAddToCart);
		$I->waitForElementVisible(Redshopb2bPage::$addToCartModal, 30);
		$I->waitForText(Redshopb2bPage::$messageAddToCartSuccess, 30);
		$I->click(Redshopb2bPage::$btnGoesToCheckout);
		$I->waitForText($productsPrice, 30);
		$I->waitForText($discount, 30);
		$I->waitForText($totalPrice, 30, Redshopb2bPage::$priceTotalFinal);
		$I->waitForElementVisible(Redshopb2bPage::$nextButton, 30);
		$I->click(Redshopb2bPage::$nextButton);
		$I->waitForElementVisible(Redshopb2bPage::$nextButton, 30);
		$I->click(Redshopb2bPage::$nextButton);
		$I->waitForElementVisible(Redshopb2bPage::$completeOderButton, 30);
		$I->click(Redshopb2bPage::$completeOderButton);
		$I->waitForElementVisible(Redshopb2bPage::$messageSuccessID, 30);
		$I->doFrontEndLogout();
	}

	/**
	 * Selects an option in a Selectize js Selector based on its label
	 *
	 * @return void
	 * @throws \Exception
	 */
	public function selectOptionInSelectize($label, $option)
	{
		$I = $this;
		$I->waitForJS("return jQuery(\"label:contains('$label')\");");
		$selectID          = $I->executeJS("return jQuery(\"label:contains('$label')\").attr(\"for\");");
		$selectizeSelectID = $selectID . '_selectize';
		$dropwdownId       = $selectID . '_selectize_options';
		$I->waitForElement(['id' => $selectizeSelectID], 60);
		$I->comment("I open the $label chosen selector");
		$I->click(['xpath' => "//div[@id='$selectizeSelectID']/div"]);
		$I->waitForElement(['xpath' => "//div[@id='$selectizeSelectID']//input"], 60);
		$I->pressKey(['xpath' => "//div[@id='$selectizeSelectID']//input"], \Facebook\WebDriver\WebDriverKeys::BACKSPACE);
		$I->waitForElementVisible(['xpath' => "//div[@id='$selectizeSelectID']//input"], 30);
		$I->fillField(['xpath' => "//div[@id='$selectizeSelectID']//input"], $option);
		$I->waitForElement(['xpath' => "//div[@id='$dropwdownId']/div[@class='selectize-dropdown-content']/div[@data-title='$option']"], 60);
		$I->click(['xpath' => "//div[@id='$dropwdownId']/div[@class='selectize-dropdown-content']/div[@data-title='$option']"]);
	}

	// @todo: this function should be moved to JoomlaBrowser like https://github.com/joomla-projects/joomla-browser/pull/100/files
	public function selectOptionInChosenjs($label, $option)
	{
		$I = $this;

		$I->waitForJS("return jQuery(\"label:contains('$label')\");");
		$selectID = $I->executeJS("return jQuery(\"label:contains('$label')\").attr(\"for\");");

		$option = trim($option);

		$I->waitForJS(
			"jQuery('#$selectID option').filter(function(){ return this.text.trim() === \"$option\" }).prop('selected', true); return true;",
			30
		);
		$I->waitForJS(
			"jQuery('#$selectID').trigger('liszt:updated').trigger('chosen:updated'); return true;",
			30
		);
		$I->waitForJS(
			"jQuery('#$selectID').trigger('change'); return true;",
			30
		);
	}

	/**
	 * @param $id
	 * @param $value
	 * @throws \Exception
	 */
	public function selectOptionInChosenXpath($id, $value)
	{
		$I = $this;
		$I->waitForElementVisible("//div[@id='$id']/a", 30);
		$I->scrollTo("//div[@id='$id']/a");
		$I->wait(0.5);
		$I->click("//div[@id='$id']/a");
		$I->wait(0.5);
		$I->waitForElement("//div[@id='$id']/div/ul/li[contains(normalize-space(),'$value')]", 30);
		$I->scrollTo("//div[@id='$id']/div/ul/li[contains(normalize-space(),'$value')]");
		$I->wait(0.5);
		$I->click("//div[@id='$id']/div/ul/li[contains(normalize-space(),'$value')]");
	}
	public function scrollUp()
	{
		$I = $this;
		$I->executeJS('window.scrollTo(0,0)');
	}

	/**
	 * Selects an option in a Select2 js Selector  based on its label
	 *
	 * @return void
	 * @throws \Exception
	 */
	public function selectOptionInSelect2($label, $option)
	{
		$I = $this;
		$I->waitForJS("return jQuery(\"label:contains('$label')\");");
		$selectID         = $I->executeJS("return jQuery(\"label:contains('$label')\").attr(\"for\");");
		$dropwdownLocator = "//span[@class='select2-selection select2-selection--single select2_${selectID}_class']//span[@class='select2-selection__arrow']";
		$I->waitForElement(['xpath' => $dropwdownLocator], 60);
		$I->comment("I open the $label chosen selector");

		$I->click(['xpath' => $dropwdownLocator]);
		$I->waitForElementVisible(['class' => "select2-search__field"], 60);

		/*
		 @todo: clean field
		$I->comment('I clean field');
		$I->doubleClick();
		$I->pressKey(['xpath' => "//div[@id='jform_customer_ids_chzn']//ul/li//input"], \Facebook\WebDriver\WebDriverKeys::BACKSPACE);
		 */

		/*
		 @todo test adding individual chars
		$optionChars = str_split($option);
		foreach ($optionChars as $char)
		{
		$I->pressKey(['xpath' => "//div[@id='jform_customer_ids_chzn']//ul/li//input"], $char);
		}
		 */

		$I->fillField(['class' => "select2-search__field"], $option);
		$I->waitForElementVisible(['xpath' => "//ul[@id='select2-${selectID}-results']/li[@class='select2-results__option select2-results__option--highlighted']"], 60);
		$I->click(['xpath' => "//ul[@id='select2-${selectID}-results']/li[@class='select2-results__option select2-results__option--highlighted']"]);

	}

	/**
	 * Selects one or more options in a Select2 js Multiple Selector based on its label
	 *
	 * @param   string  $label    String with the <label> name of the field
	 * @param   array   $options  Array of strings containing the options to be selected
	 *
	 * @return void
	 * @throws \Exception
	 */

	public function selectOptionsInSelect2($label, $options = [])
	{
		$I = $this;
		$I->waitForJS("return jQuery(\"label:contains('$label')\");");
		$selectID         = $I->executeJS("return jQuery(\"label:contains('$label')\").attr(\"for\");");
		$dropwdownLocator = "//span[@class='select2-selection select2-selection--multiple select2_${selectID}_class']";
		$I->waitForElement(['xpath' => $dropwdownLocator], 60);
		$I->comment("I open the $label chosen selector");

		foreach ($options as $option)
		{
			$I->click(['xpath' => $dropwdownLocator]);
			$I->waitForElementVisible(['class' => "select2-search__field"], 60);
			$I->fillField(['class' => "select2-search__field"], $option);
			$I->waitForElementVisible(['xpath' => "//ul[@id='select2-${selectID}-results']/li[@class='select2-results__option select2-results__option--highlighted']"], 60);
			$I->click(['xpath' => "//ul[@id='select2-${selectID}-results']/li[@class='select2-results__option select2-results__option--highlighted']"]);
		}
	}

	/**
	 * Checks if an element is currently present on the page
	 *
	 * @param   string  $totalPrice  Total price with currency symbol
	 *
	 * @return void
	 * @throws \Exception
	 */
	public function runPaypalTest($totalPrice)
	{
		$I = $this;
		$I->comment('wait for Paypal to load');

		if ($I->isElementLoaded('id', 'loadLogin', 60))
		{
			$I->see($totalPrice, ['xpath' => "//div[@id='miniCart']/div[@class='wrap items totals item1']/ul/li[@class='small heavy']/span[@class='amount']"]);
			$I->comment('I open the using existing paypal account accordion');
			$I->click(['id' => 'loadLogin']);
			$I->waitForElementVisible(['id' => "login_email"], 60);
			$I->fillField(['id' => 'login_email'], "alexis-buyer@redcomponent.com");
			$I->fillField(['id' => 'login_password'], 'I10v3redK0mpont#');
			$I->click(['id' => 'submitLogin']);
			$I->waitForElement(['id' => "continue"], 120);
			$I->click(['id' => "continue"]);
			$I->waitForElement(['xpath' => "//span[@title='You just completed your payment.']"], 60);
			$I->seeElement(['xpath' => "//span[@title='You just completed your payment.']"]);
		}
		else
		{
			$I->waitForElement(['id' => 'loginSection'], 60);
			$I->see($totalPrice, ['xpath' => "//span[@id='transactionCart']/span[@class='ng-scope']/format-currency/span[@class='ltrOverride ng-binding']"]);
			$I->switchToIframe('injectedUl');
			$I->waitForElementVisible(['id' => "email"], 60);
			$I->fillField(['id' => 'email'], "alexis-buyer@redcomponent.com");
			$I->waitForElement(['id' => 'password'], 90);
			$I->fillField(['id' => 'password'], 'I10v3redK0mpont#');
			$I->waitForElement(['id' => 'btnLogin'], 90);
			$I->click(['id' => 'btnLogin']);
			$I->switchToIframe();
			$I->waitForElement(['id' => 'confirmButtonTop'], 90);
			$I->click(['id' => 'confirmButtonTop']);
			$I->waitForElement(['id' => 'paid-text'], 90);
			$I->see('You paid', ['xpath' => "//div[@id='paid-text']/span[@class='ng-scope']"]);
		}
	}

	/**
	 * Installs a Extension in Joomla using the file upload option
	 *
	 * @param   string  $file   Path to the file in the _data folder
	 * @param   string  $type  Type of Extension
	 *
	 * {@internal doAdminLogin() before}
	 *
	 * @return    void
	 * @throws \Exception
	 */
	public function installExtensionFromFileUpload($file, $type = 'Extension')
	{
		$I = $this;
		$I->amOnPage(Redshopb2bPage::$URLInstall);
		$I->waitForText('Extensions: Install', '30', ['css' => 'H1']);
		$I->click(['link' => 'Upload Package File']);
		$I->comment('I enter the file input');
		$I->attachFile(['id' => 'install_package'], $file);
		$I->click(['id' => 'installbutton_package']);
		$I->waitForText('was successful', '30', Redshopb2bPage::$systemContainer);

		if ($type == 'Extension')
		{
			$this->comment('Extension successfully installed.');
		}

		if ($type == 'Plugin')
		{
			$this->comment('Installing plugin was successful.');
		}

		if ($type == 'Package')
		{
			$this->comment('Installation of the package was successful.');
		}
	}

	/**
	 * Waits for an element and throws exception if it doesn't load
	 *
	 * @param   string  $selector    Type of selector
	 * @param   string  $attributes  Attributes
	 * @param   int     $timeout     Time allowed for action
	 *
	 * @return boolean
	 */
	public function isElementLoaded($selector, $attributes, $timeout)
	{
		$I = $this;

		try
		{
			$I->waitForElement([$selector => $attributes], $timeout);
		}
		catch (\Exception $e)
		{
			return false;
		}

		return true;
	}

	/**
	 * do front logout
	 * @throws \Exception
	 */
	public function doFrontendLogoutOrder()
	{
		$I = $this;
		$this->amOnPage(Redshopb2bPage::$URLLogin);
		$I->waitForElement(Redshopb2bPage::$logoutButton, 30);
		$I->click(Redshopb2bPage::$logoutButton);
	}

	/**
	 * @param $menuName
	 * @param $menuType
	 * @param $menuTypeSpecific
	 * @throws \Exception
	 */
	public function createModule($menuName, $menuType, $menuTypeSpecific)
	{
		$client = $this;
		$this->amOnPage(Redshopb2bPage::$moduleUrl);
		$client->see(Redshopb2bPage::$menusItem);
		$client->waitForElement(Redshopb2bPage::$titleMenu, 30);
		$client->fillField(Redshopb2bPage::$titleMenu, $menuName);
		$client->waitForElement(Redshopb2bPage::$selectButton, 30);
		$client->click(Redshopb2bPage::$selectButton);
		$client->see(Redshopb2bPage::$itemType);
		$client->switchToIFrame(Redshopb2bPage::$itemType);
		$client->waitForElement(".//*[@id='collapseTypes']/div[1]/div[1]/strong/a", 30);
		$client->click(".//*[@id='collapseTypes']/div[1]/div[1]/strong/a");
		$usePage = new ProductPage();
		$client->waitForElement($usePage->getXpathItem($menuTypeSpecific), 30);
		$client->scrollTo($usePage->getXpathItem($menuTypeSpecific));
		$client->click($usePage->getXpathItem($menuTypeSpecific));
		$client->seeInField(Redshopb2bPage::$typeMenuValue, $menuTypeSpecific);
		$client->scrollTo('Save');
		$client->click('Save');
		$client->waitForText(Redshopb2bPage::$moduleSaveSuccess, 30, Redshopb2bPage::$alertMessage);
	}

	/**
	 * @throws \Exception
	 * Client want to active paypal payment and enable the payment
	 */
	public function activatePaypalPayment()
	{
		$client = $this;
		$client->wantToTest('Activate the redCORE Payments');
		$client->amOnPage(Redshopb2bPage::$URLRedCORE);
		$client->waitForText(Redshopb2bPage::$redcoreConfig, 30);
		$client->click(['link' => Redshopb2bPage::$apiOption]);
		$client->waitForElement(Redshopb2bPage::$paymentEnableID, 30);
		$client->selectOptionInRadioField(Redshopb2bPage::$paymentEnable, 'Yes');
		$client->click(Redshopb2bPage::$saveButton);
		$client->waitForElement(Redshopb2bPage::$systemContainer, 30);
		$client->amOnPage(Redshopb2bPage::$URLPlugins);
		$client->searchForItem('redCORE paypal gateway plugin');
		$client->waitForElement(['link' => Redshopb2bPage::$redcorePlugins], 30);
		$client->wait(0.5);
		$client->checkAllResults();
		$client->click('Enable');
		$client->waitForText(Redshopb2bPage::$pluginEnableSuccess, 30, Redshopb2bPage::$systemContainer);
	}

	/**
	 * Function for add debug
	 * @throws \Exception
	 */
	public function debugSystem($debug = array())
	{

		$client = $this;
		$client->amOnPage('/administrator/index.php?option=com_config');
		$client->wantTo('I wait for Global Configuration title');
		$client->waitForText('Global Configuration', 30, array('css' => '.page-title'));
		$client->wantTo('I open the Server Tab');
		$client->waitForElement(".//*[@id='application-form']/div/div[2]/ul/li[2]/a", 30);
		$client->click(".//*[@id='application-form']/div/div[2]/ul/li[2]/a");
		if(isset($debug['system']))
		{
			$client->wantToTest('Client want to Debug System');
			$client->selectOptionInRadioField(Redshopb2bPage::$debugSystem,$debug['system']);
		}
		if(isset($debug['language']))
		{
			$client->wantTo('Client want to Debug language');
			$client->selectOptionInRadioField(Redshopb2bPage::$debugLanguage, $debug['language']);
		}

		$client->click('Save');
		$client->waitForText(Redshopb2bPage::$configurationSuccess, 30, Redshopb2bPage::$messageClass);
	}
}
