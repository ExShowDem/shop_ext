<?php
namespace Step\Acceptance;

class FrontendActions extends \AcceptanceTester
{
	protected $scenario;

	/**
	 * @Given I am logged into frontend as :username
	 */
	public function iAmLoggedInAsUser($username)
	{
		$I = $this;

		$user = RedshopbCreate::$placeholders[$username];

		$I->am($user);
		$I->doFrontendLogin($user, $user);
	}

	/**
	 * @Given I am logged into frontend as administrator
	 */
	public function iAmLoggedInAsAdministrator()
	{
		$I = $this;

		$I->am('Administrator');
		$I->doFrontendLogin();
	}

	/**
	 * @When I log into frontend as user :username
	 */
	public function iLogInAsUser($username)
	{
		$I = $this;

		$user = RedshopbCreate::$placeholders[$username];

		$I->doFrontendLogin($user, $user);
	}

	/**
	 * @When I log into frontend as administrator
	 */
	public function iLogInAsAdministrator()
	{
		$I = $this;

		$I->doFrontendLogin();
	}

	/**
	 * @When I log out of frontend
	 */
	public function iLogOutOfFrontend()
	{
		$I = $this;
		$I->doFrontendLogout();
	}
}
