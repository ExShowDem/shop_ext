<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

$formName   = $displayData['formName'];
$attributes = $displayData['attributes'];
$return     = $displayData['return'];
$url        = $displayData['action'];

if (!empty($return))
{
	$url .= '&return=' . $return;
}

$action               = RedshopbRoute::_($url);
$maxCountRows         = 10;
$staticTypes          = $displayData['staticTypes'];
$dynamicTypes         = $displayData['dynamicTypes'];
$issetItems           = $displayData['issetItems'];
$issetDynamicVariants = $displayData['issetDynamicVariants'];
$productId            = $displayData['productId'];

if (empty($issetDynamicVariants) && empty($dynamicTypes))
{
	$issetDynamicVariants = [0];
	$dynamicTypes[0]      = (object) ['name' => '', 'type_id' => 1, 'string_value' => ''];
}

RedshopbHtml::loadFooTable();

$displayDefaultMessage = (empty($staticTypes) || empty($issetItems))
?>
<?php if ($displayDefaultMessage) : ?>
	<div class="alert alert-info">
		<div class="pagination-centered">
			<h3><?php echo Text::_('COM_REDSHOPB_PRODUCT_ITEM_CREATE_ATTRIBUTES_AND_CLICK_GENERATE_ITEMS') ?></h3>
		</div>
	</div>
	<?php return;?>
<?php endif; ?>
<script>
	function deleteItem(id) {
		document.getElementById('item_id').value = id;
		var form = document.getElementById('<?php echo $formName ?>');
		Joomla.submitform('product_items.delete', form);
	}
