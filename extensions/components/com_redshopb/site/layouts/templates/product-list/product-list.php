<?php
/**
 * @package     Aesir.E-Commerce.Template
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;

if (!isset($showCategoryFilter))
{
	$showCategoryFilter = false;
}

if (!isset($productListDivId))
{
	$productListDivId = 'pageProductList';
}

if (!isset($showPagination))
{
	$showPagination = true;
}

$collectionProducts = $extThis->collectionProducts;
$input              = Factory::getApplication()->input;
$pageView           = $input->getCmd('view');
$searchView         = $input->getString('search');
?>
<div class="col-md-12 top-nav-productlist">
	<div class="pull-left redshopb-productlist-count">
		<?php echo $extThis->productsCount . ' ' . Text::_('COM_REDSHOPB_CATEGORY_NUMBER_OF_PRODUCTS'); ?>
	</div>
	<div class="pull-right redshopb-shop-productlist-show">
		<?php $availableStyles = array('list', 'grid'); ?>
		{product-list.show_as}
	</div>
	<?php if (isset($showCategoryFilter) && $showCategoryFilter) : ?>
		<div class="pull-right redshopb-productlist-product_category">
			<?php echo $categoryField->renderField(); ?>
		</div>
	<?php endif; ?>

	<?php if (isset($showProductFilter) && $showProductFilter) : ?>
		<div class="pull-right redshopb-category-show-products">
			<?php echo $productShowField->renderField(); ?>
		</div>
	<?php endif; ?>

	<?php if (isset($sortByField)) : ?>
	<div class="pull-right redshopb-productlist-sort-by">
		<?php echo $sortByField->renderField(); ?>
	</div>
	<?php endif; ?>

	<?php if (isset($sortDirField)) : ?>
		<div class="pull-right redshopb-productlist-sort-dir">
			<?php echo $sortDirField->renderField(); ?>
		</div>
	<?php endif; ?>
</div>
<script type="text/javascript">
	function JAjaxProductListPageUpdate(page) {
		jQuery.ajax({
			url: '<?php echo Uri::root(); ?>index.php?option=com_redshopb&task=shop.ajaxGetProductListPage',
			type: 'post',
			data: {
				'page': page,
				'noPages': <?php echo (int) $productsPagesCount; ?>,
				'category_id': <?php echo isset($extThis->category->id) ? $extThis->category->id : 0; ?>,
				'collection_id': <?php echo isset($extThis->collectionId) ? $extThis->collectionId : 0; ?>,
				'show_as': '<?php echo $showAs; ?>',
				'layout':  '<?php echo $input->getCmd('layout'); ?>',
				'id': '<?php echo $input->getId('id'); ?>'
			},
			beforeSend: function (xhr) {
				jQuery('.redcore').addClass('opacity-40');
				jQuery('#<?php echo $productListDivId; ?>').html('<div class="spinner pagination-centered"><?php echo HTMLHelper::image('media/com_redshopb/images/ajax-loader.gif', ''); ?></div>');
			}
		}).done(function (data) {
			jQuery('#<?php echo $productListDivId; ?>').html(data).find('select').chosen({
				"disable_search_threshold": 10,
				"allow_single_deselect": true
			});
			jQuery('#<?php echo $productListDivId; ?>').trigger('change');

			if(typeof assignAddToFavoriteButtons != 'undefined')
			{
				assignAddToFavoriteButtons();
			}

			jQuery('.redcore').removeClass('opacity-40');
			Holder.run();
		});
		jQuery('body').animate({scrollTop: 300}, 500);
	}
</script>
<div class="customer-list" id="<?php echo $productListDivId; ?>">

	<?php if (empty($collectionProducts) || !$extThis->productsCount) : ?>
		<div class="alert alert-info">
			<button type="button" class="close" data-dismiss="alert">&times;</button>
			<div class="pagination-centered">
				<?php
				if ($pageView == 'shop' && $searchView):
					$nothingText = Text::_('COM_REDSHOPB_NOTHING_TO_DISPLAY_SEARCH');
				else:
					$nothingText = Text::_('COM_REDSHOPB_NOTHING_TO_DISPLAY');
				endif;
				?>
				<h3><?php echo $nothingText; ?></h3>
			</div>
		</div>
	<?php else :
		$numberOfPages = $productsPagesCount;
		$ajaxJS        = 'redSHOPB.shop.updatePage(event, ' . (int) RedshopbApp::isUseAjaxReadMorePagination() . ');';
		$collectionId  = $extThis->collectionId;
		?>
		{template.product-list-collection}
	<?php endif; ?>
</div>
