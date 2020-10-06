<?php
/**
 * @package     Aesir.E-Commerce.Libraries
 * @subpackage  HTML
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

use Joomla\Registry\Registry;

/**
 * Shipping plugin base Class
 *
 * @package     Aesir.E-Commerce
 * @subpackage  Shipping
 * @since       1.6
 */
abstract class RedshopbShippingPluginHelperShipping implements RedshopbShippingPluginInterface
{
	/**
	 * Shipping Name
	 * @var string
	 */
	public $shippingName = '';

	/**
	 * Plugin parameters
	 * @var Registry
	 */
	public $params = null;

	/**
	 * Plugin can be disabled for specific extension this is set in shipping configuration
	 * @var boolean
	 */
	public $pluginEnabled = true;

	/**
	 * Constructor
	 *
	 * @param   Registry  $params  Parameters from the plugin
	 *
	 * @since   1.5
	 */
	public function __construct($params = null)
	{
		$this->params = $params;
	}

	/**
	 * Check for new status change from Shipping Gateway
	 *
	 * @param   string  $extensionName    Name of the extension
	 * @param   string  $ownerName        Name of the owner
	 * @param   object  $deliveryAddress  Delivery address
	 * @param   array   $cart             Shopping Cart
	 *
	 * @return array
	 */
	public function getShippingRates($extensionName, $ownerName, $deliveryAddress, $cart)
	{
		$shippingRates = RedshopbShippingHelper::getShippingRates($this->shippingName, $extensionName, $ownerName, $deliveryAddress, $cart);

		return $shippingRates;
	}

	/**
	 * Get the shipping amount as an integer.
	 *
	 * @param   float   $amount    Amount
	 * @param   string  $currency  Iso 4217 3 letters currency code
	 *
	 * @return integer
	 */
	public function getAmountInteger($amount, $currency)
	{
		return (int) round($amount * pow(10, RHelperCurrency::getPrecision($currency)));
	}

	/**
	 * Get the shipping amount as an float.
	 *
	 * @param   int     $amount    Amount
	 * @param   string  $currency  Iso 4217 3 letters currency code
	 *
	 * @return float
	 */
	public function getAmountToFloat($amount, $currency)
	{
		return (float) round($amount / pow(10, RHelperCurrency::getPrecision($currency)));
	}
}
