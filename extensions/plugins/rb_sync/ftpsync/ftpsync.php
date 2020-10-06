<?php
/**
 * @package     Aesir.E-Commerce.Plugin
 * @subpackage  Fengel
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('JPATH_BASE') or die;

use Joomla\Registry\Registry;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Plugin\CMSPlugin;

/**
 * FTP Sync plugin.
 *
 * @package     Aesir.E-Commerce.Plugin
 * @subpackage  FTP Sync
 * @since       1.0
 */
class PlgRb_SyncFtpsync extends CMSPlugin
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
	 * Get data and update relevant tables
	 *
	 * @param   string  $src              Src
	 * @param   string  $func             Func
	 * @param   RTable  $webserviceData   All current webservice data
	 *
	 * @return  array|boolean  true on success
	 *
	 * @throws  RuntimeException
	 */
	public function onFuncRead($src, $func, &$webserviceData)
	{
		if ($src !== $this->src || $func != 'FtpsyncFTPSync')
		{
			return false;
		}

		Table::addIncludePath(JPATH_SITE . '/components/com_redshopb/tables');
		RLoader::registerPrefix('Redshopb', JPATH_LIBRARIES . '/redshopb');

		require_once __DIR__ . '/client/client.php';

		$ftpClient = new RedshopbClientFtpsync;

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

		return $ftpClient->onFTPSync($webserviceData, $this->params);
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

		if (ucfirst($cronItem->plugin) != 'Ftpsync')
		{
			return false;
		}

		require_once __DIR__ . '/client/client.php';

		/** @var RedshopbClientFtpsync $class */
		$class = new RedshopbClientFtpsync;

		return $class->clearHashKeys();
	}
}
