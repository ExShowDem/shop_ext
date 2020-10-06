<?php
/**
 * @package     Aesir-e-commerce
 * @subpackage  Cest
 * @copyright   Copyright (C) 2014 - 2018 Aesir-e-commerce. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Step\Bootstrap3;

use Page\Bootstrap3\SetupFrontendFrameworkPage as SetupFrontendFrameworkPage;

class SetupFrontendFrameworkSteps extends \Step\Acceptance\redshopb2b
{
	/**
	 * @since 2.1.0
	 * @return void
	 * @throws \Exception
	 */
	public function configure()
	{
		$I=$this;
		$I->amOnPage(SetupFrontendFrameworkPage::$configurationUrl);
		$I->waitForElement(SetupFrontendFrameworkPage::$linkB2B, 30);
		$I->selectOptionInChosenjs(SetupFrontendFrameworkPage::$labelBootstrap, 'Bootstrap 3');
		$I->click(SetupFrontendFrameworkPage::$saveButton);
		try
		{
			$I->waitForText(SetupFrontendFrameworkPage::$saveItemSuccess, 10, SetupFrontendFrameworkPage::$systemContainer);
		}
		catch (\Exception $e)
		{
			$I->waitForText(SetupFrontendFrameworkPage::$saveItemSuccess, 10, SetupFrontendFrameworkPage::$systemContainer);
		}
		$I->waitForText(SetupFrontendFrameworkPage::$saveItemSuccess, 10, SetupFrontendFrameworkPage::$systemContainer);
		$I->click(SetupFrontendFrameworkPage::$closeButton);
	}

	/**
	 * @param $nameTemplate
	 * @since 2.1.0
	 * @return void
	 * @throws \Exception
	 */
	public function makeWrightTemplateDefault($nameTemplate)
	{
		$I = $this;
		$I->amOnPage(SetupFrontendFrameworkPage::$templateUrl);
		$I->waitForText(SetupFrontendFrameworkPage::$template, 30, SetupFrontendFrameworkPage::$productNameH1);
		$I->searchForItem($nameTemplate);
		$I->click(SetupFrontendFrameworkPage::$setDefault);
		$I->waitForText(SetupFrontendFrameworkPage::$messageDefault, 30, SetupFrontendFrameworkPage::$systemContainer);
	}

	/**
	 * @throws \Exception
	 */
	public function disableTheFloatingTemplateToolbars()
	{
		$I = $this;
		$I->comment('disable the floating template toolbars');
		$I->amOnPage(SetupFrontendFrameworkPage::$templateUrl);
		$I->waitForText(SetupFrontendFrameworkPage::$template, 30, SetupFrontendFrameworkPage::$productNameH1);
		$I->selectOptionInChosen(SetupFrontendFrameworkPage::$administratorId, 'Administrator');
		$I->waitForText(SetupFrontendFrameworkPage::$templateAdministrator, 60, SetupFrontendFrameworkPage::$productNameH1);
		$I->click(SetupFrontendFrameworkPage::$isisDefault);
		$I->waitForText(SetupFrontendFrameworkPage::$templateEditStyle, 60, SetupFrontendFrameworkPage::$productNameH1);
		$I->click(SetupFrontendFrameworkPage::$advanced);
		$I->waitForElement(SetupFrontendFrameworkPage::$statusModulePosition, 60);
		$I->executeJS(SetupFrontendFrameworkPage::$executeJS);
		$I->selectOptionInChosen(SetupFrontendFrameworkPage::$statusModulePositionLbl, 'Top');
		$I->selectOptionInRadioField(SetupFrontendFrameworkPage::$pinnedToolbar, 'No');
		$I->click(SetupFrontendFrameworkPage::$saveCloseButton);
		$I->waitForText(SetupFrontendFrameworkPage::$saveSuccessMessage, 60, SetupFrontendFrameworkPage::$systemContainer);
	}

	/**
	 * @param $moduleName
	 * @param $module
	 * @param $position
	 * @param $menu
	 * @since 2.1.0
	 * @return void
	 * @throws \Exception
	 */
	public function switchModulePosition($moduleName, $module, $position, $menu)
	{
		$I = $this;
		$I->amOnPage(SetupFrontendFrameworkPage::$moduleSiteUrl);
		$I->searchForItem($moduleName);
		$I->waitForElement(['link' => $moduleName], 30);
		$I->click(['link' => $moduleName]);
		$I->wait(1);
		$I->click(['link' => $module]);
		$I->waitForElement(SetupFrontendFrameworkPage::$moduleId, 30);
		$I->selectOptionInChosen('Position', $position);
		$I->waitForElement(['link' => $menu], 30);
		$I->click(['link' => $menu]);
		$I->waitForElement(SetupFrontendFrameworkPage::$menuAssignmentLbl, 30);
		$I->click(SetupFrontendFrameworkPage::$menuAssignmentId);
		$I->wait(0.5);
		$I->click(SetupFrontendFrameworkPage::$onAllPage);
		$I->wait(0.5);
		$I->click(SetupFrontendFrameworkPage::$moduleSave);
		$I->wait(0.5);
		$I->waitForText(SetupFrontendFrameworkPage::$moduleSaveSuccessMessage, 30, SetupFrontendFrameworkPage::$systemContainer);
		$I->amOnPage(SetupFrontendFrameworkPage::$indexURL);
		$user = new SetupFrontendFrameworkPage();
		$I->seeElement($user->xPathATag($moduleName));
	}
}