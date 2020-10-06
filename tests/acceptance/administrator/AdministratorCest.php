<?php
/**
 * @package     Aesir-ec
 * @subpackage  Step Address
 * @copyright   Copyright (C) 2016 - 2019 Aesir-ec. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 **/

use Step\Acceptance\AdministratorSteps as AdministratorSteps;
use Step\Acceptance\Configuration\SettingsRole as SettingsRoleStep;

class AdministratorCest
{
	/**
	 * @var \Faker\Generator
	 */
	protected $faker;

	/**
	 * @var array
	 * @since 2.4.0
	 */
	protected $configureGlobal;

	/**
	 * @var array
	 * @since 2.4.0
	 */
	protected $settingShop;

	/**
	 * @var array
	 */
	protected $settingCartCheckout;

	/**
	 * @var array
	 * @since 2.4.0
	 */
	protected $settingImage;

	/**
	 * @var array
	 * @since 2.4.0
	 */
	protected $breadcrumbs;

	/**
	 * @var array
	 * @since 2.4.0
	 */
	protected $productSearch;

	/**
	 * @var array
	 * @since 2.4.0
	 */
	protected  $settingPage;

	/**
	 * @var array
	 * @since 2.4.0
	 */
	protected $vatSetting;

	/**
	 * @var array
	 * @since 2.4.0
	 */
	protected $settingOrder;

	/**
	 * @var array
	 * @since 2.4.0
	 */
	protected $settingNotification;

	/**
	 * @var array
	 * @since 2.5.1
	 */
	protected $settingsRole;

