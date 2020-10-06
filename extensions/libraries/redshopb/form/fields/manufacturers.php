<?php
/**
 * @package     Aesir.E-Commerce.Library
 * @subpackage  Fields
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\HTML\HTMLHelper;

FormHelper::loadFieldClass('rlist');

/**
 * Manufacturers Field
 *
 * @package     Aesir.E-Commerce.Library
 * @subpackage  Fields
 * @since       1.6.51
 */
class JFormFieldManufacturers extends JFormFieldRlist
{
	/**
	 * The form field type.
	 *
	 * @var string
	 */
	public $type = 'Manufacturers';

	/**
	 * A static cache.
	 *
	 * @var array
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

		// Get the categories.
		$items         = $this->getManufacturers();
		$parentOptions = parent::getOptions();

		if ($parentOptions)
		{
			$options = $parentOptions;
		}
		else
		{
			$options[] = HTMLHelper::_('select.option', '', Text::_('COM_REDSHOPB_NO_MANUFACTURER'));
		}

		// Build the field options.
		if (!empty($items))
		{
			foreach ($items as $item)
			{
				$options[] = HTMLHelper::_('select.option', $item->value, $item->text, 'value', 'text');
			}
		}

		return $options;
	}

	/**
	 * Method to get the manufacturers list.
	 *
	 * @return  array  An array of manufacturers
	 */
	protected function getManufacturers()
	{
		if (empty($this->cache))
		{
			$db = Factory::getDbo();

			$query = $db->getQuery(true)
				->select(
					array(
						$db->qn('id', 'value'),
						$db->qn('name', 'text'),
						$db->qn('level'),
						$db->qn('state')
					)
				)
				->from($db->qn('#__redshopb_manufacturer'));

			// Avoiding root and disabled manufacturers
			$query->where($db->qn('parent_id') . ' IS NOT NULL')
				->where($db->qn('state') . ' IN (0,1)')
				->order($db->qn('lft') . ' ASC');

			// Get the options.
			$db->setQuery($query);
			$options = $db->loadObjectList();

			if (empty($options))
			{
				return array();
			}

			// Pad the option text with spaces using depth level as a multiplier.
			$count = count($options);

			for ($i = 0; $i < $count; $i++)
			{
				if (!$options[$i]->state)
				{
					$options[$i]->text = $options[$i]->text . ' [' . Text::_('JUNPUBLISHED') . ']';
				}

				if ($options[$i]->level)
				{
					$options[$i]->text = str_repeat('- ', $options[$i]->level - 1) . $options[$i]->text;
				}
			}

			$this->cache = $options;
		}

		return $this->cache;
	}
}
