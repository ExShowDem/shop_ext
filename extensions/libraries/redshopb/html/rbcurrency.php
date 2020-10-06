<?php
/**
 * @package     Aesir.E-Commerce.Libraries
 * @subpackage  HTML
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
/**
 * Utility class for currency
 *
 * @package     Aesir.E-Commerce.Libraries
 * @subpackage  HTML
 * @since       1.0
 */
abstract class JHtmlRbcurrency
{
	/**
	 * Cached array of items
	 *
	 * @var  array
	 */
	protected static $items = array();

	/**
	 * Returns an array of currencies
	 *
	 * @param   array  $config  An array of configuration options. By default, only published and unpublished categories are returned.
	 *
	 * @return  array  The currencies
	 */
	public static function currencies($config = array('filter.state' => array(0, 1)))
	{
		$hash = md5(serialize($config));

		if (!isset(static::$items[$hash]))
		{
			$db = Factory::getDbo();

			$query = $db->getQuery(true)
				->select('id as identifier')
				->select('name as data')
				->from('#__redshopb_currency')
				->order('name');

			// Filter on the published state
			if (isset($config['filter.state']))
			{
				if (is_numeric($config['filter.state']))
				{
					$query->where('state = ' . (int) $config['filter.state']);
				}

				elseif (is_array($config['filter.state']))
				{
					$config['filter.state'] = ArrayHelper::toInteger($config['filter.state']);
					$query->where('state IN (' . implode(',', $config['filter.state']) . ')');
				}
			}

			$db->setQuery($query);
			$items = $db->loadObjectList();

			// Build the field options
			$options = array();

			if (!empty($items))
			{
				foreach ($items as $item)
				{
					$options[] = HTMLHelper::_('select.option', $item->identifier, $item->data);
				}
			}

			static::$items[$hash] = $options;
		}

		return static::$items[$hash];
	}
}
