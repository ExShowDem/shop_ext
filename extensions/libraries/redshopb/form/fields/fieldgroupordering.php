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
 * Field Field
 *
 * @package     Aesir.E-Commerce.Library
 * @subpackage  Fields
 * @since       1.0
 */
class JFormFieldFieldgroupordering extends JFormFieldRlist
{
	/**
	 * The form field type.
	 *
	 * @var  string
	 */
	public $type = 'Fieldgroupordering';

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
		$items = $this->getFieldOrdering();

		// Build the field options.
		if (!empty($items))
		{
			foreach ($items as $item)
			{
				$options[] = HTMLHelper::_('select.option', $item->value, $item->text);
			}
		}

		return array_merge(parent::getOptions(), $options);
	}

	/**
	 * Method to get the list of Fields.
	 *
	 * @return  array  An array of field ids and names.
	 */
	protected function getFieldOrdering()
	{
		if (empty($this->cache))
		{
			$db = Factory::getDbo();

			$query = $db->getQuery(true)
				->select(
					array(
						$db->qn('ordering', 'value'),
						'CONCAT(' . $db->qn('ordering') . ', \':\' ,' . $db->qn('name') . ') as text'
						)
				);

			$query->from('#__redshopb_field_group');

			$scope = $this->form->getData()->get('scope', null);

			$query->where($db->qn('scope') . ' = ' . $db->q($scope));
			$query->order('scope ASC')->order('ordering ASC');

			$db->setQuery($query);

			$result = $db->loadObjectList();

			if (is_array($result))
			{
				$this->cache = $result;
			}
		}

		return $this->cache;
	}

	/**
	 * @uses  JFormFieldRlist::getInput()
	 *
	 * @return string
	 */
	protected function getInput()
	{
		$scope = $this->form->getData()->get('scope', null);

		if (empty($scope))
		{
			return '<p id="jform_ordering" class="text-error" style="margin-top:5px;">' . Text::_('COM_REDSHOPB_SELECT_SCOPE_TO_ORDER') . '</p>';
		}

		return parent::getInput();
	}
}
