<?php
/**
 * @package     Aesir.E-Commerce
 * @subpackage  Tests
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

class SiteRedshopbProduct110tasksCest
{
	public function _before()
	{
		$this->faker = Faker\Factory::create();
	}

	public function taskPublish(Step\Api\redshopb2b $I)
	{
		$I->wantTo('PUBLISH a Product using GET');
		$I->comment('I create a product unpublished to be published');

		$product = new stdClass;
		$product->name  = (string) $this->faker->bothify('SiteRedshopbProduct110tasksCest product ?##?');
		$product->sku  = $this->faker->randomNumber(3);
		$product->category_name = (string) $this->faker->bothify('SiteRedshopbProduct110FilteringCest category ?##?');
		$product->category_id  = (int) $I->createCategory($product->category_name);
		$product->id = (int) $I->createProduct(
			$product->name,
			$product->sku,
			$product->category_id,
			'1.1.0'
		);

		$I->unpublishProduct($product->id, '1.1.0');

		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=product'
			. '&task=publish'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
			. '&id=' . $product->id
		);

		$I->seeResponseCodeIs(200);

		$I->sendGET('index.php'
			. '?option=redshopb&view=product'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
			. '&id=' . $product->id
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->seeResponseContainsJson(['name' => $product->name]);
		$I->seeResponseContainsJson(['state' => true]);

		// Clean up
		$I->deleteProduct($product->id, '1.1.0');
	}

	public function taskUnpublish(Step\Api\redshopb2b $I)
	{
		$I->wantTo('UNPUBLISH a Product using GET');

		$product = new stdClass;
		$product->name  = (string) $this->faker->bothify('SiteRedshopbProduct110tasksCest product ?##?');
		$product->sku  = $this->faker->randomNumber(3);
		$product->category_name = (string) $this->faker->bothify('SiteRedshopbProduct110FilteringCest category ?##?');
		$product->category_id  = (int) $I->createCategory($product->category_name);
		$product->id = (int) $I->createProduct(
			$product->name,
			$product->sku,
			$product->category_id,
			'1.1.0'
		);

		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=product'
			. '&task=unpublish'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
			. '&id=' . $product->id
		);

		$I->seeResponseCodeIs(200);

		$I->sendGET('index.php'
			. '?option=redshopb&view=product'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
			. '&id=' . $product->id
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->seeResponseContainsJson(['name' => $product->name]);
		$I->seeResponseContainsJson(['state' => false]);

		// Clean up
		$I->deleteProduct($product->id, '1.1.0');
	}

	public function translate(Step\Api\redshopb2b $I)
	{
		$I->wantTo('Create a translation for a product with POST');

		$I->comment('I create a product to be translated');
		$product = new stdClass;
		$product->name  = (string) $this->faker->bothify('SiteRedshopbProduct110tasksCest product ?##?');
		$product->sku  = $this->faker->randomNumber(3);
		$product->category_name = (string) $this->faker->bothify('SiteRedshopbProduct110FilteringCest category ?##?');
		$product->category_id  = (int) $I->createCategory($product->category_name);
		$product->id = (int) $I->createProduct(
			$product->name,
			$product->sku,
			$product->category_id,
			'1.1.0'
		);

		$I->amHttpAuthenticated('admin', 'admin');
		$product->translatedName = 'french-' . $product->name;
		$I->sendPOST('index.php'
			. '?option=redshopb&view=product'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
			. '&task=translate'
			. '&language=fr-FR'
			. '&id=' . $product->id
			. '&name=' . $product->translatedName
		);

		$I->seeResponseCodeIs(200);

		$I->sendGET('index.php'
			. '?option=redshopb&view=product'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
			. '&id=' . $product->id
			. '&language=fr-FR'
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->seeResponseContainsJson(['name' => $product->translatedName]);
		$I->dontSeeResponseContainsJson(['name' => $product->name]);

		// Clean up
		$I->deleteProduct($product->id, '1.1.0');
	}

	public function translateRemove(Step\Api\redshopb2b $I)
	{
		$I->wantTo('Create a translation for a product to be removed afterwards');

		$I->comment('I create a product to be translated');
		$product = new stdClass;
		$product->name  = (string) $this->faker->bothify('SiteRedshopbProduct110tasksCest product ?##?');
		$product->sku  = $this->faker->randomNumber(3);
		$product->category_name = (string) $this->faker->bothify('SiteRedshopbProduct110FilteringCest category ?##?');
		$product->category_id  = (int) $I->createCategory($product->category_name);
		$product->id = (int) $I->createProduct(
			$product->name,
			$product->sku,
			$product->category_id,
			'1.1.0'
		);

		$I->amHttpAuthenticated('admin', 'admin');
		$product->translatedName = 'french-' . $product->name;
		$I->sendPOST('index.php'
			. '?option=redshopb&view=product'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
			. '&task=translate'
			. '&language=fr-FR'
			. '&id=' . $product->id
			. '&name=' . $product->translatedName
		);

		$I->seeResponseCodeIs(200);

		$I->comment('I remove the translation');
		$I->sendPOST('index.php'
			. '?option=redshopb&view=product'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
			. '&task=translateRemove'
			. '&language=fr-FR'
			. '&id=' . $product->id
		);

		$I->sendGET('index.php'
			. '?option=redshopb&view=product'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
			. '&id=' . $product->id
			. '&language=fr-FR'
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();

		$I->dontSeeResponseContainsJson(['name' => $product->translatedName]);
		$I->seeResponseContainsJson(['name' => $product->name]);

		// Clean up
		$I->deleteProduct($product->id, '1.1.0');
	}

	public function taskTagAddandTagRemove(Step\Api\redshopb2b $I)
	{
		$I->wantTo('Create a a product with tags');

		$I->comment('I create a product to be translated');
		$product = new stdClass;
		$product->name  = (string) $this->faker->bothify('SiteRedshopbProduct110tasksCest product ?##?');
		$product->sku  = $this->faker->randomNumber(3);
		$product->category_name = (string) $this->faker->bothify('SiteRedshopbProduct110FilteringCest category ?##?');
		$product->category_id  = (int) $I->createCategory($product->category_name);
		$product->id = (int) $I->createProduct(
			$product->name,
			$product->sku,
			$product->category_id,
			'1.1.0'
		);

		$tag1 = new stdClass;
		$tag1->name = $this->faker->bothify('SiteRedshopbProduct110tasksCest tag1 ?##?');
		$tag1->id  = (int) $I->createTag($tag1->name);

		$tag2 = new stdClass;
		$tag2->name = $this->faker->bothify('SiteRedshopbProduct110tasksCest tag2 ?##?');
		$tag2->id  = (int) $I->createTag($tag2->name);

		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendPOST('index.php'
			. '?option=redshopb&view=product'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
			. '&task=tagAdd'
			. '&id=' . $product->id
			. '&tag_id=' . $tag1->id
		);

		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendPOST('index.php'
			. '?option=redshopb&view=product'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
			. '&task=tagAdd'
			. '&id=' . $product->id
			. '&tag_id=' . $tag2->id
		);

		$I->sendGET('index.php'
			. '?option=redshopb&view=product'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
			. '&id=' . $product->id
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();

		$I->seeResponseContainsJson(['name' => $product->name]);
		$I->seeResponseContainsJson(['tags' => [$tag1->id, $tag2->id]]);

		// Tag remove task
		$I->comment('I test removing the tags from the product');
		$I->sendPOST('index.php'
			. '?option=redshopb&view=product'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
			. '&task=tagRemove'
			. '&id=' . $product->id
			. '&tag_id=' . $tag1->id
		);

		$I->sendPOST('index.php'
			. '?option=redshopb&view=product'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
			. '&task=tagRemove'
			. '&id=' . $product->id
			. '&tag_id=' . $tag2->id
		);

		$I->sendGET('index.php'
					. '?option=redshopb&view=product'
					. '&api=Hal'
					. '&webserviceClient=site'
					. '&webserviceVersion=1.1.0'
					. '&id=' . $product->id
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();

		$I->seeResponseContainsJson(['name' => $product->name]);
		$I->dontSeeResponseContainsJson(['tags' => [$tag1->id, $tag2->id]]);
		$I->seeResponseContainsJson(['tags' => []]);

		// Clean up
		$I->deleteTag($tag1->id);
		$I->deleteTag($tag2->id);
		$I->deleteProduct($product->id, '1.1.0');
		$I->deleteCategory($product->category_id, '1.1.0');
	}
}
