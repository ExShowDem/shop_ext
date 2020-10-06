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
use Joomla\CMS\Form\FormHelper;

FormHelper::loadFieldClass('list');
FormHelper::loadFieldClass('radio');

/**
 * Department Field
 *
 * @package     Aesir.E-Commerce.Library
 * @subpackage  Fields
 * @since       1.0
 */
class JFormFieldRadioRedshopb extends JFormFieldRadio
{
	/**
	 * The form field type.
	 *
	 * @var  string
	 */
	public $type = 'RadioRedshopb';

	/**
	 * Method to get the radio button field input markup.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   11.1
	 */
	protected function getInput()
	{
		$html = array();

		// Initialize some field attributes.

		$required  = $this->required ? ' required aria-required="true"' : '';
		$autofocus = $this->autofocus ? ' autofocus' : '';
		$disabled  = $this->disabled ? ' disabled' : '';
		$readonly  = $this->readonly;

		// Get the field options.
		$options = $this->getOptions();

		if ($readonly)
		{
			$class = !empty($this->class) ? ' class="' . $this->class . '"' : '';

			foreach ($options as $option)
			{
				if ((string) $option->value == (string) $this->value)
				{
					$html[] = '<input type="text" value="' . Text::alt($option->text, preg_replace('/[^a-zA-Z0-9_\-]/', '_', $this->fieldname))
						. '" id="' . $this->id . '_text"' . $class . ' disabled="disabled" />';
					$html[] = '<input type="hidden" value="' . htmlspecialchars($option->value, ENT_COMPAT, 'UTF-8') . '" name="'
						. $this->name . '" id="' . $this->id . '" />';
					break;
				}
			}
		}
		else
		{
			$class = !empty($this->class) ? ' class="radio ' . $this->class . '"' : ' class="radio"';

			// Start the radio field output.
			$html[] = '<fieldset id="' . $this->id . '"' . $class . $required . $autofocus . $disabled . ' >';

			// Build the radio field output.
			foreach ($options as $i => $option)
			{
				// Initialize some option attributes.
				$checked = ((string) $option->value == (string) $this->value) ? ' checked="checked"' : '';
				$class   = !empty($option->class) ? ' class="' . $option->class . '"' : '';

				$disabled = !empty($option->disable) || ($readonly && !$checked);

				$disabled = $disabled ? ' disabled' : '';

				// Initialize some JavaScript option attributes.
				$onclick  = !empty($option->onclick) ? ' onclick="' . $option->onclick . '"' : '';
				$onchange = !empty($option->onchange) ? ' onchange="' . $option->onchange . '"' : '';

				$html[] = '<input type="radio" id="' . $this->id . $i . '" name="' . $this->name . '" value="'
					. htmlspecialchars($option->value, ENT_COMPAT, 'UTF-8') . '"' . $checked . $class . $required . $onclick
					. $onchange . $disabled . ' />';

				$html[] = '<label for="' . $this->id . $i . '"' . $class . ' >'
					. Text::alt($option->text, preg_replace('/[^a-zA-Z0-9_\-]/', '_', $this->fieldname)) . '</label>';

				$required = '';
			}

			// End the radio field output.
			$html[] = '</fieldset>';
		}

		return implode($html);
	}
}
