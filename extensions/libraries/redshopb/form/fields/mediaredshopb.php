<?php
/**
 * @package     Aesir.E-Commerce.Library
 * @subpackage  Fields
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Form\FormField;

/**
 * Form Media Redshopb class. Provides an input field for files
 *
 * @package     Aesir.E-Commerce.Library
 * @subpackage  Fields
 * @since       2.0
 */
class JFormFieldMediaRedshopb extends FormField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  11.1
	 */
	protected $type = 'mediaRedshopb';

	/**
	 * The accepted file type list.
	 *
	 * @var    mixed
	 * @since  3.2
	 */
	protected $accept;

	/**
	 * Method to get certain otherwise inaccessible properties from the form field object.
	 *
	 * @param   string  $name  The property name for which to the the value.
	 *
	 * @return  mixed  The property value or null.
	 *
	 * @since   3.2
	 */
	public function __get($name)
	{
		if ($name == 'accept')
		{
			return $this->accept;
		}

		return parent::__get($name);
	}

	/**
	 * Method to set certain otherwise inaccessible properties of the form field object.
	 *
	 * @param   string  $name   The property name for which to the the value.
	 * @param   mixed   $value  The value of the property.
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	public function __set($name, $value)
	{
		if ($name == 'accept')
		{
			$this->accept = (string) $value;

			return;
		}

		parent::__set($name, $value);
	}

	/**
	 * Method to attach a Joomla\CMS\Form\Form object to the field.
	 *
	 * @param   SimpleXMLElement  $element  The SimpleXMLElement object representing the <field /> tag for the form field object.
	 * @param   mixed             $value    The form field value to validate.
	 * @param   string            $group    The field name group control value. This acts as as an array container for the field.
	 *                                      For example if the field has name="foo" and the group value is set to "bar" then the
	 *                                      full field name would end up being "bar[foo]".
	 *
	 * @return  boolean  True on success.
	 *
	 * @see     Joomla\CMS\Form\FormField::setup()
	 * @since   3.2
	 */
	public function setup(SimpleXMLElement $element, $value, $group = null)
	{
		$return = parent::setup($element, $value, $group);

		if ($return)
		{
			$this->accept = (string) $this->element['accept'];
		}

		return $return;
	}

	/**
	 * Method to get the field input markup for the file field.
	 * Field attributes allow specification of a maximum file size and a string
	 * of accepted file extensions.
	 *
	 * @return  string  The field input markup.
	 *
	 * @note    The field does not include an upload mechanism.
	 * @see     Joomla\CMS\Form\Field\MediaField
	 * @since   11.1
	 */
	protected function getInput()
	{
		$formGroup = (isset($this->element['form_group'])) ? (string) $this->element['form_group'] : null;
		$fieldData = $this->form->getValue($this->fieldname, $formGroup);

		$output = '';

		if (!is_array($fieldData))
		{
			$fieldData = array($fieldData);
		}

		foreach ($fieldData as $multipleField)
		{
			if ($multipleField)
			{
				$this->setValue($multipleField->value);
			}

			$output .= RedshopbLayoutHelper::render(
				'fields.mediaredshopb',
				array(
					'view' => $this,
					'options' => array (
						'field' => $this,
						'field_data' => $multipleField
					)
				)
			);
		}

		return $output;
	}
}
