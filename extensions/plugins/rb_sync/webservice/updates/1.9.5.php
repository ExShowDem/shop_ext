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

/**
 * Custom upgrade of Redshop b2b.
 *
 * @package     Aesir.E-Commerce
 * @subpackage  Upgrade
 * @since       1.9
 */
class WebserviceUpdateScript_1_9_5
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
			->select('s.local_id')
			->from($db->qn('#__redshopb_sync', 's'))
			->leftJoin($db->qn('#__redshopb_field_data', 'b') . ' ON s.reference = ' . $db->q('erp.webservice.field_data') . ' AND s.local_id = b.id')
			->leftJoin($db->qn('#__redshopb_field', 'a') . ' ON a.id = b.field_id')
			->leftJoin($db->qn('#__redshopb_type', 'c') . ' ON a.type_id = c.id')
			->where('c.value_type = ' . $db->q('float_value'))
			->where('b.float_value IS NULL')
			->where('s.reference = ' . $db->q('erp.webservice.field_data'));

		$ids = $db->setQuery($query)->loadColumn();

		$query = 'DELETE b FROM `#__redshopb_field_data` AS b'
			. ' LEFT JOIN `#__redshopb_field` AS `a` ON a.id = b.field_id'
			. '	LEFT JOIN `#__redshopb_type` AS `c` ON a.type_id = c.id'
			. ' WHERE b.`float_value` IS NULL AND c.value_type = ' . $db->q('float_value');

		if (!empty($ids))
		{
			$query .= ' AND b.id NOT IN (' . implode(',', $ids) . ')';
		}

		try
		{
			$db->setQuery($query)->execute();
		}
		catch (Exception $e)
		{
			Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');

			return false;
		}

		return true;
	}
}
