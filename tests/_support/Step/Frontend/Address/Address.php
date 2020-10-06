<?php
/**
 * @package     Aesir-ec
 * @subpackage  Step Address
 * @copyright   Copyright (C) 2016 - 2019 Aesir-ec. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 **/
namespace Step\Frontend\Address;
use Step\Frontend\UserSteps;
use Page\Frontend\Address\AddressPage;
class Address extends UserSteps
{
	/**
	 * @param array $address
	 * @param $function
	 * @throws \Exception
	 * @since 2.5.0
	 */
	public function create($address = array(), $function = 'save')
	{
		$client = $this;
		$client->amOnPage(AddressPage::$url);
		$client->waitForElementVisible(AddressPage::$newButton,30);
		$client->click(AddressPage::$newButton);
		$client->waitForElementVisible(AddressPage::$nameID, 30);
		$client->fillField(AddressPage::$nameID, $address['name']);

		if(isset($address['name_second']))
		{
			$client->fillField(AddressPage::$addressSecondLine, $address['name_second']);
		}
		$client->fillField(AddressPage::$addressAddress, $address['address']);

		if (isset($address['address_second']))
		{
			$client->fillField(AddressPage::$addressSecondLine, $address['address_second']);
		}
		$client->fillField(AddressPage::$addressZipCode, $address['code']);
		$client->fillField(AddressPage::$addressCity, $address['city']);

		if (isset($address['email']))
		{
			$client->fillField(AddressPage::$emailId, $address['email']);
		}

		if(isset($address['phone']))
		{
			$client->fillField(AddressPage::$phoneID, $address['phone']);
		}
		$client->selectOptionInChosenjs(AddressPage::$country, $address['country']);

		if(isset($address['address_type']))
		{
			$client->selectOptionInChosenjs(AddressPage::$addressType, $address['address_type']);
		}
		$client->selectOptionInChosenjs(AddressPage::$entityType, $address['entity_type']);

		switch ($address['entity_type'])
		{
			case 'Company':
				$client->click(AddressPage::$entityCompanyId);
				$use = new AddressPage();
				$client->waitForElementVisible($use->getXpathInsideLi($address['entity_name']), 30);
				$client->click($use->getXpathInsideLi($address['entity_name']));
				break;
			case 'Department':
				$client->click(AddressPage::$entityDepartmentId);
				$use = new AddressPage();
				$client->waitForElementVisible($use->getXpathInsideLi($address['entity_name']), 30);
				$client->click($use->getXpathInsideLi($address['entity_name']));
				break;
			default:
				$client->selectOptionInChosenjs(AddressPage::$entityName, $address['entity_name']);
				break;
		}

		switch ($function)
		{
			case 'save':
				$client->waitForElementVisible(AddressPage::$saveButton);
				$client->click(AddressPage::$saveButton);
				$client->waitForText(AddressPage::$saveSuccessAddress, 30);
				$client->waitForElementVisible(AddressPage::$closeButton,30);
				$client->click(AddressPage::$closeButton);
				break;
			case 'save&close':
				$client->waitForElementVisible(AddressPage::$saveCloseButton);
				$client->click(AddressPage::$saveCloseButton);
				$client->waitForText(AddressPage::$saveSuccessAddress, 30);
				break;
			case 'cancel':
				$client->waitForElementVisible(AddressPage::$cancelButton);
				$client->click(AddressPage::$cancelButton);
		}
	}

	/**
	 * @param array $addressOld
	 * @param array $addressNew
	 * @param string $function
	 * @throws \Exception
	 * @since 2.5.0
	 */
	public function edit($addressOld = array(), $addressNew = array(), $function = 'save')
	{
		$client = $this;
		$client->amOnPage(AddressPage::$url);
		$client->searchForItemInFrontend($addressOld['name'],['search field locator id' => AddressPage::$searchAddress]);
		$client->waitForElementVisible(['link' => $addressOld['name']], 30);
		$client->click(['link' => $addressOld['name']]);
		$client->waitForElementVisible(AddressPage::$nameID, 30);
		if ($addressOld['name'] != $addressNew['name'])
		{
			$client->fillField(AddressPage::$nameID, $addressNew['name']);
		}

		if ($addressOld['name_second'] != $addressNew['name_second'])
		{
			$client->fillField(AddressPage::$addressSecondLine, $addressNew['address_second']);
		}

		if ($addressOld['address'] != $addressNew['address'])
		{
			$client->fillField(AddressPage::$addressAddress, $addressNew['address']);
		}

		if ($addressOld['code'] != $addressNew['code'])
		{
			$client->fillField(AddressPage::$addressAddress, $addressNew['code']);
		}

		if ($addressOld['email'] != $addressNew['email'])
		{
			$client->fillField(AddressPage::$addressAddress, $addressNew['email']);
		}

		if ($addressOld['city'] != $addressNew['city'])
		{
			$client->fillField(AddressPage::$addressAddress, $addressNew['city']);
		}

		if ($addressOld['phone'] != $addressNew['phone'])
		{
			$client->fillField(AddressPage::$addressAddress, $addressNew['phone']);
		}

		if ($addressOld['country'] != $addressNew['country'])
		{
			$client->selectOptionInChosenjs(AddressPage::$country, $addressNew['country']);
		}

		if($function == 'save&close')
		{
			$client->waitForElementVisible(AddressPage::$saveCloseButton);
			$client->click(AddressPage::$saveCloseButton);
			$client->waitForText(AddressPage::$saveSuccessAddress, 30);
		}
		else
		{
			$client->waitForElementVisible(AddressPage::$saveButton);
			$client->click(AddressPage::$saveButton);
			$client->waitForText(AddressPage::$saveSuccessAddressAfterUpdate, 30);
			$client->waitForElementVisible(AddressPage::$closeButton,30);
			$client->click(AddressPage::$closeButton);
		}
	}

