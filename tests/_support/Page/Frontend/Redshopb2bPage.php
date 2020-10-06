<?php
namespace Page\Frontend;

abstract class Redshopb2bPage
{
	/**
	 * @var string
	 */
	public static $URLShop = 'index.php?option=com_redshopb&view=shop';

	/**
	 * @var string
	 */
	public static $URLInstall = '/administrator/index.php?option=com_installer';

	/**
	 * @var string
	 */
	public static $URLCart = 'index.php?option=com_redshopb&view=carts';

	/**
	 * @var string
	 */
	public static $moduleUrl = '/administrator/index.php?option=com_menus&view=item&layout=edit&menutype=mainmenu';

	/**
	 * @var string
	 */
	public static $URLLogin = '/index.php?option=com_users&view=login';

	/**
	 * @var string
	 */
	public static $URLRedCORE = 'administrator/index.php?option=com_redcore&view=config&layout=edit&component=com_redcore&return=';

	/**
	 * @var string
	 */
	public static $URLWebservice = 'administrator/index.php?option=com_redcore&view=webservices';

	/**
	 * @var string
	 */
	public static $URLPlugins = 'administrator/index.php?option=com_plugins';

	/**
	 * @var string
	 */
	public static $urlMyProfile = 'index.php?option=com_redshopb&view=myprofile';

	/**
	 * @var string
	 */
	public static $editBilling = "//form[@name='billingform']//input[@name='editbilling']";

	/**
	 * @var string
	 */
	public static $phoneID = "#jform_phone";

	/**
	 * @var string
	 */
	public static $checkPhone = "//p[@id='delivery-phone']";

	/**
	 * @var array
	 * general new button
	 */
	public static $newButton = ['xpath' => "//button[contains(normalize-space(), 'New')]"];

	/**
	 * @var array
	 */
	public static $saveButton = ['xpath' => "//button[contains(normalize-space(), 'Save')]"];

	/**
	 * @var array
	 */
	public static $closeButton = ['xpath' => "//button[contains(normalize-space(), 'Close')]"];

	/**
	 * @var array
	 */
	public static $deleteButton = ['xpath' => "//button[contains(normalize-space(), 'Delete')]"];

	/**
	 * @var array
	 */
	public static $saveCloseButton = ['xpath' => "//button[contains(normalize-space(), 'Save & Close')]"];

	/**
	 * @var array
	 */
	public static $saveNewButton = ['xpath' => "//button[contains(normalize-space(), 'Save & New')]"];

	/**
	 * @var array
	 */
	public static $cancelButton = ['xpath' => "//button[contains(normalize-space(), 'Cancel')]"];

	/**
	 * @var array
	 */
	public static $nextButton = ['xpath' => "//button[contains(normalize-space(), 'Next')]"];

	/**
	 * @var array
	 */
	public static $backButton = ['xpath' => '//button[contains(normalize-space(), \'Back\')]'];

	/**
	 * @var array
	 */
	public static $publishButton = ['xpath' => "//button[contains(normalize-space(), 'Publish')]"];

	/**
	 * @var array
	 */
	public static $unpublishButton = ['xpath' => "//button[contains(normalize-space(), 'Unpublish')]"];

	/**
	 * @var string
	 * @since 2.5.0
	 */
	public static $unpublishLabel = "//label[contains(normalize-space(), 'Unpublish')]";

	/**
	 * @var array
	 */
	public static $completeOderButton = ['xpath' => "//button[contains(normalize-space(), 'Complete Order')]"];

	/**
	 * @var array
	 */
	public static $editButton = ['xpath' => "//button[contains(normalize-space(), 'Edit')]"];

	/**
	 * @var string
	 */
	public static $endButton = ['xpath' => "//button[contains(normalize-space(), 'End')]"];

	/**
	 * @var array
	 */
	public static $saveSuccessButton = ['xpath' => "//button[contains(normalize-space(), 'Save') and @class='btn btn-default btn-success']"];

	/**
	 * @var array
	 */
	public static $closeDangerButton = ['xpath' => "//button[contains(normalize-space(), 'Close') and @class='btn btn-default btn-danger']"];

	/**
	 * @var array
	 */
	public static $newSuccessButton = ['xpath' => "//button[contains(normalize-space(), 'New') and @class='btn btn-default btn-success']"];

	/**
	 * @var array
	 */
	public static $messageSuccessID = ['id' => 'system-message'];

