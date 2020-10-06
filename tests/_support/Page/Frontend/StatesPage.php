<?php

namespace Page\Frontend;

class StatesPage extends Redshopb2bPage
{
	/**
	 * @var string
	 */
	public static $URL = '/index.php?option=com_redshopb&view=states';

	/**
	 * @var string
	 */
	public static $labelCountry = 'Country';

	/**
	 * @var array
	 */
	public static $alpha2Code = ['id' => 'jform_alpha2'];

	/**
	 * @var array
	 */
	public static $alpha3Code = ['id' => 'jform_alpha3'];

	/**
	 * @var string
	 */
	public static $labelCompany = 'Company';

	/**
	 * @var string
	 */
	public static $searchID = 'filter_search_states';

	/**
	 * @var string
	 */
	public static $messageRequiredCountry = 'Field required: Country';

	/**
	 * @var string
	 */
	public static $messageRequiredCode2 = 'Field required: Alpha2 Code';

	/**
	 * @var string
	 */
	public static $messageRequiredCode3 = 'Field required: Alpha3 Code';

	/**
	 * @var string
	 */
	public static $messageRequiredName = 'Field required: Name';

	/**
	 * @param $code
	 * @return string
	 */
	public function messageAlpha2Already($code)
	{
		$message = "Save failed with the following error: The Aplha 2 code '.$code.' is already taken by an other state. Please choose another.";
		return $message;
	}

	/**
	 * @param $code
	 * @return string
	 */
	public function messageAlpha3Already ($code)
	{
		$message = "Save failed with the following error: The Aplha 3 code '.$code.' is already taken by an other state. Please choose another.";
		return $message;
	}
}