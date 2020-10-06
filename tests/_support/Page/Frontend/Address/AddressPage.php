<?php
/**
 * @package     Aesir-ec
 * @subpackage  Page Address
 * @copyright   Copyright (C) 2016 - 2019 Aesir-ec. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 **/

namespace Page\Frontend\Address;
use Page\Frontend\UserPage;

class AddressPage extends UserPage
{
	/**
	 * @var string
	 * @since 2.5.0
	 */
	public static $url = 'index.php?option=com_redshopb&view=addresses';

	/**
	 * @var string
	 * @since 2.5.0
	 */
	public static $searchAddress = 'filter_search_addresses';

	/**
	 * @var string
	 * @since 2.5.0
	 */
	public static $addressType = 'Address Type';

	/**
	 * @var string
	 * @since 2.5.0
	 */
	public static $entityType = 'Entity Type';

	/**
	 * @var string
	 * @since 2.5.0
	 */
	public static $entityName = 'Entity Name';

	/**
	 * @var string
	 * @since 2.5.0
	 */
	public static $entityCompanyId = '//div[@id="jform_company_customer_id_chzn"]';

	/**
	 * @var string
	 * @since 2.5.0
	 */
	public static $entityDepartmentId = '//div[@id="jform_department_customer_id_chzn"]';

	/**
	 * @var string
	 * @since 2.5.0
	 */
	public static $saveSuccessAddress = 'Address successfully submitted.';

	/**
	 * @var string
	 * @since 2.5.0
	 */
	public static $saveSuccessAddressAfterUpdate = 'Address successfully saved.';

	/**
	 * @var string
	 * @since 2.5.0
	 */
	public static $missingType = 'Field required: Entity Type';
}