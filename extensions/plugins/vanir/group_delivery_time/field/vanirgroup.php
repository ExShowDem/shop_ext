<?php
/**
 * @package     Aesir.E-Commerce.Plugin
 * @subpackage  Fields
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Form\FormField;

JImport("helper", JPATH_ROOT . '/plugins/vanir/group_delivery_time/helper');

/**
 * Group delivery time - groups field
 *
 * @package     Aesir.E-Commerce.Plugin
 * @subpackage  Group_Delivery_Time
 * @since       1.0
 */
class JFormFieldVanirgroup extends FormField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  1.0
	 */
	protected $type = 'Vanirgroup';

	/**
	 * Layout to render
	 *
	 * @var  string
	 */
	protected $layout = 'group_delivery';

	/**
	 * Method to get the field input markup for a generic list.
	 * Use the multiple attribute to enable multiselect.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   1.0
	 */
	protected function getInput()
	{
		$layout = !empty($this->element['layout']) ? $this->element['layout'] : $this->layout;

		return RLayoutHelper::render(
			trim($layout),
			array(
				'id'       => $this->id,
				'name'     => $this->name,
				'options'  => (array) $this->getOptions(),
				'required' => $this->required,
				'value'    => $this->value
			),
			JPATH_ROOT . '/plugins/vanir/group_delivery_time/layouts'
		);
	}

	/**
	 * Get options
	 *
	 * @return  array
	 */
	protected function getOptions()
	{
		return PlgVanirGroupDeliveryTimeHelper::getGroups();
	}
}
