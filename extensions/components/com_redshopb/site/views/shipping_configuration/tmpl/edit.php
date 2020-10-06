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

$action               = RedshopbRoute::_('index.php?option=com_redshopb&view=shipping_configuration');
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
	function getShippingPlugin()
	{
		var shippingpluginname = jQuery('#jform_shipping_name').val();
		jQuery.ajax({
			url: '<?php echo Uri::root(); ?>index.php?option=com_redshopb&task=shipping_configuration.ajaxshippingplugin',
			cache: false,
			dataType:'html',
			type: 'POST',
			data: {
				'shipping_name': shippingpluginname,
				'shipping_configuration_id': <?php echo '' .
					(!empty($this->item)
						&& !empty($this->item->id) ? $this->item->id : 'null'); ?>,
				"<?php echo Session::getFormToken() ?>": 1
			   },
			beforeSend: function (xhr) {
				jQuery('#redshopb-shipping-plugin').html('');
				jQuery('#redshopb-shipping-plugin-loading').show();
			}
		}).done(function (data) {
			jQuery('#redshopb-shipping-plugin-loading').hide();
			jQuery('#redshopb-shipping-plugin').html(data);
			jQuery('select').chosen();
			jQuery('.hasTooltip').tooltip();
			rRadioGroupButtonsSet('.redshopb-shipping-configuration');
			rRadioGroupButtonsEvent('.redshopb-shipping-configuration');
			jQuery('.redshopb-shipping-configuration :input[checked="checked"]').click();
		});
	}
	jQuery(document).ready(function () {
		getShippingPlugin();

		jQuery('#jform_shipping_name').change(function (){
			getShippingPlugin();
		});
	});
</script>
<div class="redshopb-shipping-configuration">
	<form action="<?php echo $action; ?>" method="post" name="adminForm" id="adminForm"
		class="form-validate form-horizontal redshopb-shipping_configuration-form">
		<div class="form-group">
			<div class="control-label">
				<?php echo $this->form->getLabel('shipping_name'); ?>
			</div>
			<div class="controls">
				<?php echo $this->form->getInput('shipping_name'); ?>
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
		<div id="redshopb-shipping-plugin-loading" class="text-center">
			<br />
			<?php echo HTMLHelper::image('media/com_redshopb/images/ajax-loader.gif', ''); ?>
		</div>
		<div id="redshopb-shipping-plugin">
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
