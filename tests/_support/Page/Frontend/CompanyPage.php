<?php
/**
 * Page companies
 */

namespace Page\Frontend;
class CompanyPage extends Redshopb2bPage
{
//page companies
	/**
	 * @var string
	 */
	public static $URLCompanies = 'index.php?option=com_redshopb&view=companies';

	/**
	 * @var array
	 */
	public static $customerNumber = ['id' => "jform_customer_number"];
	
	/**
	 * @var string
	 */
	public static $addressField = '#jform_address';

	/**
	 * @var string
	 */
	public static $zipCodeField = '#jform_zip';

	/**
	 * @var string
	 */
	public static $cityField = '#jform_city';

	/**
	 * @var string
	 * @since 2.8.0
	 */
	public static $phoneFieldLbl = "//label[@id='jform_address_phone-lbl']";

	/**
	 * @var string
	 * @since 2.8.0
	 */
	public static $phoneField = '#jform_address_phone';

	/**
	 * @var string
	 */
	public static $countryField = 'Country';

	/***
	 * @var string
	 */
	public static $customerAt = 'Customer At';
	
	/**
	 * @var string
	 */
	public static $setB2C = 'Set as B2C';

	// tax configuration
	/**
	 * @var string
	 */
	public static $taxConfiguration = '#adminForm';

	/**
	 * @var string
	 */
	public static $taxVatGroupLbl = 'TAX/VAT group';

	/**
	 * @var string
	 */
	public static $taxVatBasedOnLbl = 'TAX/VAT Calculation based on';

	/**
	 * @var string
	 */
	public static $taxVatGroupId = "//div[@id='jform_tax_group_id_chzn']/a";

	/**
	 * @var string
	 */
	public static $taxVatBasedOnId = "//div[@id='jform_tax_based_on_chzn']/a";

	/**
	 * @var string
	 */
	public static $taxVatGroupJform = "jform_tax_group_id_chzn";

	/**
	 * @var string
	 */
	public static $taxVatBasedOnJform = "jform_tax_based_on_chzn";
	
	/**
	 * @var array
	 */
	public static $saveButtonCompany = ['xpath' => "//div[@class='btn-toolbar toolbar']//button[contains(normalize-space(), 'Save')][1]"];

	/**
	 * @var array
	 */
	public static $deleteButtonCompany = ['xpath' => "//button[@onclick=\"Joomla.submitbutton('companies.delete')\"]"];

	/**
	 * @var string
	 */
	public static $saveCompanySuccess = 'Company successfully submitted.';

	/**
	 * @var string
	 */
	public static $editCompanySuccess = 'Company successfully saved.';

	/**
	 * @var string
	 */
	public static $messageDeleteMainCompany = 'Company in Main type cannot be deleted.';

	/**
	 * @var string
	 */
	public static $searchCompanies = 'filter_search_companies';

	/**
	 * @var array
	 */
	public static $companyModal = ['id' => 'companiesModal'];

	/**
	 * @param $value
	 * @since 2.8.0
	 */
	public static function returnValueInput($value)
	{
		$xpath = "//input[@value='".$value."']";
		return $xpath;
	}
}
