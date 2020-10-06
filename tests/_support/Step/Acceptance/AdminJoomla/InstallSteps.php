<?php
	/**
	 * @package     Aesir.E-Commerce
	 * @subpackage  Steps Class
	 * @copyright   Copyright (C) 2012 - 2018 Aesir. E-Commerce. All rights reserved.
	 * @license     GNU General Public License version 2 or later; see LICENSE.txt
	 */
	namespace Step\Acceptance\AdminJoomla;
	use \Page\Acceptance\AdminJoomla\InstallPage as InstallPage;
	class InstallSteps extends \Step\Acceptance\redshopb2b
	{
        /**
         * @param $path
         * @param $nameExtension
         * @throws \Exception
         */
		public function installFromUrl($path, $nameExtension)
		{
			$i = $this;
			$i->am('Administrator');
			$i->amOnPage(InstallPage::$URL);
			$i->waitForText(InstallPage::$installTitle, 60, InstallPage::$H1);
			
			$i->click(InstallPage::$installFolder);
			$i->comment('I enter the path');
			$path = $i->getConfig($path) . $nameExtension;
			$i->comment($path);
			$i->amOnPage(InstallPage::$URL);
			$i->waitForText(InstallPage::$installTitle, '30', InstallPage::$H1);
			$i->click(InstallPage::$installUrl);
			$i->fillField(InstallPage::$urlField, $path);
			$i->click(InstallPage::$buttonUrl);
			$i->waitForText(InstallPage::$messageInstallComponentSuccess, 120, InstallPage::$message);
		}

		/**
		 * @param $configurationName
		 * @throws \Exception
		 */
		public function install($configurationName)
		{
			$I = $this;
			$username = $I->getConfig($configurationName['username']);
			$I->wantToTest($username);
			$password = $I->getConfig($configurationName['password']);
			$I->wantToTest($password);
			$I->doAdministratorLogin(null, null, false);
			$I->disableStatistics();
			$I->wantTo('Install Aesir E-Commerce Extension');
			$I->installFromUrl('redshopb packages url', 'redshopb.zip');

			if ($I->getConfig($configurationName['install demo data']) == 'Yes')
			{
				$I->click(InstallPage::$installDemoButton);
				$I->waitForText(InstallPage::$messageInstallDataSuccess, 10, InstallPage::$message);
			}

			$I->comment('I install com_rsbmedia');
			$I->installExtensionFromUrl($I->getConfig($configurationName['redshopb packages url']) . 'rsbmedia.zip');

			$I->comment('I install tpl_wrightB2B.zip');
			$I->installExtensionFromUrl($I->getConfig($configurationName['redshopb data url']) . 'tpl_wrightB2B.zip');
		}

		/**
		 * @param $webservice
		 * @throws \Exception
		 */
		public function activateWebservicesAndTranslations($webserviceName)
		{
			$I = $this;
			$I->comment('I enable basic authentication');
			$I->amOnPage(InstallPage::$URLRedCORE);
			$I->waitForText(InstallPage::$redcoreConfig, 30, InstallPage::$productNameH1);
			$I->click(InstallPage::$frontendComponentsModulesOptions);
			$I->waitForElementVisible(InstallPage::$includeRedcoreCssAndJsId);
			$I->selectOptionInRadioField(InstallPage::$includeRedcoreCssAndJsLbl, 'Yes');
			$I->click(InstallPage::$translationOptions);
			$I->waitForElementVisible(InstallPage::$enableTranslationsId);
			$I->selectOptionInRadioField(InstallPage::$enableTranslationsLbl, 'Yes');
			$I->click(InstallPage::$webserviceOptions);
			$I->waitForElementVisible(InstallPage::$enableWebserviceId);
			$I->executeJS(InstallPage::$enableWebserviceScript);
			$I->selectOptionInRadioField(InstallPage::$enableWebserviceLbl, 'Yes');
			$I->executeJS(InstallPage::$JavaScript);
			$I->selectOptionInChosen(InstallPage::$checkUserPermissionAgainstLbl, InstallPage::$checkUserPermissionAgainstOption);
			$I->selectOptionInRadioField(InstallPage::$enableSoapServerLbl, 'Yes');
			$I->click(InstallPage::$oauth2ServerOptions);
			$I->waitForText(InstallPage::$enableOauth2Server, 10);
			$I->selectOptionInRadioField(InstallPage::$enableOauth2Server, 'Yes');
			$I->executeJS(InstallPage::$enableOauth2ServerScript);
			$I->click(InstallPage::$saveButton);
			$I->waitForText(InstallPage::$saveSuccessMessage, 30, InstallPage::$message);
			$I->click(InstallPage::$closeButton);
			$I->amOnPage(InstallPage::$URLWebservice);
			$I->waitForText(InstallPage::$webserviceManager, 30, InstallPage::$productNameH1);
			$I->click(InstallPage::$notInstallWebservice);
			$I->click(InstallPage::$installWebservice);
			$I->waitForElement(InstallPage::$oauthClients, 30);
			$I->fillField(InstallPage::$searchWebservice, $webserviceName);
			$I->click(InstallPage::$iconSearch);
			$I->waitForElement(InstallPage::$oauthClients, 30);
		}



	}