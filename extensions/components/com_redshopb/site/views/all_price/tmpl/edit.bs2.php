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
use Joomla\CMS\Factory;


$url = 'index.php?option=com_redshopb&view=all_price';


if (!empty($this->productId))
{
	$url .= '&product_id=' . (int) $this->productId;
}

if (!empty($this->productItemId))
{
	$url .= '&product_item_id=' . (int) $this->productItemId;
}

if (!empty($this->type))
{
	$url .= '&type=' . $this->type;
}

$return = Factory::getApplication()->input->getBase64('return', null);

if ($return)
{
	$url .= '&return=' . $return;
}

$action = RedshopbRoute::_($url);

// HTML helpers
HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('vnrbootstrap.tooltip');
HTMLHelper::_('rjquery.chosen', 'select');
$fromProductView = RedshopbInput::isFromProduct();
$productId       = $this->productId;
$productItemId   = $this->productItemId;
$isNew           = $this->isNew;
$startDate       = null;
$endDate         = null;
$user            = Factory::getUser();
$config          = Factory::getConfig();
$timezone        = $user->getParam('timezone', $config->get('offset'));

if ($this->item != null)
{
	$startDateValue = $this->form->getValue('starting_date');
	$endDateValue   = $this->form->getValue('ending_date');

	if ($startDateValue != '0000-00-00 00:00:00' && !is_null($startDateValue))
	{
		$startDate = $startDateValue;
		$date      = Factory::getDate($startDate, 'UTC');
		$date->setTimezone(new DateTimeZone($timezone));
		$startDate = $date->format('Y-m-d H:i:s', true, false);
	}

	if ($endDateValue != '0000-00-00 00:00:00' && !is_null($endDateValue))
	{
		$endDate = $endDateValue;
		$date    = Factory::getDate($endDate, 'UTC');
		$date->setTimezone(new DateTimeZone($timezone));
		$endDate = $date->format('Y-m-d H:i:s', true, false);
	}
}

echo RedshopbBrowserBreadcrumbs::renderBreadcrumbs();
?>
<script type="text/javascript">
	(function ($) {
		$(document).ready(function () {
			var start = $('#jform_starting_date');

			<?php if (!is_null($startDate)) : ?>
			var startDate = '<?php echo $startDate; ?>';
			<?php endif; ?>

			<?php if (!is_null($endDate)) : ?>
			var endDate = '<?php echo $endDate; ?>';
			<?php endif; ?>

			var end = $('#jform_ending_date');
			start.datepicker('option',
				{
					"changeMonth": true,
					"changeYear": true,
					"dateFormat": 'yy-mm-dd',
					onClose: function (selectedDate) {
						$("#jform_ending_date").datepicker("option", "minDate", selectedDate);
					}
				}
			);
			end.datepicker('option',
				{
					"changeMonth": true,
					"changeYear": true,
					"dateFormat": 'yy-mm-dd',
					onClose: function (selectedDate) {
						$("#jform_start_date").datepicker("option", "maxDate", selectedDate);
					}
				}
			);
			<?php if (!is_null($startDate)) : ?>
			start.datepicker('setDate', startDate);
			end.datepicker("option", "minDate", startDate);
			<?php endif; ?>

			<?php if (!is_null($endDate)) : ?>
			end.datepicker('setDate', endDate);
			start.datepicker("option", "maxDate", endDate);
			<?php endif; ?>
		});
	})(jQuery);
</script>
<script type="text/javascript">
	(function ($) {
		$(document).ready(function () {
			$('#adminForm')
				.on('change', '#jform_type', function () {
					$('.type_id_class').addClass('hide');
					$('.' + $(this).val() + '_type_id_class').removeClass('hide');
				})
				.on('change', '#jform_sales_type', function () {
					$('.sales_id_class').addClass('hide');
					$('.' + $(this).val() + '_sales_id_class').removeClass('hide');
				});

			multipleOfChange($("#jform_is_multiple"));
		});
	})(jQuery);
</script>
<script type="text/javascript">
	function multipleOfChange(object)
	{
		(function ($) {
			if ($(object).is(':checked')) {
				$("#jform_quantity_max").attr("disabled", true);
			} else {
				$("#jform_quantity_max").attr("disabled", false);
			}
		})(jQuery);
	}
