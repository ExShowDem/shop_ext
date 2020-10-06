<?php
/**
 * @package     Aesir.E-Commerce.Libraries
 * @subpackage  Helper
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;

/**
 * A Breadcrumb helper.
 *
 * @package     Aesir.E-Commerce.Libraries
 * @subpackage  Helper
 * @since       1.0
 */
final class RedshopbHelperBreadcrumb
{
	/**
	 * Get the item name
	 *
	 * @param   integer  $id         The company id.
	 * @param   string   $tableName  The table name
	 * @param   string   $field      The table field name
	 *
	 * @return  mixed  The item name or null.
	 */
	public static function getItemName($id, $tableName, $field = 'name')
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->select($db->qn($field))
			->from($db->qn($tableName))
			->where('id = ' . (int) $id);
		$db->setQuery($query);
		$name = $db->loadResult();

		if (empty($name))
		{
			return null;
		}

		return $name;
	}
}
