<?php
/**
 * @package     Aesir.E-Commerce
 * @subpackage  Rules
 *
 * @copyright   Copyright (C) 2012 - 2018 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('JPATH_REDCORE') or die;

use Joomla\CMS\Form\FormRule;
use Joomla\CMS\Language\Text;
use Joomla\Registry\Registry;
use Joomla\CMS\Form\Form;
/**
 * Days in month rule.
 *
 * @package     Aesir.E-Commerce
 * @subpackage  Rules
 * @since       1.0
 */
class JFormRuleDaysInMonth extends FormRule
{
	/**
	 * Method to test if the input number is within the number of days in the month. To use this rule, the form
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
	 * @return  boolean|UnexpectedValueException  True if the value is valid, exception otherwise.
	 */
	public function test(SimpleXMLElement $element, $value, $group = null, Registry $input = null, Form $form = null)
	{
		if (is_null($input))
		{
			return false;
		}

		$monthDays = array(31, 29, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
		$month     = $input->get('month');
		$day       = $input->get('day');

		if (isset($month))
		{
			if (!preg_match('/^[0-9]+$/', $month) || intval($month) > 12)
			{
				return new UnexpectedValueException(Text::_('COM_REDSHOPB_HOLIDAY_VALIDATE_MONTH_RANGE'));
			}

			$maxdays = $monthDays[intval($month) - 1];
		}
		else
		{
			$maxdays = 31;
		}

		if (intval($day) > $maxdays)
		{
			return new UnexpectedValueException(Text::sprintf('COM_REDSHOPB_HOLIDAY_VALIDATE_DAY_RANGE', $maxdays));
		}
		else
		{
			return true;
		}
	}
}
