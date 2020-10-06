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
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Session\Session;

// HTML helpers
HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('vnrbootstrap.tooltip');
HTMLHelper::_('rjquery.chosen', 'select');
HTMLHelper::_('rsearchtools.main');

RedshopbHtml::loadFooTable();

$action               = RedshopbRoute::_('index.php?option=com_redshopb&view=payment_configuration');
$input                = Factory::getApplication()->input;
$tab                  = $input->getString('tab');
$priceDebtorGroupId   = RedshopbInput::getPriceDebtorGroupIdForm();
$fromPriceDebtorGroup = RedshopbInput::isFromPriceDebtorGroup();
$isNew                = (int) $this->item->id <= 0;

if ($isNew)
{
	$this->form->setValue('owner_name', null, $priceDebtorGroupId);
}

echo RedshopbBrowserBreadcrumbs::renderBreadcrumbs();
?>
<script type="text/javascript">
	function getPaymentPlugin()
	{
		var paymentpluginname = jQuery('#jform_payment_name').val();
		jQuery.ajax({
			url: '<?php echo Uri::root(); ?>index.php?option=com_redshopb&task=payment_configuration.ajaxpaymentplugin',
			cache: false,
			type: 'POST',
			dataType:'html',
			data: {
				'payment_name': paymentpluginname,
				'payment_configuration_id': <?php echo '' .
					(!empty($this->item)
						&& !empty($this->item->id) ? $this->item->id : 'null'); ?>,
				"<?php echo Session::getFormToken() ?>": 1
			   },
			beforeSend: function (xhr) {
				jQuery('#redshopb-payment-plugin').html('');
				jQuery('#redshopb-payment-plugin-loading').show();
			}
		}).done(function (data) {
			jQuery('#redshopb-payment-plugin-loading').hide();
			jQuery('#redshopb-payment-plugin').html(data);
			jQuery('select').chosen();
			jQuery('.hasTooltip').tooltip();
			rRadioGroupButtonsSet('.redshopb-payment-plugin');
			rRadioGroupButtonsEvent('.redshopb-payment-plugin');
			jQuery('.redshopb-payment-plugin :input[checked="checked"]').click();
		});
	}
	jQuery(document).ready(function () {
		getPaymentPlugin();

		jQuery('#jform_payment_name').change(function (){
			getPaymentPlugin();
		});
	});
</script>
<div class="redshopb-payment-configuration">
	<form action="<?php echo $action; ?>" method="post" name="adminForm" id="adminForm"
		class="form-validate form-horizontal redshopb-payment_configuration-form">
		<div class="form-group">
			<div class="control-label">
				<?php echo $this->form->getLabel('payment_name'); ?>
			</div>
			<div class="controls">
				<?php echo $this->form->getInput('payment_name'); ?>
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
		<div id="redshopb-payment-plugin-loading" class="text-center">
			<br />
			<?php echo HTMLHelper::image('media/com_redshopb/images/ajax-loader.gif', ''); ?>
		</div>
		<div id="redshopb-payment-plugin">
		</div>
		<!-- hidden fields -->
		<?php echo $this->form->getInput('extension_name'); ?>
		<?php echo $this->form->getInput('owner_name'); ?>
		<input type="hidden" name="option" value="com_redshopb">
		<input type="hidden" name="from_price_debtor_group" value="<?php echo $fromPriceDebtorGroup ?>">
		<input type="hidden" name="id" value="<?php echo $this->item->id; ?>">
		<input type="hidden" name="task" value="">
		<?php echo HTMLHelper::_('form.token'); ?>
	</form>
</div>
