<?php

namespace Page\Acceptance;

use Page\Frontend\Redshopb2bPage;

class AdministratorPage extends Redshopb2bPage
{
	/**
	 * @var string
	 */
	public static $configurationUrl = '/administrator/index.php?option=com_redshopb&task=config.edit';
	
	/**
	 * @var array
	 */
	public static $linkB2B = ['link' => 'Aesir E-Commerce'];
	
	/**
	 * @var array
	 */
	public static $configurationButton = ['xpath'=>'//span[contains(text(),\'Configuration\')]'];
	
	/**
	 * @var array
	 */
	public static $globalForm = ['xpath'=>'//a[contains(text(),\'Global\')]'];
	
	/**
	 * @var string
	 */
	public static $labelCountry = 'Default country ';
	
	/**
	 * @var string
	 */
	public static $labelBootstrap = 'Default frontend framework';
	
	/**
	 * @var string
	 */
	public static $labelAvailableCountry = 'Available countries';
	
	/**
	 * @var string
	 */
	public static $labelAllowCountryRegister = 'Allow company registration';
	
	/**
	 * @var string
	 */
	public static $labelAddressRequired = 'Address required for new user registration';

	/**
	 * @var string
	 */
	public static $labelBillingPhone = 'Billing phone number required for new user registration';

	/**
	 * @var string
	 */
	public static $labelShippingPhone = 'Shipping phone number required for new user registration';
	
	/**
	 * @var string
	 */
	public static $labelRegisterFlow = 'Register flow';
	
	/**
	 * @var array
	 */
	public static $activationEmailTemplate = ['xpath'=>'//div[@id=\'jform_activation_email_template_chzn\']/div/ul/li[2]'];
	
	/**
	 * @var array
	 */
	public static $encryptionKey = ['id'=>'jform_encryption_key'];
	
	/**
	 * @var array
	 */
	public static $relatedSKU = ['id'=>'jform_related_sku_name'];

	/**
	 * @var array
	 */
	public static $idCountry = ['id' => 'jform_default_country_id'];

	/**
	 * @var array
	 */
	public static $idBootstrap = ['id' => 'jform_default_frontend_framework'];

	/**
	 * @var array
	 */
	public static $idAvailableCountry = ['id' => 'jform_country_assignment'];

	/**
	 * @var array
	 */
	public static $idAllowCountryRegister = ['id' => 'jform_allow_company_register'];

	/**
	 * @var array
	 */
	public static $idAddressRequired = ['id' => 'jform_register_address_required'];

	/**
	 * @var array
	 */
	public static $idRegisterFlow = ['id' => 'jform_register_flow'];

	/**
	 * @var array
	 */
	public static $idBillingPhone = ['id' => 'jform_register_billing_phone_required'];

	/**
	 * @var array
	 */
	public static $idShippingPhone = ['id' => 'jform_register_shipping_phone_required'];

	/**
	 * @var array
	 */
	public static $idActivationEmailTemplate = ['id' => 'jform_activation_email_template'];

	/**
	 * @var array
	 */
	public static $idEncryptionKey = ['id' => 'jform_encryption_key'];

	/**
	 * @var array
	 */
	public static $idRelatedSKU = ['id' => 'jform_related_sku_name'];

	/**
	 * @var array
	 */
	public static $idSetWebservices = ['id' => 'jform_set_webservices'];

	/**
	 * @var array
	 */
	public static $idUserPermission = ['id' => 'jform_use_webservice_permission_restriction'];

	/**
	 * @var array
	 */
	public static $idProcessProductDescription = ['id' => 'jform_product_desc_process'];

	/**
	 * @var array
	 */
	public static $idWarningLogout = ['id' => 'jform_warning_logout_when_products_in_cart'];

	/**
	 * @var array
	 */
	public static $idWarningTextCart = ['id' => 'jform_warning_logout_when_products_in_cart_text'];

