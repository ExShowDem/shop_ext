<?php
/**
 * @package     Aesir.E-Commerce.Admin
 * @subpackage  Rsmedia
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Session\Session;

HTMLHelper::_('formbehavior.chosen', 'select');

// Load tooltip instance without HTML support because we have a HTML tag in the tip
HTMLHelper::_('bootstrap.tooltip', '.noHtmlTip', array('html' => false));

$user  = Factory::getUser();
$input = Factory::getApplication()->input;
Factory::getDocument()->addStyleDeclaration(
	'.chzn-container
	{
		display: inline-table !important;
	}'
);
?>
<script type='text/javascript'>
var image_base_path = '<?php echo COM_RSBMEDIA_BASE_RELATIVEPATH; ?>/';
</script>
<form action="index.php?option=com_rsbmedia&amp;asset=<?php echo $input->getCmd('asset');?>&amp;author=<?php echo $input->getCmd('author'); ?>" class="form-vertical" id="imageForm" method="post" enctype="multipart/form-data">
	<div id="messages" style="display: none;">
		<span id="message"></span><?php echo HTMLHelper::_('image', 'media/dots.gif', '...', array('width' => 22, 'height' => 12), true) ?>
	</div>
	<div class="well">
		<div class="row-fluid">
			<div class="span9">
				<div class="row-fluid">
					<div class="span7 control-group">
						<div class="control-label">
							<label class="control-label" for="folder"><?php echo Text::_('COM_RSBMEDIA_DIRECTORY') ?></label>
						</div>
						<div class="controls">
							<?php echo $this->folderList; ?>
							<button class="btn btn-default" type="button" id="upbutton" title="<?php echo Text::_('COM_RSBMEDIA_DIRECTORY_UP') ?>"><?php echo Text::_('COM_RSBMEDIA_UP') ?></button>
						</div>
					</div>
					<div class="span5 control-group">
						<div class="control-label">
							<label for="f_url"><?php echo Text::_('COM_RSBMEDIA_IMAGE_URL') ?></label>
						</div>
						<div class="controls">
							<input type="text" id="f_url" value="" disabled="disabled" />
						</div>
					</div>
				</div>
			</div>
			<div class="span3">
				<div class="pull-right">
					<button class="btn btn-primary" type="button" onclick="<?php if ($this->state->get('field.id')):?>window.parent.jInsertFieldValue(document.id('f_url').value,'<?php echo $this->state->get('field.id');?>');<?php else:?>ImageManager.onok();<?php endif;?>window.parent.SqueezeBox.close();"><?php echo Text::_('COM_RSBMEDIA_INSERT') ?></button>
					<button class="btn" type="button" onclick="window.parent.SqueezeBox.close();"><?php echo Text::_('JCANCEL') ?></button>
				</div>
			</div>
		</div>
	</div>

	<iframe id="imageframe" name="imageframe" src="index.php?option=com_rsbmedia&amp;view=imagesList&amp;tmpl=component&amp;folder=<?php echo $this->state->folder?>&amp;asset=<?php echo $input->getCmd('asset');?>&amp;author=<?php echo $input->getCmd('author');?>"></iframe>

	<?php if (!$this->state->get('field.id')):?>
	<div class="well">
		<div class="row-fluid">
			<div class="span6 control-group">
				<div class="control-label">
					<label title="<?php echo Text::_('COM_RSBMEDIA_ALIGN_DESC'); ?>" class="noHtmlTip" for="f_align"><?php echo Text::_('COM_RSBMEDIA_ALIGN') ?></label>
				</div>
				<div class="controls">
					<select size="1" id="f_align">
						<option value="" selected="selected"><?php echo Text::_('COM_RSBMEDIA_NOT_SET') ?></option>
						<option value="left"><?php echo Text::_('JGLOBAL_LEFT') ?></option>
						<option value="center"><?php echo Text::_('JGLOBAL_CENTER') ?></option>
						<option value="right"><?php echo Text::_('JGLOBAL_RIGHT') ?></option>
					</select>
				</div>
			</div>
			<div class="span6 control-group">
				<div class="control-label">
					<label for="f_alt"><?php echo Text::_('COM_RSBMEDIA_IMAGE_DESCRIPTION') ?></label>
				</div>
				<div class="controls">
					<input type="text" id="f_alt" value="" />
				</div>
			</div>
			<div class="span6 control-group">
				<div class="control-label">
					<label for="f_title"><?php echo Text::_('COM_RSBMEDIA_TITLE') ?></label>
				</div>
				<div class="controls">
					<input type="text" id="f_title" value="" />
				</div>
			</div>
		</div>
		<div class="row-fluid">
			<div class="span6 control-group">
				<div class="control-label">
					<label for="f_caption"><?php echo Text::_('COM_RSBMEDIA_CAPTION') ?></label>
				</div>
				<div class="controls">
					<input type="text" id="f_caption" value="" />
				</div>
			</div>
			<div class="span6 control-group">
				<div class="control-label">
					<label title="<?php echo Text::_('COM_RSBMEDIA_CAPTION_CLASS_DESC'); ?>" class="noHtmlTip" for="f_caption_class"><?php echo Text::_('COM_RSBMEDIA_CAPTION_CLASS_LABEL') ?></label>
				</div>
				<div class="controls">
					<input type="text" list="d_caption_class" id="f_caption_class" value="" />
					<datalist id="d_caption_class">
						<option value="text-left">
						<option value="text-center">
						<option value="text-right">
					</datalist>
				</div>
			</div>
		</div>
	<?php endif;?>

		<input type="hidden" id="f_url" value="" />
		<input type="hidden" id="dirPath" name="dirPath" />
		<input type="hidden" id="f_file" name="f_file" />
		<input type="hidden" id="tmpl" name="component" />

	</div>
</form>

<form action="<?php echo Uri::base(); ?>index.php?option=com_rsbmedia&amp;task=file.upload&amp;tmpl=component&amp;<?php echo $this->session->getName() . '=' . $this->session->getId(); ?>&amp;<?php echo Session::getFormToken();?>=1&amp;asset=<?php echo $input->getCmd('asset');?>&amp;author=<?php echo $input->getCmd('author');?>&amp;view=images" id="uploadForm" class="form-horizontal" name="uploadForm" method="post" enctype="multipart/form-data">
	<div id="uploadform" class="well">
		<fieldset id="upload-noflash" class="actions">
			<div class="control-group">
				<div class="control-label">
					<label for="upload-file" class="control-label"><?php echo Text::_('COM_RSBMEDIA_UPLOAD_FILE'); ?></label>
				</div>
				<div class="controls">
					<input type="file" id="upload-file" name="Filedata[]" multiple /><button class="btn btn-primary" id="upload-submit"><i class="icon-upload icon-white"></i> <?php echo Text::_('COM_RSBMEDIA_START_UPLOAD'); ?></button>
					<p class="help-block"><?php echo $this->config->get('upload_maxsize') == '0' ? Text::_('COM_RSBMEDIA_UPLOAD_FILES_NOLIMIT') : Text::sprintf('COM_RSBMEDIA_UPLOAD_FILES', $this->config->get('upload_maxsize')); ?></p>
				</div>
			</div>
		</fieldset>
		<input type="hidden" name="return-url" value="<?php echo base64_encode('index.php?option=com_rsbmedia&view=images&tmpl=component&fieldid=' . $input->getCmd('fieldid', '') . '&e_name=' . $input->getCmd('e_name') . '&asset=' . $input->getCmd('asset') . '&author=' . $input->getCmd('author')); ?>" />
	</div>
</form>
