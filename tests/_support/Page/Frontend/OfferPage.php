<?php
/**
 * @package     Aesir-E-commerce
 * @subpackage  Page
 * @copyright   Copyright (C) 2016 - 2018 Aesir-E-commerce. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Page\Frontend;
class OfferPage extends Redshopb2bPage
{

	/**
	 * @var string
	 */
	public static $URL = '/index.php?option=com_redshopb&view=offers';

	/**
	 * @var string
	 */
	public static $URLForEmployee = '/index.php?option=com_redshopb&view=myoffers';

	/**
	 * @var string
	 */
	public static $labelOfferName = 'Offer Name';

	/**
	 * @var string
	 */
	public static $labelExpirationDate = 'Add expiration date';

	/**
	 * @var string
	 */
	public static $labelDate = ' Expiration date';

	/**
	 * @var string
	 */
	public static $labelCustomerType = 'Customer type';

	/**
	 * @var string
	 */
	public static $labelCustomer = 'Customer';

	/**
	 * @var string
	 */
	public static $labelCollection = 'Collection';

	/**
	 * @var string
	 */
	public static $searchOffer = 'filter_search_offers';

	/**
	 * @var string
	 */
	public static $productOffer = 'filter_search_products';

	/**
	 * @var string
	 */
	public static $searchMyOffer = 'filter_search_myoffers';

	/**
	 * @var array
	 */
	public static $searchMyOfferId = ['id' => 'filter_search_myoffers'];

	/**
	 * @var array
	 */
	public static $companyLabelXpath = ['xpath' => "//div[@id='jform_company_id_chzn']/a"];

	/**
	 * @var string
	 */
	public static $companySearch = 'jform_company_id';

	/**
	 * @var string
	 */
	public static $departmentSearch = 'jform_department_id';

	/**
	 * @var string
	 */
	public static $employeeSearch = 'jform_user_id';

	/**
	 * @var string
	 */
	public static $statusSearch = 'jform_status';

	/**
	 * @var array
	 */
	public static $searchOfferId = ['id' => 'filter_search_offers'];

	/**
	 * @var array
	 */
	public static $searchProductsXpath = ['xpath' => '//input[@id=\'filter_search_products\']'];

	/**
	 * @var array
	 */
	public static $searchProductId = ['id' => 'filter_search_products'];

	/**
	 * @var string
	 */
	public static $detailOfferTab = "//ul[@id='offerTabs']/li[1]/a";

	/**
	 * @var array
	 */
	public static $offerProductsTab = ['xpath' => '//ul[@id=\'offerTabs\']/li[2]/a'];

	/**
	 * @var array
	 */
	public static $offerProductAtOffer =  ['xpath' => '//ul[@id=\'offerTabs\']/li[3]/a'];

	/**
	 * @var array
	 */
	public static $firstSKUXpathAtOfferTab = ['xpath' => '//td[2]/i'];

	/*
	 *
	 */
	public static $secondSKUXpathAtOfferTab = ['xpath' => '(//td[2]/i)[2]'];

	/**
	 * @var array
	 */
	public static $firstPriceFinalOfferTab = ['xpath' => '//td[7]'];

	/**
	 * @var array
	 */
	public static $firstQuantityOffTab = ['xpath' => '//td[6]/div/input'];

	/**
	 * @var array
	 */
	public static $firstPriceXpath = ['xpath' => '//tr[@id=\'product-row-1\']/td[3]'];

	/**
	 * @var array
	 */
	public static $buttonAddFirstProduct = ['xpath' => '//td[1]/a'];

	/**
	 * @var array
	 */
	public static $secondSKUXpath = ['xpath' => '//tr[@id=\'product-row-2\']/td[2]/i'];

	/**
	 * @var array
	 */
	public static $secondPriceXpath = ['xpath' => '//tr[@id=\'product-row-2\']/td[3]'];

	/**
	 * @var array
	 */
	public static $buttonAddSecondProduct = ['xpath' => '//tr[@id=\'product-row-2\']/td[1]/a'];

	// for user login to checkout
	//content
	/**
	 * @var string
	 */
	public static $accept = 'Accept';
	// button
	/**
	 * @var array
	 */
	public static $buttonAccept  = ['xpath' => '(//div[@class=\'pull-right\'])[2]/a[1]'];

	/**
	 * @var array
	 */
	public static $buttonReject  = ['xpath' => '(//div[@class=\'pull-right\'])[2]/a[2]'];

	//xpath
	/**
	 * @var array
	 */
	public static $buttonYesForm = "//button[@onclick=\"Joomla.submitform('myoffer.checkoutCart', document.getElementById('modalOfferForm'))\"]";

	/**
	 * @var array
	 */
	public static $buttonNoForm = ['xpath' => '//form[@id=\'modalOfferForm\']/button[2]'];

	/**
	 * @var array
	 */
	public static $buttonCancel= ['xpath' => '//form[@id=\'modalOfferForm\']/button[3]'];

	/**
	 * @var string
	 */
	public static $yesOffer = "//button[@onclick=\"Joomla.submitform('myoffer.checkoutCart', document.getElementById('modalOfferForm'))\"]";

	/**
	 * @var string
	 */
	public static $requestOffer = "//a[@data-original-title=\"Send letter\"]";

	/**
	 * @var string
	 */
	public static $offerName = "//input[@id=\"jform_name\"]";

	/**
	 * @var string
	 */
	public static $offerDescription = "//textarea[@id=\"jform_comments\"]";

	/**
	 * @var string
	 */
	public static $submitSendRequest = "//button[@class=\"btn btn-success validate\"]";

	/**
	 * @var string
	 */
	public static $messageSuccess = "Your Offer has been Requested successfully.";

	/**
	 * @var string
	 */
	public static $messageEditSuccess = "Item saved.";

	/**
	 * @var string
	 */
	public static $discount = "//input[@id=\"jform_discount\"]";

	/**
	 * @var string
	 */
	public static $myOfferModal = "#myOfferModal";

	/**
	 * @param $name
	 * @return array
	 */
	public function skuProduct($name)
	{
		$xpath = ['xpath' =>'//i[contains(normalize-space(),'.$name.')]'];

		return $xpath;
	}
}