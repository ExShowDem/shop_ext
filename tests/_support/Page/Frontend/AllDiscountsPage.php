<?php
/**
 * @package     Aesir-ec
 * @subpackage  Page AllDiscount
 * @copyright   Copyright (C) 2016 - 2018 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 **/

namespace Page\Frontend;

class AllDiscountsPage extends Redshopb2bPage
{
	/**
	 * @var string
	 */
	public static $url = 'index.php?option=com_redshopb&view=all_discounts';

	/**
	 * @var string
	 */
	public static $discountTypeLabel = 'Discount Type';

	/**
	 * @var string
	 * @since 2.8.0
	 */
	public static $productDiscountGroupLabel = "Product Discount Group";

	/**
	 * @var string
	 */
	public static $productLabel = 'Product';

	/**
	 * @var string
	 */
	public static $salesTypeLabel = 'Sales Type';

	/**
	 * @var string
	 */
	public static $statusLabel ='Status';

	/**
	 * @var string
	 */
	public static $currencyLabel = 'Currency';

	/**
	 * @var string
	 */
	public static $discountPercent = '//label[@for=\'jform_kind0\']';

	/**
	 * @var string
	 */
	public static $discountTotal = '//label[@for=\'jform_kind1\']';

	/**
	 * @var array
	 */
	public static $percentId = '#jform_percent';

	/**
	 * @var array
	 */
	public static $totalId = '//input[@id=\'jform_total\']';

	/**
	 * @var array
	 */
	public static $productId = '#jform_type_product_id-lbl';

	/**
	 * @var string
	 */
	public static $messageSuccess = 'Discount successfully submitted.';

	/**
	 * @var string
	 */
	public static $messageEditSuccess = 'Discount successfully saved.';

	/**
	 * @var string
	 */
	public static $messageDeleteSuccess = '1 item successfully deleted';
}