</script>
<div class="redshopb-all_price">
	<form action="<?php echo $action; ?>" method="post" name="adminForm" id="adminForm" class="form-validate form-horizontal">
		<div class="control-group">
			<div class="control-label">
				<?php echo $this->form->getLabel('type'); ?>
			</div>
			<div class="controls">
				<?php echo $this->form->getInput('type'); ?>
			</div>
		</div>

		<div class="control-group product_type_id_class product_item_type_id_class type_id_class<?php
		echo ($this->form->getValue('type') != 'product' && $this->form->getValue('type') != 'product_item') ? ' hide' : ''; ?>">
			<div class="control-label">
				<?php echo $this->form->getLabel('type_product_id'); ?>
			</div>
			<div class="controls">
				<?php echo $this->form->getInput('type_product_id'); ?>
			</div>
		</div>
		<div class="control-group product_item_type_id_class type_id_class<?php
		echo ($this->form->getValue('type') == 'product' || $this->form->getValue('type') == '') ? ' hide' : ''; ?>">
			<div class="control-label">
				<?php echo $this->form->getLabel('type_product_item_id'); ?>
			</div>
			<div class="controls">
				<?php echo $this->form->getInput('type_product_item_id'); ?>
			</div>
		</div>

		<div class="control-group">
			<div class="control-label">
				<?php echo $this->form->getLabel('sales_type'); ?>
			</div>
			<div class="controls">
				<?php echo $this->form->getInput('sales_type'); ?>
			</div>
		</div>

		<div class="control-group customer_price_sales_id_class sales_id_class<?php
		echo ($this->form->getValue('sales_type') != 'customer_price') ? ' hide' : ''; ?>">
			<div class="control-label">
				<?php echo $this->form->getLabel('sales_customer_id'); ?>
			</div>
			<div class="controls">
				<?php echo $this->form->getInput('sales_customer_id'); ?>
			</div>
		</div>
		<div class="control-group customer_price_group_sales_id_class sales_id_class<?php
		echo ($this->form->getValue('sales_type') != 'customer_price_group') ? ' hide' : ''; ?>">
			<div class="control-label">
				<?php echo $this->form->getLabel('sales_customer_price_group_id'); ?>
			</div>
			<div class="controls">
				<?php echo $this->form->getInput('sales_customer_price_group_id'); ?>
			</div>
		</div>
		<div class="control-group campaign_sales_id_class sales_id_class<?php
		echo ($this->form->getValue('sales_type') != 'campaign') ? ' hide' : ''; ?>">
			<div class="control-label">
				<?php echo $this->form->getLabel('sales_campaign_id'); ?>
			</div>
			<div class="controls">
				<?php echo $this->form->getInput('sales_campaign_id'); ?>
			</div>
		</div>

		<div class="control-group">
			<div class="control-label">
				<?php echo $this->form->getLabel('starting_date'); ?>
			</div>
			<div class="controls">
				<?php echo $this->form->getInput('starting_date'); ?>
			</div>
		</div>
		<div class="control-group">
			<div class="control-label">
				<?php echo $this->form->getLabel('ending_date'); ?>
			</div>
			<div class="controls">
				<?php echo $this->form->getInput('ending_date'); ?>
			</div>
		</div>
		<div class="control-group">
			<div class="control-label">
				<?php echo $this->form->getLabel('is_multiple'); ?>
			</div>
			<div class="controls">
				<?php echo $this->form->getInput('is_multiple'); ?>
			</div>
		</div>
		<div class="control-group">
			<div class="control-label">
				<?php echo $this->form->getLabel('quantity_min'); ?>
			</div>
			<div class="controls">
				<?php echo $this->form->getInput('quantity_min'); ?>
			</div>
		</div>
		<div class="control-group" id="quantity_max">
			<div class="control-label">
				<?php echo $this->form->getLabel('quantity_max'); ?>
			</div>
			<div class="controls">
				<?php echo $this->form->getInput('quantity_max'); ?>
			</div>
		</div>
		<div class="control-group">
			<div class="control-label">
				<?php echo $this->form->getLabel('price'); ?>
			</div>
			<div class="controls">
				<?php echo $this->form->getInput('price'); ?>
			</div>
		</div>
		<div class="control-group">
			<div class="control-label">
				<?php echo $this->form->getLabel('retail_price'); ?>
			</div>
			<div class="controls">
				<?php echo $this->form->getInput('retail_price'); ?>
			</div>
		</div>
		<div class="control-group">
			<div class="control-label">
				<?php echo $this->form->getLabel('currency_id'); ?>
			</div>
			<div class="controls">
				<?php echo $this->form->getInput('currency_id'); ?>
			</div>
		</div>
		<div class="control-group">
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
