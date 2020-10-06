<?php

namespace Page\Frontend;
/**
 * Class TaxPage
 * @package Page\Frontend
 */
class TaxPage extends Redshopb2bPage
{
	/**
	 * @var string
	 */
	public static $URLTax = 'index.php?option=com_redshopb&view=taxes';

	/**
	 * @var string
	 */
	public static $searchTaxId = 'filter_search_tax_configurations';

	/**
	 * @var array
	 */
	public static $searchXpath = ['id' => 'filter_search_tax_configurations'];

	/**
	 * @var string
	 */
	public static $labelTaxRate = '#jform_tax_rate';

	/**
	 * @var string
	 */
	public static $optionEUCountry = 'EU Country';

	/**
	 * @var string
	 */
	public static $labelTaxGroup = 'Tax groups';

	/**
	 * @var string
	 */
	public static $taxGroup = "//div[@id='jform_tax_groups_chzn']";

	/**
	 * @var string
	 */
	public static $missingTaxRate = 'Field required: Tax rate';

	/**
	 * @param $value
	 * @return array
	 */
	public static function returnChoice($value)
	{
		return ['xpath' => "//div[@class='chzn-drop']/ul/li[contains(text(), '" . $value . "')]"];
	}
}