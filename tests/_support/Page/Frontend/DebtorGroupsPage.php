<?php
/**
 * Page depter groups
 */
namespace Page\Frontend;

class DebtorGroupsPage extends Redshopb2bPage
{
	//page debtor groups
	/**
	 * @var string
	 */
	public static $URLDebtorGroups = 'index.php?option=com_redshopb&view=price_debtor_groups';

	/**
	 * @var string
	 */
	public static $customerInput = "//div[@id=\"jform_customer_ids_chzn\"]/ul/li/input";

	/**
	 * @var string
	 */
	public static $debtorGroupSuccess = 'Price Debtor Group successfully submitted.';

	/**
	 * @var string
	 */
	public static $debtorGroupEditSuccess = 'Price Debtor Group successfully saved.';

	/**
	 * @var string
	 */
	public static $messageMissingName = 'Field required: Debtor Group Name';

	/**
	 * @var string
	 */
	public static $messageMissingCode = 'Field required: Debtor Group Code';

	/**
	 * @var string
	 */
	public static $searchPriceDebtorGroup = 'filter_search_price_debtor_groups';

	/**
	 * @var string
	 */
	public static $labelOwnerCompany = 'Owner Company';

	/**
	 * @var string
	 */
	public static $labelCompanies = 'Companies';

	/**
	 * @var string
	 */
	public static $labelShowStockAs = 'Show stock as';

	// shipping method
	/**
	 * @var array
	 */
	public static $shippingMethod = ['link' => "Shipping methods"];

	/**
	 * @var array
	 */
	public static $shippingMethodForm = ['id' => 'shippingConfigurationsForm'];

	/**
	 * @var array
	 */
	public static $newShipping = ['xpath' => "//div[@id='shipping_configurations']//button[contains(normalize-space(), 'New')]"];

	/**
	 * @var string
	 */
	public static $shippingTitle = "//input[@id='plugin_params_shipping_title']";

	// payment method
	/**
	 * @var array
	 */
	public static $paymentMethod = ['link' => "Payment Methods"];

	/**
	 * @var array
	 */
	public static $paymentConfiguration = ['id' => "paymentConfigurationsForm"];

	/**
	 * @var array
	 */
	public static $newPaymentButton = ['xpath' => "//div[@id='payment_configurations']//button[contains(normalize-space(), 'New')]"];

	/**
	 * @var string
	 */
	public static $paymentOption = 'Payment Option';

	/**
	 * @var array
	 */
	public static $paymentPlugin = ['id' => 'plugin_params_merchant_id'];

	/**
	 * @var string
	 */
	public static $paymentAccount = 'Paypal account';

	/**
	 * @var string
	 */
	public static $paymentSandboxAccount = 'Paypal Sandbox account';

	/**
	 * @var string
	 */
	public static $usedSandbox = 'Use sandbox';

	/**
	 * @param $companies
	 * @return array
	 */
	public function getXpathCompany($companies)
	{
		return ['xpath' => "//div[@id='jform_customer_ids_chzn']//ul[@class='chzn-results']/li[contains(.,'" . $companies . "')]"];
	}
}