	public static $labelActivationEmail = 'Activation email template';

	public static $labelSetWebservices = 'Set webservices';

	public static $labelUserPermission = 'Use webservice permission restriction';

	public static $labelProcessProductDescription = 'Process product description';

	public static $labelWarningLogout = 'Warning on log out if there are products in cart';

	public static $warningTextCart = ['id'=>'jform_warning_logout_when_products_in_cart_text'];

	public static $labelRichSnippet = 'Rich Snippet';

	//shop tab
	public static $shopTab = ['xpath'=>'//a[contains(text(),\'Shop\')]'];

	public static $labelShowCategoryProduct = 'Show Subcategory Products';

	public static $labelAjaxCategory = 'Ajax Categories';

	public static $dayProductNews = ['id'=>'jform_date_new_product'];

	public static $labelShowProductPrintOption = 'Show products print option';

	public static $labelCompareUsingOption = 'Companies using collections';

	public static $labelShowShopAs = 'Show shop as';

	public static $labelDefaultLayout = 'Default Product Layout';

	public static $labelDefaultAccessory = 'Default Accessory Layout';

	public static $labelShowInlineCategory = 'Show Inline Category Filter';

	public static $labelShowShopCollection = 'Show shop collection filter';

	//cart checkout tab
	public static $cartCheckoutTab = ['xpath'=>'//a[contains(text(),\'Cart/Checkout\')]'];

	public static $labelAddToCart = 'Add to cart notification type';

	public static $addToCartAlert=['xpath'=>'//fieldset[@id=\'jform_add_to_cart_notification_type\']/label[2]'];

	public static $labelCartCounting = 'Cart counting';

	public static $labelShowImageInCart = 'Show product image in cart view';

	public static $labelShowTaxInCart = 'Show taxes in the cart module';

	public static $labelCheckoutRegister = 'Checkout Registration ';

	public static $guestUserDefault=['id'=>'jform_checkout_guest_user_name'];

	public static $labelCheckoutMode = 'Checkout Mode';

	public static $labelShowImageProductCheckout = 'Show product image during checkout';

	public static $labelShowStockPresent = 'Stock presented as';

	public static $labelEnableShipping = 'Enable shipping date';

	public static $labelTimeShipping = 'Stockroom Delivery time';

	public static $labelClearCartBeforeAddFavourite = 'Clean cart before add products from favourite list?';

	public static $labelRedirectAfterAdd = 'Redirect after add to cart';
	
	/**
	 * @var string
	 */
	public static $favoriteCart = 'Go to checkout when add favorite list to cart';
	
	/**
	 * @var string
	 */
	public static $checkoutRedirect = 'Quick order checkout redirect';
	
	/**
	 * @var string
	 */
	public static $saveCart = 'Save cart for user';
	
	/**
	 * @var string
	 */
	public static $invoiceEmail = 'Enable invoice email';

	/**
	 * @var string
	 */
	public static $saveToCartBy = 'Save to cart by';

	// image tab
	public static $imageTab = ['xpath'=>'//a[contains(text(),\'Images\')]'];

	public static $storeMaxWidth = ['id'=>'jform_stored_max_width'];

	public static $storeMaxHeight = ['id'=>'jform_stored_max_height'];

	public static $labelStoreOptimize = 'Optimize when stored';

	public static $labelImageOptimization = 'Select images optimization';

	public static $thumbnailWidth = ['id'=>'jform_thumbnail_width'];

	public static $thumbnailHeight = ['id'=>'jform_thumbnail_height'];

	public static $gridImageWidth = ['id'=>'jform_grid_image_width'];

	public static $gridImageHeight = ['id'=>'jform_grid_image_height'];

	public static $productImageWidth = ['id'=>'jform_product_image_width'];

	public static $productImageHeight = ['id'=>'jform_product_image_height'];

	public static $categoryImageWidth = ['id'=>'jform_category_image_width'];

