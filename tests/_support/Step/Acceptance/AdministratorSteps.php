<?php
namespace Step\Acceptance;
use \Page\Acceptance\AdministratorPage as AdministratorPage;
use Symfony\Component\Finder\Adapter\AdapterInterface;

class AdministratorSteps extends \Step\Acceptance\redshopb2b
{

	/**
	 * @param array $configureGlobal
	 * @throws \Exception
	 */
	public function configureGlobal($configureGlobal = array())
	{
		$I = $this;
		$I->amOnPage(AdministratorPage::$configurationUrl);
		$I->waitForElement(AdministratorPage::$linkB2B, 30);
		$I->click(AdministratorPage::$linkB2B);
		$I->waitForElement(AdministratorPage::$configurationButton,30);
		$I->click(AdministratorPage::$configurationButton);
		$I->waitForElement(AdministratorPage::$globalForm,30);

		//choice default country
		if (isset($configureGlobal['country']))
		{
			$I->selectOptionInChosen(AdministratorPage::$labelCountry,$configureGlobal['country']);
		}

		//choice bootstrap
		if(isset($configureGlobal['bootstrap']))
		{
			$I->selectOptionInChosen(AdministratorPage::$labelBootstrap,$configureGlobal['bootstrap']);
		}

		if(isset($configureGlobal['availableCountry']))
		{
			$I->selectOptionInChosen(AdministratorPage::$labelAvailableCountry,$configureGlobal['availableCountry']);
		}

		if(isset($configureGlobal['allowCountryRegister']))
		{
			$I->selectOptionInRadioField(AdministratorPage::$labelAllowCountryRegister,$configureGlobal['allowCountryRegister']);
		}

		if(isset($configureGlobal['addressRequired']))
		{
			$I->selectOptionInRadioField(AdministratorPage::$labelAddressRequired, $configureGlobal['addressRequired']);
		}

		if(isset($configureGlobal['billingPhone']))
		{
			$I->selectOptionInRadioField(AdministratorPage::$labelBillingPhone, $configureGlobal['billingPhone']);
		}

		if(isset($configureGlobal['shippingPhone']))
		{
			$I->selectOptionInRadioField(AdministratorPage::$labelShippingPhone, $configureGlobal['shippingPhone']);
		}

		if(isset($configureGlobal['registerFlow']))
		{
			$I->waitForElement(AdministratorPage::$idShippingPhone, 30);
			$I->scrollTo(AdministratorPage::$idShippingPhone);
			$I->selectOptionInChosen(AdministratorPage::$labelRegisterFlow,$configureGlobal['registerFlow']);
		}

		// activation email template
		if (isset($configureGlobal['activationEmail']))
		{
			$I->waitForElement(AdministratorPage::$idRegisterFlow, 30);
			$I->scrollTo(AdministratorPage::$idRegisterFlow);
			$I->selectOptionInChosen(AdministratorPage::$labelActivationEmail,$configureGlobal['activationEmail']);
		}

		//Encryption key
		if (isset($configureGlobal['encryptionKey']))
		{
			$I->waitForElement(AdministratorPage::$idActivationEmailTemplate, 30);
			$I->scrollTo(AdministratorPage::$idActivationEmailTemplate);
			$I->fillField(AdministratorPage::$encryptionKey,$configureGlobal['encryptionKey']);
		}

		//related SKU name
		if (isset($configureGlobal['relatedSKU']))
		{
			$I->waitForElement(AdministratorPage::$idEncryptionKey, 30);
			$I->scrollTo(AdministratorPage::$idEncryptionKey);
			$I->fillField(AdministratorPage::$relatedSKU,$configureGlobal['relatedSKU']);
		}

		//webservices
		if (isset($configureGlobal['setWebservices']))
		{
			$I->waitForElement(AdministratorPage::$idSetWebservices, 30);
			$I->scrollTo(AdministratorPage::$idSetWebservices);
			$I->selectOptionInRadioField(AdministratorPage::$labelSetWebservices,$configureGlobal['setWebservices']);
		}

		//webservice permission
		if (isset($configureGlobal['userPermission']))
		{
			$I->wantToTest('Configuration user Permisson is' . $configureGlobal['userPermission']);
			$I->selectOptionInRadioField(AdministratorPage::$labelUserPermission,$configureGlobal['userPermission']);
		}

		//webservice permission
		if (isset($configureGlobal['processProductDescription']))
		{
			$I->selectOptionInRadioField(AdministratorPage::$labelProcessProductDescription,$configureGlobal['processProductDescription']);
		}

		//log out
		if (isset($configureGlobal['warningLogout']))
		{
			$I->selectOptionInRadioField(AdministratorPage::$labelWarningLogout,$configureGlobal['warningLogout']);
		}

		if (isset($configureGlobal['warningTextCart']))
		{
			$I->wantToTest('add warning text cart');
			$I->fillField(AdministratorPage::$warningTextCart,$configureGlobal['warningTextCart']);
		}

		// Make sure it scrolls to the bottom of the page, so it can select successfully
		$I->scrollTo(['css' => '#global > div:last-of-type']);

		//rich snippet
		if (isset($configureGlobal['richSnippet']))
		{
			$I->selectOptionInRadioField(AdministratorPage::$labelRichSnippet,$configureGlobal['richSnippet']);
		}

		$I->scrollUp();
		$I->waitForElement(AdministratorPage::$saveButton,30);
		$I->click(AdministratorPage::$saveButton);
		$I->waitForElement(AdministratorPage::$systemContainer,30);
		$I->waitForText(AdministratorPage::$saveItemSuccess, 30,AdministratorPage::$systemContainer );
	}

