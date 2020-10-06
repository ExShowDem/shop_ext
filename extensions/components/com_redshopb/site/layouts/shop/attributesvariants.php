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

$issetDynamicVariants = $displayData['issetDynamicVariants'];
$collectionId         = $displayData['collectionId'];
$productId            = $displayData['productId'];
$staticTypes          = $displayData['staticTypes'];
$issetItems           = $displayData['issetItems'];
$dynamicTypes         = $displayData['dynamicTypes'];
$displayProductImages = $displayData['displayProductImages'];
$productImages        = $displayData['productImages'];
$prices               = $displayData['prices'];
$displayAccessories   = $displayData['displayAccessories'];
$accessories          = $displayData['accessories'];
$showStockAs          = ($displayData['showStockAs'] == '') ? 'actual_stock' : $displayData['showStockAs'];
$currency             = $displayData['currency'];
$dropDownSelected     = isset($displayData['dropDownSelected']) ? (int) $displayData['dropDownSelected'] : 0;
$displayWashCareLink  = isset($displayData['displayWashCareLink']) ? $displayData['displayWashCareLink'] : false;
$customerType         = $displayData['customerType'];
$customerId           = $displayData['customerId'];
$thumbWidth           = (isset($displayData['thumbWidth']) ? $displayData['thumbWidth'] : 144);
$thumbHeight          = (isset($displayData['thumbHeight']) ? $displayData['thumbHeight'] : 144);
$placeOrderPermission = (isset($displayData['placeOrderPermission']) ? $displayData['placeOrderPermission'] : true);
$thumbStyle           = (isset($displayData['thumbStyle']) ? $displayData['thumbStyle'] : 'images');
$availableThumbStyles = array('images', 'imagescarouselnavigation');

if (isset($prices[$productId]))
{
	$prices = $prices[$productId];
}

if (!in_array($thumbStyle, $availableThumbStyles))
{
	$thumbStyle = 'images';
}

$staticTypesProduct = array();

$headColumnWidth = 20;
$variantsPages   = false;

$maxCountCols       = (isset($maxCountCols) ? $maxCountCols : 10);
$maxTabletCountCols = (isset($maxTabletCountCols) ? $maxTabletCountCols : 5);

