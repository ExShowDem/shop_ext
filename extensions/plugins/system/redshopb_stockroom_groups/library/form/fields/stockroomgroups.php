<?php
/**
 * @package     Aesir.E-Commerce.Library
 * @subpackage  Fields
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormHelper;

FormHelper::loadFieldClass('rlist');

/**
 * Stockroom Groups Field
 *
 * @package     Aesir.E-Commerce.Library
 * @subpackage  Fields
 * @since       1.0
 */
class JFormFieldStockroomgroups extends JFormFieldRlist
{
	/**
	 * The form field type.
	 *
	 * @var  string
	 */
	public $type = 'Stockroomgroups';

	/**
	 * A static cache.
	 *
	 * @var  array
	 */
	protected $cache = array();

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 */
	protected function getOptions()
	{
		$options = array();

		// Get the countries.
		$items = $this->getStockroomGroups();

		// Build the field options.
		if (!empty($items))
		{
			foreach ($items as $item)
			{
				$options[] = HTMLHelper::_('select.option', $item->identifier, $item->data);
			}
		}

		// Set Value to array type
		if (!empty($this->value) && !is_array($this->value))
		{
			$this->value = explode(', ', $this->value);
		}

		return array_merge(parent::getOptions(), $options);
	}

	/**
	 * Method to get the list of countries.
	 *
	 * @return  array  An array of country names.
	 */
	protected function getStockroomGroups()
	{
		if (empty($this->cache))
		{
			$db = Factory::getDbo();

			$query = $db->getQuery(true)
				->select('id as identifier')
				->select('name as data')
				->from('#__redshopb_stockroom_group')
				->order('name');

			$db->setQuery($query);

			$result = $db->loadObjectList();

			if (is_array($result))
			{
				$this->cache = $result;
			}
		}

		return $this->cache;
	}
}
