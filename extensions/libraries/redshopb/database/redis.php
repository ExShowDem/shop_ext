<?php
/**
 * @package     Aesir.E-Commerce.Library
 * @subpackage  Database
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
/**
 * Redis Database class
 *
 * @package     Aesir.E-Commerce.Library
 * @subpackage  Database
 * @since       1.13.2
 */
class RedshopbDatabaseRedis
{
	/**
	 * @var integer
	 *
	 * Redis database index for this instance
	 * @since 1.13.2
	 */
	protected $index = 0;

	/**
	 * @var Redis
	 *
	 * Redis object used by this instance
	 *
	 * @since   1.13.2
	 */
	protected $redis = null;

	/**
	 * Initializes the Redis database using the assigned DB index, or auto-assigning one and optionally flushing the DB
	 *
	 * @param   bool    $flush  Flush the DB
	 *
	 * @since   1.13.2
	 * @throws  Exception
	 */
	public function __construct($flush = false)
	{
		// If no redis support is found, aborts every other initialization without setting the index
		if (!class_exists('Redis'))
		{
			throw new Exception('No Redis support for PHP found', 590);
		}

		$this->redis = new Redis;

		$host = RedshopbApp::getConfig()->getString('redis_host', 'localhost');
		$port = RedshopbApp::getConfig()->getInt('redis_port', '6379');

		try
		{
			// Tries to connect to the Redis database
			if (!$this->redis->pconnect($host, $port))
			{
				$this->redis = null;

				throw new Exception('Could not find a connection to Redis', 591);
			}
		}
		catch (Exception $exception)
		{
			throw new Exception('Could not find a connection to Redis: ' . $exception->getMessage(), 591);
		}

		// Gets (or sets) the DB index according to the mysql DB name to get unique instances
		$db          = Factory::getConfig()->get('db');
		$this->index = $this->redis->get('vanir_db_' . $db);

		if (!$this->index)
		{
			$this->index = 0;
			$dbKeys      = $this->redis->keys('vanir_db_*');

			if ($dbKeys && count($dbKeys))
			{
				foreach ($dbKeys as $dbKey)
				{
					$dbIndex = $this->redis->get($dbKey);

					if ($dbIndex > $this->index)
					{
						$this->index = $dbIndex;
					}
				}
			}

			$this->index ++;
		}

		$this->redis->set('vanir_db_' . $db, $this->index);
		$this->redis->select($this->index);

		if ($flush)
		{
			$this->redis->flushDB();
		}
	}

	/**
	 * Gets the current DB index
	 *
	 * @return  integer
	 * @since   1.13.2
	 */
	public function getIndex()
	{
		if (is_null($this->redis))
		{
			return 0;
		}

		return $this->index;
	}

	/**
	 * Flushes the current database
	 *
	 * @return  boolean
	 * @since   1.13.2
	 */
	public function flushDB()
	{
		if (is_null($this->redis))
		{
			return false;
		}

		return $this->redis->flushDB();
	}

	/**
	 * Gets a certain key
	 *
	 * @param   string  $key        Key name (or number)
	 * @param   string  $prefix     Key prefix
	 * @param   string  $separator  Separator between the prefix and key
	 *
	 * @return  string|false
	 * @since   1.13.2
	 */
	public function getKey($key, $prefix, $separator = '_')
	{
		return $this->redisWrapper($key, $prefix, 'get', [], $separator);
	}

	/**
	 * Gets multiple keys
	 *
	 * @param   array   $keys          Array of keys
	 * @param   string  $prefix        Key prefix
	 * @param   int     $maxChunkSize  Max chunk size to get from Redis at a single time
	 *
	 * @return  array|false
	 * @since   1.13.2
	 */
	public function getKeys($keys, $prefix = '', $maxChunkSize = 100000)
	{
		if (is_null($this->redis))
		{
			return false;
		}

		if (!is_array($keys) || empty($keys))
		{
			return false;
		}

		$redisArray = array();

		// Array with actual ids, it splits in chunks for Redis processing
		$keysSplit = array_chunk($keys, $maxChunkSize);

		foreach ($keysSplit as $keysPart)
		{
			// Appends the prefix to every key, if found
			if ($prefix != '')
			{
				$keysPart = preg_filter('/^/', $prefix . '_', $keysPart);
			}

			$redisVals = $this->redis->mget($keysPart);

			if (!empty($redisVals))
			{
				$redisArray = array_merge($redisArray, $redisVals);
			}
		}

		return $redisArray;
	}

	/**
	 * Sets a certain key value
	 *
	 * @param   string  $key        Key name (or number)
	 * @param   string  $value      Value to be set to the key
	 * @param   string  $prefix     Key prefix
	 * @param   string  $separator  Separator between the prefix and key
	 *
	 * @return  boolean
	 * @since   1.13.2
	 */
	public function setKey($key, $value, $prefix, $separator = '_')
	{
		return $this->redisWrapper($key, $prefix, 'set', array($value), $separator);
	}

