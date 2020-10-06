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
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Session\Session;

// HTML helpers
HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('rdropdown.init');
HTMLHelper::_('rjquery.chosen', 'select');
HTMLHelper::_('behavior.formvalidation');

// Variables
$app     = Factory::getApplication();
$input   = $app->input;
$isNew   = (int) $this->item->id <= 0;
$tab     = $input->getString('tab');
$isNew   = (int) $this->item->id <= 0;
$itemId  = $input->getInt('Itemid', 0);
$action  = RedshopbRoute::_('index.php?option=com_redshopb&task=order.edit&id=' . $this->item->id . '&Itemid=' . $itemId, false);
$config  = RedshopbApp::getConfig();

echo RedshopbBrowserBreadcrumbs::renderBreadcrumbs();
$showPaymentForm = false;

if (!empty($this->item->payment_name))
{
	$payment = RApiPaymentHelper::getPaymentByExtensionId('com_redshopb', $this->item->id);

	if ($payment)
	{
		$pluginPayment = RApiPaymentHelper::getPaymentParams($payment->payment_name, $payment->extension_name, $payment->owner_name);

		if ($pluginPayment
			&& $pluginPayment->params->get('offline_payment', 0) == 0
			&& in_array(
				$this->item->payment_status,
				array(
					RApiPaymentStatus::getStatusCreated(),
					RApiPaymentStatus::getStatusProcessed(),
					RApiPaymentStatus::getStatusAuthorized()
				)
			))
		{
			$showPaymentForm = true;
		}
	}
}

switch ((int) $this->item->status)
{
	case 0:
	case 1:
	case 4:
	case 5:?>

	<script type="text/javascript">
		jQuery(document).ready(function() {
			jQuery('option[value="3"]').attr("disabled","disabled");
			jQuery('option[value="6"]').attr("disabled","disabled");
			jQuery('option[value="7"]').attr("disabled","disabled");
			jQuery('#jform_status').trigger('liszt:updated');
		});
	</script>

<?php
		break;
	case 2:?>
	<script type="text/javascript">
		jQuery(document).ready(function() {
			jQuery('option[value="0"]').attr("disabled","disabled");
			jQuery('option[value="1"]').attr("disabled","disabled");
			jQuery('option[value="3"]').attr("disabled","disabled");
			jQuery('option[value="4"]').attr("disabled","disabled");
			jQuery('option[value="5"]').attr("disabled","disabled");
			jQuery('option[value="6"]').attr("disabled","disabled");
			jQuery('option[value="7"]').attr("disabled","disabled");
			jQuery('#jform_status').trigger('liszt:updated');
		});
	</script>
<?php
		break;
	case 3:?>
	<script type="text/javascript">
		jQuery(document).ready(function() {
			jQuery('option[value="0"]').attr("disabled","disabled");
			jQuery('option[value="1"]').attr("disabled","disabled");
			jQuery('option[value="2"]').attr("disabled","disabled");
			jQuery('option[value="4"]').attr("disabled","disabled");
			jQuery('option[value="5"]').attr("disabled","disabled");
			jQuery('option[value="6"]').attr("disabled","disabled");
			jQuery('option[value="7"]').attr("disabled","disabled");
			jQuery('#jform_status').trigger('liszt:updated');
		});
	</script>
<?php
		break;
	case 6:?>

	<script type="text/javascript">
		jQuery(document).ready(function() {
			jQuery('option[value="0"]').attr("disabled","disabled");
			jQuery('option[value="1"]').attr("disabled","disabled");
			jQuery('option[value="2"]').attr("disabled","disabled");
			jQuery('option[value="3"]').attr("disabled","disabled");
			jQuery('option[value="4"]').attr("disabled","disabled");
			jQuery('option[value="5"]').attr("disabled","disabled");
			jQuery('option[value="7"]').attr("disabled","disabled");
			jQuery('#jform_status').trigger('liszt:updated');
		});
	</script>

<?php
		break;
	case 7:?>

	<script type="text/javascript">
		jQuery(document).ready(function() {
			jQuery('option[value="0"]').attr("disabled","disabled");
			jQuery('option[value="1"]').attr("disabled","disabled");
			jQuery('option[value="2"]').attr("disabled","disabled");
			jQuery('option[value="3"]').attr("disabled","disabled");
			jQuery('option[value="4"]').attr("disabled","disabled");
			jQuery('option[value="5"]').attr("disabled","disabled");
			jQuery('option[value="6"]').attr("disabled","disabled");
			jQuery('#jform_status').trigger('liszt:updated');
		});
	</script>

<?php
		break;
}?>
<?php
if ($this->item->id): ?>
	<script type="text/javascript">
		(function($){
			$(document).ready(function(){
				$('.redshopb-receipt-reorder-btn').click(function(event) {
					event.preventDefault();

					var orderId = $(this).attr("data-order");

					// Perform ajax request for remove saved cart
					$.ajax({
							url: "<?php echo Uri::root(); ?>index.php?option=com_redshopb&task=order.ajaxCheckoutCartFromOrder",
							data: {
								"id": orderId,
								"<?php echo Session::getFormToken() ?>": 1
							},
							cache: false,
							type: 'POST',
							dataType: "JSON"
						})
						.success(function(data) {
							if (data == '1') {
								window.location.href = "<?php echo RedshopbRoute::_('index.php?option=com_redshopb&view=shop&layout=cart', false) ?>";
							}
						});
				});
			});
		})(jQuery);
	</script>
