<?php
/**
 * @package     Aesir.E-Commerce.Library
 * @subpackage  Database
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */
defined('_JEXEC') or die;

use Joomla\CMS\Filter\InputFilter;

/**
 * Class RedshopbDatabaseIndexerStemmer
 *
 * @since  1.13.0
 */
abstract class RedshopbDatabaseIndexerStemmer
{
	/**
	 * An internal cache of stemmed tokens.
	 *
	 * @var    array
	 * @since  1.13.0
	 */
	public $cache = array();

	/**
	 * RedshopbDatabaseIndexerStemmer constructor.
	 *
	 * @param   string  $lang  Language tag
	 *
	 * @since  1.13.0
	 */
	public function __construct($lang = '*')
	{
	}

	/**
	 * Method to get a stemmer, creating it if necessary.
	 *
	 * @param   string  $lang     Language tag
	 * @param   string  $adapter  The type of stemmer to load.
	 *
	 * @return  RedshopbDatabaseIndexerStemmer  A FinderIndexerStemmer instance.
	 *
	 * @since   1.13.0
	 * @throws  Exception on invalid stemmer.
	 */
	public static function getInstance($lang = '', $adapter = 'default')
	{
		static $instances = array();

		$redshopbConfig = RedshopbApp::getConfig();

		if ($adapter == 'default')
		{
			if ($redshopbConfig->getInt('stem', 1))
			{
				$adapter = $redshopbConfig->getString('stemmer', 'snowball');
			}
		}

		// Only create one stemmer for each adapter.
		if (array_key_exists($adapter, $instances))
		{
			return $instances[$adapter];
		}

		if ($adapter == 'default')
		{
			$instances[$adapter] = new RedshopbDatabaseIndexerStemmerRaw($lang);

			return $instances[$adapter];
		}

		// Setup the adapter for the stemmer.
		$adapter = InputFilter::getInstance()->clean($adapter, 'cmd');

		if ($adapter == 'alternative')
		{
			$adapter = $lang;
		}

		$path  = __DIR__ . '/stemmer/' . $adapter . '.php';
		$class = 'RedshopbDatabaseIndexerStemmer' . ucfirst($adapter);

		// Check if a stemmer exists for the adapter.
		if (file_exists($path))
		{
			// Instantiate the stemmer.
			include_once $path;
			$instances[$adapter] = new $class($lang);
		}
		else
		{
			include_once __DIR__ . '/stemmer/snowball.php';
			$class               = 'RedshopbDatabaseIndexerStemmerSnowball';
			$instances[$adapter] = new $class($lang);
		}

		return $instances[$adapter];
	}

	/**
	 * Method to stem a token and return the root.
	 *
	 * @param   string  $token  The token to stem.
	 *
	 * @return  string  The root token.
	 *
	 * @since   1.13.0
	 */
	abstract public function stem($token);
}
