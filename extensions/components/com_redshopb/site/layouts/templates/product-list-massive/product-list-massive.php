<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Layouts.Templates.Product-List-Massive
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;

use Joomla\CMS\Uri\Uri;

use Joomla\CMS\HTML\HTMLHelper;

use Joomla\CMS\Session\Session;

use Joomla\CMS\Language\Text;

/**
 * Layout variables
 * ============================
 * @var   array $displayData Layout data
 */
extract($displayData);

$flexsliderOptions    = array('slideshow' => false, 'directionNav' => false, 'animation' => 'slide', 'animationLoop' => false);
$flexsliderOptionsReg = RedshopbHelperShop::options2Jregistry($flexsliderOptions);

HTMLHelper::_('rjquery.flexslider', '.flexslider', $flexsliderOptions);

RedshopbHtml::loadFooTable();

$companyId     = RedshopbHelperCompany::getCompanyIdByCustomer($extThis->customerId, $extThis->customerType);
$collectionUse = RedshopbHelperShop::inCollectionMode(RedshopbEntityCompany::getInstance($companyId));

/** @var RedshopbModelShop $model */
$model = RedshopbModel::getFrontInstance('Shop');

$extThis->productMassive = array();

foreach ($extThis->collectionProducts as $collection => $collectionProduct)
{
	$model->setState('product_collection', $collection);

	$extThis->productMassive[$collection] = $model->prepareItemsForShopView(
		$collectionProduct->items, $extThis->customerId, $extThis->customerType, $collection, false
	);

	if (!$collectionUse)
	{
		continue;
	}

	$prices = array();

	foreach ($extThis->productMassive[$collection]->prices as $price)
	{
		if (!array_key_exists($price->product_id, $prices))
		{
			$prices[$price->product_id] = array();
		}

		$prices[$price->product_id][$price->id] = $price;
	}

	$extThis->productMassive[$collection]->prices = $prices;
}

$showCategoryFilter = !isset($showCategoryFilter) ? false : $showCategoryFilter;
$productListDivId   = !isset($productListDivId) ? 'pageProductList' : $productListDivId;
$showPagination     = !isset($showPagination) ? true : $showPagination;
$collectionProducts = $extThis->collectionProducts;
$input              = Factory::getApplication()->input;
$pageView           = $input->getCmd('view');
$searchView         = $input->getString('search');
?>
<div class="row">
	<div class="col-md-12 top-nav-productlist">
		<div class="pull-left redshopb-productlist-count">
			<?php echo $extThis->productsCount . ' ' . Text::_('COM_REDSHOPB_CATEGORY_NUMBER_OF_PRODUCTS') ?>
		</div>
		<?php if (isset($showCategoryFilter) && $showCategoryFilter): ?>
			<div class="pull-right redshopb-productlist-product_category">
				<?php echo $categoryField->renderField(); ?>
			</div>
		<?php endif; ?>

		<?php if (isset($showProductFilter) && $showProductFilter): ?>
			<div class="pull-right redshopb-category-show-products">
				<?php echo $productShowField->renderField(); ?>
			</div>
		<?php endif; ?>

		<?php if (isset($sortByField)): ?>
			<div class="pull-right redshopb-productlist-sort-by">
				<?php echo $sortByField->renderField(); ?>
			</div>
		<?php endif; ?>
	</div>
