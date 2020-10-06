<?php
/**
 * @package     Aesir.E-Commerce.Plugin
 * @subpackage  Filesync
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('JPATH_BASE') or die;

use Joomla\CMS\Language\Text;

/**
 * Interface to handle api calls
 *
 * @package  Redshopb
 *
 * @since    1.6.24
 */
class RbfilesyncHelperHal extends  RApiHalHelper
{
	/**
	 * Load configuration file and set all Api parameters
	 *
	 * @param   array   $webserviceName  Name of the webservice file
	 * @param   string  $version         Suffixes for loading of webservice configuration file
	 * @param   string  $extension       File extension name
	 * @param   string  $path            Path to webservice files
	 * @param   string  $client          Client
	 *
	 * @return  SimpleXMLElement  Loaded configuration object
	 *
	 * @throws  Exception
	 */
	public static function loadWebserviceConfiguration($webserviceName, $version = '', $extension = 'xml', $path = '', $client = '')
	{
		// Check possible overrides, and build the full path to api file
		$configurationFullPath = self::getWebserviceFile($client, strtolower($webserviceName), $version, $extension, $path);

		if (!is_readable($configurationFullPath))
		{
			throw new Exception(Text::_('LIB_REDCORE_API_HAL_WEBSERVICE_CONFIGURATION_FILE_UNREADABLE'));
		}

		$content = @file_get_contents($configurationFullPath);

		if (is_string($content))
		{
			return new SimpleXMLElement($content);
		}

		return null;
	}

	/**
	 * Method to finds the full real file path, checking possible overrides
	 *
	 * @param   string  $client          Client
	 * @param   string  $webserviceName  Name of the webservice
	 * @param   string  $version         Suffixes to the file name (ex. 1.0.0)
	 * @param   string  $extension       Extension of the file to search
	 * @param   string  $path            Path to webservice files
	 *
	 * @return  string  The full path to the api file
	 */
	public static function getWebserviceFile($client, $webserviceName, $version = '', $extension = 'xml', $path = '')
	{
		JLoader::import('joomla.filesystem.path');

		if (!empty($webserviceName))
		{
			$version        = !empty($version) ? array(JPath::clean($version)) : array('1.0.0');
			$webservicePath = !empty($path) ? self::getWebservicesPath() . '/' . $path : self::getWebservicesPath();

			// Search for suffixed versions. Example: content.1.0.0.xml
			if (!empty($version))
			{
				foreach ($version as $suffix)
				{
					$rawPath = $webserviceName . '.' . $suffix;
					$rawPath = !empty($extension) ? $rawPath . '.' . $extension : $rawPath;
					$rawPath = !empty($client) ? $client . '.' . $rawPath : $rawPath;

					$configurationFullPath = JPath::find($webservicePath, $rawPath);

					if ($configurationFullPath)
					{
						return $configurationFullPath;
					}
				}
			}

			// Standard version
			$rawPath = !empty($extension) ? $webserviceName . '.' . $extension : $webserviceName;
			$rawPath = !empty($client) ? $client . '.' . $rawPath : $rawPath;

			return JPath::find($webservicePath, $rawPath);
		}

		return null;
	}

	/**
	 * Loading of related XML files
	 *
	 * @param   string  $client              Client
	 * @param   string  $webserviceName      Webservice name
	 * @param   string  $lastExecuteVersion  Last execute version of the webservice
	 * @param   string  $path                Path to webservice files
	 *
	 * @throws Exception
	 * @return  array
	 */
	public static function getNotExecutedWebServices($client = '', $webserviceName = '', $lastExecuteVersion = '0', $path = '')
	{
		$filter            = $client . '.' . $webserviceName . '.(.{1,}).xml';
		self::$webservices = array();

		$webserviceXmls = JFolder::files(
			self::getWebservicesPath() . '/' . $path, $filter,
			false, false, array('.svn', 'CVS', '.DS_Store', '__MACOSX'),
			array('^\..*', '.*~'), true
		);

		if ($webserviceXmls)
		{
			foreach ($webserviceXmls as $webserviceXml)
			{
				if (preg_match("/$filter/", $webserviceXml, $matches))
				{
					$fileVersion = $matches[1];

					if (version_compare($lastExecuteVersion, $fileVersion, '<'))
					{
						// Version, Extension and Client are already part of file name
						$xml = self::loadWebserviceConfiguration($webserviceName, $fileVersion, 'xml', $path, $client);

						if (!empty($xml))
						{
							$client  = self::getWebserviceClient($xml);
							$version = !empty($xml->config->version) ? (string) $xml->config->version : $fileVersion;

							self::$webservices[$client][(string) $xml->config->name][$version] = $xml;
						}
					}
				}
			}
		}

		return self::$webservices;
	}

	/**
	 * Get Webservices path
	 *
	 * @return  string
	 */
	public static function getWebservicesPath()
	{
		return JPATH_ROOT . '/' . self::getWebservicesRelativePath();
	}

	/**
	 * Get Webservices path
	 *
	 * @return  string
	 */
	public static function getWebservicesRelativePath()
	{
		return 'media/com_redshopb';
	}
}
