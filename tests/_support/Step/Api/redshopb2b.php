<?php
namespace Step\Api;

class redshopb2b extends \ApiTester
{
	/**
	 * @var string
	 * @since 2.4.1
	 */
	protected $companyWebServiceVersion = '1.1.0';

	/**
	 * @var string
	 * @since 2.4.1
	 */
	protected $currencyWebServiceVersion = '1.0.0';

	/**
	 * @var string
	 * @since 2.4.1
	 */
	protected $countryWebServiceVersion = '1.0.0';

	/**
	 * @var string
	 * @since 2.4.0
	 */
	protected $categoryWebServiceVersion = '1.1.0';

	/**
	 * @var string
	 * @since 2.4.0
	 */
	protected $manufacturerWebServiceVersion = '1.1.0';

	/**
	 * @var string
	 * @since 2.4.0
	 */
	protected $productWebServiceVersion = '1.1.0';

	/**
	 * @var string
	 * @since 2.4.0
	 */
	protected $tagWebServiceVersion = '1.1.0';

	/**
	 * @var string
	 * @since 2.4.0
	 */
	protected $productFieldGroupWebServiceVersion = '1.0.0';

	/**
	 * @var string
	 * @since 2.5.0
	 */
	protected $holidayWebServiceVersion = '1.0.0';

	/**
	 * @var array
	 * @since 2.4.1
	 */
	protected $webserviceVersions = array(
		'company' => '1.1.0',
		'category' => '1.1.0',
		'product' => '1.1.0',
		'stockroom' => '1.2.0',
		'stockroom_product' => '1.0.0',
		'stockroom_product_item' => '1.0.0',
		'product_attribute' => '1.0.0',
		'product_attribute_value' => '1.1.0',
		'product_item' => '1.0.0',
		'order' => '1.5.0',
		'delivery_address' => '1.0.0',
		'product_field_group' => '1.0.0',
		'holiday' => '1.0.0'
	);

