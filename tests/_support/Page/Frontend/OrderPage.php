<?php

namespace Page\Frontend;

class OrderPage extends Redshopb2bPage
{

	/**
	 * @var string
	 */
	public static $Url = '/index.php?option=com_redshopb&view=orders';

	/**
	 * @var string
	 */
	public static $searchOrder = 'filter_search_orders';

	/**
	 * @var string
	 */
	public static $filterOrderId = "//input[@id='filter_search_orders']";

	/**
	 * @var string
	 */
	public static $shippingTitle = 'Shipping method information';

	/**
	 * @var string
	 */
	public static $paymentTitle = 'Payment method information';

	/**
	 * @var string
	 */
	public static $labelStatusOrder = 'Status';

	/**
	 * @var string
	 */
	public static $messageSaveOrderSuccess = 'Order successfully saved.';

	/**
	 * @var array
	 */
	public static $statusOrder = ['class' => 'label-success'];

	/**
	 * @var string
	 */
	public static $confirmed = 'Confirmed';

	/**
	 * @var array
	 */
	public static $btnApplyStatusOrder = ['xpath' => "//button[contains(@onclick, \"Joomla.submitbutton('order.apply')\")]"];

	/**
	 * @var array
	 */
	public static $orderItemTab = ['xpath' => '//ul[@id=\'mainTabTabs\']/li[2]'];

	/**
	 * @var array
	 */
	public static $priceTotalFinal = ['xpath' => '//div[@id=\'totalfinal\']/strong/span'];
	
	//order detail
	/**
	 * @var array
	 */
	public static $titleAtOrderDetail = ['xpath' => "//div[@id='ordergeneraltab']//div[@class='well']/h5"];

	//order item
	/**
	 * @var array
	 */
	public static $buttonChangeItem = ['xpath' => "//button[contains(@onclick, \"Joomla.submitbutton('order.editOrderItems')\")]"];

	/**
	 * @var array
	 */
	public static $buttonProcessToCart = ['xpath' => "//button[contains(@onclick, \"Joomla.submitbutton('shop.checkout')\")]"];

	/**
	 * @var array
	 */
	public static $buttonUpdateOrder = ['xpath' => "//button[contains(@onclick, \"Joomla.submitbutton('shop.updateorder')\")]"];

	/**
	 * @var array
	 */
	public static $buttonCancelEditOrderItems = ['xpath' => "//button[contains(@onclick, \"Joomla.submitbutton('order.cancelEditOrderItems')\")]"];
}
