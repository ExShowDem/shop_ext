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

extract($displayData);

// HTML helpers
HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('vnrbootstrap.tooltip');
HTMLHelper::_('rjquery.chosen', 'select');

$defaultCurrency = RedshopbHelperPrices::getCurrency($item->user_id, 'employee');

$currentPage = $pagination->get('pagesCurrent', 1);
$lastPage    = $pagination->get('pagesStop', 1);
$limit       = $pagination->get('limit', 10);
$start       = $pagination->get('limitstart', 0);

$prevPage = false;

if ($currentPage > 1)
{
	$prevPage = $currentPage - 1;
}

$prevStart = ($limit * $prevPage) - $limit;

if ($prevStart < 0)
{
	$prevStart = 0;
}

$nextPage  = $currentPage + 1;
$nextStart = $limit * $nextPage;

if ($nextPage > $lastPage)
{
	$nextPage = false;
}
?>

<form method="post" action="<?php echo RedshopbRoute::_('index.php?option=com_redshopb&view=myfavoritelist');?>" id="modalProductList">
	<div class="redshopb-favlist-tool container-fluid">
		<div class="row-fluid">
			<div class="span12">
				<div class="input-append">
					<input type="text"
						   id="modalSearch"
						   name="filter[search_shop_products]"
						   placeholder="<?php echo Text::_('JSEARCH_FILTER');?>"
						   value="<?php echo $shopState->get('filter.search_shop_products', '');?>"/>
					<a href="javascript:void(0);" onclick="jQuery('#modalProductList').submit();" class="btn"><i class="icon-search"></i></a>
					<a href="javascript:void(0);" onclick="jQuery('#modalSearch').val(''); jQuery('#modalProductList').submit();"  class="btn" ><i class="icon-remove"></i></a>
				</div>
				<div class="pull-right">
					<?php echo $pagination->getLimitBox();?>
				</div>
			</div>
		</div>
		<br/>
		<div class="row-fluid">
			<div class="span12">
				<?php if (empty($products)):?>
					<?php echo RedshopbLayoutHelper::render('common.nodata'); ?>
				<?php else :?>
					<table class="table">
						<thead>
						<th>&nbsp;</th>
						<th><?php echo Text::_('COM_REDSHOPB_MYFAVORITELIST_PRODUCTS_LIST_PRODUCT'); ?></th>
						<th><?php echo Text::_('COM_REDSHOPB_MYFAVORITELIST_PRODUCTS_LIST_STOCK_STATUS'); ?></th>
						<th><?php echo Text::_('COM_REDSHOPB_MYFAVORITELIST_PRODUCTS_LIST_PRICE'); ?></th>
						<th>&nbsp;</th>
						</thead>
						<tfoot>
						<tr>
							<td>
								<?php if ($prevPage):?>
									<a href="javascript:void(0)"
									   class="btn hasTooltip"
									   title = "<?php echo Text::_('JPrev');?>"
									   onclick="jQuery('#com_redshopb_myfavoritelists_shop_start').val('<?php echo $prevStart;?>'); jQuery('#modalProductList').submit();">
										<i class="icon-arrow-left"></i>
									</a>
								<?php endif;?>
							</td>
							<td colspan="3">
								<?php if ($lastPage != 1):?>
								<div class="text-center center">
									<?php echo $pagination->getPagesCounter();?>
								</div>
								<?php endif;?>
							</td>
							<td>
								<?php if ($nextPage):?>
									<?php $nextStart = $limit * $nextPage;?>
									<a href="javascript:void(0)"
									   class="btn pull-right hasTooltip"
									   title = "<?php echo Text::_('JNext');?>"
									   onclick="jQuery('#com_redshopb_myfavoritelists_shop_start').val('<?php echo $nextStart;?>'); jQuery('#modalProductList').submit();">
										<i class="icon-arrow-right"></i>
									</a>
								<?php endif;?>
							</td>
						</tr>
						</tfoot>
						<tbody>
						<?php foreach ($products AS $product):?>
							<tr>
								<td>
									<?php $image = RedshopbHelperProduct::getProductImageThumbPath($product->id); ?>

									<?php if ($image): ?>
										<img src="<?php echo $image ?>" />
									<?php else: ?>
										<?php echo RedshopbHelperMedia::drawDefaultImg(72, 72, Text::_('COM_REDSHOPB_NO_IMAGE_LABEL')) ?>
									<?php endif; ?>
								</td>
								<td>
									<div class="product-sku"><?php echo $product->sku; ?></div>
									<div class="product-name"><?php echo $product->name; ?></div>
								</td>
								<td>
									<?php if (RedshopbHelperStockroom::productHasInStock($product->id)): ?>
										<div class="product-on-stock"><?php echo Text::_('COM_REDSHOPB_PRODUCT_ON_STOCK'); ?></div>
									<?php else: ?>
										<div class="product-no-stock"><?php echo Text::_('COM_REDSHOPB_PRODUCT_NO_STOCK'); ?></div>
									<?php endif; ?>
								</td>
								<td>
									<?php $price = RedshopbHelperPrices::getProductsPrice(array($product->id), $item->user_id, 'employee');?>

									<?php if (!empty($price[$product->id])):?>
										<?php $price = $price[$product->id];?>
										<?php echo RedshopbHelperProduct::getProductFormattedPrice($price->price, $price->currency); ?>
									<?php else:?>
										<?php echo RedshopbHelperProduct::getProductFormattedPrice(0, $defaultCurrency); ?>
									<?php endif;?>
								</td>
								<td>
									<a  href="javascript:void(0);" onclick="redSHOPB.favoritelist.addProduct(event)"
										class="btn btn-success btn-small" data-product_id="<?php echo $product->id; ?>">
										<i class="icon icon-plus"></i> <?php echo Text::_('JADD')?>
									</a>
								</td>
							</tr>
						<?php endforeach;?>
						</tbody>
					</table>
				<?php endif;?>
			</div>
		</div>

		<input type="hidden" id="com_redshopb_myfavoritelists_shop_start" name="com_redshopb_myfavoritelists_shop_start" value="<?php echo $start;?>" />
		<input type="hidden" id="favorite_list_id" name="id" value="<?php echo $favId; ?>" />
		<input type="hidden" name="task" value="myfavoritelist.ajaxGetProductList" />
		<input type="hidden" name="option" value="com_redshopb" />
		<?php echo HTMLHelper::_('form.token'); ?>

	</div>
</form>
