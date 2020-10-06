<?php
/**
 * Page users
 */
namespace Page\Frontend;

class UserPage extends Redshopb2bPage
{
	/**
	 * Include url of current page
	 *
	 * @var   string
	 * @since 1.0.0
	 */
	public static $URLUsers = '/index.php?option=com_redshopb&view=users';

	/**
	 * Role of user
	 *
	 * @var   string
	 * @since 1.0.0
	 */
	public static $idRole = ['id' => "jform_role_type_id-lbl"];

	/**
	 * Role type of user
	 *
	 * @var   string
	 * @since 1.0.0
	 */
	public static $roleDropdown = "//div[@id='jform_role_type_id_chzn']/a";

	/**
	 * Role label of user
	 *
	 * @var   string
	 * @since 1.0.0
	 */
	public static $roleLabel = 'Role';

	/**
	 * Email of user
	 *
	 * @var   string
	 * @since 1.0.0
	 */
	public static $hasMailLabel = 'Has Email?';

	/**
	 * Send Email for user
	 *
	 * @var   string
	 * @since 1.0.0
	 */
	public static $sendMailLabel = 'Send E-mails';

	/**
	 * Email form for user
	 *
	 * @var   string
	 * @since 1.0.0
	 */
	public static $emailId = ['id' => 'jform_email'];

	/**
	 * Name Login form for user
	 *
	 * @var   string
	 * @since 1.0.0
	 */
	public static $name1 = ['id' => 'jform_name1'];

	/**
	 * Company for user
	 *
	 * @var   string
	 * @since 1.0.0
	 */
	public static $company = 'Company';

	/**
	 * Status for user
	 *
	 * @var   string
	 * @since 1.0.0
	 */
	public static $status = 'Status';

	/**
	 * Message Missing Name for user
	 *
	 * @var   string
	 * @since 1.0.0
	 */
	public static $missingName = 'Field required: Name';

	/**
	 * Message Missing email for user
	 *
	 * @var   string
	 * @since 1.0.0
	 */
	public static $missingEmail = 'Field required: Email';

	/**
	 * Message Missing Company for user
	 *
	 * @var   string
	 * @since 1.0.0
	 */
	public static $missingCompany = 'Field required: Company';

	/**
	 * @var string
	 * @since 2.5.0
	 */
	public static $missingAddress = 'Field required: Address';

	/**
	 * @var string
	 * @since 2.5.0
	 */
	public static $missingZipCode = 'Field required: Zip Code';

	/**
	 * @var string
	 * @since 2.5.0
	 */
	public static $missingCity = 'Field required: City';

	/**
	 * @var string
	 * @since 2.5.0
	 */
	public static $missingCountry = 'Field required: Country';

	/**
	 * Message Missing Company email for user
	 *
	 * @var   string
	 * @since 1.0.0
	 */
	public static $missingCompanyFirst = 'Company is missing for email usage!';

	/**
	 * Message Missing Role email for user
	 *
	 * @var   string
	 * @since 1.0.0
	 */
	public static $missingRole = 'Field required: Role';

	/**
	 * Message Username and password do not match
	 *
	 * @var   string
	 * @since 1.0.0
	 */
	public static $messageUserPassNotMatch = 'Username and password do not match or you do not have an account yet.';

	/**
	 * Address name value
	 *
	 * @var   string
	 * @since 1.0.0
	 */
	public static $addressName = ['id' => 'jform_address_name'];

	/**
	 * Address value
	 *
	 * @var   string
	 * @since 1.0.0
	 */
	public static $addressAddress = ['id' => 'jform_address'];

	/**
	 * Address2 value
	 *
	 * @var   string
	 * @since 1.0.0
	 */
	public static $addressSecondLine = ['id' => 'jform_address2'];

	/**
	 * Zip value
	 *
	 * @var   string
	 * @since 1.0.0
	 */
	public static $addressZipCode = ['id' => 'jform_zip'];

	/**
	 * City value
	 *
	 * @var   string
	 * @since 1.0.0
	 */
	public static $addressCity = ['id' => 'jform_city'];

