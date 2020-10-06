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
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Language\Text;

extract($displayData);

/**
 * Extracted
 *
 * @var $element
 * @var $id
 * @var $dynamicFilters
 * @var $resetWhenDynamicFilterChanged
 * @var $hint
 * @var $limit
 */

if (!$disabled && !$readonly)
{
	$formName  = (string) $element['formName'];
	$fieldName = (string) $element['name'];
	$formPath  = (string) $element['formPath'];

	$ajaxRoute = Uri::base() . 'index.php?option=com_redshopb&task=list_ajax.jsonPagination&form_name='
		. $formName . '&field_name=' . $fieldName;

	if (!empty($formPath))
	{
		$ajaxRoute .= '&form_path=' . $formPath;
	}

	if (isset($element['url']) && (string) $element['url'])
	{
		$ajaxRoute = (string) $element['url'];
	}

	HTMLHelper::_('rjquery.framework');
	RHelperAsset::load('js/vendor/select2/select2.full.js', 'com_redshopb');
	RHelperAsset::load('css/vendor/select2/select2.css', 'com_redshopb');

	$dynamicFilterString              = '';
	$selector                         = '#' . $id;
	$initResetFiledAfterFilterChanged = array();
	$extra                            = array();
	$prefix                           = array();

	if (count($dynamicFilters))
	{
		foreach ($dynamicFilters as $stateVar => $dynamicFilter)
		{
			$dynamicFilterString               .= ',\'' . $stateVar . '\': $(\'' . $dynamicFilter . '\').val()';
			$initResetFiledAfterFilterChanged[] = $dynamicFilter;
		}
	}

	if ($resetWhenDynamicFilterChanged && count($initResetFiledAfterFilterChanged))
	{
		$extra[] = "$(document).on('change', '" . implode(',', $initResetFiledAfterFilterChanged) . "', function(){
			$('" . $selector . "').val('').trigger('change');
			initSelect2_" . $id . "();
		})";
	}

	if ($multiple && isset($element['useShuffle']) && $element['useShuffle'] == 'true')
	{
		$prefix[] = "$(\"ul.select2-selection__rendered\").sortable({
			containment: 'parent'
		})";
	}

	if (!$hint)
	{
		$hint = Text::_('JSELECT');
	}

	if (!$multiple)
	{
		$displayData['options'][] = (object) array('value' => '', 'text' => $hint . '');
	}

	$extraClass   = array('select2_' . $id . '_class');
	$extraClass[] = isset($element['class']) ? (string) $element['class'] : '';
	$allowCreate  = isset($element['allowCreate']) ? (string) $element['allowCreate'] : 'false';

	Factory::getDocument()->addScriptDeclaration("
		(function($){
			$(document).ready(function () {
				$('select" . $selector . "').show().removeClass('chzn-done').next().remove();
				function initSelect2_" . $id . "(){
					$('" . $selector . "').select2({
						placeholder : '" . $hint . "',
						minimumInputLength : " . (isset($element['minimumInputLength']) ? (string) $element['minimumInputLength'] : 2) . ",
						width: '" . (isset($element['width']) ? (string) $element['width'] : '220px') . "',
						multiple : " . ($multiple ? 'true' : 'false') . ",
						allowClear : " . ($multiple ? 'false' : 'true') . ",
						containerCssClass: '" . implode(' ', $extraClass) . "',
						tags: " . $allowCreate . ",
						ajax : {
							url: '" . $ajaxRoute . "',
							dataType: 'json',
							type: 'post',
							quietMillis: " . (isset($element['quietMillis']) ? (string) $element['quietMillis'] : 300) . ",
							data: function (params) {
								return {
									query: params.term, // search term
									'" . Session::getFormToken() . "': 1,
									page: params.page // page number
									" . $dynamicFilterString . "
								};
							},
							processResults: function (data, params) {
								// parse the results into the format expected by Select2
								// since we are using custom formatting functions we do not need to
								// alter the remote JSON data, except to indicate that infinite
								// scrolling can be used
								params.page = params.page || 1;
	
								return {
									results: data.result,
									pagination: {
										more: (params.page * " . $limit . ") < data.total
									}
								};
							}
						},
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
					" . implode(';', $prefix) . ";
				};
				initSelect2_" . $id . "();
				" . implode(';', $extra) . ";

				// Fix for select2 inside bootstrap modal
				$.fn.modal.Constructor.prototype.enforceFocus =function(){};
			});
		})(jQuery);
	"
	);
}

echo RedshopbLayoutHelper::render('redcore.field.list', $displayData);