	/**
	 * @param array $settingShop
	 * @throws \Exception
	 */
	public function settingShop($settingShop = array())
	{
		$I=$this;
		$I->amOnPage(AdministratorPage::$configurationUrl);
		$I->waitForElement(AdministratorPage::$linkB2B, 30);
		$I->click(AdministratorPage::$linkB2B);
		$I->waitForElement(AdministratorPage::$configurationButton,30);
		$I->click(AdministratorPage::$configurationButton);
		$I->waitForElement(AdministratorPage::$shopTab,30);
		$I->click(AdministratorPage::$shopTab);

		//show subcategory Products
		if(isset($settingShop['showCategoryProduct']))
		{
			$I->selectOptionInRadioField(AdministratorPage::$labelShowCategoryProduct,$settingShop['showCategoryProduct']);
		}

		$I->comment('Setup Ajax Categories');
		if(isset($settingShop['showCategoryProduct']))
		{
			$I->selectOptionInRadioField(AdministratorPage::$labelAjaxCategory,$settingShop['ajaxCategory']);
		}
		
		if(isset($settingShop['dayProduct']))
		{
			$I->fillField(AdministratorPage::$dayProductNews,$settingShop['dayProduct']);
		}
		
		$I->comment('Setup show product print option');
		if(isset($settingShop['showProductPrintOption']))
		{
			$I->selectOptionInRadioField(AdministratorPage::$labelShowProductPrintOption,$settingShop['showProductPrintOption']);
		}

		$I->comment('Setup choice companies using collections');
		if(isset($settingShop['compareUsingOption']))
		{
			$I->selectOptionInChosenjs(AdministratorPage::$labelCompareUsingOption,$settingShop['compareUsingOption']);
		}

		//show shop as
		$I->comment('Setup show shop as');
		if(isset($settingShop['showShopAs']))
		{
			$I->selectOptionInRadioField(AdministratorPage::$labelShowShopAs,$settingShop['showShopAs']);
		}
		$I->comment('show default product layout');
		if(isset($settingShop['defaultLayout']))
		{
			$I->selectOptionInRadioField(AdministratorPage::$labelDefaultLayout,$settingShop['defaultLayout']);
		}
		$I->comment('show default accessory layout');

		// Scroll to bottom of page
		$I->scrollTo(['css' => '#shop > div:last-of-type']);
		
		if(isset($settingShop['defaultAccessory']))
		{
			$I->selectOptionInRadioField(AdministratorPage::$labelDefaultAccessory,$settingShop['defaultAccessory']);
		}

		$I->comment('show inline category filter');
		if(isset($settingShop['showInlineCategory']))
		{
			$I->selectOptionInRadioField(AdministratorPage::$labelShowInlineCategory,$settingShop['showInlineCategory']);
		}

		$I->comment('show inline collection filter');
		if(isset($settingShop['showShopCollection']))
		{
			$I->selectOptionInRadioField(AdministratorPage::$labelShowShopCollection,$settingShop['showShopCollection']);
		}
		$I->scrollUp();
		$I->waitForElement(AdministratorPage::$saveButton,30);
		$I->click(AdministratorPage::$saveButton);
		$I->waitForElement(AdministratorPage::$systemContainer,30);
		$I->waitForText(AdministratorPage::$saveItemSuccess, 30,AdministratorPage::$systemContainer );
	}

