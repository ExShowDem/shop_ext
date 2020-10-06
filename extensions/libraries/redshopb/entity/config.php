<?php
/**
 * @package     Aesir.E-Commerce.Libraries
 * @subpackage  Entity
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\Registry\Registry;
use Joomla\CMS\Factory;
use Joomla\CMS\Filter\InputFilter;
/**
 * Represents the config
 *
 * @package     Aesir.E-Commerce.Libraries
 * @subpackage  Entity
 * @since       1.0
 *
 * @method      integer  getInt()       getInt($name, $default = null)    Get a signed integer.
 * @method      integer  getUint()      getUint($name, $default = null)   Get an unsigned integer.
 * @method      float    getFloat()     getFloat($name, $default = null)  Get a floating-point number.
 * @method      boolean  getBool()      getBool($name, $default = null)   Get a boolean.
 * @method      string   getWord()      getWord($name, $default = null)
 * @method      string   getAlnum()     getAlnum($name, $default = null)
 * @method      string   getCmd()       getCmd($name, $default = null)
 * @method      string   getBase64()    getBase64($name, $default = null)
 * @method      string   getString()    getString($name, $default = null)
 * @method      string   getHtml()      getHtml($name, $default = null)
 * @method      string   getPath()      getPath($name, $default = null)
 * @method      string   getUsername()  getUsername($name, $default = null)
 */
final class RedshopbEntityConfig
{
	/**
	 * A RedshopbEntityConfig instance.
	 *
	 * @var  RedshopbEntityConfig
	 */
	private static $instance = null;

	/**
	 * The component config.
	 *
	 * @var  Registry
	 */
	private $config;

	/**
	 * Filter object to use.
	 *
	 * @var  FilterInput
	 */
	protected $filter = null;

	/**
	 * Singleton.
	 */
	private function __construct()
	{
		$db = Factory::getDbo();

		$query   = $db->getQuery(true)
			->select('*')
			->from($db->qn('#__redshopb_config'))
			->order($db->qn('name'));
		$results = $db->setQuery($query)->loadObjectList();

		$item = array();

		foreach ($results as $result)
		{
			$item[$result->name] = json_decode($result->value);
		}

		$this->config = new Registry($item);
		$this->filter = InputFilter::getInstance();
	}

	/**
	 * Get an instance or create it.
	 *
	 * @return  RedshopbEntityConfig
	 */
	public static function getInstance()
	{
		if (!static::$instance)
		{
			static::$instance = new static;
		}

		return static::$instance;
	}

	/**
	 * Magic method to get filtered input data.
	 *
	 * @param   string  $name       Name of the filter type prefixed with 'get'.
	 * @param   array   $arguments  [0] The name of the variable [1] The default value.
	 *
	 * @return  mixed   The filtered input value.
	 */
	public function __call($name, $arguments)
	{
		if (substr($name, 0, 3) == 'get')
		{
			$filter = substr($name, 3);

			$default = null;

			if (isset($arguments[1]))
			{
				$default = $arguments[1];
			}

			return $this->get($arguments[0], $default, $filter);
		}
	}

	/**
	 * Get a config value.
	 *
	 * @param   string  $key      The config key.
	 * @param   mixed   $default  The default value if not found.
	 * @param   string  $filter   Filter to apply to the value.
	 *
	 * @return  mixed  The config value or default if not found.
	 */
	public function get($key, $default = null, $filter = 'cmd')
	{
		if (!$this->config->exists($key))
		{
			return $default;
		}

		$value = $this->config->get($key);

		if (is_array($value))
		{
			foreach ($value AS &$arrayValue)
			{
				$arrayValue = $this->filter->clean($arrayValue, $filter);
			}

			return $value;
		}

		return $this->filter->clean($this->config->get($key), $filter);
	}

