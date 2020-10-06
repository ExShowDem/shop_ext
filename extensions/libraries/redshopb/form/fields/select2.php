<?php
/**
 * @package     Aesir.E-Commerce
 * @subpackage  Fields
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('JPATH_REDCORE') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Form\FormHelper;

FormHelper::loadFieldClass('rlist');

/**
 * Field to select a user id from a modal list.
 *
 * @package     Aesir.E-Commerce
 * @subpackage  Fields
 * @since       1.0
 */
class JFormFieldSelect2 extends JFormFieldRlist
{
	/**
	 * @var string
	 */
	public $type = 'select2';

	/**
	 * @var string
	 */
	public $layout = 'redshopb.field.select2';

	/**
	 * @var string
	 */
	protected $defaultValuesSeparator;

	/**
	 * Method to attach a Joomla\CMS\Form\Form object to the field.
	 *
	 * @param   SimpleXMLElement  $element  The SimpleXMLElement object representing the `<field>` tag for the form field object.
	 * @param   mixed             $value    The form field value to validate.
	 * @param   string            $group    The field name group control value. This acts as as an array container for the field.
	 *                                      For example if the field has name="foo" and the group value is set to "bar" then the
	 *                                      full field name would end up being "bar[foo]".
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   11.1
	 */
	public function setup(SimpleXMLElement $element, $value, $group = null)
	{
		if (!parent::setup($element, $value, $group))
		{
			return false;
		}

		$attributeName = 'defaultValuesSeparator';

		if (!empty($element[$attributeName]))
		{
			$this->__set($attributeName, (string) $element[$attributeName]);
		}

		if (is_string($this->value) && $this->multiple && $this->defaultValuesSeparator
			&& strpos($this->value, $this->defaultValuesSeparator) !== false)
		{
			$this->value = explode($this->defaultValuesSeparator, $this->value);
		}

		return true;
	}

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

		return RedshopbLayoutHelper::render(
			trim($layout),
			array(
				'id'       => $this->id,
				'element'  => $this->element,
				'field'    => $this,
				'multiple' => $this->multiple,
				'name'     => $this->name,
				'options'  => (array) $this->getOptions(),
				'required' => $this->required,
				'value'    => $this->value,
				'readonly' => $this->readonly,
				'hint'     => $this->translateHint ? Text::_($this->hint) : $this->hint,
				'disabled' => $this->disabled
			)
		);
	}

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   11.1
	 */
	protected function getOptions()
	{
		$options = parent::getOptions();

		if (!empty($options) && !empty($this->value)
			&& isset($this->element['useShuffle']) && (string) $this->element['useShuffle'] == 'true')
		{
			$newOptions = array();

			foreach ((array) $this->value as $value)
			{
				foreach ($options as $key => $option)
				{
					if ($option->value == $value)
					{
						$newOptions[] = $option;
						unset($options[$key]);
					}
				}
			}

			if (!empty($options))
			{
				$newOptions = array_merge($newOptions, $options);
			}

			$options = $newOptions;
		}

		return $options;
	}
}