	/**
	 * @param array $settingCartCheckout
	 * @throws \Exception
	 */
	public function settingCartCheckout($settingCartCheckout = array())
	{
		$I=$this;
		$I->amOnPage(AdministratorPage::$configurationUrl);
		$I->waitForElement(AdministratorPage::$linkB2B, 30);
		$I->click(AdministratorPage::$linkB2B);
		$I->waitForElement(AdministratorPage::$configurationButton,30);
		$I->click(AdministratorPage::$configurationButton);
		$I->waitForElement(AdministratorPage::$cartCheckoutTab,30);
		$I->click(AdministratorPage::$cartCheckoutTab);

		$I->comment('Setup add to cart notification type');
		if(isset($settingCartCheckout['addToCart']))
		{
			$I->selectOptionInRadioField(AdministratorPage::$labelAddToCart,$settingCartCheckout['addToCart']);
		}

		$I->comment('SetupCart counting');
		if (isset($settingCartCheckout['cartBy']))
		{
			$I->selectOptionInChosenjs(AdministratorPage::$labelCartCounting,$settingCartCheckout['cartBy']);
		}

		$I->comment('SetupCart choice show image in cart ');
		if (isset($settingCartCheckout['$showImageInCart']))
		{
			$I->selectOptionInRadioField(AdministratorPage::$labelShowImageInCart,$settingCartCheckout['showImageInCart']);
		}

		$I->comment('Setup choice show Tax inside cart');
		if(isset($settingCartCheckout['showTaxInCart']))
		{
			$I->selectOptionInRadioField(AdministratorPage::$labelShowTaxInCart,$settingCartCheckout['showTaxInCart']);
		}

		$I->comment('Setup choice user register');
		if(isset($settingCartCheckout['checkoutRegister']))
		{
			$I->selectOptionInChosen(AdministratorPage::$labelCheckoutRegister,$settingCartCheckout['checkoutRegister']);
		}
		
		if(isset($settingCartCheckout['guestUserDefault']))
		{
			$I->fillField(AdministratorPage::$guestUserDefault,$settingCartCheckout['guestUserDefault']);
		}

		$I->comment('Setup Checkout Mode');
		if (isset($settingCartCheckout['checkoutMode']))
		{
			$I->selectOptionInRadioField(AdministratorPage::$labelCheckoutMode,$settingCartCheckout['checkoutMode']);
		}

		$I->comment('setup choice show Image Product Checkout ');
		if (isset($settingCartCheckout['showImageProductCheckout']))
		{
			$I->selectOptionInChosenjs(AdministratorPage::$labelShowImageProductCheckout,$settingCartCheckout['showImageProductCheckout']);
		}

		// Scroll to bottom of page
		$I->scrollTo(['css' => '#cart > div:last-of-type']);

		$I->comment('Setup show Stock Present ');
		if (isset($settingCartCheckout['showStockPresent']))
		{
			$I->selectOptionInRadioField(AdministratorPage::$labelShowStockPresent,$settingCartCheckout['showStockPresent']);
		}

		$I->comment('Setup enable Shipping');
		if (isset($settingCartCheckout['enableShipping']))
		{
			$I->selectOptionInRadioField(AdministratorPage::$labelEnableShipping,$settingCartCheckout['enableShipping']);
		}

		$I->comment('setup time shipping');
		if (isset($settingCartCheckout['timeShipping']))
		{
			$I->selectOptionInRadioField(AdministratorPage::$labelTimeShipping,$settingCartCheckout['timeShipping']);
		}

		$I->comment('setup Clean cart before add products from favourite list ');
		if (isset($settingCartCheckout['clearCartBeforeAddFavourite']))
		{
			$I->selectOptionInRadioField(AdministratorPage::$labelClearCartBeforeAddFavourite,$settingCartCheckout['clearCartBeforeAddFavourite']);
		}

		$I->comment('setup Redirect after add to cart  ');
		if (isset($settingCartCheckout['redirectAfterAdd']))
		{
			$I->selectOptionInRadioField(AdministratorPage::$labelRedirectAfterAdd,$settingCartCheckout['redirectAfterAdd']);
		}

		$I->comment('Go to checkout when add favorite list to cart');
		if (isset($settingCartCheckout['favoriteCart']))
		{
			$I->selectOptionInRadioField(AdministratorPage::$favoriteCart,$settingCartCheckout['favoriteCart']);
		}
		
		$I->comment('Quick order checkout redirect ');
		if (isset($settingCartCheckout['checkoutRedirect']))
		{
			$I->selectOptionInRadioField(AdministratorPage::$checkoutRedirect,$settingCartCheckout['checkoutRedirect']);
		}
		
		$I->comment('Save cart for user ');
		if (isset($settingCartCheckout['saveCart']))
		{
			$I->selectOptionInRadioField(AdministratorPage::$saveCart,$settingCartCheckout['saveCart']);
		}
		
		$I->comment('Invoice Mail ');
		if(isset($settingCartCheckout['invoiceMail']))
		{
			$I->selectOptionInRadioField(AdministratorPage::$invoiceEmail, $settingCartCheckout['invoiceMail']);
		}

		$I->comment('Save to cart by ');
		if(isset($settingCartCheckout['saveToCartBy']))
		{
			$I->selectOptionInRadioField(AdministratorPage::$saveToCartBy, $settingCartCheckout['saveToCartBy']);
		}

		$I->scrollUp();
		$I->waitForElement(AdministratorPage::$saveButton,30);
		$I->click(AdministratorPage::$saveButton);
		$I->waitForElement(AdministratorPage::$systemContainer,30);
		$I->waitForText(AdministratorPage::$saveItemSuccess, 30,AdministratorPage::$systemContainer );
	}
	
