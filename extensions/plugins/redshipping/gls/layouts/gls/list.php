<?php
/**
 * @package     Aesir.E-Commerce.Plugin.Redshipping.Layout.GLS
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

/** @var array $displayData Imported layout data */
?>
<div id="extended-data-<?php echo $displayData['shipping_rate_id']; ?>" class="well-small">

	<?php foreach ($displayData['delivery_services'] as $service) :  ?>
	<label for="gls-service-<?php echo $service; ?>">
		<input
				id="gls-service-<?php echo $service; ?>"
				class="gls_delivery_service"
				type="radio"
				name="gls_delivery_service-<?php echo $displayData['shipping_rate_id']; ?>"
				value="<?php echo $service; ?>"
		/>
		<?php echo Text::_('PLG_REDSHIPPING_GLS_OPTION_' . strtoupper($service) . '_DELIVERY'); ?>
	</label>
	<?php endforeach; ?>
</div>
<div id="gls-parcelshop-<?php echo $displayData['shipping_rate_id']; ?>" style="display: none;">
	<div id="gls-search">
		<input type="text" id="parcelShopSearchInput" placeholder="<?php echo Text::_('PLG_REDSHIPPING_GLS_ZIP_CODE'); ?>">
		<button type="button" id="parcelShopSearchBtn"><?php echo Text::_('PLG_REDSHIPPING_GLS_SEARCH_BUTTON'); ?></button>
	</div>
	<?php echo RedshopbLayoutHelper::render('gls.shops', $displayData); ?>
	<script>
		var addressInput = jQuery('#address');
		var zipInput     = jQuery('#zip');

		jQuery('.gls_delivery_service').on('click', function () {
			jQuery.ajax({
				url: 'index.php?option=com_ajax&plugin=glsPickOption&group=redshipping&format=json',
				data: {'value': this.value, 'shipping_id': '<?php echo $displayData['shipping_rate_id']; ?>'},
				dataType: 'json',
			});
		});

		jQuery('input[name="gls_delivery_service-<?php echo $displayData['shipping_rate_id']; ?>"]').on('click', function () {
			var parcelshop = jQuery('#gls-parcelshop-<?php echo $displayData['shipping_rate_id']; ?>');

			parcelshop.hide();

			if ('gls-service-parcelshop' === this.id)
			{
				parcelshop.show();
			}
		});

		jQuery(document).ready(function()
		{
			var extendedDiv   = jQuery('#extended-data-<?php echo $displayData['shipping_rate_id']; ?>');
			var presentValues = [];
			var userState     = '<?php echo $displayData['saved_user_state']; ?>';

			extendedDiv.find('input[type=radio]').each(function () {
				presentValues.push(this.value);
			});

			if (userState != '' && presentValues.indexOf(userState) != -1)
			{
				extendedDiv.find('input[value=' + userState + ']').trigger('click');
			}
			else
			{
				extendedDiv.find('input[type=radio]:first').trigger('click');
			}
		});

		jQuery('#parcelShopSearchBtn').on('click', function () {
			var input   = jQuery(this).siblings('#parcelShopSearchInput');
			var address = addressInput.val();
			var zipCode = input.val();
			var country = '<?php echo $displayData['country']; ?>';

			ajaxUpdateShopList(address, zipCode, country);
		});

		jQuery('#zip, #address').on('blur', function () {
			var address = addressInput.val();
			var zipCode = zipInput.val();
			var country = '<?php echo $displayData['country']; ?>';

			ajaxUpdateShopList(address, zipCode, country);
		});

		function ajaxUpdateShopList(address, zipCode, country)
		{
			var ajaxData = {'address': address, 'zip': zipCode, 'country': country};

			jQuery.ajax({
				url: 'index.php?option=com_ajax&plugin=glsGetParcelShops&group=redshipping&format=json',
				data: ajaxData,
				complete: function (jqXHR) {
					jQuery('#gls-results').replaceWith(jqXHR.responseText);
				}
			});
		}
	</script>
</div>
