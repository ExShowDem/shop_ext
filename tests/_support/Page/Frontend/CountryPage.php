<?php
/**
 * @package     Aesir.E-Commerce
 * @subpackage  Page
 * @copyright   Copyright (C) 2012 - 2019  Aesir.E-Commerce All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Page\Frontend;

class CountryPage extends Redshopb2bPage
{
	/**
	 * @var string
	 *
	 * @since 2.3.0
	 */
	public static $URL = 'index.php?option=com_redshopb&view=countries';

	/**
	 * @var array
	 *
	 * @since 2.3.0
	 */
	public static $code2Id = "#jform_alpha2";

	/**
	 * @var array
	 *
	 * @since 2.3.0
	 */
	public static $code3Id = "#jform_alpha3";

	/**
	 * @var array
	 *
	 * @since 2.3.0
	 */
	public static $numberCodeId = "#jform_numeric";

	/**
	 * @var string
	 *
	 * @since 2.3.0
	 */
	public static $labelEurozone = 'Eurozone';

	/**
	 * @var string
	 *
	 * @since 2.3.0
	 */
	public static $searchCountry = 'filter_search_countries';

	/**
	 * @var string
	 *
	 * @since 2.3.0
	 */
	public static $messageMissingCode2 = 'Field required: Alpha2 Code';

	/**
	 * @var string
	 *
	 * @since 2.3.0
	 */
	public static $messageMissingCode3 = 'Field required: Alpha3 Code';

	/**
	 * @var string
	 *
	 * @since 2.3.0
	 */
	public static $messageNumberric = 'Save failed with the following error: The numeric 0 is already taken by an other country. Please choose another.';

	/**
	 * @param $value
	 * @return string
	 *
	 * @since 2.3.0
	 */
	public function  messageAlreadyAlpha2($value)
	{
		$message ='Save failed with the following error: The Aplha 2 code '.$value.' is already taken by an other country. Please choose another.';
		return $message;
	}

	/**
	 * @param $value
	 * @return string
	 *
	 * @since 2.3.0
	 */
	public function messageAlreadyAlpha3($value)
	{
		$message ='Save failed with the following error: The Aplha 3 code '.$value.' is already taken by an other country. Please choose another.';
		return $message;
	}

	/**
	 * @param $value
	 * @return string
	 *
	 * @since 2.3.0
	 */
	public function messageAlreadyNumberric($value)
	{
		$message = 'Save failed with the following error: The numeric '.$value.' is already taken by an other country. Please choose another.';
		return $message;
	}
}