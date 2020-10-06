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
use Joomla\CMS\Session\Session;

extract($displayData);

if (!$disabled && !$readonly)
{
	$formName  = (string) $displayData['element']['formName'];
	$fieldName = (string) $displayData['element']['name'];

	$ajaxRoute = RedshopbRoute::_(
		'index.php?option=com_redshopb&task=list_ajax.json&form_name=' . $formName
			. '&field_name=' . $fieldName . '&' . Session::getFormToken() . '=1', false
	);

	RHelperAsset::load('vendor/selectize/js/selectize.min.js', 'com_redshopb');
	RHelperAsset::load('vendor/selectize/css/selectize.min.css', 'com_redshopb');
	RHelperAsset::load('vendor/selectize/css/selectize.bootstrap2.min.css', 'com_redshopb');

	$allowCreate = isset($element['allowCreate']) && RHelperString::toBool($element['allowCreate']) ? 'true' : 'false';

	Factory::getDocument()->addScriptDeclaration("
		(function ($) {
			$(document).ready(function () {
				var element = $('#$id');
				var dynamicFilters = " . json_encode($dynamicFilters) . ";

				// Remove chosen on the element
				if (element.hasClass('chzn-done')) {
					element.next('[id*=_chzn]').remove();
					element.removeClass('chzn-done').css('display', 'block');
				}

				var selectizeItem = element.selectize({
					valueField: 'value',
					labelField: 'text',
					searchField: 'text',
					placeholder: '" . $hint . "',
					options: [],
					create: $allowCreate,
					preload: true,
					load: function (query, callback) {

						var data = {
							query : query
						};

						if (Object.keys(dynamicFilters).length) {
							for (var stateVar in dynamicFilters) {
								data[stateVar] = $(dynamicFilters[stateVar]).val();
							}
						}

						$.ajax({
							url: '$ajaxRoute',
							type: 'GET',
							dataType: 'json',
							data: data,
							error: function () {
								callback();
							},
							success: function (res) {
								callback(res);
							}
						});
					},
					// Set an id for the selectize wrapper so it can be used for tests
					onLoad : function () {
						var selectizeId = '" . $id . "_selectize',
							control = element.next('.selectize-control'),
							dropdownId = '" . $id . "_selectize_options';

						control.attr('id', selectizeId);
						control.find('.selectize-dropdown').attr('id', dropdownId);
					},
					render: {
						option: function(item, escape) {
							return '<div class=\"option\" data-title=\"' + escape(item.text) + '\">' +
								escape(item.text) +
							'</div>';
						}
					},
				});

				// Refresh data when a dynamic filter changes
				if (Object.keys(dynamicFilters).length) {
					var control = selectizeItem[0].selectize;

					for (var stateVar in dynamicFilters) {
						var filterSelector = dynamicFilters[stateVar];
						$(filterSelector).change(function(){
							control.clearOptions();
						})
					}
				}
			});
		})(jQuery);
	"
	);
}

echo RedshopbLayoutHelper::render('redcore.field.list', $displayData);
