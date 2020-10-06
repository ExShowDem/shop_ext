<?php
/**
 * @package     Aesir.E-Commerce.Plugin
 * @subpackage  Client
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */
defined('_JEXEC') or die;

use Joomla\Registry\Registry;
use Joomla\CMS\Table\Table;

/**
 * Redshopb Webservice Client.
 *
 * @package     Aesir.E-Commerce
 * @subpackage  Client
 * @since       1.0
 */
class RedshopbClientWebservice
{
	/**
	 * @var string
	 */
	public $serverUrl;

	/**
	 * @var integer
	 */
	public $clientId;

	/**
	 * @var string
	 */
	public $clientSecret;

	/**
	 * @var string
	 */
	public $accessToken;

	/**
	 * @var string
	 */
	public $error;

	/**
	 * @var integer
	 */
	public $enableCompression;

	/**
	 * @var array
	 */
	protected static $instance = array();

	/**
	 * Constructor.
	 *
	 * @param   array  $options  The options for the client
	 */
	private function __construct(array $options = array())
	{
		$this->serverUrl         = $options['remote_url'];
		$this->clientId          = $options['client_id'];
		$this->clientSecret      = $options['client_secret'];
		$this->enableCompression = $options['enableCompression'];

		// Generate access token
		$this->getAccessToken();
	}

	/**
	 * Method for get access_token (Authorized by OAuth2)
	 *
	 * @return  string  Access token
	 */
	public function getAccessToken()
	{
		if (!$this->accessToken)
		{
			$this->accessToken = (new Registry($this->getPluginTable()->get('params')))
				->get('access_token', null);

			if (!$this->accessToken)
			{
				$this->requestAccessToken();
			}
			else
			{
				// Check if saved locally access_token is still valid
				$curl        = curl_init($this->serverUrl . 'index.php?option=resource&api=oauth2&access_token=' . $this->accessToken);
				$curlOptions = array(
					'client_id'     => $this->clientId,
					'client_secret' => $this->clientSecret,
					'grant_type'    => 'client_credentials'
				);
				curl_setopt($curl, CURLOPT_POST, true);
				curl_setopt($curl, CURLOPT_POSTFIELDS, $curlOptions);
				curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);

				$result = curl_exec($curl);
				$result = json_decode($result);

				if (!isset($result->success)
					|| !$result->success)
				{
					$this->requestAccessToken();
				}
			}
		}

		return $this->accessToken;
	}

	/**
	 * @return boolean|Table
	 * @since  2.7.3
	 */
	protected function getPluginTable()
	{
		$table = Table::getInstance('Extension');
		$table->load(['type' => 'plugin', 'folder' => 'rb_sync', 'element' => 'webservice']);

		return $table;
	}

	/**
	 * Method for return url for get access_token
	 *
	 * @return  string  Url
	 */
	public function getAccessTokenUrl()
	{
		return $this->serverUrl . 'index.php?option=token&api=oauth2';
	}

	/**
	 * Method for get access_token base on config of plugin
	 *
	 * @return  boolean  True on success. False otherwise.
	 */
	public function requestAccessToken()
	{
		$curl        = curl_init($this->getAccessTokenUrl());
		$curlOptions = array(
			'client_id'     => $this->clientId,
			'client_secret' => $this->clientSecret,
			'grant_type'    => 'client_credentials'
		);
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $curlOptions);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);

		// Enable compression
		if ($this->enableCompression)
		{
			curl_setopt($curl, CURLOPT_ENCODING, 1);

			$headers = array(
				'Accept-Encoding: gzip, deflate',
				'Content-Encoding: gzip, deflate',
			);

			curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
		}

		$result = curl_exec($curl);
		$result = json_decode($result);

		if (isset($result->error))
		{
			$this->error = $result->error_description;

			return false;
		}

		$this->accessToken = $result->access_token;

		$table  = $this->getPluginTable();
		$params = new Registry($table->get('params'));
		$params->set('access_token', $this->accessToken);
		$table->save(['params' => $params->toString()]);

		return true;
	}

	/**
	 * Get an instance or create it.
	 *
	 * @param   array  $options  The options for the soap client
	 *
	 * @return  RedshopbClientWebservice
	 */
	public static function getInstance(array $options = array())
	{
		$hash = md5(serialize($options));

		if (!isset(self::$instance[$hash]))
		{
			self::$instance[$hash] = new static($options);
		}

		return self::$instance[$hash];
	}
}
