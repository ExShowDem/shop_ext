<?php
namespace Step\Frontend;

use Step\Acceptance\redshopb2b;
use Page\Frontend\UserPage as UserPage;
class UserSteps extends redshopb2b
{
	/**
	 * @param array $user
	 *
	 * Create new user and check login at frontend . if user have role >= 5 will login success
	 *
	 * @throws \Exception
	 */
	public function createUserRole($user = array())
	{
		$I = $this;
		$I->amGoingTo('Navigate to Users page in frontend');
		$I->amOnPage(UserPage::$URLUsers);

		$I->comment($user['name']);
		$I->waitForElement(UserPage::$newButton, 30);
		$I->click(UserPage::$newButton);

		$I->comment('I am redirected to the form');
		$I->waitForElement(UserPage::$adminForm, 30);
		$I->waitForElementVisible(UserPage::$idRole, 30);
		$I->selectOptionInChosenjs(UserPage::$roleLabel, $user['role']);
		$I->fillField(UserPage::$name1, $user['name']);
		$I->fillField(UserPage::$loginNameId, $user['name']);
		$I->fillField(UserPage::$passwordId, $user['name']);
		$I->fillField(UserPage::$passwordConfirmId, $user['name']);

		if ($user['hasmail'] == 'Yes')
		{
			$I->selectOptionInRadioField(UserPage::$hasMailLabel, $user['hasmail']);
			$I->fillField(UserPage::$emailId, $user['email']);
			$I->selectOptionInRadioField(UserPage::$sendMailLabel, $user['sendMail']);

		}
		else
		{
			$I->selectOptionInRadioField(UserPage::$hasMailLabel, 'No');
		}
		
		$I->selectOptionInChosenjs(UserPage::$company,$user['company']);
		$I->fillField(UserPage::$addressName , $user['a_name']);
		$I->fillField(UserPage::$addressAddress, $user['a_address']);
		$I->fillField(UserPage::$addressSecondLine, $user['a_second']);
		$I->fillField(UserPage::$addressZipCode, $user['a_zip']);
		$I->fillField(UserPage::$addressCity, $user['a_city']);
		$I->fillField(UserPage::$addressPhone, $user['a_phone']);
		$I->fillField(UserPage::$addressPhoneUser, $user['a_phone']);
		$I->fillField(UserPage::$addressCellPhone, $user['a_cphone']);
		$I->selectOptionInChosenjs(UserPage::$addressCountry, $user['a_country']);
		$I->wait(1);
		$I->click(UserPage::$saveButton);
		$I->waitForElement(UserPage::$messageSuccessID, 10);
		$I->see(UserPage::$saveUserSuccess, UserPage::$messageSuccessID);
		$I->wait(0.5);
		$I->click(UserPage::$closeButton);
		$I->searchForItemInFrontend($user['name'], ['search field locator id' => UserPage::$searchUser]);
		$I->click(['link' => $user['name']]);
		$I->waitForElement(UserPage::$adminForm);
		$I->seeInField(UserPage::$name1, $user['name']);
		$I->seeInField(UserPage::$addressName, $user['a_name']);
		$I->doFrontendLogout();
		if ($user['role'] != '06 :: Employee')
		{
			$I->doFrontEndLogin($user['name'], $user['name']);
		}
		else
		{
			$I->fillField(UserPage::$usernameId, $user['name']);
			$I->fillField(UserPage::$passwordId, $user['name']);
			$I->click(UserPage::$loginButton);
			$I->dontSeeInField(UserPage::$usernameId, $user['name']);
		}
	}
	
	/**
	 * @param $name
	 * @param array $user
	 * @throws \Exception
	 */
	public function editUser($name , $user = array())
	{
		$I = $this;
		$I->amGoingTo('Navigate to Users page in frontend');
		$I->amOnPage(UserPage::$URLUsers);
		$I->wantTo('Change name and role of this user');
		$I->waitForElement(UserPage::$newButton, 30);
		$I->searchForItemInFrontend($name, ['search field locator id' => UserPage::$searchUser]);
		$I->wait(0.5);
		$I->waitForElement(['link' => $name], 30);
		$I->click(['link' => $name]);
		$I->wait(1);
		$I->waitForJS('return !!window.jQuery && window.jQuery.active == 0;', 30);
		$I->waitForElement(UserPage::$adminForm, 30);
		$I->seeInField(UserPage::$name1, $name);
		$I->fillField(UserPage::$name1, $user['name']);
		$I->fillField(UserPage::$loginNameId, 30);
		$I->wait(1);
		$I->fillField(UserPage::$loginNameId, $user['name']);
		$I->waitForElement(UserPage::$passwordId, 30);
		$I->fillField(UserPage::$passwordId, $user['name']);
		$I->waitForElement(UserPage::$passwordConfirmId, 30);
		$I->fillField(UserPage::$passwordConfirmId, $user['name']);
		$I->wait(1);
		$I->click(UserPage::$saveButton);
		$I->click(UserPage::$saveCloseButton);
		$I->wait(1);
		$I->waitForJS('return !!window.jQuery && window.jQuery.active == 0;', 30);
		$I->searchForItemInFrontend($user['name'], ['search field locator id' => UserPage::$searchUser]);
		$I->waitForElement(['link' => $user['name']], 30);
		$I->dontSeeElement(['link' => $name]);

		$I->doFrontendLogout();
		if ($user['role'] != '06 :: Employee')
		{
			$I->doFrontEndLogin($user['name'], $user['name']);
		}
		else
		{
			$I->fillField(UserPage::$usernameId, $user['name']);
			$I->fillField(UserPage::$passwordId, $user['name']);
			$I->click(UserPage::$loginButton);
			$I->waitForText(UserPage::$messageUserPassNotMatch, 30);
		}
	}
	
