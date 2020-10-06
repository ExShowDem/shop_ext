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

$maxCountRows         = 8;
$staticTypes          = $displayData['staticTypes'];
$dynamicTypes         = $displayData['dynamicTypes'];
$issetDynamicVariants = (isset($displayData['issetDynamicVariants'])) ? $displayData['issetDynamicVariants'] : null;
$issetItems           = $displayData['issetItems'];
$issetItemsPrices     = $displayData['issetItemsPrices'];
$data                 = $displayData;
$formName             = $displayData['formName'];
$url                  = $displayData['action'];
$productId            = $displayData['productId'];
$return               = $displayData['return'];
$canEdit              = RedshopbHelperACL::getPermission('manage', 'product', array('edit', 'edit.own'), true);
$productInfo          = $displayData['productInfo'];
$companyInfo          = RedshopbHelperCompany::getCompanyById($productInfo->company_id);
$defaultCurrencyId    = null;
$isLocked             = RedshopbEntityProduct::getInstance($productId)->canReadOnly();
$showToolbar          = isset($displayData['showToolbar']) && !$isLocked ? $displayData['showToolbar'] : false;

if ($companyInfo)
{
	$defaultCurrencyId = $companyInfo->currency_id;
}

if (!$defaultCurrencyId)
{
	$config            = RedshopbEntityConfig::getInstance();
	$defaultCurrencyId = $config->getInt('default_currency', 38);
}

$itemUrl = 'index.php?option=com_redshopb&product_id=' . $productId
	. '&tab=TablePrices';

if ($return)
{
	$itemUrl .= '&return=' . $return;
	$url     .= '&return=' . $return;
}

$action          = RedshopbRoute::_($url);
$defaultCurrency = RedshopbHelperProduct::getCurrency($defaultCurrencyId)->alpha3;

$salesTypes = array(
	'customer_price' => Text::_('COM_REDSHOPB_PRODUCT_PRICE_DEBTOR'),
	'all_customers' => Text::_('COM_REDSHOPB_PRODUCT_PRICE_ALL_DEBTOR'),
	'customer_price_group' => Text::_('COM_REDSHOPB_PRODUCT_PRICE_DEBTOR_GROUP'),
	'campaign' => Text::_('COM_REDSHOPB_PRODUCT_PRICE_CAMPAIGN')
);

$searchToolsOptions = array(
	'view' => (object) array(
		'filterForm' => $displayData['filter_form'],
		'activeFilters' => $displayData['activeFilters']
	),
	'options' => array(
		'filterButton' => false,
		'formSelector' => ("#" . $formName),
		'searchButton' => false,
		'filtersHidden' => false
	)
);
?>
<script type="text/javascript">
	(function ($) {
		$(document).ready(function () {

			jQuery('[data-action = "deletePrice"]').on('click', function(event)
			{
				redSHOPB.products.deletePrice(event);
			});

			jQuery('[data-action ="updatePrice"').on('click', function(event)
			{
				redSHOPB.products.updatePrice(event);
			});

			jQuery('[data-action ="saveNewPrice"').on('click', function(event)
			{
				redSHOPB.products.saveNewPrice(event);
			});

			redSHOPB.products.initPopOver();
		});
	})(jQuery);
