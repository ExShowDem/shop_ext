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

extract($displayData);

$offerTable = RedshopbTable::getAdminInstance('Offer');
$offerTable->load($item->id);
$customerType = $offerTable->get('customer_type', '');
$customerId   = $offerTable->get('customer_id', 0);
?>
<h1 style="text-align:center;"><?php echo RedshopbHelperOffers::getOfferName($item->id);?></h1>
<hr/>
<div>
		<div>
			<table style="width: 100%;">
				<thead>
				<tr>
					<th style="text-align: left;"><?php echo Text::_('COM_REDSHOPB_MYOFFER_PRODUCTS_LIST_PRODUCT'); ?></th>
					<th style="text-align: left;"><?php echo Text::_('COM_REDSHOPB_MYOFFER_PRODUCTS_PIECES'); ?></th>
					<th style="text-align: left;"><?php echo Text::_('COM_REDSHOPB_MYOFFER_PRODUCTS_PRICE_PIECES'); ?></th>
					<th style="text-align: left;"><?php echo Text::_('COM_REDSHOPB_OFFER_DISCOUNT_LBL'); ?></th>
					<th style="text-align: left;"><?php echo Text::_('COM_REDSHOPB_MYOFFER_PRODUCTS_TOTAL_PRICE'); ?></th>
				</tr>
				</thead>
				<tbody>
				<?php $productIds = array(); ?>

				<?php foreach ($offerItems as $i => $offerItem): ?>
				<?php $productIds[] = $offerItem->product_id; ?>
				<tr id="product-row-<?php echo $offerItem->product_id; ?>">
					<td style="text-align: left;">
						<div class="product-sku"><?php echo $offerItem->sku; ?></div>
						<div class="product-name"><?php echo $offerItem->product_name; ?></div>
					</td>
					<td style="text-align: left;">
						<?php echo $offerItem->quantity; ?>
					</td>
					<td style="text-align: left;">
						<?php echo RedshopbHelperProduct::getProductFormattedPrice($offerItem->unit_price, $item->currency_id); ?>
					</td>
					<td style="text-align: left;">
						<?php

						if ($offerItem->discount != 0)
						{
							switch ($offerItem->discount_type)
							{
								case 'total':
									echo RedshopbHelperProduct::getProductFormattedPrice($offerItem->discount, $item->currency_id);
									break;
								case 'percent':
								default:
									echo $offerItem->discount . '%';
									break;
							}
						}
						else
						{
							echo '-';
						}
						?>
					</td>
					<td style="text-align: left;">
						<?php echo RedshopbHelperProduct::getProductFormattedPrice($offerItem->total, $item->currency_id);?>
					</td>
				</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
		</div>
	<hr />
	<?php if ($item->id): ?>
		<div class="row-fluid">
			<div class="span6">

			</div>
			<div class="span6">
				<div class="products-vat" style="text-align: right;">
					<?php

					$taxes       = RedshopbHelperTax::getProductsTaxRates($productIds, $customerId, $customerType);
					$globalTaxes = $taxes[0];
					$allTaxes    = array();
					$totalTax    = 0;

					if ($globalTaxes)
					{
						foreach ($globalTaxes as $globalTax)
						{
							$singleTax           = new stdClass;
							$singleTax->name     = $globalTax->name;
							$singleTax->tax_rate = $globalTax->tax_rate;
							$singleTax->tax      = $item->total * $globalTax->tax_rate;

							$totalTax += (float) $singleTax->tax;

							$allTaxes[] = $singleTax;
						}
					}

					foreach ($offerItems as $offerItem)
					{
						if ($taxes[$offerItem->product_id])
						{
							$singleTax           = new stdClass;
							$singleTax->name     = Text::sprintf('COM_REDSHOPB_TAX_FROM_PRODUCT', $taxes[$offerItem->product_id]->name, $offerItem->product_name);
							$singleTax->tax_rate = $taxes[$offerItem->product_id]->tax_rate;
							$singleTax->tax      = $offerItem->total * $taxes[$offerItem->product_id]->tax_rate;

							$totalTax += (float) $singleTax->tax;

							$allTaxes[] = $singleTax;
						}
					}
					?>
					<?php foreach ($allTaxes as $allTaxesEntry): ?>
						<div>
							<strong><?php echo $allTaxesEntry->name ?></strong>
							<small>(<?php echo number_format(($allTaxesEntry->tax_rate * 100), 2, ',', '.') ?> %)</small>
							: <strong><?php echo RedshopbHelperProduct::getProductFormattedPrice($allTaxesEntry->tax, $item->currency_id) ?></strong>
						</div>
					<?php endforeach; ?>
				</div>
				<br/>
				<?php if ($item->discount != 0): ?>
					<div class="products-vat" style="text-align: right;">
						<?php echo Text::_('COM_REDSHOPB_OFFER_DISCOUNT_LBL'); ?>: <?php

						switch ($item->discount_type)
						{
							case 'total':
								echo RedshopbHelperProduct::getProductFormattedPrice($item->discount, $item->currency_id);
								break;
							case 'percent':
							default:
								echo $item->discount . '%';
								break;
						}
						?>
					</div>
					<br/>
				<?php endif; ?>
				<div class="products-total" style="text-align: right;">
					<?php echo Text::_('COM_REDSHOPB_MYOFFER_PRODUCTS_TOTAL_PRICE'); ?>: <?php echo RedshopbHelperProduct::getProductFormattedPrice($item->total + $totalTax, $item->currency_id);?>
				</div>
			</div>
		</div>
		<br/>
		<div class="clear"></div>
	<?php endif; ?>
</div>