<div class="pull-right">
	<a href="javascript:void(0);" class="redshopb-receipt-reorder-btn" data-order="<?php echo $this->item->id ?>">
		<?php echo Text::_('COM_REDSHOPB_SHOP_REORDER') ?>
	</a>
</div>
<?php endif; ?>
<div class="redshopb-order">



	<form action="<?php echo $action ?>" method="post" id="adminForm" name="adminForm" class="form-validate redshopb-order-form">
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="option" value="com_redshopb">
		<input type="hidden" name="id" value="<?php echo $this->item->id; ?>" id="order_id">
		<?php echo HTMLHelper::_('form.token'); ?>
		<?php echo HTMLHelper::_('vnrbootstrap.startTabSet', 'mainTab', array('active' => 'ordergeneraltab')); ?>

		<?php echo HTMLHelper::_('vnrbootstrap.addTab', 'mainTab', 'ordergeneraltab', Text::_('COM_REDSHOPB_ORDER_GENERAL', true)); ?>
		<div class="container-fluid">
			<div class="row">
				<div class="col-md-6">
					<div class="form-group">
						<div class="control-label">
							<?php echo Text::_('COM_REDSHOPB_ORDER_CUSTOMER_TITLE'); ?>
						</div>
						<div class="controls">
							<div class="help-inline">
								<strong><?php echo $this->item->customer_name . ' (' . Text::_('COM_REDSHOPB_' . $this->item->customer_type) . ')'; ?></strong>
							</div>
						</div>
					</div>
					<?php
					foreach ($this->form->getFieldset($this->formFieldset) as $field)
						:
						?>
						<?php if ($field->hidden) : ?>
						<?php echo $field->input;?>
						<?php continue;?>
						<?php endif; ?>

						<?php
						if (($field->fieldname == 'sales_header_id' && ((int) $field->value) == 0)
							|| ($field->fieldname == 'sales_header_type' && ((string) $field->value) == '')
							|| ($config->getInt('use_shipping_date', 0) == 0 && $field->fieldname == 'shipping_date')
						)
						{
							continue;
						}
						?>
						<div class="form-group">
							<div class="control-label">
								<?php echo $field->label; ?>
							</div>
							<div class="controls">
								<?php echo $field->input; ?>
								<?php
								if ($field->fieldname == 'total_price'): ?>
																	<?php echo '&nbsp;' . $this->item->currency ?>
								<?php endif; ?>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
				<div class="col-md-6">
					<div class="well">
						<h5><?php echo Text::_('COM_REDSHOPB_ORDER_DELIVERY_ADDRESS_TITLE', true); ?></h5><hr />
						<p><?php echo $this->item->delivery_address_name; ?></p>
						<?php
						if (isset($this->item->delivery_address_name2)) :?>
													<p><?php echo $this->item->delivery_address_name2; ?></p>
						<?php endif;?>
						<p><?php echo $this->item->delivery_address_address; ?></p>
						<?php
						if (isset($this->item->delivery_address_address2)) :?>
													<p><?php echo $this->item->delivery_address_address2; ?></p>
						<?php endif;?>
						<p><?php echo $this->item->delivery_address_zip; ?>, <?php echo $this->item->delivery_address_city; ?></p>
						<p>
							<?php
							if ($this->item->delivery_address_state)
							{
								echo $this->item->delivery_address_state . ',&nbsp;';
							}

							echo $this->item->delivery_address_country;
							?>
						</p>
						<input type="hidden" name="jform[delivery_address_id]" value="<?php echo $this->item->delivery_address_id ?>" />
					</div>
					<?php if (!empty($this->item->shipping_rate_id)) :?>
					<div class="well">
						<h5><?php echo Text::_('COM_REDSHOPB_ORDER_SHIPPING_METHOD'); ?></h5><hr />
						<p>
							<strong><?php echo Text::_('COM_REDSHOPB_ORDER_SHIPPING_METHOD_SELECTED'); ?>: </strong>
							<?php echo RedshopbShippingHelper::getShippingRateName($this->item->shipping_rate_id, true, $this->item->currency, $this->item->id); ?>
						</p>
						<?php
						PluginHelper::importPlugin('redshipping');

						$app->triggerEvent('onAESECExtendedShippingInfo', array($this->item, &$html));

						echo $html;
						?>
					</div>
					<?php endif; ?>
					<?php
					if (!empty($this->item->payment_name)) : ?>
						<div class="well">
							<h5><?php echo Text::_('COM_REDSHOPB_ORDER_PAYMENT_METHOD'); ?></h5><hr />
							<p>
								<strong><?php echo Text::_('COM_REDSHOPB_ORDER_PAYMENT_METHOD_SELECTED'); ?>: </strong>
								<?php echo $this->item->payment_title; ?>
							</p>
							<?php
							$paymentExtraFields = RedshopbHelperOrder::getPaymentExtraInformation($this->item->id);

							foreach ($paymentExtraFields as $paymentExtraField) : ?>
								<p>
									<strong><?php echo $paymentExtraField->title; ?>: </strong>
									<?php echo $paymentExtraField->value; ?>
								</p>
							<?php endforeach; ?>
							<p>
								<strong><?php echo Text::_('COM_REDSHOPB_ORDER_PAYMENT_STATUS'); ?>: </strong>
								<label class="label label-<?php echo RApiPaymentStatus::getStatusLabelClass($this->item->payment_status); ?>">
									<?php echo RApiPaymentStatus::getStatusLabel($this->item->payment_status); ?>
								</label>
							</p>
							<p>
								<strong><?php echo Text::_('COM_REDSHOPB_ORDER_PAYMENT_AMOUNT_PAID'); ?>: </strong>
								<?php echo RHelperCurrency::getFormattedPrice($this->item->total_price_paid, $this->item->currency); ?>
							</p>
							<?php if (!empty($payment->amount_payment_fee) && $payment->amount_payment_fee != '0.00') :?>
							<p>
								<strong><?php echo Text::_('COM_REDSHOPB_ORDER_PAYMENT_FEE'); ?>: </strong>
								<?php echo RHelperCurrency::getFormattedPrice($payment->amount_payment_fee, $payment->currency); ?>
							</p>
							<?php endif; ?>

							<?php  if ($showPaymentForm) : ?>
								<p><strong><?php echo Text::_('COM_REDSHOPB_ORDER_PAYMENT_PAY_NOW'); ?>: </strong></p>
								<input
									type="button"
									class="btn btn-primary"
									value="<?php echo Text::sprintf('LIB_REDCORE_PAYMENT_SUBMIT_TO_PAYMENT', $this->item->payment_title); ?>"
									onclick="jQuery('#redpaymentForm<?php echo $payment->id; ?> .submitButton').click();"
								/>
							<?php endif; ?>
						</div>
					<?php endif; ?>
				</div>
			</div>
		</div>
		<?php echo HTMLHelper::_('vnrbootstrap.endTab'); ?>

		<?php echo HTMLHelper::_('vnrbootstrap.addTab', 'mainTab', 'orderitemstab', Text::_('COM_REDSHOPB_ORDER_ITEMS', true)); ?>
			<?php echo $this->loadTemplate("orderitems"); ?>
		<?php echo HTMLHelper::_('vnrbootstrap.endTab'); ?>

		<?php echo HTMLHelper::_('vnrbootstrap.endTabSet'); ?>
	</form>
