<?php
/**
 * @package     Aesir.E-Commerce.Template
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

extract($displayData);
$uri    = Uri::getInstance();
$domain = $uri->toString(array('scheme', 'host', 'port'));

$offerTable = RedshopbTable::getAdminInstance('Offer');
$offerTable->load($offer->get('id'));
$customerType = $offerTable->get('customer_type', '');
$customerId   = $offerTable->get('customer_id', 0);
?>
<table border="0" width="100%" cellspacing="0" cellpadding="5">
	<tbody>
	<tr>
		<td>
			<table border="0" width="100%" cellspacing="0" cellpadding="2">
				<tbody>
				<tr style="background-color: #cccccc;">
					<th align="left"><?php echo Text::_('COM_REDSHOPB_OFFER_EMAIL_INFORMATION'); ?></th>
				</tr>
				<tr>
					<td><?php echo Text::_('COM_REDSHOPB_OFFER_EMAIL_ID_LBL'); ?> : <?php echo $offer->get('id'); ?></td>
				</tr>
				<tr>
					<td><?php echo Text::_('COM_REDSHOPB_OFFER_EMAIL_EXPIRATION_TIME_LBL'); ?> : <?php echo $offer->get('expiration_date'); ?></td>
				</tr>
				<tr>
					<td><?php echo Text::_('COM_REDSHOPB_OFFER_EMAIL_STATUS_LBL'); ?> : <?php echo $offer->get('status'); ?></td>
				</tr>
				<tr>
					<td><a href="<?php echo $domain . RedshopbRoute::_('index.php?option=com_redshopb&task=myoffer.edit&id=' . $offer->get('id'), false); ?>">
							<?php echo Text::_('COM_REDSHOPB_OFFER_EMAIL_DETAIL_LINK_LBL'); ?>
						</a></td>
				</tr>
				</tbody>
			</table>
		</td>
	</tr>
	<tr>
		<td>
			<table border="0" width="100%" cellspacing="0" cellpadding="2">
				<tbody>
				<tr style="background-color: #cccccc;">
					<th align="left"><?php echo Text::_('COM_REDSHOPB_OFFER_EMAIL_DETAILS'); ?></th>
				</tr>
				<tr>
					<td>
						<table border="0" width="100%" cellspacing="2" cellpadding="2">
							<tbody>
							<tr>
								<th>&nbsp;</th>
								<th align="left"><?php echo Text::_('COM_REDSHOPB_MYOFFER_PRODUCTS_LIST_PRODUCT'); ?></th>
								<th align="left"><?php echo Text::_('COM_REDSHOPB_MYOFFER_PRODUCTS_PIECES'); ?></th>
								<th align="left"><?php echo Text::_('COM_REDSHOPB_MYOFFER_PRODUCTS_PRICE_PIECES'); ?></th>
								<th align="left"><?php echo Text::_('COM_REDSHOPB_OFFER_DISCOUNT_LBL'); ?></th>
								<th align="left"><?php echo Text::_('COM_REDSHOPB_MYOFFER_PRODUCTS_TOTAL_PRICE'); ?></th>
							</tr>
							<?php $productIds = array(); ?>

							<?php foreach ($offerItems as $offerItem): ?>
							<?php $productIds[]  = $offerItem->product_id; ?>
							<tr>
								<td>
									<?php $image = RedshopbHelperProduct::getProductImageThumbPath($offerItem->product_id, $offerItem->product_item_id);

									if ($image): ?>
									<img src="<?php echo $image; ?>" alt="<?php echo RedshopbHelperThumbnail::safeAlt($offerItem->product_name) ?>" />
									<?php endif; ?>
								</td>
								<td>
									<?php echo $offerItem->sku; ?>
									<br />
									<?php echo $offerItem->product_name; ?>
									<?php
									$customText = null;
									RFactory::getDispatcher()->trigger('onVanirProductCustomTextGetField', array($offerItem, &$customText, true));
									echo $customText;
									?>
								</td>
								<td><?php echo $offerItem->quantity; ?></td>
								<td><?php echo RedshopbHelperProduct::getProductFormattedPrice($offerItem->unit_price, $offer->get('currency_id')); ?></td>
								<td>
									<?php

									if ($offerItem->discount != 0)
									{
										switch ($offerItem->discount_type)
										{
											case 'total':
												echo RedshopbHelperProduct::getProductFormattedPrice($offerItem->discount, $offer->get('currency_id'));
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
								<td><?php echo RedshopbHelperProduct::getProductFormattedPrice($offerItem->total, $offer->get('currency_id'));?></td>
							</tr>
							<?php endforeach; ?>
							</tbody>
						</table>
					</td>
				</tr>
				<tr>
					<td> </td>
				</tr>
				<tr>
					<td>
						<table border="0" width="100%" cellspacing="2" cellpadding="2">
							<tbody>
							<?php

							$taxes    = RedshopbHelperTax::getProductsTaxRates($productIds, $customerId, $customerType);
							$allTaxes = array();
							$totalTax = 0;

							if (isset($taxes[0]))
							{
								$globalTaxes = $taxes[0];

								foreach ($globalTaxes as $globalTax)
								{
									$singleTax           = new stdClass;
									$singleTax->name     = $globalTax->name;
									$singleTax->tax_rate = $globalTax->tax_rate;
									$singleTax->tax      = $offer->get('total') * $globalTax->tax_rate;

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
							<tr>
								<td align="left">
									<strong><?php echo $allTaxesEntry->name ?></strong>
									<small>(<?php echo number_format(($allTaxesEntry->tax_rate * 100), 2, ',', '.') ?> %)</small> :
								</td>
								<td align="right">
									<strong><?php echo RedshopbHelperProduct::getProductFormattedPrice($allTaxesEntry->tax, $offer->get('currency_id')) ?></strong>
								</td>
							</tr>
							<?php endforeach; ?>

							<?php if ($offer->get('discount') != 0): ?>
								<tr align="left">
									<td align="left"><strong><?php echo Text::_('COM_REDSHOPB_OFFER_DISCOUNT_LBL'); ?> : </strong></td>
									<td align="right">
										<?php
										switch ($offer->get('discount_type'))
										{
											case 'total':
												echo RedshopbHelperProduct::getProductFormattedPrice($offer->get('discount'), $offer->get('currency_id'));
												break;
											case 'percent':
											default:
												echo $offer->get('discount') . '%';
												break;
										}
										?>
									</td>
								</tr>
							<?php endif; ?>
							<tr align="left">
								<td colspan="2" align="left">
									<hr/>
								</td>
							</tr>
							<tr align="left">
								<td align="left">
									<strong><?php echo Text::_('COM_REDSHOPB_MYOFFER_PRODUCTS_TOTAL_PRICE'); ?> :</strong>
								</td>
								<td align="right">
									<?php echo RedshopbHelperProduct::getProductFormattedPrice($offer->get('total') + $totalTax, $offer->get('currency_id'));?>
								</td>
							</tr>
							<tr align="left">
								<td colspan="2" align="left">
									<hr/>
									<br/>
									<hr/>
								</td>
							</tr>
							</tbody>
						</table>
					</td>
				</tr>
				</tbody>
			</table>
		</td>
	</tr>
	</tbody>
</table>
