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
HTMLHelper::_('rjquery.framework');
HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('vnrbootstrap.tooltip');
HTMLHelper::_('rjquery.chosen', 'select');
$fromProductView = RedshopbInput::isFromProduct();
$productId       = RedshopbInput::getProductIdForm();

$action = RedshopbRoute::_('index.php?option=com_redshopb&view=wash_care_spec_xref');
$isNew  = (int) $this->item->id <= 0;

if ($isNew && $productId)
{
	$this->form->setValue('product_id', null, $productId);
}

echo RedshopbBrowserBreadcrumbs::renderBreadcrumbs();
?>
<style>
	#jform_wash_care_spec_id_chzn
	{
		width: 400px !important;
	}
</style>

<div class="redshopb-product_wash_care_spec_xref">
	<form action="<?php echo $action; ?>" method="post" name="adminForm" id="adminForm" class="form-validate form-horizontal redshopb-product_wash_care_spec_xref-form">
		<div class="row-fluid">
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
					<?php echo $this->form->getLabel('wash_care_spec_id'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('wash_care_spec_id'); ?>
				</div>
			</div>
		</div>

		<!-- hidden fields -->
		<input type="hidden" name="option" value="com_redshopb">
		<input type="hidden" name="id" value="<?php echo $this->item->id; ?>">

		<?php if ($fromProductView && $productId) : ?>
		<input type="hidden" name="from_product" value="<?php echo $fromProductView ?>">
		<input type="hidden" name="jform[product_id]" value="<?php echo $productId; ?>">
		<?php endif; ?>
		<input type="hidden" name="task" value="">
		<?php echo HTMLHelper::_('form.token'); ?>
	</form>
</div>
