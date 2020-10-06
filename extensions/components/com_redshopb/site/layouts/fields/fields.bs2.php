<?php
/**
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Session\Session;

extract($displayData);

$action      = (isset($action)) ? $action : RedshopbRoute::_('index.php?option=com_redshopb&view=product&layout=edit&id=' . $itemId);
$showToolbar = (isset($showToolbar)) ? (bool) $showToolbar : false;
$class       = (isset($class)) ? $class : '';

$fieldSets     = $form->getFieldsets();
$activeControl = 'class="active"';
$activePane    = ' active';
?>
<script type="text/javascript">
	(function ($)
	{
		function addNewFieldRow(obj) {
			var selectedRow = $(obj).parents('.form-group').find('.controls:first');
			var fieldId = $(obj).val();

			$.ajax({
				url: '<?php echo Uri::root(); ?>index.php?option=com_redshopb&task=scope_fields.ajaxnewfielddatarow',
				type: 'POST',
				data: {
					"field_id" : fieldId,
					"<?php echo Session::getFormToken() ?>": 1
				},
				cache: false
			}).done(function (data) {
				$(selectedRow).append("<div style=\"margin-bottom:3px;\">" + data + '</div>');
				$(selectedRow).find('input').attr('form', 'adminForm');
				var rows = $(selectedRow).find('.rdatepicker');
				var lastRow = $(rows[(rows.length - 1)]);
				var baseId = $(rows[0]).attr('id') + '_' + rows.length;
				lastRow.attr('id', baseId);
				$('#' + baseId).datepicker();

				$(selectedRow).find('textarea').attr('form', 'adminForm');
				var editors = $(selectedRow).find('div.editor textarea');

				if (editors.length > 0)
				{
					var lastEditor = $(editors[(editors.length - 1)]);
					var editorBaseId =  $(editors[0]).attr('id');
					lastEditor.attr('id', editorBaseId.substr(0, (editorBaseId.length - 1)) + (editors.length - 1));
					lastEditor.siblings('div.toggle-editor').remove();
					tinymce.init({selector: '#' + lastEditor.attr('id'), inline: false});
				}

				checkExtraFields(selectedRow);
			});
		}

		function removeRow(button)
		{
			var controls = $(button).parents('.controls');
			$(button).parent().find('[name^="' + $(button).val() + '"][type!="hidden"]').val('');
			$(button).parent().detach().appendTo('#removedFieldDataValues');
			checkExtraFields(controls);
		}

		function resetRow(button)
		{
			$(button).parent().find('input[name^="' + $(button).val() + '"][type!="hidden"]').val('');
			checkExtraFields($(button).parents('.controls'));
		}

		function checkExtraFields(parent)
		{
			var text     = '<i class="icon-minus-sign"></i> <?php echo Text::_('COM_REDSHOPB_REMOVE'); ?>';
			var eleClass = 'remove-field-row';
			var flag     = 0;
			var wells    = $(parent).find('.well');

			if (wells.length == 1)
			{
				text     = '<i class="icon-remove"></i> <?php echo Text::_('COM_REDSHOPB_RESET'); ?>';
				eleClass = 'reset-field-row';
				flag     = 1;
			}

			parent.find('button')
				.removeClass('remove-field-row reset-field-row')
				.addClass(eleClass)
				.html(text)
				.unbind('click')
				.on('click', function() {
					if (flag == 0)
					{
						removeRow(this);
					}
					else if (flag == 1)
					{
						resetRow(this);
					}

					return true;
				});
		}

		$(document).ready(function () {
			var body = $('body');

			body.on('click', '.add-field-row', function(){
				addNewFieldRow(this);

				return true;
			});

			$('.media-field').each(function() {
				checkExtraFields($(this).find('.controls'));
			});

			body.on('click', '.remove-field-row', function(){
				removeRow(this);

				return true;
			});

			body.on('click', '.reset-field-row', function() {
				resetRow(this);

				return true;
			});
		});
	})(jQuery);
</script>
<?php if ($showToolbar && RedshopbHelperACL::getPermission('manage', $scope, array('edit', 'edit.own'), true)): ?>
	<div class="row-fluid">
		<div class="btn-toolbar">
			<div class="btn-group">
					<button class="btn btn-success"
							onclick="Joomla.submitform('<?php echo $task; ?>', document.getElementById('<?php echo $formName; ?>'))"
							href="#">
						<i class="icon-save"></i>
						<?php echo Text::_('COM_REDSHOPB_FIELDS_SAVE_ALL_FIELDS') ?>
					</button>
			</div>
		</div>
	</div>
	<form method="post" name="<?php echo $formName; ?>" id="<?php echo $formName; ?>" enctype="multipart/form-data"
	  class="form form-horizontal form-validate field-set-form" action="<?php echo $action ?>">
<?php else: ?>
<div class="form form-horizontal form-validate field-set-form">
<?php endif; ?>
<ul class="nav nav-tabs clear" id="fieldsTabs">
<?php foreach ($fieldSets AS $fieldSet):?>
	<?php if ($fieldSet->name == 'hidden' || !isset($fieldSet->extra_field_set)): ?>
		<?php continue;?>
	<?php endif;?>
	<li <?php echo $activeControl; ?>>
		<?php $activeControl = null; ?>
		<a href="#<?php echo $fieldSet->name;?>"  data-toggle="tab">
			<?php echo Text::_($fieldSet->title);?>
		</a>
	</li>
<?php endforeach;?>
</ul>
<div class="tab-content">
	<?php foreach ($fieldSets AS $fieldSet):?>
		<?php if ($fieldSet->name == 'hidden' || !isset($fieldSet->extra_field_set)):?>
			<?php continue;?>
		<?php endif;?>
		<?php $hasFields = false;?>
		<div id="<?php echo $fieldSet->name; ?>" class="tab-pane <?php echo $activePane; ?>">
		<?php $activePane = null; ?>
			<div class="row-fluid">
				<div class="span12">
					<?php foreach ($form->getFieldset($fieldSet->name) AS $field):?>
						<?php $hasFields = true;?>
						<div class="form-group">
							<?php if (!$field->hidden) : ?>
								<div class="control-label">
									<?php echo $field->label; ?>
									<?php echo $form->getBackWSValueButton($field->fieldname, $field->group); ?>

									<?php if ($field->getAttribute('multiple_values') === "1" && $field->getAttribute('value_type') != 'field_value' && $field->getAttribute('disabled') != 'true') : ?>
										<button
											type="button"
											class="btn btn-success btn-small clear pull-right add-field-row"
											value="<?php echo $field->getAttribute('field_id'); ?>">
											<i class="icon-plus-sign"></i> <?php echo Text::_('COM_REDSHOPB_FIELDS_ADD_NEW_FIELD_DATA_VALUE'); ?>
										</button>
									<?php endif; ?>
								</div>
							<?php endif;?>
							<div class="controls">
									<?php echo $field->input; ?>
							</div>
						</div>
					<?php endforeach;?>

					<?php if (!$hasFields): ?>
						<div class="alert alert-info">
							<div class="pagination-centered">
								<h3><?php echo Text::_('COM_REDSHOPB_NOTHING_TO_DISPLAY') ?></h3>
							</div>
						</div>
					<?php endif;?>
				</div>
			</div>
		</div>
	<?php endforeach;?>
	<div id="removedFieldDataValues" style="display:none;"></div>
</div>
<?php if ($showToolbar && RedshopbHelperACL::getPermission('manage', $scope, array('edit', 'edit.own'), true)): ?>
	<!-- hidden fields -->
	<input type="hidden" name="item_id" value="<?php echo $itemId; ?>" />
	<input type="hidden" name="subitem_id" value="<?php echo (!empty($subItemId)) ? $subItemId : ''; ?>" />
	<input type="hidden" name="task" value="<?php echo $task; ?>" />
	<input type="hidden" name="tabSave" value="true" />
	<input type="hidden" name="option" value="com_redshopb" />
	<?php echo HTMLHelper::_('form.token'); ?>
</form>
<?php else: ?>
</div>
<?php endif;
