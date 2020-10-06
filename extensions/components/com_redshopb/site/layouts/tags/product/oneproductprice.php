<?php
/**
 * @package     Aesir.E-Commerce.Tag
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

if (!$isShop || !$isOneProduct)
{
	return;
}

$price     = (isset($extThis->product->prices[$product->id]) ? $extThis->product->prices[$product->id]->price : '');
$hidePrice = false;
RFactory::getDispatcher()->trigger('onBeforeRedshopbProcessTagPrice', array(&$price, &$hidePrice, 0, $product->id));

if ($hidePrice)
{
	return;
}

$displayPrice = '';

if ((float) $price > 0)
{
	$displayPrice = RedshopbHelperProduct::getProductFormattedPrice(
		(float) $price,
		$extThis->product->currency
	);
}

$class = 'price';

if (!empty($extThis->product->items[0]->hasVolumePricing))
{
	$class .= ' js-volume_pricing';
}

?>
<span class="<?php echo $class ?>" data-product_id="<?php echo $product->id;?>" data-collection_id="<?php echo $extThis->collectionId;?>">
	<?php echo $displayPrice;?>
</span>