	/**
	 * @param array $settingImage
	 */
	public function settingImage($settingImage = array())
	{
		$I=$this;
		$I->amOnPage(AdministratorPage::$configurationUrl);
		$I->waitForElement(AdministratorPage::$linkB2B, 30);
		$I->click(AdministratorPage::$linkB2B);
		$I->waitForElement(AdministratorPage::$configurationButton,30);
		$I->click(AdministratorPage::$configurationButton);
		$I->waitForElement(AdministratorPage::$imageTab,30);
		$I->click(AdministratorPage::$imageTab);

		if(isset($settingImage['storeMaxWidth']))
		{
			$I->fillField(AdministratorPage::$storeMaxWidth,$settingImage['storeMaxWidth']);
		}
		if(isset($settingImage['storeMaxHeight']))
		{
			$I->fillField(AdministratorPage::$storeMaxHeight, $settingImage['storeMaxHeight']);
		}

		$I->comment('setup Select images optimization');
		if(isset($settingImage['storeOptimize']))
		{
			$I->selectOptionInRadioField(AdministratorPage::$labelStoreOptimize, $settingImage['storeOptimize']);
		}

		$I->comment('setup Select images optimization');
		if (isset($settingImage['imageOptimization']))
		{
			$I->selectOptionInChosen(AdministratorPage::$labelImageOptimization,$settingImage['imageOptimization']);
		}

		if (isset($settingImage['thumbnailWidth']))
		{
			$I->fillField(AdministratorPage::$thumbnailWidth,$settingImage['thumbnailWidth']);
		}

		if (isset($settingImagep['thumbnailHeight']))
		{
			$I->fillField(AdministratorPage::$thumbnailHeight,$settingImage['thumbnailHeight']);
		}
		
		if (isset($settingImage['gridImageWidth']))
		{
			$I->fillField(AdministratorPage::$gridImageWidth,$settingImage['gridImageWidth']);
		}
		
		
		if (isset($settingImage['gridImageHeight']))
		{
			$I->fillField(AdministratorPage::$gridImageHeight, $settingImage['gridImageHeight']);
		}
		
		if (isset($settingImage['productImageWidth']))
		{
			$I->fillField(AdministratorPage::$productImageWidth, $settingImage['productImageWidth']);
		}
		
		if (isset($settingImage['productImageHeight']))
		{
			$I->fillField(AdministratorPage::$productImageHeight, $settingImage['productImageHeight']);
		}
		if (isset($settingImage['categoryImageWidth']))
		{
			$I->fillField(AdministratorPage::$categoryImageWidth, $settingImage['categoryImageWidth']);
		}
		if (isset($settingImage['categoryImageHeight']))
		{
			$I->fillField(AdministratorPage::$categoryImageHeight, $settingImage['categoryImageHeight']);
		}
		if (isset($settingImage['manufactureImageWidth']))
		{
			$I->fillField(AdministratorPage::$manufactureImageWidth, $settingImage['manufactureImageWidth']);
		}
		if (isset($settingImage['manufactureImageHeight']))
		{
			$I->fillField(AdministratorPage::$manufactureImageHeight, $settingImage['manufactureImageHeight']);
		}
		
		$I->scrollUp();
		$I->waitForElement(AdministratorPage::$saveButton,30);
		$I->click(AdministratorPage::$saveButton);
		$I->waitForElement(AdministratorPage::$systemContainer,30);
		$I->waitForText(AdministratorPage::$saveItemSuccess, 30,AdministratorPage::$systemContainer );

	}

