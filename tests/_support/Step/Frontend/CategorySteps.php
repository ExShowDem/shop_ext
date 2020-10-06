<?php
/**
 * @package  AcceptanceTester
 *
 * @since    1.4
 *
 * @link     http://codeception.com/docs/07-AdvancedUsage#StepObjects
 */
namespace Step\Frontend;
use Page\Frontend\CategoryPage as CategoryPage;
use Page\Frontend\Redshopb2bPage as Redshopb2bPage;
class CategorySteps extends \Step\Acceptance\redshopb2b
{
	/**
	 * @param $name
	 * @param $function
	 * @throws \Exception
	 */
	public function create($name,$function)
	{
		$I = $this;
		$I->am('Administrator');
		$I->wantToTest('Category creation in Frontend');

		$I->amGoingTo('Navigate to Categories page in Frontend');
		$I->amOnPage(CategoryPage::$URLCategories);
		$I->checkForPhpNoticesOrWarnings();
		$I->waitForElement(CategoryPage::$newButton, 30);
		$I->click(CategoryPage::$newButton);
		$I->waitForJS('return !!window.jQuery && window.jQuery.active == 0;', 30);
		$I->comment('I am redirected to the form');
		$I->waitForElement(CategoryPage::$adminForm, 30);
		$I->checkForPhpNoticesOrWarnings();
		$I->fillField(CategoryPage::$nameID, $name);
		$I->wait(1);
		switch ($function){
			case 'save':
				$I->wait(1);
				$I->click(CategoryPage::$saveButton);
				$I->waitForJS('return !!window.jQuery && window.jQuery.active == 0;', 30);
				$I->waitForText(CategoryPage::$saveCategorySuccess, 30, CategoryPage::$messageSuccessID);
				$I->click(CategoryPage::$saveCloseButton);
				$I->waitForElement(['link' => $name], 30);
				break;
			case 'save&close':
				$I->wait(1);
				$I->click(CategoryPage::$saveCloseButton);
				$I->waitForJS('return !!window.jQuery && window.jQuery.active == 0;', 30);
				$I->waitForElement(['link' => $name], 30);
				break;
			case 'save&new':
				$I->wait(1);
				$I->click(CategoryPage::$saveNewButton);
				$I->waitForJS('return !!window.jQuery && window.jQuery.active == 0;', 30);
				$I->waitForElement(CategoryPage::$adminForm, 30);
				$I->click(CategoryPage::$cancelButton);
				break;
			case 'cancel':
				$I->click(CategoryPage::$cancelButton);
				break;
			default:
				break;
		}
	}

	/**
	 * @param string $name
	 * @param null $company
	 * @param array $params
	 * @throws \Exception
	 */
	public function createCategory($name, $company = null, $params = [])
	{
		$I = $this;
		$I->comment('I navigate to Categories page in frontend');
		$I->amOnPage(CategoryPage::$URLCategories);
		$I->waitForElement(CategoryPage::$newButton, 30);
		$I->click(CategoryPage::$newButton);
		$I->comment('I am redirected to the form category detail');
		$I->waitForElementVisible(CategoryPage::$nameID, 30);
		$I->reloadPage();
		$I->waitForElementVisible(CategoryPage::$nameID, 30);
		$I->fillField(CategoryPage::$nameID, $name);
		if (!is_null($company))
		{
			$I->selectOptionInChosenXpath(CategoryPage::$categoryCompanyJform, $company);
		}

		if (isset($params['Description']))
		{
			$I->click(CategoryPage::$editor);
			$I->waitForElementVisible(CategoryPage::$description, 30);
			$I->fillField(CategoryPage::$description, $params['Description']);
			$I->click(CategoryPage::$editor);
		}

		$I->waitForElementVisible(CategoryPage::$saveButton, 30);
		$I->wait(2);
		$I->click(CategoryPage::$saveButton);
		$I->waitForJS('return !!window.jQuery && window.jQuery.active == 0;', 60);
		$I->wait(1);
		try
		{
			$I->waitForText(CategoryPage::$saveCategorySuccess, 10, CategoryPage::$messageSuccessID);
		}catch (\Exception $exception)
		{
			$I->click(CategoryPage::$saveButton);
			$I->waitForJS('return !!window.jQuery && window.jQuery.active == 0;', 60);
			$I->waitForText(CategoryPage::$saveCategorySuccess, 10, CategoryPage::$messageSuccessID);
		}
		$I->click(CategoryPage::$closeButton);
		$I->wait(1);
		$I->searchForItemInFrontend($name, ['search field locator id' => CategoryPage::$searchCategory]);
		$usepage = new CategoryPage();
		$I->waitForElementVisible($usepage->getXpathItem($name), 30);
	}

