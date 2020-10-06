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
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Uri\Uri;

FormHelper::loadFieldClass('text');

/**
 * Vies Field
 *
 * @package     Aesir.E-Commerce.Library
 * @subpackage  Fields
 * @since       1.0
 */
class JFormFieldVies extends JFormFieldText
{
	/**
	 * The form field type.
	 *
	 * @var  string
	 */
	public $type = 'Vies';

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string  The field input markup.
	 */
	public function getInput()
	{
		HTMLHelper::_('rbvalidate.framework');
		$dataAttributes   = array();
		$scriptRules      = array();
		$countryField     = $this->formControl . '_' . (string) $this->element['country_field'];
		$invalidFlag      = $this->formControl ? $this->formControl . '[' . $this->fieldname . '_invalid]' : $this->fieldname;
		$dataAttributes[] = 'data-rule-vies_' . $this->id . '="true"';
		$scriptRules[]    = '
		jQuery(\'#' . $countryField . '\').change(function() {
			jQuery(this).valid();
		});

		jQuery.validator.addMethod("vies_' . $this->id . '", function( value, element, param, method ) {
			if ( this.optional( element ) ) {
				return "dependency-mismatch";
			}

			method = typeof method === "string" && method || "remote";

			if (jQuery(\'#vies_' . $this->id . '_status_invalid\').is(\':checked\')){
				return true;
			}

			var previous = this.previousValue( element, method ),
				validator, data;

			if (!this.settings.messages[ element.name ] ) {
				this.settings.messages[ element.name ] = {};
			}
			previous.originalMessage = previous.originalMessage || this.settings.messages[ element.name ][ method ];
			this.settings.messages[ element.name ][ method ] = previous.message;

			param = typeof param === "string" && { url: param } || param;

			if ( previous.old === value ) {
				return previous.valid;
			}

			previous.old = value;
			validator = this;
			this.startRequest( element );
			data = {};
			data[ element.name ] = value;
			data["jform[country_id]"] = jQuery("#' . $countryField . '").val();
			data["' . Session::getFormToken() . '"] = 1;
			jQuery(\'.waitVies_' . $this->id . '\').remove();
			jQuery.ajax( jQuery.extend( true, {
				url: "' . Uri::root() . 'index.php?tmpl=component&option=com_redshopb&task=b2buserregister.checkViesValidation",
				mode: "abort",
				port: "validate" + element.name,
				dataType: "json",
				data: data,
				type: "post",
				context: validator.currentForm,
				beforeSend: function () {
					jQuery( "<label class=\'waitVies waitVies_' . $this->id . '\'>'
			. Text::_('COM_REDSHOPB_VIES_REGISTRATION_VERYFIES_VAT_NUMBER', true) . '</label>" )
						.insertAfter(\'#\' + element.id);
				},
				complete: function( data ) {
					var response = data.responseJSON;
					var valid = response === true || response === "true",
						errors, message, submitted;
					jQuery(\'.waitVies_' . $this->id . '\').remove();
					validator.settings.messages[ element.name ][ method ] = previous.originalMessage;
					if ( valid ) {
						submitted = validator.formSubmitted;
						validator.prepareElement( element );
						validator.formSubmitted = submitted;
						validator.successList.push( element );
						delete validator.invalid[ element.name ];
						validator.showErrors();
						jQuery( "<label class=\"waitVies waitVies_' . $this->id . '\" style=\"color: #008000;\">'
			. Text::_('COM_REDSHOPB_VIES_REGISTRATION_VALID_VAT_NUMBER', true) . '</label>" )
							.insertAfter(\'#\' + element.id);
					} else {
						errors = {};
						message = response || validator.defaultMessage( element, { method: method, parameters: value } );
						errors[ element.name ] = previous.message = jQuery.isFunction( message ) ? message( value ) : message;
						validator.invalid[ element.name ] = true;
						validator.showErrors( errors );
						jQuery( "<label class=\"checkbox waitVies waitVies_' . $this->id . '\" style=\"color: #000;\">'
			. '<input type=\"checkbox\" class\"viesStatusInvalid\" name=\"' . $invalidFlag
			. '\" id=\"vies_status_invalid_' . $this->id . '\" value=\"1\">'
			. Text::_('COM_REDSHOPB_VIES_REGISTRATION_VALIDATION_STATUS2', true) . '</label>" )
							.insertAfter(\'#\' + element.id);
					}
					previous.valid = valid;
					validator.stopRequest( element, valid );
				}
			}, param ) );
			return "pending";
		});

		jQuery.validator.addMethod("country_' . $countryField . '", function( value, element, param, method ) {

			if ( this.optional( element ) ) {
				return "dependency-mismatch";
			}

			if (jQuery(\'#vies_status_invalid_' . $this->id . '\').is(\':checked\')){
				return true;
			}

			method = typeof method === "string" && method || "remote";

			var vatNumber = jQuery("#' . $this->id . '");
			var vatElement = vatNumber[0];
			var previous = this.previousValue( element, method );

			if (!this.settings.messages[ element.name ] ) {
				this.settings.messages[ element.name ] = {};
			}
			previous.originalMessage = previous.originalMessage || this.settings.messages[ element.name ][ method ];
			this.settings.messages[ element.name ][ method ] = previous.message;

			if ( previous.old === value ) {
				return previous.valid;
			}

			previous.old = value;

			if (jQuery.data(vatElement, "previousValue"))
			{
				jQuery.data(vatElement, "previousValue", {
					old: null,
					valid: true,
					message: this.defaultMessage(vatElement, { method: "vies_' . $this->id . '" }  )
				});
			}

			var cleanElement = this.clean( vatNumber ),
				checkElement = this.validationTargetFor( cleanElement ),
				result = true;

			this.lastElement = checkElement;

			if ( checkElement === undefined ) {
				delete this.invalid[ cleanElement.name ];
			} else {
				result = this.check( checkElement ) !== false;
				if ( result ) {
					delete this.invalid[ checkElement.name ];
				} else {
					this.invalid[ checkElement.name ] = true;
				}
			}
			// Add aria-invalid status for screen readers
			jQuery( vatNumber ).attr( "aria-invalid", !result );

			if ( !this.numberOfInvalids() ) {
				// Hide error containers on last error
				this.toHide = this.toHide.add( this.containers );
			}

			return "pending";
		});

		jQuery("#' . $countryField . '").rules(\'add\', {
			country_' . $countryField . ': true
		});';

		// Load script on document load.
		Factory::getDocument()->addScriptDeclaration(
			"jQuery(document).ready(function(){" . implode('', $scriptRules) . "});"
		);

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
		$autocomplete = !$this->autocomplete ? ' autocomplete="off"' : ' autocomplete="' . $this->autocomplete . '"';
		$autocomplete = $autocomplete == ' autocomplete="on"' ? '' : $autocomplete;
		$autofocus    = $this->autofocus ? ' autofocus' : '';
		$spellcheck   = $this->spellcheck ? '' : ' spellcheck="false"';
		$pattern      = !empty($this->pattern) ? ' pattern="' . $this->pattern . '"' : '';
		$inputmode    = !empty($this->inputmode) ? ' inputmode="' . $this->inputmode . '"' : '';
		$dirname      = !empty($this->dirname) ? ' dirname="' . $this->dirname . '"' : '';

		// Initialize JavaScript field attributes.
		$onchange = !empty($this->onchange) ? ' onchange="' . $this->onchange . '"' : '';

		// Including fallback code for HTML5 non supported browsers.
		HTMLHelper::_('jquery.framework');
		HTMLHelper::_('script', 'system/html5fallback.js', false, true);

		$datalist = '';
		$list     = '';
		$options  = (array) $this->getOptions();

		if ($options)
		{
			$datalist = '<datalist id="' . $this->id . '_datalist">';

			foreach ($options as $option)
			{
				if (!$option->value)
				{
					continue;
				}

				$datalist .= '<option value="' . $option->value . '">' . $option->text . '</option>';
			}

			$datalist .= '</datalist>';
			$list      = ' list="' . $this->id . '_datalist"';
		}

		$html[] = '<input type="text" name="' . $this->name . '" id="' . $this->id . '"' . $dirname . ' value="'
			. htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8') . '"' . $class . $size . $disabled . $readonly . $list
			. $hint . $onchange . $maxLength . $required . $autocomplete . $autofocus . $spellcheck . $inputmode . $pattern
			. ' ' . implode(' ', $dataAttributes) . ' />';
		$html[] = $datalist;

		return implode($html);
	}
}
