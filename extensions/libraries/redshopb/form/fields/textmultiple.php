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
use Joomla\CMS\Form\FormField;

/**
 * Text field.
 *
 * @package     Aesir.E-Commerce
 * @subpackage  Fields
 * @since       1.0
 */
class JFormFieldTextMultiple extends FormField
{
	/**
	 * Input field attributes
	 *
	 * @var  array
	 */
	protected $attribs = array();

	/**
	 * Attributes not allowed to use in field definition
	 *
	 * @var  array
	 */
	protected $forbiddenAttributes = array(
		'id', 'default', 'description', 'disabled', 'name',
		'placeholder', 'readonly', 'required', 'type', 'value'
	);

	/**
	 * The form field type.
	 *
	 * @var  string
	 */
	protected $type = 'TextMultiple';

	/**
	 * Add an attribute to the input field
	 *
	 * @param   string  $name   Name of the attribute
	 * @param   string  $value  Value for the attribute
	 *
	 * @return  void
	 */
	protected function addAttribute($name, $value)
	{
		if (!is_null($value))
		{
			$name = strtolower($name);

			$this->attribs[$name] = (string) $value;
		}
	}

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string  The field input markup.
	 */
	protected function getInput()
	{
		$this->multiple = (isset($this->multiple)) ? (boolean) $this->multiple : false;

		// Manually handled attributes
		$this->attribs['id']          = $this->id;
		$this->attribs['name']        = $this->name;
		$this->attribs['type']        = 'text';
		$this->attribs['readonly']    = ($this->element['readonly'] == 'true') ? 'readonly' : null;
		$this->attribs['disabled']    = ($this->element['disabled'] == 'true') ? 'disabled' : null;
		$this->attribs['placeholder'] = $this->element['placeholder'] ? Text::_($this->element['placeholder']) : null;

		if ($this->multiple)
		{
			$values = (!is_array($this->value)) ? array($this->value) : $this->value;

			foreach ($values as $index => $value)
			{
				$values[$index] = htmlspecialchars($value, ENT_COMPAT, 'UTF-8');
			}

			$this->value = $values;
		}

		// Automatically insert any other attribute inserted
		$elementAttribs = $this->element->attributes();

		if ($elementAttribs)
		{
			foreach ($elementAttribs as $name => $value)
			{
				if (!in_array($name, $this->forbiddenAttributes))
				{
					$this->addAttribute($name, $value);
				}
			}
		}

		if (!$this->multiple)
		{
			return '<input ' . $this->parseAttributes() . ' />';
		}

		$html = array();

		foreach ($this->value as $value)
		{
			$this->attribs['value'] = $value;
			$html[]                 = '<input ' . $this->parseAttributes() . ' />';
		}

		return implode('', $html);
	}

	/**
	 * Function to parse the attributes of the input field
	 *
	 * @return  string  Attributes in format: type="text" name="name" value="2"
	 */
	protected function parseAttributes()
	{
		$attributes = array();

		if (!empty($this->attribs))
		{
			foreach ($this->attribs as $name => $value)
			{
				if (!is_null($value))
				{
					$attributes[] = $name . '="' . $value . '"';
				}
			}

			$attributes = implode(' ', $attributes);
		}

		return $attributes;
	}
}
