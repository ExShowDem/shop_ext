<?php
/**
 * @package     Aesir.E-Commerce.Library
 * @subpackage  Fields
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\HTML\HTMLHelper;

FormHelper::loadFieldClass('rlist');

/**
 * Field Field
 *
 * @package     Aesir.E-Commerce.Library
 * @subpackage  Fields
 * @since       1.0
 */
class JFormFieldField extends JFormFieldRlist
{
	/**
	 * The form field type.
	 *
	 * @var  string
	 */
	public $type = 'Field';

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

		// Get the options.
		$items = $this->getFieldRecords();

		// Build the field options.
		if (!empty($items))
		{
			foreach ($items as $item)
			{
				$options[] = HTMLHelper::_('select.option', $item->id, $item->title . ($item->title != $item->name ? ' [' . $item->name . ']' : ''));
			}
		}

		return array_merge(parent::getOptions(), $options);
	}

	/**
	 * Method to get the list of Fields.
	 *
	 * @return  array  An array of field ids and names.
	 */
	protected function getFieldRecords()
	{
		$currentId = (int) $this->form->getData()->get('id', 0);
		$scope     = '';

		if (!empty($this->element['scope']) && (string) $this->element['scope'])
		{
			$scope = (string) $this->element['scope'];
		}

		$funcArgs = get_defined_vars();
		$key      = serialize($funcArgs);

		if (empty($this->cache[$key]))
		{
			$db    = Factory::getDbo();
			$query = $db->getQuery(true)
				->select(
					array(
						$db->qn('id'),
						$db->qn('name'),
						$db->qn('title')
					)
				)
				->from('#__redshopb_field')
				->order('name');

			if ($currentId != 0)
			{
				$query->where($db->qn('id') . ' != ' . (int) $currentId);
			}

			if ($scope)
			{
				$query->where('scope = ' . $db->q($scope));
			}

			$this->cache[$key] = $db->setQuery($query)
				->loadObjectList();
		}

		return $this->cache[$key];
	}
}