</div>
<script type="text/javascript">
	(function ($) {
		$(document).ready(function () {
			$('#pageProductList').on('change', '.dropDownAttribute', function () {
				var $this = $(this),
					parameters = $this.attr('id').split('_'),
					collectionid = $this.data('collection'),
					currencyid = $this.data('currency'),
					dropDownSelected = $this.val(),
					$tableVariants = $('#tableVariants_' + collectionid + '_' + parameters[1]),
					$productThumbs = $('#productThumbs_' + collectionid + '_' + parameters[1]),
					$accessory = $('#accessory_' + collectionid + '_' + parameters[1]),
					tableVariantsHeight = $tableVariants.height(),
					productThumbsHeight = $productThumbs.height();

				$.ajax({
					url: '<?php echo Uri::root(); ?>index.php?option=com_redshopb&task=shop.ajaxChangeDropDownAttribute'
					+ '&product_id=' + parameters[1]
					+ '&drop_down_selected=' + dropDownSelected
					+ '&collection_id=' + collectionid
					+ '&currency_id=' + currencyid,
					type: 'POST',
					data: {
						"<?php echo Session::getFormToken() ?>": 1
					},
					beforeSend: function () {
						$tableVariants.empty().html(
							'<div class="spinner pagination-centered"><?php echo HTMLHelper::image('media/com_redshopb/images/ajax-loader.gif', '') ?></div>'
						).find('.spinner').height(tableVariantsHeight);
						$productThumbs.empty().html(
							'<div class="spinner pagination-centered"><?php echo HTMLHelper::image('media/com_redshopb/images/ajax-loader.gif', '') ?></div>'
						).find('.spinner').height(productThumbsHeight);
					}
				}).done(function (data) {
					$tableVariants.html(data);
					$productThumbs.empty();
					var sliderImage = '';
					if ($('#productImages').length > 0) {
						$('#productImages .flexslider').appendTo($productThumbs);
						$('#productImages').remove();
						sliderImage = ', #flexsliderImage_' + parameters[1] + '_' + parameters[2];
						$productThumbs.removeClass('hide');
						Holder.run();
						$('.flexslider').flexslider(<?php echo $flexsliderOptionsReg->toString() ?>);
					}
					else {
						$productThumbs.addClass('hide');
					}
					if ($('#washCareLink').length > 0) {
						$('#washCareLink_' + parameters[1] + '_' + collectionid).attr('href', $('#washCareLink').text());
						$('#washCareLink').remove();
					}
					$accessory.empty();
					if ($('#divAccessory').length > 0) {
						$('#divAccessory .dropDownAccessory').appendTo($accessory);
						$('#divAccessory').remove();
						var currentAccessory = $('#dropDownAccessory_' + parameters[1]);
						currentAccessory.multiselect({'nonSelectedText': currentAccessory.find('optgroup:first').attr('label')});
					}

					initFootableRedshopb();
					initHideItemsRow();
					$('.carousel-variants').bind('slid', function () {
						fooTableRedraw();
						initHideItemsRow();
					});
				});
			});

			$('#pageProductList').on('click', '.clearAmounts', function () {
				var $this = $(this),
					parameters = $this.attr('id').split('_'),
					$tableVariants = $('#tableVariants_' + parameters[1] + '_' + parameters[2]);
				$tableVariants.find('.amountInput').val('');
			});
		});
	})(jQuery);

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
				'layout': '<?php echo $input->getCmd('layout'); ?>',
				'id': '<?php echo $input->getId('id'); ?>'
			},
			beforeSend: function (xhr) {
				jQuery('.redcore').addClass('opacity-40');
				jQuery('#<?php echo $productListDivId; ?>').html('<div class="spinner pagination-centered"><?php echo HTMLHelper::image('media/com_redshopb/images/ajax-loader.gif', '') ?></div>');
			}
		}).done(function (data) {
			jQuery('#<?php echo $productListDivId; ?>').html(data).find('select').chosen({
				"disable_search_threshold": 10,
				"allow_single_deselect": true
			});
			jQuery('#<?php echo $productListDivId; ?>').trigger('change');

			if (typeof assignAddToFavoriteButtons != 'undefined') {
				assignAddToFavoriteButtons();
			}

			jQuery('.redcore').removeClass('opacity-40');
			Holder.run();
		});
		jQuery('body').animate({scrollTop: 300}, 500);
	}

	function initHideItemsRow() {
		var rows = jQuery('.items-row');
		rows.each(function (ir, er) {
			var row = jQuery(er);
			var showRow = true;
			var columns = row.find('.item-column');
			if (columns.length == 0) {
				showRow = false;
			}
			if (!showRow) {
				row.css('display', 'none');
			}
		});
	}
</script>
<div class="customer-list" id="<?php echo $productListDivId; ?>">

	<?php if (empty($collectionProducts) || !$extThis->productsCount): ?>
		<div class="alert alert-info">
			<button type="button" class="close" data-dismiss="alert">&times;</button>
			<div class="pagination-centered">
				<?php
				$nothingText = $pageView == 'shop' && $searchView ? Text::_('COM_REDSHOPB_NOTHING_TO_DISPLAY_SEARCH') : Text::_('COM_REDSHOPB_NOTHING_TO_DISPLAY');
				?>
				<h3><?php echo $nothingText; ?></h3>
			</div>
		</div>
	<?php else: ?>
		<?php
		$numberOfPages = $productsPagesCount;
		$ajaxJS        = 'redSHOPB.shop.updatePage(event, ' . (int) RedshopbApp::isUseAjaxReadMorePagination() . ');';
		$collectionId  = $extThis->collectionId;
		$showAs        = 'massive';
		?>
		{template.product-list-collection}
	<?php endif; ?>
</div>
