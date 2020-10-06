<?php

namespace Page\Frontend;

class ProductDiscountGroupsPage extends Redshopb2bPage
{
	/**
	 * @var string
	 */
	public static $URL = '/index.php?option=com_redshopb&view=product_discount_groups';

	/**
	 * @var string
	 */
	public static $labelOwnerCompany = 'Owner Company';

	/**
	 * @var string
	 */
	public static $labelProduct = 'Product';

	/**
	 * @var string
	 */
	public static $labelProductItem = 'Product Item';

	/**
	 * @var string
	 */
	public static $searchId = 'filter_search_product_discount_groups';

	/**
	 * @var array
	 */
	public static $productInput = ['xpath' => '(//input[@class=\'select2-search__field\'])[1]'];

	/**
	 * @var array
	 */
	public static $productItemInput = ['xpath' => '(//input[@class=\'select2-search__field\'])[2]'];
}