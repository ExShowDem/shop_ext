<?php
/**
 * @package     Aesir.E-Commerce
 * @subpackage  Cept
 * @copyright   Copyright (C) 2012 - 2018 Aesir-E-Commerce. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
use Step\Acceptance\AdminJoomla\InstallSteps as InstallSteps;
use Step\Acceptance\AdministratorSteps as AdministratorSteps;
use Step\Bootstrap3\SetupFrontendFrameworkSteps as SetupFrontendFrameworkSteps;
use Step\Acceptance\redshopb2b as redshopb2b;
class InstallExtensionCest
{
	/**
	 * @var array
	 * @since 2.4.0
	 */
	protected $vatSetting;

	/**
	 * @var array
	 * @since 2.4.0
	 */
	protected $configureGlobal;

	/**
	 * @var
	 * @since 2.4.0
	 */
	protected $webserviceName;

	/**
	 * @var array
	 * @since 2.4.0
	 */
	protected $configurationName;

	/**
	 * @var string
	 * @since 2.4.0
	 */
	protected $nameTemplate;

	/**
	 * @var string
	 * @since 2.4.0
	 */
	protected $position;

	/**
	 * @var string
	 * @since 2.4.0
	 */
	protected $moduleName;

	public function __construct()
	{
		//setting vat
		$this->vatSetting =
			[
				'defaultCurrency' => "Euro",
				'currencySymbol'=> '€',
				'currencySeparator' => ",",
				'showPrice' => 'Yes',
				'outletProduct' => 'No',
				'lowestProduct' => 'No',
				'offSystem' => 'Yes',
				'vat' => 'Vendor',
				'calculation' => 'Payment',
				'useTax' => 'No'
			];

		/**
		 * configuration Global
		 */
		$this->configureGlobal =
			[
				'country' => 'Denmark',
				'bootstrap' => 'Bootstrap 2',
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

		$this->configurationName =
			[
				'username'              => 'username',
				'password'              => 'password',
				'install demo data'     => 'install demo data',
				'redshopb packages url' => 'redshopb packages url',
				'redshopb data url'     => 'redshopb data url'
			];

		$this->webserviceName = 'Aesir E-Commerce - Category Webservice';

		$this->nameTemplate                   = 'protostar - Default';
		$this->position                       = 'position-7';
		$this->moduleName                     = 'Aesir E-Commerce Status';

	}

	/**
	 * @param InstallSteps $I
	 * @throws Exception
	 */
	public function install(InstallSteps $I)
	{
		$I->wantTo('Install Aesir E-Commerce Extension');
		$I->install($this->configurationName);
	}
}
