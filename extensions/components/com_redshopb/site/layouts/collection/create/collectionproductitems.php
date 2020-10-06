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
use Joomla\CMS\Factory;

$data                 = $displayData;
$staticTypes          = $data['staticTypes'];
$dynamicTypes         = $data['dynamicTypes'];
$issetItems           = $data['issetItems'];
$issetDynamicVariants = $data['issetDynamicVariants'];
$dynamicVariantCount  = 0;
$maxCountRows         = 9;
$productId            = Factory::getApplication()->input->getInt('product_id');
?>
<?php if (!$staticTypes): ?>
	<div class="alert alert-info">
		<button type="button" class="close" data-dismiss="alert">&times;</button>
		<div class="pagination-centered">
			<h3><?php echo Text::_('COM_REDSHOPB_PRODUCT_ITEM_CREATE_ATTRIBUTES_AND_CLICK_GENERATE_ITEMS') ?></h3>
		</div>
	</div>
<?php else: ?>
	<div class="redshopb-collection-collectionproductitems">
		<div class="flexslider" id="flexslider_collection_items">
			<ul class="slides">
				<?php while ($staticTypes): ?>
				<li>
					<table class="table table-condensed table-striped table-product-items footable js-redshopb-footable redshopb-footable">
						<thead>
						<tr>
							<?php
							$countRows = 0;

							if (isset($issetDynamicVariants) && !empty($issetDynamicVariants[0]))
							{
								$dynamicVariants = explode('_', $issetDynamicVariants[0]);

								if ($dynamicVariants)
								{
									$colspan                 = count($dynamicVariants);
									$orderingDynamicVariants = array();

									foreach ($dynamicVariants as $dynamicVariant)
									{
										$orderingDynamicVariants[] = $dynamicTypes[$dynamicVariant]->name;
									}

									$orderingDynamicVariants[] = current($staticTypes)->name;

									echo '<th colspan="' . $colspan . '" data-toggle="true">' . implode(' \ ', $orderingDynamicVariants) . '</th>';
									$countRows = $countRows + $colspan;
								}
							}
							?>
							<?php if (isset($staticTypes)): ?>
								<?php foreach ($staticTypes as $staticType): ?>
									<th class="nowrap text-center" data-hide="phone, tablet">
										<?php echo RedshopbHelperCollection::getProductItemValueFromType($staticType->type_id, $staticType); ?>
									</th>
									<?php
									$countRows++;

									if ($countRows >= $maxCountRows)
									{
										break;
									}
								endforeach;

if ($countRows < $maxCountRows)
			{
	for ($j = 0; $j + $countRows < $maxCountRows; $j++)
				{
		echo '<th></th>';
	}
}
								?>
							<?php endif; ?>

							<th class="nowrap text-center" data-hide="phone,tablet">
								<?php echo Text::_('COM_REDSHOPB_SELECT_ALL_TITLE'); ?>
							</th>
						</tr>
						</thead>
						<tbody>
						<?php

						if (isset($issetDynamicVariants) && !empty($issetDynamicVariants))
						{
							$totalDynamicVariants  = count($issetDynamicVariants);
							$counterDynamicVariant = 0;

							foreach ($issetDynamicVariants as $issetDynamicVariant)
							{
								$countRows       = 0;
								$dynamicVariants = explode('_', $issetDynamicVariant);
								$counterDynamicVariant++;

								if (count($dynamicVariants) > $dynamicVariantCount)
								{
									$dynamicVariantCount = count($dynamicVariants);
								}

								echo '<tr>';

								if ($dynamicVariants)
								{
									foreach ($dynamicVariants as $dynamicVariant)
									{
										$thumb = '';

										if ($dynamicTypes[$dynamicVariant]->main_attribute == 1)
										{
											$thumb = RedshopbHelperProduct::getProductImageThumbHtml($productId, 0, $dynamicTypes[$dynamicVariant]->id);
										}

										echo '<td>';

										if ($thumb != '')
										{
											echo '<span class="hasTooltip" data-original-title="' . htmlspecialchars('<div class="thumb">' . $thumb . '</div>', ENT_COMPAT, 'UTF-8') . '">';
										}

										if (isset($dynamicTypes[$dynamicVariant]))
										{
											echo RedshopbHelperCollection::getProductItemValueFromType(
												$dynamicTypes[$dynamicVariant]->type_id,
												$dynamicTypes[$dynamicVariant]
											);
										}

										if ($thumb != '')
										{
											echo '</span>';
										}

										echo '</td>';
										$countRows++;
									}
								}

								if (isset($staticTypes))
								{
									foreach ($staticTypes as $key => $staticType)
									{
										echo '<td class="collection-items" style="text-align: center">';

										if (isset($issetItems[$staticType->id . '_' . $issetDynamicVariant]))
										{
											if ($issetItems[$staticType->id . '_' . $issetDynamicVariant]->collection_id
												&& 1 == $issetItems[$staticType->id . '_' . $issetDynamicVariant]->state)
											{
												$checked = ' checked="checked"';
											}
											else
											{
												$checked = '';
											}

											echo '<label class="fromCheckBoxInTd">'
												. '<input type="checkbox" name="" id="jform_productitem_'
												. $issetItems[$staticType->id . '_' . $issetDynamicVariant]->id
												. '" class="collection-product-item-on" value="1" ' . $checked . ' /></label>';
										}

										echo '</td>';

										$countRows++;

										if ($totalDynamicVariants <= $counterDynamicVariant)
										{
											unset($staticTypes[$key]);
										}

										if ($countRows >= $maxCountRows)
										{
											break;
										}
									}
								}

								if ($countRows < $maxCountRows)
								{
									for ($j = 0; $j + $countRows < $maxCountRows; $j++)
									{
										echo '<td></td>';
									}
								}

								?>

								<td class="nowrap text-center">
									<input type="checkbox" name="checkAllVariantsInRow" class="check-all-variants-in-row" value="0">
								</td>

								<?php
								echo '</tr>';
							}
						}
						?>
							<tr>
								<?php for ($i = 1; $i < $dynamicVariantCount; $i++): ?>
								<td> </td>
								<?php endfor; ?>
								<td>
									<?php echo Text::_('COM_REDSHOPB_SELECT_ALL_TITLE'); ?>
								</td>
							<?php if ($countRows > 1): ?>
								<?php for ($i = 0; $i < $countRows - $dynamicVariantCount; $i++): ?>
								<td class="nowrap text-center">
									<input type="checkbox" name="checkAllVariantsInColumn" class="check-all-variants-in-column" value="0">
								</td>
								<?php endfor; ?>
							<?php endif; ?>
							</tr>
						</tbody>
					</table>
				</li>
				<?php endwhile; ?>
			</ul>
		</div>
	</div>
<?php endif;
