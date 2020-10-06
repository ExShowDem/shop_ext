<?php
/**
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Views
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

$data = $displayData;

$state                = $data['state'];
$collectionId         = $data['collectionId'];
$preparedItems        = $data['preparedItems'];
$pagination           = $data['pagination'];
$formName             = $data['formName'];
$placeOrderPermission = (isset($data['placeOrderPermission']) ? $data['placeOrderPermission'] : true);
$return               = isset($data['return']) ? $data['return'] : null;
$action               = RedshopbRoute::_('index.php?option=com_redshopb&view=shop');
HTMLHelper::_('vnrbootstrap.framework');
HTMLHelper::_('vnrbootstrap.modal', 'myModal');
HTMLHelper::_('vnrbootstrap.modal', 'zoomModal');

$config = RedshopbEntityConfig::getInstance();
$isShop = RedshopbHelperPrices::displayPrices();

// Allow to override the form action
if (isset($data['action']))
{
	$action = $data['action'];
}
?>
<form action="<?php echo $action; ?>" name="<?php echo $formName; ?>" class="adminForm" id="<?php echo $formName; ?>"
	  method="post">
	<?php if (empty($preparedItems) || empty($preparedItems->items)) : ?>
		<?php echo RedshopbLayoutHelper::render('common.nodata'); ?>
	<?php else : ?>

		<?php foreach ($preparedItems->items as $item): ?>
			<div class="productList container-fluid">
				<div class="row-fluid">
					<div class="span12">
						<?php if ($item->date_new != '0000-00-00' && strtotime($item->date_new) && time() <= strtotime('+' . $config->getInt('date_new_product', 14) . ' day', strtotime($item->date_new))): ?>
							<span class="badge badge-important"><?php echo Text::_('COM_REDSHOPB_SHOP_NEW'); ?></span>&nbsp;
						<?php endif; ?>
						<div class="shop-cart-product-title">
							<h3>
									<span class="shop-list-product-sku">
										<?php echo Text::_('COM_REDSHOPB_SHOP_MODEL'); ?> : <?php echo $item->sku; ?>
									</span>
								<span class="shop-list-product-name">
										<?php echo $item->name; ?>
									</span>
							</h3>
						</div>
					</div>
				</div>
				<div class="row-fluid">
					<div class="span2 productThumbs" id="productThumbs_<?php echo $collectionId; ?>_<?php echo $item->id; ?>">
						<div class="flexslider">
							<ul class="slides">
								<?php if (isset($preparedItems->productImages[$item->id])): ?>
									<?php foreach ($preparedItems->productImages[$item->id] as $image):
										?>
										<li>
											<a href="<?php echo RedshopbRoute::_('index.php?option=com_redshopb&task=shop.ajaxGetZoomImage&productId=' . $item->id . (isset($preparedItems->dropDownSelected[$item->id]) ? '&flatAttrId=' . $preparedItems->dropDownSelected[$item->id] : '') . '&mediaId=' . $image->id);?>" class="btn btn-link btn-small pull-right" id="zoomLink<?php echo $item->id . '_' . $preparedItems->collectionId; ?>" data-toggle="modal" data-target="#zoomModal">
												<div class="thumbnail">
													<img src="<?php echo RedshopbHelperThumbnail::originalToResize($image->name, 144, 144, 100, 0, 'products', false, $image->remote_path); ?>" alt="<?php echo RedshopbHelperThumbnail::safeAlt($image->alt, $item->name) ?>" />
												</div>
											</a>
										</li>
									<?php endforeach; ?>
								<?php else: ?>
									<li>
										<div class="thumbnail">
											<div class="emptyImage">
												<?php echo RedshopbHelperMedia::drawDefaultImg(144, 144, Text::_('COM_REDSHOPB_NO_IMAGE_LABEL'), '#999999', '#dfdfdf') ?>
											</div>
										</div>
									</li>
								<?php endif; ?>
							</ul>
						</div>
					</div>
					<div class="span10">
						<div class="row-fluid">
							<div class="clearfix">
								<div class="span6">
									<?php if (isset($preparedItems->dropDownTypes[$item->id])) : ?>
										<?php
										// If more one type attributes - display select ?>
										<?php if (count($preparedItems->dropDownTypes[$item->id]) > 1): ?>
											<a href="<?php echo RedshopbRoute::_('index.php?option=com_redshopb&task=shop.ajaxWashAndCare&productId=' . $item->id . '&flatAttrId=' . $preparedItems->dropDownSelected[$item->id]);?>" class="btn btn-info btn-wash-care-info-multiple pull-right" id="washCareLink_<?php echo $item->id . '_' . $preparedItems->collectionId; ?>" data-toggle="modal" data-target="#myModal">
												<i class="icon-info-sign"></i> <?php echo Text::_('COM_REDSHOPB_SHOP_INFO'); ?>
											</a>
											<?php
											$options = array();

											// Generate select
											foreach ($preparedItems->dropDownTypes[$item->id] as $dropDownType)
											{
												$text = RedshopbHelperCollection::getProductItemValueFromType($dropDownType->type_id, $dropDownType, true);

												$options[] = HTMLHelper::_(
													'select.option', $dropDownType->id, $text, 'value', 'text'
												);
											}
											?>
											<?php
											echo HTMLHelper::_(
												'select.genericlist', $options, 'dropDownType[' . $item->id . ']',
												' class="dropDownAttribute pull-left" data-collection="' . $preparedItems->collectionId . '" data-currency="' . $preparedItems->currency . '" ', 'value', 'text', $preparedItems->dropDownSelected[$item->id],
												'dropDownType_' . $item->id
												. '_' . $dropDownType->product_attribute_id
											);
											?>
											<?php
										else: // Only one variant, so display as text
											?>
											<a href="<?php echo RedshopbRoute::_('index.php?option=com_redshopb&task=shop.ajaxWashAndCare&productId=' . $item->id . '&flatAttrId=' . $preparedItems->dropDownTypes[$item->id][0]->id);?>" class="btn btn-info btn-wash-care-info-single pull-right" id="washCareLink_<?php echo $item->id . '_' . $preparedItems->collectionId; ?>" data-toggle="modal" data-target="#myModal">
												<i class="icon-info-sign"></i>  <?php echo Text::_('COM_REDSHOPB_SHOP_INFO'); ?>
											</a>
											<?php echo '<h4 class="nowrap pull-left">' . RedshopbHelperCollection::getProductItemValueFromType(
												$preparedItems->dropDownTypes[$item->id][0]->type_id, $preparedItems->dropDownTypes[$item->id][0], true
											) . ' </h4>';
											?>
										<?php endif; ?>
									<?php endif; ?>
								</div>
								<div class="span3" id="accessory_<?php echo $collectionId; ?>_<?php echo $item->id; ?>">
									<?php
									if (isset($preparedItems->accessories[$item->id]))
									{
										echo RedshopbHelperProduct::renderAccessoriesDropdown($preparedItems->accessories[$item->id], $item->id);
									}
									?>
								</div>
								<div class="span3">
									<?php
									if ($isShop && $placeOrderPermission) :
										?>
										<button class="btn btn-info btn-small add-to-cart" type="button" onclick="redSHOPB.shop.addToCart(event);" name="addtocart_<?php echo $item->id; ?>_<?php echo $collectionId; ?>">
											<i class="icon-shopping-cart"></i>
											<?php echo Text::_('COM_REDSHOPB_SHOP_ADD_TO_CART'); ?>
										</button>
										<button class="btn btn-small clearAmounts" type="button" id="clearAmounts_<?php echo $collectionId; ?>_<?php echo $item->id; ?>"
												name="clearAmounts_<?php echo $collectionId; ?>_<?php echo $item->id; ?>">
											<?php echo Text::_('COM_REDSHOPB_SHOP_CLEAR_CART'); ?>
										</button>
										<?php
									endif;
									?>
								</div>

							</div>
						</div>
						<div class="row-fluid" id="tableVariants_<?php echo $collectionId; ?>_<?php echo $item->id; ?>">
							<?php
							$dropDownSelected = isset($preparedItems->dropDownSelected[$item->id]) ? $preparedItems->dropDownSelected[$item->id] : null;

							echo RedshopbLayoutHelper::render('shop.attributesvariants', array(
									'staticTypes' => $preparedItems->staticTypes,
									'collectionId' => $collectionId,
									'dynamicTypes' => $preparedItems->dynamicTypes,
									'issetItems' => $preparedItems->issetItems,
									'issetDynamicVariants' => $preparedItems->issetDynamicVariants,
									'productId' => $item->id,
									'displayProductImages' => false,
									'productImages' => null,
									'prices' => $preparedItems->prices,
									'displayAccessories' => false,
									'accessories' => null,
									'showStockAs' => $preparedItems->showStockAs,
									'currency' => $preparedItems->currency,
									'dropDownSelected' => $dropDownSelected,
									'customerId' => $data['customerId'],
									'customerType' => $data['customerType'],
									'placeOrderPermission' => $placeOrderPermission
								)
							);
							?>
						</div>
					</div>
				</div>
			</div>
		<?php endforeach; ?>
		<?php echo $pagination->getPaginationLinks('tab.pagination.links', array('showLimitBox' => false)); ?>
		<input type="hidden" name="collectionId" value="<?php echo $preparedItems->collectionId ?>">
	<?php endif; ?>
	<input type="hidden" name="from_shop" value="1">
	<input type="hidden" name="boxchecked" value="0">
	<input type="hidden" name="task" value="">
	<?php echo HTMLHelper::_('form.token'); ?>
</form>