	/**
	 * @var array
	 */
	public static $alertError = ['class' => 'alert-error'];

	/**
	 * @var array
	 */
	public static $messageError = ['class' => 'alert-error'];

	/**
	 * @var array
	 */
	public static $alertMessage = ['xpath' => "//div[@class='alert-message']"];

	/**
	 * @var array
	 */
	public static $alertHead = ['xpath' => "//h4[@class='alert-heading']"];

	/**
	 * @var array
	 * check product frontend
	 */
	public static $categoryClass = ['class' => 'redshopb-shop-category-title'];

	/**
	 * @var array
	 */
	public static $productNameH1 = ['xpath' => '//h1'];

	/**
	 * @var array
	 */
	public static $productPrice = ['xpath' => '//span[@id=\'productPrice\']'];

	/**
	 * @var array
	 */
	public static $productPriceWithoutAttribute = ['class' => 'oneProductPrice'];

	/**
	 * @var array
	 */
	public static $quantityProduct = ['css' => 'input.quantityForOneProduct'];

	/**
	 * @var string
	 * general field
	 */

	public static $title = 'Title';

	/**
	 * @var string
	 */
	public static $status = 'Status';

	/**
	 * @var string
	 */
	public static $featured = 'Featured';

	/**
	 * @var array
	 */
	public static $codeId = ['id' =>'jform_code'];

	/**
	 * @var string
	 */
	public static $nameID = ['id' => 'jform_name'];

	/**
	 * @var string
	 */
	public static $company = 'Company';

	/**
	 * @var string
	 */
	public static $country = 'Country';

	/**
	 * @var string
	 * general message
	 */
	public static $saveSuccess = 'Item submitted';

	/**
	 * @var string
	 */
	public static $productAddCartSuccess = 'Items added to cart';

	/**
	 * @var string
	 */
	public static $saveItem = 'Item saved.';

	/**
	 * @var string
	 */
	public static $messageWarning = 'Warning';

	/**
	 * @var string
	 */
	public static $messageErrorSave = 'Error';

	/**
	 * @var string
	 */
	public static $saveItemSuccess = "Item saved.";

	/**
	 * @var string
	 */
	public static $publishOneSuccess = "1 item successfully published";

	/**
	 * @var string
	 */
	public static $unpublishOneSuccess = "1 item successfully unpublished";


	/**
	 * @var string
	 */
	public static $messageDeleteSuccess = "1 item successfully deleted";

	/**
	 * @var string
	 */
	public static $configurationSuccess = 'Configuration saved.';

	/**
	 * @var string
	 */
	public static $moduleSaveSuccess = "Menu item saved.";

	/**
	 * @var string
	 */
	public static $pluginEnableSuccess = "Plugin enabled.";

	/**
	 * @var string
	 */
	public static $messageOrderSuccess = "has been placed";

	/**
	 * @var array
	 * General located
	 */
	public static $systemContainer = ['id' => 'system-message-container'];

	/**
	 * @var array
	 */
	public static $iconSearch = ['class' => "icon-search"];

	/**
	 * @var array
	 */
	public static $iconClear = ['xpath' => '//button[contains(text(),\'Clear\')]'];

	/**
	 * @var string
	 */
	public static $firstValue = "//input[@id='cb0']";

	/**
	 * @var array
	 */
	public static $messageClass = ['xpath' => "//div[@class='alert-message']"];

	/**
	 * @var string
	 */
	public static $messageAddToCartSuccess = 'Was successfully added to the cart!';

	/**
	 * @var string
	 * @since 2.8.0
	 */
	public static $messageStockNotEnough = "Oops! Could not add to cart. The stock amount not have enough.";

	/**
	 * @var string
	 * @since 2.8.0
	 */
	public static $shopMore = "//p[@class='close shop-more']";

	/**
	 * @var string
	 */
	public static $messageSaveCartSuccess = 'Your cart has been saved successfully.';

	/**
	 * @var string
	 */
	public static $messageCartToOrder = 'has been placed';

	/**
	 * @var string
	 */
	public static $missingNameRequired = 'Field required: Name';

	/**
	 * @var string
	 */
	public static $missingCompanyRequired = 'Field required: Company';

	/**
	 * @var string
	 */
	public static $messageInvalidFieldName = 'Invalid field:  Name';

