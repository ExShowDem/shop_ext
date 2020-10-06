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

$price     = (isset($product->prices) ? $product->prices->price : '');
$hidePrice = false;
RFactory::getDispatcher()->trigger('onBeforeRedshopbProcessTagPrice', array(&$price, &$hidePrice, 0, $product->id));

$displayPrice = RedshopbHelperProduct::getProductFormattedPrice(
	(float) $price,
	$product->prices->currency
);

$class = 'price';

if (!empty($product->hasVolumePricing))
{
	$class .= ' js-volume_pricing"';
}

?>
<span class="<?php echo $class ?>">
	<?php echo $displayPrice;?>
</span>
