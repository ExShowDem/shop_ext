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
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Session\Session;

HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('rdropdown.init');
HTMLHelper::_('vnrbootstrap.tooltip');
HTMLHelper::_('rjquery.chosen', 'select');

$action    = RedshopbRoute::_('index.php?option=com_redshopb&view=return_order');
$listOrder = $this->state->get('list.ordering');
$listDirn  = $this->state->get('list.direction');

echo RedshopbBrowserBreadcrumbs::renderBreadcrumbs();
?>
<script type="text/javascript">
	function deleteReturnOrder(id)
	{
		jQuery('#adminFormList [name="cid"]').val(id);
		Joomla.submitform('return_orders.delete', document.getElementById('adminFormList'));
	}
	function ajaxsetProducts(element) {
		var orderId = jQuery(element).val();

		if (!orderId)
			return;

		jQuery.ajax({
			url: '<?php echo Uri::root(); ?>index.php?option=com_redshopb&task=return_order.ajaxGetOrderProducts',
			data: {
				"order_id": orderId,
				"<?php echo Session::getFormToken() ?>": 1
			},
			dataType: 'text',
			type: 'POST',
			beforeSend: function (xhr) {
				jQuery('#productIdBox').addClass('opacity-40');
				jQuery('#productIdBox .spinner').show();
			}
		}).done(function (data) {
			jQuery('#productIdBox .spinner').hide();
			jQuery('#productIdBox').removeClass('opacity-40').html(data);
			jQuery('.hasTooltip').tooltip({"animation": true, "html": true, "placement": "top",
				"selector": false, "title": "", "trigger": "hover focus", "delay": 0, "container": false});
			jQuery('select').chosen({"disable_search_threshold": 10, "allow_single_deselect": true});
			jQuery('#jform_product_id').trigger("liszt:updated");
		});
	}
	(function ($) {
		$(document).ready(function () {
			//$('#jform_order_id').trigger("liszt:updated");
			ajaxsetProducts($("#jform_order_id"));
		});
	})(jQuery);
</script>
<div class="redshopb-return_order">
	<form action="<?php echo $action; ?>" method="post" name="adminForm" id="adminForm" class="form-validate form-horizontal redshopb-department-form">
		<div class="control-group">
			<div class="control-label">
				<?php echo $this->itemForm->getLabel('order_id'); ?>
			</div>
			<div class="controls">
				<?php echo $this->itemForm->getInput('order_id'); ?>
			</div>
		</div>
		<div class="control-group">
			<div class="control-label">
				<?php echo $this->itemForm->getLabel('order_item_id'); ?>
			</div>
			<div class="controls" id="productIdBox">
				<div class="spinner pagination-centered" style="display:none;">
					<?php echo HTMLHelper::image('media/com_redshopb/images/ajax-loader.gif', '') ?>
				</div>
			</div>
		</div>
		<div class="control-group">
			<div class="control-label">
				<?php echo $this->itemForm->getLabel('quantity'); ?>
			</div>
			<div class="controls">
				<?php echo $this->itemForm->getInput('quantity'); ?>
			</div>
		</div>

		<div class="control-group">
			<div class="control-label">
				<?php echo $this->itemForm->getLabel('comment'); ?>
			</div>
			<div class="controls">
				<?php echo $this->itemForm->getInput('comment'); ?>
			</div>
		</div>

		<div>
			<button class="btn btn-success validate" type="button" onclick="Joomla.submitbutton('return_order.save')">
				<i class="icon icon-plus"></i> <?php echo Text::_('COM_REDSHOPB_RETURN_ORDER_ADD') ?>
			</button>
		</div>

		<!-- hidden fields -->
		<input type="hidden" name="option" value="com_redshopb">
		<input type="hidden" name="task" value="">
		<?php echo HTMLHelper::_('form.token'); ?>
	</form>
</div>

<div class="redshopb-return_orders">
	<hr />
	<h3><?php echo Text::_('COM_REDSHOPB_RETURN_ORDER_LIST') ?></h3>
	<form action="<?php echo $action; ?>" name="adminFormList" class="adminForm" id="adminFormList" method="post">
		<?php
		echo RedshopbLayoutHelper::render(
			'searchtools.default',
			array(
				'view' => $this,
				'options' => array(
					'filterButton' => false,
					'searchField' => 'search_return_orders',
					'searchFieldSelector' => '#filter_search_return_orders',
					'limitFieldSelector' => '#list_return_orders_limit',
					'activeOrder' => $listOrder,
					'activeDirection' => $listDirn
				)
			)
		);
		?>
		<hr />
		<?php if (empty($this->items)) : ?>
			<?php echo RedshopbLayoutHelper::render('common.nodata'); ?>
		<?php else : ?>
			<div class="redshopb-return_orders-table">
				<table class="table table-striped table-hover toggle-circle-filled" id="return_ordersList">
					<?php if ($this->items): ?>
						<tbody>
						<?php foreach ($this->items as $i => $item): ?>
							<tr>
								<td>
									<span class="muted"><?php echo Text::_('COM_REDSHOPB_ORDER_ID_TITLE'); ?>:</span>&nbsp;
									<?php echo $this->escape($item->order_id); ?><br />
									<span class="muted"><?php echo Text::_('COM_REDSHOPB_PRODUCT_NAME'); ?>:</span>&nbsp;
									<?php echo $this->escape($item->product_name); ?> (<?php echo $this->escape($item->product_sku); ?>)<br />
									<span class="muted"><?php echo Text::_('COM_REDSHOPB_RETURN_ORDER_COMMENT'); ?>:</span><br />
									<?php echo $this->escape($item->comment); ?>
								</td>
								<td>
									<?php echo $this->escape($item->quantity); ?>&nbsp;
									<?php echo Text::_('COM_REDSHOPB_PRODUCT_UOM_PCS'); ?>
									<button class="btn pull-right" type="button"
											onclick="deleteReturnOrder(<?php echo $item->id; ?>)">
										<i class="icon-remove"></i>
									</button>
								</td>
							</tr>
						<?php endforeach; ?>
						</tbody>
					<?php endif; ?>
				</table>
			</div>
			<div class="redshopb-return_orders-pagination">
				<?php echo $this->pagination->getPaginationLinks(null, array('showLimitBox' => false)); ?>
			</div>
		<?php endif; ?>

		<div>
			<input type="hidden" name="task" value="">
			<input type="hidden" name="cid" value="">
			<input type="hidden" name="boxchecked" value="0">
			<input type="hidden" name="return" value="<?php echo $this->return; ?>">
			<?php echo HTMLHelper::_('form.token'); ?>
		</div>
	</form>
</div>