</div>
<?php if ($showPaymentForm) : ?>
<div class="hidden">
	<?php echo RApiPaymentHelper::displayPayment(
		$this->paymentData['payment_name'],
		$this->paymentData['extension_name'],
		$this->paymentData['owner_name'],
		$this->paymentData
	); ?>
</div>
<?php endif; ?>

	<?php if ($tab) : ?>
		<script type="text/javascript">
			jQuery(document).ready(function () {

				// Show the corresponding tab
				jQuery('#mainTabTabs a[href="#<?php echo $tab ?>"]').tab('show');
			});
		</script>
	<?php endif; ?>
<script type="text/javascript">
	jQuery(document).ready(function(){
		Joomla.submitbutton = function (task)
		{
			if (task == 'order.save' || task == 'order.apply') {
				var items = [];
				var orderId = jQuery('#order_id').val();
				jQuery('.ajax-quantity-change').each(function(i,e) {
					var element = jQuery(e);
					var id = element.attr('id');
					var info = id.split('_');
					items.push(info[2] + '_' + element.val());
				});

				jQuery.ajax({
					url: '<?php echo Uri::root(); ?>index.php?option=com_redshopb&task=order.ajaxSaveOrderItems',
					type: 'POST',
					data: {
						'items': items,
						'orderid': orderId,
						"<?php echo Session::getFormToken() ?>": 1
					},
					dataType: 'json',
					before: function ()
					{
						jQuery('button').attr('disabled', 'disabled');
						jQuery('a').attr('disabled', 'disabled');
						jQuery('input').attr('disabled', 'disabled');
					},
					success: function (data) {
						if (data.success == 0)
						{
							var messageContainer = jQuery('#system-message-container');
							var msg = '<div class=\"alert alert-warning\"><a class=\"close\" data-dismiss=\"alert\">×</a><h4 class=\"alert-heading\"><?php echo Text::_('WARNING')?></h4><div><p>' + data.msg + '</p></div></div>';
							messageContainer.html(msg);

							jQuery('html, body').animate({
								scrollTop: messageContainer.offset().top
							}, 1000);
							return false;
						}
						else
						{
							jQuery('#jform_total_price').val(data.total);
							Joomla.submitform(task, document.getElementById('adminForm'));
						}
					}
				});
			} else if (task == 'order.editOrderItems'
				|| task == 'order.cancel'
				|| document.formvalidator.isValid(document.getElementById('adminForm'))) {
				Joomla.submitform(task, document.getElementById('adminForm'));
			} else {
				jQuery('#system-message-container').html("<div><?php echo Text::_('COM_REDSHOPB_ORDER_FILL_REQUIRED') ?></div>");
			}
		}
	});
</script>
