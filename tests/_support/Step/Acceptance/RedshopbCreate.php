<?php
namespace Step\Acceptance;

class RedshopbCreate extends \AcceptanceTester
{
	protected $scenario;

	protected $faker;

	public static $placeholders;

	public function __construct(\AcceptanceTester $I) 
	{
		$this->scenario = $I->getScenario();
		$this->faker = \Faker\Factory::create();
	}

	/**
	 * @Given there is a Vendor Company called :vendor
	 */
	public function thereIsAVendorCompanyCalled($vendor)
	{
		$redshopb2b = new \Step\Acceptance\redshopb2b($this->scenario);

		$this::$placeholders[$vendor] = $this->faker->bothify('Vendor Company ?###?');

		$redshopb2b->createRedshopbCompany(
			$this::$placeholders[$vendor],
			$this::$placeholders[$vendor],
			'address',
			$this->faker->postcode,
			$this->faker->city,
			'Denmark',
			'Main Company'
		);
	}

	/**
	 * @Given there is a Customer called :customer that is Customer at Company :vendor
	 */
	public function thereIsACustomerCalledThatIsCustomerAtCompany($customer, $vendor)
	{
		$redshopb2b = new \Step\Acceptance\redshopb2b($this->scenario);

		$this::$placeholders[$customer] = $this->faker->bothify('Customer Company ?###?');

		$redshopb2b->createRedshopbCompany(
			$this::$placeholders[$customer],
			$this::$placeholders[$customer],
			'address',
			$this->faker->postcode,
			$this->faker->city,
			'Denmark',
			"- " . $this::$placeholders[$vendor]
		);
	}

	/**
	 * @Given there are several product Categories
	 */
	public function thereAreSeveralProductCategories(\Behat\Gherkin\Node\TableNode $categories)
	{
		$redshopb2b = new \Step\Acceptance\redshopb2b($this->scenario);

		foreach ($categories->getRows() as $index => $row)
		{
			if ($index === 0)
			{
				$keys = $row;
				continue;
			}

			$categoryParams = array_combine($keys, $row);

			$this::$placeholders[$categoryParams['category']] = $this->faker->bothify('Category ?###?');

			$redshopb2b->createRedshopbCategory($this::$placeholders[$categoryParams['category']], "- " . $this::$placeholders[$categoryParams['vendor']]);
		}
	}

	/**
	 * @Given there is a product category called :category at :vendor
	 */
	public function thereIsAProductCategoryCalledAt($category, $vendor)
	{
		$redshopb2b = new \Step\Acceptance\redshopb2b($this->scenario);

		$this::$placeholders[$category] = $this->faker->bothify('Category ?###?');

		$redshopb2b->createRedshopbCategory($this::$placeholders[$category], "- " . $this::$placeholders[$vendor]);
	}

	/**
	 * @Given there are several products
	 */
	public function thereAreSeveralProducts(\Behat\Gherkin\Node\TableNode $products)
	{
		$redshopb2b = new \Step\Acceptance\redshopb2b($this->scenario);

		foreach ($products->getRows() as $index => $row)
		{
			if ($index === 0)
			{
				$keys = $row;
				continue;
			}

			$productParams = array_combine($keys, $row);

			$this::$placeholders[$productParams['product']] = $this->faker->bothify('Product ?###?');

			$redshopb2b->createRedshopbProduct(
				$this::$placeholders[$productParams['product']],
				$this::$placeholders[$productParams['category']],
				$productParams['sku'],
				"- " . $this::$placeholders[$productParams['company']],
				$productParams['price']
			);
		}
	}

	/**
	 * @Given there is an Employee With Login at :company called :employee
	 */
	public function thereIsAnEmployeeWithLoginAtCalledWithEmail($company, $employee)
	{
		$redshopb2b = new \Step\Acceptance\redshopb2b($this->scenario);

		$this::$placeholders[$employee] = $this->faker->bothify('Employee ?###?');

		$redshopb2b->createRedshopbUserEmployeeWithLogin(
			$this::$placeholders[$employee],
			$this->faker->email,
			"- - " . $this::$placeholders[$company]
		);
	}

	/**
	 * @Given Employee With Login :employee has :credit :currency added to their account
	 */
	public function employeeWithLoginHasAddedToTheirAccount($employee, $credit, $currency)
	{
		$redshopb2b = new \Step\Acceptance\redshopb2b($this->scenario);

		$redshopb2b->addCreditToEmployeeWithLogin($this::$placeholders[$employee], $currency, $credit);
	}

	/**
	 * @When I delete the created companies
	 */
	public function iDeleteTheCreatedCompanies(\Behat\Gherkin\Node\TableNode $companies)
	{
		$redshopb2b = new \Step\Acceptance\redshopb2b($this->scenario);

		foreach ($companies->getRows() as $index => $row)
		{
			if ($index === 0)
			{
				$keys = $row;
				continue;
			}

			$companyParams = array_combine($keys, $row);

			$redshopb2b->deleteRedshopbCompany($this::$placeholders[$companyParams['company']]);
		}
	}

	/**
	 * @Then I empty placeholders
	 */
	public function iEmptyPlaceholders()
	{
		$this::$placeholders = array();
	}
}
