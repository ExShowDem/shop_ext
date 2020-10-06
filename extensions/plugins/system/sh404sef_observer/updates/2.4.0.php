<?php
/**
 * @package     Aesir.E-Commerce
 * @subpackage  Upgrade
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;

/**
 * Custom upgrade.
 *
 * @package     Aesir
 * @subpackage  Upgrade
 * @since       2.6.0
 */
class Sh404sef_ObserverUpdateScript_2_4_0
{
	/**
	 * Performs the upgrade for this version
	 *
	 * @return  boolean
	 */
	public function execute()
	{
		if (!PluginHelper::isEnabled('system', 'sh404sef'))
		{
			return true;
		}

		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->select('f.oldurl')
			->from($db->qn('#__sh404sef_urls', 'f'))
			->where('f.rank = 0');

		$query = $db->getQuery(true)
			->select('e.oldurl, min(e.rank) as min_rank')
			->from($db->qn('#__sh404sef_urls', 'e'))
			->where('e.oldurl not in (' . $query . ')')
			->where('e.rank > 0')
			->group('e.oldurl');

		$query = $db->getQuery(true)
			->select('q.id')
			->from($db->qn('#__sh404sef_urls', 'q'))
			->innerJoin('(' . $query . ') as w on w.oldurl = q.oldurl and w.min_rank = q.rank');

		$query = $db->getQuery(true)
			->select('g.*')
			->from('(' . $query . ') as g');

		$query = $db->getQuery(true)
			->update($db->qn('#__sh404sef_urls', 'z'))
			->set('z.rank = 0')
			->where('z.id IN (' . $query . ')');

		if (!$db->setQuery($query)->execute())
		{
			return false;
		}

		return true;
	}
}
