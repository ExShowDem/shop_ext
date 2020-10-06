<?php
/**
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Tables
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */


defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\HTML\HTMLHelper;

FormHelper::loadFieldClass('list');

/**
 * Field to load a list of shipping routes
 *
 * @package     Aesir.E-Commerce
 * @subpackage  Field
 * @since       1.0
 */
class JFormFieldShippingroutes extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  1.0
	 */
	public $type = 'Shippingroutes';

	/**
	 * Cached array of the plugin items.
	 *
	 * @var    array
	 * @since  1.0
	 */
	protected static $cache = array();

	/**
	 * Method to get the options to populate to populate list
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   1.0
	 */
	protected function getOptions()
	{
		// Accepted modifiers
		$hash = md5($this->element);

		if (!isset(static::$cache[$hash]))
		{
			static::$cache[$hash] = parent::getOptions();

			$options = array();
			$db      = Factory::getDbo();
			$query   = $db->getQuery(true)
				->select('*')
				->from('#__redshopb_shipping_route')
				->where($db->qn('state') . ' = 1');

			// Setup the query
			$db->setQuery($query);

			// Return the result
			$shippingRoutes = $db->loadObjectList();

			if (!empty($shippingRoutes))
			{
				foreach ($shippingRoutes as $value)
				{
					$options[] = HTMLHelper::_('select.option', $value->id, $value->name);
				}

				static::$cache[$hash] = array_merge(static::$cache[$hash], $options);
			}
		}

		return static::$cache[$hash];
	}
}
