<?php
/**
 * @package     Aesir.E-Commerce.Tag
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;

if (!$isShop || !$isOneProduct || !array_key_exists($product->id, $extThis->product->prices))
{
	return;
}

$price     = $extThis->product->prices[$product->id]->price;
$hidePrice = false;
RFactory::getDispatcher()->trigger('onBeforeRedshopbProcessTagPrice', array(&$price, &$hidePrice, 0, $product->id));

if ($hidePrice)
{
	return;
}

$class        = 'oneProductPriceWithTax';
$displayPrice = '';

if (!empty($extThis->product->items[0]->hasVolumePricing))
{
	$class .= ' js-volume_pricing';
}

if ($extThis->product->prices[$product->id]->price_with_tax > 0)
{
	$displayPrice = RedshopbHelperProduct::getProductFormattedPrice(
		$extThis->product->prices[$product->id]->price_with_tax, $extThis->product->prices[$product->id]->currency
	);
}

if (!empty($extThis->product->items[0]->hasVolumePricing)
	&& !array_key_exists('initOneProductPriceWithTax', RedshopbHelperTemplate::$layoutsInitValues))
{
	RedshopbHelperTemplate::$layoutsInitValues['initOneProductPriceWithTax'] = true;
	Factory::getDocument()->addScriptDeclaration('
	jQuery(document).on("onProductAjaxPriceUpdate", function(event, data){
		if (jQuery.type(data) == "object"){
			jQuery.each(data, function(index, element) {
				var oneProductPriceWithTax = "";
				if (element.price_with_tax > 0){
					oneProductPriceWithTax = redSHOPB.shop.getProductFormattedPrice(element.price_with_tax, element.format);
				}
				jQuery(".oneProductPriceWithTax[data-product_id=\'"+index+"\']").html(oneProductPriceWithTax);
			});
		}
	});'
	);
}

?>
<span class="<?php echo $class ?>"
	  data-product_id="<?php echo $product->id;?>"
	  data-collection_id="<?php echo $extThis->collectionId;?>">
	<?php echo $displayPrice;?>
</span>
