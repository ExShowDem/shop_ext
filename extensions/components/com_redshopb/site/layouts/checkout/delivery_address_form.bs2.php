<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Views
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Factory;

if (is_object($displayData))
{
	$displayData = (array) $displayData;
}

$showTitle         = $displayData['showTitle'];
$deliveryAddressId = (!empty($displayData['address']['id'])) ? $displayData['address']['id'] : 0;
$name              = (!empty($displayData['address']['name'])) ? $displayData['address']['name'] : '';
$name2             = (!empty($displayData['address']['name2'])) ? $displayData['address']['name2'] : '';
$address           = (!empty($displayData['address']['address'])) ? $displayData['address']['address'] : '';
$address2          = (!empty($displayData['address']['address2'])) ? $displayData['address']['address2'] : '';
$zip               = (!empty($displayData['address']['zip'])) ? $displayData['address']['zip'] : '';
$city              = (!empty($displayData['address']['city'])) ? $displayData['address']['city'] : '';
$phone             = (!empty($displayData['address']['phone'])) ? $displayData['address']['phone'] : '';
$stateName         = (!empty($displayData['address']['state_name'])) ? $displayData['address']['state_name'] : '';
$country           = (!empty($displayData['address']['country'])) ? $displayData['address']['country'] : '';

$app          = Factory::getApplication();
$config       = RedshopbEntityConfig::getInstance();
$checkoutMode = $config->get('checkout_mode', 'default', 'string');

$fields = $displayData['fields'];

if (!empty($deliveryAddressId))
{
	$fields->delivery_address_id->setValue($deliveryAddressId);
}

$countryForm = $fields->country_id->input;
$params      = array(&$countryForm, true);

$dispatcher = RFactory::getDispatcher();
PluginHelper::importPlugin('vanir');
$dispatcher->trigger('post_process_countries', $params);

$title            = 'COM_REDSHOPB_SHOP_DELIVERY_ADDRESS_CHANGE';
$formWrapperStyle = ' style="display:none;"';
$showFullForm     = ($displayData['manageOwnAddress']);

$customerId   = isset($displayData['address']['customer_id']) ? $displayData['address']['customer_id'] : $app->getUserState('shop.customer_id');
$customerType = isset($displayData['address']['customer_type']) ? $displayData['address']['customer_type'] : $app->getUserState('shop.customer_type');

$billingAddressId = RedshopbEntityCustomer::getInstance(
	$customerId,
	$customerType
)->getAddress()->getExtendedData()->id;

if (is_null($billingAddressId))
{
	if ($customerType === RedshopbEntityCustomer::TYPE_DEPARTMENT)
	{
		$companyId      = RedshopbHelperDepartment::getCompanyId($customerId);
		$billingAddress = RedshopbEntityCustomer::getInstance(
			$companyId,
			RedshopbEntityCustomer::TYPE_COMPANY
		)->getAddress()->getExtendedData();

		$billingAddressId = $billingAddress->id ? $billingAddress->id : 0;
	}
}

?>
<script type="text/javascript">
	jQuery(document).ready(function(){

		var deliveryAddressId = jQuery('#delivery_address_id');
		deliveryAddressId.on('change', function (event)
		{
			<?php if ($checkoutMode == 'onepage') : ?>
				if (jQuery("div#shippingMethods").length > 0)
				{
					redSHOPB.shop.checkout.updateShippingMethods();
				}
			<?php endif; ?>

			redSHOPB.shop.checkout.updateDelivery(event);
		});

		var billingAddrId   = '<?php echo $billingAddressId; ?>';
		var firstDeliveryId = deliveryAddressId.find('option:nth(1)').val();

		jQuery('#usebilling').on('change', function (event)
		{
			if (this.checked)
			{
				/*
				Temporarily adds the billing address id as an option
				so we can change the delivery address to the billing address
				when checking the "Use billing address for shipping" button
				 */
				deliveryAddressId.append(jQuery('<option>', {value: billingAddrId}));
				deliveryAddressId.val(billingAddrId);
				deliveryAddressId.trigger('change');
				deliveryAddressId.find('option:last').remove();
			}
			else
			{
				/*
				When we uncheck it we just select the first delivery address by default
				 */
				deliveryAddressId.val(firstDeliveryId);
				deliveryAddressId.trigger('change');
				deliveryAddressId.trigger("liszt:updated");
			}
		});

		redSHOPB.shop.checkout.checkCountry();

		jQuery(document).on('change', '#country_id', function (event) {
			redSHOPB.shop.checkout.checkCountry();
		});
	});
</script>
<?php if ($showTitle):?>
	<h4><?php echo Text::_($title, true); ?></h4>
<?php endif;?>
<label class="checkbox">
	<?php echo $fields->usebilling->input; ?>&nbsp;
	<?php echo Text::_('COM_REDSHOPB_B2BUSER_USE_ADDRESS_BILLING_SHIPPING') ?>
</label>
<div class="js-form-wrapper"<?php echo $formWrapperStyle;?>>
	<div class="row-fluid">
		<div class="span12">
			<div class="control-group" style="height: 65px;">
				<div class="control-label">
					<?php echo $fields->delivery_address_id->label; ?>
				</div>
				<div class="controls">
					<?php echo $fields->delivery_address_id->input; ?>
				</div>
			</div>
			<?php if ($showFullForm):?>
				<div class="control-group">
					<div class="control-label">
						<?php echo $fields->email->label; ?>
					</div>
					<div class="controls">
						<?php echo $fields->email->input; ?>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo $fields->name->label; ?>
					</div>
					<div class="controls">
						<?php echo $fields->name->input; ?>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo $fields->name2->label; ?>
					</div>
					<div class="controls">
						<?php echo $fields->name2->input; ?>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo $fields->address->label; ?>
					</div>
					<div class="controls">
						<?php echo $fields->address->input; ?>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo $fields->address2->label; ?>
					</div>
					<div class="controls">
						<?php echo $fields->address2->input; ?>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo $fields->city->label; ?>
					</div>
					<div class="controls">
						<?php echo $fields->city->input; ?>
					</div>
				</div>
				<div class="control-group billingStateGroup hide">
					<div class="control-label">
						<?php echo $fields->state_id->label; ?>
					</div>
					<div class="controls">
						<?php echo $fields->state_id->input; ?>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo $fields->zip->label; ?>
					</div>
					<div class="controls">
						<?php echo $fields->zip->input; ?>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo $fields->phone->label; ?>
					</div>
					<div class="controls">
						<?php echo $fields->phone->input; ?>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo $fields->country_id->label; ?>
					</div>
					<div class="controls">
						<?php echo $countryForm; ?>
					</div>
				</div>
			<?php endif;?>
		</div>
	</div>
	<div class="row-fluid">
		<div class="span12">
			<div class="btn-toolbar toolbar pull-right" style="margin-right: 19px">
				<div class="btn-group">
					<a class="btn btn-default" href="javascript:void(0);" id="update-btn"
					   onclick="redSHOPB.shop.checkout.saveAddress(event);" data-action="update">
						<i class="icon-save"></i>
						<?php echo Text::_('JTOOLBAR_UPDATE'); ?>
					</a>
					<?php if ($displayData['manageOwnAddress']) : ?>
						<a href="javascript:void(0);" class="btn btn-success" id="save-as-new-btn"
						   onclick="redSHOPB.shop.checkout.saveAddress(event);" data-action="new">
							<i class="icon-file-text-alt"></i>
							<?php echo Text::_('JTOOLBAR_SAVE_AS_NEW'); ?>
						</a>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</div>
</div>
