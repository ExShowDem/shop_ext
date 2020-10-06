<?php
/**
 * @package     Aesir.E-Commerce
 * @subpackage  Tests
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

class SiteRedshopbManufacturer100TasksCest
{
	/**
	 * Set up
	 */
	public function __construct()
	{
		$this->faker = Faker\Factory::create();
	}

	public function feature(Step\Api\redshopb2b $I)
	{
		$I->wantTo('Feature a Manufacturer using GET');
		$I->comment('I create a manufacturer unfeatured to be featured');
		$manufacturerA['name']  = (string) $this->faker->bothify('SiteRedshopbManufacturer100TasksCest manufacturer ?##?');
		$manufacturerA['id'] = (int) $I->createManufacturer($manufacturerA['name'], '1.0.0');
		$I->unfeatureManufacturer($manufacturerA['id'], '1.0.0');

		$I->wantTo('Set a manufacturer as Featured');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendPOST('index.php'
			. '?option=redshopb&view=manufacturer'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.0.0'
			. "&id=" . $manufacturerA['id']
			. '&task=feature'
		);

		$I->seeResponseCodeIs(200);

		$I->sendGET('index.php'
			. '?option=redshopb&view=manufacturer'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.0.0'
			. "&id=" . $manufacturerA['id']
		);
		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->seeResponseContainsJson(['featured' => true]);
		$I->deleteManufacturer($manufacturerA['id'], '1.0.0');
	}

	public function unfeature(Step\Api\redshopb2b $I)
	{
		$I->wantTo('Set a manufacturer as UnFeatured');
		$manufacturerB['name']  = (string) $this->faker->bothify('SiteRedshopbManufacturer100TasksCest manufacturer ?##?');
		$manufacturerB['id'] = (int) $I->createManufacturer($manufacturerB['name'], '1.0.0');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendPOST('index.php'
			 . '?option=redshopb&view=manufacturer'
			 . '&api=Hal'
			 . '&webserviceClient=site'
			 . '&webserviceVersion=1.0.0'
			 . "&id=" . $manufacturerB['id']
			 . '&task=unfeature'
		);

		$I->seeResponseCodeIs(200);

		$I->sendGET('index.php'
			. '?option=redshopb&view=manufacturer'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.0.0'
			. "&id=" . $manufacturerB['id']
		);
		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->seeResponseContainsJson(['featured' => false]);
		$I->deleteManufacturer($manufacturerB['id'], '1.0.0');
	}

	public function imageUploadAndImageRemove(Step\Api\redshopb2b $I)
	{
		$I->wantTo('Upload an image to a Manufacturer using POST');
		$I->comment('I create a manufacturer');
		$manufacturerC['name']  = (string) $this->faker->bothify('SiteRedshopbManufacturer100TasksCest manufacturer ?##?');
		$manufacturerC['id'] = (int) $I->createManufacturer($manufacturerC['name'], '1.0.0');

		// The following base64 code is a representation of a green 16px x 16px image
		$manufacturerC['image'] = "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";


		$I->wantTo('Upload a Base64 encoded Image');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendPOST('index.php'
					 . '?option=redshopb&view=manufacturer'
					 . '&api=Hal'
					 . '&webserviceClient=site'
					 . '&webserviceVersion=1.0.0'
					 . '&task=imageUpload',
					 [
						 'id' => $manufacturerC['id'],
						 'image' => $manufacturerC['image']
					 ]
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();

		$I->sendGET('index.php'
					. '?option=redshopb&view=manufacturer'
					. '&api=Hal'
					. '&webserviceClient=site'
					. '&webserviceVersion=1.0.0'
					. "&id=" . $manufacturerC['id']
		);
		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();

		$I->comment('I assert that image has been created');
		$I->dontSeeResponseContainsJson(['image' => null]);

		$image = $I->grabDataFromResponseByJsonPath('$.image');
		$I->assertRegExp("/(.*)media\/com_redshopb\/images\/originals\/manufacturers\/(.*).png/", $image[0], 'PNG Image has been created');

		$I->comment('I test image removal');
		$I->sendPOST('index.php'
			. '?option=redshopb&view=manufacturer'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.0.0'
			. '&task=imageRemove'
			. "&id=" . $manufacturerC['id']
		);
		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();

		$I->sendGET('index.php'
			. '?option=redshopb&view=manufacturer'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.0.0'
			. "&id=" . $manufacturerC['id']
		);
		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();

		$I->comment('I assert that image has been removed');
		$I->seeResponseContainsJson(['image' => null]);

		$I->deleteManufacturer($manufacturerC['id'], '1.0.0');
	}
}
