<?php
/**
 * @package     Aesir.E-Commerce
 * @subpackage  Webservices
 *
 * @copyright   Copyright (C) 2008 - 2017 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Language\Text;

/**
 * Api Helper class for overriding default methods
 *
 * @package     Aesir.E-Commerce
 * @subpackage  Webservices
 * @since       1.6
 */
class RApiHalHelperSiteRedshopbCron
{
	/**
	 * Set document content for List view
	 *
	 * @param   array             $items          List of items
	 * @param   SimpleXMLElement  $configuration  Configuration for displaying object
	 * @param   RApiHalHal        $apiHal         Hal main object
	 *
	 * @return void
	 */
	public function setForRenderList($items, $configuration, RApiHalHal $apiHal)
	{
		$xml = RComponentHelper::getComponentManifestFile('com_redshopb');

		if ($xml)
		{
			$version = (string) $xml->version;
			$apiHal->setData('vanir_version', $version);
		}

		$xml = RComponentHelper::getComponentManifestFile('com_redcore');

		if ($xml)
		{
			$version = (string) $xml->version;
			$apiHal->setData('redcore_version', $version);
		}

		$xml = RComponentHelper::getComponentManifestFile('com_reditem');

		if ($xml)
		{
			$version = (string) $xml->version;
			$apiHal->setData('aesir_version', $version);
		}

		$db = Factory::getDbo();
		PluginHelper::importPlugin('rb_sync');
		$apiHal->setData('joomla_version', JVERSION);
		$apiHal->setData('php_version', PHP_VERSION);
		$apiHal->setData('mysql_version', $db->getVersion());

		foreach ($items as $item)
		{
			$item->title = Text::_('COM_REDSHOPB_SYNC_' . $item->plugin . '_' . $item->name);
		}

		$apiHal->setForRenderList($items, $configuration);
	}
}