</script>
<form action="<?php echo $action; ?>" name="<?php echo $formName ?>" class="adminForm" id="<?php echo $formName ?>" method="post">
	<div class="row-fluid">
		<div class="span12">
			<h4><?php echo Text::_('COM_REDSHOPB_COMBINATIONS') ?></h4>
			<div class="flexslider" id="flexslider_combinations">
				<ul class="slides unstyled list-unstyled">
					<?php foreach ($staticTypes  as $staticTypeKey => $staticTypeValue):?>
						<?php $countRows = 0; ?>
						<li>
							<table class="table table-condensed table-striped table-product-items footable js-redshopb-footable redshopb-footable">
								<thead>
								<tr>
									<?php if (!empty($issetDynamicVariants[0])): ?>
										<?php
										$dynamicVariants = explode('_', $issetDynamicVariants[0]);
										$colspan         = count((array) $dynamicVariants);
										?>

										<?php if ($colspan):?>
											<?php $orderingDynamicVariants = array();?>

											<?php foreach ($dynamicVariants as $dynamicVariant):
												if (!empty($dynamicTypes[$dynamicVariant]->name))
												{
													$orderingDynamicVariants[] = $dynamicTypes[$dynamicVariant]->name;
												}
											endforeach;?>
											<?php $orderingDynamicVariants[] = $staticTypeValue->name;?>
											<th colspan="<?php echo $colspan;?>" data-toggle="true">
												<?php echo implode(' \ ', $orderingDynamicVariants); ?>
											</th>
											<?php $countRows = $countRows + $colspan; ?>
										<?php endif;?>
									<?php else: ?>
										<th data-toggle="true"><?php echo $staticTypeValue->name; ?></th>
										<?php $countRows++; ?>
									<?php endif; ?>

									<?php foreach ($staticTypes AS $staticType):?>
										<th class="nowrap text-center" data-hide="phone, tablet">
											<?php echo RedshopbHelperCollection::getProductItemValueFromType($staticType->type_id, $staticType); ?>
										</th>
										<?php $countRows++?>
										<?php
										if ($countRows >= $maxCountRows)
										{
											break;
										};
										?>
									<?php endforeach;?>

									<?php if ($countRows < $maxCountRows):?>
										<?php for ($i = 0; $countRows + $i < $maxCountRows; $i++) :?>
											<th></th>
										<?php endfor;?>
									<?php endif;?>
								</tr>
								</thead>
								<tbody>

								<?php if (!empty($issetDynamicVariants)): ?>
									<?php $totalDynamicVariants  = count($issetDynamicVariants); ?>
									<?php $counterDynamicVariant = 0; ?>

									<?php foreach ($issetDynamicVariants as $issetDynamicVariant):?>
										<?php
										$countRows       = 0;
										$dynamicVariants = explode('_', $issetDynamicVariant);
										$counterDynamicVariant++;
										?>
										<tr>
											<?php if ($dynamicVariants) :?>
												<?php foreach ($dynamicVariants as $dynamicVariant):?>
													<td>
														<?php echo RedshopbHelperCollection::getProductItemValueFromType(
															$dynamicTypes[$dynamicVariant]->type_id,
															$dynamicTypes[$dynamicVariant]
														);
														?>
													</td>
													<?php $countRows++;?>
												<?php endforeach;?>
											<?php endif;?>

											<?php foreach ($staticTypes as $key => $staticType):?>
												<?php
												$class       = 'notExists';
												$item        = new stdClass;
												$item->id    = 0;
												$item->state = 0;
												?>

												<?php if (isset($issetItems[$staticType->id . '_' . $issetDynamicVariant])):?>
													<?php $item = $issetItems[$staticType->id . '_' . $issetDynamicVariant];?>
												<?php  elseif (isset($issetItems[$staticType->id])):?>
													<?php  $item = $issetItems[$staticType->id];?>
												<?php endif;?>
												<?php $itemId = $item->id;?>
												<?php $class  = ($item->state == 1) ? 'itemPublish' : 'itemUnpublish';?>
												<td class="text-center <?php echo $class; ?>">

													<?php if ($itemId !== 0): ?>
														<div class="pagination-centered">
															<div class="btn-group">
																<a href="javascript:void(0);"
																   class="btn btn-mini"
																   onclick="jQuery('#item_id').val('<?php echo $itemId; ?>'); redSHOPB.form.submit(event)"
																   data-task="product_item.edit"
																   data-action="leaveForm">
																	<i class="icon-edit"></i>
																</a>
																<?php if (!RedshopbEntityProduct_Item::getInstance($itemId)->canReadOnly()): ?>
																<a  href="javascript:void(0);"
																	class="btn btn-danger btn-mini"
																	onclick="jQuery('#item_id').val('<?php echo $itemId; ?>'); redSHOPB.products.tabSubmit(event)"
																	data-task="product_items.delete">
																	<i class="icon-trash"></i>
																</a>
																<?php endif ?>
															</div>
														</div>
													<?php endif; ?>
												</td>
												<?php $countRows++;?>

												<?php
												if ($totalDynamicVariants <= $counterDynamicVariant)
												{
													unset($staticTypes[$key]);
												}
												?>

												<?php
												if ($countRows >= $maxCountRows)
												{
													break;
												}
												?>
											<?php endforeach;?>

											<?php if ($countRows < $maxCountRows):?>
												<?php for ($i = 0; $countRows + $i < $maxCountRows; $i++) :?>
													<td></td>
												<?php endfor;?>
											<?php endif;?>
										</tr>
									<?php endforeach;?>
								<?php else: ?>
									<?php
									$class    = 'notExists';
									$item     = new stdClass;
									$item->id = 0;
									?>

									<?php if (isset($issetItems[$staticTypeValue->id . '_'])):?>
										<?php $item = $issetItems[$staticTypeValue->id . '_'];?>
									<?php  elseif (isset($issetItems[$staticTypeValue->id])):?>
										<?php  $item = $issetItems[$staticTypeValue->id];?>
									<?php endif;?>
									<?php $itemId = $item->id;?>
									<?php $class  = ($item->state == 1) ? 'itemPublish' : 'itemUnpublish';?>
									<tr>
										<td class="text-center <?php echo $class; ?>">

											<?php if ($itemId !== 0): ?>
												<?php $itemEditUrl = 'index.php?option=com_redshopb&task=product_item.edit&id=' . (int) $itemId . '&return=' . $return;?>
												<div class="pagination-centered">
													<div class="btn-group">
														<a href="javascript:void(0);"
														   class="btn btn-mini"
														   onclick="jQuery('#item_id').val('<?php echo $itemId; ?>'); redSHOPB.form.submit(event)"
														   data-task="product_item.edit"
														   data-action="leaveForm">
															<i class="icon-edit"></i>
														</a>
														<?php if (!RedshopbEntityProduct_Item::getInstance($itemId)->canReadOnly()): ?>
														<a  href="javascript:void(0);"
															class="btn btn-danger btn-mini"
															onclick="jQuery('#item_id').val('<?php echo $itemId; ?>'); redSHOPB.products.tabSubmit(event)"
															data-task="product_items.delete">
															<i class="icon-trash"></i>
														</a>
														<?php endif ?>
													</div>
												</div>
											<?php endif; ?>
										</td>
									</tr>
								<?php endif; ?>
								</tbody>
							</table>
						</li>
						<?php
						if (empty($staticTypes))
						{
							break;
						}
						?>
					<?php endforeach;?>
				</ul>
			</div>
		</div>
	</div>
	<input id="item_id" type="hidden" name="cid[]" value="">
	<input type="hidden" name="task" value="">
	<input type="hidden" name="tab" value="Combinations">
	<?php echo HTMLHelper::_('form.token'); ?>
</form>
