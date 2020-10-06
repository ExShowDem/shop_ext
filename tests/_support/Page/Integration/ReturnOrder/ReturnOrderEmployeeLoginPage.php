<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir . E-commerce. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace Page\Integration\ReturnOrder;
use Page\Frontend\Redshopb2bPage as Redshopb2bPage;

/**
 * Class ReturnOrderEmployeeLoginPage
 *
 * @package Page\Integration
 * @since 2.8.0
 */
class ReturnOrderEmployeeLoginPage extends Redshopb2bPage
{
	/**
	 * @var string
	 * @since 2.8.0
	 */
	public static $returnOrderUrl = 'index.php?option=com_redshopb&view=return_order';

	/**
	 * @var string
	 * @since 2.8.0
	 */
	public static $returnOrderAdminUrl = 'index.php?option=com_redshopb&view=return_orders';

	/**
	 * @var string
	 * @since 2.8.0
	 */
	public static $selectOrderJform = "jform_order_id_chzn";


	/**
	 * @var string
	 * @since 2.8.0
	 */
	public static $productJform = "jform_order_item_id_chzn";

	/**
	 * @var string
	 * @since 2.8.0
	 */
	public static $quantityProducts = "//input[@id='jform_quantity']";

	/**
	 * @var string
	 * @since 2.8.0
	 */
	public static $comment = "//textarea[@id='jform_comment']";

	/**
	 * @var string
	 * @since 2.8.0
	 */
	public static $addIcon = "//button[@onclick=\"Joomla.submitbutton('return_order.save')\"]";

	/**
	 * @var string
	 * @since 2.8.0
	 */
	public static $search = "//input[@id='filter_search_return_orders']";

	/**
	 * @var string
	 * @since 2.8.0
	 */
	public static $orderXpath = "//div[@id='system-message']/div[2]/div";
}