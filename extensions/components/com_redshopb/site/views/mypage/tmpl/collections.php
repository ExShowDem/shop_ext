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
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Session\Session;

$level = 0;

HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('rdropdown.init');
HTMLHelper::_('vnrbootstrap.tooltip');
HTMLHelper::_('vnrbootstrap.checkbox');
HTMLHelper::_('rjquery.chosen', '.chosenSelect, .productListContainer select');
HTMLHelper::_('rholder.holder');
HTMLHelper::_('rjquery.flexslider');

RHelperAsset::load('lib/bootstrap-multiselect.js', 'com_redshopb');
RHelperAsset::load('lib/bootstrap-multiselect.css', 'com_redshopb');

RedshopbHtml::loadFooTable();

$categoriesPerPage = RedshopbApp::getConfig()->get('shop_categories_per_page', 12);

// @toDO: make sure that $this->categoriesCount is stable and is always from the same type
if (empty($categoriesPerPage) || !($categoriesPerPage) || is_array($this->categoriesCount))
{
	$categoriesPagesCount = 1;
}
else
{
	$categoriesPagesCount = ceil($this->categoriesCount / $categoriesPerPage);
}

if ($this->collectionMode)
{
	$flexsliderOptions    = array('slideshow' => false, 'directionNav' => false, 'animation' => 'slide', 'animationLoop' => false);
	$flexsliderOptionsReg = RedshopbHelperShop::options2Jregistry($flexsliderOptions);
	$currentCollection    = Factory::getApplication()->input->getInt('collectionId', 0);
}

RHelperAsset::load('shop.css', 'com_redshopb');
?>

<?php if (!empty($this->categories)): ?>
<script type="text/javascript">
	function JAjaxCategoriesPageUpdate(e)
	{
		var $categories = jQuery("#pageCategories");

		(function($) {
			var $e = jQuery(e);
			jQuery.ajax({
				url: '<?php echo Uri::root(); ?>index.php?option=com_redshopb&task=shop.ajaxGetCategoriesPage',
				cache: false,
				type: 'post',
				data: {'page' : $e.data('page'), 'noPages' : $e.data('page_total')},
				beforeSend: function (xhr) {
					<?php if (RedshopbApp::isUseAjaxReadMorePagination()): ?>
					$categories.append('<div class="spinner pagination-centered"><?php echo HTMLHelper::image('media/com_redshopb/images/ajax-loader.gif', '') ?></div>');
					<?php else: ?>
					$categories.html('<div class="spinner pagination-centered"><?php echo HTMLHelper::image('media/com_redshopb/images/ajax-loader.gif', '') ?></div>');
					<?php endif; ?>
				}
			}).done(function (data) {
				<?php if (RedshopbApp::isUseAjaxReadMorePagination()): ?>
				$categories.find(".spinner.pagination-centered").remove();
				$categories.find("#redshopbPaginationLoadMore").remove();
				$categories.append(data);
				<?php else: ?>
				$categories.html(data);
				<?php endif; ?>
				Holder.run();
			});
		})(jQuery);
	}
</script>
<?php endif; ?>