if (isset($staticTypes[$productId])) :
	$staticTypesProduct = array_chunk($staticTypes[$productId], $maxCountCols - 1);

	$columnWidth = (100 - $headColumnWidth) / ($maxCountCols - 1);

	if (count($staticTypesProduct) > 1) :
		$columnWidth   = (100 - $headColumnWidth) / ($maxCountCols + 1);
		$variantsPages = true;
		?>

		<div id="carousel-variants-<?php echo $collectionId; ?>-<?php echo $productId; ?>" class="carousel slide carousel-variants" data-pause="true" data-interval="0">
		<div class="carousel-indicators-wrapper">
			<div class="pull-left carousel-indicators-title">
				<?php echo Text::_('COM_REDSHOPB_SHOP_OTHER_COMBINATIONS'); ?>:
			</div>
			<ol class="carousel-indicators">
				<?php
				$noStaticTypeGroup = 0;

				foreach ($staticTypesProduct as $staticTypeGroup):
					?>
					<li data-target="#carousel-variants-<?php echo $collectionId; ?>-<?php echo $productId; ?>"
						data-slide-to="<?php echo $noStaticTypeGroup; ?>"<?php if (!$noStaticTypeGroup) : ?> class="active"<?php
									   endif; ?>><?php echo ($noStaticTypeGroup + 1); ?></li>
					<?php
					$noStaticTypeGroup++;
				endforeach;
				?>
			</ol>
		</div>

		<div class="clear">
		</div>

		<div class="carousel-inner">
	<?php
	endif;

	$noStaticTypeGroup = 0;

	foreach ($staticTypesProduct as $i => $staticTypeGroup):
		if ($variantsPages) :
			?>
			<div class="item<?php if (!$noStaticTypeGroup) : ?> active<?php
							endif; ?>">
		<?php
		endif;
		?>

		<?php
		if ($variantsPages) :
			?>
			<table class="variants-table-navigator">
				<tbody>
				<tr>
					<td width="<?php echo $headColumnWidth ?>" style="text-align: center">
						&nbsp;
					</td>
					<td style="text-align: left" data-hide="phone">
						<?php
						if ($i > 0) :
							?>
							<a href="#carousel-variants-<?php echo $collectionId; ?>-<?php echo $productId; ?>" data-slide="prev"><i
										class="icon-arrow-left"></i></a>
						<?php
						else :
							?>
							&nbsp;
						<?php
						endif;
						?>
					</td>
					<td style="text-align: right">
						<?php
						if ($i < count($staticTypesProduct) - 1) :
							?>
							<a href="#carousel-variants-<?php echo $collectionId; ?>-<?php echo $productId; ?>" data-slide="next"><i
										class="icon-arrow-right"></i></a>
						<?php
						else :
							?>
							&nbsp;
						<?php
						endif;
						?>
					</td>
				</tr>
				</tbody>
			</table>
		<?php
		endif;
		?>

		<table class="variants-table footable table toggle-circle toggle-medium"
			   id="variants-table<?php echo (isset($collectionId) ? '-' . $collectionId : ''); ?>-<?php echo $productId; ?>-<?php echo $noStaticTypeGroup; ?>"
			   data-page-navigation=".table-pagination">
			<thead>
			<tr>
				<th data-sort-ignore="true" data-toggle="true" width="<?php echo $headColumnWidth ?>%" style="text-align: right">
					<?php echo current($staticTypes[$productId])->name; ?>:
				</th>
				<?php
				$noStaticType = 0;

				foreach ($staticTypeGroup as $staticType) :
					?>
					<th data-sort-ignore="true" data-hide="phone<?php if ($noStaticType > $maxTabletCountCols) : ?>,tablet<?php
																endif; ?>"
						class="text-center" width="<?php echo $columnWidth ?>%">
						<?php echo RedshopbHelperCollection::getProductItemValueFromType($staticType->type_id, $staticType); ?>
					</th>
					<?php
					$noStaticType++;
				endforeach;

				while ($noStaticType < $maxCountCols - 1) :
					?>
					<th data-sort-ignore="true" class="text-center" width="<?php echo $columnWidth ?>%">
						&nbsp;
					</th>
					<?php
					$noStaticType++;
				endwhile;

				?>
			</tr>
			</thead>
			<?php
			if (isset($issetDynamicVariants[$productId]) && !is_null($issetDynamicVariants[$productId])) :
				?>
				<tbody>
				<?php
				foreach ($issetDynamicVariants[$productId] as $issetDynamicVariant) :
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
								$dynamicTypes[$productId][$dynamicVariant]->type_id,
								$dynamicTypes[$productId][$dynamicVariant]
							);
						}

						$itemName = implode($itemNameArray, ' | ');
					}

					// Searches for prices
					$hasPrices      = false;
					$hasRetailPrice = false;

					foreach ($staticTypeGroup as $staticType)
					{
						if (isset($issetItems[$productId][$staticType->id . '_' . $issetDynamicVariant]))
						{
							$issetItem = $issetItems[$productId][$staticType->id . '_' . $issetDynamicVariant];
						}
						elseif (isset($issetItems[$productId][$staticType->id]))
						{
							$issetItem = $issetItems[$productId][$staticType->id];
						}

						if (!is_null($issetItem))
						{
							if (is_array($prices) && isset($prices[$issetItem->id]))
							{
								if ((float) $prices[$issetItem->id]->price)
								{
									$hasPrices = true;
								}

								if (isset($prices[$issetItem->id]->retail_price) && (float) $prices[$issetItem->id]->retail_price)
								{
									$hasRetailPrice = true;
								}
							}
						}
					}

					if (count($staticTypeGroup) > 0) :
						if (RedshopbHelperCompany::checkStatusDisplayRetailPrice($customerId, $customerType) && $hasRetailPrice): ?>
							<tr class="retailPrices items-row">
								<td>
									<strong>
										<?php echo Text::_('COM_REDSHOPB_SHOP_RETAIL_PRICE') . ':'; ?>
									</strong>
								</td>
								<?php
								$noStaticType = 0;

								foreach ($staticTypeGroup as $staticType)
								{
									$issetItem = null;

									if (isset($issetItems[$productId][$staticType->id . '_' . $issetDynamicVariant]))
									{
										$issetItem = $issetItems[$productId][$staticType->id . '_' . $issetDynamicVariant];
									}
									elseif (isset($issetItems[$productId][$staticType->id]))
									{
										$issetItem = $issetItems[$productId][$staticType->id];
									}

									if ($issetItem && isset($prices[$issetItem->id]->retail_price))
									{
										?>
										<td class="text-center item-column">
											<?php
											echo RedshopbHelperProduct::getProductFormattedPrice(
												(float) $prices[$issetItem->id]->retail_price,
												$prices[$issetItem->id]->retail_currency_id
											);
											?>
										</td>
										<?php
									}
									else
									{
										?>
										<td>&nbsp;</td>
										<?php
									}

									$noStaticType++;
								}

								while ($noStaticType < $maxCountCols - 1) :
									?>
									<td>&nbsp;</td>
									<?php
									$noStaticType++;
								endwhile;
								?>
							</tr>
						<?php endif; ?>
						<tr class="items-row">
							<td>
								<strong>
									<?php echo $itemName . ':'; ?>
								</strong>
							</td>
							<?php
							$noStaticType = 0;

							foreach ($staticTypeGroup as $staticType) :
								$amount    = 0;
								$issetItem = null;
								$priceText = '';

								if (isset($issetItems[$productId][$staticType->id . '_' . $issetDynamicVariant]))
								{
									$issetItem = $issetItems[$productId][$staticType->id . '_' . $issetDynamicVariant];
								}
								elseif (isset($issetItems[$productId][$staticType->id]))
								{
									$issetItem = $issetItems[$productId][$staticType->id];
								}

								$class     = array();
								$iconStock = array();
								$price     = 0;

								if (!is_null($issetItem))
								{
									if (is_array($prices) && isset($prices[$issetItem->id]))
									{
										$item     = $prices[$issetItem->id];
										$currency = $item->currency_id;
										$price    = $item->price;
										$outlet   = (isset($item->outlet)) ? $item->outlet : false;
									}

									if ($price > 0)
									{
										if ($outlet)
										{
											$priceText = '<b>' . RedshopbHelperProduct::getProductFormattedPrice($price, $currency) . '</b>' .
												'<br /><span style="text-decoration: line-through;">' . RedshopbHelperProduct::getProductFormattedPrice($item->oldPrice, $currency) . '</span>';
										}
										else
										{
											$priceText = RedshopbHelperProduct::getProductFormattedPrice($price, $currency);
										}
									}

									// Stockroom amount
									$stockrooms = RedshopbHelperStockroom::getProductItemStockroomData($issetItem->id);

									foreach ($stockrooms as $stockroom)
									{
										$amount                          = (int) $stockrooms[$issetItem->id . '_' . $stockroom->stockroom_id]->amount;
										$class[$stockroom->stockroom_id] = '';

										if ($amount > 0)
										{
											$class[$stockroom->stockroom_id] = 'inStock';
										}

										$colorAmount                         = RedshopbHelperProduct::getColorAmount($issetItem);
										$iconStock[$stockroom->stockroom_id] = 'ok';

										switch ($colorAmount)
										{
											case ' amountLessZero':
												$iconStock[$stockroom->stockroom_id] = 'remove';
												break;
											case ' amountMoreZeroLessLower':
												$iconStock[$stockroom->stockroom_id] = 'warning-sign';
												break;
										}

										$class[$stockroom->stockroom_id] .= ' ' . $colorAmount;
									}

									// Check product unit measure
									$decimal = RedshopbEntityProduct::getInstance($productId)->getUnitMeasure()->get('decimal_position', 0);
									?>
									<td class="text-center item-column">
										<?php if (($showStockAs != 'hide') && !empty($stockrooms) && !empty($iconStock) && !empty($class)): ?>
											<!-- Stockroom amount -->
											<select name="stockroom_<?php echo $productId ?>_<?php echo $issetItem->id ?>"
													class="input chosen-icon stockroom_<?php echo $productId ?>_<?php echo $issetItem->id ?>"
													id="stockroom_<?php echo $productId ?>_<?php echo $issetItem->id ?>">
												<?php $stockroomSelected = false; ?>

												<?php foreach ($stockrooms as $stockroom): ?>
													<option value="<?php echo $stockroom->stockroom_id ?>" <?php echo (!$stockroomSelected) ? 'selected="selected"' : '' ?>>
														<?php if ($showStockAs == 'actual_stock'): ?>
															<?php if ($stockroom->unlimited): ?>
																<?php echo $stockroom->name ?> (<?php echo Text::_('COM_REDSHOPB_STOCKROOM_UNLIMITED') ?>)
															<?php else: ?>
																<?php $stockAmount = RedshopbHelperProduct::decimalFormat($stockroom->amount, $productId); ?>
																<?php echo $stockroom->name ?> (<?php echo Text::_('COM_REDSHOPB_SHOP_IN_STOCK') ?>: <?php echo $stockAmount ?>)
															<?php endif; ?>
														<?php elseif ($showStockAs == 'color_codes'): ?>
															<?php if ($stockroom->unlimited): ?>
																<?php echo $stockroom->name ?> (<?php echo Text::_('COM_REDSHOPB_STOCKROOM_UNLIMITED') ?>)
															<?php else: ?>
																<?php echo $stockroom->name ?> <i
																		class="icon-<?php echo $iconStock[$stockroom->stockroom_id] ?> <?php echo $class[$stockroom->stockroom_id] ?>"></i>
															<?php endif; ?>
														<?php endif; ?>
													</option>
													<?php $stockroomSelected = true; ?>
												<?php endforeach; ?>
											</select>
											<!-- Stockroom - end -->
										<?php endif; ?>

										<?php if ($hasPrices) : ?>
											<div class="price">
												<?php if ($priceText != '') : ?>
													<?php echo $priceText ?>
												<?php endif; ?>
											</div>
										<?php endif; ?>
										<?php
										if ($placeOrderPermission) :
											?>
											<input value="" type="text" class="input-xmini input-sm amountInput"
												   name="quantity_<?php echo $productId ?>_<?php echo $issetItem->id ?>"/>
											<input value="<?php echo $price; ?>" type="hidden"
												   name="price_<?php echo $productId ?>_<?php echo $issetItem->id ?>"/>
											<input value="<?php echo $currency; ?>" type="hidden"
												   name="currency_<?php echo $productId ?>_<?php echo $issetItem->id ?>"/>
											<input value="<?php echo $collectionId; ?>" type="hidden"
												   name="collection_<?php echo $productId ?>_<?php echo $issetItem->id ?>"/>
											<input value="<?php echo $dropDownSelected; ?>" type="hidden"
												   name="dropDownSelected_<?php echo $productId ?>_<?php echo $issetItem->id ?>"/>
										<?php
										endif;
										?>
									</td>
									<?php
								}
								else
								{
									?>
									<td>&nbsp;</td>
									<?php
								}

								$noStaticType++;
							endforeach;

							while ($noStaticType < $maxCountCols - 1) :
								?>
								<td>&nbsp;</td>
								<?php
								$noStaticType++;
							endwhile;
							?>
						</tr>
					<?php
					endif;
				endforeach;
				?>
				</tbody>
			<?php
			endif;
			$noStaticTypeGroup++;
			?>
		</table>
		<?php
		if (count($staticTypesProduct) > 1) :
			?>
			</div>
		<?php
		endif;
	endforeach;