	/**
	 * @param   string      $key       The config key
	 * @param   mixed       $value     The overwrite value
	 * @param   string|null $seperator The key seperator
	 *
	 * @return mixed
	 * @since __DEPLOY VERSION__
	 */
	public function set($key, $value, $seperator = null)
	{
		return $this->config->set($key, $value, $seperator);
	}

	/**
	 * Check if a config name exists.
	 *
	 * @param   string  $key  The config key.
	 *
	 * @return  boolean  True if it exsists, false otherwise.
	 */
	public function exists($key)
	{
		return $this->config->exists($key);
	}

	/**
	 * Get the thumbnail width.
	 *
	 * @return  integer  The width
	 */
	public function getThumbnailWidth()
	{
		return $this->getInt('thumbnail_width');
	}

	/**
	 * Get the thumbnails height
	 *
	 * @return  integer  The height
	 */
	public function getThumbnailHeight()
	{
		return $this->getInt('thumbnail_height');
	}

	/**
	 * Get the encryption key
	 *
	 * @return  integer  The height
	 */
	public function getEncryptionKey()
	{
		return $this->getString('encryption_key', 'redshopb');
	}

	/**
	 * Get the image header for PDFs
	 *
	 * @return  string  Image file path
	 */
	public function getImageHeader()
	{
		return $this->getString('image_header');
	}

	/**
	 * Get the image footer for PDFs
	 *
	 * @return  string  Image file path
	 */
	public function getImageFooter()
	{
		return $this->getString('image_footer');
	}

	/**
	 * Get Allowed Order By Fields
	 *
	 * @param   string  $configLayout  Config layout
	 *
	 * @return  array
	 *
	 * @since  1.13.0
	 */
	public function getAllowedOrderByFields($configLayout = 'category')
	{
		switch ($configLayout)
		{
			case 'productlist':
				$allowedOrderByFields = (array) $this->get(
					'productlist_allowed_order_by', array('relevance', 'name', 'sku', 'price', 'most_popular', 'most_purchased')
				);
				break;

			case 'ajax_search':
				$allowedOrderByFields = (array) $this->get(
					'ajaxsearch_allowed_order_by', array('name',  'sku', 'price', 'most_popular', 'most_purchased')
				);
				break;

			case 'category':
			default:
				$allowedOrderByFields = (array) $this->get(
					'category_allowed_order_by', array('name',  'sku', 'price', 'most_popular', 'most_purchased')
				);
				break;
		}

		$priceKey = array_search('price', $allowedOrderByFields);

		if (!RedshopbHelperPrices::displayPrices() && false !== $priceKey)
		{
			unset($allowedOrderByFields[$priceKey]);
		}

		return $allowedOrderByFields;
	}

	/**
	 * Get Default Order By Field
	 *
	 * @param   string  $configLayout  Config layout
	 *
	 * @return  string
	 *
	 * @since  1.13.0
	 */
	public function getDefaultOrderByField($configLayout = 'category')
	{
		$allowedOrderByFields = $this->getAllowedOrderByFields($configLayout);

		if (!empty($allowedOrderByFields))
		{
			return reset($allowedOrderByFields);
		}

		return '';
	}

	/**
	 * getAllowSplittingOrder
	 *
	 * @return boolean
	 *
	 * @since 1.12.82
	 */
	public function getAllowSplittingOrder()
	{
		if ($this->get('allow_splitting_order')
			&& $this->get('use_shipping_date'))
		{
			return true;
		}

		return false;
	}

	/**
	 * Method for get all config as array
	 *
	 * @return  array
	 */
	public function toArray()
	{
		return $this->config->toArray();
	}

	/**
	 * Method for get all config as object
	 *
	 * @return  object
	 */
	public function toObject()
	{
		return $this->config->toObject();
	}

	/**
	 *  Method for get all config as string
	 *
	 * @param   string   $format   [description]
	 *
	 * @return  string
	 */
	public function toString($format = 'JSON')
	{
		return $this->config->toString($format);
	}
}
