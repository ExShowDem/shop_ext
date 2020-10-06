<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Views
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

/**
 * Layout variables
 * ================================
 * @var  array $displayData Layout data
 * @var  int   $productId   Product Id..
 * @var  array $prices      List of available prices.
 */

extract($displayData);

$tax = RedshopbHelperTax::getProductTax($productId);
?>

<?php if (!empty($prices)): ?>
	<div class="product-volume-with-tax-prices product-volume-with-tax-price-<?php echo $productId ?>">
		<?php foreach ($prices as $price): ?>
			<div class="volume-price-with-tax volume-price-with-tax-<?php echo $price->id ?>">
				<span class="volume-price-quantity">
				<?php if ($price->is_multiple): ?>
					<?php echo '* ' . $price->quantity_min ?>
				<?php elseif ($price->quantity_min && $price->quantity_max): ?>
					<?php echo $price->quantity_min . ' - ' . $price->quantity_max ?>
				<?php elseif ($price->quantity_min): ?>
					<?php echo '> ' . $price->quantity_min ?>
				<?php elseif ($price->quantity_max): ?>
					<?php echo '< ' . $price->quantity_max ?>
				<?php endif; ?>
				</span>
				<span class="volume-price-amount">
					<?php
					$finalPrice = (float) $price->price * (1 + $tax);
					echo RedshopbHelperProduct::getProductFormattedPrice($finalPrice, $price->currency_id);
					?>
				</span>
			</div>
		<?php endforeach; ?>
	</div>
<?php endif;