	/**
	 * @var array
	 * admin Form
	 */
	public static $adminForm = ['id' => 'adminForm'];

	/**
	 * @var array
	 */
	public static $usernameId = ['id' =>'username'];

	/**
	 * @var array
	 */
	public static $passwordId = ['id' => 'password'];

	/**
	 * @var array
	 */
	public static $loginButton = ['xpath'=>'//button[contains(text(),\'Log in\')]'];

	/**
	 * @var array
	 */
	public static $loginButtonCheckout = ['xpath' => '//button[contains(text(),\'Login\')]'];

	/**
	 * @var array
	 */
	public static $logoutButton = ['xpath' => '//div[@class=\'logout\']//button[contains(text(), \'Log out\')]'];

	/**
	 * @var array
	 * frontend page
	 */
	public static $categoryTitle = ['class' => 'redshopb-shop-category-title'];

	/**
	 * @var array
	 */
	public static $buttonAddToCart = ['xpath' => "//button[contains(@class, 'add-to-cart') and contains(@class, 'add-to-cart')]"];

	/**
	 * @var array
	 */
	public static $addToCartModal = ['id' => 'addToCartModalContent'];

	/**
	 * @var array
	 */
	public static $btnGoesToCheckout = ['xpath' => "//a[text()='Go to checkout']"];

	/**
	 * @var string
	 */
	public static $buttonCloseModalCart = '#addToCartModalContent .close';

	/**
	 * @var array
	 */
	public static $cartLink = ['id' => 'redshopb-cart-link'];

	/**
	 * @var array
	 */
	public static $shopCart = ['class' => "redshopb-shop-cart"];

	/**
	 * @var array
	 */
	public static $deleteSaveCart = "//a[@class='btn-remove-saved-cart']";

	/**
	 * @var array
	 */
	public static $priceTotalFinal = ['xpath' => '//div[@id=\'totalfinal\']'];

	/**
	 * @var array
	 */
	public static $currencyTotal = ['class' => 'oneCurrencyTotal'];

	/**
	 * @var array
	 */
	public static $cartCheckout = ['id' => "lc-shopping-cart-checkout"];

	/**
	 * @var array
	 */
	public static $buttonGoesCheckout = ['link' => "Go to checkout"];

	/**
	 * @var array
	 */
	public static $linkCartFirst = ['link' => '1. Cart'];

	/**
	 * @var string
	 */
	public static $statusModule = "#redshopb-cart-link";

	/**
	 * @var string
	 */
	public static $checkoutStatusModule = "#lc-shopping-cart-checkout";

	/**
	 * @var string
	 */
	public static $cartStatusModule = "#cart-productList";

	/**
	 * @var array
	 */
	public static $buttonSaveCart = ['id' => "save-cart-btn"];

	/**
	 * @var array
	 */
	public static $nameCartField = "//input[@class='input required']";

	/**
	 * @var array
	 */
	public static $saveCartAs =  "savedCartId_chzn";

	/**
	 * @var array
	 */
	public static $labelSaveCartXpath = ['xpath' => '//h3[@id=\'saveCartModalLabel\']'];

	/**
	 * @var string
	 */
	public static $labelSaveCartLabel = 'Save cart';

	/**
	 * @var array
	 */
	public static $cartTable = ['id' => 'savedCartsTable'];

	/**
	 * @var string
	 */
	public static $searchCart = 'filter_search_carts';

	/**
	 * @var array
	 */
	public static $btnCheckoutCart = ['xpath' => "//button[@class='btn btn-default btn-checkout-saved-cart']"];

	/**
	 * @var array
	 */
	public static $btnGoToCheckout = ['link' => "Go to checkout"];

	/**
	 * @var array
	 */
	public static $deliveryInfo = ['class' => 'delivery-info-title'];

	/**
	 * @var string
	 */
	public static $deliveryInfoContent = 'Delivery information';

	/**
	 * @var string
	 */
	public static $selectShippingMethodContent = 'Select shipping method';

	/**
	 * @var string
	 */
	public static $shippingRateId = "//input[@name='shipping_rate_id']";

	/**
	 * @var string
	 */
	public static $userBillingInfor = "#usebilling";

	/**
	 * @var string
	 * @since 2.8.0
	 */
	public static $invoiceEmail = "#invoice_email_toggle";

	/**
	 * @var string
	 * @since 2.8.0
	 */
	public static $invoiceEmailForm = "#invoice_email";

