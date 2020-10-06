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

$isNew = (int) $this->item->id <= 0;

// HTML helpers
HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('vnrbootstrap.tooltip');
HTMLHelper::_('rjquery.chosen', 'select');

// Variables
$action = RedshopbRoute::_('index.php?option=com_redshopb&view=layout&layout=edit&id=' . $this->item->id);

$params               = $this->form->getFieldset('params');
$fieldTopImage        = $params['jform_params_topImage'];
$fieldBackgroundImage = $params['jform_params_backgroundImage'];
$fieldAddress         = $params['jform_params_address'];
$fieldWelcome         = $params['jform_params_welcome'];
$fieldCustomCSS       = $params['jform_params_customCSS'];
$fieldBackgroundColor = $params['jform_params_backgroundColor'];
$fieldHeadline        = $params['jform_params_headLine'];
Factory::getDocument()->addStyleDeclaration('
	.CodeMirror
	{
		height : 300px !important;
		margin: 15px 0;
	}
'
);

echo RedshopbBrowserBreadcrumbs::renderBreadcrumbs();
?>
<script type="text/javascript">
	jQuery(document).ready(function()
	{
		jQuery('a[href="#layout-customize"]').on('shown', function (e) {
			Joomla.editors.instances['jform_params_customCSS'].refresh();
		});

		Joomla.submitbutton = function(task)
		{
			if (task == "layout.cancel" || document.formvalidator.isValid(document.getElementById("adminForm")))
			{
				<?php echo $this->form->getField('address', 'params')->save() ?>
				<?php echo $this->form->getField('welcome', 'params')->save() ?>
				<?php echo $this->form->getField('headLine', 'params')->save() ?>
				Joomla.submitform(task, document.getElementById("adminForm"));
			}
		};
	});
</script>
<div class="redshopb-layout">
	<div class="row-fluid">
		<form action="<?php echo $action; ?>" method="post" name="adminForm" id="adminForm" class="form-validate redshopb-layout-form">

			<?php if ($this->item->id) : ?>
			<div class="span4">
				<h3>
					<?php echo Text::_('COM_REDSHOPB_LAYOUT_IMAGE_THUMB_LABEL'); ?>
				</h3>
				<?php
				echo RedshopbLayoutHelper::render(
					'layout.preview',
					array(
						'id' => $this->item->id
					)
				);
				?>
			</div>
			<?php endif; ?>
			<div class="span<?php echo $this->item->id ? 8 : 12; ?>">
				<ul class="nav nav-tabs" id="layoutTab">
					<li class="active">
						<a href="#layout-display-info" data-toggle="tab"><?php echo Text::_('COM_REDSHOPB_LAYOUT_DISPLAY_TITLE'); ?></a>
					</li>
					<li>
						<a href="#layout-customize" data-toggle="tab"><?php echo Text::_('COM_REDSHOPB_LAYOUT_CUSTOMIZE_TITLE'); ?></a>
					</li>
				</ul>
				<div class="tab-content">
					<div class="tab-pane active" id="layout-display-info">
						<fieldset class="form-horizontal">
							<div class="control-group">
								<div class="control-label">
									<?php echo $this->form->getLabel('name'); ?>
								</div>
								<div class="controls">
									<?php echo $this->form->getInput('name'); ?>
								</div>
							</div>
							<div class="control-group">
								<div class="control-label">
									<?php echo $this->form->getLabel('style'); ?>
								</div>
								<div class="controls">
									<?php echo $this->form->getInput('style'); ?>
								</div>
							</div>
							<div class="control-group">
								<div class="control-label">
									<?php echo $this->form->getLabel('company_id'); ?>
								</div>
								<div class="controls">
									<?php echo $this->form->getInput('company_id'); ?>
								</div>
							</div>
							<div class="control-group">
								<div class="control-label">
									<?php echo $this->form->getLabel('department_id'); ?>
								</div>
								<div class="controls">
									<?php echo $this->form->getInput('department_id'); ?>
								</div>
							</div>
							<div class="control-group">
								<div class="control-label">
									<?php echo $this->form->getLabel('menu_type'); ?>
								</div>
								<div class="controls">
									<?php echo $this->form->getInput('menu_type'); ?>
								</div>
							</div>
							<div class="control-group">
								<div class="control-label">
									<?php echo $this->form->getLabel('alias'); ?>
								</div>
								<div class="controls">
									<?php echo $this->form->getInput('alias'); ?>
								</div>
							</div>
							<div class="control-group">
								<div class="control-label">
									<?php echo $fieldTopImage->label; ?>
								</div>
								<div class="controls">
									<?php echo $fieldTopImage->input; ?>
								</div>
							</div>
							<div class="control-group">
								<div class="control-label">
									<?php echo $fieldBackgroundImage->label; ?>
								</div>
								<div class="controls">
									<?php echo $fieldBackgroundImage->input; ?>
								</div>
							</div>
							<div class="control-group">
								<div class="control-label">
									<?php echo $fieldBackgroundColor->label; ?>
								</div>
								<div class="controls">
									<?php echo $fieldBackgroundColor->input; ?>
								</div>
							</div>
						</fieldset>
					</div>
					<div class="tab-pane" id="layout-customize">
						<fieldset class="form-vertical">
							<div class="control-group">
								<div class="control-label">
									<?php echo $fieldAddress->label; ?>
								</div>
								<div class="controls">
									<?php echo $fieldAddress->input; ?>
								</div>
							</div>
							<div class="control-group">
								<div class="control-label">
									<?php echo $fieldWelcome->label; ?>
								</div>
								<div class="controls">
									<?php echo $fieldWelcome->input; ?>
								</div>
							</div>
							<div class="control-group">
								<div class="control-label">
									<?php echo $fieldHeadline->label; ?>
								</div>
								<div class="controls">
									<?php echo $fieldHeadline->input; ?>
								</div>
							</div>
							<?php
							if (RedshopbHelperACL::getPermission('manage', 'mainwarehouse'))
							:
							?>
							<div class="container-fluid">
							<div class="row-fluid">
							<div class="span12">
								<fieldset>
									<strong>
										<?php echo $fieldCustomCSS->label; ?>
									</strong>
									<?php echo $fieldCustomCSS->input; ?>
								</fieldset>
							</div>
							</div>
							</div>
							<?php
							endif;
							?>
						</fieldset>
					</div>
				</div>
			</div>

			<!-- hidden fields -->
			<input type="hidden" name="id" value="<?php echo $this->item->id; ?>" />
			<input type="hidden" name="option" value="com_redshopb" />
			<input type="hidden" name="task" value="" />
			<?php echo HTMLHelper::_('form.token'); ?>
		</form>
	</div>
</div>
