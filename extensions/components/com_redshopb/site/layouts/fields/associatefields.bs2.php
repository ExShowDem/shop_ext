<?php
/**
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Session\Session;

extract($displayData);

$entityName  = 'RedshopbEntity' . ucfirst($controller);
$canReadOnly = false;

if (class_exists(ucfirst($entityName)))
{
	$canReadOnly = $entityName::getInstance($itemId)->canReadOnly();
}

if (!$canReadOnly):
?>
<script type="text/javascript">
	var itemId = '<?php echo $itemId; ?>';
	var fieldId;

	(function ($) {
		$(document).ready(function () {
			$('.field_association-add').click(function(event){
				fieldId = $("#jform_field_association").val();
				var url = '<?php echo Uri::root(); ?>index.php?option=com_redshopb&task=<?php echo $controller; ?>.ajaxAddFieldAssociation';

				$.ajax({
					url: url,
					type: 'POST',
					cache: false,
					data : {
						'<?php echo Session::getFormToken(); ?>' : 1,
						"field_id": fieldId,
						"item_id": itemId
					},
				})
				.done(function(data){
					if (data == 1) {
						$('.asso-field-' + fieldId).removeClass('hidden');
					}
					else {
						var saveFailedMsg = '<div class="alert alert-warning"><a class="close" data-dismiss="alert">×</a><?php echo Text::_('COM_REDSHOPB_FIELD_FAILED_TO_ADD'); ?></div>';
						jQuery('#system-message-container').append(saveFailedMsg);
					}
				});
			});
		});
	})(jQuery);

	function removeField(event)
	{
		var targ;

		if (event.target.tagName == 'I')
		{
			targ = jQuery(event.target.parentElement);
		}
		else
		{
			targ = jQuery(event.target);
		}

		fieldId = targ.attr('data-fieldremoveid');

		var url = '<?php echo Uri::root(); ?>index.php?option=com_redshopb&task=<?php echo $controller; ?>.ajaxRemoveFieldAssociation';

		jQuery.ajax({
			url: url,
			type: 'POST',
			cache: false,
			data : {
				'<?php echo Session::getFormToken(); ?>' : 1,
				"field_id": fieldId,
				"item_id": itemId
			},
		})
		.done(function(data){
			if (data == 1) {
				jQuery('.asso-field-' + fieldId).addClass('hidden');
			}
			else {
				var removeFailedMsg = '<div class="alert alert-warning"><a class="close" data-dismiss="alert">×</a><?php echo Text::_('COM_REDSHOPB_FIELD_FAILED_TO_REMOVE'); ?></div>';
				jQuery('#system-message-container').append(removeFailedMsg);
			}
		});
	}
</script>
<div class="control-group">
	<form method="post" name="fieldAssociationForm" id="fieldAssociationForm"
	class="form-horizontal">
		<?php echo $form->renderField('field_association'); ?>
		<a class="btn btn-success field_association-add" href="javascript:void(0);" data-task="field_association.add">
			<i class="icon-plus-sign"></i>
			<?php echo Text::_('COM_REDSHOPB_FIELD_ASSOCIATION_ADD'); ?>
		</a>
	</form>
</div>
<?php endif ?>
<div>
	<table class="table table-striped table-hover">
		<thead>
			<tr>
				<th><?php echo Text::_('COM_REDSHOPB_FIELD_NAME_LABEL');?></th>
				<td width="1%"></td>
			</tr>
		</thead>
		<tbody id="includedFieldsTable">
			<?php foreach ($fields as $field): ?>
				<tr class="asso-field-<?php echo $field->id ?>">
					<td>
						<?php echo $field->name; ?>
					</td>
					<td>
						<?php if (!$canReadOnly): ?>
						<div class="btn-group">
							<a class="product-item-remove btn btn-default btn-danger" href="javascript:void(0);" onclick="removeField(event);" data-fieldremoveid="<?php echo $field->id; ?>">
								<i class="icon-remove"></i>
							</a>
						</div>
						<?php endif ?>
					</td>
				</tr>
			<?php endforeach; ?>

			<?php foreach ($unassociatedFields as $field): ?>
				<tr class="hidden asso-field-<?php echo $field->id ?>">
					<td>
						<?php echo $field->name; ?>
					</td>
					<td>
						<?php if (!$canReadOnly): ?>
							<div class="btn-group">
								<a class="product-item-remove btn btn-default btn-danger" href="javascript:void(0);" onclick="removeField(event);" data-fieldremoveid="<?php echo $field->id; ?>">
									<i class="icon-remove"></i>
								</a>
							</div>
						<?php endif ?>
					</td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
</div>
