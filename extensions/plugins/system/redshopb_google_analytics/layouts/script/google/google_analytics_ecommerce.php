<?php
/**
 * @package     Aesir.E-Commerce
 * @subpackage  Plugin
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

extract($displayData);

$conf     = RFactory::getConfig();
$orderTax = 0;

if (!empty($order->taxs))
{
	foreach ($order->taxs as $tax)
	{
		$orderTax += $tax->tax;
	}
}

?><!-- Google Code for Order Confirmation Conversion Page -->
<script type="text/javascript">
	(function ($) {
		$(document).ready(function () {
			ga('require', 'ecommerce');
			ga("ecommerce:addTransaction", {
				"id": "<?php echo $order->id ?>",
				"affiliation": "<?php echo addslashes($conf->get('sitename', '')) ?>",
				"revenue": "<?php echo $order->total_price ?>",
				"shipping": "<?php echo $order->shipping_price ?>",
				"tax": "<?php echo $orderTax ?>"
			});
			<?php
			if (!empty($order->regular->items)):
				foreach ($order->regular->items as $item):
					$categoryId   = RedshopbEntityProduct::getInstance($item->product_id)->get('category_id');
					$categoryName = $categoryId ? RedshopbEntityCategory::getInstance($categoryId)->get('name') : '';
					?>ga("ecommerce:addItem", {
					"id": "<?php echo $order->id ?>",
				"name": "<?php echo addslashes($item->product_name) ?>",
				"sku": "<?php echo addslashes($item->product_item_sku) ?>",
				"category": "<?php echo addslashes($categoryName) ?>",
				"price": "<?php echo $item->price ?>",
				"quantity": "<?php echo $item->quantity ?>"
				});<?php
				endforeach;
			endif;
			?>
			ga("ecommerce:send");
		});
	})(jQuery);
</script>