	/**
	 * @param array $address
	 * @throws \Exception
	 * @since 2.5.0
	 */
	public function deleteAddress($address = array())
	{
		$client = $this;
		$client->amOnPage(AddressPage::$url);
		$client->waitForElementVisible(AddressPage::$iconClear, 30);
		$client->click(AddressPage::$iconClear);
		$client->searchForItemInFrontend($address['name'],['search field locator id' => AddressPage::$searchAddress]);
		$client->waitForElementVisible(['link' => $address['name']], 30);
		$client->checkAllResults();
		$client->waitForElementVisible(AddressPage::$deleteButton, 30);
		$client->click(AddressPage::$deleteButton);
		$client->waitForElementVisible(AddressPage::$iconClear, 30);
		$client->click(AddressPage::$iconClear);
		$client->searchForItemInFrontend($address['name'],  ['search field locator id' => AddressPage::$searchAddress] );
		$client->dontSeeElement(['link' => $address['name']]);
	}

	/**
	 * @param array $address
	 * @throws \Exception
	 * @since 2.5.0
	 */
	public function createMissingValue($address = array())
	{
		$client = $this;
		$client->amOnPage(AddressPage::$url);
		$client->waitForElementVisible(AddressPage::$newButton,30);
		$client->click(AddressPage::$newButton);
		$client->waitForElementVisible(AddressPage::$nameID, 30);
		$client->wantToTest('Create without input any value');
		$client->waitForElementVisible(AddressPage::$saveButton, 30);
		$client->click(AddressPage::$saveButton);
		$client->waitForText(AddressPage::$missingAddress, 10);
		$client->waitForText(AddressPage::$missingZipCode, 10);
		$client->waitForText(AddressPage::$missingCity, 30);
		$client->waitForText(AddressPage::$missingCountry, 30);
		$client->waitForText(AddressPage::$missingName, 30);
		$client->waitForText(AddressPage::$missingType, 30);

		$client->wantToTest('Create just have Name');
		$client->fillField(AddressPage::$nameID, $address['name']);

		$client->waitForElementVisible(AddressPage::$saveButton, 30);
		$client->click(AddressPage::$saveButton);
		$client->dontSee(AddressPage::$missingName);
		$client->waitForText(AddressPage::$missingAddress, 10);
		$client->waitForText(AddressPage::$missingZipCode, 10);
		$client->waitForText(AddressPage::$missingCity, 30);
		$client->waitForText(AddressPage::$missingCountry, 30);
		$client->waitForText(AddressPage::$missingType, 30);

		$client->wantToTest('Create have name and Zipcode');
		$client->fillField(AddressPage::$addressZipCode, $address['code']);
		$client->waitForElementVisible(AddressPage::$saveButton, 30);
		$client->click(AddressPage::$saveButton);
		$client->waitForText(AddressPage::$missingAddress, 10);
		$client->waitForText(AddressPage::$missingCity, 30);
		$client->waitForText(AddressPage::$missingCountry, 30);
		$client->dontSee(AddressPage::$missingZipCode);
		$client->waitForText(AddressPage::$missingType, 30);

		$client->wantToTest('Create have name and Zipcode and Address');
		$client->fillField(AddressPage::$addressAddress, $address['address']);
		$client->waitForElementVisible(AddressPage::$saveButton, 30);
		$client->click(AddressPage::$saveButton);
		$client->dontSee(AddressPage::$missingAddress);
		$client->waitForText(AddressPage::$missingCity, 30);
		$client->waitForText(AddressPage::$missingCountry, 30);
		$client->dontSee(AddressPage::$missingZipCode);
		$client->waitForText(AddressPage::$missingType, 30);

		$client->wantToTest('Create have Name and Zipcode and Address and Country');
		$client->selectOptionInChosenjs(AddressPage::$country, $address['country']);
		$client->waitForElementVisible(AddressPage::$saveButton, 30);
		$client->click(AddressPage::$saveButton);
		$client->dontSee(AddressPage::$missingAddress);
		$client->waitForText(AddressPage::$missingCity, 30);
		$client->dontSee(AddressPage::$missingCountry);
		$client->dontSee(AddressPage::$missingZipCode);
		$client->waitForText(AddressPage::$missingType, 30);

		$client->wantToTest('Create missing Entity Type');
		$client->fillField(AddressPage::$addressCity, $address['city']);
		$client->waitForElementVisible(AddressPage::$saveButton, 30);
		$client->click(AddressPage::$saveButton);
		$client->dontSee(AddressPage::$missingAddress);
		$client->dontSee(AddressPage::$missingCity);
		$client->dontSee(AddressPage::$missingCountry);
		$client->dontSee(AddressPage::$missingZipCode);
		$client->waitForText(AddressPage::$missingType, 30);

		$client->waitForElementVisible(AddressPage::$cancelButton);
		$client->click(AddressPage::$cancelButton);
	}
}