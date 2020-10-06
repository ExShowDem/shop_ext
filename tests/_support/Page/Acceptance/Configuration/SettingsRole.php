<?php
/**
 * @package     Aesir-ec
 * @subpackage  Step Address
 * @copyright   Copyright (C) 2016 - 2019 Aesir-ec. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 **/

namespace Page\Acceptance\Configuration;

use Page\Acceptance\AdministratorPage;

class SettingsRole extends AdministratorPage
{
	/**
	 * @var string
	 * @since 2.5.1
	 */
	public static $roleSettingsTab = "//a[contains(text(),'Role settings')]";

	/**
	 * @var string
	 * @since 2.5.1
	 */
	public static $labelAdministrators = "Administrators";

	/**
	 * @var string
	 * @since 2.5.1
	 */
	public static $labelHeadOfDepartments = "Head of departments";

	/**
	 * @var string
	 * @since 2.5.1
	 */
	public static $labelSalesPersons = "Salespersons";

	/**
	 * @var string
	 * @since 2.5.1
	 */
	public static $labelPurchasers = "Purchasers";

	/**
	 * @var string
	 * @since 2.5.1
	 */
	public static $labelEmployeesWithLogin = "Employees with login";

	/**
	 * @var string
	 * @since 2.5.1
	 */
	public static $labelEmployees = "Employees";

	/**
	 * @var string
	 * @since 2.5.1
	 */
	public static $labelAllCollectionsAdmin = "All collections are visible to admins";

	/**
	 * @var string
	 * @since 2.5.1
	 */
	public static $labelAllCollectionsHeadOfDepartments = "All collections are visible to head of departments";

	/**
	 * @var string
	 * @since 2.5.1
	 */
	public static $labelAllCollectionsSalesPersons = "All collections are visible to salespersons";

	/**
	 * @var string
	 * @since 2.5.1
	 */
	public static $labelAllCollectionsPurchasers = "All collections are visible to purchasers";

	/**
	 * @var string
	 * @since 2.5.1
	 */
	public static $allCollectionsPurchasers = "//label[@id='jform_purchaser_see_all_collections-lbl']";

	/**
	 * @var string
	 * @since 2.5.1
	 */
	public static $labelAllCollectionsEmployeesWithLogin = "All collections are visible to employees with login";

	/**
	 * @var string
	 * @since 2.5.1
	 */
	public static $labelAllCollectionsEmployees = "All collections are visible to employees";
}