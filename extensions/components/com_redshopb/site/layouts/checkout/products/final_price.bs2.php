<?php
/**
 * @package     Aesir.E-Commerce.Admin
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

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
	'onRedshopbOrderHistoryFinalPrices',
	array(
		$items,
		$item,
		$currency,
		$item->quantity,
		$item->price,
		&$additionalPrices
	)
);

$finalPriceWithTaxes = null;

if (!$hidePrice && (float) $field->originalValue > 0)
{
	$productTax = RedshopbHelperTax::getProductTax(
		$item->product_id,
		$displayData['customerId'],
		$displayData['customerType']
	);

	if (!empty($productTax))
	{
		$value               = $field->originalValue + ($field->originalValue * $productTax);
		$finalPriceWithTaxes = RedshopbHelperProduct::getProductFormattedPrice($value, $currency);
	}
}
?>

<td class="field_<?php echo $field->fieldname ?>">
	<div class="input-prepend">
		<span class="add-on">
			<?php echo RedshopbHelperProduct::getCurrency($currency)->symbol; ?>
		</span>
		<?php echo ($isEmail) ? $field->value : $field->input; ?>
	</div>
	<span class="cart-product-additional-price-final">
		<?php echo $additionalPrices;?>
	</span>
	<?php if (!empty($finalPriceWithTaxes)): ?>
		<div class="field_price_with_tax">
			<small>
				<?php echo Text::_('COM_REDSHOPB_TAX_FINAL_PRICE') . ' ' . $finalPriceWithTaxes; ?>
			</small>
		</div>
	<?php endif;?>
</td>