	/**
	 * @param $name deleteUser user by name
	 * @throws \Exception
	 */
	public function deleteUser($name)
	{
		$I = $this;
		$I->amGoingTo('Navigate to Users page in frontend');
		$I->amOnPage(UserPage::$URLUsers);
		$I->wantTo('Change name and role of this user');
		$I->waitForElement(UserPage::$newButton, 30);
		$I->searchForItemInFrontend($name, ['search field locator id' => UserPage::$searchUser]);
		$I->waitForElement(['link' => $name], 30);
		$I->checkAllResults();
		$I->click(UserPage::$deleteButton);
		$I->searchForItemInFrontend($name, ['search field locator id' => UserPage::$searchUser]);
		$I->dontSeeElement(['link' => $name]);
	}
	
	/**
	 * @param array $user
	 * @throws \Exception
	 */
	public function createMissing($user = array())
	{
		$I = $this;
		$I->amGoingTo('Navigate to Users page in frontend');
		$I->amOnPage(UserPage::$URLUsers);

		$I->comment($user['name']);
		$I->waitForElement(UserPage::$newButton, 30);
		$I->click(UserPage::$newButton);

		$I->comment('I am redirected to the form');
		$I->waitForElement(UserPage::$adminForm, 30);

		$I->comment('Create with missing company');
		$I->waitForElementVisible(UserPage::$idRole, 30);
		$I->wait(1);
		$I->click(UserPage::$saveButton);
		$I->waitForJS('return !!window.jQuery && window.jQuery.active == 0;', 60);
		$I->waitForElement(UserPage::$messageSuccessID);
		$I->waitForText(UserPage::$missingCompany, 30 , UserPage::$messageSuccessID);

		$I->comment('Create with missing role');
		$I->waitForElementVisible(UserPage::$idRole, 30);
		$I->fillField(UserPage::$name1, $user['name']);
		$I->fillField(UserPage::$loginNameId, $user['name']);
		$I->fillField(UserPage::$passwordId, $user['name']);
		$I->fillField(UserPage::$passwordConfirmId, $user['name']);

		if ($user['hasmail'] == 'Yes')
		{
			$I->selectOptionInRadioField(UserPage::$hasMailLabel, $user['hasmail']);
			$I->fillField(UserPage::$emailId, $user['email']);
			$I->selectOptionInRadioField(UserPage::$sendMailLabel, $user['sendMail']);
		}else
		{
			$I->selectOptionInRadioField(UserPage::$hasMailLabel, 'No');
		}

		$I->click(UserPage::$saveButton);
		$I->waitForJS('return !!window.jQuery && window.jQuery.active == 0;', 60);
		$I->waitForElement(UserPage::$messageSuccessID, 30);
		$I->waitForText(UserPage::$missingRole, 30 , UserPage::$messageSuccessID);

		$I->comment('Create with missing Name');
		$I->selectOptionInChosenjs(UserPage::$roleLabel, $user['role']);
		$I->fillField(UserPage::$name1,"");
		$I->click(UserPage::$saveButton);
		$I->waitForElement(UserPage::$messageSuccessID, 30);
		$I->waitForText(UserPage::$missingName, 30 , UserPage::$messageSuccessID);
		$I->comment('Missing Email');
		$I->fillField(UserPage::$name1, $user['name']);
		$I->fillField(UserPage::$emailId, "");
		$I->wait(1);
		$I->click(UserPage::$saveButton);
		$I->waitForJS('return !!window.jQuery && window.jQuery.active == 0;', 60);
		$I->waitForElement(UserPage::$messageSuccessID, 30);
		$I->waitForText(UserPage::$missingEmail, 30 , UserPage::$messageSuccessID);
	}

	/**
	 * @param $user
	 * @throws \Exception
	 * @since 2.8.0
	 */
	public function editUserWithDepartment($user)
	{
		$I = $this;
		$I->amGoingTo('Navigate to Users page in frontend');
		$I->amOnPage(UserPage::$URLUsers);
		$I->wantTo('Change name and role of this user');
		$I->waitForElementVisible(UserPage::$newButton, 30);
		$I->searchForItemInFrontend($user['username'], ['search field locator id' => UserPage::$searchUser]);
		$I->waitForElementVisible(['link' => $user['username']], 30);
		$I->click(['link' => $user['username']]);
		$I->waitForElementVisible(UserPage::$adminForm, 30);
		$I->selectOptionInChosenjs(UserPage::$department, $user['department']);
		$I->waitForElementVisible(UserPage::$passwordId, 30);
		$I->fillField(UserPage::$passwordId, $user['username']);
		$I->waitForElementVisible(UserPage::$passwordConfirmId, 30);
		$I->fillField(UserPage::$passwordConfirmId, $user['username']);
		$I->waitForElementVisible(UserPage::$saveCloseButton, 30);
		$I->click(UserPage::$saveCloseButton);
		$I->waitForText(UserPage::$userSaveSuccess, 30, UserPage::$alertMessage);
	}
}