<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Views
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

$action = RedshopbRoute::_('index.php?option=com_redshopb&view=myoffer');
$isNew  = (int) $this->item->id <= 0;

// HTML helpers
HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('vnrbootstrap.tooltip');
HTMLHelper::_('rjquery.chosen', 'select');
HTMLHelper::_('behavior.modal');

echo RedshopbBrowserBreadcrumbs::renderBreadcrumbs();
?>
<div class="redshopb-myoffer">
	<form action="<?php echo $action;
?>" method="post" name="adminForm" id="adminForm" class="form-horizontal" >
		<?php // @TODO: Currently work on generate row data same as "shop.cart" layout ?>
		<?php if (!empty($this->offerItems)): ?>
		<div class="offer-product-wrapper">
			<h3><?php echo RedshopbHelperOffers::getOfferName($this->item->id);?></h3>
			<div class="pull-right">
				<a href="javascript:void(0)" onclick="Joomla.submitbutton('myoffer.printPDF', 'adminForm')" class="btn btn-default">
					<i class="icon-print"></i>&nbsp;<?php echo Text::_('COM_REDSHOPB_PRINT');?>
				</a>
			</div>
			<table class="offer-products table table-striped table-hover" id="table-offer-products">
				<thead>
					<tr>
						<th>&nbsp;</th>
						<th><?php echo Text::_('COM_REDSHOPB_MYOFFER_PRODUCTS_LIST_PRODUCT'); ?></th>
						<th><?php echo Text::_('COM_REDSHOPB_MYOFFER_PRODUCTS_PIECES'); ?></th>
						<th><?php echo Text::_('COM_REDSHOPB_MYOFFER_PRODUCTS_PRICE_PIECES'); ?></th>
						<th><?php echo Text::_('COM_REDSHOPB_OFFER_DISCOUNT_LBL'); ?></th>
						<th><?php echo Text::_('COM_REDSHOPB_MYOFFER_PRODUCTS_TOTAL_PRICE'); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php $productIds = array(); ?>

					<?php foreach ($this->offerItems as $i => $offerItem): ?>
					<?php $productIds[] = $offerItem->product_id; ?>
					<tr id="product-row-<?php echo $offerItem->product_id; ?>">
						<td>
							<?php $image = RedshopbHelperProduct::getProductImageThumbPath($offerItem->product_id, $offerItem->product_item_id);

							if ($image): ?>
								<img src="<?php echo $image; ?>" />
							<?php endif; ?>
						</td>
						<td>
							<div class="product-sku"><?php echo $offerItem->sku; ?></div>
							<div class="product-name"><?php echo $offerItem->product_name; ?></div>
							<?php
							$customText = null;
							RFactory::getDispatcher()->trigger('onVanirProductCustomTextGetField', array($offerItem, &$customText, true));
							echo $customText;
							?>
						</td>
						<td>
						<?php echo $offerItem->quantity; ?>
						</td>
						<td>
						<?php echo RedshopbHelperProduct::getProductFormattedPrice($offerItem->unit_price, $this->item->currency_id); ?>
						</td>
						<td>
							<?php

							if ($offerItem->discount != 0)
							{
								switch ($offerItem->discount_type)
								{
									case 'total':
										echo RedshopbHelperProduct::getProductFormattedPrice($offerItem->discount, $this->item->currency_id);
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
						<td>
						<?php echo RedshopbHelperProduct::getProductFormattedPrice($offerItem->total, $this->item->currency_id);?>
						</td>
					</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
		<?php endif; ?>
		<hr />
		<?php if ($this->item->id): ?>
		<div class="row-fluid">
			<div class="span6">
				<div class="control-group">
					<div class="control-label disablePadding">
						<?php echo Text::_('COM_REDSHOPB_MYOFFERS_STATUS_LABEL'); ?>
					</div>
					<div class="controls">
						<?php echo RedshopbHelperOffers::getColorForStatus($this->item->status);?>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label disablePadding">
						<?php echo Text::_('COM_REDSHOPB_OFFER_EXPIRATION_DATE_LABEL'); ?>
					</div>
					<div class="controls">
						<?php
						if ($this->item->expiration_date == '0000-00-00 00:00:00')
						{
							echo Text::_('COM_REDSHOPB_OFFER_EXPIRATION_DATE_NEVER');
						}
						else
						{
							echo HTMLHelper::_('date', $this->item->expiration_date, Text::_('DATE_FORMAT_LC4'));
							;
						}
						?>
					</div>
				</div>
			</div>
			<div class="span6">
				<div class="products-vat pull-right">
					<?php

					$taxes       = RedshopbHelperTax::getProductsTaxRates($productIds, $this->customerId, $this->customerType);
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
							$singleTax->tax      = $this->item->total * $globalTax->tax_rate;

							$totalTax += (float) $singleTax->tax;

							$allTaxes[] = $singleTax;
						}
					}

					foreach ($this->offerItems as $offerItem)
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
							: <strong><?php echo RedshopbHelperProduct::getProductFormattedPrice($allTaxesEntry->tax, $this->item->currency_id) ?></strong>
						</div>
					<?php endforeach; ?>
				</div>
				<br/>
				<?php if ($this->item->discount != 0): ?>
				<div class="products-vat pull-right">
					<?php echo Text::_('COM_REDSHOPB_OFFER_DISCOUNT_LBL'); ?>: <?php

					switch ($this->item->discount_type)
					{
						case 'total':
							echo RedshopbHelperProduct::getProductFormattedPrice($this->item->discount, $this->item->currency_id);
							break;
						case 'percent':
						default:
							echo $this->item->discount . '%';
							break;
					}
					?>
				</div>
					<br/>
				<?php endif; ?>
				<div class="products-total pull-right">
					<?php echo Text::_('COM_REDSHOPB_MYOFFER_PRODUCTS_TOTAL_PRICE'); ?>: <?php echo RedshopbHelperProduct::getProductFormattedPrice($this->item->total + $totalTax, $this->item->currency_id);?>
				</div>
			</div>
		</div>
		<?php endif; ?>

	<?php if ($this->item->id && !in_array($this->item->status, array('rejected', 'requested', 'created', 'ordered'))): ?>
	<br/>
	<div class="clear"></div>
	<div class="pull-right">
		<?php if (in_array($this->item->status, array('sent'))): ?>
			<a class="btn btn-success" href="#myOfferModal"  data-toggle="modal" role="button" data-source="<?php
			echo $this->item->id; ?>">
				<i class="icon-thumbs-up-alt"></i>&nbsp;<?php
				echo Text::_('COM_REDSHOPB_OFFER_ACCEPT_LBL'); ?>
			</a>
		<?php endif;

