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
use Joomla\CMS\Factory;

$app = Factory::getApplication();
?>

<script type="text/javascript">
(function($) {
	$(document).ready(function() {
		var shouldBillAsShipChecked = Boolean(<?php echo (int) $app->getUserState("checkout.usebilling", true); ?>);

		var isBillAsShipChecked = $("input#usebilling").prop("checked");

		if (!shouldBillAsShipChecked && isBillAsShipChecked)
		{
			$("div.js-form-wrapper").css("display", "block");
			$("input#usebilling").prop("checked", false);

			if ($("select#delivery_address_id").children().length > 1)
			{
				$("div#redshopb-delivery-info-address a#update-btn").css("display", "block");
			}
			else
			{
				$("div#redshopb-delivery-info-address a#update-btn").css("display", "none");

				$("#delivery").find(".js-address-wrapper").html("\<p\><?php echo Text::_("COM_REDSHOPB_SHOP_DELIVERY_NOT_SET") ?>\<\/p\>");
			}
		}

		if (shouldBillAsShipChecked && !isBillAsShipChecked)
		{
			$("div.js-form-wrapper").css("display", "none");
			$("input#usebilling").prop("checked", true);
		}
	});
})(jQuery);
</script>
