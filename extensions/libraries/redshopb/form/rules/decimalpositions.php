<?php
/**
 * @package     Aesir.E-Commerce
 * @subpackage  Rules
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('JPATH_REDCORE') or die;

use Joomla\CMS\Form\FormRule;
use Joomla\CMS\Language\Text;
use Joomla\Registry\Registry;
use Joomla\CMS\Form\Form;

/**
 * Main url rule.
 *
 * @package     Aesir.E-Commerce
 * @subpackage  Rules
 * @since       1.0
 */
class JFormRuleDecimalPositions extends FormRule
{
	/**
	 * Tests if the `decimal_position` field is filled out correctly when `pkg_size` is set
	 *
	 * @param   SimpleXMLElement  $element  The SimpleXMLElement object representing the <field /> tag for the form field object.
	 * @param   mixed             $value    The form field value to validate.
	 * @param   string            $group    The field name group control value. This acts as as an array container for the field.
	 *                                      For example if the field has name="foo" and the group value is set to "bar" then the
	 *                                      full field name would end up being "bar[foo]".
	 * @param   Registry          $input    An optional Registry object with the entire data set to validate against the entire form.
	 * @param   Form              $form     The form object for which the field is being tested.
	 *
	 * @return  boolean|UnexpectedValueException  True if the value is valid, exception otherwise.
	 */
	public function test(SimpleXMLElement $element, $value, $group = null, Registry $input = null, Form $form = null)
	{
		// If no input or form is passed we have nothing to validate against
		if (!($input instanceof Registry) || !($form instanceof Form))
		{
			return true;
		}

		$pkgSizeDecimals = explode('.', $value);
		$decimalPosition = (int) $input->get('decimal_position');

		if ((isset($pkgSizeDecimals[1])) && ($decimalPosition < strlen($pkgSizeDecimals[1])))
		{
			$msg = Text::sprintf(
				'JLIB_FORM_VALIDATE_FIELD_INVALID',
				Text::sprintf(
					'COM_REDSHOPB_FORMRULE_INVALID_DECIMAL_POSITIONS',
					Text::_($form->getFieldAttribute('decimal_position', 'label')),
					Text::_($element['label'])
				)
			);

			return new UnexpectedValueException($msg);
		}

		return true;
	}
}
