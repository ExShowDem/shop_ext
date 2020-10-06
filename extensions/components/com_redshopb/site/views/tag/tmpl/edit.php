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

// HTML helpers
HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('vnrbootstrap.tooltip');
HTMLHelper::_('rjquery.chosen', 'select');

$action    = RedshopbRoute::_('index.php?option=com_redshopb&view=tag');
$isNew     = (int) $this->item->id <= 0;
$imagePath = RedshopbHelperThumbnail::originalToResize($this->item->image, 150, 80, 100, 0, 'tags');

echo RedshopbBrowserBreadcrumbs::renderBreadcrumbs();
?>
<div class="redshopb-tag">
	<form action="<?php echo $action; ?>" method="post" name="adminForm" id="adminForm" class="form-validate form-horizontal redshopb-tag-form" enctype="multipart/form-data">
		<div class="form-group">
			<div class="control-label">
				<?php echo $this->form->getLabel('name'); ?>
			</div>
			<div class="controls">
				<?php echo $this->form->getInput('name'); ?>
			</div>
		</div>
		<div class="form-group">
			<div class="control-label">
				<?php echo $this->form->getLabel('type'); ?>
			</div>
			<div class="controls">
				<?php echo $this->form->getInput('type'); ?>
			</div>
		</div>
		<div class="form-group">
			<div class="control-label">
				<?php echo $this->form->getLabel('state'); ?>
			</div>
			<div class="controls">
				<?php echo $this->form->getInput('state'); ?>
			</div>
		</div>
		<div class="form-group">
			<div class="control-label">
				<?php echo $this->form->getLabel('parent_id'); ?>
			</div>
			<div class="controls">
				<?php echo $this->form->getInput('parent_id'); ?>
			</div>
		</div>
		<div class="form-group">
			<div class="control-label">
				<?php echo $this->form->getLabel('company_id'); ?>
			</div>
			<div class="controls">
				<?php echo $this->form->getInput('company_id'); ?>
			</div>
		</div>
		<?php if ($this->item->image): ?>
			<div class="form-group">
				<div class="control-label">
					<?php echo $this->form->getLabel('deleteImage'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('deleteImage'); ?>
				</div>
			</div>
		<?php endif; ?>
		<div class="form-group">
			<div class="control-label">
				<?php echo $this->form->getLabel('imageFileUpload'); ?>
				<?php echo $this->form->getBackWSValueButton('image'); ?>
			</div>
			<div class="controls">
				<?php echo $this->form->getInput('imageFileUpload'); ?>

				<?php if (!empty($imagePath)) :?>
					<img src="<?php echo $imagePath; ?>" />
				<?php endif;?>
			</div>
		</div>
		<!-- hidden fields -->
		<?php echo $this->form->getInput('image'); ?>
		<input type="hidden" name="option" value="com_redshopb">
		<input type="hidden" name="id" value="<?php echo $this->item->id; ?>">
		<input type="hidden" name="task" value="">
		<?php echo HTMLHelper::_('form.token'); ?>
	</form>
</div>
