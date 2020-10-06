<?php
/**
 * @package     Plugin.Vanir_Search
 * @subpackage  SOLR
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

// No direct access
defined('_JEXEC') or die;

/**
 * [PlgVanirSearchSolrConfig description]
 *
 * @since __VERSION__
 */
class PlgVanirSearchSolrConfig
{
	/**
	 * @var array
	 */
	protected $endpoints = array();

	/**
	 * @param   string  $name  Name of the endpoint
	 * @param   string  $host  IP of SOLR server
	 * @param   string  $port  Port to access
	 * @param   string  $path  Query path
	 * @param   string  $core  Name of the core to use
	 *
	 * @return $this
	 */
	public function addEndpoint($name, $host, $port, $path, $core)
	{
		$host = trim($host, '/');

		if (!preg_match('#^http(s)?://#', $host))
		{
			$host = 'http://' . $host;
		}

		$cleanUrl = parse_url($host);

		$endPoint = array(
			'host' => $cleanUrl['host'],
			'port' => $port,
			'path' => $path,
			'core' => $core);

		$this->endpoints[$name] = $endPoint;

		return $this;
	}

	/**
	 * Method to get a configuration array
	 *
	 * @param   array  $includedEndpoints  list of endpoint names to include (if empty all endpoints will be used)
	 *
	 * @return  array
	 */
	public function getConfigArray($includedEndpoints = array())
	{
		if (empty($includedEndpoints))
		{
			return array('endpoint' => $this->endpoints);
		}

		$config = array('endpoint' => array());

		foreach ($includedEndpoints AS $name)
		{
			if (empty($this->endpoints[$name]))
			{
				continue;
			}

			$config['endpoint'][$name] = $this->endpoints[$name];
		}

		return $config;
	}
}