	/**
	 * Address phone
	 *
	 * @var   string
	 * @since 1.0.0
	 */
	public static $addressPhone = ['id' => 'jform_address_phone'];

	/**
	 * Phone
	 *
	 * @var   string
	 * @since 1.0.0
	 */
	public static $addressPhoneUser = ['id' => 'jform_phone'];

	/**
	 * Cell phone
	 *
	 * @var   string
	 * @since 1.0.0
	 */
	public static $addressCellPhone = ['id' => 'jform_cell_phone'];

	/**
	 * @var string
	 */
	public static $loginNameId = "//input[@id='jform_username']";

	/**
	 * @var string
	 */
	public static $passwordId = "//input[@id='jform_password']";

	/**
	 * @var string
	 */
	public static $passwordConfirmId = "//input[@id='jform_password2']";

	/**
	 * Country
	 *
	 * @var   string
	 * @since 1.0.0
	 */
	public static $addressCountry = 'Country';

	/**
	 * Name 2
	 *
	 * @var   string
	 * @since 1.0.0
	 */
	public static $name2Id = ['id' => 'jform_name2'];

	/**
	 * Create user success
	 *
	 * @var   string
	 * @since 1.0.0
	 */
	public static $saveUserSuccess = 'User successfully submitted.';

	/**
	 * User Wallet tab
	 *
	 * @var   string
	 * @since 1.0.0
	 */
	public static $walletOfUser = ['link' => "User Wallet"];

	/**
	 * User currency
	 *
	 * @var   string
	 * @since 1.0.0
	 */
	public static $currencyOfUser = ['id' => "assign-currency-lbl"];

	/**
	 * Currency
	 *
	 * @var   string
	 * @since 1.0.0
	 */
	public static $currencyField = 'Currency';

	/**
	 * Amount
	 *
	 * @var   string
	 * @since 1.0.0
	 */
	public static $amountField = 'Amount';

	/**
	 * Amount Button
	 *
	 * @var   string
	 * @since 1.0.0
	 */
	public static $addAmountButton = ['xpath' => "//button[contains(normalize-space(), 'Add Amount')]"];

	/**
	 * Money successfully
	 *
	 * @var   string
	 * @since 1.0.0
	 */
	public static $addMoneySuccess = 'Money successfully credited to 1 user(s)';

	/**
	 * User Wallet
	 *
	 * @var   string
	 * @since 1.0.0
	 */
	public static $userWalletId = ['id' => 'user-wallet'];

	/**
	 * Address Type
	 *
	 * @var   string
	 * @since 1.0.0
	 */
	public static $addressType = ' Address Type';

	/**
	 * Search Price Debtor Group
	 *
	 * @var   string
	 * @since 1.0.0
	 */
	public static $searchPriceDebGroup = 'filter_search_price_debtor_groups';

	/**
	 * Search Users
	 *
	 * @var   string
	 * @since 1.0.0
	 */
	public static $searchUser = 'filter_search_users';

	/**
	 * @var string
	 * @since 2.8.0
	 */
	public static $department = "Department";

	/**
	 * @var string
	 * @since 2.8.0
	 */
	public static $userSaveSuccess = "User successfully saved.";

	/**
	 * Setup list kind of role
	 * @var   string
	 * @since 1.0.0
	 */
	public function setRoleList($value)
	{
		echo $value;
		$roleList = array();
		$roleList['1'] = '01 :: Administrator';
		$roleList['2'] = '02 :: Head of Department';
		$roleList['3'] = '03 :: Sales Person';
		$roleList['4'] = '04 :: Purchaser';
		$roleList['5'] = '05 :: Employee with login';
		$roleList['6'] = '06 :: Employee';
		return $roleList[$value];
	}
	
	/**
	 * @param $nameRole
	 * @return string
	 * @since 2.0.3
	 */
	public function selectRole($nameRole)
	{
		$xpath = "//select[@id='jform_role_type_id']/option[contains(text(), '$nameRole')]";
		return $xpath;
	}
}