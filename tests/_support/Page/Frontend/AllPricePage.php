<?php
/**
 * Page All Prices
 */

namespace Page\Frontend;
class AllPricePage extends Redshopb2bPage
{
	/**
	 * @var string
	 */
	public static $URL = 'index.php?option=com_redshopb&view=all_prices';

	/**
	 * @var string
	 */
	public static $searchProduct = 'filter_search_all_prices';

	/**
	 * @var string
	 */
	public static $messageSaveSuccess = 'Price successfully saved.';
}