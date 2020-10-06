<?php
/**
 * @package     Aesir.E-Commerce
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('JPATH_REDCORE') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

$id     = $displayData['id'];
$fields = $displayData['fields'];
?>
<div id="fieldsModal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h3><?php echo Text::_('COM_REDSHOPB_ADD_EDIT_FIELD_VALUE');?></h3>
			</div>
			<div class="modal-body">
				<div class="container-fluid">
					<div class="row">
						<div class="col-md-12">
							<div id="fieldsList">
								<table class="table table-striped table-hover">
									<thead>
									<tr>
										<th><?php echo Text::_('COM_REDSHOPB_FIELD_NAME_LABEL');?></th>
										<th><?php echo Text::_('COM_REDSHOPB_FIELD_TYPE_ID_LABEL');?></th>
										<th><?php echo Text::_('COM_REDSHOPB_FIELD_ALIAS_LABEL');?></th>
										<td width="1%"></td>
									</tr>
									</thead>
									<tfoot>
									<tr><td colspan="4"><p></p></td></tr>
									</tfoot>
									<tbody id="excludedFieldsTable">
									<?php echo RedshopbLayoutHelper::render('filter_fieldset.modal.fields', array('fields' => $fields)); ?>
									</tbody>
								</table>
								<div class="alert alert-info">
									<div class="pagination-centered">
										<h5><?php echo Text::_('COM_REDSHOPB_ONLY_EXCLUDED_FIELDS_ARE_SHOWN_HERE'); ?></h5>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
			<span id="searchFields" class="pull-left">
				<form id="searchForm" action="" class="form-search">
					<input type="text" name="search" id="fields_search" class="input-medium search-query" />
					<button type="button" class="btn btn-sm" onclick="searchFields()"><?php echo Text::_('JSEARCH');?></button>
					<button type="button" class="btn btn-sm btn-danger" onclick="jQuery('#fields_search').val('');searchFields();"><?php echo Text::_('JCLEAR')?></button>
				</form>
			</span>
				<a href="#" class="btn" data-dismiss="modal"><?php echo Text::_('JTOOLBAR_CLOSE');?></a>
			</div>
		</div>
	</div>
</div>
<script type="text/javascript">
	jQuery(document).ready(function() {
		jQuery('#searchForm').on('submit', function(event) {
			event.preventDefault();

			return false;
		});

		jQuery('#fields_search').on('keyup', function(event) {
			event.preventDefault();

			if (event.keyCode == 13 || event.which == 13)
			{
				searchFields();
			}

			return false;
		});
	});

	function modalAddField(event)
	{
		var targ     = jQuery(event.target);
		var parent   = jQuery(event.target.parentElement.parentElement);
		var included = jQuery('#includedFieldsTable');

		if(event.target.tagName == 'I')
		{
			targ   = jQuery(event.target.parentElement);
			parent = jQuery(event.target.parentElement.parentElement.parentElement);
		}

		parent.detach();

		if (included.find('#' + parent.attr('id')).length > 0)
		{
			return;
		}

		targ.removeClass('btn-success').addClass('btn-danger');
		targ.attr('onclick', 'removeField(event);');

		targ.children('i').attr('class', 'icon-remove');

		included.append(parent);
	}

	function searchFields()
	{
		var tbody = jQuery('#excludedFieldsTable');

		jQuery.ajax({
			url        : '<?php echo RedshopbRoute::_('index.php?option=com_redshopb&task=filter_fieldset.ajaxSearchFields', false); ?>',
			dataType   : 'JSON',
			beforeSend : function () {
				tbody.html('<?php echo HTMLHelper::image('media/com_redshopb/images/ajax-loader.gif', '') ?>');
				tbody.addClass('opacity-40');
			},
			data       : {
				'id'     : '<?php echo $id; ?>',
				'search' : jQuery('#fields_search').val()
			}
		}).done(function (response){
			tbody.html(response.body);
			tbody.removeClass('opacity-40');
		});

		return false;
	}
</script>
