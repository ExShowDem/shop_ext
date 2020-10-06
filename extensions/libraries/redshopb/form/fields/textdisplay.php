<?php
/**
 * @package     Aesir.E-Commerce
 * @subpackage  Fields
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('JPATH_REDCORE') or die;

jimport('redcore.form.fields.rtext');

/**
 * Text field.
 *
 * @package     Aesir.E-Commerce
 * @subpackage  Fields
 * @since       1.0
 */
class JFormFieldTextDisplay extends JFormFieldRtext
{
	/**
	 * The form field type.
	 *
	 * @var  string
	 */
	protected $type = 'TextDisplay';

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string  The field input markup.
	 */
	protected function getInput()
	{
		$input = '';

		if (($this->element['readonly'] == 'true'))
		{
			$input .= '<span class="help-block">' . htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8') . '</span>';
			$input .= '<input type="hidden" id="' . $this->id . '" name="' . $this->name . '" />';
		}
		else
		{
			$input .= parent::getInput();
		}

		return $input;
	}
}
