<?php
/**
 * @package     Aesir.E-Commerce.Library
 * @subpackage  Fields
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\Language\Text;

FormHelper::loadFieldClass('rtext');

RHelperAsset::load('redshopb.field.range.js', 'com_redshopb');

/**
 * Range class field for Aesir EC
 *
 * @package     Aesir.E-Commerce.Library
 * @subpackage  Fields
 * @since       1.13.2
 */
class JFormFieldAesECRange extends JFormFieldRtext
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  1.13.2
	 */
	protected $type = 'aesECRange';

	/**
	 * @var    array
	 * @since  1.13.2
	 */
	protected $attribsMin = array();

	/**
	 * @var    array
	 * @since  1.13.2
	 */
	protected $attribsMax = array();

	/**
	 * @var   float
	 * @since 1.13.2
	 */
	protected $minValue = null;

	/**
	 * @var   float
	 * @since 1.13.2
	 */
	protected $maxValue = null;

	/**
	 * Add an attribute to the input field
	 *
	 * @param   string  $name   Name of the attribute
	 * @param   string  $value  Value for the attribute
	 *
	 * @return  void
	 * @since  1.13.2
	 */
	protected function addAttribute($name, $value)
	{
		if (!is_null($value))
		{
			$name = strtolower($name);

			$this->attribs[$name]    = (string) $value;
			$this->attribsMin[$name] = (string) $value . '_min';
			$this->attribsMax[$name] = (string) $value . '_max';
		}
	}

	/**
	 * Parse values
	 */

	/**
	 * Function to parse the attributes of the input field
	 *
	 * @param   array|null  $attribsArray  Array of attributes to parse (or use the attribs property)
	 *
	 * @return  string  Attributes in format: type="text" name="name" value="2"
	 * @since   1.13.2
	 */
	protected function parseAttributes($attribsArray = null)
	{
		$attributes   = array();
		$attribsArray = is_null($attribsArray) ? $this->attribs : $attribsArray;

		if (!empty($attribsArray))
		{
			foreach ($attribsArray as $name => $value)
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

	/**
	 * Parse the stored DB value into min/max properties
	 *
	 * @return  null
	 *
	 * @since   1.13.2
	 */
	protected function parseMinMaxValues()
	{
		$this->minValue = null;
		$this->maxValue = null;

		if (empty($this->value))
		{
			return null;
		}

		if (!preg_match('/^([0-9]*)(\.[0-9]+)?\-([0-9]*)(\.[0-9]+)?$/', $this->value, $matches))
		{
			return null;
		}

		$minValue = ($matches[1] . $matches[2]);
		$maxValue = ($matches[3] . (isset($matches[4]) ? $matches[4] : ''));

		$this->minValue = (empty($minValue) ? null : (float) $minValue);
		$this->maxValue = (empty($maxValue) ? null : (float) $maxValue);
	}

	/**
	 * Method to get the field input markup for the file field.
	 * Field attributes allow specification of a maximum file size and a string
	 * of accepted file extensions.
	 *
	 * @return  string  The field input markup.
	 *
	 * @note    The field does not include an upload mechanism.
	 * @see     JFormFieldMedia
	 * @since   1.13.2
	 */
	protected function getInput()
	{
		$this->parseMinMaxValues();

		$this->attribs['id']             = $this->id;
		$this->attribsMin['id']          = $this->id . '_min';
		$this->attribsMax['id']          = $this->id . '_max';
		$this->attribs['name']           = $this->name;
		$this->attribs['type']           = 'hidden';
		$this->attribsMin['type']        = 'text';
		$this->attribsMax['type']        = 'text';
		$this->attribsMin['readonly']    = ($this->element['readonly'] == 'true') ? 'readonly' : null;
		$this->attribsMax['readonly']    = ($this->element['readonly'] == 'true') ? 'readonly' : null;
		$this->attribsMin['disabled']    = ($this->element['disabled'] == 'true') ? 'disabled' : null;
		$this->attribsMax['disabled']    = ($this->element['disabled'] == 'true') ? 'disabled' : null;
		$this->attribsMin['placeholder'] = $this->element['placeholder'] ? Text::_($this->element['placeholder']) : null;
		$this->attribsMax['placeholder'] = $this->element['placeholder'] ? Text::_($this->element['placeholder']) : null;

		$this->attribs['data-aesec-field-range-edit-min'] = $this->attribsMin['id'];
		$this->attribs['data-aesec-field-range-edit-max'] = $this->attribsMax['id'];

		$this->attribsMin['data-aesec-field-range-edit'] = $this->id;
		$this->attribsMax['data-aesec-field-range-edit'] = $this->id;

		$this->attribsMin['value'] = (is_null($this->minValue) ? '' : $this->minValue);
		$this->attribsMax['value'] = (is_null($this->maxValue) ? '' : $this->maxValue);

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

		$this->attribs['value'] = htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8');

		return '<input ' . $this->parseAttributes() . ' />' .
			'<input ' . $this->parseAttributes($this->attribsMin) . ' />' .
			' - ' .
			'<input ' . $this->parseAttributes($this->attribsMax) . ' />';
	}
}