endif;

if (count($staticTypesProduct) > 1) :
	?>

	</div>
	</div>
<?php
elseif (!empty($issetItems)):
	// In case product has one single attribute.
	if (isset($issetItems[$productId]))
	{
		$currentProduct = $issetItems[$productId];
		$issetItem      = current($currentProduct);
	}
	else
	{
		$issetItem = null;
	}

	$hasPrices      = false;
	$hasRetailPrice = false;

	if (!is_null($issetItem))
	{
		$price = null;

		if (is_array($prices) && isset($prices[$issetItem->id]))
		{
			$item      = $prices[$issetItem->id];
			$currency  = $item->currency_id;
			$price     = $item->price;
			$outlet    = (isset($item->outlet)) ? $item->outlet : false;
			$hasPrices = true;

			if (isset($prices[$issetItem->id]->retail_price) && (float) $prices[$issetItem->id]->retail_price)
			{
				$hasRetailPrice = true;
			}
		}

		if ($price > 0)
		{
			if ($outlet)
			{
				$priceText = '<b>' . RedshopbHelperProduct::getProductFormattedPrice($price, $currency) . '</b>' .
					'<br /><span style="text-decoration: line-through;">' . RedshopbHelperProduct::getProductFormattedPrice($item->oldPrice, $currency) . '</span>';
			}
			else
			{
				$priceText = RedshopbHelperProduct::getProductFormattedPrice($price, $currency);
			}
		}

		// Stockroom amount
		$stockrooms = RedshopbHelperStockroom::getProductItemStockroomData($issetItem->id);

		foreach ($stockrooms as $stockroom)
		{
			$amount                          = (int) $stockrooms[$issetItem->id . '_' . $stockroom->stockroom_id]->amount;
			$class[$stockroom->stockroom_id] = '';

			if ($amount > 0)
			{
				$class[$stockroom->stockroom_id] = 'inStock';
			}

			$colorAmount                         = RedshopbHelperProduct::getColorAmount($issetItem);
			$iconStock[$stockroom->stockroom_id] = 'ok';

			switch ($colorAmount)
			{
				case ' amountLessZero':
					$iconStock[$stockroom->stockroom_id] = 'remove';
					break;
				case ' amountMoreZeroLessLower':
					$iconStock[$stockroom->stockroom_id] = 'warning-sign';
					break;
			}

			$class[$stockroom->stockroom_id] .= ' ' . $colorAmount;
		}

		// Check product unit measure
		$decimal = RedshopbEntityProduct::getInstance($productId)->getUnitMeasure()->get('decimal_position', 0);
		?>
		<div class="item-column">
			<?php if (($showStockAs != 'hide') && !empty($stockrooms) && !empty($iconStock) && !empty($class)): ?>
				<!-- Stockroom amount -->
				<select name="stockroom_<?php echo $productId ?>_<?php echo $issetItem->id ?>"
						class="input chosen-icon stockroom_<?php echo $productId ?>_<?php echo $issetItem->id ?>"
						id="stockroom_<?php echo $productId ?>_<?php echo $issetItem->id ?>">
					<?php $stockroomSelected = false; ?>

					<?php foreach ($stockrooms as $stockroom): ?>
						<option value="<?php echo $stockroom->stockroom_id ?>" <?php echo (!$stockroomSelected) ? 'selected="selected"' : '' ?>>
							<?php if ($showStockAs == 'actual_stock'): ?>
								<?php if ($stockroom->unlimited): ?>
									<?php echo $stockroom->name ?> (<?php echo Text::_('COM_REDSHOPB_STOCKROOM_UNLIMITED') ?>)
								<?php else: ?>
									<?php $stockAmount = RedshopbHelperProduct::decimalFormat($stockroom->amount, $productId); ?>
									<?php echo $stockroom->name ?> (<?php echo Text::_('COM_REDSHOPB_SHOP_IN_STOCK') ?>: <?php echo $stockAmount ?>)
								<?php endif; ?>
							<?php elseif ($showStockAs == 'color_codes'): ?>
								<?php if ($stockroom->unlimited): ?>
									<?php echo $stockroom->name ?> (<?php echo Text::_('COM_REDSHOPB_STOCKROOM_UNLIMITED') ?>)
								<?php else: ?>
									<?php echo $stockroom->name ?> <i
											class="icon-<?php echo $iconStock[$stockroom->stockroom_id] ?> <?php echo $class[$stockroom->stockroom_id] ?>"></i>
								<?php endif; ?>
							<?php endif; ?>
						</option>
						<?php $stockroomSelected = true; ?>
					<?php endforeach; ?>
				</select>
				<!-- Stockroom - end -->
			<?php endif; ?>

			<?php if ($hasPrices) : ?>
				<div class="price">
					<?php if (!empty($priceText)) : ?>
						<strong>
							<?php echo Text::_('COM_REDSHOPB_SHOP_PRICE') . ':'; ?>
						</strong>&nbsp;<?php echo $priceText ?>
					<?php endif; ?>
				</div>
			<?php endif; ?>
			<?php
			if ($placeOrderPermission) :
				?>
				<strong>
					<?php echo Text::_('COM_REDSHOPB_SHOP_QUANTITY') . ':'; ?>
				</strong>
				<input value="" type="text" class="input-xmini input-sm amountInput"
					   name="quantity_<?php echo $productId ?>_<?php echo $issetItem->id ?>"/>
				<input value="<?php echo $price; ?>" type="hidden" name="price_<?php echo $productId ?>_<?php echo $issetItem->id ?>"/>
				<input value="<?php echo $currency; ?>" type="hidden" name="currency_<?php echo $productId ?>_<?php echo $issetItem->id ?>"/>
				<input value="<?php echo $collectionId; ?>" type="hidden" name="collection_<?php echo $productId ?>_<?php echo $issetItem->id ?>"/>
				<input value="<?php echo $dropDownSelected; ?>" type="hidden"
					   name="dropDownSelected_<?php echo $productId ?>_<?php echo $issetItem->id ?>"/>
			<?php
			endif;
			?>
		</div>
		<?php
	}
endif;

if ($displayProductImages) : ?>
	<div id="productImages">
		<?php
		$extThis                         = new stdClass;
		$extThis->product                = RedshopbEntityProduct::load($productId);
		$extThis->product->collectionId  = $collectionId;
		$extThis->product->productImages = $productImages;

		$product     = new stdClass;
		$product->id = $productId;

		echo RedshopbLayoutHelper::render(
			'tags.product.' . $thumbStyle,
			array(
				'isAjax'  => true,
				'extThis' => $extThis,
				'product' => $product,
				'width'   => $thumbWidth,
				'height'  => $thumbHeight
			)
		); ?>
	</div>
<?php endif; ?>

<?php if ($displayAccessories) :
	if (isset($accessories[$productId])): ?>
		<div id="divAccessory">
			<?php echo RedshopbHelperProduct::renderAccessoriesDropdown($accessories[$productId], $productId); ?>
		</div>
	<?php
	endif;
endif;

if ($displayWashCareLink):
	?>
	<div id="washCareLink"><?php echo RedshopbRoute::_('index.php?option=com_redshopb&task=shop.ajaxWashAndCare&productId=' . $productId . '&flatAttrId=' . $dropDownSelected); ?></div>
<?php
endif;
