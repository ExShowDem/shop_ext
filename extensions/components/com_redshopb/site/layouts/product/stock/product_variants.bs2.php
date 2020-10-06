<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;


use Joomla\CMS\Language\Text;

$maxCountRows = 10;

$staticTypes          = $displayData['staticTypes'];
$issetItems           = $displayData['issetItems'];
$dynamicTypes         = $displayData['dynamicTypes'];
$productId            = $displayData['productId'];
$stockroomId          = $displayData['stockroomId'];
$stockRoom            = $displayData['stockroom'];
$stockroomData        = $displayData['stockroomData'];
$issetDynamicVariants = $displayData['issetDynamicVariants'];
$unitMeasure          = $displayData['unitMeasure'];
$isLocked             = RedshopbEntityProduct::getInstance($productId)->canReadOnly();
?>

<?php if (empty($staticTypes) || empty($issetItems)) : ?>
	<?php echo RedshopbLayoutHelper::render('common.nodata'); ?>

<?php return;
endif; ?>

<h4><?php echo Text::_('COM_REDSHOPB_STOCKROOM_PRODUCT_ITEMS') ?></h4>

<div class="flexslider" id="flexslider_<?php echo $productId ?>_<?php echo $stockroomId ?>">
	<?php if (empty($issetDynamicVariants)): ?>
		<?php echo RLayoutHelper::render('product.stock.simple_variant_table', $displayData);?>
	<?php else: ?>
	<ul class="slides unstyled list-unstyled">
		<?php while ($currentStaticType = current($staticTypes)): ?>
		<li>
			<table class="table table-condensed table-striped table-product-items footable js-redshopb-footable redshopb-footable">
				<thead>
				<tr>
					<?php $countRows = 0;?>

					<?php if (!empty($issetDynamicVariants)): ?>
						<?php $dynamicVariants = explode('_', $issetDynamicVariants[0]);?>

						<?php if ($dynamicVariants): ?>
							<?php $colspan                 = count($dynamicVariants);?>
							<?php $orderingDynamicVariants = array();?>

							<?php foreach ($dynamicVariants as $dynamicVariant): ?>
								<?php $orderingDynamicVariants[] = $dynamicTypes[$dynamicVariant]->name;?>
							<?php endforeach;?>

							<?php $orderingDynamicVariants[] = $currentStaticType->name;?>

							<?php echo '<th colspan="' . $colspan . '" data-toggle="true">' . implode(' \ ', $orderingDynamicVariants) . '</th>';?>
							<?php $countRows = $countRows + $colspan;?>
						<?php endif; ?>
					<?php endif; ?>

					<?php if (isset($staticTypes)): ?>
						<?php foreach ($staticTypes as $staticType): ?>
							<th class="nowrap text-center">
								<?php echo RedshopbHelperCollection::getProductItemValueFromType($staticType->type_id, $staticType); ?>
							</th>
							<?php $countRows++;?>

							<?php if ($countRows >= $maxCountRows):?>
								<?php break; ?>
							<?php endif; ?>
						<?php endforeach; ?>
					<?php endif; ?>
				</tr>
				</thead>
				<tbody>
					<?php if (!empty($issetDynamicVariants)): ?>
						<?php $totalDynamicVariants  = count($issetDynamicVariants);?>
						<?php $counterDynamicVariant = 0; ?>

						<?php foreach ($issetDynamicVariants as $issetDynamicVariant): ?>
							<?php $countRows       = 0; ?>
							<?php $dynamicVariants = explode('_', $issetDynamicVariant); ?>
							<?php $counterDynamicVariant++; ?>
							<tr>
								<?php if ($dynamicVariants): ?>
									<?php foreach ($dynamicVariants as $dynamicVariant): ?>
										<td>
											<?php if (isset($dynamicTypes[$dynamicVariant])): ?>
												<?php
												echo RedshopbHelperCollection::getProductItemValueFromType(
													$dynamicTypes[$dynamicVariant]->type_id,
													$dynamicTypes[$dynamicVariant]
												); ?>
											<?php endif; ?>
										</td>
										<?php $countRows++; ?>
									<?php endforeach; ?>
								<?php endif; ?>

								<?php if (isset($staticTypes)): ?>
									<?php foreach ($staticTypes as $key => $staticType): ?>
										<?php $inputId      = '';?>
										<?php $inputValue   = Text::_('COM_REDSHOPB_STOCKROOM_UNLIMITED');?>
										<?php $inputClasses = 'input-xmini input-sm amountInput ajaxUpdateAmount';?>
										<?php $inputType    = "text";?>
										<?php $unlimited    = 0;?>
										<?php $stockroomKey = '';?>
										<?php $stockAmount  = number_format(0, $unitMeasure->decimal_position); ?>

										<?php // @ToDo: Bring right stock amount depending on stockrooms ?>
										<?php if (isset($issetItems[$staticType->id . '_' . $issetDynamicVariant])): ?>
											<?php $stockroomKey = $issetItems[$staticType->id . '_' . $issetDynamicVariant]->id . '_' . $stockroomId; ?>
											<?php $inputId      = $issetItems[$staticType->id . '_' . $issetDynamicVariant]->id;?>

										<?php elseif (isset($issetItems[$staticType->id])): ?>
											<?php $stockroomKey = $issetItems[$staticType->id]->id . '_' . $stockroomId; ?>
											<?php $inputId      = $issetItems[$staticType->id]->id;?>
										<?php endif;?>

										<?php if (isset($stockroomData[$stockroomKey])): ?>
											<?php $stockAmount = number_format((float) $stockroomData[$stockroomKey]->amount, $unitMeasure->decimal_position); ?>
											<?php $unlimited   = (int) $stockroomData[$stockroomKey]->unlimited; ?>
										<?php endif;?>

										<?php if ($unlimited == 0): ?>
											<?php $inputType  = 'number';?>
											<?php $inputValue = $stockAmount;?>
										<?php endif; ?>

										<td class="text-center <?php echo (!empty($inputId) && isset($issetItems[$inputId])) ? 'inStock' : 'notExists' ?>">
											<?php if (!empty($inputId)):?>
												<div class="input-append">
													<input type="<?php echo $inputType?>"
														   value="<?php echo $inputValue?>"
														   name="jform[amount][<?php echo $inputId;?>]"
														   id="jform_amount_<?php echo $inputId;?>"
														   data-unlimited="<?php echo $unlimited; ?>"
														   class="<?php echo $inputClasses;?>"
														<?php echo ($inputType == 'text' || $isLocked || RedshopbEntityProduct_Item::getInstance($inputId)->canReadOnly()) ? 'disabled="disabled"' : 'step="' . $unitMeasure->step . '" min="0"';?>
													/>
													<?php if (!$isLocked): ?>
													<a class="btn ajaxUpdateAmountUnlimited btn-success"
													   href="javascript:void(0)"
													   data-field="jform_amount_<?php echo $inputId;?>" >&infin;</a>
													<?php endif ?>
												</div>
											<?php endif;?>
										</td>
										<?php $countRows++;?>

										<?php if ($totalDynamicVariants <= $counterDynamicVariant):?>
											<?php unset($staticTypes[$key]);?>
										<?php endif;?>

										<?php if ($countRows >= $maxCountRows):?>
											<?php break; ?>
										<?php endif; ?>
									<?php endforeach; ?>
								<?php endif; ?>
							</tr>
						<?php endforeach; ?>
					<?php endif; ?>
				</tbody>
			</table>
		<?php endwhile; ?>
		</li>
	</ul>
	<?php endif; ?>
</div>
