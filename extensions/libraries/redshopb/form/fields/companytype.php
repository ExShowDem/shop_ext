<?php
/**
 * @package     Aesir.E-Commerce
 * @subpackage  Field
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Form\FormHelper;

FormHelper::loadFieldClass('rlist');

/**
 * Base field for overridable list of options
 *
 * @package     Aesir.E-Commerce
 * @subpackage  Field
 * @since       1.0
 */
class JFormFieldCompanyType extends JFormFieldRlist
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 */
	protected $type = 'CompanyType';

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
		if (RedshopbHelperACL::getPermission('changetype', 'company'))
		{
			return parent::getInput();
		}
		else
		{
			$name    = (string) $this->element['name'];
			$options = $this->getOptions();

			$value     = $this->form->getValue($name);
			$valueText = '';

			foreach ($options as $option)
			{
				if ($option->value == $value)
				{
					$valueText = $option->text;
				}
			}

			return '<input type="text" value="' . $valueText . '" disabled="disabled" />';
		}
	}
}
