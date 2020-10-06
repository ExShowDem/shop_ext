<?php
namespace Step\Acceptance;

class AdministratorActions extends \AcceptanceTester
{
	protected $scenario;

    /**
     * AdministratorActions constructor.
     * @param \AcceptanceTester $I
     */
	public function __construct(\AcceptanceTester $I) 
	{
		$this->scenario = $I->getScenario();
	}

	/**
	 * @Given I am logged in as a Joomla Administrator
	 */
	public function iAmLoggedInAsAJoomlaAdministrator()
	{
		$I = $this;

		$I->am('administrator');
		$I->doAdministratorLogin();
	}

	/**
	 * @When I publish the module :module
	 */
	public function iPublishTheModule($module)
	{
		$I = $this;

		$I->publishModule($module);
	}

	/**
	 * @When I display the module :module
	 */
	public function iDisplayTheModule($module)
	{
		$I = $this;

		$I->displayModuleOnAllPages($module);
	}

	/**
	 * @When I set the module position of :module to :position
	 */
	public function iSetTheModulePositionOfTo($module, $position)
	{
		$I = $this;

		$redshopb2b = new \Step\Acceptance\redshopb2b($this->scenario);

		// JoomlaBrowser's setModulePosition doesn't account for the save button being blocked by the admin navbar on larger module pages, so we do it manually
		$I->amOnPage('administrator/index.php?option=com_modules');
		$I->searchForItem($module);
		$I->click(['link' => $module]);
		$I->waitForText("Modules: $module", 30, ['css' => 'h1.page-title']);
		$I->click(['link' => 'Module']);
		$I->waitForElement(['id' => 'general'], 30);
		$redshopb2b->selectOptionInChosen('Position', $position);
		$I->executeJS("window.scrollTo(0, 0);");
		$I->click(['xpath' => "//div[@id='toolbar-apply']/button"]);
		$I->waitForText('Module successfully saved', 30, ['id' => 'system-message-container']);
	}
}
