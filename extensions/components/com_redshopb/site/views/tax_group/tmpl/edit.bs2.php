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

$action = RedshopbRoute::_('index.php?option=com_redshopb&view=tax_group');

// HTML helpers
HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('vnrbootstrap.tooltip');
HTMLHelper::_('rjquery.chosen', 'select');

echo RedshopbBrowserBreadcrumbs::renderBreadcrumbs();
?>
<div class="redshopb-tax_group">
	<form action="<?php echo $action; ?>" method="post" name="adminForm" id="adminForm" class="form-validate form-horizontal redshopb-tax_group-form">
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
				<?php echo $this->form->getLabel('company_id'); ?>
			</div>
			<div class="controls">
				<?php echo $this->form->getInput('company_id'); ?>
			</div>
		</div>
		<div class="control-group">
			<div class="control-label">
				<?php echo $this->form->getLabel('state'); ?>
			</div>
			<div class="controls">
				<?php echo $this->form->getInput('state'); ?>
			</div>
		</div>
		<div class="control-group">
			<div class="control-label">
				<?php echo $this->form->getLabel('taxes'); ?>
			</div>
			<div class="controls">
				<?php echo $this->form->getInput('taxes'); ?>
			</div>
		</div>

		<!-- hidden fields -->
		<input type="hidden" name="option" value="com_redshopb">
		<input type="hidden" name="id" value="<?php echo $this->item->id; ?>">
		<input type="hidden" name="task" value="">
		<?php echo HTMLHelper::_('form.token'); ?>
	</form>
</div>
