<?php
/**
 * @package     Aesir E-Commerce.
 * @subpackage  Tests
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir E-Commerce. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

use Step\Api\redshopb2b;

/**
 * Class SiteRedshopbDepartment120erpCest
 * @since 2.8.0
 */
class SiteRedshopbDepartment120erpCest
{
	/**
	 * @var
	 * @since 2.8.0
	 */
	protected $faker;

	/**
	 * @var
	 * @since 2.8.0
	 */
	protected $erpId;

	/**
	 * @var
	 * @since 2.8.0
	 */
	protected $name;

	/**
	 * @var
	 * @since 2.8.0
	 */
	protected $company_id;

	/**
	 * @var
	 * @since 2.8.0
	 */
	protected $id;

	/**
	 * @var
	 * @since 2.8.0
	 */
	protected $department_number;

	/**
	 * @var
	 * @since 2.8.0
	 */
	protected $new_name;

	/**
	 * @param redshopb2b $I
	 * @since 2.8.0
	 */
	public function prepare(redshopb2b $I)
	{
		$I->wantTo('prepare for POST a new department');
		$this->faker = Faker\Factory::create();

		$this->erpId = (int) $this->faker->numberBetween(100, 1000);
		$this->company_id = $I->getMainCompanyId();
		$this->name = $this->faker->bothify('SiteRedshopbDepartment120erpCest ?##?');

		$this->department_number = (int) $this->faker->numberBetween(100, 1000);
	}

	/**
	 * @param redshopb2b $I
	 * @since 2.8.0
	 */
	public function createWithErpId(redshopb2b $I)
	{
		$I->wantTo('POST a new department with erp id');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendPOST('index.php'
			. '?option=redshopb&view=department'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.2.0'
			. "&id=$this->erpId"
			. "&name=$this->name"
			. "&company_id=$this->company_id"
			. "&department_number=$this->department_number"
		);

		$I->seeResponseCodeIs(201);
		$I->seeResponseIsJson();
	}

	/**
	 * @param ApiTester $I
	 * @since 2.8.0
	 */
	public function readWithErpId(ApiTester $I)
	{
		$I->wantTo('GET an existing department with erp id');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=department'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.2.0'
			. "&id=erp.$this->erpId"
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsjson();
		$I->seeResponseContainsJson(['id_others' => ['erp.' . $this->erpId]]);
		$I->seeResponseContainsJson(['name' => $this->name]);
	}

	/**
	 * @param ApiTester $I
	 * @since 2.8.0
	 */
	public function updateWithErpId(ApiTester $I)
	{
		$I->wantTo('UPDATE a department using PUT with erp id');
		$I->amHttpAuthenticated('admin', 'admin');
		$this->new_name = "new_" . $this->name;
		$I->sendPUT('index.php'
			. '?option=redshopb&view=department'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.2.0'
			. "&id=erp.$this->erpId"
			. "&name=$this->new_name"
		);

		$I->seeResponseCodeIs(200);

		$I->sendGET('index.php'
			. '?option=redshopb&view=department'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.2.0'
			. "&id=erp.$this->erpId"
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsjson();
		$I->seeResponseContainsJson(['name' => $this->new_name]);
		$I->dontSeeResponseContainsJson(['name' => $this->name]);

		$this->name = $this->new_name;
	}

	/**
	 * @param ApiTester $I
	 * @since 2.8.0
	 */
	public function taskUnpublishWithErpId(ApiTester $I)
	{
		$I->wantTo('unpublish a department using GET with erp id');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=department'
			. '&task=unpublish'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.2.0'
			. "&id=erp.$this->erpId"
		);

		$I->seeResponseCodeIs(200);

		$I->sendGET('index.php'
			. '?option=redshopb&view=department'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.2.0'
			. "&id=erp.$this->erpId"
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->seeResponseContainsJson(['state' => false]);
	}

	/**
	 * @param ApiTester $I
	 * @since 2.8.0
	 */
	public function taskPublishWithErpId(ApiTester $I)
	{
		$I->wantTo('publish a department using GET with erp id');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=department'
			. '&task=publish'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.2.0'
			. "&id=erp.$this->erpId"
		);

		$I->seeResponseCodeIs(200);

		$I->sendGET('index.php'
			. '?option=redshopb&view=department'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.2.0'
			. "&id=erp.$this->erpId"
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->seeResponseContainsJson(['state' => true]);
	}

	/**
	 * @param ApiTester $I
	 * @since 2.8.0
	 */
	public function taskImageUploadWithErpId(ApiTester $I)
	{
		// The following base64 code is a representation of a green 16px x 16px image
		$this->image = "iVBORw0KGgoAAAANSUhEUgAAADIAAAAyCAIAAACRXR/mAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAyJpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuMC1jMDYwIDYxLjEzNDc3NywgMjAxMC8wMi8xMi0xNzozMjowMCAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvIiB4bWxuczp4bXBNTT0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL21tLyIgeG1sbnM6c3RSZWY9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZVJlZiMiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENTNSBNYWNpbnRvc2giIHhtcE1NOkluc3RhbmNlSUQ9InhtcC5paWQ6RDUxRjY0ODgyQTkxMTFFMjk0RkU5NjI5MEVDQTI2QzUiIHhtcE1NOkRvY3VtZW50SUQ9InhtcC5kaWQ6RDUxRjY0ODkyQTkxMTFFMjk0RkU5NjI5MEVDQTI2QzUiPiA8eG1wTU06RGVyaXZlZEZyb20gc3RSZWY6aW5zdGFuY2VJRD0ieG1wLmlpZDpENTFGNjQ4NjJBOTExMUUyOTRGRTk2MjkwRUNBMjZDNSIgc3RSZWY6ZG9jdW1lbnRJRD0ieG1wLmRpZDpENTFGNjQ4NzJBOTExMUUyOTRGRTk2MjkwRUNBMjZDNSIvPiA8L3JkZjpEZXNjcmlwdGlvbj4gPC9yZGY6UkRGPiA8L3g6eG1wbWV0YT4gPD94cGFja2V0IGVuZD0iciI/PuT868wAAABESURBVHja7M4xEQAwDAOxuPw5uwi6ZeigB/CntJ2lkmytznwZFhYWFhYWFhYWFhYWFhYWFhYWFhYWFhYWFhYW1qsrwABYuwNkimqm3gAAAABJRU5ErkJggg==";
		$I->wantTo('upload an image to the department using GET with erp id');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=department'
			. '&task=imageUpload'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.2.0'
			. "&id=erp.$this->erpId"
			. "&image=$this->image"
		);

		$I->seeResponseCodeIs(200);
	}

	/**
	 * @param ApiTester $I
	 * @since 2.8.0
	 */
	public function taskImageRemoveWithErpId(ApiTester $I)
	{
		$I->wantTo('remove an image to the department using GET with erp id');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=department'
			. '&task=imageRemove'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.2.0'
			. "&id=erp.$this->erpId"
		);

		$I->seeResponseCodeIs(200);
	}

	/**
	 * @param ApiTester $I
	 * @since 2.8.0
	 */
	public function deleteWithErpId(ApiTester $I)
	{
		$I->wantTo('DELETE a department using DELETE with erp id');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendDELETE('index.php'
			. '?option=redshopb&view=department'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.2.0'
			. "&id=erp.$this->erpId"
		);

		$I->seeResponseCodeIs(200);

		$I->sendGET('index.php'
			. '?option=redshopb&view=department'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.2.0'
			. "&id=erp.$this->erpId"
		);

		$I->seeResponseCodeIs(404);
		$I->seeResponseIsJson();
	}
}