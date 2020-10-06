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

$action = RedshopbRoute::_('index.php?option=com_redshopb&view=shipping_rate');
$isNew  = (int) $this->item->id <= 0;

// HTML helpers
HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('vnrbootstrap.tooltip');
HTMLHelper::_('rjquery.chosen', 'select');
HTMLHelper::_('behavior.formvalidator');

echo RedshopbBrowserBreadcrumbs::renderBreadcrumbs();

if (!$isNew)
{
	$this->form->setFieldAttribute('shipping_configuration_id', 'readonly', 'true');
}
?>
<script type="text/javascript">
	function changeToShipper(obj, shipperList, autoCalcList)
	{
		var shipperSelected  = false;
		var autoCalcSelected = false;

		for(var i = 0; i < shipperList.length; i++)
		{
			if(shipperList[i] == obj.value)
			{
				shipperSelected = true;
			}
		}

		for(var i = 0; i < autoCalcList.length; i++)
		{
			if(autoCalcList[i] == obj.value)
			{
				autoCalcSelected = true;
			}
		}

		if (shipperSelected)
		{
			jQuery('.is-shipper').show();
			jQuery('.is-non-shipper').hide();
			jQuery('[name="is_shipper"]').val('1');
		}
		else
		{
			jQuery('.is-shipper').hide();
			jQuery('.is-non-shipper').show();
			jQuery('[name="is_shipper"]').val('0');
		}

		if (autoCalcSelected)
		{
			jQuery('.is-shipper').hide();
			jQuery('.is-non-shipper').hide();
		}
	}

	Joomla.submitbutton = function(task)
	{
		if (task == "shipping_rate.cancel" || document.formvalidator.isValid(document.getElementById("adminForm")))
		{
			<?php echo $this->form->getField('shipping_location_info')->save() ?>
			Joomla.submitform(task, document.getElementById("adminForm"));
		}
	};

	(function ($) {
		$(document).ready(function () {
			$('#jform_shipping_configuration_id').change();
		});
	})(jQuery);
</script>
<div class="redshopb-shipping_rate">
	<form
		action="<?php echo $action; ?>"
		method="post"
		name="adminForm"
		id="adminForm"
		class="form-validate form-horizontal redshopb-shipping_rate-form">
		<div class="row form-horizontal-desktop">
			<div class="col-md-6">
				<?php echo $this->form->renderField('shipping_configuration_id'); ?>
				<?php echo $this->form->renderField('name'); ?>
				<div class="is-shipper">
					<?php echo $this->form->renderField('countries'); ?>
					<?php echo $this->form->renderField('on_product'); ?>
					<?php echo $this->form->renderField('on_product_discount_group'); ?>
					<?php echo $this->form->renderField('on_category'); ?>
					<?php echo $this->form->renderField('priority'); ?>
					<?php echo $this->form->renderField('price'); ?>
					<?php echo $this->form->renderField('state'); ?>
				</div>
			</div>
			<div class="col-md-6 is-shipper">
				<?php echo $this->form->renderField('zip_start'); ?>
				<?php echo $this->form->renderField('zip_end'); ?>
				<?php echo $this->form->renderField('weight_start'); ?>
				<?php echo $this->form->renderField('weight_end'); ?>
				<?php echo $this->form->renderField('volume_start'); ?>
				<?php echo $this->form->renderField('volume_end'); ?>
				<?php echo $this->form->renderField('length_start'); ?>
				<?php echo $this->form->renderField('length_end'); ?>
				<?php echo $this->form->renderField('width_start'); ?>
				<?php echo $this->form->renderField('width_end'); ?>
				<?php echo $this->form->renderField('height_start'); ?>
				<?php echo $this->form->renderField('height_end'); ?>
				<?php echo $this->form->renderField('order_total_start'); ?>
				<?php echo $this->form->renderField('order_total_end'); ?>
			</div>
		</div>
		<div class="row form-horizontal-desktop is-non-shipper">
			<div class="col-md-12">
				<?php echo $this->form->renderField('shipping_location_info'); ?>
			</div>
		</div>
		<!-- hidden fields -->
		<input type="hidden" name="option" value="com_redshopb">
		<input type="hidden" name="id" value="<?php echo $this->item->id; ?>">
		<input type="hidden" name="is_shipper" value="0">
		<input type="hidden" name="task" value="">
		<?php echo HTMLHelper::_('form.token'); ?>
	</form>
</div>
