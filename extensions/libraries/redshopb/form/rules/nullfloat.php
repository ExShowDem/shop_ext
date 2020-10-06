<?php
/**
 * @package     Aesir.E-Commerce
 * @subpackage  Rules
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('JPATH_REDCORE') or die;

use Joomla\Registry\Registry;
use Joomla\CMS\Form\Form;
/**
 * Integer rule.
 *
 * @package     Aesir.E-Commerce
 * @subpackage  Rules
 * @since       1.6.77
 */
class JFormRuleNullFloat extends RFormRule
{
	/**
	 * Method to test if two values are not equal. To use this rule, the form
	 * XML needs a validate attribute of equals and a field attribute
	 * that is equal to the field to test against.
	 *
	 * @param   SimpleXMLElement  $element  The SimpleXMLElement object representing the <field /> tag for the form field object.
	 * @param   mixed             $value    The form field value to validate.
	 * @param   string            $group    The field name group control value. This acts as as an array container for the field.
	 *                                      For example if the field has name="foo" and the group value is set to "bar" then the
	 *                                      full field name would end up being "bar[foo]".
	 * @param   Registry          $input    An optional Registry object with the entire data set to validate against the entire form.
	 * @param   Form              $form     The form object for which the field is being tested.
	 *
	 * @return  boolean  True if the value is valid, false otherwise.
	 *
	 * @throws  InvalidArgumentException
	 * @throws  UnexpectedValueException
	 */
	public function test(SimpleXMLElement $element, $value, $group = null, Registry $input = null, Form $form = null)
	{
		if ($value === null)
		{
			return true;
		}

		$pattern = "~^[0-9]+(\.[0-9]+)?$~xD";

		// If signed float.
		if (isset($element['signed']) && RHelperString::toBool($element['signed']))
		{
			$pattern = "~^-?[0-9]+(\.[0-9]+)?$~xD";
		}

		return 1 === preg_match($pattern, $value);
	}
}
