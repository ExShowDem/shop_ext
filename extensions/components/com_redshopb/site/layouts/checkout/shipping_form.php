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

$showTitle = $displayData['showTitle'];

?>
<?php if ($showTitle):?>
<h4><?php echo Text::_('COM_REDSHOPB_SHOP_SELECT_SHIPPING_TITLE', true); ?></h4>
<?php endif;?>

<div class="row">
	<div class="col-md-12">
	<?php echo RedshopbLayoutHelper::render('redshipping.list.radio', $displayData); ?>
	</div>
</div>
<script type="text/javascript">
	jQuery(document).ready(function() {
		jQuery('#shippingMethods').on('change', 'input[id^=shippingRate]', function() {
			jQuery.ajax({
				url: 'index.php?option=com_redshopb&task=shop.ajaxUpdateShippingRateId',
				data: {shippingRateId: jQuery(this).val()},
				method: 'post',
				dataType: 'json'
			}).done(function(data, textStatus, jqXHR)
			{
				var useBillingInput = jQuery('#usebilling');

				if (data.selfPickup == true && useBillingInput.length > 0)
				{
					if (useBillingInput.prop('checked') == false)
					{
						useBillingInput.click();
					}

					useBillingInput.prop('disabled', true);
				}
				else
				{
					useBillingInput.prop('disabled', false);
				}
			});

			jQuery(document).one('ajaxStop', function() {
				redSHOPB.shop.updateCheckout();
			});
		});
	});
</script>
