<?php
/**
 * @package     Aesir.E-Commerce.Libraries
 * @subpackage  HTML
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
/**
 * Rbvalidate HTML class.
 *
 * @package     Aesir.E-Commerce
 * @subpackage  Html
 * @since       1.9.3
 */
abstract class JHtmlRbvalidate
{
	/**
	 * Array containing information for loaded files
	 *
	 * @var  array
	 */
	protected static $loaded = false;

	/**
	 * Load the validate library
	 *
	 * @param   string  $selector  Form selector
	 *
	 * @return  void
	 */
	public static function framework($selector = '.form-jquery-validate')
	{
		// Only load once
		if (static::$loaded)
		{
			return;
		}

		static::$loaded = true;
		HTMLHelper::_('jquery.framework');
		HTMLHelper::_('script', 'com_redshopb/lib/jquery-validation/jquery.validate.min.js', false, true);
		$scriptRules = array();

		// Translate errors
		$scriptRules[] = 'jQuery.extend(jQuery.validator.messages, {
			required: "' . Text::_('COM_UREDSHOPB_MYPROFILE_FIELD_REQUIRED', true) . '",
			minlength: jQuery.validator.format("' . Text::_('COM_UREDSHOPB_MYPROFILE_ENTER_AT_LEAST', true) . '"),
			equalTo: "' . Text::_('COM_UREDSHOPB_MYPROFILE_ENTER_SAME_VALUE_AGAIN', true) . '",
			maxlength: jQuery.validator.format("' . Text::_('COM_UREDSHOPB_MYPROFILE_ENTER_MORE_THAN', true) . '")
		});';

		// Force validate for class .validateNeeded even field hidden, helpful for chosen library
		$scriptRules[] = 'jQuery.validator.setDefaults({ ignore: ":hidden:not(\'.validateNeeded\')" });';

		// Compatibility with joomla form style validator
		$scriptRules[] = 'jQuery("' . $selector . '").validate({
			showErrors: function(errorMap, errorList) {
				var html = "";
				jQuery(errorList).each(function(error, index){
					var $label = jQuery("#" + error.element.id + "-lbl");
					jQuery(error.element).addClass("invalid");
					var labelText = $label.html();
					if (labelText) {
						error.message = labelText.replace("*", "") + ": " + error.message;
						$label.addClass("invalid");
					}
					html = html + \'<p>\' + error.message + \'</p>\';
				});

				if (html != ""){
					html = \'<div class="alert alert-error">\' + html + \' </div > \';
				}

				jQuery("#system-message-container").html(html);
			}
		});';

		Factory::getDocument()->addScriptDeclaration("
			(function($){
				$(document).ready(function () {
					" . implode('', $scriptRules) . "
				});
			})(jQuery);"
		);

		return null;
	}
}
