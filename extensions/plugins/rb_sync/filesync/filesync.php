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
use Joomla\CMS\Plugin\CMSPlugin;

JLoader::registerPrefix('Rbfilesync', __DIR__);

/**
 * FTP Sync plugin.
 *
 * @package     Aesir.E-Commerce.Plugin
 * @subpackage  FTP Sync
 * @since       1.0
 */
class PlgRb_SyncFilesync extends CMSPlugin
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
	 * @param   object  $webserviceData   All current webservice data
	 *
	 * @return  array|boolean  true on success
	 *
	 * @throws  RuntimeException
	 */
	public function onFuncRead($src, $func, &$webserviceData)
	{
		if ($src !== $this->src)
		{
			return false;
		}

		$cronFunctionName = substr($func, 8);
		$fileName         = strtolower($cronFunctionName);
		JLoader::import('redshopb.library');
		$this->params->set('optionName', 'redshopb');
		$this->params->set('webserviceClient', 'site');
		$this->params->set('viewName', $fileName);
		$this->params->set('cronFunctionName', $cronFunctionName);

		// Set webservice parameters
		$webserviceParams = new Registry($webserviceData->params);
		$this->params->set('webserviceParams', $webserviceParams);

		return RbfilesyncHelperParser::getInstance($this->params)
			->executeAll();
	}
}
