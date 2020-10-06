<?php
/**
 * @package     Aesir.E-Commerce.Library
 * @subpackage  Fields
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\Registry\Registry;
use Joomla\CMS\Form\FormField;

/**
 * Bootstrap timepicker field.
 *
 * @package     Aesir.E-Commerce
 * @subpackage  Fields
 * @since       1.0
 */
class JFormFieldTimePicker extends FormField
{
	/**
	 * The form field type.
	 *
	 * @var  string
	 */
	protected $type = 'TimePicker';

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string  The field input markup.
	 */
	protected function getInput()
	{
		$class = $this->element['class'] ? $this->element['class'] : '';

		return RedshopbLayoutHelper::render('fields.timepicker',
			array(
				'field'    => $this,
				'class'    => $class,
				'id'       => $this->id,
				'required' => $this->required,
				'name'     => $this->name,
				'value'    => $this->value
			)
		);
	}

	/**
	 * Get the field options as a js string.
	 *
	 * @return  string  The options.
	 */
	public function getOptions()
	{
		// Prepare the params.
		$template   = $this->element['template'] ? $this->element['template'] : 'dropdown';
		$minuteStep = (isset($this->element['minute_step']) && !empty($this->element['minute_step'])) ?
			(int) $this->element['minute_step'] : 15;
		$secondStep = (isset($this->element['second_step']) && !empty($this->element['second_step'])) ?
			(int) $this->element['second_step'] : 15;

		$icons         = array();
		$icons['up']   = (!empty($this->element['icon_up'])) ? $this->element['icon_up'] : 'icon-chevron-up';
		$icons['down'] = (!empty($this->element['icon_down'])) ? $this->element['icon_down'] : 'icon-chevron-down';

		$showSeconds   = RHelperString::toBool($this->element['seconds']);
		$showMeridian  = RHelperString::toBool($this->element['meridian']);
		$showInputs    = RHelperString::toBool($this->element['inputs']);
		$disableFocus  = RHelperString::toBool($this->element['disable_focus']);
		$modalBackdrop = RHelperString::toBool($this->element['backdrop']);

		// If we don't have a value.
		if (empty($this->value))
		{
			$defaultTime = $this->element['default'] ? $this->element['default'] : 'current';
		}

		else
		{
			$defaultTime = 'value';
		}

		$options = new Registry;

		$options->loadArray(
			array(
				'template' => $template,
				'minuteStep' => $minuteStep,
				'showSeconds' => $showSeconds,
				'secondStep' => $secondStep,
				'defaultTime' => $defaultTime,
				'showMeridian' => $showMeridian,
				'showInputs' => $showInputs,
				'disableFocus' => $disableFocus,
				'modalBackdrop' => $modalBackdrop,
				'icons' => $icons
			)
		);

		return $options->toString();
	}
}
