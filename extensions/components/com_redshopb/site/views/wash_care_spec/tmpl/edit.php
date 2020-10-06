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

// HTML helpers
HTMLHelper::_('rjquery.framework');
HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('vnrbootstrap.tooltip');
HTMLHelper::_('rjquery.chosen', 'select');

$action = RedshopbRoute::_('index.php?option=com_redshopb&view=wash_care_spec');
$isNew  = (int) $this->item->id <= 0;

echo RedshopbBrowserBreadcrumbs::renderBreadcrumbs();
?>
<div class="redshopb-wash_care_spec">
	<?php if ($this->item->id) : ?>

	<?php endif; ?>
	<form action="<?php echo $action; ?>" method="post" name="adminForm" id="adminForm" class="form-validate form-horizontal redshopb-wash_care_spec-form" enctype="multipart/form-data">
		<div class="row">
			<div class="form-group">
				<div class="control-label">
					<?php echo $this->form->getLabel('type_code'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('type_code'); ?>
				</div>
			</div>
			<div class="form-group">
				<div class="control-label">
					<?php echo $this->form->getLabel('code'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('code'); ?>
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
				</div>
				<div class="controls">
				<?php if ($isNew): ?>
					<input type="text" value="<?php echo Text::_('COM_REDSHOPB_WASH_CARE_SPEC_FOR_UPLOAD_IMAGE_SAVE_ITEM_FIRST'); ?>" disabled="disabled" class="disabled" />
				<?php else: ?>
					<?php echo $this->form->getInput('imageFileUpload'); ?>

				<?php if ($this->item->image):
						$imagePath = RedshopbHelperThumbnail::originalToResize($this->item->image, 150, 80, 100, 0, 'wash_care_spec');

					if ($imagePath !== false)
						{
						echo '<img src="' . $imagePath . '" />';
					} ?>
				<?php endif;
				endif; ?>
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
					<?php echo $this->form->getLabel('description'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('description'); ?>
				</div>
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
