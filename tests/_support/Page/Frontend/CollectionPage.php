<?php
/**
 * @package     Aesir-ec
 * @subpackage  Page Collection
 * @copyright   Copyright (C) 2016 - 2018 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 **/

namespace Page\Frontend;
class CollectionPage extends Redshopb2bPage
{
	/**
	 * @var string
	 */
	public static $URL = 'index.php?option=com_redshopb&view=collections';
	/**
	 * @var array
	 */
	public static $buttonCreateNext = ['xpath' => "//button[@onclick=\"Joomla.submitbutton('collection.createNext')\"]"];
	
	/**
	 * @var array
	 */
	public static $searchCollection = ['id' => 'filter_search_collections'];
	
	/**
	 * @var string
	 */
	public static $searchForItemInFrontend = 'filter_search_collections';
	/**
	 * @var string
	 * @since 2.0.3
	 */
	public static $labelCompany = 'Company';
	
	/**
	 * @var string
	 * @since 2.0.3
	 */
	public static $labelCollectionCurrency = 'Collection Currency';
	
	/**
	 * @var string
	 * @since 2.0.3
	 */
	public static $labelStatus = 'Status';
	
	/**
	 * @var string
	 * @since 2.0.3
	 */
	public static $labelCustomerDepartments = 'Customer Departments';

	/**
	 * @param   string $departmentsFieldID Department id
	 * @return string
	 * @since 2.0.3
	 */
	public static $departmentsFieldID = "#jform_department_ids";

	/**
	 * @var string
	 * @since 2.8.0
	 */
	public static $prices = ['link' => "Prices"];

	/**
	 * @var string
	 * @since 2.8.0
	 */
	public static $saveAllPrices = "//button[@class='btn btn-success']";

	/**
	 * @var string
	 * @since 2.8.0
	 */
	public static $messageUpdatePrices = "collection price update";

	/**
	 * @var string
	 * @since 2.8.0
	 */
	public static $messageSuccess = "Collection successfully saved.";
	
	/**
	 * @param   string $value Position of product
	 * @return string
	 * @since 2.0.3
	 */
	public function product($value)
	{
		$xpath = "(//button[contains(text(), 'Add')])['$value']";
		
		return $xpath;
	}

	/**
	 * @param $position
	 *
	 * @return string
	 * @since 2.8.0
	 */
	public function productPrices($position)
	{
		$xpath = "(//td[@class='footable-visible footable-first-column']/input)[$position]";

		return $xpath;
	}
}
