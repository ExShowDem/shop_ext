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
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Uri\Uri;

extract($displayData);
$return   = isset($displayData['return']) ? $displayData['return'] : null;
$document = Factory::getDocument();

HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('rdropdown.init');
HTMLHelper::_('vnrbootstrap.tooltip');
HTMLHelper::_('rjquery.chosen', 'select');

RedshopbHtml::loadFooTable();
$listOrder = $state->get('list.ordering');
$listDirn  = $state->get('list.direction');
$saveOrder = $listOrder == 'ordering';

$searchToolsOptions = array(
	"searchFieldSelector" => "#filter_search_products",
	"orderFieldSelector" => "#list_fullordering",
	"searchField" => "search_products",
	"limitFieldSelector" => "#list_product_limit",
	"activeOrder" => $listOrder,
	"activeDirection" => $listDirn,
	"formSelector" => ("#" . $formName),
	"filtersHidden" => (bool) empty($displayData['activeFilters'])
);
?>

<script type="text/javascript">
	(function ($) {
		$(document).ready(function () {
			$('#<?php echo $formName; ?>').searchtools(
				<?php echo json_encode($searchToolsOptions); ?>
			);
		});
	})(jQuery);
</script>
<div class="redshopb-offer-products">
	<form action="<?php echo $action; ?>" name="<?php echo $formName; ?>" class="adminForm" id="<?php echo $formName; ?>" method="post">
		<?php
		echo RedshopbLayoutHelper::render(
			'searchtools.default',
			array(
				'view' => (object) array(
					'filterForm' => $displayData['filter_form'],
					'activeFilters' => $displayData['activeFilters']
				),
				'options' => $searchToolsOptions
			)
		);
		?>
		<hr/>
		<?php if (empty($items)) : ?>
			<?php echo RedshopbLayoutHelper::render('common.nodata'); ?>
		<?php else : ?>
		<?php
		$hasProductItems = false;

		foreach ($items as $item)
		{
			if ((isset($item->product_item_id) && isset($item->product_item_sku))
				|| (isset($productItems) && array_key_exists($item->id, $productItems)))
			{
				$hasProductItems = true;
				break;
			}
		}
		?>
			<?php if ($displayData['button'] == 2) : ?>
			<div id="save-offer-items-toolbar" class="btn-toolbar">
				<div class="btn-group">
					<button
							class="btn btn-lg btn-success save-offer-items disabled"
							type="button">
						<i class="icon-save"></i>
						<?php echo Text::_('COM_REDSHOPB_OFFER_SAVE_OFFER_ITEMS') ?>
					</button>
				</div>
			</div>
			<div id="save-offer-items-message"></div>
			<?php endif; ?>

			<div class="redshopb-offer-products-table">
				<table
					class="table table-condensed table-striped table-hover footable js-redshopb-footable redshopb-footable removeBottomMarginForTableInputs"
					id="<?php echo $formName; ?>Table">
					<thead>
					<tr>
						<th><?php echo Text::_('COM_REDSHOPB_OFFER_ACTION'); ?></th>
						<th>
							<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_NAME', 'p.name', $listDirn, $listOrder, null, 'asc', '', null, $formName); ?>
							&nbsp;/&nbsp;<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_SKU', 'p.sku', $listDirn, $listOrder, null, 'asc', '', null, $formName); ?>

							<?php if ($hasProductItems): ?>
								&nbsp;/&nbsp;<?php echo Text::_('COM_REDSHOPB_OFFER_PRODUCT_ATTRIBUTES'); ?>
							<?php endif; ?>
						</th>
						<th><?php echo Text::_('COM_REDSHOPB_OFFER_PRICE'); ?></th>
						<th><?php echo Text::_('COM_REDSHOPB_OFFER_DISCOUNT_TYPE_LBL'); ?></th>
						<th><?php echo Text::_('COM_REDSHOPB_OFFER_DISCOUNT_LBL'); ?></th>
						<th><?php echo Text::_('COM_REDSHOPB_OFFER_QUANTITY'); ?></th>
						<th><?php echo Text::_('COM_REDSHOPB_OFFER_FINAL_PRICE'); ?></th>
					</tr>
					</thead>
					<?php if ($items): ?>
						<tbody>
						<?php foreach ($items as $i => $item): ?>
							<?php
							$canChange  = false;
							$canEdit    = 1;
							$canCheckin = 1;
							$thumb      = RedshopbHelperProduct::getProductImageThumbHtml($item->id);
							?>
							<tr id="product-row-<?php echo $item->id; ?>">
								<td>
									<?php if ($displayData['button'] == 1) : ?>
										<a class="offer-item-add btn btn-success hasTooltip" href="javascript:void(0);"
										   data-original-title="<?php echo Text::_('COM_REDSHOPB_OFFER_ADD'); ?>"
										   data-productid="<?php echo $item->id; ?>"
										   data-offerid="<?php echo $offerId; ?>">
											<i class="icon-file-text"></i>
										</a>
									<?php else : ?>
										<div class="btn-group">
											<a class="offer-item-remove btn btn-danger hasTooltip" href="javascript:void(0);"
											   data-original-title="<?php echo Text::_('COM_REDSHOPB_OFFER_REMOVE'); ?>"
											   data-productremoveid="<?php echo $item->id; ?>"
											   data-offerremoveid="<?php echo $offerId; ?>">
												<i class="icon-trash"></i>
											</a>
										</div>
									<?php endif;?>
									<div class="product-row-msg"></div>
								</td>
								<td>
									<?php
									if ($thumb != '') :
									?>
									<span class="hasTooltip"
										  data-original-title="<div class='thumb'><?php echo htmlspecialchars($thumb, ENT_COMPAT, 'UTF-8'); ?></div>">
									<?php
									endif;
									?>
									<?php echo $this->escape($item->name); ?>
									<?php
									if ($thumb != '') :
									?>
									</span>
									<?php
									endif;
									?>
									<br/>
									<i><?php echo $item->sku; ?></i>
									<?php
									if (isset($item->product_item_id) && isset($item->product_item_sku))
									{
										echo $item->product_item_sku
											. '<input type="hidden" value="' . $item->product_item_id
											. '" name="productItem_' . $item->id . '" class="productItem" />';
									}
									elseif (isset($productItems) && array_key_exists($item->id, $productItems))
									{
										$options   = array();
										$options[] = HTMLHelper::_('select.option', '', Text::_('JOPTION_SELECT_SKU'));

										foreach ($productItems[$item->id] as $column)
										{
											$options[] = (object) array('text' => $column->sku, 'value' => $column->pi_id);
										}

										?>
										<br/>
										<?php
										echo HTMLHelper::_(
											'select.genericlist', $options, 'productItem_' . $item->id,
											'class="dropdownPriceCondition productItem"',
											'value', 'text',
											$value = isset($item->selectedProductItemId) ? $item->selectedProductItemId : ''
										);
									} ?>
									<br/>
									<?php
									$offerItem                  = new stdClass;
									$offerItem->offer_id        = $offerId;
									$offerItem->product_id      = $item->id;
									$offerItem->product_item_id = isset($item->product_item_id) ? $item->product_item_id : null;

									if ($displayData['button'] === 1)
									{
										$inputHtml = null;
										RFactory::getDispatcher()->trigger('onVanirProductCustomTextGetInput', array($offerItem, &$inputHtml, true));
										echo $inputHtml;
									}
									elseif ($displayData['button'] === 2)
									{
										$customText = null;
										RFactory::getDispatcher()->trigger('onVanirProductCustomTextGetField', array($offerItem, &$customText, true));
										echo $customText;
									}
									?>
								</td>
								<?php
								$productPrice      = 0;
								$quantity          = 1;
								$minQuantityExists = false;

								// Check exists offer price
								if (isset($item->unit_price))
								{
									$productPrice = $item->unit_price;
								}

								// Check exists product item price
								elseif (isset($defaultProductItems) && array_key_exists($item->id, $defaultProductItems))
								{
									if (array_key_exists($item->id, $productItemPrices))
									{
										$productItem  = $defaultProductItems[$item->id];
										$productPrice = $productItemPrices[$item->id][$productItem]->price;
										$currency     = $productItemPrices[$item->id][$productItem]->currency_id;

										if (isset($productItemPrices[$item->id][$productItem]->quantity))
										{
											$quantity = $productItemPrices[$item->id][$productItem]->quantity;
										}
									}
								}

								// Check exists product price
								elseif (array_key_exists($item->id, $productPrices))
								{
									$productPrice = $productPrices[$item->id]->price;

									if (isset($productPrices[$item->id]->quantity))
									{
										$quantity = $productPrices[$item->id]->quantity;
									}
								}

								// Check exists offer quantity
								if (isset($item->quantity))
								{
									$quantity = $item->quantity;
								}

								$quantity = RedshopbHelperProduct::decimalFormat($quantity, $item->id);

								if (isset($item->total))
								{
									$finalPrice = $item->total;
								}
								else
								{
									$finalPrice = $productPrice;
								}
								?>
								<td class="productPrice_<?php echo $item->id; ?> productPrice verticalAlignTableInput">
									<?php echo RedshopbHelperProduct::getProductFormattedPrice((float) $productPrice, $currency); ?>
								</td>
								<td>
									<select id="productDiscountType_<?php echo $item->id; ?>"
											name="productDiscountType[<?php echo $item->id; ?>]"
											class="input-medium dropdownPriceCondition productDiscountType">
										<option <?php echo (isset($item->discount_type) && $item->discount_type == 'percent' ? 'selected="selected"' : ''); ?>
											value="percent"><?php echo Text::_('COM_REDSHOPB_OFFER_DISCOUNT_TYPE_PERCENTAGE'); ?></option>
										<option <?php echo (isset($item->discount_type) && $item->discount_type == 'total' ? 'selected="selected"' : ''); ?>
											value="total"><?php echo Text::_('COM_REDSHOPB_OFFER_DISCOUNT_TYPE_TOTAL'); ?></option>
									</select>
								</td>
								<td>
									<input type="text" class="input-small textPriceCondition productDiscount"
										   name="productDiscount_<?php echo $item->id; ?>" value="<?php echo (isset($item->discount) ? $item->discount : 0); ?>">
								</td>
								<td>
									<div class="input-append">
										<input type="text" class="input-small productQuantity"
											   name="productQuantity_<?php echo $item->id; ?>" value="<?php echo $quantity; ?>">
										<span class="add-on"><?php echo $item->unit_measure_code; ?></span>
									</div>
									<input type="hidden" class="productIdField" value="<?php echo $item->id; ?>">
									<input type="hidden" class="offerItemId"
										   value="<?php echo (isset($item->offer_item_id) ? $item->offer_item_id : ''); ?>">
								</td>
								<td class="productFinalPrice_<?php echo $item->id; ?> productFinalPrice verticalAlignTableInput">
									<?php echo RedshopbHelperProduct::getProductFormattedPrice((float) $finalPrice, $currency); ?>
								</td>
							</tr>
						<?php endforeach; ?>
						</tbody>
					<?php endif; ?>
				</table>
			</div>
			<div class="redshopb-offer-products-pagination">
				<?php echo $pagination->getPaginationLinks('tab.pagination.links', array('showLimitBox' => false)); ?>
			</div>
		<?php endif; ?>

		<div>
			<input type="hidden" name="task" value="offer.saveModelState">
			<input type="hidden" name="context" value="<?php echo $context; ?>"/>

			<?php if ($return) : ?>
				<input type="hidden" name="return" value="<?php echo $return ?>">
			<?php endif; ?>
			<input type="hidden" name="layout_filter[offer_id]" value="<?php echo $offerId; ?>">
			<input type="hidden" name="jform[offer_id]" value="<?php echo $offerId; ?>">
			<input type="hidden" name="from_product" value="1">
			<input type="hidden" name="boxchecked" value="0">
			<div id="token">
			<?php echo HTMLHelper::_('form.token'); ?>
			</div>
		</div>
	</form>
</div>
