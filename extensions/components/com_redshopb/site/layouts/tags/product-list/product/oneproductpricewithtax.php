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

/**
 * Extracted
 *
 * @var $isShop        bool
 * @var $price         float
 * @var $productId     int
 * @var $products      object
 * @var $collectionId  int
 */

if ($isShop && $price && array_key_exists($productId, $products->prices))
{
	$class        = 'oneProductPriceWithTax';
	$displayPrice = '';

	if (!empty($products->items[$productId]->hasVolumePricing))
	{
		$class .= ' js-volume_pricing';
	}

	if (!empty($products->items[$productId]->hasVolumePricing)
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

	if ($products->prices[$productId]->price_with_tax > 0)
	{
		$displayPrice = RedshopbHelperProduct::getProductFormattedPrice(
			$products->prices[$productId]->price_with_tax, $products->prices[$productId]->currency
		);
	}

	?>
	<span class="<?php echo $class ?>"
		  data-product_id="<?php echo $productId ?>"
		  data-collection_id="<?php echo $collectionId ?>">
		<?php echo $displayPrice;?>
	</span>
<?php
}