	/**
	 * @param array $breadcrumbs
	 * @throws \Exception
	 */
	public function breadcrumbs($breadcrumbs = array()){
		$I=$this;
		$I->amOnPage(AdministratorPage::$configurationUrl);
		$I->waitForElement(AdministratorPage::$linkB2B, 30);
		$I->click(AdministratorPage::$linkB2B);
		$I->waitForElement(AdministratorPage::$configurationButton,30);
		$I->click(AdministratorPage::$configurationButton);
		$I->waitForElement(AdministratorPage::$imageTab,30);
		$I->click(AdministratorPage::$breadcrumbTab);

		$I->comment('setup Select images optimization');
		if (isset($breadcrumbs['showBreadcrumbs']))
		{
			$I->selectOptionInRadioField(AdministratorPage::$labelShowBreadcrumbs,$breadcrumbs['showBreadcrumbs']);
		}

		$I->comment('setup Impersonation breadcrumbs');
		if (isset($breadcrumbs['impersonation']))
		{
			$I->selectOptionInChosen(AdministratorPage::$labelImpersonation, $breadcrumbs['impersonation']);
		}
		
		$I->comment('setup Impersonation breadcrumbs');
		if (isset($breadcrumbs['youAreHere']))
		{
			$I->selectOptionInRadioField(AdministratorPage::$labelYouAreHere, $breadcrumbs['youAreHere']);
		}
		
		$I->comment('setup Show Home');
		if (isset($breadcrumbs['showHome']))
		{
			$I->selectOptionInRadioField(AdministratorPage::$labelShowHome, $breadcrumbs['showHome']);
		}
		
		if (isset($breadcrumbs['textForHomeEntry']))
		{
			$I->fillField(AdministratorPage::$textForHomeEntry, $breadcrumbs['textForHomeEntry']);
		}
		if (isset($breadcrumbs['textSeparator']))
		{
			$I->fillField(AdministratorPage::$textSeparator, $breadcrumbs['textSeparator']);
		}
		
		$I->scrollUp();

		$I->waitForElement(AdministratorPage::$saveButton,30);
		$I->click(AdministratorPage::$saveButton);
		$I->waitForElement(AdministratorPage::$systemContainer,30);
		$I->waitForText(AdministratorPage::$saveItemSuccess, 30,AdministratorPage::$systemContainer );
	}