<div class="redshopb-mypage-collections">
	<div class="redshopb-mypage-collections-header">
		<h3><?php echo Text::_('COM_REDSHOPB_MY_COLLECTIONS_TITLE'); ?></h3>
	</div>
	<form method="post" id="adminForm" name="adminForm">
		<input type="hidden" name="option" value="com_redshopb">
		<input type="hidden" name="task" value="" />

		<?php if ($this->collectionMode && !empty($this->collections)): ?>
			<div class="productList">
			<?php $i = 0; ?>
			<?php echo HTMLHelper::_('vnrbootstrap.startTabSet', 'collection', array()); ?>

			<?php foreach ($this->collections as $collection): ?>
				<?php echo HTMLHelper::_('vnrbootstrap.addTab', 'collection', 'collectionForm_' . $collection->identifier, $collection->data); ?>
				<div class="spinner pagination-centered" id="collection_spinner_<?php echo $collection->identifier; ?>">
					<?php echo HTMLHelper::image('media/com_redshopb/images/ajax-loader.gif', '') ?>
				</div>
				<div id="collection_products_<?php echo $collection->identifier; ?>"></div>
				<?php echo HTMLHelper::_('vnrbootstrap.endTab'); ?>

				<?php if (empty($default) && $i == 0): ?>
					<?php $default = $collection->identifier; ?>
				<?php endif; ?>
				<?php $i++; ?>
			<?php endforeach; ?>
			<?php echo HTMLHelper::_('vnrbootstrap.endTabSet'); ?>
			<?php
			if (isset($default) && !$currentCollection)
			{
				$id = $default;
			}
			elseif ($currentCollection)
			{
				$id = $this->collectionId;
			}
			?>
			<script type="text/javascript">
				jQuery(document).ready(function () {
					jQuery('a[data-toggle="tab"]').on('shown', function (e) {
						id = e.target.hash.split('_')[1];
						limit = jQuery('#list_product_shop_limit').val();
						start = 0;

						if (!JTabIsLoaded(id)) {
							JLoadCollectionProducts(id, start, limit);
						} else {
							jQuery(document).trigger('refreshDropdowns', [id]);
						}
					});
					jQuery('#collectionTabs a[href="#collectionForm_<?php echo $id ?>"]').tab('show');
				});

				function setWashCareModal() {
					var popoverInit = false;

					jQuery('.washCareLink').on('click', function () {
						popoverInit = false;
					});

					jQuery('.myModal')
						.on('hidden.bs.modal', function () {
							jQuery(this).removeData('modal');
						})
						.on('show.bs.modal', function () {
							if (popoverInit == false) {
								jQuery(this).find('.modal-body').html('<div class="spinner pagination-centered"><?php echo HTMLHelper::image('media/com_redshopb/images/ajax-loader.gif', '') ?></div>');
							}
						})
				}

				function JTabIsLoaded(id) {
					loaded = window.loadedIds;

					if (loaded == undefined) {
						window.loadedIds = new Array(id);

						return false;
					}
					else {
						if (loaded.indexOf(id) != -1) {
							return true;
						}
						else {
							loaded.push(id);
							window.loadedIds = loaded;

							return false;
						}
					}
				}

				function JLoadCollectionProducts(id, start, limit) {
					var dataVar = {};
					onSale = jQuery('#filter_onsale').attr('checked');
					search = jQuery('#filter_search_shop_products').val();
					category = jQuery('#filter_product_category').val();
					flat_display = jQuery('#filter_attribute_flat_display').val();
					collection = jQuery('#filter_product_collection').val();

					link = '<?php echo Uri::root(); ?>index.php?option=com_redshopb&task=mypage.ajaxGetCollectionItems' +
						'&collectionId=' + id + '&start=' + start + '&limit=' + limit;

					if (onSale && onSale == 'checked') {
						link += '&onSale=1';
					}
					if (search !== undefined && search !== '') {
						link += '&search=' + search;
					}
					if (category !== undefined && category !== '') {
						link += '&category=' + category;
					}
					if (flat_display !== undefined && flat_display !== '') {
						link += '&flat_display=' + flat_display;
					}
					if (collection !== undefined && collection !== '') {
						link += '&collection=' + collection;
					}
					jQuery(".filter-form-shop [name^='filter']").each(function(idx,ele){
						dataVar[jQuery(ele).attr('name')] = jQuery(ele).val();
					});

					dataVar["<?php echo Session::getFormToken(); ?>"] = 1;

					jQuery.ajax({
						url: link,
						cache: false,
						type: 'post',
						data: dataVar,
						beforeSend: function (xhr) {
							jQuery('#collection_spinner_' + id).show();
							jQuery('#collection_products_' + id).html('');
						}
					}).done(function (data) {
						jQuery('#collection_spinner_' + id).hide();
						jQuery('#collection_products_' + id).html(data);
						limit = jQuery('#list_product_shop_limit').val();
						patern = new RegExp('limitstart.value=[0-9]+', 'i');
						jQuery('#collection_products_' + id + ' ul[class="pagination-list"] li a').each(function () {
							var onclick = jQuery(this).attr('onclick');

							if (onclick != undefined && onclick != null) {
								start = patern.exec(onclick)[0].split('=')[1];
							}
							else {
								start = 0;
							}

							jQuery(this).attr('onclick', 'JLoadCollectionProducts(' + id + ',' + start + ',' + limit + '); return false;');
						});

						Holder.run();
						jQuery('.flexslider').flexslider(<?php echo $flexsliderOptionsReg->toString() ?>);
						setWashCareModal();

						initFootableRedshopb();
						jQuery('.carousel-variants').bind('slid', function () {
							fooTableRedraw();
							initHideItemsRow();
						});

						jQuery('.dropDownAccessory').each(function(){jQuery(this).multiselect({'nonSelectedText': jQuery(this).find('optgroup:first').attr('label')})});
						jQuery(document).trigger('refreshDropdowns', [id]);
					});
				}

				jQuery(document).on('refreshDropdowns', function(e, wid) {
					var parent = jQuery('#filter_attribute_flat_display').parent();
					jQuery.ajax({
						url: '<?php echo Uri::root(); ?>index.php?option=com_redshopb&task=shop.ajaxRefreshDropDowns&collection_id=' + wid,
						dataType: 'html',
						type: 'POST',
						beforeSend: function()
						{
							jQuery('#filter_attribute_flat_display').remove();
							jQuery(parent).html('<div class="spinner pagination-centered"><?php echo HTMLHelper::image('media/com_redshopb/images/ajax-loader.gif', '');?></div>');
						}
					}).done(function(data)
					{
						jQuery(parent).hide();
						data = jQuery(data).find('.controls').html();
						jQuery(parent).html('<div class="control-group">' + data + '</div>');
						jQuery('#filter_attribute_flat_display').multiselect({'nonSelectedText': jQuery(this).find('optgroup:first').attr('label'), 'maxHeight' : 200});
						jQuery(parent).show();
					});
				});
			</script>
		</div>
		<?php elseif (!empty($this->categories)) : ?>
			<div class="redshopb-mypage-collections-categories" id="pageCategories">
				<?php echo RedshopbLayoutHelper::render(
					'shop.pages.categories',
					array(
						'categories'     => $this->categories,
						'showPagination' => true,
						'numberOfPages'  => $categoriesPagesCount,
						'currentPage'    => 1,
						'ajaxJS'         => 'JAjaxCategoriesPageUpdate(this);'
					)
				);?>
			</div>
		<?php else : ?>
			<?php echo RedshopbLayoutHelper::render('common.nodata'); ?>
		<?php endif; ?>
	</form>
</div>
