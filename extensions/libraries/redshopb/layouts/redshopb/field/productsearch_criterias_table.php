<?php
/**
 * @package     Aesir.E-Commerce.Library
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

extract($displayData);

$optionValues = array();

foreach ($options as $key => $option)
{
	$optionValues[$option->value] = array(
		'text' => $option->text,
		'key' => $key
	);
}

if (!empty($value))
{
	foreach ($value as $groupId => &$sections)
	{
		foreach ($sections as &$section)
		{
			if (isset($optionValues[$section['name']]))
			{
				$section['title']       = $optionValues[$section['name']]['text'];
				$key                    = $optionValues[$section['name']]['key'];
				$options[$key]->disable = true;
			}
			else
			{
				$section['title'] = $section['name'];
			}
		}
	}
}

RHelperAsset::load('lib/sortable.min.js', 'com_redshopb');
echo HTMLHelper::_('select.genericlist', $options, '', '', 'value', 'text', null, $id . '_dropdown');

?>
<script type="text/javascript">
	(function($){
		$(document).ready(function(){
			function initDragAndDropPlugin(){
				Sortable.create(divSearchGroups, {
					animation: 150,
					draggable: '.oneSearchGroup',
					handle: '.reorderSearchGroupButton',
					ghostClass: "btn-info",
					onSort: function(){
						recalculateSearchEntities();
					}
				});
				[].forEach.call(divSearchGroups.getElementsByClassName('oneSearchGroupEntities'), function (el){
					Sortable.create(el, {
						group: 'searchEntities',
						animation: 150,
						ghostClass: "btn-info",
						sort: false,
						onSort: function(){
							recalculateSearchEntities();
						}
					});
				});
			}
			initDragAndDropPlugin();
			var $dropdown = $('#<?php echo $id ?>_dropdown');
			var $divSearchGroups = $('#divSearchGroups');
			function checkCriteriaSelection(){
				var criteria = $dropdown.val();
				if (!criteria){
					alert('<?php echo Text::_('COM_REDSHOPB_CONFIG_PRODUCT_SEARCH_ADDITIONAL_FIELDS_SELECT_CRITERIA_FIRST') ?>');
					return false;
				}
				return true;
			}
			function addCriteriaGroup() {
				if (!checkCriteriaSelection()) return;
				var criteriaText = $dropdown.find('option:selected').text();
				var criteriaValue = $dropdown.val();
				var count = $divSearchGroups.children('div').length;
				$('.oneSearchGroup').removeClass('activeSearchCriteriaGroup');
				var groupTemplate = '<?php echo addslashes(
					str_replace(
						array("\n", "\r"), '',
						RedshopbLayoutHelper::render(
							'redshopb.field.productsearch_criterias_group',
							array(
								'id'     => $id,
								'entities' => array(
									array(
										'title' => '{entityName}',
										'name' => '{entityId}'
									)
								),
								'name'   => $name,
								'groupNumber' => '{groupNumber}',
								'groupClass' => 'activeSearchCriteriaGroup'
							)
						)
					)
				); ?>';

				groupTemplate = groupTemplate
					.replace(/\{groupNumber}/g, count + 1)
					.replace(/\{entityId}/g, criteriaValue)
					.replace(/\{entityName}/g, criteriaText);
				$divSearchGroups.append(groupTemplate);
				$('select').chosen({"disable_search_threshold": 10, "allow_single_deselect": true});
				$dropdown.find('option[value="'+criteriaValue+'"]').prop('disabled', true);
				$dropdown.find('option[value=""]').prop('selected', true).trigger("liszt:updated");
				initDragAndDropPlugin();
			}
			function addCriteriaEntity() {
				if (!checkCriteriaSelection()) return;
				var count = $divSearchGroups.children('div').length;
				if (count < 1 || $divSearchGroups.find('.activeSearchCriteriaGroup').length < 1) {
					return addCriteriaGroup();
				}
				var criteriaText = $dropdown.find('option:selected').text();
				var criteriaValue = $dropdown.val();
				var groupTemplate = '<?php echo addslashes(
					str_replace(
						array("\n", "\r"), '',
						RedshopbLayoutHelper::render(
							'redshopb.field.productsearch_criterias_entity',
							array(
								'id'     => $id,
								'entity' => array(
									'title' => '{entityName}',
									'name' => '{entityId}'
								),
								'entityNumber' => '{entityId}',
								'name'   => $name,
								'groupNumber' => '{groupNumber}'
							)
						)
					)
				); ?>';
				groupTemplate = groupTemplate
					.replace(/\{groupNumber}/g, count)
					.replace(/\{entityId}/g, criteriaValue)
					.replace(/\{entityName}/g, criteriaText);
				$divSearchGroups.find('.activeSearchCriteriaGroup .oneSearchGroupEntities').append(groupTemplate);
				$('select').chosen({"disable_search_threshold": 10, "allow_single_deselect": true});
				$dropdown.find('option[value="'+criteriaValue+'"]').prop('disabled', true);
				$dropdown.find('option[value=""]').prop('selected', true).trigger("liszt:updated");
				initDragAndDropPlugin();
			}

			function recalculateSearchEntities() {
				var groupIndex = 0;
				$divSearchGroups.find('.oneSearchGroup').each(function(index){
					var $oneSearchGroup = $(this);
					if ($oneSearchGroup.find('.oneSearchGroupEntity').length > 0){
						groupIndex = groupIndex + 1;
						$oneSearchGroup.find('.order-number').html(groupIndex);
						$oneSearchGroup.find('.oneSearchGroupEntity').each(function () {
							$(this).find('.inputForReplace').each(function(){
								var $this = $(this);
								var data = $this.data('templateName');
								if (data != undefined && data){
									data = data.replace(/\{replaceNumberGroup}/g, groupIndex);
									$this.attr('name', data);
								}
								data = $this.data('templateId');
								if (data != undefined && data){
									data = data.replace(/\{replaceNumberGroup}/g, groupIndex);
									$this.attr('id', data);
								}
							})
						})
					} else {
						$oneSearchGroup.remove();
					}
				});
				$('select').chosen({"disable_search_threshold": 10, "allow_single_deselect": true});
			}

			function deleteOneSearchGroup($this) {
				$this.closest('.oneSearchGroup').find('.searchEntityName').each(function(){
					var searchEntityVal = $(this).val();
					if (searchEntityVal){
						$dropdown.find('option[value="'+searchEntityVal+'"]').prop('disabled', false).trigger("liszt:updated");
					}
				});
				$this.closest('.oneSearchGroup').remove();
				recalculateSearchEntities();
				initDragAndDropPlugin();
			}

			$('#<?php echo $id ?>_button_add_in_new_group').on('click', function(e){
				e.preventDefault();
				addCriteriaGroup();
			});
			$('#<?php echo $id ?>_button_add_in_selected_group').on('click', function(e){
				e.preventDefault();
				addCriteriaEntity();
			});
			$divSearchGroups
				.on('click', '.oneSearchGroup', function () {
					$('.oneSearchGroup').removeClass('activeSearchCriteriaGroup');
					$(this).addClass('activeSearchCriteriaGroup');
				})
				.on('click', '.deleteCriteria', function (e) {
					e.preventDefault();
					var searchEntityVal = $(this).closest('.oneSearchGroupEntity').find('.searchEntityName').val();
					if (searchEntityVal){
						$dropdown.find('option[value="'+searchEntityVal+'"]').prop('disabled', false).trigger("liszt:updated");
					}

					if ($(this).closest('.oneSearchGroup').find('.oneSearchGroupEntity').length > 1){
						$(this).closest('.oneSearchGroupEntity').remove();
					}else{
						deleteOneSearchGroup($(this));
					}
				})
				.on('click', '.deleteSearchGroup', function (e) {
					e.preventDefault();
					deleteOneSearchGroup($(this));
				});
		});
	})(jQuery);
</script>
<button class="btn btn-success" id="<?php echo $id . '_button_add_in_new_group' ?>"><?php echo Text::_('JADD') ?> in new group</button>
<button class="btn btn-success" id="<?php echo $id . '_button_add_in_selected_group' ?>"><?php echo Text::_('JADD') ?> in selected group</button>

<div class="row-fluid searchGroupTitles">
	<div class="span2">
		<strong><?php echo Text::_('COM_REDSHOPB_CONFIG_PRODUCT_SEARCH_ADDITIONAL_FIELDS_GROUP_NUMBER') ?></strong>
	</div>
	<div class="span10">
		<div class="row-fluid">
			<div class="span3"><strong><?php echo Text::_('COM_REDSHOPB_NAME') ?></strong></div>
			<div class="span3"><strong><?php echo Text::_('COM_REDSHOPB_CONFIG_PRODUCT_SEARCH_ADDITIONAL_FIELDS_USE_METHOD') ?></strong></div>
			<div class="span3"><strong><?php echo Text::_('COM_REDSHOPB_CONFIG_PRODUCT_SEARCH_ADDITIONAL_FIELDS_USE_SYNONYMS') ?></strong></div>
			<div class="span3"><strong><?php echo Text::_('COM_REDSHOPB_CONFIG_PRODUCT_SEARCH_ADDITIONAL_FIELDS_USE_STEMMER') ?></strong></div>
		</div>
	</div>
</div>
<div class="divSearchGroups" id="divSearchGroups">
	<?php
	if (!empty($value)):
		$groupNumbers = array_keys($value);
		$lastKey      = end($groupNumbers);

		foreach ($value as $groupNumber => $entities):
			echo RedshopbLayoutHelper::render(
				'redshopb.field.productsearch_criterias_group',
				array(
				'id'     => $id,
				'entities' => $entities,
				'name'   => $name,
				'groupNumber' => $groupNumber,
				'groupClass' => $lastKey == $groupNumber ? 'activeSearchCriteriaGroup' : ''
				)
			);
		endforeach;
	endif;
	?>
</div>