	public static $categoryImageHeight = ['id'=>'jform_category_image_height'];

	public static $manufactureImageWidth = ['id'=>'jform_manufacturer_image_width'];

	public static $manufactureImageHeight = ['id'=>'jform_manufacturer_image_height'];

	//breadcrumb configuration
	public static $breadcrumbTab = ['xpath'=>'//a[contains(text(),\'Breadcrumbs\')]'];

	public static $labelShowBreadcrumbs = 'Show Breadcrumbs';

	public static $labelImpersonation  = 'Impersonation breadcrumbs';

	public static $labelYouAreHere = 'Show "You are here"';

	public static $labelShowHome = 'Show Home';

	public static $textForHomeEntry = ['id'=>'jform_homeText'];

	public static $textSeparator = ['id'=>'jform_separator'];

	//product search setting
	public static $productSearchTab = ['xpath'=>'//a[contains(text(),\'Product Search\')]'];

	public static $labelSearch = 'Search method';

	public static $labelSynonymsSupport = 'Synonyms Support';

	public static $labelenableStemmer = 'Enable Stemmer';

	public static $labelStemmer  = 'Stemmer';

	public static $labelStopIfFound = 'Stop if found';

	//pagination tab
	public static $paginationTab = ['xpath'=>'//a[contains(text(),\'Pagination\')]'];

	public static $labelNoPage = 'No pagination';

	public static $categoryPerPage = ['id'=>'jform_shop_categories_per_page'];

	public static $categoryProductPerPage = ['id'=>'jform_shop_products_per_page'];

	public static $comparePerPage = ['id'=>'jform_shop_companies_per_page'];

	public static $departmentPerPage = ['id'=>'jform_shop_departments_per_page'];

	public static $employeesPerPage = ['id'=>'jform_shop_employees_per_page'];

	public static $labelnumberOfColumn = 'No. of columns';

	public static $numberOfCategory = ['id'=>'jform_category_number_of_categories_per_column'];


	//vat tab
	public static $vatTab = ['xpath'=>'//a[contains(text(),\'Prices/VAT/TAX\')]'];

	public static $labelDefaultCurrency = 'Default currency';

	public static $currencySeparator = ['id'=>'jform_currency_separator'];

	public static $labelShowPrice = 'Show prices';

	public static $labelOutletProduct = 'Show Outlet price';

	public static $labelLowestProduct = 'Always use lowest product price';

	public static $labelOffSystem = 'Offer system';

	public static $labelVat = ' TAX/VAT Calculation based on';

	public static $labelCalculation = 'Calculation based on';

	public static $labelUseTax = 'Use Tax Exempt';

	// order tab]
	public static $orderTab = ['xpath'=>'//a[contains(text(),\'Orders\')]'];

	public static $labelCompany = 'Vendor of companies in lower levels';

	public static $labelImpersonatedCompanies = 'Show impersonated companies';

	public static $labelImpersonatedUser = 'Show impersonated users';

	public static $labelCollectOrder = 'Collect orders';

	public static $labelOrderExpedition = 'Order Expedition';

	public static $labelOrderReturn = 'Return orders';

	public static $labelPlaceOrder = 'Place Order';

	//setting notification

	public static $notificationTab = ['xpath'=>'//a[contains(text(),\'Notifications\')]'];

	public static $labelNotifyUserRegister = 'Notify users on new user registration';

	public static $userToNotify = ['id'=>'jform_user_notification_user_add_to_users'];

	public static $mailForm = ['id'=>'jform_mailfrom'];

	public static $syncNotification = ['id'=>'jform_sync_notification_recipients'];

	public static $labelNotifyAdmin = 'Notify administrators';

	public static $labelNotifyHead = 'Notify heads of departments';

	public static $labelNotifySale = 'Notify salespersons';

	public static $labelNotifyAuthor = 'Notify order authors';

	public static $labelNotifyPurchaser = 'Notify purchasers';
}

