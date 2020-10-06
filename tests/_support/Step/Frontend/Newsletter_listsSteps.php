<?php
/**
 * @package  AcceptanceTester
 *
 * @since    2.4
 *
 * @link     http://codeception.com/docs/07-AdvancedUsage#StepObjects
 */

namespace Step\Frontend;
use Page\Frontend\NewsletterPage;

class Newsletter_listsSteps extends \Step\Acceptance\redshopb2b
{
	/**
	 * @param array $newsletter
	 * @throws \Exception
	 */
	public function createNewsletter($newsletter = array())
	{
		$client = $this;
		$client->amOnPage(NewsletterPage::$URL);
		$client->waitForElement(NewsletterPage::$newButton, 30);
		$client->click(NewsletterPage::$newButton);
		$client->waitForElement(NewsletterPage::$adminForm, 30);

		if (isset($newsletter['name']))
		{
			$client->fillField(NewsletterPage::$nameID, $newsletter['name']);
		}

		if(isset($newsletter['status']))
		{
			if($newsletter['status'] == 'Published')
			{
				$client->selectOptionInRadioField('Status', 'Published');
			}
			else
			{
				$client->selectOptionInRadioField('Status', 'Unpublished');
			}
		}

		try
		{

		} catch (\Exception $e)
		{
			$client->click(NewsletterPage::$saveCloseButton);
		}

		if(isset($newsletter['save']))
		{
			if ($newsletter['save'] == 'save')
			{
				$client->waitForElementVisible(NewsletterPage::$saveButton, 30);
				$client->click(NewsletterPage::$saveButton);
				$client->waitForText(NewsletterPage::$saveSuccess, 30, NewsletterPage::$messageSuccessID);
			}
			elseif ($newsletter['save'] == 'save&new')
			{
				$client->waitForElementVisible(NewsletterPage::$saveNewButton, 30);
				$client->click(NewsletterPage::$saveNewButton);
				$client->waitForText(NewsletterPage::$saveSuccess, 30, NewsletterPage::$messageSuccessID);
				$client->waitForElement(NewsletterPage::$cancelButton, 30);
				$client->click(NewsletterPage::$cancelButton);
			}
		}
		else
		{
			$client->waitForElementVisible(NewsletterPage::$saveCloseButton, 30);
			$client->click(NewsletterPage::$saveCloseButton);
			$client->waitForText(NewsletterPage::$saveSuccess, 30, NewsletterPage::$messageSuccessID);
		}
	}

	/**
	 * @param $name
	 * @param $nameEdit
	 * @throws \Exception
	 */
	public function editNameNewLetter($name, $nameEdit)
	{
		$client = $this;
		$client->amOnPage(NewsletterPage::$URL);
		$client->waitForElement(NewsletterPage::$editButton, 30);
		$client->checkAllResults();
		$client->click(NewsletterPage::$editButton);
		$client->waitForElement(NewsletterPage::$adminForm, 30);
		$client->waitForElement(NewsletterPage::$nameID, 30);
		$client->fillField(NewsletterPage::$nameID, $nameEdit);
		$client->waitForElement(NewsletterPage::$saveCloseButton, 30);
		$client->wait(0.5);
		$client->click(NewsletterPage::$saveCloseButton);
		$client->waitForText(NewsletterPage::$saveItem, 30, NewsletterPage::$messageSuccessID);
		$client->waitForElement(['link' => $nameEdit], 30);
		$client->dontSeeElement(['link' => $name]);
	}

	/**
	 * @param $name
	 * @throws \Exception
	 */
	public function deleteNewLetter($name)
	{
		$client = $this;
		$client->amOnPage(NewsletterPage::$URL);
		$client->waitForElement(NewsletterPage::$editButton, 30);
		$client->searchForItemInFrontend($name, ['search field locator id' => NewsletterPage::$newLetterSearch]);
		$client->waitForElement(NewsletterPage::$deleteButton, 30);
		$client->checkAllResults();
		$client->click(NewsletterPage::$deleteButton);
		$client->dontSeeElement(['link' => $name]);
	}
}