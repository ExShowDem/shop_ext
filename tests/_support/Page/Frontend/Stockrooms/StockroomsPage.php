<?php
/**
 * @package     Aesir-ec
 * @subpackage  Page Address
 * @copyright   Copyright (C) 2016 - 2019 Aesir-ec. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 **/

namespace Page\Frontend\Stockrooms;
use Page\Frontend\UserPage as UserPage;

/**
 * Class Stockrooms
 *
 * @package Page\Frontend\Address
 * @since 2.6.0
 */
class StockroomsPage extends UserPage
{
	/**
	 * @var string
	 * @since 2.6.0
	 */
	public static $url = 'index.php?option=com_redshopb&view=stockrooms';

	/**
	 * @var string
	 * @since 2.6.0
	 */
	public static $companyStockroomsId = "//div[@id='jform_company_id_chzn']/a";

	/**
	 * @var string
	 * @since 2.6.0
	 */
	public static $companyStockroomsJform = "jform_company_id_chzn";

	/**
	 * @var string
	 * @since 2.6.0
	 */
	public static $minDeliveryTime = "//input[@id='jform_min_delivery_time']";

	/**
	 * @var string
	 * @since 2.6.0
	 */
	public static $maxDeliveryTime = "//input[@id='jform_max_delivery_time']";

	/**
	 * @var string
	 * @since 2.6.0
	 */
	public static $lowerLevel = "//input[@id='jform_stock_lower_level']";

	/**
	 * @var string
	 * @since 2.6.0
	 */
	public static $upperLevel = "//input[@id='jform_stock_upper_level']";

	/**
	 * @var string
	 * @since 2.6.0
	 */
	public static $searchStockrooms = "filter_search";

	/**
	 * @var array
	 * @since 2.6.0
	 */
	public static $address = ['link' => "Address"];

	/**
	 * @var array
	 * @since 2.6.0
	 */
	public static $addressName2 = ['id' => 'jform_address_name2'];

	/**
	 * @var string
	 * @since 2.6.0
	 */
	public static $messageMinSmallerThanMaxDeliveryTime = "Save failed with the following error: Min Delivery Time must be smaller than Max Deliver Time";

	/**
	 * @var string
	 * @since 2.6.0
	 */
	public static $messageLowerSmallerThanUpperLevel = "Save failed with the following error: Stock lower level must be smaller than upper level.";
}