	/**
	 * Method for check missing title when create category
	 * @throws \Exception
	 */
	public function createMissingTitle()
	{
		$I = $this;
		$I->am('Administrator');
		$I->wantToTest('Category creation in Frontend');

		$I->amGoingTo('Navigate to Categories page in Frontend');
		$I->amOnPage(CategoryPage::$URLCategories);
		$I->checkForPhpNoticesOrWarnings();

		$I->waitForElement(CategoryPage::$newButton, 30);
		$I->click(CategoryPage::$newButton);

		$I->comment('I am redirected to the form');
		$I->waitForElement(CategoryPage::$adminForm, 30);
		$I->checkForPhpNoticesOrWarnings();
		$I->wait(1);
		$I->click(CategoryPage::$saveButton);
		$I->waitForText(CategoryPage::$missingTitle, '30', Redshopb2bPage::$systemContainer);
	}

	/**
	 * @param $buttonName
	 * @throws \Exception
	 */
	public function checkButton($buttonName)
	{
		$I = $this;
		$I->am('Administrator');
		$I->wantToTest('Category creation in Frontend');

		$I->amGoingTo('Navigate to Categories page in Frontend');
		$I->amOnPage(CategoryPage::$URLCategories);
		$I->checkForPhpNoticesOrWarnings();

		switch ($buttonName){
			case 'publish':
				$I->waitForElementVisible(Redshopb2bPage::$publishButton, 30);
				$I->waitForElement(Redshopb2bPage::$publishButton,30);
				$I->click(Redshopb2bPage::$publishButton);
				$I->acceptPopup();
				break;
			case 'unpublish':
				$I->click(Redshopb2bPage::$unpublishButton);
				$I->acceptPopup();
				break;
			case 'delete':
				$I->click(Redshopb2bPage::$deleteButton);
				$I->acceptPopup();
				break;
			case 'edit':
				$I->click(Redshopb2bPage::$editButton);
				$I->acceptPopup();
				break;
			default:
				break;
		}
	}

	/**
	 * @param $name
	 * @param $function
	 * @throws \Exception
	 */
	public function changeStatusCategoryByButton($name,$function)
	{
		$I = $this;
		$I->am('Administrator');
		$I->wantToTest('Category edit in Frontend');

		$I->amGoingTo('Navigate to Categories page in Frontend');
		$I->amOnPage(CategoryPage::$URLCategories);
		$I->searchForItemInFrontend($name, ['search field locator id' => CategoryPage::$searchCategory]);
		$I->waitForJS('return !!window.jQuery && window.jQuery.active == 0;', 30);
		$I->waitForElement("//thead//input[@name='checkall-toggle' or @name='toggle']", 30);

		$I->checkAllResults();
		switch ($function)
		{
			case 'publish':
				$I->click(Redshopb2bPage::$publishButton);
				$I->waitForJS('return !!window.jQuery && window.jQuery.active == 0;', 60);
				$I->waitForText(CategoryPage::$publishOneSuccess, 30, CategoryPage::$messageSuccessID);
				$currentState = $I->getCategoryState($name);
				$I->verifyState('published', $currentState);
				break;
			case 'unpublish':
				$I->click(Redshopb2bPage::$unpublishButton);
				$I->waitForJS('return !!window.jQuery && window.jQuery.active == 0;', 60);
				try
				{
					$I->waitForText(CategoryPage::$unpublishOneSuccess, 10, CategoryPage::$messageSuccessID);
				}catch (\Exception $e)
				{
					$I->click(Redshopb2bPage::$unpublishButton);
					$I->waitForText(CategoryPage::$unpublishOneSuccess, 10, CategoryPage::$messageSuccessID);
				}
				$currentState = $I->getCategoryState($name);
				$I->verifyState('unpublished', $currentState);
				break;
			case 'publishState';
				$I->click(CategoryPage::$categoryStatePath);
				$I->waitForJS('return !!window.jQuery && window.jQuery.active == 0;', 60);
				$currentState = $I->getCategoryState($name);$I->verifyState('published', $currentState);
				$I->verifyState('published', $currentState);
				break;
			case 'unpublishState';
				$I->click(CategoryPage::$categoryStatePath);
				$I->waitForJS('return !!window.jQuery && window.jQuery.active == 0;', 60);
				$currentState = $I->getCategoryState($name);
				$I->verifyState('unpublished', $currentState);
				break;
			default:
				break;
		}
	}

