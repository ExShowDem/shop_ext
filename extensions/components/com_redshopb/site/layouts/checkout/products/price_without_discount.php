<?php
/**
 * @package     Aesir.E-Commerce.Admin
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

$items     = $displayData['items'];
$item      = $displayData['item'];
$field     = $displayData['field'];
$currency  = $displayData['currency'];
$hidePrice = $displayData['hidePrice'];
$isEmail   = $displayData['isEmail'];

if ($hidePrice)
{
	$field->originalValue = 0;
	$field->value         = RedshopbHelperProduct::getProductFormattedPrice(0, $currency, false);
}
else
{
	$field->originalValue = $field->value;
	$field->value         = RedshopbHelperProduct::getProductFormattedPrice($field->value, $currency, false);
}

$additionalPrices = '';
$dispatcher       = RFactory::getDispatcher();
$dispatcher->trigger(
	'onRedshopbOrderHistoryPrices',
	array(
		$items,
		$item,
		$currency,
		&$additionalPrices
	)
);

?>

<td class="field_<?php echo $field->fieldname ?>">
	<?php echo ($isEmail) ? $field->value : $field->input; ?>

	<?php if (!empty($item->accessories)): ?>
		<span class="accessory-list" style="margin-top: 0px;">
			<?php // @todo Tables inside of spans are not valid markup ?>
			<table class="table table-condensed table-bordered">
				<tbody>
				<?php foreach ($item->accessories as $accessory): ?>
					<?php $hideCollection = !empty($accessory['hide_on_collection']);?>
					<?php $hasPrice       = ($accessory['price'] > 0);?>

					<?php if (!$hideCollection || $hasPrice):?>
					<tr>
						<td>
							<small>
								+&nbsp;<?php echo RedshopbHelperProduct::getProductFormattedPrice($accessory['price'], $currency, false);
								echo (array_key_exists('quantity', $accessory) && $accessory['quantity'] > 1 ? '&nbsp;x&nbsp;' . $accessory['quantity'] : ''); ?>
								(<?php echo $accessory['sku']; ?>)
							</small>
						</td>
					</tr>
					<?php endif; ?>
				<?php endforeach; ?>
				</tbody>
			</table>
		</span>
	<?php endif;?>

	<span class="cart-product-additional-price">
		<?php echo $additionalPrices;?>
	</span>
</td>


