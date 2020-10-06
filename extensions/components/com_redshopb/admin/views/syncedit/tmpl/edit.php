<?php
/**
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Views
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

$action = RedshopbRoute::_('index.php?option=com_redshopb&view=syncedit');

// HTML helpers
HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('behavior.tabstate');
HTMLHelper::_('vnrbootstrap.tooltip');
HTMLHelper::_('rjquery.chosen', 'select');
?>
<form action="<?php echo $action; ?>" method="post" name="adminForm" id="adminForm"
	  class="form-validate form-horizontal">
	<?php echo HTMLHelper::_('bootstrap.startTabSet', 'myTab', array('active' => 'general')); ?>
	<?php echo HTMLHelper::_('bootstrap.addTab', 'myTab', 'general', Text::_('COM_REDSHOPB_SYNC_BASIC', true)); ?>
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
			<?php echo $this->form->getLabel('plugin'); ?>
		</div>
		<div class="controls">
			<?php echo $this->form->getInput('plugin'); ?>
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
			<?php echo $this->form->getLabel('state'); ?>
		</div>
		<div class="controls">
			<?php echo $this->form->getInput('state'); ?>
		</div>
	</div>
	<div class="form-group">
		<div class="control-label">
			<?php echo $this->form->getLabel('execute_sync'); ?>
		</div>
		<div class="controls">
			<?php echo $this->form->getInput('execute_sync'); ?>
		</div>
	</div>
	<div class="form-group">
		<div class="control-label">
			<?php echo $this->form->getLabel('mask_time'); ?>
		</div>
		<div class="controls">
			<?php echo $this->form->getInput('mask_time'); ?>
		</div>
	</div>
	<div class="form-group">
		<div class="control-label">
			<?php echo $this->form->getLabel('offset_time'); ?>
		</div>
		<div class="controls">
			<?php echo $this->form->getInput('offset_time'); ?>
		</div>
	</div>
	<div class="form-group">
		<div class="control-label">
			<?php echo $this->form->getLabel('mute_from'); ?>
		</div>
		<div class="controls">
			<?php echo $this->form->getInput('mute_from'); ?>
		</div>
	</div>
	<div class="form-group">
		<div class="control-label">
			<?php echo $this->form->getLabel('mute_to'); ?>
		</div>
		<div class="controls">
			<?php echo $this->form->getInput('mute_to'); ?>
		</div>
	</div>
	<div class="form-group">
		<div class="control-label">
			<?php echo $this->form->getLabel('is_continuous'); ?>
		</div>
		<div class="controls">
			<?php echo $this->form->getInput('is_continuous'); ?>
		</div>
	</div>
	<div class="form-group">
		<div class="control-label">
			<?php echo $this->form->getLabel('items_process_step'); ?>
		</div>
		<div class="controls">
			<?php echo $this->form->getInput('items_process_step'); ?>
		</div>
	</div>
	<div class="form-group">
		<div class="control-label">
			<?php echo $this->form->getLabel('items_processed'); ?>
		</div>
		<div class="controls">
			<?php echo $this->form->getInput('items_processed'); ?>
		</div>
	</div>
	<div class="form-group">
		<div class="control-label">
			<?php echo $this->form->getLabel('items_total'); ?>
		</div>
		<div class="controls">
			<?php echo $this->form->getInput('items_total'); ?>
		</div>
	</div>
	<div class="form-group">
		<div class="control-label">
			<?php echo $this->form->getLabel('last_status_messages'); ?>
		</div>
		<div class="controls">
			<?php echo $this->form->getInput('last_status_messages'); ?>
		</div>
	</div>
	<!-- hidden fields -->
	<input type="hidden" name="option" value="com_redshopb">
	<input type="hidden" name="id" value="<?php echo $this->item->id; ?>">
	<input type="hidden" name="task" value="">
	<?php echo HTMLHelper::_('form.token'); ?>
	<?php echo HTMLHelper::_('bootstrap.endTab'); ?>
	<?php echo RedshopbLayoutHelper::render('joomla.edit.params', $this); ?>
	<?php echo HTMLHelper::_('bootstrap.endTabSet'); ?>
</form>
