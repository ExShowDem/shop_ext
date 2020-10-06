<?php


namespace Page\Frontend;
class ShopPage extends Redshopb2bPage
{
	/**
	 * @var string
	 */
	public static $URL = '/index.php?option=com_redshopb&view=shop';

	/**
	 * @var array
	 */
	public static $searchId = ['id' => 'filterShopName'];

	/**
	 * @var string
	 */
	public static $searchShop = 'filterShopName';

	/**
	 * @var string
	 */
	public static $buttonShop = 'Shop';

	/**
	 * @var array
	 */
	public static $customerInfoCompany = ['xpath' => '//div[@class=\'customer-info\']/p[1]'];

	/**
	 * @var array
	 */
	public static $customerInfoDepartment = ['xpath' => '//div[@class=\'customer-info\']/p[2]'];

	/**
	 * @var array
	 */
	public static $customerInfoEmployee = ['xpath' => '//div[@class=\'customer-info\']/p[3]'];

	/**
	 * @var array
	 */
	public static $customerWallet = ['xpath' => '//h5'];

	/**
	 * @var array
	 */
	public static $nameShop = ['xpath' => '//h4'];
}