	/**
	 * @param array $productSearch
	 * @throws \Exception
	 */
	public function productSearch($productSearch = array())
	{
		$I=$this;
		$I->amOnPage(AdministratorPage::$configurationUrl);
		$I->waitForElement(AdministratorPage::$linkB2B, 30);
		$I->click(AdministratorPage::$linkB2B);
		$I->waitForElement(AdministratorPage::$configurationButton,30);
		$I->click(AdministratorPage::$configurationButton);
		$I->waitForElement(AdministratorPage::$productSearchTab,30);
		$I->click(AdministratorPage::$productSearchTab);

		$I->comment('setup Search method ');
		if (isset($productSearch['search']))
		{
			$I->selectOptionInChosenjs(AdministratorPage::$labelSearch,$productSearch['search']);
		}

		$I->comment('setup Synonyms Support  ');
		if (isset($productSearch['synonymsSupport']))
		{
			$I->selectOptionInRadioField(AdministratorPage::$labelSynonymsSupport, $productSearch['synonymsSupport']);
		}
		
		$I->comment('setup Enable Stemmer ');
		if (isset($productSearch['enableStemmer']))
		{
			$I->selectOptionInRadioField(AdministratorPage::$labelenableStemmer, $productSearch['enableStemmer']);
		}
		
		$I->comment('setup Stemmer   ');
		if (isset($productSearch['stemmer']))
		{
			$I->selectOptionInChosenjs(AdministratorPage::$labelStemmer, $productSearch['stemmer']);
		}
		
		$I->scrollTo(['css' => '#product_search > div:last-of-type']);

		$I->comment('setup Stop if found   ');
		if (isset($productSearch['stopIfFound']))
		{
			$I->selectOptionInRadioField(AdministratorPage::$labelStopIfFound, $productSearch['stopIfFound']);
		}
		
		$I->scrollUp();

		$I->waitForElement(AdministratorPage::$saveButton,30);
		$I->click(AdministratorPage::$saveButton);
		$I->waitForElement(AdministratorPage::$systemContainer,30);
		$I->waitForText(AdministratorPage::$saveItemSuccess, 30,AdministratorPage::$systemContainer );

	}

	/**
	 * @param array $settingPage
	 * @throws \Exception
	 */
	public function settingPage($settingPage = array())
	{
		$I=$this;
		$I->amOnPage(AdministratorPage::$configurationUrl);
		$I->waitForElement(AdministratorPage::$linkB2B, 30);
		$I->click(AdministratorPage::$linkB2B);
		$I->waitForElement(AdministratorPage::$configurationButton,30);
		$I->click(AdministratorPage::$configurationButton);
		$I->waitForElement(AdministratorPage::$productSearchTab,30);
		$I->click(AdministratorPage::$paginationTab);

		$I->comment('setup No pagination');
		if(isset($settingPage['']))
		{
			$I->selectOptionInRadioField(AdministratorPage::$labelNoPage,$settingPage['noPage']);
		}

		if(isset($settingPage['categoryPerPage']))
		{
			$I->fillField(AdministratorPage::$categoryPerPage, $settingPage['categoryPerPage']);
		}
		
		if(isset($settingPage['categoryProductPerPage']))
		{
			$I->fillField(AdministratorPage::$categoryProductPerPage,$settingPage['categoryProductPerPage']);
		}
		
		if(isset($settingPage['departmentPerPage']))
		{
			$I->fillField(AdministratorPage::$departmentPerPage,$settingPage['departmentPerPage']);
		}

		$I->scrollTo(['css' => '#pagination > div:last-of-type']);

		$I->comment('setup No pagination   ');
		if(isset($settingPage['numberOfColumn']))
		{
			$I->selectOptionInChosenjs(AdministratorPage::$labelnumberOfColumn,$settingPage['numberOfColumn']);
		}
		
		if(isset($settingPage['numberOfCategory']))
		{
			$I->fillField(AdministratorPage::$numberOfCategory,$settingPage['numberOfCategory']);
		}
		$I->scrollUp();

		$I->waitForElement(AdministratorPage::$saveButton,30);
		$I->click(AdministratorPage::$saveButton);
		$I->waitForElement(AdministratorPage::$systemContainer,30);
		$I->waitForText(AdministratorPage::$saveItemSuccess, 30,AdministratorPage::$systemContainer );
	}

