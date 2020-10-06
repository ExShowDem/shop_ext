<?php
/**
 * @package     Aesir-e-commerce
 * @subpackage  Cest
 * @copyright   Copyright (C) 2014 - 2019 Aesir-e-commerce. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Step\Acceptance\AdminJoomla\InstallSteps as InstallSteps;
use Step\Acceptance\AdministratorSteps as AdministratorSteps;
use Step\Bootstrap3\SetupFrontendFrameworkSteps as SetupFrontendFrameworkSteps;
use Step\Acceptance\redshopb2b as redshopb2b;
class setupFrontendBootstrap2Cest
{
	/**
	 * @var array
	 * @since 2.5.0
	 */
	protected $vatSetting;

	/**
	 * @var array
	 * @since 2.5.0
	 */
	protected $configureGlobal;

	/**
	 * @var
	 * @since 2.5.0
	 */
	protected $webserviceName;

	/**
	 * @var array
	 * @since 2.5.0
	 */
	protected $configurationName;

	/**
	 * @var string
	 * @since 2.5.0
	 */
	protected $nameTemplate;

	/**
	 * @var string
	 * @since 2.5.0
	 */
	protected $position;

	/**
	 * @var string
	 * @since 2.5.0
	 */
	protected $moduleName;

	public function __construct()
	{
		/**
		 * setting vat
		 * @since 2.5.0
		 */
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
		 * @since 2.5.0
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

		$this->webserviceName = 'Aesir E-Commerce - Category Webservice';

		$this->nameTemplate                   = 'protostar - Default';
		$this->position                       = 'position-7';
		$this->moduleName                     = 'Aesir E-Commerce Status';
	}

	/**
	 * @param redshopb2b            $I
	 * @param \Codeception\Scenario $scenario
	 * @throws Exception
	 * @since 2.5.0
	 */
	public function configure(redshopb2b $I, \Codeception\Scenario $scenario)
	{
		$I->wantToTest('Setup VAT and Bootstrap 2');
		$I->doAdministratorLogin();
		$I = new AdministratorSteps($scenario);
		$I->wantTo('Setting VAT  , just with default install');
		$I->vatSetting($this->vatSetting);

		$I->wantTo('Setting Global , just with default install');
		$I->configureGlobal($this->configureGlobal);
	}

	/**
	 * @param redshopb2b            $I
	 * @param \Codeception\Scenario $scenario
	 * @throws Exception
	 * @since 2.5.0
	 */
	public function activateWebservicesAndTranslations(redshopb2b $I, \Codeception\Scenario $scenario)
	{
		$I->am('Administrator');
		$I = new InstallSteps($scenario);
		$I->doAdministratorLogin();
		$I->wantTo('Activate the default webservices available in redCORE');
		$I->activateWebservicesAndTranslations($this->webserviceName);
	}

	/**
	 * @param redshopb2b            $I
	 * @param \Codeception\Scenario $scenario
	 * @throws Exception
	 * @since 2.5.0
	 */
	public function displayStatusModuleTemplate(redshopb2b $I, \Codeception\Scenario $scenario)
	{
		$I->am('Administrator');
		$I->wantTo('Display in frontend Aesir E-Commerce Status Module');
		$I->doAdministratorLogin();
		$I = new SetupFrontendFrameworkSteps($scenario);
		$I->publishModule($this->moduleName);
		$I->displayModuleOnAllPages($this->moduleName);
		$I->setModulePosition($this->moduleName, $this->position);
		$I->disableTheFloatingTemplateToolbars();
	}
}