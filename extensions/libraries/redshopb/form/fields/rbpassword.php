<?php
/**
 * @package     Aesir.E-Commerce.Library
 * @subpackage  Fields
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Component\ComponentHelper;

FormHelper::loadFieldClass('password');

/**
 * Form Field class for the Joomla Platform.
 * Text field for passwords
 *
 * @link   http://www.w3.org/TR/html-markup/input.password.html#input.password
 * @note   Two password fields may be validated as matching using Joomla\CMS\Form\Rule\EqualsRule
 * @since  11.1
 */
class JFormFieldRbpassword extends JFormFieldPassword
{
	/**
	 * @var string
	 */
	protected $type = 'RBPassword';

	/**
	 * Method to get the field input markup for password.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   11.1
	 */
	protected function getInput()
	{
		HTMLHelper::_('rbvalidate.framework');

		// Including fallback code for HTML5 non supported browsers.
		HTMLHelper::_('script', 'system/html5fallback.js', false, true);

		$minimumLength    = isset($element['minimum_length']) ? (int) $element['minimum_length'] : 4;
		$minimumIntegers  = isset($element['minimum_integers']) ? (int) $element['minimum_integers'] : 0;
		$minimumSymbols   = isset($element['minimum_symbols']) ? (int) $element['minimum_symbols'] : 0;
		$minimumUppercase = isset($element['minimum_uppercase']) ? (int) $element['minimum_uppercase'] : 0;
		$dataAttributes   = array();
		$scriptRules      = array();

		// If we have parameters from com_users, use those instead.
		// Some of these may be empty for legacy reasons.
		$params = ComponentHelper::getParams('com_users');

		if (!empty($params))
		{
			$minimumLengthp    = $params->get('minimum_length');
			$minimumIntegersp  = $params->get('minimum_integers');
			$minimumSymbolsp   = $params->get('minimum_symbols');
			$minimumUppercasep = $params->get('minimum_uppercase');

			empty($minimumLengthp) ? : $minimumLength       = (int) $minimumLengthp;
			empty($minimumIntegersp) ? : $minimumIntegers   = (int) $minimumIntegersp;
			empty($minimumSymbolsp) ? : $minimumSymbols     = (int) $minimumSymbolsp;
			empty($minimumUppercasep) ? : $minimumUppercase = (int) $minimumUppercasep;
		}

		// We set a maximum length to prevent abuse since it is unfiltered.
		$dataAttributes[] = 'data-rule-maxlength=4096';

		if (!empty($minimumLength))
		{
			$dataAttributes[] = 'data-rule-minlength=' . $minimumLength;
		}

		if (isset($this->element['validate'])
			&& 'equals' == (string) $this->element['validate']
			&& isset($this->element['field'])
			&& (string) $this->element['field'])
		{
			$dataAttributes[] = 'data-rule-equalto_' . $this->id . '="#' . $this->formControl . '_' . (string) $this->element['field'] . '""';
			$text             = Text::_('COM_UREDSHOPB_MYPROFILE_ENTER_SAME_VALUE_AGAIN', true);

			if (isset($this->element['message'])
				&& (string) $this->element['message'])
			{
				$text = Text::_((string) $this->element['message'], true);
			}

			$scriptRules[] = '
				jQuery.validator.addMethod("equalTo_' . $this->id . '", function(value, element, param) {
					// Bind to the blur event of the target in order to revalidate whenever the target field is updated
					var target = jQuery( param );
					if ( this.settings.onfocusout && target.not( ".validate-equalTo-blur" ).length ) {
						target.addClass( "validate-equalTo-blur" ).on( "blur.validate-equalTo", function() {
							jQuery( element ).valid();
						} );
					}
					return value === target.val();
				}, "' . $text . '");';
		}

		if (!empty($minimumIntegers))
		{
			$dataAttributes[] = 'data-rule-minimumintegers_' . $this->id . '="true"';
			$scriptRules[]    = '
				jQuery.validator.addMethod("minimumIntegers_' . $this->id . '", function(value, element) {
				var matches = value.match(/[0-9]/g);
				if (matches && ' . (int) $minimumIntegers . ' < matches.length){
					return true;
				}else{
					return false;
				}
			}, "' . Text::sprintf('COM_REDSHOPB_MYPROFILE_MSG_NOT_ENOUGH_INTEGERS_N', (int) $minimumIntegers, true) . '");';
		}

		if (!empty($minimumSymbols))
		{
			$dataAttributes[] = 'data-rule-minimumsymbols_' . $this->id . '="true"';
			$scriptRules[]    = '
				jQuery.validator.addMethod("minimumSymbols_' . $this->id . '", function(value, element) {
				var matches = value.match(/[a-zA-Z]/g);
				if (matches && ' . (int) $minimumSymbols . ' < matches.length){
					return true;
				}else{
					return false;
				}
			}, "' . Text::sprintf('COM_UREDSHOPB_MYPROFILE_MSG_NOT_ENOUGH_SYMBOLS_N', (int) $minimumSymbols, true) . '");';
		}

		if (!empty($minimumUppercase))
		{
			$dataAttributes[] = 'data-rule-minimumuppercase_' . $this->id . '="true"';
			$scriptRules[]    = '
				jQuery.validator.addMethod("minimumUppercase_' . $this->id . '", function(value, element) {
				var matches = value.match(/[A-Z]/g);
				if (matches && ' . (int) $minimumUppercase . ' < matches.length){
					return true;
				}else{
					return false;
				}
			}, "' . Text::sprintf('COM_UREDSHOPB_MYPROFILE_MSG_NOT_ENOUGH_UPPERCASE_LETTERS_N', (int) $minimumUppercase, true) . '");';
		}

		$dataAttributes[] = 'data-rule-nospace_' . $this->id . '="true"';
		$scriptRules[]    = '
		jQuery.validator.addMethod("noSpace_' . $this->id . '", function(value, element) {
			return value.indexOf(" ") < 0 && value != "";
		}, "' . Text::_('COM_REDSHOPB_MYPROFILE_MSG_SPACES_IN_PASSWORD', true) . '");';

		// Translate placeholder text
		$hint = $this->translateHint ? Text::_($this->hint) : $this->hint;

		// Initialize some field attributes.
		$size         = !empty($this->size) ? ' size="' . $this->size . '"' : '';
		$maxLength    = !empty($this->maxLength) ? ' maxlength="' . $this->maxLength . '"' : '';
		$class        = !empty($this->class) ? ' class="' . $this->class . '"' : '';
		$readonly     = $this->readonly ? ' readonly' : '';
		$disabled     = $this->disabled ? ' disabled' : '';
		$required     = $this->required ? ' required aria-required="true"' : '';
		$hint         = $hint ? ' placeholder="' . $hint . '"' : '';
		$autocomplete = !$this->autocomplete ? ' autocomplete="off"' : '';
		$autofocus    = $this->autofocus ? ' autofocus' : '';

		if ($this->meter && false === RHtmlMedia::isMootoolsDisabled())
		{
			HTMLHelper::_('script', 'system/passwordstrength.js', true, true);
			$scriptRules[] = 'new Form.PasswordStrength("' . $this->id . '",
				{
					threshold: ' . $this->threshold . ',
					onUpdate: function(element, strength, threshold) {
						element.set("data-passwordstrength", strength);
					}
				}
			);';
		}

		// Load script on document load.
		Factory::getDocument()->addScriptDeclaration(
			"jQuery(document).ready(function(){" . implode('', $scriptRules) . "});"
		);

		return '<input type="password" name="' . $this->name . '" id="' . $this->id . '"' .
		' value="' . htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8') . '"' . $hint . $autocomplete .
		$class . $readonly . $disabled . $size . $maxLength . $required . $autofocus . ' ' . implode(' ', $dataAttributes) . ' />';
	}
}