	/**
	 * @param array $vatSetting
	 * @throws \Exception
	 */
	public function vatSetting($vatSetting = array())
	{
		$I=$this;
		$I->amOnPage(AdministratorPage::$configurationUrl);
		$I->waitForElement(AdministratorPage::$linkB2B, 30);
		$I->click(AdministratorPage::$linkB2B);
		$I->waitForElement(AdministratorPage::$configurationButton,30);
		$I->click(AdministratorPage::$configurationButton);
		$I->waitForElement(AdministratorPage::$vatTab,30);
		$I->click(AdministratorPage::$vatTab);
		
		$I->comment('setup Default currency  ');
		
		if (isset($vatSetting['defaultCurrency']))
		{
			$I->selectOptionInChosen(AdministratorPage::$labelDefaultCurrency,$vatSetting['defaultCurrency']);
		}
		
		if (isset($vatSetting['showPrice']))
		{
			$I->comment('setup Show prices');
			$I->selectOptionInRadioField(AdministratorPage::$labelShowPrice,$vatSetting['showPrice']);
		}
		
		if (isset($vatSetting['outletProduct']))
		{
			
			$I->comment('setup  Show Outlet price');
			$I->selectOptionInRadioField(AdministratorPage::$labelOutletProduct,$vatSetting['outletProduct']);
		}
		
		if (isset($vatSetting['lowestProduct']))
		{
			
			$I->comment('setup  Always use lowest product price');
			$I->selectOptionInRadioField(AdministratorPage::$labelLowestProduct,$vatSetting['lowestProduct']);
		}
		if (isset($vatSetting['offSystem']))
		{
			
			$I->comment('setup  Offer system');
			$I->selectOptionInChosenjs(AdministratorPage::$labelOffSystem,$vatSetting['offSystem']);
		}
		
		if (isset($vatSetting['vat']))
		{
			
			$I->comment('setup  TAX/VAT Calculation based on ');
			$I->selectOptionInChosen(AdministratorPage::$labelVat,$vatSetting['vat']);
		}
		if (isset($vatSetting['calculation']))
		{
			
			$I->comment('setup Calculation based on');
			$I->selectOptionInChosenjs(AdministratorPage::$labelCalculation,$vatSetting['calculation']);
		}

		
		$I->scrollTo(['css' => '#payment > div:last-of-type']);

		$I->comment('setup Calculation based on');
		if (isset($vatSetting['useTax']))
		{
			$I->selectOptionInRadioField(AdministratorPage::$labelUseTax,$vatSetting['useTax']);
		}

		$I->scrollUp();

		$I->waitForElement(AdministratorPage::$saveButton,30);
		$I->click(AdministratorPage::$saveButton);
		$I->waitForElement(AdministratorPage::$systemContainer,30);
		$I->waitForText(AdministratorPage::$saveItemSuccess, 30,AdministratorPage::$systemContainer );
	}

	/**
	 * @param array $settingOrder
	 * @throws \Exception
	 */
	public function settingOrder($settingOrder = array())
	{
		$I=$this;
		$I->amOnPage(AdministratorPage::$configurationUrl);
		$I->waitForElement(AdministratorPage::$linkB2B, 30);
		$I->click(AdministratorPage::$linkB2B);
		$I->waitForElement(AdministratorPage::$configurationButton,30);
		$I->click(AdministratorPage::$configurationButton);
		$I->waitForElement(AdministratorPage::$orderTab,30);
		$I->click(AdministratorPage::$orderTab);
		
		if (isset($settingOrder['company']))
		{
			$I->comment('setup Vendor of companies in lower levels');
			$I->selectOptionInRadioField(AdministratorPage::$labelCompany, $settingOrder['company']);
		}

		$I->comment('setup Show impersonated companies ');
		if (isset($settingOrder['impersonatedCompanies']))
		{
			$I->selectOptionInRadioField(AdministratorPage::$labelImpersonatedCompanies,$settingOrder['impersonatedCompanies']);
		}
		
		if (isset($settingOrder['impersonatedUser']))
		{
			$I->comment('setup Show impersonated departments');
			$I->selectOptionInRadioField(AdministratorPage::$labelImpersonatedUser,$settingOrder['impersonatedUser']);
		}

		if (isset($settingOrder['collectOrder']))
		{
			$I->comment('setup Collect orders');
			$I->selectOptionInRadioField(AdministratorPage::$labelCollectOrder,$settingOrder['collectOrder']);
		}

		$I->comment('setup Order Expedition ');
		if (isset($settingOrder['orderExpedition']))
		{
			$I->selectOptionInRadioField(AdministratorPage::$labelOrderExpedition,$settingOrder['orderExpedition']);
		}

		$I->scrollTo(['css' => '#order > div:last-of-type']);

		$I->comment('setup Return orders ');
		if (isset($settingOrder['orderReturn']))
		{
			$I->selectOptionInRadioField(AdministratorPage::$labelOrderReturn,$settingOrder['orderReturn']);
		}

		$I->comment('setup Place Order  ');
		if (isset($settingOrder['placeOrder']))
		{
			$I->selectOptionInRadioField(AdministratorPage::$labelPlaceOrder,$settingOrder['placeOrder']);
		}

		$I->scrollUp();

		$I->waitForElement(AdministratorPage::$saveButton,30);
		$I->click(AdministratorPage::$saveButton);
		$I->waitForElement(AdministratorPage::$systemContainer,30);
		$I->waitForText(AdministratorPage::$saveItemSuccess, 30,AdministratorPage::$systemContainer );
	}

