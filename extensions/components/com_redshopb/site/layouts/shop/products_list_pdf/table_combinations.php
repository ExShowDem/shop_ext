<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Layouts.Shop.Products_List_PDF
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

$item         = $displayData['item'];
$part         = isset($displayData['part']) ? $displayData['part'] : 0;
$maxCountCols = 13;

if ($part == 0)
{
	$temp        = array_chunk($item->staticTypes, 12);
	$staticTypes = $temp[0];
}
else
{
	$itemStaticTypes = $item->staticTypes;
	$temp            = array();

	foreach ($itemStaticTypes as $key => $staticType)
	{
		if ($key >= 12)
		{
			$temp[] = $staticType;
		}
	}

	$parts       = array_chunk($temp, 12);
	$staticTypes = $parts[$part - 1];
}

$issetDynamicVariants = $item->issetDynamicVariants;
$issetItems           = $item->issetItems;
$currency             = $item->currency;
$dynamicTypes         = $item->dynamicTypes;
$showStockAs          = isset($item->showStockAs) ? $item->showStockAs : '';
$headColumnWidth      = 20;
$columnWidth          = (100 - $headColumnWidth) / ($maxCountCols - 1);
$columnHeight         = $columnWidth;
$prices               = $item->prices;
?>

<table style="border: 1px solid #808080; border-collapse: collapse;">
	<thead>
	<tr style="min-height: <?php echo $columnHeight; ?>%;">
		<th width="<?php echo $headColumnWidth ?>%" style="text-align: right; border: 1px solid #808080;">
			<?php echo $staticTypes[0]->name; ?>:
		</th>
		<?php
		$noStaticType = 0;

		foreach ($staticTypes as $staticType) :	?>
			<th width="<?php echo $columnWidth ?>%"  style="border: 1px solid #808080;">
				<?php echo RedshopbHelperCollection::getProductItemValueFromType($staticType->type, $staticType); ?>
			</th>
			<?php
			$noStaticType++;
		endforeach;

		while ($noStaticType < $maxCountCols - 1) : ?>
			<th style="border: 1px solid #808080; width: <?php echo $columnWidth ?>%">
				&nbsp;
			</th>
			<?php
			$noStaticType++;
		endwhile;?>
	</tr>
	</thead>
	<tbody>
	<?php
	foreach ($issetDynamicVariants as $issetDynamicVariant) :
		$dynamicVariants = explode('_', $issetDynamicVariant);
		unset($dynamicVariants[0]);
		$issetDynamicVariant = implode('_', $dynamicVariants);

		$itemName = Text::_('COM_REDSHOPB_SHOP_QTY');

		if ($dynamicVariants)
		{
			$itemNameArray = array();

			foreach ($dynamicVariants as $dynamicVariant)
			{
				$itemNameArray[] = RedshopbHelperCollection::getProductItemValueFromType(
					$dynamicTypes[$dynamicVariant]->type,
					$dynamicTypes[$dynamicVariant]
				);
			}

			$itemName = implode($itemNameArray, ' | ');
		}

		if (count($staticTypes) > 0) :?>
			<tr style="min-height: <?php echo $columnHeight; ?>%;">
				<td style="border: 1px solid #808080;">
					<strong>
						<?php echo $itemName . ':'; ?>
					</strong>
				</td>
				<?php
				$noStaticType = 0;

				// Searches for prices
				$hasPrices = false;

				foreach ($staticTypes as $staticType)
				{
					if (isset($issetItems[$staticType->id . '_' . $issetDynamicVariant]))
					{
						$issetItem = $issetItems[$staticType->id . '_' . $issetDynamicVariant];
					}
					elseif (isset($issetItems[$staticType->id]))
					{
						$issetItem = $issetItems[$staticType->id];
					}

					if (isset($issetItem) && !is_null($issetItem))
					{
						if (isset($prices))
						{
							if ((float) $prices[$issetItem->id]->price)
							{
								$hasPrices = true;
							}
						}
					}
				}

				foreach ($staticTypes as $staticType) :
					$amount    = 0;
					$issetItem = null;
					$priceText = '';

					if (isset($issetItems[$staticType->id . '_' . $issetDynamicVariant]))
					{
						$issetItem = $issetItems[$staticType->id . '_' . $issetDynamicVariant];
					}
					elseif (isset($issetItems[$staticType->id]))
					{
						$issetItem = $issetItems[$staticType->id];
					}

					$class     = '';
					$iconStock = '';
					$price     = 0;

					if (!is_null($issetItem)) :
						if (isset($prices[$issetItem->id]))
						{
							$item     = $prices[$issetItem->id];
							$currency = $item->currency_id;
							$price    = $item->price;
							$outlet   = (isset($item->outlet)) ? $item->outlet : false;
						}

						if ($price > 0)
						{
							if (isset($outlet) && !empty($outlet))
							{
								$priceText = '<b>' . RedshopbHelperProduct::getProductFormattedPrice($price, $currency) . '</b>' .
									'<br /><span style="text-decoration: line-through;">' . RedshopbHelperProduct::getProductFormattedPrice($item->oldPrice, $currency) . '</span>';
							}
							else
							{
								$priceText = RedshopbHelperProduct::getProductFormattedPrice($price, $currency);
							}
						}

						if (isset($issetItems[$staticType->id . '_' . $issetDynamicVariant]))
						{
							$amount = $issetItems[$staticType->id . '_' . $issetDynamicVariant]->amount;
							$amount = $amount < 0 ? 0 : $amount;

							if ($amount > 0)
							{
								$class = 'inStock';
							}
						}
						elseif (isset($issetItems[$staticType->id]))
						{
							$amount = $issetItems[$staticType->id]->amount;
							$amount = $amount < 0 ? 0 : $amount;

							if ($amount > 0)
							{
								$class = 'inStock';
							}
						}

						$colorAmount = RedshopbHelperProduct::getColorAmount($issetItem);
						$iconStock   = 'ok';

						switch ($colorAmount)
						{
							case ' amountLessZero':
								$iconStock = 'remove';
								break;
							case ' amountMoreZeroLessLower':
								$iconStock = 'warning-sign';
								break;
						}

						$class .= $colorAmount;
						?>
						<td style="border: 1px solid #000;">
							<?php if (!empty($showStockAs) && $iconStock != '' && $class != '') : ?>
								<?php if ($showStockAs == 'actual_stock') : ?>
									<div><?php echo Text::_('COM_REDSHOPB_SHOP_IN_STOCK') ?>: <?php echo $amount ?></div>
								<?php endif; ?>

								<?php if ($showStockAs == 'color_codes') : ?>
									<i class="icon-<?php echo $iconStock ?> <?php echo $class ?>"></i>
									<br />
								<?php endif; ?>
							<?php endif; ?>

							<?php if ($hasPrices) : ?>
								<div class="price">
									<?php if ($priceText != '') : ?>
										<?php echo $priceText ?>
									<?php endif; ?>
								</div>
							<?php endif; ?>
						</td>
					<?php else :?>
						<td>&nbsp;</td>
					<?php endif;
					$noStaticType++;
				endforeach;

				while ($noStaticType < $maxCountCols - 1) :	?>
					<td>&nbsp;</td>
					<?php
					$noStaticType++;
				endwhile;?>
			</tr>
		<?php
		endif;
	endforeach;
	?>
	</tbody>
</table>