if (in_array($this->item->status, array('accepted'))):
?>
<button class="btn btn-default btn-checkout-offer" type="submit">
<?php echo Text::_('COM_REDSHOPB_OFFER_CHECK_OUT') ?>
</button>
<?php endif; ?>
		<a href="<?php echo RedshopbRoute::_('index.php?option=com_redshopb&view=myoffer&layout=addcomment&id=' . $this->item->id . '&tmpl=component'); ?>" class="modal btn btn-default ">
			<?php echo Text::_('COM_REDSHOPB_MYOFFERS_REJECT'); ?>
		</a>
	</div>
	<?php endif; ?>
		<input type="hidden" name="option" value="com_redshopb">
		<input type="hidden" name="task" value="myoffer.checkoutCart">
		<input type="hidden" name="id" value="<?php echo $this->item->id ?>">
		<?php echo HTMLHelper::_('form.token'); ?>
	</form>
	<script type="text/javascript" language="javascript">
		jQuery(document).ready(function(){
			jQuery('[data-toggle="modal"]').on('click', function() {
				jQuery(jQuery(this).attr('href')).data('trigger', this);
			});
			jQuery('#myOfferModal').on('show.bs.modal', function () {
				var $invoker = jQuery(jQuery(this).data('trigger'));
				var source = $invoker.data('source');
				jQuery('#modalIdField').val(source);
			});
		});
	</script>
	<?php echo RHtml::_(
		'vnrbootstrap.renderModal', 'myOfferModal',
		array(
					'title' => Text::_('COM_REDSHOPB_OFFER_ADD_OFFER_TO_CART_LBL')
			),
		RedshopbLayoutHelper::render('myoffer.acceptform', array('return' => base64_encode(Uri::getInstance())))
	); ?>
</div>
