<?php
/**
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Helpers
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;

/**
 * VIES helper.
 *
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Helpers
 * @since       1.6
 */
class RedshopbHelperVies
{
	const WSDL = "http://ec.europa.eu/taxation_customs/vies/checkVatService.wsdl";

	/**
	 * @var null
	 */
	private $client = null;

	/**
	 * @var boolean
	 */
	private $debug = false;

	/**
	 * @var boolean
	 */
	private $valid = false;

	/**
	 * @var array
	 */
	private $data = array();

	/**
	 * @param   array $options [description]
	 *
	 * @uses    SoapClient
	 *
	 * @throws Exception
	 */
	public function __construct($options = array())
	{
		parent::__construct($options);

		if (!class_exists('SoapClient'))
		{
			throw new Exception('The Soap library has to be installed and enabled');
		}

		try
		{
			$this->client = new SoapClient(self::WSDL, array('trace' => true));
		}
		catch (Exception $e)
		{
			$this->trace('Vat Translation Error', $e->getMessage());
		}
	}

	/**
	 * @param   string  $countryCode [description]
	 * @param   integer $vatNumber   [description]
	 * @return  boolean
	 */
	public function check($countryCode, $vatNumber)
	{
		$rs = $this->client->checkVat(array('countryCode' => $countryCode, 'vatNumber' => $vatNumber));

		if ($this->isDebug())
		{
			$this->trace('Web Service result', $this->client->__getLastResponse());
		}

		if ($rs->valid)
		{
			$this->valid               = true;
			list($denomination, $name) = explode(" ", $rs->name, 2);
			$this->data                = array(
				'denomination' => $denomination,
				'name' => $this->cleanUpString($name),
				'address' => $this->cleanUpString($rs->address),
			);

			return true;
		}
		else
		{
			$this->valid = false;
			$this->data  = array();

			return false;
		}
	}

	/**
	 * @return boolean
	 */
	public function isValid()
	{
		return $this->valid;
	}

	/**
	 * @return [type]
	 */
	public function getDenomination()
	{
		return $this->data['denomination'];
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->data['name'];
	}

	/**
	 * @return string
	 */
	public function getAddress()
	{
		return $this->data['address'];
	}

	/**
	 * @return boolean
	 */
	public function isDebug()
	{
		return ($this->debug === true);
	}

	/**
	 * @param   string $title [description]
	 * @param   string $body  [description]
	 * @return  void
	 */
	private function trace($title, $body)
	{
		Factory::getApplication()->enqueueMessage('<h2>' . $title . '</h2>' . htmlentities($body), 'warning');
	}

	/**
	 * @param   string $string [description]
	 * @return  string
	 */
	private function cleanUpString($string)
	{
		for ($i = 0; $i < 100; $i++)
		{
			$newString = str_replace("  ", " ", $string);

			if ($newString === $string)
			{
				break;
			}
			else
			{
				$string = $newString;
			}
		}

		$newString = "";
		$words     = explode(" ", $string);

		foreach ($words as $k => $w)
		{
			$newString .= ucfirst(strtolower($w)) . " ";
		}

		return $newString;
	}
}
