@modules
Feature: administrator login
	As an employee with login
	I want to use the filter module
	In order to filter products in the shop

  Scenario: Enable filter module
      Given I am logged in as a Joomla Administrator
      When I publish the module "redSHOP B2B Filter"
      And I display the module "redSHOP B2B Filter"
      And I set the module position of "redSHOP B2B Filter" to "position-7"

  	Scenario: Check existence of module and its filters
  		Given I am logged into frontend as administrator
  		And there is a Vendor Company called "%randomVendor%"
  		And there is a Customer called "%randomCustomer%" that is Customer at Company "%randomVendor%"
  		And there is a product category called "%randomCategory%" at "%randomVendor%"
  		And there are several products
  			| product           | sku | category          | company         | price |
  			| %randomProduct1%  | bt1 | %randomCategory%  | %randomVendor%  | 5     |
  			| %randomProduct2%  | bt2 | %randomCategory%  | %randomVendor%  | 10    |
  		And there is an Employee With Login at "%randomCustomer%" called "%randomEmployee%"
  		When I log out of frontend
  		And I log into frontend as user "%randomEmployee%"
  		Then I should see the filter module in the shop under the "%randomCategory%" category

  	Scenario: Delete created companies
  		Given I am logged into frontend as administrator
  		When I delete the created companies
  			| company           |
  			| %randomCustomer%  |
  			| %randomVendor%    |
      Then I empty placeholders
