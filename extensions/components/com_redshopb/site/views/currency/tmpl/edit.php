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

$action = RedshopbRoute::_('index.php?option=com_redshopb&view=currency');

// HTML helpers
HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('vnrbootstrap.tooltip');
HTMLHelper::_('rjquery.chosen', 'select');
?>
<div class="redshopb-currency">
	<form action="<?php echo $action; ?>" method="post" name="adminForm" id="adminForm" class="form-validate form-horizontal redshopb-currency-form">
		<div class="row">
			<div class="col-md-6">
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
						<?php echo $this->form->getLabel('alpha3'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('alpha3'); ?>
					</div>
				</div>
				<div class="form-group">
					<div class="control-label">
						<?php echo $this->form->getLabel('numeric'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('numeric'); ?>
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
						<?php echo $this->form->getLabel('id'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('id'); ?>
					</div>
				</div>
			</div>

			<div class="col-md-6">
				<div class="form-group">
					<div class="control-label">
						<?php echo $this->form->getLabel('decimals'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('decimals'); ?>
					</div>
				</div>
				<div class="form-group">
					<div class="control-label">
						<?php echo $this->form->getLabel('symbol'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('symbol'); ?>
					</div>
				</div>
				<div class="form-group">
					<div class="control-label">
						<?php echo $this->form->getLabel('symbol_position'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('symbol_position'); ?>
					</div>
				</div>
				<div class="form-group">
					<div class="control-label">
						<?php echo $this->form->getLabel('blank_space'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('blank_space'); ?>
					</div>
				</div>
				<div class="form-group">
					<div class="control-label">
						<?php echo $this->form->getLabel('decimal_separator'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('decimal_separator'); ?>
					</div>
				</div>
				<div class="form-group">
					<div class="control-label">
						<?php echo $this->form->getLabel('thousands_separator'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('thousands_separator'); ?>
					</div>
				</div>
			</div>
		</div>

		<!-- hidden fields -->
		<input type="hidden" name="option" value="com_redshopb">
		<input type="hidden" name="id" value="<?php echo $this->item->id; ?>">
		<input type="hidden" name="task" value="">
		<?php echo HTMLHelper::_('form.token'); ?>
	</form>
</div>
