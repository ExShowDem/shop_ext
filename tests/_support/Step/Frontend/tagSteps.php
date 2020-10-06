<?php
/**
 * @package     Aesir-E-commerce
 * @subpackage  Step
 * @copyright   Copyright (C) 2016 - 2018 Aesir-E-commerce. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Step\Frontend;
use Page\Frontend\tagPage;
use phpDocumentor\Reflection\DocBlock\Tag;

class tagSteps extends \Step\Acceptance\redshopb2b
{
	/**
	 * @param array $tag
	 * @throws \Exception
	 */
	public function create($tag = array())
	{
		$client = $this;
		$client->amOnPage(tagPage::$url);
		$client->waitForElement(tagPage::$newButton);
		$client->click(tagPage::$newButton);
		$client->waitForElement(tagPage::$adminForm, 30);
		$client->waitForElement(tagPage::$nameID, 30);
		$client->fillField(tagPage::$nameID, $tag['name']);

		if(isset($newsletter['status']))
		{
			if($tag['status'] == 'Published')
			{
				$client->selectOptionInRadioField('Status', 'Published');
			}
			else
			{
				$client->selectOptionInRadioField('Status', 'Unpublished');
			}
		}

		$client->waitForElement(tagPage::$saveButton, 30);
		$client->wait(0.5);
		$client->click(tagPage::$saveCloseButton);
		$client->waitForText(tagPage::$saveSuccessMessage, 30);
		$client->waitForText($tag['name'], 30);
	}

	/**
	 * @param $name
	 * @param $nameEdit
	 * @throws \Exception
	 */
	public function editName($name, $nameEdit)
	{
		$client = $this;
		$client->amOnPage(tagPage::$url);
		$client->waitForElementVisible(tagPage::$newButton, 30);
		$client->searchForItemInFrontend($name, ['search field locator id' => tagPage::$tagSearch]);
		$client->wait(0.5);
		$client->waitForElementVisible(['link' => $name], 30);
		$client->click($name);
		$client->wait(1);
		$client->waitForElementVisible(tagPage::$adminForm, 30);
		$client->waitForElementVisible(tagPage::$nameID, 30);
		$client->fillField(tagPage::$nameID, $nameEdit);
		$client->waitForElementVisible(tagPage::$saveCloseButton, 30);
		$client->wait(0.5);
		$client->click(tagPage::$saveCloseButton);
		$client->waitForText(tagPage::$saveEditSuccess, 30);
		$client->waitForElementVisible(tagPage::$iconClear, 30);
		$client->click(tagPage::$iconClear);
		$client->waitForElementVisible(['link' => $nameEdit], 30);
		$client->dontSeeElement(['link' => $name]);
	}

	/**
	 * @param $name
	 * @throws \Exception
	 */
	public function delete($name)
	{
		$client = $this;
		$client->amOnPage(tagPage::$url);
		$client->amOnPage(tagPage::$url);
		$client->waitForElement(tagPage::$newButton);
		$client->searchForItemInFrontend($name, ['search field locator id' => tagPage::$tagSearch]);
		$client->waitForElement(['link' => $name], 30);
		$client->checkAllResults();
		$client->click(tagPage::$deleteButton);
		$client->waitForElementVisible(tagPage::$tagModal, 30);

		$client->waitForElementVisible(tagPage::$acceptDelete, 30);
		$client->wait(1);
		$client->click(tagPage::$acceptDelete);
		$client->waitForText(tagPage::$messageDeleteSuccess, 30);
		$client->dontSeeElement(['link' => $name]);
	}
}