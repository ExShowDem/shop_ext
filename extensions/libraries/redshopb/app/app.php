<?php
/**
 * @package     Aesir.E-Commerce.Library
 * @subpackage  App
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Table\Table;

/**
 * App factory.
 *
 * @since  1.7
 */
abstract class RedshopbApp
{
	/**
	 * @var    RedshopbEntityCompany
	 * @since  2.0
	 */
	protected static $b2cCompany;

	/**
	 * @var    RedshopbEntityConfig
	 * @since  1.7
	 */
	private static $config;

	/**
	 * @var    JDatabaseDriver
	 * @since  2.0
	 */
	private static $dbo;

	/**
	 * @var    RedshopbEntityCurrency
	 * @since  2.0
	 */
	private static $defaultCurrency;

	/**
	 * @var Vanir\Access\Specification
	 * @since  2.0
	 */
	private static $accessSpecification;

	/**
	 * @var    RedshopbEntityCompany
	 * @since  2.0
	 */
	protected static $mainCompany;

	/**
	 * @var    RedshopbEntityUser
	 * @since  1.7
	 */
	protected static $user;

	/**
	 * Root component asset
	 *
	 * @var    Table
	 * @since  1.7
	 */
	private static $rootAsset;

	/**
	 * Get the active B2C company
	 *
	 * @return  RedshopbEntityCompany
	 *
	 * @since   2.0
	 */
	public static function getB2cCompany()
	{
		if (null === static::$b2cCompany)
		{
			static::loadB2cCompany();
		}

		return static::$b2cCompany;
	}

	/**
	 * Get the configuration
	 *
	 * @return  RedshopbEntityConfig
	 *
	 * @since   1.7
	 */
	public static function getConfig()
	{
		if (null === self::$config)
		{
			self::$config = RedshopbEntityConfig::getInstance();
		}

		return self::$config;
	}

	/**
	 * Get the database driver
	 *
	 * @return  JDatabaseDriver
	 *
	 * @since   2.0
	 */
	public static function getDbo()
	{
		if (null === self::$dbo)
		{
			self::$dbo = Factory::getDbo();
		}

		return self::$dbo;
	}

	/**
	 * Get the default currency
	 *
	 * @return  RedshopbEntityCurrency
	 *
	 * @since   2.0
	 */
	public static function getDefaultCurrency()
	{
		if (null === self::$defaultCurrency)
		{
			static::loadDefaultCurrency();
		}

		return self::$defaultCurrency;
	}

	/**
	 * Method to get the access specification object
	 *
	 * @return \Vanir\Access\Specification
	 */
	public static function getAccessSpecification()
	{
		if (null === self::$accessSpecification)
		{
			static::loadAccessSpecification();
		}

		return self::$accessSpecification;
	}

	/**
	 * Get the company set as main
	 *
	 * @return  RedshopbEntityCompany
	 *
	 * @since   2.0
	 */
	public static function getMainCompany()
	{
		if (null === static::$mainCompany)
		{
			static::loadMainCompany();
		}

		return static::$mainCompany;
	}

	/**
	 * Get the active user
	 *
	 * @return  RedshopbEntityUser
	 *
	 * @since   1.7
	 */
	public static function getUser()
	{
		if (null === static::$user)
		{
			static::$user = RedshopbEntityUser::loadActive();
		}

		return static::$user;
	}

	/**
	 * Get com_redshop root asset
	 *
	 * @return  Table
	 *
	 * @since   1.7
	 */
	public static function getRootAsset()
	{
		if (null === self::$rootAsset)
		{
			static::loadRootAsset();
		}

		return self::$rootAsset;
	}

	/**
	 * Check if B2C is enabled on the system
	 *
	 * @return  boolean
	 *
	 * @since   2.0
	 */
	public static function isB2cEnabled()
	{
		$b2cCompany = static::getB2cCompany();

		return $b2cCompany->isLoaded();
	}

