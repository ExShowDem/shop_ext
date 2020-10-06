<?php


/**
 * Inherited Methods
 * @method void wantToTest($text)
 * @method void wantTo($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method \Codeception\Lib\Friend haveFriend($name, $actorClass = NULL)
 *
 * @SuppressWarnings(PHPMD)
*/
class FunctionalTester extends \Codeception\Actor
{
    use _generated\FunctionalTesterActions;

   /**
    * Define custom actions here
    */

    /**
     * @Given there is a Vendor Company called :arg1
     */
    public function thereIsAVendorCompanyCalled($arg1)
    {
        $I = $this;
 //       $I->am('Administrator');
 //       $I->wantToTest('Product checkout in Frontend');

        $I->doFrontEndLogin($this->employeeWithLogin, $this->employeeWithLogin);

        $I->amOnPage('index.php?option=com_redshopb&view=shop');
        $I->waitForElement(['link' => $this->category], 30);
        $I->checkForPhpNoticesOrWarnings();
        $I->click(['link' => $this->category]);
        $I->waitForText($this->category,30,['class' => 'redshopb-shop-category-title']);
        $I->checkForPhpNoticesOrWarnings();
        $I->click(['link' => $this->product]);
        $I->waitForText($this->product,30,['css' => 'h1']);
        $I->checkForPhpNoticesOrWarnings();
        $I->see('1,50 €', ['class' => 'oneProductPrice']);
        $I->fillField(['css' => 'input.quantityForOneProduct'], 2);
        $I->click(['css' => 'button.add-to-cart-product']);
        $I->waitForText('Items added to cart', 30, ['id' => 'redshopbalertmessage']);
        $I->checkForPhpNoticesOrWarnings();
        $I->click(['id' => 'redshopb-cart-link']);
        $I->comment('I wait for Cart floating div to open');
        $I->waitForText('Credit:', 30, ['class' => 'cartLabelCreditText']);
        $I->checkForPhpNoticesOrWarnings();
        $I->see('3,00 €', ['class' => 'oneCurrencyTotal']);
        $I->waitForElement(['id' => "lc-shopping-cart-checkout"], 60);
        $I->click(['id' => "lc-shopping-cart-checkout"]);
        $I->waitForElement(['link' => '1. Cart'], 30);
        $I->checkForPhpNoticesOrWarnings();
        $I->click(['xpath' => "//button[contains(normalize-space(), 'Next')]"]);
        $I->waitForText('Delivery Address',30,['css' => 'h4']);
        $I->checkForPhpNoticesOrWarnings();
        $I->click(['xpath' => "//button[contains(normalize-space(), 'Next')]"]);
        $I->waitForElement(['xpath' => "//button[contains(normalize-space(), 'Complete Order')]"], 30);
        $I->checkForPhpNoticesOrWarnings();
        $I->click(['xpath' => "//button[contains(normalize-space(), 'Complete Order')]"]);
        $I->waitForText('has been placed', 30, ['id' => 'system-message-container']);
        $I->checkForPhpNoticesOrWarnings();
        $I->see('has been placed', ['id' => 'system-message-container']);
    }
    /**
     * @Given there is a Customer Company of :arg1 called :arg2
     */
    public function thereIsACustomerCompanyOfCalled($arg1, $arg2)
    {
    }
    /**
     * @Given there is a Category :arg1
     */
    public function thereIsACategory($arg1)
    {
    }

    /**
     * @Given there is a :arg1 product, witch costs :arg2
     */
    public function thereIsAProductWitchCosts($arg1, $arg2)
    {
    }

    /**
     * @Given there is a Employee With Login called :arg1
     */
    public function thereIsAEmployeeWithLoginCalled($arg1)
    {
    }
    /**
     * @Given that the :arg1 has the amount of :arg2 in the wallet for purchasing
     */
    public function thatTheHasTheAmountOfInTheWalletForPurchasing($arg1, $arg2)
    {
    }
    /**
     * @When I add the amount of :arg1 products of :arg2 to the basket
     */
    public function iAddTheAmountOfProductsOfToTheBasket($arg1, $arg2)
    {
    }
    /**
     * @Then I should have :arg1 products in cart
     */
    public function iShouldHaveProductsInCart($arg1)
    {
    }
    /**
     * @Then the overall cart price should be :arg1
     */
    public function theOverallCartPriceShouldBe($arg1)
    {
    }
}
