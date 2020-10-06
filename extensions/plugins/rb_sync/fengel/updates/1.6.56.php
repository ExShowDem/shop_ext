<?php
/**
 * @package     Aesir.E-Commerce
 * @subpackage  Upgrade
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\Registry\Registry;
use Joomla\CMS\Factory;
/**
 * Custom upgrade of Redshop b2b plugin.
 *
 * @package     Aesir.E-Commerce
 * @subpackage  Upgrade
 * @since       1.6.56
 */
class FengelUpdateScript_1_6_56
{
	/**
	 * Performs the upgrade for this version
	 *
	 * @return  boolean
	 */
	public function execute()
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->select('c.*, e.params AS pluginParams')
			->from($db->qn('#__redshopb_cron', 'c'))
			->leftJoin(
				$db->qn('#__extensions', 'e') . ' ON e.element = c.plugin AND e.type = ' . $db->q('plugin')
				. ' AND e.folder = ' . $db->q('rb_sync')
			)
			->where('c.plugin = ' . $db->q('fengel'));

		$crons = $db->setQuery($query)
			->loadObjectList();

		if ($crons)
		{
			foreach ($crons as $cron)
			{
				$needUpdate = false;
				$cronParams = new Registry;
				$cronParams->loadString($cron->params);

				$pluginParams = new Registry;
				$pluginParams->loadString($cron->pluginParams);

				$key   = 'source';
				$param = $cronParams->get($key);

				if ($param)
				{
					$pluginParams->set($key, $param);
					$cronParams->set($key, null);
					$needUpdate = true;
				}

				$key   = 'url';
				$param = $cronParams->get($key);

				if ($param)
				{
					$pluginParams->set($key, $param);
					$cronParams->set($key, null);
					$needUpdate = true;
				}

				$key   = 'folder';
				$param = $cronParams->get($key);

				if ($param)
				{
					$pluginParams->set($key, $param);
					$cronParams->set($key, null);
					$needUpdate = true;
				}

				if ($needUpdate)
				{
					$pluginParams->set('extends_plugin_config', 1);
					$cronParams->set($cron->plugin, $pluginParams->toArray());
					$pluginCronParams = $cronParams->toString();

					$query->clear()
						->update($db->qn('#__redshopb_cron'))
						->set('params = ' . $db->q($pluginCronParams))
						->where('id = ' . (int) $cron->id);

					$db->setQuery($query)
						->execute();
				}
			}
		}

		return true;
	}
}
