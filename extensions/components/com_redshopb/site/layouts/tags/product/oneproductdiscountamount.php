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

if ($isShop && $isOneProduct && $extThis->placeOrderPermission
	&& isset($extThis->product->prices[$product->id]->discount)
	&& $extThis->product->prices[$product->id]->discount)
{
	$class = 'oneProductPriceDiscountAmount';

	if (!empty($extThis->product->items[0]->hasVolumePricing))
	{
		$class .= ' js-volume_pricing';
	}

	$displayDiscount = RedshopbHelperPrices::displayDiscount(
		($extThis->product->prices[$product->id]->discount / 100) * $extThis->product->prices[$product->id]->fallback_price,
		RedshopbHelperPrices::DISCOUNT_TOTAL,
		$extThis->product->prices[$product->id]->currency
	);

	if (!empty($extThis->product->items[0]->hasVolumePricing)
		&& !array_key_exists('initOneProductPriceWithTax', RedshopbHelperTemplate::$layoutsInitValues))
	{
		RedshopbHelperTemplate::$layoutsInitValues['initOneProductDiscountAmount'] = true;
		Factory::getDocument()->addScriptDeclaration('
	jQuery(document).on("onProductAjaxPriceUpdate", function(event, data){
		if (jQuery.type(data) == "object"){
			jQuery.each(data, function(index, element) {
				var oneProductPriceDiscountAmount = "";
				if (element.fallback_price > 0 && element.discount > 0){
					oneProductPriceDiscountAmount = redSHOPB.shop.getProductFormattedPrice((element.discount / 100) * element.fallback_price, element.format)
				}
				jQuery(".oneProductPriceDiscountAmount[data-product_id=\'"+index+"\']").html(oneProductPriceDiscountAmount);
			});
		}
	});'
		);
	}

?>
<span class="<?php echo $class ?>"
	  data-product_id="<?php echo $product->id;?>"
	  data-collection_id="<?php echo $extThis->collectionId;?>">
	<?php echo $displayDiscount;?>
</span>
<?php
}