</script>
<form action="<?php echo $action; ?>"
	  name="<?php echo $formName ?>"
	  class="adminForm" id="<?php echo $formName ?>"
	  method="post" data-save-task="all_prices.saveAllPrices">
	<h4>
		<?php echo Text::_('COM_REDSHOPB_PRODUCT_COMBINATIONS_PRICES_TITLE'); ?>
	</h4>
	<?php if ($showToolbar && (!empty($staticTypes) || !empty($issetItems))):?>
		<?php echo RedshopbLayoutHelper::render('product.tableprices.toolbar', $displayData);?>
	<?php endif;?>

	<?php echo RedshopbLayoutHelper::render('searchtools.default', $searchToolsOptions);?>

	<?php if (empty($staticTypes) || empty($issetItems) || empty($issetDynamicVariants)) : ?>
		<?php echo RedshopbLayoutHelper::render('common.nodata'); ?>
	<?php else :
	if (empty($issetDynamicVariants) && empty($dynamicTypes))
		{
		$issetDynamicVariants = [0];
		$dynamicTypes[0]      = (object) ['name' => '', 'type_id' => 1, 'string_value' => ''];
	}
		?>
		<div class="flexslider" id="flexslider_tableprices">
			<ul class="slides unstyled list-unstyled">
				<?php while ($currentStaticType = current($staticTypes)): ?>
					<li>
						<div class="tablePrices">
							<table class="table table-condensed table-striped table-product-items footable js-redshopb-footable redshopb-footable">
								<thead>
								<tr>
									<?php $countRows = 0;?>

									<?php if (!empty($issetDynamicVariants)):?>
										<?php $dynamicVariants = explode('_', $issetDynamicVariants[0]); ?>

										<?php if ($dynamicVariants): ?>
											<?php $orderingDynamicVariants = array(); ?>

											<?php foreach ($dynamicVariants as $dynamicVariant):
												if (!empty($dynamicTypes[$dynamicVariant]->name))
												{
													$orderingDynamicVariants[] = $dynamicTypes[$dynamicVariant]->name;
												}
											endforeach;?>

											<?php $colspan                   = count($dynamicVariants); ?>
											<?php $orderingDynamicVariants[] = $currentStaticType->name; ?>
											<th colspan="<?php echo $colspan?>'" data-toggle="true">
												<?php echo implode(' \ ', $orderingDynamicVariants);?>
											</th>
											<?php $countRows = $countRows + $colspan; ?>
										<?php endif;?>
									<?php endif;?>

									<?php if (isset($staticTypes)): ?>
										<?php foreach ($staticTypes as $staticType): ?>
											<th class="nowrap text-center"  data-hide="phone, tablet">
												<?php echo RedshopbHelperCollection::getProductItemValueFromType($staticType->type_id, $staticType); ?>
											</th>
											<?php $countRows++;?>

											<?php if ($countRows >= $maxCountRows):?>
												<?php break;?>
											<?php endif;?>
										<?php endforeach;?>

										<?php if ($countRows < $maxCountRows):?>
											<?php for ($j = 0; $j + $countRows < $maxCountRows; $j++):?>
												<th data-hide="phone,tablet"></th>
											<?php endfor;?>
										<?php endif;?>
									<?php endif; ?>
								</tr>
								</thead>
								<tbody>
								<?php if (!empty($issetDynamicVariants)): ?>
									<?php $totalDynamicVariants  = count($issetDynamicVariants);?>
									<?php $counterDynamicVariant = 0;?>

									<?php foreach ($issetDynamicVariants as $keyVariants => $issetDynamicVariant): ?>
										<?php $countRows       = 0;?>
										<?php $dynamicVariants = explode('_', $issetDynamicVariant);?>
										<?php $counterDynamicVariant++;?>
										<tr>

											<?php if ($dynamicVariants):?>
												<?php foreach ($dynamicVariants as $dynamicVariant):?>
													<td>
														<?php if (isset($dynamicTypes[$dynamicVariant])):?>
															<?php echo RedshopbHelperCollection::getProductItemValueFromType(
																$dynamicTypes[$dynamicVariant]->type_id,
																$dynamicTypes[$dynamicVariant]
															);?>
														<?php endif;?>
													</td>
													<?php $countRows++;?>
												<?php endforeach;?>
											<?php endif;?>

											<?php if (isset($staticTypes)):?>
												<?php $itemId = 0;?>

												<?php foreach ($staticTypes as $key => $staticType):?>
													<?php $class       = '';?>
													<?php $text        = '';?>
													<?php $pricesArray = false;?>

													<?php if ($counterDynamicVariant == 1 && count($issetDynamicVariants) == 1):?>
														<?php $class = ' enlarge';?>
													<?php endif;?>

													<?php if (isset($issetItemsPrices[$staticType->id . '_' . $issetDynamicVariant])):?>
														<?php $pricesArray = $issetItemsPrices[$staticType->id . '_' . $issetDynamicVariant];?>
													<?php elseif (isset($issetItemsPrices[$staticType->id])): ?>
														<?php $pricesArray = $issetItemsPrices[$staticType->id];?>
													<?php endif;?>

													<?php if ($pricesArray):?>
														<?php $lastPosition = count($pricesArray);?>
														<?php $position     = 1;?>
														<td class="text-center itemprice inStock<?php echo $class;?>">

															<?php foreach ($pricesArray as $onePrice):?>
																<?php $itemId   = $onePrice->type_id;
																$isLocked       = RedshopbEntityProduct_Item::getInstance($itemId)->canReadOnly(); ?>
																<?php $disabled = ' disabled="disabled"';?>

																<?php if ($canEdit || !$isLocked):?>
																	<?php $disabled = '';?>
																<?php endif;?>
																<div class="js-price-wrapper" data-sku="<?php echo $onePrice->sku;?>">
																	<div class="input-prepend input-append">
																		<span class="add-on">
																			<?php echo $onePrice->alpha3;?>
																		</span>

																		<input type="text" class="input-mini hasPopover"
																			   data-price-id="<?php echo $onePrice->id;?>"
																			   value="<?php echo $onePrice->price;?>"
																			   name="jform[price][<?php echo $onePrice->id; ?>]"
																			   id="jform_price_<?php echo $onePrice->id; ?>"
																			<?php echo $disabled;?>/>

																		<?php if ($canEdit):?>
																			<?php $divClass = 'btn-group';?>

																			<?php if ($keyVariants != 0):?>
																				<?php $divClass .= ' dropup';?>
																			<?php endif;?>
																			<?php $ulClass = 'dropdown-menu text-left';?>

																			<?php if ($countRows + 1 >= $maxCountRows):?>
																				<?php $ulClass .= ' pull-right';?>
																			<?php endif;?>

																			<div class="js-record-controls <?php echo $divClass;?>">
																				<button tabindex="-1" data-toggle="dropdown" class="btn dropdown-toggle">
																					<span class="caret"></span>
																				</button>
																				<ul class="<?php echo $ulClass;?>">

																					<?php if (!$isLocked): ?>
																					<li>
																						<a href="javascript:void(0);" data-action="updatePrice" data-id="<?php echo $onePrice->id;?>">
																						<i class="icon-save"></i> <?php echo Text::_('JTOOLBAR_APPLY');?>
																						</a>
																					</li>
																					<?php endif ?>
																					<li>
																						<a href="<?php echo RedshopbRoute::_($itemUrl . '&id=' . $onePrice->id . '&task=all_price.edit');?>" data-action="leaveForm">
																							<i class="icon-edit"></i> <?php echo Text::_('JTOOLBAR_EDIT');?>
																						</a>
																					</li>
																					<?php if (!$isLocked): ?>
																					<li>
																						<a href="javascript:void(0);" data-action="deletePrice" data-id="<?php echo $onePrice->id;?>">
																							<i class="icon-trash"></i> <?php echo Text::_('JTOOLBAR_DELETE');?>
																						</a>
																					</li>
																					<?php endif ?>
																				</ul>
																			</div>
																		<?php endif;?>
																	</div>
																	<?php $salesType = '';?>

																	<?php if (array_key_exists($onePrice->sales_type, $salesTypes)):?>
																		<?php $salesType = $salesTypes[$onePrice->sales_type];?>
																	<?php endif;?>
																	<div id="price_description_<?php echo $onePrice->id;?>" class="hide">
																		<table class="table table-condensed table-bordered">
																			<tr>
																				<th>
																					<?php echo Text::_('COM_REDSHOPB_SKU');?>
																				</th>
																				<td>
																					<?php echo $onePrice->sku;?>
																				</td>
																			</tr>
																			<tr>
																				<th><?php echo Text::_('COM_REDSHOPB_SALES_TYPE');?></th>
																				<td><?php echo $salesType;?></td>
																			</tr>
																			<?php if ($onePrice->sales_name):?>
																				<tr>
																					<th><?php echo Text::_('COM_REDSHOPB_DISCOUNT_SALES_NAME');?></th>
																					<td><?php echo $onePrice->sales_name;?></td>
																				</tr>
																			<?php endif;?>
																				<tr>
																					<th><?php echo Text::_('COM_REDSHOPB_START');?></th>
																					<td>
																						<?php if ($onePrice->starting_date == '0000-00-00 00:00:00'):?>
																							<?php echo ' - ';?>
																						<?php else:?>
																							<?php echo HTMLHelper::_('date', $onePrice->starting_date, Text::_('DATE_FORMAT_LC4'), null);?>
																						<?php endif;?>
																					</td>
																				</tr>
																				<tr>
																					<th><?php echo Text::_('COM_REDSHOPB_END');?></th>
																					<td>
																						<?php if ($onePrice->ending_date == '0000-00-00 00:00:00'):?>
																							<?php echo ' - ';?>
																						<?php else:?>
																							<?php echo HTMLHelper::_('date', $onePrice->starting_date, Text::_('DATE_FORMAT_LC4'), null);?>
																						<?php endif;?>
																					</td>
																				</tr>
																			</table>
																		</div>
																	</div>
																	<?php if ($position == $lastPosition && $canEdit && !$isLocked):?>
																	<div>
																		<a href="<?php echo RedshopbRoute::_($itemUrl . '&product_id=' . $productId . '&product_item_id=' . $onePrice->type_id . '&type=product_item&task=all_price.add');?>" class="btn btn-success btn-small" data-action="leaveForm">
																			<i class="icon-file-text-alt"></i> <?php echo Text::_('JTOOLBAR_NEW');?>
																		</a>
																	</div>
																	<?php endif;?>
															<?php $position++;?>
															<?php endforeach;?>
														</td>
													<?php else:?>
														<?php $firstPrice    = false;?>
														<?php $issetItemsKey = $staticType->id . '_' . $issetDynamicVariant;?>

														<?php if (isset($issetItems[$issetItemsKey])):?>
															<?php $firstPrice = $issetItems[$issetItemsKey];?>
														<?php elseif (isset($issetItems[$staticType->id])):?>
															<?php $firstPrice = $issetItems[$staticType->id];?>
														<?php endif;?>

														<?php if ($canEdit && $firstPrice && count($displayData['activeFilters']) == 0):?>
															<td class="text-center inStock<?php echo $class;?>">
																<div class="js-price-wrapper" data-sku="">
																	<div class="input-prepend input-append" id="divNewPrice_<?php echo $firstPrice->id;?>">
																		<span class="add-on"><?php echo $defaultCurrency?></span>
																		<input type="text"
																			   class="input-mini"
																			   name="jform[price_new][<?php echo $firstPrice->id;?>]"
																			   id="jform_price_new_<?php echo $firstPrice->id;?>"/>
																		<a href="javascript:void(0);" class="btn" data-action="saveNewPrice" data-id="<?php echo $firstPrice->id;?>" data-currency-alpha="<?php echo $defaultCurrency?>" data-currency-id="<?php echo $defaultCurrencyId; ?>">
																			<i class="icon-save"></i>
																		</a>
																	</div>
																</div>
																<div>
																	<a href="<?php echo RedshopbRoute::_($itemUrl . '&product_id=' . $productId . '&product_item_id=' . $firstPrice->id . '&type=product_item&task=all_price.add');?>" class="btn btn-success btn-small" data-action="leaveForm">
																		<i class="icon-file-text-alt"></i> <?php echo Text::_('JTOOLBAR_NEW');?>
																	</a>
																</div>
															</td>
														<?php else:?>
															<td class="text-center notExists<?php echo $class;?>"></td>
														<?php endif;?>
													<?php endif;?>
													<?php $countRows++;?>

													<?php if ($totalDynamicVariants <= $counterDynamicVariant):?>
														<?php unset($staticTypes[$key]);?>
													<?php endif;?>

													<?php if ($countRows >= $maxCountRows):?>
														<?php break;?>
													<?php endif;?>
												<?php endforeach;?>
											<?php endif;?>

											<?php if ($countRows < $maxCountRows):?>
												<?php for ($j = 0; $j + $countRows < $maxCountRows; $j++):?>
													<td></td>
												<?php endfor;?>
											<?php endif;?>
										</tr>
									<?php endforeach;?>
								<?php endif; ?>
								</tbody>
							</table>
						</div>
					</li>
				<?php endwhile; ?>
			</ul>
		</div>
	<?php endif;?>
	<input type="hidden" name="task" value="" />
	<input id="price_id" type="hidden" value="" name="cid[]" />
	<input type="hidden" name="jform[default_currency_id]" id="default_currency_id" value="<?php echo $defaultCurrencyId; ?>" />
	<input type="hidden" name="boxchecked" value="0" />
	<?php echo HTMLHelper::_('form.token'); ?>
</form>
