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

$action = RedshopbRoute::_('index.php?option=com_redshopb&view=template');
$isNew = (int) $this->item->id <= 0;
$templateName = $this->state->get('templateName');

if (!$isNew)
{
	$this->form->setFieldAttribute('scope', 'readonly', 'true');
	$this->form->setFieldAttribute('scope', 'required', 'false');
	$this->form->setFieldAttribute('template_group', 'readonly', 'true');
	$this->form->setFieldAttribute('template_group', 'required', 'false');

	// Condition for edit any template
	if ($templateName != 'none')
	{
		$this->form->setFieldAttribute('templateName', 'readonly', 'true');
		$this->form->setValue('templateName', null, $this->state->get('templateName'));
	}

	// Condition for create new customization
	elseif ($templateName == 'none')
	{
		$defaultTemplate = Factory::getApplication()->getTemplate();
		$templateNames = array();
		$customizations = RedshopbHelperTemplate::getListCustomizations($this->item);

		if (count($customizations) > 0)
		{
			$templateNames = array_keys($customizations);
		}

		$templateNames[] = 'system';
		$this->form->setFieldAttribute('templateName', 'exclude', implode('|', $templateNames));

		if (!in_array($defaultTemplate, $templateNames))
		{
			$this->form->setValue('templateName', null, $defaultTemplate);
		}
	}

	// Condition for edit customization
	if ($templateName != '')
	{
		$this->form->setFieldAttribute('name', 'readonly', 'true');
		$this->form->setFieldAttribute('alias', 'readonly', 'true');
		$this->form->setFieldAttribute('state', 'type', 'hidden');
		$this->form->setFieldAttribute('default', 'type', 'hidden');
	}
}

$templateGroup = $this->form->getValue('template_group');
$scope         = $this->form->getValue('scope');

if (!$templateGroup)
{
	$this->form->setFieldAttribute('scope', 'readonly', 'true');
}

if ($templateGroup != 'email' || $scope == 'send-to-friend')
{
	$this->form->removeField('mail_subject', 'params');
}

// Condition for create new template in the component folder
if ($templateName == '')
{
	$this->form->setFieldAttribute('templateName', 'type', 'hidden');
	$this->form->setFieldAttribute('templateName', 'required', 'false');
}

// HTML helpers
HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('vnrbootstrap.tooltip');
HTMLHelper::_('rjquery.chosen', 'select');

echo RedshopbBrowserBreadcrumbs::renderBreadcrumbs();
?>
<script type="text/javascript">
	(function ($) {
		$(document).ready(function () {
			jQuery('.tab-content label').on('click', function (e) {
				var $button = jQuery(this);
				var $tag = '{' + $button.html() + '}';
				var cm = $('.CodeMirror')[0].CodeMirror;
				var doc = cm.getDoc();
				var cursor = doc.getCursor();
				var pos = {
					line: cursor.line,
					ch: cursor.ch
				}
				doc.replaceRange($tag, pos);
				$button.addClass('label-success');
			});
		});
	})(jQuery);
</script>
<div class="redshopb-template">
	<form action="<?php echo $action; ?>" method="post" name="adminForm" id="adminForm" class="form-validate form-horizontal redshopb-template-form">
		<div class="row-fluid">
			<div class="span6">
				<?php echo $this->form->renderField('name'); ?>
				<?php echo $this->form->renderField('template_group'); ?>
				<?php echo $this->form->renderField('scope'); ?>
				<?php
				if (strpos($templateName, '.') !== false)
				{
					?>
					<div class="control-group">
						<div class="control-label">
							<label id="jform_jform_templateName-lbl" for="jform_templateName">
								<?php echo Text::_('COM_REDSHOPB_TEMPLATE_FILE_PATH') ?>
							</label>
						</div>
						<div class="controls">
							<span class="label label-warning">
							<?php echo str_replace('.', '/', $templateName)
								. '/' . RedshopbHelperTemplate::getGroupFolderName($this->item->template_group)
								. '/' . $this->item->scope
								. '/' . $this->item->alias . '.php'; ?>
							</span>
						</div>
					</div>
					<?php
				}
				else
				{
					echo $this->form->renderField('templateName');
				}
				?>
				<div class="control-group">
					<div class="control-label">
						<?php echo $this->form->getLabel('alias'); ?>
					</div>
					<div class="controls">
						<div class="input-append">
							<?php echo $this->form->getInput('alias'); ?>
							<span class="add-on">.php</span>
						</div>
					</div>
				</div>
				<?php echo $this->form->renderField('state'); ?>
				<?php echo $this->form->renderField('default'); ?>
			</div>
			<div class="span6">
				<?php
				$fields = $this->form->getFieldset('params');

				if ($fields)
				{
					foreach ($fields as $field)
					{
						?>
						<div class="control-group">
							<div class="control-label">
								<?php echo $field->label; ?>
							</div>
							<div class="controls">
								<?php echo $field->input; ?>
							</div>
						</div>
						<?php
					}
				}
				?>
			</div>
		</div>
		<?php echo $this->form->renderField('content'); ?>
		<div>
			<span class="label label-success"><?php echo Text::_('COM_REDSHOPB_TEMPLATE_TAG_USED'); ?></span>
			<span class="label label-default"><?php echo Text::_('COM_REDSHOPB_TEMPLATE_TAG_NOT_USED'); ?></span>
		</div>
		<br />
		<ul class="nav nav-tabs clear" id="templateTabs">
			<?php
			$lang = Factory::getLanguage();
			$i = 0;

			foreach ($this->wholeTags as $section => $tags): ?>
				<li<?php echo $i == 0 ? ' class="active"' : ''; ?>>
					<a href="#tab<?php echo ucfirst($section); ?>Tags" data-toggle="tab">
						<?php
						if ($lang->hasKey('COM_REDSHOPB_TEMPLATE_' . strtoupper($section) . '_TAGS'))
						{
							echo Text::_('COM_REDSHOPB_TEMPLATE_' . strtoupper($section) . '_TAGS');
						}
						else
						{
							echo ucfirst($section);
						}
						?>
					</a>
				</li>
				<?php
				$i++;
			endforeach; ?>
		</ul>
		<div class="tab-content">
			<?php
			$i = 0;

			foreach ($this->wholeTags as $section => $tags) : ?>
				<div
					class="tab-pane<?php echo $i == 0 ? ' active' : ''; ?>"
					id="tab<?php echo ucfirst($section); ?>Tags"
				>
					<?php
					echo RedshopbLayoutHelper::render(
						'template.tags',
						array(
							'usedTags'  => $this->usedTags,
							'tags'   => $tags,
						)
					);
					?>
				</div>
				<?php $i++;
			endforeach; ?>
		</div>

		<!-- hidden fields -->
		<input type="hidden" name="option" value="com_redshopb">
		<input type="hidden" name="id" value="<?php echo $this->item->id; ?>">
		<input type="hidden" name="task" id="formTask" value="">
		<input type="hidden" name="templateName" value="<?php echo $this->state->get('templateName'); ?>">
		<?php echo HTMLHelper::_('form.token'); ?>
	</form>
</div>
