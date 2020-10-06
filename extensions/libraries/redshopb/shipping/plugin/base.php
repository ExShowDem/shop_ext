<?php
/**
 * @package     Aesir.E-Commerce.Libraries
 * @subpackage  HTML
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

use Joomla\CMS\Plugin\CMSPlugin;

/**
 * Shipping plugin base Class
 *
 * @package     Aesir.E-Commerce
 * @subpackage  Shipping
 * @since       1.6
 */
abstract class RedshopbShippingPluginBase extends CMSPlugin
{
	/**
	 * Shipping gateway helper class
	 * @var RedshopbShippingPluginHelperShipping
	 */
	public $shippingHelper = null;

	/**
	 * Name of the shipping plugin
	 * @var string
	 */
	protected $shippingName = null;

	/**
	 * Affects constructor behavior. If true, language files will be loaded automatically.
	 *
	 * @var    boolean
	 * @since  1.5
	 */
	protected $autoloadLanguage = true;

	/**
	 * Constructor
	 *
	 * @param   object  $subject   The object to observe
	 * @param   array   $config    An optional associative array of configuration settings.
	 *                             Recognized key values include 'name', 'group', 'params', 'language'
	 *                             (this list is not meant to be comprehensive).
	 *
	 * @since   2.0
	 */
	public function __construct(&$subject, $config = array())
	{
		parent::__construct($subject, $config);

		// Load default helper file or use the plugin helper file
		$this->loadShippingHelper();

		$this->shippingHelper->shippingName = $this->shippingName;
	}

	/**
	 * Collects all shipping plugins for given extension and owner name
	 *
	 * @param   string  $extensionName    Name of the extension
	 * @param   string  $ownerName        Name of the owner
	 * @param   object  $deliveryAddress  Delivery address
	 * @param   array   $cart             Shopping Cart
	 * @param   array   $shippingList     Shipping list
	 *
	 * @return string
	 */
	public function onRedshippingListShipping($extensionName, $ownerName, $deliveryAddress, $cart, &$shippingList)
	{
		$this->setRedshippingOptions($extensionName, $ownerName);

		// Plugin is disabled (optionally for this extension)
		if (!$this->shippingHelper->pluginEnabled)
		{
			return null;
		}

		$shippingRates = $this->shippingHelper->getShippingRates($extensionName, $ownerName, $deliveryAddress, $cart);

		if (!empty($shippingRates))
		{
			$shippingList[] = (object) array(
				'value' => $this->shippingName,
				'text' => $this->params->get('shipping_title', $this->shippingName),
				'logo' => $this->params->get('shipping_logo', ''),
				'params' => $this->shippingHelper->params,
				'helper' => $this->shippingHelper,
				'shippingRates' => $shippingRates,
			);
		}
	}

	/**
	 * Sets plugin parameters specific to given extension name (if extension have its own configuration)
	 *
	 * @param   string  $extensionName  Name of the extension
	 * @param   string  $ownerName      Name of the owner
	 *
	 * @return void
	 */
	protected function setRedshippingOptions($extensionName, $ownerName)
	{
		$pluginOptions                       = RedshopbShippingHelper::getShippingParams($this->shippingName, $extensionName, $ownerName);
		$this->shippingHelper->pluginEnabled = (bool) $pluginOptions->state;
		$this->shippingHelper->params        = $pluginOptions->params;
	}

	/**
	 * Loads Shipping Helper object
	 *
	 * @return RedshopbShippingPluginHelperShipping
	 */
	protected function loadShippingHelper()
	{
		if (!$this->shippingHelper)
		{
			$reflector  = new ReflectionClass(get_class($this));
			$helperPath = dirname($reflector->getFileName());

			if (file_exists($helperPath . '/helpers/shipping.php'))
			{
				require_once $helperPath . '/helpers/shipping.php';

				$helperClass          = 'ShippingHelper' . ucfirst($this->shippingName);
				$this->shippingHelper = new $helperClass($this->params);
			}
		}

		return $this->shippingHelper;
	}

	/**
	 * Sets plugin parameters specific to given extension name (if extension have its own configuration)
	 *
	 * @param   string  $shippingName   Shipping name
	 * @param   string  $extensionName  Name of the extension
	 * @param   string  $ownerName      Name of the owner
	 *
	 * @return boolean
	 */
	protected function isShippingEnabled($shippingName, $extensionName, $ownerName)
	{
		if ($shippingName != $this->shippingName)
		{
			return false;
		}

		$this->setRedshippingOptions($extensionName, $ownerName);

		// Plugin is disabled (optionally for this extension)
		if (!$this->shippingHelper->pluginEnabled)
		{
			return false;
		}

		return true;
	}
}
