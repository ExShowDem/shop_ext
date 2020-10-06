<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Views
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Plugin\PluginHelper;

RHelperAsset::load('redshopb.shop.js', 'com_redshopb');
RHelperAsset::load('redshopb.quickorder.js', 'com_redshopb');

if (PluginHelper::isEnabled('vanir', 'product_custom_text'))
{
	RHelperAsset::load('script.js', 'plg_vanir_product_custom_text');
}

RedshopbHtml::loadFooTable();

$app          = Factory::getApplication();
$customerType = $app->getUserState('shop.customer_type', '');
$customerId   = $app->getUserState('shop.customer_id', 0);
$action       = RedshopbRoute::_('index.php?option=com_redshopb&view=quick_order');
$counter      = 1;

echo RedshopbBrowserBreadcrumbs::renderBreadcrumbs();
?>

<div class="redshopb-quickorder">
	<h1><?php echo Text::_('COM_REDSHOPB_QUICK_ORDER') ?></h1>
	<div class="well quickOrderDiv">
		<h3><?php echo Text::_('COM_REDSHOPB_QUICK_ORDER') ?></h3>
		<?php
		if (true === $this->enabled)
		{
			echo RedshopbLayoutHelper::render('quickorder.tool', array('jsCallback' => 'redshopbQuickOrderAddProduct'));
		}
		else
		{
			echo RedshopbLayoutHelper::render('notification.warning', array(
					'message' => Text::_('COM_REDSHOPB_QUICK_ORDER_IMPERSONATE_TO_USE')
				)
			);
		}
		?>
	</div>

	<?php if (true === $this->enabled) : ?>
	<!-- Data part -->
	<div class="redshopb-quickorder-data">
		<form class="adminForm redshopb-quickorder-form" action="<?php echo $action ?>" method="post" id="redshopb-quickorder-form">
			<table class="table table-striped table-hover footable js-redshopb-footable redshopb-footable toggle-circle-filled"
				id="redshopb-quickorder-table">
				<thead>
					<tr>
						<th width="30%">
							<?php echo Text::_('COM_REDSHOPB_PRODUCT_NAME') ?>
						</th>
						<th width="9%">
							<?php echo Text::_('COM_REDSHOPB_PRODUCT_UOM_PCS') ?>
						</th>
						<th width="30%">
							<?php echo Text::_('COM_REDSHOPB_DISCOUNT_TITLE') ?>
						</th>
						<th width="30%">
							<?php echo Text::_('COM_REDSHOPB_ORDER_TOTAL') ?>
						</th>
						<th width="1%">
							&nbsp;
						</th>
					</tr>
				</thead>
				<tbody>
				<?php if (!empty($this->cartItems)) : ?>
					<?php $itemForChecks = RedshopbHelperCart::cartFieldsForCheck(); ?>

				<?php foreach ($this->cartItems as $item) : ?>
						<?php
						$id      = isset($item['id']) ? $item['id'] : $item['productId'];
						$sku     = isset($item['product_sku']) ? $item['product_sku'] : $item['sku'];
						$nameKey = '';

						foreach ($itemForChecks as $itemForCheck)
						{
							$nameKey .= '_';

							if (array_key_exists($itemForCheck, $item))
							{
								$nameKey .= $item[$itemForCheck];
							}
						}

						$nameKey .= '_' . $counter;
						$counter++;
						?>
					<tr id="quickorder-product-<?php echo $id; ?>">
						<td>
							<span class="quickorder-product-name"><?php echo $item['name']; ?></span><br>
							<span class="quickorder-product-sku"><?php echo $sku; ?></span>
							<?php
							$customText = null;
							RFactory::getDispatcher()->trigger('onVanirProductCustomTextGetField', array($item, &$customText));
							echo $customText;
							?>
						</td>
						<td>
							<span class="quickorder-product-quantity"><?php echo $item['quantity']; ?></span>
						</td>
						<td>
							<?php $discount = (float) ($item['quantity'] * $item['price'] * ($item['discount'] / 100)); ?>
							<span
									id="discount_price_<?php echo $id; ?>"
									data-real="<?php echo $discount;?>"
							>
								<?php echo RedshopbHelperProduct::getProductFormattedPrice($discount, $item['currency']); ?>
							</span>
						</td>
						<td>
							<?php $price = (float) ($item['quantity'] * $item['price']); ?>
							<span
									data-currency="<?php echo $item['currency']; ?>"
									class="total-price"
									data-real="<?php echo $price; ?>"
									id="total_price_<?php echo $id; ?>"
							>
								<?php echo RedshopbHelperProduct::getProductFormattedPrice($price, $item['currency']); ?>
							</span>
						</td>
						<td>
							<button class="btn btn-mini shopping-cart-remove" type="button" data-tr="<?php echo $id; ?>"
									name="shop-cart-product-remove_<?php echo $customerType; ?>_<?php echo $customerId; ?><?php echo $nameKey; ?>"
									data-cart_item_hash="<?php echo $item['hash'] ?>">
							<i class="icon-trash"></i>
							</button>
						</td>
					</tr>
				<?php endforeach; ?>
				<?php endif; ?>
				</tbody>
			</table>
			<hr />
			<div class="row-fluid">
				<div class="span6">
					<!-- Comments part -->
					<div class="redshopb-quickorder-comments">
						<textarea name="comment" placeholder="<?php echo Text::_('COM_REDSHOPB_QUICK_ORDER_COMMENT') ?>"
								  class="textarea input-block-level" rows="5"></textarea>
					</div>
				</div>
				<div class="span6">
					<div class="row-fluid">
						<div class="span6 redshopb-quickorder-vat-title">
							<strong><?php echo Text::_('COM_REDSHOPB_QUICK_ORDER_VAT'); ?></strong>
						</div>
						<div class="span6">
							<div id="redshopb-quickorder-vat">
								<?php foreach ($this->taxes as $currency => $tax) : ?>
									<?php foreach ($tax as $name => $taxData) : ?>
										<div id="vat_<?php echo $currency;?>" class="pull-right">
											<?php echo RedshopbHelperProduct::getProductFormattedPrice($taxData['tax'], $currency); ?>
										</div>
									<?php endforeach; ?>
								<?php endforeach; ?>
							</div>
						</div>
					</div>
					<div class="row-fluid">
						<div class="span6 redshopb-quickorder-totalprice-title">
							<strong><?php echo Text::_('COM_REDSHOPB_ORDER_TOTAL_PRICE'); ?></strong>
						</div>
						<div class="span6">
							<div id="redshopb-quickorder-totalprice">
								<?php foreach ($this->totals as $currency => $total) :?>
								<div id="total_price_<?php echo $currency;?>" class="pull-right">
									<?php echo RedshopbHelperProduct::getProductFormattedPrice($total, $currency); ?>
								</div>
								<?php endforeach; ?>
							</div>
						</div>
					</div>
					<!-- Order button -->
					<div class="redshopb-quickorder-buttons">
						<button class="btn btn-success pull-right btn-default" id="redshopb-quickorder-button"><i class="icon icon-shopping-cart">
							</i> <?php echo Text::_('COM_REDSHOPB_SHOP_CHECKOUT') ?>
						</button>
					</div>
				</div>
			</div>
			<div>
				<input type="hidden" name="task" value="quick_order.order" />
				<input type="hidden" name="<?php echo Session::getFormToken();?>" value="1" id="redshopb-quickorder-tool-token"/>
			</div>
		</form>
	</div>
	<?php endif; ?>
</div>
