<?php
/**
 * @package     Aesir
 * @subpackage  Cest
 * @copyright   Copyright (C) 2016 - 2019 Aesir-ec. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Page\Frontend;

/**
 * Class DebtorGroupsDiscountPage
 * @package Page\Frontend
 */
class DebtorGroupsDiscountPage extends Redshopb2bPage
{
	/**
	 * Include url of current page
	 * @var string
	 */
	public static $URL = '/index.php?option=com_redshopb&view=discount_debtor_groups';

	/**
	 * @var array
	 * @since 2.3.0
	 */
	public static $codeID = "#jform_code";

	/**
	 * @var string
	 * @since 2.3.0
	 */
	public static $labelCompany = 'Owner Company';

	/**
	 * @var string
	 */
	public static $labelCompanies = 'Companies';

	/**
	 * @var string
	 * @since 2.3.0
	 */
	public static $searchId = 'filter_search_discount_debtor_groups';

	/**
	 * @var string
	 * @since 2.3.0
	 */
	public static $missingCompaniesMessage = 'Field required: Companies';

	/**
	 * @var string
	 * @since 2.3.0
	 */
	public static $missingCodeMessage = 'Field required: Debtor Group Code';

	/**
	 * @var string
	 * @since 2.3.0
	 */
	public static $missingNameMessage = 'Field required: Debtor Group Name';

	/**
	 * @param $code
	 * @return string
	 * @since 2.3.0
	 */
	public function messageCodeReady($code)
	{
		return 'Save failed with the following error: Code discount group '.$code. ' already taken';
	}
}