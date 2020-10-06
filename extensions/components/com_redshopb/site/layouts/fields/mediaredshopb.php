<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\Registry\Registry;

$data = (object) $displayData;
/** @var JFormFieldMediaRedshopb $field */
$field = $data->options['field'];

// Initialize some field attributes.
$accept         = !empty($field->accept) ? ' accept="' . $field->accept . '"' : '';
$size           = !empty($field->size) ? ' size="' . $field->size . '"' : '';
$class          = !empty($field->class) ? ' class="' . $field->class . '"' : '';
$disabled       = $field->disabled ? ' disabled' : '';
$required       = $field->required ? ' required aria-required="true"' : '';
$autofocus      = $field->autofocus ? ' autofocus' : '';
$multiple       = $field->multiple ? ' multiple' : '';
$paramData      = $data->options['field_data'] ? $data->options['field_data']->field_data_params : null;
$params         = new Registry($paramData);
$multipleValues = $data->options['field_data'] ? '[' . $data->options['field_data']->id . ']' : '[' . RFilesystemFile::getUniqueName() . ']';

$paramName = $field->name . $multipleValues . '[params]';
$fieldName = $field->name . $multipleValues;

$href = $params->get('external_url', null);

if (!$href && $data->options['field_data'])
{
	$scope = RInflector::pluralize($data->options['field_data']->scope);
	$href  = RedshopbHelperMedia::getFullMediaPath($params->get('internal_url', ''), $scope, $data->options['field_data']->type_alias);
}

// Initialize JavaScript field attributes.
$onchange = $field->onchange ? ' onchange="' . $field->onchange . '"' : '';

// Including fallback code for HTML5 non supported browsers.
HTMLHelper::_('jquery.framework');
HTMLHelper::_('script', 'system/html5fallback.js', false, true);
?>
<div class="well media-field">
	<?php if (!$field->disabled): ?>
	<button type="button" class="btn btn-danger pull-right reset-field-row" value="<?php echo $fieldName;?>">
		<i class="icon-remove"></i> <?php echo Text::_('COM_REDSHOPB_RESET'); ?>
	</button>
	<?php endif ?>
	<?php if ($href): ?>
		<div class="form-group">
			<div class="control-label">
				<label><?php echo Text::_('COM_REDSHOPB_MEDIA_OPEN_CURRENT_FILE'); ?></label>
			</div>
			<div class="controls">
				<a target="_blank" href="<?php echo $href; ?>">
					<?php echo $field->value; ?>
				</a>
			</div>
		</div>
	<?php endif; ?>
	<div class="form-group">
		<div class="control-label">
			<label for="<?php echo $field->id . 'name';?>">
				<?php echo Text::_('COM_REDSHOPB_NAME'); ?></label>
		</div>
		<div class="controls">
			<input
				id="<?php echo $field->id . 'name';?>"
				name="<?php echo $fieldName . '[name]';?>"
				type="text"
				<?php echo $disabled ?>
				value="<?php echo $field->value; ?>"
				/>
			<input
				id="<?php echo $field->id . 'state';?>"
				name="<?php echo $fieldName . '[state]';?>"
				type="hidden"
				value="1"
				/>
		</div>
	</div>
	<div class="form-group">
		<div class="control-label">
			<label for="<?php echo $field->id . 'description';?>">
				<?php echo Text::_('COM_REDSHOPB_DESCRIPTION'); ?></label>
		</div>
		<div class="controls">
			<input
				id="<?php echo $field->id . 'description';?>"
				name="<?php echo $paramName . '[description]';?>"
				type="text"
				<?php echo $disabled ?>
				value="<?php echo $params->get('description'); ?>"
				/>
		</div>
	</div>
	<div class="form-group">
		<div class="control-label">
			<label for="<?php echo $field->id . 'external_url';?>">
				<?php echo Text::_('COM_REDSHOPB_MEDIA_EXTERNAL_URL'); ?></label>
		</div>
		<div class="controls">
			<input
				id="<?php echo $field->id . 'external_url';?>"
				name="<?php echo $paramName . '[external_url]';?>"
				type="text"
				<?php echo $disabled ?>
				value="<?php echo $params->get('external_url'); ?>"
				/>
		</div>
	</div>
	<div class="form-group">
		<div class="control-label">
			<label for="<?php echo $field->id . 'internal_url';?>">
				<?php echo Text::_('COM_REDSHOPB_MEDIA_INTERNAL_FILE'); ?></label>
		</div>
		<div class="controls">
			<input
				readonly="readonly"
				id="<?php echo $field->id . 'internal_url';?>"
				name="<?php echo $paramName . '[internal_url]';?>"
				type="text"
				<?php echo $disabled ?>
				value="<?php echo $params->get('internal_url'); ?>"
				/>
		</div>
	</div>
	<div class="form-group">
		<div class="control-label">
			<label for="<?php echo $field->id . 'file';?>">
				<?php echo Text::_('COM_REDSHOPB_MEDIA_IMAGE_SELECT_FILE'); ?></label>
		</div>
		<div class="controls">
			<?php echo '<input type="file" name="' . $fieldName . '[file]" id="' . $field->id . 'file"' . $accept
				. $disabled . $class . $size . $onchange . $required . $autofocus . $multiple . ' />'; ?>
		</div>
	</div>
</div>