	/**
	 * @param array $settingNotification
	 * @throws \Exception
	 */
	public function settingNotification($settingNotification = array())
	{
		$I=$this;
		$I->amOnPage(AdministratorPage::$configurationUrl);
		$I->waitForElement(AdministratorPage::$linkB2B, 30);
		$I->click(AdministratorPage::$linkB2B);
		$I->waitForElement(AdministratorPage::$configurationButton,30);
		$I->click(AdministratorPage::$configurationButton);
		$I->waitForElement(AdministratorPage::$notificationTab,30);
		$I->click(AdministratorPage::$notificationTab);

		$I->comment('setup Return orders ');
		if (isset($settingNotification['notifyUserRegister']))
		$I->selectOptionInRadioField(AdministratorPage::$labelNotifyUserRegister,$settingNotification['notifyUserRegister']);

		if (isset($settingNotification['userToNotify']))
		{
			$I->fillField(AdministratorPage::$userToNotify,$settingNotification['userToNotify']);
		}
		if (isset($settingNotification['mailForm']))
		{
			$I->fillField(AdministratorPage::$mailForm,$settingNotification['mailForm']);
		}
		
		if (isset($settingNotification['syncNotification']))
		{
			$I->fillField(AdministratorPage::$syncNotification,$settingNotification['syncNotification']);
			
		}

		if (isset($settingNotification['notifyAdmin']))
		{
			$I->comment('setup  Notify administrators');
			$I->selectOptionInRadioField(AdministratorPage::$labelNotifyAdmin,$settingNotification['notifyAdmin']);
			
		}

		if (isset($settingNotification['notifyHead']))
		{
			$I->comment('setup Return orders ');
			$I->selectOptionInRadioField(AdministratorPage::$labelNotifyHead,$settingNotification['notifyHead']);
			
		}

		if (isset($settingNotification['notifySale']))
		{
			$I->comment('setup Notify salespersons ');
			$I->selectOptionInRadioField(AdministratorPage::$labelNotifySale,$settingNotification['notifySale']);
		}

		$I->scrollTo(['css' => '#notifications > div:last-of-type']);

		if (isset($settingNotification['notifyAuthor']))
		{
			$I->comment('setup Notify order authors');
			$I->selectOptionInRadioField(AdministratorPage::$labelNotifyAuthor,$settingNotification['notifyAuthor']);
			
		}

		if (isset($settingNotification['notifyPurchaser']))
		{
			$I->comment('setup Notify purchasers ');
			$I->selectOptionInRadioField(AdministratorPage::$labelNotifyPurchaser,$settingNotification['notifyPurchaser']);
		}

		$I->scrollUp();

		$I->waitForElement(AdministratorPage::$saveButton,30);
		$I->click(AdministratorPage::$saveButton);
		$I->waitForElement(AdministratorPage::$systemContainer,30);
		$I->waitForText(AdministratorPage::$saveItemSuccess, 30,AdministratorPage::$systemContainer );
	}
}