	/**
	 * Get the Main Company from witch it hangs all the rest of companies
	 *
	 * @param   string  $webserviceVersion  Version of the company web service to use to get it (if null it uses the global property of this class)
	 * @param   string  $name               The name of the Main Company
	 *
	 * @depends WebserviceIsAvailable
	 */
	public function getMainCompanyId($webserviceVersion = null, $name = 'Main Company')
	{
		$I = $this;
		$I->wantTo('Get the main company');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET(
			'index.php'
			. '?option=redshopb&view=company'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=' . (is_null($webserviceVersion) ? $this->companyWebServiceVersion : $webserviceVersion)
			. '&id=erp.main'
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$this->mainCompanyId = (int) $I->grabDataFromResponseByJsonPath('$.id')[0];

		$I->comment('The Main Company id is: ' . $this->mainCompanyId);

		return $this->mainCompanyId;
	}

	/**
	 * @param       $name
	 * @param null  $webserviceVersion
	 * @param array $params
	 *
	 * @return mixed
	 * @throws \Exception
	 * @since 2.4.1
	 */
	public function createCompany($name, $webserviceVersion = null, $params = array())
	{
		$I = $this;

		if (!isset($params['parent_id']))
		{
			$params['parent_id'] = $I->getMainCompanyId($webserviceVersion);
		}

		if (!isset($params['id']))
		{
			$params['id'] = rand(999, 9999);
		}

		// Add dummy address if no specified
		if (!isset($params['address_line1']))
		{
			$params['address_line1'] = "Blangstedgaardvej 1";
		}

		// Add dummy zip if no specified
		if (!isset($params['zip']))
		{
			$params['zip'] = "5220";
		}

		// Add dummy city if no specified
		if (!isset($params['city']))
		{
			$params['city'] = "Odense";
		}

		if (!isset($params['country_code']))
		{
			$params['country_code'] = 'DK';
		}

		// Add default currency code if no specified
		if (!isset($params['currency_code']))
		{
			$params['currency_code'] = 'EUR';
		}

		$I->amHttpAuthenticated('admin', 'admin');

		$request = 'index.php'
			. '?option=redshopb&view=company'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=' . (is_null($webserviceVersion) ? $this->companyWebServiceVersion : $webserviceVersion)
			. "&name=$name";

		foreach ($params as $field => $value)
		{
			$request .= "&$field=$value";
		}

		$I->sendPOST($request);
		$I->seeResponseCodeIs(201);
		$I->seeResponseIsJson();
		$links = $I->grabDataFromResponseByJsonPath('$._links');
		$I->sendGET($links[0]['redshopb-company:self']['href']);
		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$ids = $I->grabDataFromResponseByJsonPath('$.id');
		$id = $ids[0];
		$I->comment("Created new company with name '$name' and id '$id'");

		return $id;
	}

	/**
	 * Unpublishes a redSHOPB company
	 *
	 * @param   integer  $id                 the id number of the company to be unpublished
	 * @param   string   $webserviceVersion  Version of the company web service to use to get it (if null it uses the global property of this class)
	 *
	 * @return void
	 */
	public function unpublishCompany($id, $webserviceVersion = null)
	{
		$I = $this;
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendPOST('index.php'
			. '?option=redshopb&view=company'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=' . (is_null($webserviceVersion) ? $this->companyWebServiceVersion : $webserviceVersion)
			. "&id=$id"
			. '&task=unpublish'
		);

		$I->seeResponseCodeIs(200);
	}

	/**
	 * publishes a redSHOPB company
	 *
	 * @param   integer  $id                 the id number of the company to be published
	 * @param   string   $webserviceVersion  Version of the company web service to use to get it (if null it uses the global property of this class)
	 *
	 * @return void
	 * @since 2.5.0
	 */
	public function publishCompany($id, $webserviceVersion = null)
	{
		$I = $this;
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendPOST('index.php'
		             . '?option=redshopb&view=company'
		             . '&api=Hal'
		             . '&webserviceClient=site'
		             . '&webserviceVersion=' . (is_null($webserviceVersion) ? $this->companyWebServiceVersion : $webserviceVersion)
		             . "&id=$id"
		             . '&task=publish'
		);

		$I->seeResponseCodeIs(200);
	}

	public function deleteCompany($id, $webserviceVersion = null)
	{
		$I = $this;

		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendDELETE('index.php'
			. '?option=redshopb&view=company'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=' . (is_null($webserviceVersion) ? $this->companyWebServiceVersion : $webserviceVersion)
			. "&id=$id"
		);
		$I->seeResponseCodeIs(200);
	}

	/**
	 * Get a currency ID by it's code.
	 *
	 * @param   string  $code               The currency 3-digit code. Euro by default.
	 * @param   string  $webserviceVersion  Version of the currency web service to use to get it (if null it uses the global property of this class)
	 *
	 * @return int
	 */
	public function getCurrencyId($code = 'EUR', $webserviceVersion = null)
	{
		/* This function don't work until RSBTB-2353 get's fixed
		$I = $this;
		$I->wantTo("GET an existing currency");
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=currency'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=' . (is_null($webserviceVersion) ? $this->currencyWebServiceVersion : $webserviceVersion)
			. "&code=$code"
		);
		...
		*/

		// While the issue get's solved I'm returning the Euro ID
		return 44;
	}

	/**
	 * Get a country ID by it's code.
	 *
	 * @param   string  $code               The currency 3-digit code. Euro by default.
	 * @param   string  $webserviceVersion  Version of the country web service to use to get it (if null it uses the global property of this class)
	 *
	 * @return int
	 */
	public function getCountryId($code = 'DK', $webserviceVersion = null)
	{
		/* This function don't work until RSBTB-2354 get's fixed
		$I = $this;
		$I->wantTo("GET an existing currency");
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=country'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=' . (is_null($webserviceVersion) ? $this->countryWebServiceVersion : $webserviceVersion)
			. "&code=$code"
		);
		...
		*/

		// While the issue get's solved I'm returning the Danmark ID
		return 59;
	}

	/**
	 * Creates a redSHOPB category
	 *
	 * @param   string  $name               The name for the Category
	 * @param   string  $webserviceVersion  Version of the category web service to use to get it (if null it uses the global property of this class)
	 * @param   array   $params             Optional parameters to create the category
	 *
	 * @return integer
	 */
	public function createCategory($name, $webserviceVersion = null, $params = array())
	{
		$I = $this;
		$I->amHttpAuthenticated('admin', 'admin');

		$request = 'index.php'
				. '?option=redshopb&view=category'
				. '&api=Hal'
				. '&webserviceClient=site'
				. '&webserviceVersion=' . (is_null($webserviceVersion) ? $this->categoryWebServiceVersion : $webserviceVersion)
				. '&name=' . $name;

		foreach ($params as $field => $value)
		{
			$request .= "&$field=$value";
		}

		$I->sendPOST($request);
		$I->seeResponseCodeIs(201);
		$I->seeResponseIsJson();
		$links = $I->grabDataFromResponseByJsonPath('$._links');
		$I->sendGET($links[0]['redshopb-category:self']['href']);
		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$ids = $I->grabDataFromResponseByJsonPath('$.id');
		$id = $ids[0];
		$I->comment("Created new category with name '$name' and id '$id'");

		return $id;
	}

	/**
	 * @param       $name
	 * @param null  $webserviceVersion
	 * @param array $params
	 *
	 * @return mixed
	 * @throws \Exception
	 * @since 2.5.0
	 */
	public function createProductFieldGroup($name, $webserviceVersion = null, $params = array())
	{
		$I = $this;
		$I->amHttpAuthenticated('admin', 'admin');

		$request = 'index.php'
			. '?option=redshopb&view=product_field_group'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=' . (is_null($webserviceVersion) ? $this->productFieldGroupWebServiceVersion : $webserviceVersion)
			. '&name=' . $name;

		foreach ($params as $field => $value)
		{
			$request .= "&$field=$value";
		}

		$I->sendPOST($request);
		$I->seeResponseCodeIs(201);
		$I->seeResponseIsJson();
		$links = $I->grabDataFromResponseByJsonPath('$._links');
		$I->sendGET($links[0]['redshopb-product_field_group:self']['href']);
		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$ids = $I->grabDataFromResponseByJsonPath('$.id');
		$id = $ids[0];
		$I->comment("Created new product_field_group with name '$name' and id '$id'");

		return $id;
	}

	/**
	 * @param       $name
	 * @param       $day
	 * @param       $month
	 * @param       $country_id
	 * @param null  $webserviceVersion
	 * @param array $params
	 *
	 * @return mixed
	 * @throws \Exception
	 * @since 2.5.0
	 */
	public function createHoliday($name, $day, $month, $country_id, $webserviceVersion = null, $params = array())
	{
		$I = $this;
		$I->amHttpAuthenticated('admin', 'admin');
		$request = 'index.php'
			. '?option=redshopb&view=holiday'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=' . (is_null($webserviceVersion) ? $this->holidayWebServiceVersion : $webserviceVersion)
			. '&name=' . $name
			. '&country_id=' . $country_id
			. '&day=' . $day
			. '&month=' . $month;
		foreach ($params as $field => $value)
		{
			$request .= "&$field=$value";
		}
		$I->sendPOST($request);
		$I->seeResponseCodeIs(201);
		$I->seeResponseIsJson();
		$links = $I->grabDataFromResponseByJsonPath('$._links');
		$I->sendGET($links[0]['redshopb-holiday:self']['href']);
		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$ids = $I->grabDataFromResponseByJsonPath('$.id');
		$id = $ids[0];
		$I->comment("Created new holiday with name '$name' and id '$id'");
		return $id;
	}

	/**
	 * @param       $name
	 * @param       $address_line1
	 * @param       $zip
	 * @param       $city
	 * @param       $country_code
	 * @param null  $webserviceVersion
	 * @param array $params
	 *
	 * @return mixed
	 * @throws \Exception
	 * @since 2.5.0
	 */
	public function createDeliveryAddress($name, $address_line1, $zip, $city, $country_code, $webserviceVersion = null, $params = array())
	{
		$I = $this;
		$I->amHttpAuthenticated('admin', 'admin');

		$request = 'index.php'
		           . '?option=redshopb&view=delivery_address'
		           . '&api=Hal'
		           . '&webserviceClient=site'
		           . '&webserviceVersion=' . (is_null($webserviceVersion) ? $this->productFieldGroupWebServiceVersion : $webserviceVersion)
		           . '&name1=' . $name
		           . '&address_line1=' . $address_line1
		           . '&zip=' . $zip
		           . '&city=' . $city
		           . '&country_code=' . $country_code;

		foreach ($params as $field => $value)
		{
			$request .= "&$field=$value";
		}

		$I->sendPOST($request);
		$I->seeResponseCodeIs(201);
		$I->seeResponseIsJson();
		$links = $I->grabDataFromResponseByJsonPath('$._links');
		$I->sendGET($links[0]['redshopb-delivery_address:self']['href']);
		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$ids = $I->grabDataFromResponseByJsonPath('$.id');
		$id = $ids[0];
		$I->comment("Created new delivery_address with name '$name' and id '$id'");
		return $id;
	}

	/**
	 * @param      $id
	 * @param null $webserviceVersion
	 * @since 2.5.0
	 */
	public function deleteDeliveryAddress($id, $webserviceVersion = null)
	{
		$I = $this;
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendDELETE('index.php'
		               . '?option=redshopb&view=delivery_address'
		               . '&api=Hal'
		               . '&webserviceClient=site'
		               . '&webserviceVersion=' . (is_null($webserviceVersion) ? $this->categoryWebServiceVersion : $webserviceVersion)
		               . "&id=$id"
		);

		$I->seeResponseCodeIs(200);
	}

	/**
	 * Deletes a redSHOPB category
	 *
	 * @param   integer  $id                 the id number of the category to be removed
	 * @param   string   $webserviceVersion  Version of the category web service to use to get it (if null it uses the global property of this class)
	 *
	 * @return  void
	 */
	public function deleteCategory($id, $webserviceVersion = null)
	{
		$I = $this;
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendDELETE('index.php'
			. '?option=redshopb&view=category'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=' . (is_null($webserviceVersion) ? $this->categoryWebServiceVersion : $webserviceVersion)
			. "&id=$id"
		);

		$I->seeResponseCodeIs(200);
	}

	/**
	 * @param      $id
	 * @param null $webserviceVersion
	 */
	public function deleteProductFieldGroup($id, $webserviceVersion = null)
	{
		$I = $this;
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendDELETE('index.php'
			. '?option=redshopb&view=product_field_group'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=' . (is_null($webserviceVersion) ? $this->productFieldGroupWebServiceVersion : $webserviceVersion)
			. "&id=$id"
		);

		$I->seeResponseCodeIs(200);
	}

	/**
	 * @param      $id
	 * @param null $webserviceVersion
	 * @since 2.5.0
	 */
	public function deleteHoliday($id, $webserviceVersion = null)
	{
		$I = $this;
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendDELETE('index.php'
			. '?option=redshopb&view=holiday'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=' . (is_null($webserviceVersion) ? $this->holidayWebServiceVersion : $webserviceVersion)
			. "&id=$id"
		);

		$I->seeResponseCodeIs(200);
	}

	/**
	 * Publishes a redSHOPB category
	 *
	 * @param   integer  $id                 the id number of the category to be published
	 * @param   string   $webserviceVersion  Version of the category web service to use to get it (if null it uses the global property of this class)
	 *
	 * @return  void
	 */
	public function publishCategory($id, $webserviceVersion = null)
	{
		$I = $this;
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendPOST('index.php'
			. '?option=redshopb&view=category'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=' . (is_null($webserviceVersion) ? $this->categoryWebServiceVersion : $webserviceVersion)
			. "&id=$id"
			. '&task=publish'
		);

		$I->seeResponseCodeIs(200);
	}

	/**
	 * Unpublishes a redSHOPB category
	 *
	 * @param   integer  $id                 the id number of the category to be unpublished
	 * @param   string   $webserviceVersion  Version of the category web service to use to get it (if null it uses the global property of this class)
	 *
	 * @return  void
	 */
	public function unpublishCategory($id, $webserviceVersion = null)
	{
		$I = $this;
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendPOST('index.php'
			. '?option=redshopb&view=category'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=' . (is_null($webserviceVersion) ? $this->categoryWebServiceVersion : $webserviceVersion)
			. "&id=$id"
			. '&task=unpublish'
		);

		$I->seeResponseCodeIs(200);
	}

	/**
	 * Creates a redSHOPB manufacturer
	 *
	 * @param   string  $name               The name for the Category
	 * @param   string  $webserviceVersion  Version of the manufacturer web service to use to get it (if null it uses the global property of this class)
	 * @param   array   $params             Optional parameters to create the manufacturer
	 *
	 * @return integer
	 */
	public function createManufacturer($name, $webserviceVersion = null, $params = array())
	{
		$I = $this;
		$I->amHttpAuthenticated('admin', 'admin');

		$request = 'index.php'
				. '?option=redshopb&view=manufacturer'
				. '&api=Hal'
				. '&webserviceClient=site'
				. '&webserviceVersion=' . (is_null($webserviceVersion) ? $this->manufacturerWebServiceVersion : $webserviceVersion)
				. '&name=' . $name;

		foreach ($params as $field => $value)
		{
			$request .= "&$field=$value";
		}

		$I->sendPOST($request);
		$I->seeResponseCodeIs(201);
		$I->seeResponseIsJson();
		$links = $I->grabDataFromResponseByJsonPath('$._links');
		$I->sendGET($links[0]['redshopb-manufacturer:self']['href']);
		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$ids = $I->grabDataFromResponseByJsonPath('$.id');
		$id = $ids[0];
		$I->comment("Created new manufacturer with name '$name' and id '$id'");

		return $id;
	}

	public function unfeatureManufacturer($id, $webserviceVersion = null)
	{
		$I = $this;
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendPOST('index.php'
			. '?option=redshopb&view=manufacturer'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=' . (is_null($webserviceVersion) ? $this->manufacturerWebServiceVersion : $webserviceVersion)
			. "&id=$id"
			. '&task=unfeature'
		);

		$I->seeResponseCodeIs(200);
	}

	public function deleteManufacturer($id, $webserviceVersion = null)
	{
		$I = $this;

		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendDELETE('index.php'
			. '?option=redshopb&view=manufacturer'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=' . (is_null($webserviceVersion) ? $this->manufacturerWebServiceVersion : $webserviceVersion)
			. "&id=$id"
		);
		$I->seeResponseCodeIs(200);
	}

	/**
	 * Publishes a redSHOPB manufacturer
	 *
	 * @param   integer  $id                 the id number of the manufacturer to be published
	 * @param   string   $webserviceVersion  Version of the manufacturer web service to use to get it (if null it uses the global property of this class)
	 *
	 * @return  void
	 */
	public function publishManufacturer($id, $webserviceVersion = null)
	{
		$I = $this;
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendPOST('index.php'
			. '?option=redshopb&view=manufacturer'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=' . (is_null($webserviceVersion) ? $this->manufacturerWebServiceVersion : $webserviceVersion)
			. "&id=$id"
			. '&task=publish'
		);

		$I->seeResponseCodeIs(200);
	}

	/**
	 * Unpublishes a redSHOPB manufacturer
	 *
	 * @param   integer  $id                 the id number of the manufacturer to be unpublished
	 * @param   string   $webserviceVersion  Version of the manufacturer web service to use to get it (if null it uses the global property of this class)
	 *
	 * @return  void
	 */
	public function unpublishManufacturer($id, $webserviceVersion = null)
	{
		$I = $this;
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendPOST('index.php'
			. '?option=redshopb&view=manufacturer'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=' . (is_null($webserviceVersion) ? $this->manufacturerWebServiceVersion : $webserviceVersion)
			. "&id=$id"
			. '&task=unpublish'
		);

		$I->seeResponseCodeIs(200);
	}

	public function createProduct($name, $sku, $category_id, $webserviceVersion = null, $params = [])
	{
		$I = $this;
		$I->amHttpAuthenticated('admin', 'admin');

		$request = 'index.php'
				. '?option=redshopb&view=product'
				. '&api=Hal'
				. '&webserviceClient=site'
				. '&webserviceVersion=' . (is_null($webserviceVersion) ? $this->productWebServiceVersion : $webserviceVersion)
				. "&name=$name"
				. "&sku=$sku"
				. "&category_id=$category_id";

		foreach ($params as $field => $value)
		{
			$request .= "&$field=$value";
		}

		$I->sendPOST($request);

		$I->seeResponseCodeIs(201);
		$I->seeResponseIsJson();

		$links = $I->grabDataFromResponseByJsonPath('$._links');
		$I->sendGET($links[0]['redshopb-product:self']['href']);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$ids = $I->grabDataFromResponseByJsonPath('$.id');
		$id = $ids[0];

		return $id;
	}

	/**
	 * Unpublishes a redSHOPB product
	 *
	 * @param   integer  $id                 the id number of the product to be unpublished
	 * @param   string   $webserviceVersion  Version of the product web service to use to get it (if null it uses the global property of this class)
	 *
	 * @return void
	 */
	public function unpublishProduct($id, $webserviceVersion = null)
	{
		$I = $this;
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendPOST(
			'index.php'
			. '?option=redshopb&view=product'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=' . (is_null($webserviceVersion) ? $this->productWebServiceVersion : $webserviceVersion)
			. "&id=$id"
			. '&task=unpublish'
		);

		$I->seeResponseCodeIs(200);
	}

	public function deleteProduct($id, $webserviceVersion = null)
	{
		$I = $this;

		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendDELETE('index.php'
			. '?option=redshopb&view=product'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=' . (is_null($webserviceVersion) ? $this->productWebServiceVersion : $webserviceVersion)
			. "&id=$id"
		);
		$I->seeResponseCodeIs(200);
	}

	public function createTag($name, $webserviceVersion = null, $params = [])
	{
		$I = $this;
		$I->amHttpAuthenticated('admin', 'admin');

		$request = 'index.php'
			. '?option=redshopb&view=tag'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=' . (is_null($webserviceVersion) ? $this->tagWebServiceVersion : $webserviceVersion)
			. "&name=$name";

		foreach ($params as $field => $value)
		{
			$request .= "&$field=$value";
		}

		$I->sendPOST($request);

		$I->seeResponseCodeIs(201);
		$I->seeResponseIsJson();

		$links = $I->grabDataFromResponseByJsonPath('$._links');
		$I->sendGET($links[0]['redshopb-tag:self']['href']);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$ids = $I->grabDataFromResponseByJsonPath('$.id');
		$id = $ids[0];

		return $id;
	}

	public function deleteTag($id, $webserviceVersion = null)
	{
		$I = $this;

		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendDELETE('index.php'
			. '?option=redshopb&view=tag'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=' . (is_null($webserviceVersion) ? $this->tagWebServiceVersion : $webserviceVersion)
			. "&id=$id"
		);

		$I->seeResponseCodeIs(200);
	}

	/**
	 * Unpublishes a redSHOPB tag
	 *
	 * @param   integer  $id  the id number of the tag to be unpublished
	 */
	public function unpublishTag($id, $webserviceVersion = null)
	{
		$I = $this;
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendPOST('index.php'
			. '?option=redshopb&view=tag'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=' . (is_null($webserviceVersion) ? $this->tagWebServiceVersion : $webserviceVersion)
			. "&id=$id"
			. '&task=unpublish'
		);

		$I->seeResponseCodeIs(200);
	}

	/**
	 * Creates a redSHOPB record with a webservice
	 *
	 * @param   string  $view               The view of the webservice
	 * @param   array   $params             The parameters of the webservice
	 * @param   string  $webserviceVersion  The version of the webservice to be used
	 * @param   string  $webserviceApi      The type of api to use
	 * @param   string  $webserviceClient   The client the webservice should use
	 *
	 * @return void
	 */
	public function webserviceCrudCreate($view, $params, $webserviceVersion = null, $webserviceApi = 'Hal', $webserviceClient = 'site')
	{
		$I = $this;
		$I->amHttpAuthenticated('admin', 'admin');

		$request = 'index.php'
				. '?option=redshopb&view=' . $view
				. '&api=' . $webserviceApi
				. '&webserviceClient=' . $webserviceClient
				. '&webserviceVersion=' . (is_null($webserviceVersion) ? $this->webserviceVersions[$view] : $webserviceVersion);

		foreach ($params as $field => $value)
		{
			$request .= "&$field=$value";
		}

		$I->sendPOST($request);

		$I->seeResponseCodeIs(201);
		$I->seeResponseIsJson();

		$links = $I->grabDataFromResponseByJsonPath('$._links');
		$I->sendGET($links[0]['redshopb-' . $view . ':self']['href']);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$ids = $I->grabDataFromResponseByJsonPath('$.id');
		$id = $ids[0];

		return $id;
	}

	/**
	 * Deletes a redSHOPB record with a webservice
	 *
	 * @param   string  $view               The view of the webservice
	 * @param   array   $id                 The id of the record to be deleted
	 * @param   string  $webserviceVersion  The version of the webservice to be used
	 * @param   string  $webserviceApi      The type of api to use
	 * @param   string  $webserviceClient   The client the webservice should use
	 *
	 * @return void
	 */
	public function webserviceCrudDelete($view, $id, $webserviceVersion = null, $webserviceApi = 'Hal', $webserviceClient = 'site')
	{
		$I = $this;
		$I->amHttpAuthenticated('admin', 'admin');

		$I->sendDELETE('index.php'
			. '?option=redshopb&view=' . $view
			. '&api=' . $webserviceApi
			. '&webserviceClient=' . $webserviceClient
			. '&webserviceVersion=' . (is_null($webserviceVersion) ? $this->webserviceVersions[$view] : $webserviceVersion)
			. '&id=' . $id
		);

		$I->seeResponseCodeIs(200);
	}

	/**
	 * Unpublishes a redSHOPB record with a webservice
	 *
	 * @param   string  $view               The view of the webservice
	 * @param   array   $id                 The id of the record to be unpublished
	 * @param   string  $webserviceVersion  The version of the webservice to be used
	 * @param   string  $webserviceApi      The type of api to use
	 * @param   string  $webserviceClient   The client the webservice should use
	 *
	 * @return void
	 */
	public function webserviceTaskUnpublish($view, $id, $webserviceVersion = null, $webserviceApi = 'Hal', $webserviceClient = 'site')
	{
		$I = $this;
		$I->amHttpAuthenticated('admin', 'admin');

		$I->sendGET('index.php'
			. '?option=redshopb&view=' . $view
			. '&api=' . $webserviceApi
			. '&webserviceClient=' . $webserviceClient
			. '&webserviceVersion=' . (is_null($webserviceVersion) ? $this->webserviceVersions[$view] : $webserviceVersion)
			. '&id=' . $id
			. '&task=unpublish'
		);

		$I->seeResponseCodeIs(200);
	}
}
