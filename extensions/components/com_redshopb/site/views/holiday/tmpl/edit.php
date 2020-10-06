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

$action = RedshopbRoute::_('index.php?option=com_redshopb&view=holiday');

// HTML helpers
HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('rbootstrap.tooltip');
HTMLHelper::_('rjquery.chosen', 'select');

echo RedshopbBrowserBreadcrumbs::renderBreadcrumbs();
?>
<div class="redshopb-holiday">
	<form action="<?php echo $action; ?>" method="post" name="adminForm" id="adminForm" class="form-validate form-horizontal redshopb-holiday-form">
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
				<?php echo $this->form->getLabel('day'); ?>
			</div>
			<div class="controls">
				<?php echo $this->form->getInput('day'); ?>
			</div>
		</div>
		<div class="form-group">
			<div class="control-label">
				<?php echo $this->form->getLabel('month'); ?>
			</div>
			<div class="controls">
				<?php echo $this->form->getInput('month'); ?>
			</div>
		</div>
		<div class="form-group">
			<div class="control-label">
				<?php echo $this->form->getLabel('year'); ?>
			</div>
			<div class="controls">
				<?php echo $this->form->getInput('year'); ?>
			</div>
		</div>
		<div class="form-group">
			<div class="control-label">
				<?php echo $this->form->getLabel('country_id'); ?>
			</div>
			<div class="controls">
				<?php echo $this->form->getInput('country_id'); ?>
			</div>
		</div>

		<!-- hidden fields -->
		<input type="hidden" name="option" value="com_redshopb">
		<input type="hidden" name="id" value="<?php echo $this->item->id; ?>">
		<input type="hidden" name="task" value="">
		<?php echo HTMLHelper::_('form.token'); ?>
	</form>
</div>