	/**
	 * AdministratorCest constructor.
	 * @since 2.4.0
	 */
	public function __construct()
	{
		$this->faker = Faker\Factory::create();
		
		/**
		 * configuration Global
		 */
		$this->configureGlobal =
			[
				'country' => 'Denmark',
				'bootstrap' => 'Bootstrap 3',
				'availableCountry' => 'All countries',
				'allowCountryRegister' => 'No',
				'addressRequired' => 'Yes',
				'registerFlow' => 'Activated after registration',
				'activationEmail' => 'Generic mail template',
				'encryptionKey' => 'redshoppb',
				'setWebservices' => 'Disabled',
				'userPermission' => 'No',
				'processProductDescription' => 'No',
				'warningLogout' => 'No',
				'richSnippet' => 'No',
				'billingPhone' => 'No',
				'shippingPhone' => 'No'
			];
		
		/**
		 * setting shop
		 */
		$this->settingShop =
			[
				'showCategoryProduct' => 'No',
				'ajaxCategory' => 'No',
				'dayProduct' => 16,
				'showProductPrintOption' => 'No',
				'compareUsingOption' => 'End customers (level3+)',
				'showShopAs' => 'Categories',
				'defaultLayout' => 'List',
				'defaultAccessory' => 'Checkbox Input',
				'showInlineCategory' => 'Yes',
				'showShopCollection' => 'Yes',
			];
		//setting cart checkout
		$this->settingCartCheckout =
			[
				'addToCart' => 'Modal',
				'cartBy' => 'By Quantity',
				'showImageInCart' => 'No',
				'showTaxInCart' => 'Yes',
				'checkoutRegister' => 'Registration Required',
				'guestUserDefault' => "guest",
				'checkoutMode' => 'Default',
				'showImageProductCheckout' => 'No',
				'showStockPresent' => 'Semaphore',
				'enableShipping' => 'No',
				'timeShipping' => 'Hours',
				'clearCartBeforeAddFavourite' => 'Yes',
				'redirectAfterAdd' => 'No',
			];

		//image setting
		$this->settingImage =
			[
				'storeMaxWidth' => 100,
				'storeMaxHeight' => 10,
				'storeOptimize' => 'Yes',
				'imageOptimization' => "Lazy optimization",
				'thumbnailWidth' => 144,
				'thumbnailHeight' => 144,
				'gridImageWidth' => 144,
				'gridImageHeight' => 144,
				'productImageWidth' => 256,
				'productImageHeight' => 256,
				'categoryImageWidth' => 72,
				'categoryImageHeight' => 72,
				'manufactureImageWidth' => 400,
				'manufactureImageHeight' => 400,
			];

		//setting breadcrumbs
		$this->breadcrumbs =
			[
				'showBreadcrumbs' => 'Yes',
				'impersonation' => 'Display in main breadcrumbs',
				'youAreHere' => 'Yes',
				'showHome' => 'Yes',
				'textForHomeEntry' => "",
				'textSeparator' => "",
			];

		//setting  product search
		$this->productSearch =
			[
				'search' => " Exact and partial",
				'synonymsSupport' => 'No',
				'enableStemmer' => 'No',
				'stemmer' => "Snowball",
				'stopIfFound' => 'Yes',
			];
		/**
		 * setup for setting page
		 */
		$this->settingPage =
			[
				'noPage' => 'No',
				'categoryPerPage' => 12,
				'categoryProductPerPage' => 12,
				'departmentPerPage' => 12,
				'numberOfColumn' => 'Show two column',
				'numberOfCategory' => 13
			];

		//setting vat
		$this->vatSetting =
			[
				'defaultCurrency' => "Euro",
				'currencySeparator' => ",",
				'showPrice' => 'Yes',
				'outletProduct' => 'No',
				'lowestProduct' => 'No',
				'offSystem' => 'Yes',
				'vat' => 'Vendor',
				'calculation' => 'Payment',
				'useTax' => 'No'
			];

		//setting order
		$this->settingOrder =
			[
				'company' => "Parent company",
				'impersonatedCompanies' => "Yes",
				'impersonatedUser' => 'Yes',
				'collectOrder' => 'No',
				'orderExpedition' => 'No',
				'orderReturn' => 'Yes',
				'placeOrder' => 'Yes'
			];

		//setting notification
		$this->settingNotification =
			[
				'notifyUserRegister' => "No",
				'userToNotify' => "",
				'mailForm' => "",
				'syncNotification' => "",
				'notifyAdmin' => "Yes",
				'notifyHead' => "Yes",
				'notifySale' => "Yes",
				'notifyAuthor' => "Yes",
				'notifyPurchaser' => "Yes"
			];

		//setting role
		$this->settingsRole =
			[
				'administrator'                             => 'No',
				'headOfDepartments'                         => 'No',
				'salesPersons'                              => 'No',
				'purchasers'                                => 'No',
				'employeesWithLogin'                        => 'No',
				'employees'                                 => 'No',
				'allCollectionsToAdministrator'             => 'Yes',
				'allCollectionsToHeadOfDepartments'         => 'No',
				'allCollectionsToSalesPersons'              => 'Yes',
				'allCollectionsToPurchasers'                => 'Yes',
				'allCollectionsToEmployeesWithLogin'        => 'No',
				'allCollectionsToEmployees'                 => 'No'
			];
	}

	/**
	 * @param AcceptanceTester $I
	 * @throws Exception
	 */
	public function _before(AcceptanceTester $I)
	{
		$I->doAdministratorLogin();
	}
	
	/**
	 * @param AdministratorSteps $client
	 * @param $scenario
	 * @throws Exception
	 */
	public function settingAdminPage(AdministratorSteps $client, $scenario)
	{
		$client->wantTo('Test Country creation in Administrator');
		$client = new AdministratorSteps($scenario);

		$client->wantTo('setting Shop , just with default install');
		$client->settingShop($this->settingShop);

		$client->wantTo('setting Image show up , just with default install');
		$client->settingImage($this->settingImage);

		$client->wantTo('setting Breadcrumbs , just with default install');
		$client->breadcrumbs($this->breadcrumbs);

		$client->wantTo('setting Product Search , just with default install');
		$client->productSearch($this->productSearch);

		$client->wantTo('setting Product Search , just with default install');
		$client->settingPage($this->settingPage);

		$client->wantTo('setting Order , just with default install');
		$client->settingOrder($this->settingOrder);

		$client->wantTo('setting Notification , just with default install');
		$client->settingNotification($this->settingNotification);

		$client->wantTo('settings Role, just with default install');
		$client = new SettingsRoleStep($scenario);
		$client->settingsRole($this->settingsRole);
	}
}