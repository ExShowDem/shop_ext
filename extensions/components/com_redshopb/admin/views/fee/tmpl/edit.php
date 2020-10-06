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

$action = 'index.php?option=com_redshopb&view=fee';

// HTML helpers
HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('vnrbootstrap.tooltip');
HTMLHelper::_('rjquery.chosen', 'select');
$isNew = (int) $this->item->id <= 0;
?>
<form action="<?php echo $action; ?>" method="post" name="adminForm" id="adminForm"
	  class="form-validate form-horizontal">
	<div class="form-group">
		<div class="control-label">
			<?php echo $this->form->getLabel('product_id'); ?>
		</div>
		<div class="controls">
			<?php echo $this->form->getInput('product_id'); ?>
		</div>
	</div>
	<div class="form-group">
		<div class="control-label">
			<?php echo $this->form->getLabel('currency_id'); ?>
		</div>
		<div class="controls">
			<?php echo $this->form->getInput('currency_id'); ?>
		</div>
	</div>
	<div class="form-group">
		<div class="control-label">
			<?php echo $this->form->getLabel('fee_limit'); ?>
		</div>
		<div class="controls">
			<?php echo $this->form->getInput('fee_limit'); ?>
		</div>
	</div>
	<div class="form-group">
		<div class="control-label">
			<?php echo $this->form->getLabel('fee_amount'); ?>
		</div>
		<div class="controls">
			<?php echo $this->form->getInput('fee_amount'); ?>
		</div>
	</div>
	<!-- hidden fields -->
	<input type="hidden" name="option" value="com_redshopb">
	<input type="hidden" name="id" value="<?php echo $this->item->id; ?>">
	<input type="hidden" name="task" value="">
	<?php echo HTMLHelper::_('form.token'); ?>
</form>
