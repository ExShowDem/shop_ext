<?php
/**
 * @package     Aesir-ec
 * @subpackage  Step Address
 * @copyright   Copyright (C) 2016 - 2019 Aesir-ec. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 **/

namespace Step\Acceptance\Configuration;

use Page\Acceptance\Configuration\SettingsRole as SettingsRolePage;
use Step\Acceptance\AdministratorSteps as AdministratorSteps;

class SettingsRole extends AdministratorSteps
{
	/**
	 * @param array $settingsRole
	 *
	 * @throws \Exception
	 * @since 2.5.1
	 */
	public function settingsRole($settingsRole = array())
	{
		$I = $this;
		$I->amOnPage(SettingsRolePage::$configurationUrl);
		$I->waitForElementVisible(SettingsRolePage::$roleSettingsTab, 30);
		$I->click(SettingsRolePage::$roleSettingsTab);

		$I->comment('Setup ignore collections enforcement from company for Administrator');
		if(isset($settingsRole['administrator']))
		{
			$I->selectOptionInRadioField(SettingsRolePage::$labelAdministrators, $settingsRole['administrator']);
		}

		$I->comment('Setup ignore collections enforcement from company for Head of departments');
		if(isset($settingsRole['headOfDepartments']))
		{
			$I->selectOptionInRadioField(SettingsRolePage::$labelHeadOfDepartments, $settingsRole['headOfDepartments']);
		}

		$I->comment('Setup ignore collections enforcement from company for Sales persons');
		if(isset($settingsRole['salesPersons']))
		{
			$I->selectOptionInRadioField(SettingsRolePage::$labelSalesPersons, $settingsRole['salesPersons']);
		}

		$I->comment('Setup ignore collections enforcement from company for Purchasers');
		if(isset($settingsRole['purchasers']))
		{
			$I->selectOptionInRadioField(SettingsRolePage::$labelPurchasers, $settingsRole['purchasers']);
		}

		$I->comment('Setup ignore collections enforcement from company for Employees with login');
		if(isset($settingsRole['employeesWithLogin']))
		{
			$I->selectOptionInRadioField(SettingsRolePage::$labelEmployeesWithLogin, $settingsRole['employeesWithLogin']);
		}

		$I->comment('Setup ignore collections enforcement from company for Employees');
		if(isset($settingsRole['employees']))
		{
			$I->selectOptionInRadioField(SettingsRolePage::$labelEmployees, $settingsRole['employees']);
		}

		$I->comment('Setup See all company collections when no department set for Administrator');
		if(isset($settingsRole['allCollectionsToAdministrator']))
		{
			$I->selectOptionInRadioField(SettingsRolePage::$labelAllCollectionsAdmin, $settingsRole['allCollectionsToAdministrator']);
		}

		$I->comment('Setup See all company collections when no department set for Head of departments');
		if(isset($settingsRole['allCollectionsToHeadOfDepartments']))
		{
			$I->selectOptionInRadioField(SettingsRolePage::$labelAllCollectionsHeadOfDepartments, $settingsRole['allCollectionsToHeadOfDepartments']);
		}

		$I->comment('Setup See all company collections when no department set for Sales persons');
		if(isset($settingsRole['allCollectionsToSalesPersons']))
		{
			$I->selectOptionInRadioField(SettingsRolePage::$labelAllCollectionsSalesPersons, $settingsRole['allCollectionsToSalesPersons']);
		}

		$I->comment('Setup See all company collections when no department set for Purchasers');
		if(isset($settingsRole['allCollectionsToPurchasers']))
		{
			$I->scrollTo(SettingsRolePage::$allCollectionsPurchasers);
			$I->selectOptionInRadioField(SettingsRolePage::$labelAllCollectionsPurchasers, $settingsRole['allCollectionsToPurchasers']);
		}

		$I->comment('Setup See all company collections when no department set for Employees with login');
		if(isset($settingsRole['allCollectionsToEmployeesWithLogin']))
		{
			$I->selectOptionInRadioField(SettingsRolePage::$labelAllCollectionsEmployeesWithLogin, $settingsRole['allCollectionsToEmployeesWithLogin']);
		}

		$I->comment('Setup See all company collections when no department set for Employees');
		if(isset($settingsRole['allCollectionsToEmployees']))
		{
			$I->selectOptionInRadioField(SettingsRolePage::$labelAllCollectionsEmployees, $settingsRole['allCollectionsToEmployees']);
		}

		$I->scrollUp();
		$I->waitForElementVisible(SettingsRolePage::$saveButton,30);
		$I->click(SettingsRolePage::$saveButton);
		$I->waitForElementVisible(SettingsRolePage::$systemContainer,30);
		$I->waitForText(SettingsRolePage::$saveItemSuccess, 30,SettingsRolePage::$systemContainer );
	}
}