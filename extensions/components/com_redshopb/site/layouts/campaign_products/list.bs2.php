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

extract($displayData);

$config = RedshopbApp::getConfig();
$width  = $config->get('thumbnail_width', 144);
$height = $config->get('thumbnail_height', 144);
$i      = 1;

if (!isset($user))
{
	$user = RedshopbHelperUser::getUser(Factory::getUser()->id, 'joomla');
}
?>
<div class="campaign-products container-fluid">
	<?php if (empty($products)):?>
		<?php echo RedshopbLayoutHelper::render('common.nodata'); ?>
	<?php else :?>
		<?php foreach ($products as $product) :
			$productData = RedshopbHelperProduct::loadProduct($product->id);
			$hasNoItems  = empty(RedshopbHelperProduct::getProductItems($product->id));
			?>
			<div class="row-fluid">
				<div class="span3">
					<div class="img-center img-list">
						<?php if (isset($productsImages[$product->id]) && !empty($productsImages[$product->id])) : ?>
							<?php $productImage = $productsImages[$product->id][0]; ?>
							<img src="<?php echo RedshopbHelperThumbnail::originalToResize($productImage->name, $width, $height, 100, 0, 'products', false, $productImage->remote_path); ?>" style="min-height: <?php echo $height;?>px"
								 alt="<?php echo RedshopbHelperThumbnail::safeAlt($productImage->alt, $product->name) ?>" />
						<?php else :?>
							<?php echo RedshopbHelperMedia::drawDefaultImg($width, $height, Text::_('COM_REDSHOPB_NO_IMAGE_LABEL'), '#999999', '#dfdfdf') ?>
						<?php endif; ?>
					</div>
				</div>
				<div class="span5">
					<div class="row-fluid">
						<p class="campaign-product-info"><?php echo Text::_('COM_REDSHOPB_SHOP_MODEL') . ' ' . $product->sku;?></p>
						<h4 class="campaign-product-title"><?php echo $product->name; ?></h4>
					</div>
				</div>
				<div class="span4">
					<div class="row-fluid">
						<div class="span12 pagination-centered">
							<div class="btn-group">
								<?php if ($user) : ?>
									<button type="button" class="btn btn-info add-to-favoritelist<?php if ($product->favoritelists > 0) : ?> added<?php
																								 endif; ?>" name="addtofavorite_<?php echo $product->id; ?>"
											id="addtofavorite_<?php echo $product->id; ?>">
										<i class="icon-star"></i>
									</button>
								<?php endif; ?>
								<a href="<?php
								echo RedshopbRoute::_('index.php?option=com_redshopb&view=shop&layout=product&id=' . $product->id . '&category_id=' . $productData->categories[0] . '&collection=0') ?>"
								   class="btn btn-danger">
									<?php echo Text::_('COM_REDSHOPB_DETAILS'); ?>
								</a>
								<?php if ($hasNoItems): ?>
									<button type="button" class="btn btn-info add-to-cart add-to-cart-product" name="addtocart_<?php echo $product->id; ?>_0" data-price="<?php echo $product->price ?>" data-currency="<?php echo $product->currency ?>">
										<i class="icon-shopping-cart"></i>
										<?php echo Text::_('COM_REDSHOPB_SHOP_ADD_TO_CART'); ?>
									</button>
								<?php endif; ?>
							</div>
							<br />
							<?php
							if ($product->price) :
								?>
								<span class="campaign-product-price">
						<?php echo RHelperCurrency::getFormattedPrice($product->price, $product->currency); ?>
					</span>
								<?php
							endif;
							?>
						</div>
					</div>
				</div>
			</div>
		<?php endforeach; ?>
	<?php endif;?>
</div>
