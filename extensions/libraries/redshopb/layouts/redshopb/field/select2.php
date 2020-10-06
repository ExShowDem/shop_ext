<?php
/**
 * @package     Aesir.E-Commerce.Library
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

extract($displayData);

/**
 * Extracted
 *
 * @var $element
 * @var $id
 * @var $readonly
 * @var $hint
 * @var $multiple
 */

if (!$disabled && !$readonly)
{
	$formName  = (string) $element['formName'];
	$fieldName = (string) $element['name'];

	HTMLHelper::_('rjquery.framework');
	RHelperAsset::load('js/vendor/select2/select2.full.js', 'com_redshopb');
	RHelperAsset::load('css/vendor/select2/select2.css', 'com_redshopb');

	$selector   = '#' . $id;
	$prefix     = array();
	$extraClass = array('select2_' . $id . '_class');

	if ($multiple && isset($element['useShuffle']) &&  (string) $element['useShuffle'] == 'true')
	{
		HTMLHelper::_('rjquery.ui');
		$prefix[] = "$('" . $selector . "').select2_sortable();
			$(\"" . $selector . "\").on(\"select2:select\", function (evt) {
				  var element = evt.params.data.element;
				  var \$element = $(element);
				  \$element.detach();
				  $(this).append(\$element);
				  $(this).trigger(\"change\");
			});";
	}

	$extraClass[] = isset($element['class']) ? (string) $element['class'] : '';
	$allowCreate  = isset($element['allowCreate']) ? (string) $element['allowCreate'] : 'false';

	Factory::getDocument()->addScriptDeclaration("
		(function($){
			$.fn.extend({
				select2_sortable: function(){
					var select = $(this);
					var ul = $(select).next('.select2-container').first('ul.select2-selection__rendered');
					ul.sortable({
						placeholder : 'ui-state-highlight',
						forcePlaceholderSize: true,
						items       : 'li:not(.select2-search__field)',
						tolerance   : 'pointer',
						stop: function() {
							$($(ul).find('.select2-selection__choice').get().reverse()).each(function() {
								var id = $(this).data('data').id;
								var option = select.find('option[value=\"' + id + '\"]')[0];
								$(select).prepend(option);
							});
						}
					});
				}
			});
			$(document).ready(function () {
				$('select" . $selector . "').show().removeClass('chzn-done').next().remove();
				function initSelect2_" . $id . "(){
					$('" . $selector . "').select2({
						placeholder : " . ($hint ? "'" . $hint . "'" : 'false') . ",
						width: '" . (isset($element['width']) ? (string) $element['width'] : '220px') . "',
						multiple : " . ($multiple ? 'true' : 'false') . ",
						allowClear : " . ($multiple ? 'false' : 'true') . ",
						containerCssClass: '" . implode(' ', $extraClass) . "',
						tags: " . $allowCreate . ",
						language: {
							errorLoading: function () {
								return '" . Text::_('COM_REDSHOPB_SELECT2_ERROR_LOADING') . "'
							},
							maximumSelected: function (e) {
								var t = '" . Text::_('COM_REDSHOPB_SELECT2_INPUT_TOO_BIG') . "';
								return e.maximum != 1 && (t += '" . Text::_('COM_REDSHOPB_SELECT2_SUFFIX') . "'), t
							},
							loadingMore: function () {
								return '" . Text::_('COM_REDSHOPB_SELECT2_LOAD_MORE') . "'
							},
							inputTooShort: function (e) {
								var t = e.minimum - e.input.length, n = '" . Text::_('COM_REDSHOPB_SELECT2_INPUT_TOO_SHORT') . "';
								return n
							},
							inputTooLong: function (e) {
								var t = e.input.length - e.maximum, n = '" . Text::_('COM_REDSHOPB_SELECT2_INPUT_TOO_LONG') . "';
								return t != 1 && (n += '" . Text::_('COM_REDSHOPB_SELECT2_SUFFIX') . "'), n
							},
							noResults: function () { return '" . Text::_('COM_REDSHOPB_SELECT2_NO_MATHES') . "'; },
							searching: function () { return '" . Text::_('COM_REDSHOPB_SELECT2_SEARCHING') . "'; }
						}
					});
					" . implode('', $prefix) . "
				};
				initSelect2_" . $id . "();

				// Fix for select2 inside bootstrap modal
				$.fn.modal.Constructor.prototype.enforceFocus =function(){};
			});
		})(jQuery);
	"
	);
}

echo RedshopbLayoutHelper::render('redcore.field.list', $displayData);
