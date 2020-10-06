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

/**
 * Utility class for products
 *
 * @package     Aesir.E-Commerce.Libraries
 * @subpackage  HTML
 * @since       1.0
 */
abstract class JHtmlRbproduct
{
	/**
	 * This is just a proxy for the formbehavior.ajaxchosen method
	 *
	 * @param   string   $selector     DOM id of the tag field
	 * @param   boolean  $allowCustom  Flag to allow custom values
	 *
	 * @return  void
	 */
	public static function attributevaluefield($selector='#jform_values', $allowCustom = true)
	{
		// Allow custom values ?
		if ($allowCustom)
		{
			Factory::getDocument()->addScriptDeclaration("
				(function($){
					$(document).ready(function () {

						var customTagPrefix = '#new#';

						// Method to add tags pressing enter
						$('" . $selector . "_chzn input').keydown(function(event) {

							if (event.which === 13 || event.which === 188) {

								// Search an highlighted result
								var highlighted = $('" . $selector . "_chzn').find('li.active-result.highlighted').first();

								// Add the highlighted option
								if (event.which === 13 && highlighted.text() !== '')
								{
									// Extra check. If we have added a custom tag with this text remove it
									var customOptionValue = customTagPrefix + highlighted.text();
									$('" . $selector . " option').filter(function () { return $(this).val() == customOptionValue; }).remove();

									// Select the highlighted result
									var tagOption = $('" . $selector . " option').filter(function ()
									{ return $(this).html() == highlighted.text(); });
									tagOption.attr('selected', 'selected');
								}
								// Add the custom tag option
								else
								{
									var customTag = this.value;

									// Extra check. Search if the custom tag already exists (typed faster than AJAX ready)
									var tagOption = $('" . $selector . " option').filter(function () { return $(this).html() == customTag; });
									if (tagOption.text() !== '')
									{
										tagOption.attr('selected', 'selected');
									}
									else
									{
										var option = $('<option>');
										option.text(this.value).val(customTagPrefix + this.value);
										option.attr('selected','selected');

										// Append the option an repopulate the chosen field
										$('" . $selector . "').append(option);
									}
								}

								this.value = '';
								$('" . $selector . "').trigger('liszt:updated');
								event.preventDefault();

							}
						});
					});
				})(jQuery);
				"
			);

			// Fix for undefined value of search-field input
			Factory::getDocument()->addScriptDeclaration("
				(function($){
					$(document).ready(function () {
						$('.search-field > input').val('');
					});
				})(jQuery);
				"
			);
		}
	}
}
