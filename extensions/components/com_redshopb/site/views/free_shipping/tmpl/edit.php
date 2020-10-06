<?php
/**
 * @package     Redshopb.Frontend
 * @subpackage  Views
 *
 * @copyright   Copyright (C) 2012 - 2016 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;

$action = RedshopbRoute::_('index.php?option=com_redshopb&view=free_shipping');

// HTML helpers
HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('rbootstrap.tooltip');
HTMLHelper::_('rjquery.chosen', 'select');
?>
<div class="redshopb-free_shipping">
	<form action="<?php echo $action; ?>" method="post" name="adminForm" id="adminForm" class="form-validate form-horizontal redshopb-free_shipping-form">
		<div class="control-group">
			<div class="control-label">
				<?php echo $this->form->getLabel('product_discount_group_id'); ?>
			</div>
			<div class="controls">
				<?php echo $this->form->getInput('product_discount_group_id'); ?>
			</div>
		</div>
		<div class="control-group">
			<div class="control-label">
				<?php echo $this->form->getLabel('category_id'); ?>
			</div>
			<div class="controls">
				<?php echo $this->form->getInput('category_id'); ?>
			</div>
		</div>
		<div class="control-group">
			<div class="control-label">
				<?php echo $this->form->getLabel('threshold_expenditure'); ?>
			</div>
			<div class="controls">
				<?php echo $this->form->getInput('threshold_expenditure'); ?>
			</div>
		</div>

		<!-- hidden fields -->
		<input type="hidden" name="option" value="com_redshopb">
		<input type="hidden" name="id" value="<?php echo $this->item->id; ?>">
		<input type="hidden" name="task" value="">
		<?php echo HTMLHelper::_('form.token'); ?>
	</form>
</div>
