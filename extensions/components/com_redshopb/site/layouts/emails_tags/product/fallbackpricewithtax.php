<?php
/**
 * @package     Aesir.E-Commerce.Email_tags
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

$product = $displayData['product'];

$price     = (isset($product->prices) ? $product->prices->fallback_price_with_tax : '');
$hidePrice = false;
RFactory::getDispatcher()->trigger('onBeforeRedshopbProcessTagPrice', array(&$price, &$hidePrice, 0, $product->id));

$displayPrice = RedshopbHelperProduct::getProductFormattedPrice(
	(float) $price,
	$product->prices->currency
);

?>
<span class="fallbackpricewithtax">
	<?php echo $displayPrice;?>
</span>
