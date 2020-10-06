<?php
/**
 * @package     Aesir\E-Commerce\Plugin\Redshipping
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

use Joomla\CMS\Factory;
use Joomla\CMS\Application\CMSApplication;
use Joomla\Registry\Registry;

/**
 * Class ParcelShopSearch
 *
 * @since 2.1.0
 */
class ParcelShopSearch
{
	/**
	 * @var Registry
	 *
	 * @since 2.1.0
	 */
	private $params;

	/**
	 * ParcelShopSearch constructor.
	 *
	 * @param   Registry $params Plugin parameters
	 *
	 */
	public function __construct(Registry $params)
	{
		$this->params = $params;
	}

	/**
	 * Fetches the parcel shops and renders the layout
	 *
	 * @param   string|null $address  Address to search around
	 * @param   string      $zip      Zipcode to search in
	 * @param   string      $country  Country to search in
	 *
	 * @return array
	 *
	 * @since 2.1.0
	 */
	public function search($address, $zip, $country)
	{
		$address       = urlencode($address);
		$countryEntity = RedshopbEntityCountry::loadFromName($country);
		$amount        = $this->params->get('amount', 4);

		if ($countryEntity->isLoaded())
		{
			$country = $countryEntity->alpha2;
		}
		elseif (empty($country))
		{
			$country = $this->params->get('default_country');
		}

		$data = array('nodata' => true, 'data' => null, 'country' => $country);

		$url = "gls.dk/webservices_v4/wsShopFinder.asmx/SearchNearestParcelShops?street={$address}&zipcode={$zip}&countryIso3166A2={$country}&Amount={$amount}";

		$ch = curl_init($url);

		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		$result = curl_exec($ch);

		curl_close($ch);

		if ($this->validate($result, 'parcelshops'))
		{
			$xml = new SimpleXMLElement($result);

			$data['nodata'] = false;
			$data['data']   = $xml->parcelshops->PakkeshopData;
		}

		return $data;
	}

	/**
	 * Validates the XML response from the GLS Parcel Shop Search API
	 *
	 * @param   string $response XML string to validate
	 * @param   string $tag      XML tag to validate against
	 *
	 * @return boolean
	 *
	 * @since 2.1.0
	 */
	private function validate($response, $tag)
	{
		libxml_use_internal_errors(true);
		$dom = new DOMDocument;
		$dom->loadXML($response);

		return 1 === $dom->getElementsByTagName($tag)->length;
	}

	/**
	 * Searches for GLS parcel shops based on the input parameters
	 *
	 * @param   CMSApplication $app Joomla application instance
	 *
	 * @return array
	 *
	 * @since 2.1.0
	 */
	public function ajaxSearch($app)
	{
		$address = $app->input->get('address');
		$zipCode = $app->input->get('zip');
		$country = $app->input->get('country', 'DK');

		$app->setUserState('shipping.gls-parcel-search-options', array('address' => $address, 'zip' => $zipCode, 'country' => $country));

		return $this->search($address, $zipCode, $country);
	}

	/**
	 * Find a specific parcel shop using the GLS ID
	 *
	 * @param   string $id GLS Parcelshop ID
	 *
	 * @return boolean|SimpleXMLElement
	 *
	 * @since 2.1.0
	 */
	public function getParcelshop($id)
	{
		$ch = curl_init('gls.dk/webservices_v4/wsShopFinder.asmx/GetOneParcelShop');

		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, "ParcelShopNumber={$id}");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		$response = curl_exec($ch);

		curl_close($ch);

		if (!$this->validate($response, 'PakkeshopData'))
		{
			return false;
		}

		return new SimpleXMLElement($response);
	}
}
