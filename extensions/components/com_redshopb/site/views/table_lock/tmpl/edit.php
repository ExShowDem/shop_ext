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
HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('rjquery.framework');
HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('vnrbootstrap.tooltip');
HTMLHelper::_('rjquery.chosen', 'select');

$action = RedshopbRoute::_('index.php?option=com_redshopb&view=table_lock&layout=edit');
$isNew  = (int) $this->item->id <= 0;

echo RedshopbBrowserBreadcrumbs::renderBreadcrumbs();
?>
<div class="redshopb-table_lock">
	<div class="tab-content">
		<form action="<?php echo $action; ?>" method="post" name="adminForm" id="adminForm"
			  class="form-validate form-horizontal redshopb-table_lock-form" enctype="multipart/form-data">
			<div class="tab-pane active" id="details">
				<div class="col-md-12 adapt-inputs">
					<div class="form-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('table_name'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('table_name'); ?>
						</div>
					</div>
					<div class="form-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('table_id'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('table_id'); ?>
						</div>
					</div>
					<div class="form-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('column_name'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('column_name'); ?>
						</div>
					</div>
					<div class="form-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('locked_date'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('locked_date'); ?>
						</div>
					</div>
					<div class="form-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('locked_by'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('locked_by'); ?>
						</div>
					</div>
					<div class="form-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('locked_method'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('locked_method'); ?>
						</div>
					</div>
				</div>

				<!-- hidden fields -->
				<input type="hidden" name="option" value="com_redshopb">
				<input type="hidden" name="id" value="<?php echo $this->item->id ?>">
				<input type="hidden" name="task" value="">
				<?php echo HTMLHelper::_('form.token'); ?>
			</div>
		</form>
	</div>
</div>
