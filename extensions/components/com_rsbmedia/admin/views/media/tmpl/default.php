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

$user  = Factory::getUser();
$input = Factory::getApplication()->input;
?>
<div class="row-fluid">
	<!-- Begin Sidebar -->
	<div class="span2">
		<div id="treeview">
			<div id="media-tree_tree" class="sidebar-nav">
				<?php echo $this->loadTemplate('folders'); ?>
			</div>
		</div>
	</div>
	<style>
		.overall-progress,
		.current-progress {
			width: 150px;
		}
	</style>
	<!-- End Sidebar -->
	<!-- Begin Content -->
	<div class="span10">
		<?php echo $this->loadTemplate('navigation'); ?>
		<?php if ($this->require_ftp) : ?>
			<form action="index.php?option=com_rsbmedia&amp;task=ftpValidate" name="ftpForm" id="ftpForm" method="post">
				<fieldset title="<?php echo Text::_('COM_RSBMEDIA_DESCFTPTITLE'); ?>">
					<legend><?php echo Text::_('COM_RSBMEDIA_DESCFTPTITLE'); ?></legend>
					<?php echo Text::_('COM_RSBMEDIA_DESCFTP'); ?>
					<label for="username"><?php echo Text::_('JGLOBAL_USERNAME'); ?></label>
					<input type="text" id="username" name="username" class="inputbox" size="70" value="" />

					<label for="password"><?php echo Text::_('JGLOBAL_PASSWORD'); ?></label>
					<input type="password" id="password" name="password" class="inputbox" size="70" value="" />
				</fieldset>
			</form>
		<?php endif; ?>

		<form action="index.php?option=com_rsbmedia" name="adminForm" id="mediamanager-form" method="post" enctype="multipart/form-data" >
			<input type="hidden" name="task" value="" />
			<input type="hidden" name="cb1" id="cb1" value="0" />
			<input class="update-folder" type="hidden" name="folder" id="folder" value="<?php echo $this->state->folder; ?>" />
		</form>

		<!-- File Upload Form -->
		<div id="collapseUpload" class="collapse">
			<form action="<?php echo Uri::base(); ?>index.php?option=com_rsbmedia&amp;task=file.upload&amp;tmpl=component&amp;<?php echo $this->session->getName().'='.$this->session->getId(); ?>&amp;<?php echo Session::getFormToken();?>=1&amp;format=html" id="uploadForm" class="form-inline" name="uploadForm" method="post" enctype="multipart/form-data">
				<div id="uploadform">
					<fieldset id="upload-noflash" class="actions">
							<label for="upload-file" class="control-label"><?php echo Text::_('COM_RSBMEDIA_UPLOAD_FILE'); ?></label>
								<input type="file" id="upload-file" name="Filedata[]" multiple /> <button class="btn btn-primary" id="upload-submit"><i class="icon-upload icon-white"></i> <?php echo Text::_('COM_RSBMEDIA_START_UPLOAD'); ?></button>
								<p class="help-block"><?php echo $this->config->get('upload_maxsize') == '0' ? Text::_('COM_RSBMEDIA_UPLOAD_FILES_NOLIMIT') : Text::sprintf('COM_RSBMEDIA_UPLOAD_FILES', $this->config->get('upload_maxsize')); ?></p>
					</fieldset>
					<input class="update-folder" type="hidden" name="folder" id="folder" value="<?php echo $this->state->folder; ?>" />
					<input type="hidden" name="return-url" value="<?php echo base64_encode('index.php?option=com_rsbmedia'); ?>" />
				</div>
			</form>
		</div>
		<div id="collapseFolder" class="collapse">
			<form action="index.php?option=com_rsbmedia&amp;task=folder.create&amp;tmpl=<?php echo $input->getCmd('tmpl', 'index');?>" name="folderForm" id="folderForm" class="form-inline" method="post">
					<div class="path">
						<input class="inputbox" type="text" id="folderpath" readonly="readonly" />
						<input class="inputbox" type="text" id="foldername" name="foldername"  />
						<input class="update-folder" type="hidden" name="folderbase" id="folderbase" value="<?php echo $this->state->folder; ?>" />
						<button type="submit" class="btn"><i class="icon-folder-open"></i> <?php echo Text::_('COM_RSBMEDIA_CREATE_FOLDER'); ?></button>
					</div>
					<?php echo HTMLHelper::_('form.token'); ?>
			</form>
		</div>

		<form action="index.php?option=com_rsbmedia&amp;task=folder.create&amp;tmpl=<?php echo $input->getCmd('tmpl', 'index');?>" name="folderForm" id="folderForm" method="post">
			<div id="folderview">
				<div class="view">
					<iframe class="thumbnail" src="index.php?option=com_rsbmedia&amp;view=mediaList&amp;tmpl=component&amp;folder=<?php echo $this->state->folder;?>" id="folderframe" name="folderframe" width="100%" height="500px" marginwidth="0" marginheight="0" scrolling="auto"></iframe>
				</div>
				<?php echo HTMLHelper::_('form.token'); ?>
			</div>
		</form>
	</div>
	<!-- End Content -->
</div>
