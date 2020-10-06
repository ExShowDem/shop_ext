<?php
/**
 * @package     Aesir.E-Commerce.Plugin
 * @subpackage  PIM
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
 * PIM Sync class.
 *
 * @package     Aesir.E-Commerce.Plugin
 * @subpackage  PIM
 * @since       1.0
 */
class PlgRb_SyncPim extends CMSPlugin
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
	 * @var RedshopbHelperSync
	 */
	protected $sync;

	/**
	 * Constructor
	 *
	 * @param   object  $subject  The object to observe
	 * @param   array   $config   An optional associative array of configuration settings.
	 *                            Recognized key values include 'name', 'group', 'params', 'language'
	 *                            (this list is not meant to be comprehensive).
	 */
	public function __construct($subject, array $config)
	{
		parent::__construct($subject, $config);

		$this->sync = new RedshopbHelperSync;
	}

	/**
	 * Get data and update relevant tables
	 *
	 * @param   string  $src              Src
	 * @param   string  $func             Func
	 * @param   RTable  $webserviceData   All current webservice data
	 *
	 * @return  boolean  true on success
	 *
	 * @throws  RuntimeException
	 */
	public function onFuncRead($src, $func, &$webserviceData)
	{
		if ($src !== $this->src)
		{
			return false;
		}

		require_once JPATH_LIBRARIES . '/redcore/bootstrap.php';
		Table::addIncludePath(JPATH_SITE . '/components/com_redshopb/tables');
		RLoader::registerPrefix('Redshopb', JPATH_LIBRARIES . '/redshopb');

		$className = $func;

		// Rejects the functions from other plugins
		if (strpos($className, 'Pim') !== 0)
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

		$url = $webserviceParams->get('url', '');
		$webserviceData->set('params', $webserviceParams);
		$client = $this->getClient($url);
		$client->initInstance();

		/** @var PimFunctionBase $class */
		$class = new $className;

		return $class
			->setClient($client)
			->read($webserviceData, $this->params);
	}

	/**
	 * Init and return client object
	 *
	 * @param   string  $localFolder  Local folder
	 *
	 * @return RedshopbClientPim
	 */
	protected function getClient($localFolder = '')
	{
		if (!$this->client)
		{
			require_once __DIR__ . '/client/client.php';
			$localFolder  = JPATH_ROOT . '/media/com_redshopb/' . $this->params->get('localFolder', '');
			$this->client = RedshopbClientPim::getInstance($localFolder);
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

		if (ucfirst($cronItem->plugin) != 'Pim')
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

		/** @var PimFunctionBase $class */
		$class = new $className;

		return $class->clearHashKeys();
	}
}
