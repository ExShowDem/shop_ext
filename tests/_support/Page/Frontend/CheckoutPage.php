<?php

namespace Page\Frontend;

class CheckoutPage extends Redshopb2bPage
{
	/**
	 * @var string
	 */
	public static $cartURL = 'index.php/component/redshopb/shop/cart';
	
	/**
	 * @var string
	 */
	public static $deliveryURL = 'index.php/component/redshopb/shop/delivery';
	
	/**
	 * @var string
	 */
	public static $confirmURL = 'index.php/component/redshopb/shop/confirm';
	
	/**
	 * @var string
	 */
	public static $loginForm = 'Returning customer? Please log in';
	
	/**
	 * @var string
	 */
	public static $signupform = 'Sign up here';
	
	/**
	 * @var string
	 */
	public static $firstName = "#jform_name1";
	
	/**
	 * @var string
	 */
	public static $lastName = "#jform_name2";
	
	/**
	 * @var string
	 */
	public static $emailUser = "#jform_email";
	
	/**
	 * @var string
	 */
	public static $userName = "//input[@id='jform_username']";
	
	/**
	 * @var string
	 */
	public static $passWord = "//input[@id='jform_password']";
	
	/**
	 * @var string
	 */
	public static $passWordConfirm = "//input[@id='jform_password2']";
	
	/**
	 * @var string
	 */
	public static $billingAddressFirst = "//input[@id='jform_billing_address']";
	
	/**
	 * @var string
	 */
	public static $billingAddressSecond = "//input[@id='jform_billing_address2']";
	
	/**
	 * @var string
	 */
	public static $billingZip = "//input[@id='jform_billing_zip']";
	
	/**
	 * @var string
	 */
	public static $billingCity = "//input[@id='jform_billing_city']";
	
	/**
	 * @var string
	 */
	public static $billingPhone = "//input[@id='jform_billing_phone']";
	
	/**
	 * @var string
	 */
	public static $billingCountry = 'jform_billing_country_id';
	
	/**
	 * @var string
	 */
	public static $userBillingForShipping = "Use billing address for shipping";
	
	/**
	 * @var string
	 */
	public static $shippingAddressCheckbox = "//input[@id='jform_usebilling']";
	
	/**
	 * @var string
	 */
	public static $shippingName1 = "//input[@id='jform_shipping_name1']";
	
	/**
	 * @var string
	 */
	public static $shippingName2 = "//input[@id='jform_shipping_name2']";
	
	/**
	 * @var string
	 */
	public static $shippingAddress1 = "//input[@id='jform_shipping_address']";
	
	/**
	 * @var string
	 */
	public static $shippingAddress2 = "//input[@id='jform_shipping_address2']";
	
	/**
	 * @var string
	 */
	public static $shippingZipCode = "//input[@id='jform_shipping_zip']";
	
	/**
	 * @var string
	 */
	public static $shippingCity = "//input[@id='jform_shipping_city']";
	
	/**
	 * @var string
	 */
	public static $shippingPhone = "//input[@id='jform_shipping_phone']";
	
	/**
	 * @var string
	 */
	public static $shippingCountry = "//input[@id='jform_shipping_country_id']";
	
	/**
	 * @var string
	 */
	public static $registerButon = 'Register';
	
	/**
	 * @var string
	 */
	public static $cancelButton = 'Cancel';
	
	/**
	 * @var string
	 */
	public static $checkoutGuestForm = 'Checkout as guest';
	
	/**
	 * @var string
	 */
	public static $deliveryAddress = "Enter Delivery Address";
	
	/**
	 * @var string
	 */
	public static $vendorInformation = "Vendor Information";
	
	/**
	 * @var string
	 */
	public static $nameGuest = "//input[@id='name']";
	
	/**
	 * @var string
	 */
	public static $emailGuest = "//input[@id='email']";
	
	/**
	 * @var string
	 */
	public static $phoneGuest = "//input[@id='phone']";
	
	/**
	 * @var string
	 */
	public static $guestAddress = "//input[@id='address']";
	
	/**
	 * @var string
	 */
	public static $guestAddress2 = "//input[@id='address2']";
	
	/**
	 * @var string
	 */
	public static $guestCity = "//input[@id='city']";
	
	/**
	 * @var string
	 */
	public static $guestZip = "//input[@id='zip']";
	
	/**
	 * @var string
	 */
	public static $guestCountry = "country_id";
	
	/**
	 * @var string
	 */
	public static $InfoForm = "Additional Info";
	
	/**
	 * @var string
	 */
	public static $requisition = "#requisition";
	
	/**
	 * @var string
	 */
	public static $comment = "#comment";
	
	/**
	 * @var string
	 */
	public static $contactContent = "Use contact person email as invoice email";
	
	/**
	 * @var string
	 */
	public static $contactPersonEmail = "#invoice_email_toggle";
	
	/**
	 * @var string
	 */
	public static $invoiceMail = "#invoice_email";

	// message
	/**
	 * @var string
	 */
	public static $createUserSuccessMessage = "User Registered Successfully!";
	
	/**
	 * @var string
	 */
	public static $changeDeliveryAddress = 'Change Delivery Address';
	
	/**
	 * @var string
	 */
	public static $deliveryInfo = "Delivery information";

	/**
	 * @param $text
	 * @return string
	 */
	public static function taxNotice($text)
	{
		$xpath = "//strong[contains(text(),'$text')]";
		return$xpath;
	}

	/**
	 * @param $position
	 * @return string
	 */
	public function returnIconDelete($position)
	{
		$xpath = "//tr[$position]/td/button[@onclick=\"redSHOPB.cart.removeItemFromShoppingCart(event);\"]";

		return $xpath;
	}

	/**
	 * @param $value
	 * @return array
	 */
	public static function getXpathValue($value)
	{
		return ['xpath' => "//p[contains(text(), '" . $value . "')]"];
	}
}