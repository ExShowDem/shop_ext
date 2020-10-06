<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Views
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Form\FormField;

// HTML helpers
HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('vnrbootstrap.tooltip');
HTMLHelper::_('rjquery.chosen', 'select');
HTMLHelper::_('rsearchtools.main');

$action = RedshopbRoute::_('index.php?option=com_redshopb&view=filter_fieldset&layout=edit');

/** @var Form $form */
$form             = $this->form;
$standardFieldset = $form->getFieldset('standard');

echo RedshopbBrowserBreadcrumbs::renderBreadcrumbs();
?>
<div class="redshopb-field">
	<div class="row">
		<ul class="nav nav-tabs" id="fieldTabs">
			<li class="active">
				<a href="#fieldset_details_tab" data-toggle="tab"><?php echo Text::_('COM_REDSHOPB_FIELD_DETAILS');?></a>
			</li>
			<li>
				<a href="#fieldset_fields_tab" data-toggle="tab"><?php echo Text::_('COM_REDSHOPB_FIELDS');?></a>
			</li>
		</ul>
		<form action="<?php echo $action; ?>" method="post" name="adminForm" id="adminForm"
			  class="form-validate form-horizontal redshopb-field-form">
			<div class="tab-content">
				<div class="tab-pane active" id="fieldset_details_tab">

					<?php /** @var FormField $field */ ?>
					<?php foreach ($standardFieldset AS $field):
						$backWSValueButton = $this->form->getBackWSValueButton($field->fieldname, $field->group);
						echo $field->renderField(
							array(
								'backWSValueButton' => $backWSValueButton,
								'class' => $backWSValueButton ? 'controlGroupForOverrideField' : ''
							)
						);
					endforeach; ?>
					<!-- hidden fields -->
					<input type="hidden" name="option" value="com_redshopb">
					<input type="hidden" name="id" value="<?php echo $this->item->id; ?>">
					<input type="hidden" name="task" value="">
					<?php echo HTMLHelper::_('form.token'); ?>
				</div>
				<div class="tab-pane" id="fieldset_fields_tab">
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
						<tbody id="includedFieldsTable">
						<?php foreach ($this->item->fields as $field): ?>
							<tr id="field-<?php echo $field->field_id; ?>">
								<td>
									<?php echo $this->escape($field->field_name); ?>
								</td>
								<td><?php echo $this->escape($field->field_type_name); ?></td>

								<td><?php echo $this->escape($field->type_code);?></td>
								<td>
									<a href="javascript:void(0)" class="btn btn-small btn-sm btn-danger" onclick="removeField(event);" data-id="<?php echo (int) $field->field_id;?>">
										<i class="icon-remove"></i>
									</a>
									<input type="hidden" name="jform[fields][<?php echo $field->field_id;?>]" value="<?php echo $field->field_id;?>" />
								</td>
							</tr>
						<?php endforeach;?>
						</tbody>
					</table>
					<div>
						<p><button type="button" class="btn" onclick="jQuery('#fieldsModal').modal('toggle');"><?php echo Text::_('COM_REDSHOPB_ADD');?></button></p>
					</div>
					<div class="alert alert-info">
						<div class="pagination-centered">
							<h5><?php echo Text::_('COM_REDSHOPB_YOU_MUST_SAVE_FILTER_FOR_CHANGES_TO_TAKE_AFFECT'); ?></h5>
						</div>
					</div>
				</div>
			</div>
		</form>
	</div>
</div>
<?php
echo RedshopbLayoutHelper::render(
	'filter_fieldset.modal.default',
	array (
		'fields' => $this->unselectedFields,
		'id'     => $this->item->id
	)
); ?>
<script type="text/javascript">
	function removeField(event)
	{
		var targ = jQuery(event.target);
		var parent = jQuery(event.target.parentElement.parentElement);
		if(event.target.tagName == 'I')
		{
			var targ = jQuery(event.target.parentElement);
			var parent = jQuery(event.target.parentElement.parentElement.parentElement);
		}

		parent.detach();
		targ.removeClass('btn-danger').addClass('btn-success');
		targ.attr('onclick', 'modalAddField(event);');

		targ.children('i').attr('class', 'icon-plus');

		jQuery('#excludedFieldsTable').append(parent);
	}
</script>
