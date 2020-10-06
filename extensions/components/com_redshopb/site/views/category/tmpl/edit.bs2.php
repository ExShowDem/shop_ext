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
use Joomla\CMS\Factory;


// HTML helpers
HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('rjquery.framework');
HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('vnrbootstrap.tooltip');
HTMLHelper::_('rjquery.chosen', 'select');

$action          = RedshopbRoute::_('index.php?option=com_redshopb&view=category&layout=edit');
$isNew           = (int) $this->item->id <= 0;
$imagePath       = RedshopbHelperThumbnail::originalToResize($this->item->image, 150, 80, 100, 0, 'categories');
$params          = $this->form->getFieldset('params');
$seoTitles       = $params['jform_params_seo_page_titles'];
$seoHeadings     = $params['jform_params_seo_page_headings'];
$seoDescriptions = $params['jform_params_seo_page_description'];
$seoKeywords     = $params['jform_params_seo_page_keywords'];

echo RedshopbBrowserBreadcrumbs::renderBreadcrumbs();

Factory::getDocument()->addStyleDeclaration('
.tab-content > form > .tab-pane {
	display: none;
}
.tab-content > form > .active{
	display: block;
}
'
);
?>
<script type="text/javascript">
	Joomla.submitbutton = function (task) {
		if (task == "category.cancel" || document.formvalidator.isValid(document.getElementById("adminForm"))) {
			<?php echo $this->form->getField('description')->save() ?>
			Joomla.submitform(task, document.getElementById("adminForm"));
			Joomla.submitform(task);
		}
	};
</script>
<script type="text/javascript">
	(function ($) {
		$(document).ready(function () {
			$('#categoryTabs a[data-toggle="tab"]').on('click', function () {
				var targ = $(this);
				$('.tab-content > form > .tab-pane').removeClass('active');
				$('#adminForm input[name="tab"]').val(targ.attr('href').substr(1));
			});
		});
	})(jQuery);
</script>

<div class="row-fluid">
	<div class="span12">
		<ul class="nav nav-tabs" id="categoryTabs">
			<li class="active">
				<a href="#details" data-toggle="tab">
					<?php echo Text::_('COM_REDSHOPB_DETAILS'); ?>
				</a>
			</li>
			<li>
				<a href="#seo" data-toggle="tab">
					<?php echo Text::_('COM_REDSHOPB_SEO'); ?>
				</a>
			</li>
			<?php if ($this->item->id || $this->anyRequired): ?>
				<li>
					<a href="#fields"
					   data-toggle="tab"><?php echo Text::_('COM_REDSHOPB_FIELDS_TITLE') ?></a>
				</li>
			<?php endif; ?>

			<?php if ($this->item->id): ?>
				<li>
					<a href="#fieldAssociations" data-toggle="tab" data-ajax-tab-load="true">
						<?php echo Text::_('COM_REDSHOPB_FIELD_ASSOCIATION_TITLE'); ?>
					</a>
				</li>
			<?php endif; ?>
		</ul>
	</div>
</div>

<div class="redshopb-category">
	<div class="tab-content">
		<form action="<?php echo $action; ?>" method="post" name="adminForm" id="adminForm"
			  class="form-validate form-horizontal redshopb-category-form" enctype="multipart/form-data">
			<div class="tab-pane active" id="details">
				<?php
				$detailFieldset = $this->form->getFieldset('standard');

				foreach ($detailFieldset AS $field):
					if (($field->fieldname == 'deleteImage' && !$this->item->image) || $field->fieldname == 'id')
					{
						continue;
					}

					if ($field->fieldname == 'imageFileUpload')
					{
						$backWSValueButton = $this->form->getBackWSValueButton('image', $field->group);
					}
					else
					{
						$backWSValueButton = $this->form->getBackWSValueButton($field->fieldname, $field->group);
					}

					echo $field->renderField(
						array(
							'backWSValueButton' => $backWSValueButton,
							'class'             => $backWSValueButton ? 'controlGroupForOverrideField' : ''
						)
					);

					if ($field->fieldname == 'imageFileUpload' && !empty($imagePath)) : ?>
						<img src="<?php echo $imagePath; ?>" /><?php
					endif;
				endforeach;

				if ($this->syncReference != '') :
					?>
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('sync_related_id'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('sync_related_id'); ?>
						</div>
					</div>

					<?php
				endif;
				?>
				<!-- hidden fields -->
				<?php echo $this->form->getInput('image'); ?>
				<input type="hidden" name="option" value="com_redshopb">
				<input type="hidden" name="id" value="<?php echo $this->item->id ?>">
				<input type="hidden" name="task" value="">
				<?php echo HTMLHelper::_('form.token'); ?>
			</div>
			<div class="tab-pane" id="seo">
				<div class="row-fluid">
					<div class="span12">
						<div class="alert">
							<button type="button" class="close" data-dismiss="alert">&times;</button>
							<strong>
								<i class="icon-warning-sign"></i>
								<?php echo Text::_('COM_REDSHOPB_SEO_CONFIG_OVERRIDE_WARNING') ?>
							</strong>
						</div>
						<div class="row-fluid">
							<div class="span12 adapt-inputs">
								<div class="control-group">
									<div class="control-label">
										<?php echo $seoTitles->label; ?>
									</div>
									<div class="controls">
										<?php echo $seoTitles->input; ?>
									</div>
								</div>
								<div class="control-group">
									<div class="control-label">
										<?php echo $seoHeadings->label; ?>
									</div>
									<div class="controls">
										<?php echo $seoHeadings->input; ?>
									</div>
								</div>
								<div class="control-group">
									<div class="control-label">
										<?php echo $seoDescriptions->label; ?>
									</div>
									<div class="controls">
										<?php echo $seoDescriptions->input; ?>
									</div>
								</div>
								<div class="control-group">
									<div class="control-label">
										<?php echo $seoKeywords->label; ?>
									</div>
									<div class="controls">
										<?php echo $seoKeywords->input; ?>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<?php if ($this->item->id || $this->anyRequired):?>
				<div class="tab-pane" id="fields">
					<div class="row-fluid fields-content">
						<div class="span12">
							<?php echo RedshopbLayoutHelper::render('fields.fields',
								array(
									'form'                 => $this->form,
									'fieldsUsedInTemplate' => $this->state->get('fieldsUsedInTemplate'),
									'formName'             => 'fieldsForm',
									'scope'                => 'category',
									'task'                 => 'category.saveFields',
									'itemId'               => $this->item->id,
									'action'               => RedshopbRoute::_('index.php?option=com_redshopb&view=category&layout=edit&id=' . $this->item->id),
									'return'               => base64_encode('index.php?option=com_redshopb&view=category&layout=edit&tab=fields&id=' . $this->item->id))
							); ?>
						</div>
					</div>
				</div>
			<?php endif;?>

			<?php if ($this->item->id): ?>
				<div class="tab-pane " id="fieldAssociations">
					<?php
					echo RedshopbLayoutHelper::render('fields.associatefields',
						array(
							'item_id'            => $this->item->id,
							'fields'             => $this->fields,
							'unassociatedFields' => $this->unassociatedFields,
							'form'               => $this->form,
							'controller'         => 'category'
						)
					);
					?>
				</div>
			<?php endif; ?>
		</form>
	</div>
</div>