	/**
	 * @var string
	 */
	public static $emailBilling = "#email";

	/**
	 * @var string
	 */
	public static $nameBilling = "#name";

	/**
	 * @var string
	 */
	public static $name2Billing = "#name2";

	/**
	 * @var string
	 */
	public static $phoneBilling = "#phone";

	/**
	 * @var string
	 */
	public static $emailShipping = "(//p[@class=\"js-email\"])[1]";

	/**
	 * @var string
	 */
	public static $updateButtonBilling = "#update-btn";

	/**
	 * @var string
	 */
	public static $saveAsNewButton = "#save-as-new-btn";

	/**
	 * @var array
	 */
	public static $selectShippingMethod = ['css' => 'h4'];

	/**
	 * @var array
	 */
	public static $priceFinalXpath = ['xpath' => "//div[@id='totalfinal']/strong/span"];

	/**
	 * @var string
	 * Module Page
	 */
	public static $menusItem = 'New Item';

	/**
	 * @var string
	 */
	public static $titleMenu = "//input[@id='jform_title']";

	/**
	 * @var string
	 */
	public static $typeMenuValue = "//input[@id='jform_type']";

	/**
	 * @var string
	 */
	public static $selectButton = "//a[@href='#menuTypeModal']";

	/**
	 * @var string
	 */
	public static $itemType = 'Menu Item Type';

	/**
	 * @var string
	 */
	public static $debugSystem = 'Debug System';

	/**
	 * @var string
	 */
	public static $debugLanguage = 'Debug Language';

	/**
	 * redCORE page
	 * @var string
	 */
	public static $redcoreConfig = "redCORE Config";

	/**
	 * @var string
	 */
	public static $redcorePlugins = "redCORE paypal gateway plugin";

	/**
	 * @var string
	 */
	public static $apiOption = "Payment API options";

	/**
	 * @var string
	 */
	public static $paymentEnableID = "#jform_enable_payment";

	/**
	 * @var string
	 */
	public static $paymentEnable = "Enable payments";

	/**
	 * @var string
	 */
	public static $quantity = "//td[@class='field_quantity footable-visible']/div/input[@type='number']";

	/**
	 * @var string
	 */
	public static $quantityForm = "//td[@class='field_quantity footable-visible']";

	/**
	 * @var string
	 */
	public static $removeProductFromCart = "//a[@onclick=\"redSHOPB.shop.cart.removeItem(event);\"]";

	/**
	 * @var string
	 * @since 2.6.0
	 */
	public static $attributeQuantity = "//div[@id='productItemInput']/input[@class='input-xmini input-sm amountInput']";

	/**
	 * @param $position
	 * @return string
	 * The function for get attribute for add to cart
	 */
	public function attribute($position)
	{
		$xpath = "(//div[@class='footable-row-detail-value'])[".$position."]/input[@type='text']";

		return $xpath;
	}

	/**
	 * @param $value Shiping rate id
	 * @return array
	 */
	public function returnShipping($value)
	{
		$xpath = ['xpath' => "(//input[@name='shipping_rate_id'])[".$value."]"];

		return $xpath;
	}

	/**
	 * @param   $name
	 * @param $currencySymbol
	 * @param $price
	 * @return array
	 */
	public function shippingName($name, $currencySymbol, $price)
	{
		$xpath = ['xpath' => "//div[@id='redshopb-delivery-info-customer']//label[contains(normalize-space(), '" . $name . " (" . $currencySymbol . ' ' .$price.")')]"];

		return $xpath;
	}

	/**
	 * @param $value
	 * @return array
	 */
	public static function getXpathItem($value)
	{
		return ['xpath' => "//a[contains(text(), '" . $value . "')]"];
	}

	/**
	 * @param $value
	 * @return array
	 * @since 2.5.0
	 */
	public static function getXpathInsideLi($value)
	{
		return ['xpath' => "//li[contains(text(), '" . $value . "')]"];
	}

	/**
	 * @param $id
	 * @param $name
	 * @return string
	 */
	public static function getValueSelect($id, $name)
	{

		$xpath = "//div[@id='.$id.']/div/ul/li[contains(normalize-space(),' . $name . ')]";

		return $xpath;
	}

	/**
	 * @param $id
	 * @return string
	 */
	public static function getXpathClicks($id)
	{
		$xpath = "//div[@id='.$id.']/a";

		return $xpath;
	}
}