	/**
	 * Load active B2C company from DB
	 *
	 * @return  void
	 *
	 * @since   2.0
	 */
	protected static function loadB2cCompany()
	{
		static::$b2cCompany = RedshopbEntityCompany::getInstance();

		$db    = static::getDbo();
		$query = $db->getQuery(true)
			->select($db->qn('c') . '.*')
			->from($db->qn('#__redshopb_company', 'c'))
			->where($db->qn('c.b2c') . ' = 1')
			->where($db->qn('c.state') . ' = ' . (int) RedshopbEntityCompany::STATE_ENABLED)
			->where($db->qn('c.deleted') . ' = 0');

		$db->setQuery($query, 0, 1);

		$company = $db->loadObject();

		if ($company)
		{
			static::$b2cCompany = RedshopbEntityCompany::load($company->id)->bind($company);
		}
	}

	/**
	 * Load the default currency
	 *
	 * @return  void
	 *
	 * @throws  RuntimeException  When default currency was not found
	 *
	 * @since   2.0
	 */
	protected static function loadDefaultCurrency()
	{
		$config = self::getConfig();

		$currencyId = (int) $config->get('default_currency', 38);

		$currency = RedshopbEntityCurrency::load($currencyId);

		if (!$currency->isLoaded())
		{
			throw new RuntimeException("Default currency was not found: " . $currencyId);
		}

		self::$defaultCurrency = $currency;
	}

	/**
	 * Load the access specification from component access.xml
	 *
	 * @return  void
	 *
	 * @throws  RuntimeException  If access file counld not be found
	 *
	 * @since   2.0
	 */
	protected static function loadAccessSpecification()
	{
		$accessXmlPath    = JPATH_SITE . '/components/com_redshopb/access.xml';
		$specificationXml = simplexml_load_file($accessXmlPath);

		if (!$specificationXml)
		{
			throw new RuntimeException("Unable to load access.xml: " . $accessXmlPath);
		}

		// Give us a chance to alter the specification at runtime, if we need to
		RFactory::getDispatcher()->trigger('onBeforeLoadRedshopbAccessSpecification', array($specificationXml));

		self::$accessSpecification = new Vanir\Access\Specification($specificationXml);
	}

	/**
	 * Load the company set as main
	 *
	 * @return  void
	 *
	 * @throws  RuntimeException  If no main company is found
	 *
	 * @since   2.0
	 */
	protected static function loadMainCompany()
	{
		$db = static::getDbo();

		$query = $db->getQuery(true)
			->select($db->qn('c') . '.*')
			->from($db->qn('#__redshopb_company', 'c'))
			->where($db->qn('c.deleted') . ' = 0')
			->where($db->qn('c.type') . ' = ' . $db->q('main'))
			->where($db->qn('c.state') . ' = ' . (int) RedshopbEntityCompany::STATE_ENABLED);

		$db->setQuery($query, 0, 1);

		$company = $db->loadObject();

		if (!$company)
		{
			throw new RuntimeException("Error getting the main company");
		}

		static::$mainCompany = RedshopbEntityCompany::load($company->id)->bind($company);
	}

	/**
	 * Load the root asset
	 *
	 * @return  void
	 *
	 * @throws  RuntimeException
	 *
	 * @since   1.7
	 */
	protected static function loadRootAsset()
	{
		$assetTable = Table::getInstance('Asset');

		if (!$assetTable instanceof Table || !$assetTable->load(Array('name' => 'com_redshopb')))
		{
			throw new RuntimeException("Error getting root asset");
		}

		self::$rootAsset = $assetTable;
	}

	/**
	 * Method for check if system use load more ajax pagination or not.
	 *
	 * @return boolean
	 *
	 * @since  1.9
	 */
	public static function isUseAjaxReadMorePagination()
	{
		return (boolean) self::getConfig()->get('no_pagination', 0);
	}

	/**
	 * Method for check if system enable "Rich Snippet" (Schema.org) or not.
	 *
	 * @return  boolean  True for yes.
	 *
	 * @since   1.13.0
	 */
	public static function useRichSnippets()
	{
		return (boolean) self::getConfig()->get('rich_snippet', 0);
	}
}
