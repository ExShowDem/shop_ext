<?php
/**
 * @package     Aesir.E-Commerce.Plugin
 * @subpackage  Rb_sync
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('JPATH_BASE') or die;

use Joomla\CMS\Factory;
use Joomla\Registry\Registry;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Plugin\CMSPlugin;
/**
 * Webservice Sync plugin.
 *
 * @package     Aesir.E-Commerce.Plugin
 * @subpackage  Webservice Sync
 * @since       1.0
 */
class PlgRb_SyncWebservice extends CMSPlugin
{
	/**
	 * Source name from where this plugin gets his data
	 *
	 * @var string
	 */
	protected $src = 'RedshopbSync';

	/**
	 * Load the language file on instantiation.
	 *
	 * @var    boolean
	 */
	protected $autoloadLanguage = true;

	/**
	 * Client
	 *
	 * @var object
	 */
	protected $client;

	/**
	 * Get data and update relevant tables
	 *
	 * @param   string  $src              Src
	 * @param   string  $func             Func
	 * @param   RTable  $webserviceData   All current webservice data
	 *
	 * @return  boolean  true on success
	 */
	public function onFuncRead($src, $func, &$webserviceData)
	{
		if ($src !== $this->src)
		{
			return false;
		}

		Table::addIncludePath(JPATH_SITE . '/components/com_redshopb/tables');
		RLoader::registerPrefix('Redshopb', JPATH_LIBRARIES . '/redshopb');

		$className = $func;

		// Rejects the functions from other plugins
		if (strpos($className, 'Webservice') !== 0)
		{
			return false;
		}

		if (!class_exists($className))
		{
			$path = __DIR__ . '/functions/' . $className . '.php';

			if (file_exists($path))
			{
				require_once $path;
			}

			if (!class_exists($className))
			{
				// This plugin does not support given function
				return false;
			}
		}

		$lang = Factory::getLanguage();
		$lang->load('com_redshopb', JPATH_SITE);

		$client = $this->getClient();

		/** @var WebserviceFunctionBase $class */
		$class = new $className;

		// Set webservice parameters
		$webserviceParams = new Registry($webserviceData->get('params'));
		$plugin           = $webserviceData->get('plugin');
		$pluginParams     = $webserviceParams->get($plugin);

		if ($pluginParams)
		{
			if (isset($pluginParams->extends_plugin_config)
				&& $pluginParams->extends_plugin_config == 1)
			{
				$this->params->loadArray($pluginParams);
			}

			$webserviceParams->set($plugin, null);
		}

		$webserviceData->set('params', $webserviceParams);

		return $class
			->setClient($client)
			->read($webserviceData, $this->params);
	}

	/**
	 * Get the base of enrichment set in the plugin options
	 *
	 * @return  string  Enrichment base
	 */
	public function onFuncGetEnrichmentBase()
	{
		return $this->params->get('enrichment_base', '');
	}

	/**
	 * Init and return client object
	 *
	 * @return RedshopbClientPim
	 */
	protected function getClient()
	{
		if (!$this->client)
		{
			require_once __DIR__ . '/client/client.php';

			$options = array(
				'remote_url'        => $this->params->get('remote_url', ''),
				'client_id'         => $this->params->get('client_id', ''),
				'client_secret'     => $this->params->get('client_secret', ''),
				'account'           => $this->params->get('account', ''),
				'password'          => $this->params->get('password', ''),
				'enableCompression' => (bool) $this->params->get('enableCompression', 1),
				'grant_type'        => 'password'
			);

			$this->client = RedshopbClientWebservice::getInstance($options);
		}

		return $this->client;
	}

	/**
	 * Get data and update relevant tables
	 *
	 * @param   string  $src       Src
	 * @param   object  $cronItem  Cron Item
	 *
	 * @return  boolean  true on success
	 *
	 * @throws  RuntimeException
	 */
	public function onFuncCronClearHashedKeys($src, $cronItem)
	{
		if ($src !== $this->src)
		{
			return false;
		}

		if (ucfirst($cronItem->plugin) != 'Webservice')
		{
			return false;
		}

		if (!class_exists($cronItem->name))
		{
			$className = ucfirst($cronItem->plugin) . $cronItem->name;
			$path      = __DIR__ . '/functions/' . $className . '.php';

			if (file_exists($path))
			{
				require_once $path;
			}

			if (!class_exists($className))
			{
				// This plugin does not support given function
				return false;
			}
		}

		/** @var WebserviceFunctionBase $class */
		$class = new $className;

		return $class->clearHashKeys();
	}
}
