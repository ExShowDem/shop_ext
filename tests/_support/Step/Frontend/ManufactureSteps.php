<?php
/**
 * @package  AcceptanceTester
 *
 * @since    2.2
 *
 * @link     http://codeception.com/docs/07-AdvancedUsage#StepObjects
 */

namespace Step\Frontend;
use Page\Frontend\ManufacturePage as ManufacturePage;

class ManufactureSteps extends \Step\Acceptance\redshopb2b
{
	/**
	 * @param $title
	 * @param $parent
	 * @param $status
	 * @param $featured
	 * @param $category
	 * @param $function
	 * @throws \Exception
	 */
	public function create($title, $parent, $status, $featured, $category,$function)
	{
		$I = $this;
		$I->amOnPage(ManufacturePage::$URL);
		$I->waitForElement(ManufacturePage::$newButton, 30);
		$I->click(ManufacturePage::$newButton);
		$I->waitForElement(ManufacturePage::$nameID,30);
		$I->fillField(ManufacturePage::$nameID, $title);

		if($parent != null)
		{
			$I->selectOptionInChosen(ManufacturePage::$parentManufacturers,$parent);
		}
		$I->selectOptionInChosenjs(ManufacturePage::$status,$status);
		$I->selectOptionInChosenjs(ManufacturePage::$featured,$featured);
		$I->wait(1);
		switch ($function)
		{
			case 'save':
				$I->click(ManufacturePage::$saveButton);
				$I->waitForJS('return !!window.jQuery && window.jQuery.active == 0;', 30);
				$I->waitForElement(ManufacturePage::$nameID,30);
				$I->seeInField(ManufacturePage::$nameID,$title);
				$I->click(ManufacturePage::$saveCloseButton);
				break;
			case 'save&close':
				$I->click(ManufacturePage::$saveCloseButton);
				$I->waitForJS('return !!window.jQuery && window.jQuery.active == 0;', 30);
				break;
			case 'save&new':
				$I->click(ManufacturePage::$saveNewButton);
				$I->waitForJS('return !!window.jQuery && window.jQuery.active == 0;', 30);
				$I->waitForElement(ManufacturePage::$nameID,30);
				$I->wait(0.5);
				$I->seeInField(ManufacturePage::$nameID,'');
				$I->click(ManufacturePage::$cancelButton);
				break;
		}
		$I->searchForItemInFrontend($title, ['search field locator id' => ManufacturePage::$manufacturersSearch]);
		$I->waitForElement(['link' => $title], 30);
	}

	/**
	 * @param $name
	 * @param $nameEdit
	 * @throws \Exception
	 */
	public function edit($name,$nameEdit)
	{
		$I = $this;
		$I->amOnPage(ManufacturePage::$URL);
		$I->wait(0.5);
		$I->searchForItemInFrontend($name, ['search field locator id' => ManufacturePage::$manufacturersSearch]);
		$I->waitForElement(['link' => $name], 30);
		$I->checkAllResults();
		$I->click(ManufacturePage::$editButton);
		$I->wait(1);
		$I->waitForJS('return !!window.jQuery && window.jQuery.active == 0;', 30);
		$I->waitForElement(ManufacturePage::$adminForm, 30);
		$I->waitForElement(ManufacturePage::$nameID,30);
		$I->fillField(ManufacturePage::$nameID,$nameEdit);
		$I->waitForElement(ManufacturePage::$statusButton, 30);
		$I->click(ManufacturePage::$statusButton);
		$I->wait(1);
		$I->waitForElement(ManufacturePage::$saveCloseButton, 30);
		$I->click(ManufacturePage::$saveCloseButton);
		$I->searchForItemInFrontend($nameEdit, ['search field locator id' => ManufacturePage::$manufacturersSearch]);
		$I->waitForText($nameEdit, 30);
		$I->dontSeeElement(['link'=>$name]);
	}

	/**
	 * @param $name
	 * @throws \Exception
	 */
	public function delete($name)
	{
		$I = $this;
		$I->amOnPage(ManufacturePage::$URL);
		$I->searchForItemInFrontend($name, ['search field locator id' => ManufacturePage::$manufacturersSearch]);
		$I->see($name);
		$I->checkAllResults();
		$I->click(ManufacturePage::$deleteButton);
		$I->searchForItemInFrontend($name, ['search field locator id' => ManufacturePage::$manufacturersSearch]);
		$I->dontSeeElement(['link'=>$name]);
	}
}