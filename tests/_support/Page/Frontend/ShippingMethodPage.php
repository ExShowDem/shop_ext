<?php
/**
 * Page shipping method
 */
namespace Page\Frontend;
class ShippingMethodPage extends Redshopb2bPage
{

	//page shipping rate
	public static $URLShippingRate = 'index.php?option=com_redshopb&view=shipping_rates';

	/**
	 * @var array
	 */
	public static $priceShipping = ['id' => 'jform_price'];

	/**
	 * @var string
	 */
	public static $searchShippingRate = 'filter_search_shipping_rates';

	/**
	 * @var array
	 */
	public static $countryXpath = ['xpath' => '//div[@id=\'jform_countries_chzn\']//ul'];

	/**
	 * @var array
	 */
	public static $onProduct = ['xpath' => '//input[@class=\'select2-search__field\']'];

	/**
	 * @var array
	 */
	public static $onCategory = ['xpath' => './/div[@id=\'jform_on_category_chzn\']/ul'];

	/**
	 * @var array
	 */
	public static $priority = ['id' => 'jform_priority'];
	
	//label
	/**
	 * @var string
	 */
	public static $labelCountry = 'Country';

	/**
	 * @var string
	 */
	public static $labelProduct = 'On product';

	/**
	 * @var string
	 */
	public static $labelCategory = 'On category';
}