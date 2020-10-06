Feature: Cart
  In order to buy products
  As a Employee With Login
  I need to be able to put interesting products into the Cart

  Scenario: Add 2 units of the same product to the cart
    Given there is a Vendor Company called "Nike"
    And there is a Customer called "Odense Sports Shop" that is Customer at Company "Nike"
    And there is a products Category called "football equipment" at "Nike"
    And there is a "football ball" product with sku "ball1" in "football equipment" category at "Nike" company, witch costs "1,5"
    And there is a Employee With Login called "John" at "Odense Sports Shop"
    And user "John" has the amount of "10" "euros" in the wallet for purchasing
    When I add the amount of "2" products of "football ball" to the basket
    Then I should have "2" products in cart
    And the total price in cart should be "3"
