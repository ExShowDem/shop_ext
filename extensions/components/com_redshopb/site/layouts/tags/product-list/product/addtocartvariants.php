<?php
/**
 * @package     Aesir.E-Commerce.Tag
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Plugin\PluginHelper;

defined('_JEXEC') or die;

/**
 * @var array $displayData
 * @var stdClass $productData
 * @var stdClass $productPrice
 */
extract($displayData);

HTMLHelper::_('behavior.modal');
HTMLHelper::_('rjquery.flexslider');
RHelperAsset::load('cloudzoom.css', 'com_redshopb');
RHelperAsset::load('cloudzoom.js', 'com_redshopb');

if (PluginHelper::isEnabled('vanir', 'product_custom_text'))
{
	RHelperAsset::load('script.js', 'plg_vanir_product_custom_text');
}

$data = json_encode(
	array(
		'id'                   => $productData->id,
		'categoryId'           => $productData->category_id,
		'currency'             => $currency,
		'customerId'           => $extThis->customerId,
		'customerType'         => $extThis->customerType,
		'collectionId'         => $extThis->collectionId,
		'volumePricingClass'   => $volumePricingClass,
		'cartPrefix'           => $cartPrefix,
		'price'                => base64_encode(gzcompress(serialize($productPrice))),
		'imageWidth'           => $width,
		'imageHeight'          => $height,
		'images'               => base64_encode(gzcompress(serialize($products->productImages))),
		'link'                 => $productLink,
		'placeOrderPermission' => $extThis->placeOrderPermission
	),
	JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_SLASHES
);

?>
<?php if ($isShop && $extThis->placeOrderPermission) : ?>
	<button
		type="button"
		class="btn btn-info add-to-cart add-to-cart-product"
		data-toggle="modal"
		data-target="#modalVariants-<?php echo $productData->id; ?>"
		onclick="redSHOPB.shop.loadProductVariant('<?php echo htmlentities($data); ?>');"
	>
		<i class="icon-shopping-cart"></i>
	</button>
	<div id="modalVariants-<?php echo $productData->id; ?>" class="modalVariants modal"></div>
<?php endif;
