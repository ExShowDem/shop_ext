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
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Session\Session;


$action = RedshopbRoute::_('index.php?option=com_redshopb&view=all_discount');
$isNew  = (int) $this->item->id <= 0;

// HTML helpers
HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('vnrbootstrap.tooltip');
HTMLHelper::_('rjquery.chosen', 'select');

echo RedshopbBrowserBreadcrumbs::renderBreadcrumbs();
?>

<script type="text/javascript">
	function discountProductDebtor() {
		(function($){
			var productId = $("#jform_type_product_id").val();

			if (isNaN(productId) || productId == '') {
				return false;
			}

			// Perform ajax request for remove saved cart
			$.ajax({
				url: "<?php echo Uri::root(); ?>index.php?option=com_redshopb&task=product.ajaxloadvendor",
				cache: false,
				type: 'POST',
				data: {
					'<?php echo Session::getFormToken(); ?>' : 1,
					'productid': productId
				}
			})
			.success(function(data) {
				if ($('#jform_sales_debtor_id').length) {
					$('#jform_sales_debtor_id').chosen({"disable_search_threshold":10,"allow_single_deselect":true});
				}
			});
		})(jQuery);
	}

	(function ($) {
		$(document).ready(function () {
			<?php if ($isNew): ?>
			if (($('#jform_type').val() == 'product') && ($("#jform_sales_type").val() == 'debtor')) {
				discountProductDebtor();
			}
			<?php endif; ?>

			$('#adminForm')
				.on('change', '#jform_type', function() {
					if ($(this).val() == 'product' && $("#jform_sales_type").val() == 'debtor' && $("#jform_type_product_id").val() !== '') {
						discountProductDebtor();
					}

					$('.type_id_class').addClass('hide');
					$('.' + $(this).val() + '_type_id_class').removeClass('hide');
				})
				.on('change', '#jform_sales_type', function() {
					if ($("#jform_type").val() == 'product' && $(this).val() == 'debtor' && $("#jform_type_product_id").val() !== '') {
						discountProductDebtor();
					}

					$('.sales_id_class').addClass('hide');
					$('.' + $(this).val() + '_sales_id_class').removeClass('hide');
				})
				.on('change', '#jform_type_product_id', function() {
					if (($('#jform_type').val() == 'product') && ($("#jform_sales_type").val() == 'debtor')) {
						discountProductDebtor();
					}
				});
		});
	})(jQuery);
</script>
<div class="redshopb-all_discount">
	<form action="<?php echo $action; ?>" method="post" name="adminForm" id="adminForm"
		  class="form-validate form-horizontal">
		<div class="control-group">
			<?php echo $this->form->renderField('type') ?>
		<div class="control-group product_type_id_class type_id_class<?php
		echo ($this->form->getValue('type') != 'product') ? ' hide' : '';?>">
			<div class="control-label">
				<?php echo $this->form->getLabel('type_product_id'); ?>
			</div>
			<div class="controls">
				<?php echo $this->form->getInput('type_product_id'); ?>
			</div>
		</div>
			<div class="control-group product_item_type_id_class type_id_class<?php
			echo ($this->form->getValue('type') != 'product_item') ? ' hide' : '';?>">
				<div class="control-label">
					<?php echo $this->form->getLabel('type_product_item_id'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('type_product_item_id'); ?>
				</div>
			</div>
		<div class="control-group product_discount_group_type_id_class type_id_class<?php
		echo ($this->form->getValue('type') != 'product_discount_group') ? ' hide' : '';?>">
			<div class="control-label">
				<?php echo $this->form->getLabel('type_product_discount_group_id'); ?>
			</div>
			<div class="controls">
				<?php echo $this->form->getInput('type_product_discount_group_id'); ?>
			</div>
		</div>
		<?php echo $this->form->renderField('sales_type') ?>
		<div class="control-group debtor_sales_id_class sales_id_class<?php
		echo ($this->form->getValue('sales_type') != 'debtor') ? ' hide' : '';?>">
			<div class="control-label">
				<?php echo $this->form->getLabel('sales_debtor_id'); ?>
			</div>
			<div class="controls" id="debtor_sales_id_wrapper">
				<?php echo $this->form->getInput('sales_debtor_id'); ?>
			</div>
		</div>
		<div class="control-group debtor_discount_group_sales_id_class sales_id_class<?php
		echo ($this->form->getValue('sales_type') != 'debtor_discount_group') ? ' hide' : '';?>">
			<div class="control-label">
				<?php echo $this->form->getLabel('sales_debtor_discount_group_id'); ?>
			</div>
			<div class="controls">
				<?php echo $this->form->getInput('sales_debtor_discount_group_id'); ?>
			</div>
		</div>
		<?php echo $this->form->renderField('state') ?>
		<?php echo $this->form->renderField('starting_date') ?>
		<?php echo $this->form->renderField('ending_date') ?>
		<?php echo $this->form->renderField('kind') ?>
		<?php echo $this->form->renderField('percent') ?>
		<?php echo $this->form->renderField('total') ?>
		<?php echo $this->form->renderField('quantity_min') ?>
		<?php echo $this->form->renderField('quantity_max') ?>
		<?php echo $this->form->renderField('currency_id') ?>
		<!-- hidden fields -->
		<input type="hidden" name="option" value="com_redshopb">
		<input type="hidden" name="id" value="<?php echo $this->item->id; ?>">
		<input type="hidden" name="task" value="">
		<?php echo HTMLHelper::_('form.token'); ?>
	</form>
</div>
