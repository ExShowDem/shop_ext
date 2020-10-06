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

$action = RedshopbRoute::_('index.php?option=com_redshopb&view=country');

// HTML helpers
HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('vnrbootstrap.tooltip');
HTMLHelper::_('rjquery.chosen', 'select');

if ($this->item->id > 0)
{
	$this->form->setFieldAttribute('name', 'readonly', 'true');
}

echo RedshopbBrowserBreadcrumbs::renderBreadcrumbs();

?>
<div class="redshopb-country">
	<form action="<?php echo $action; ?>" method="post" name="adminForm" id="adminForm" class="form-validate form-horizontal redshopb-country-form">
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
				<?php echo $this->form->getLabel('alpha2'); ?>
			</div>
			<div class="controls">
				<?php echo $this->form->getInput('alpha2'); ?>
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
				<?php echo $this->form->getLabel('eu_zone'); ?>
			</div>
			<div class="controls">
				<?php echo $this->form->getInput('eu_zone'); ?>
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

		<!-- hidden fields -->
		<input type="hidden" name="option" value="com_redshopb">
		<input type="hidden" name="id" value="<?php echo $this->item->id; ?>">
		<input type="hidden" name="task" value="">
		<?php echo HTMLHelper::_('form.token'); ?>
	</form>
</div>