	/**
	 * Function to get State of the Category
	 *
	 * @param   String $categoryName Name of the Category
	 *
	 * @return string
	 * @throws \Exception
	 */
	public function getCategoryState($name)
	{
		$I = $this;
		$I->amOnPage(CategoryPage::$URLCategories);
		$I->searchForItemInFrontend($name, ['search field locator id' => CategoryPage::$searchCategory]);
		$I->waitForElementVisible(CategoryPage::$categoryStatePath, 30);
		$text = $I->grabAttributeFrom(CategoryPage::$categoryStatePath, 'onclick');

		if (strpos($text, 'unpublish') > 0)
		{
			$result = 'published';
		}
		else
		{
			$result = 'unpublished';
		}

		return $result;
	}

	/**
	 * @param $name
	 * @param $nameEdit
	 * @throws \Exception
	 */
	public function edit($name,$nameEdit)
	{
		$I = $this;
		$I->am('Administrator');
		$I->wantToTest('Category creation in Frontend');

		$I->amGoingTo('Navigate to Categories page in Frontend');
		$I->amOnPage(CategoryPage::$URLCategories);
		$I->checkForPhpNoticesOrWarnings();

		$I->waitForElement(CategoryPage::$newButton, 30);
		$I->click(CategoryPage::$newButton);
		$I->waitForJS('return !!window.jQuery && window.jQuery.active == 0;', 30);
		$I->comment('I am redirected to the form');
		$I->waitForElement(CategoryPage::$adminForm, 30);
		$I->checkForPhpNoticesOrWarnings();

		$I->fillField(CategoryPage::$nameID, $name);
		$I->click(CategoryPage::$saveButton);
		$I->waitForJS('return !!window.jQuery && window.jQuery.active == 0;', 30);
		try
		{
			$I->waitForText(CategoryPage::$saveCategorySuccess, 10, CategoryPage::$messageSuccessID);
		}catch (\Exception $exception)
		{
			$I->click(CategoryPage::$saveButton);
			$I->waitForJS('return !!window.jQuery && window.jQuery.active == 0;', 30);
			$I->waitForText(CategoryPage::$saveCategorySuccess, 10, CategoryPage::$messageSuccessID);
		}
		$I->waitForElement(CategoryPage::$adminForm, 30);
		$I->fillField(CategoryPage::$nameID, $nameEdit);
		$I->click(CategoryPage::$saveButton);
		$I->waitForElement(CategoryPage::$messageSuccessID,30);
		$I->see(CategoryPage::$categorySaveSuccess, CategoryPage::$messageSuccessID);
		$I->click(CategoryPage::$saveCloseButton);
		$I->comment('I am redirected to the list');
		$I->waitForElement(CategoryPage::$searchCategoryId, 30);
		$I->waitForText($nameEdit, 30);
	}
	
	/**
	 * @param $name
	 * @throws \Exception
	 */
	public function delete($name)
	{
		$I = $this;
		$I->wantToTest('Delete a category in Frontend');
		$I->amGoingTo('Navigate to Categories page in Frontend');
		$I->amOnPage(CategoryPage::$URLCategories);
		$I->searchForItemInFrontend($name, ['search field locator id' => CategoryPage::$searchCategory]);
		$I->checkAllResults();
		$I->waitForElement(Redshopb2bPage::$deleteButton, 30);
		$I->click(Redshopb2bPage::$deleteButton);
		$I->comment('I am redirected to the list');
		$I->dontSeeElement(['link' => $name]);
	}
}