	/**
	 * Sets expiration
	 *
	 * @param   string  $key        Key name (or number)
	 * @param   string  $expire     The hamount (in milliseconds) the key should live for
	 * @param   string  $prefix     Key prefix
	 * @param   string  $separator  Separator between the prefix and key
	 *
	 * @return  boolean
	 * @since   1.13.2
	 */
	public function expire($key, $expire, $prefix, $separator = '_')
	{
		return $this->redisWrapper($key, $prefix, 'expire', array($expire), $separator);
	}

	/**
	 * Deletes a key
	 *
	 * @param   mixed   $key           Key name (or number).  It can also be an array of keys to delete
	 * @param   string  $prefix        Key prefix
	 * @param   int     $maxChunkSize  Max chunk size to delete from Redis at a single time if doing arrays
	 *
	 * @return  boolean
	 * @since   1.13.2
	 */
	public function delKey($key, $prefix = '', $maxChunkSize = 100000)
	{
		if (is_null($this->redis))
		{
			return false;
		}

		if (!is_array($key))
		{
			// Appends prefix to the key, if any
			$key = ($prefix == '' ? '' : $prefix . '_') . $key;

			// If key is not found, returns success
			if (!$this->redis->exists($key))
			{
				return true;
			}

			// Attempts to delete the key(s)
			return $this->redis->del($key);
		}

		// Empty array
		if (!count($key))
		{
			return true;
		}

		// Array with actual ids, it splits in chunks for Redis processing
		$keysSplit = array_chunk($key, $maxChunkSize);

		foreach ($keysSplit as $keysPart)
		{
			// Appends the prefix to every key, if found
			if ($prefix != '')
			{
				$keysPart = preg_filter('/^/', $prefix . '_', $keysPart);
			}

			$this->redis->del($keysPart);
		}

		return true;
	}

	/**
	 * Sets a value for multiple given keys with a single prefix
	 *
	 * @param   array   $keys          Key names (or numbers)
	 * @param   string  $value         Value to set for all
	 * @param   string  $prefix        Keys prefix
	 * @param   int     $maxChunkSize  Max chunk size to insert into Redis at a single time
	 *
	 * @return  boolean
	 * @since   1.13.2
	 */
	public function setKeysSingleValue($keys, $value, $prefix = '', $maxChunkSize = 100000)
	{
		if (is_null($this->redis))
		{
			return false;
		}

		$keysSplit = array_chunk($keys, $maxChunkSize);

		foreach ($keysSplit as $keysPart)
		{
			// Appends the prefix to every key, if found
			if ($prefix != '')
			{
				$keysPart = preg_filter('/^/', $prefix . '_', $keysPart);
			}

			// Sets the value given for every key
			$keysPart = array_fill_keys($keysPart, $value);

			// Saves all keys to redis server
			if (!$this->redis->mSet($keysPart))
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * Stores a whole array of keys and values into Redis
	 *
	 * @param   array  $data          Array of keys and values
	 * @param   int    $maxChunkSize  Max chunk size to insert into Redis at a single time
	 *
	 * @return  boolean
	 * @since   1.13.2
	 */
	public function setKeyArray($data, $maxChunkSize = 100000)
	{
		if (is_null($this->redis))
		{
			return false;
		}

		$dataSplit = array_chunk($data, $maxChunkSize, true);

		foreach ($dataSplit as $dataPart)
		{
			// Saves all keys to redis server
			if (!$this->redis->mSet($dataPart))
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * Gets keys based on a pattern
	 *
	 * @param   string  $pattern    Pattern to search keys from
	 * @param   string  $prefix     Prefix to append to the pattern
	 * @param   string  $separator  Separator between the prefix and key
	 *
	 * @return  array|false
	 * @since   1.13.2
	 */
	public function getKeysPattern($pattern, $prefix = '', $separator = '_')
	{
		return $this->redisWrapper($pattern, $prefix, 'keys', [], $separator);
	}

	/**
	 * @param   string  $pattern    Key pattern
	 * @param   string  $prefix     Key Prefix
	 * @param   string  $function   Function call
	 * @param   array   $params     Params to add (don't add pattern, it will be added automatically)
	 * @param   string  $separator  Separator between the prefix and key
	 *
	 * @return  mixed|false
	 * @since   1.13.2
	 */
	public function redisWrapper($pattern, $prefix, $function, $params = [], $separator = '_')
	{
		if (is_null($this->redis))
		{
			return false;
		}

		// Appends prefix to the key, if any
		$pattern = ($prefix == '' ? '' : $prefix . $separator) . $pattern;

		array_unshift($params, $pattern);

		return call_user_func_array(array($this->redis, $function), $params);
	}
}
