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

echo RedshopbBrowserBreadcrumbs::renderBreadcrumbs();

$dataContentBehavior = '';

if (RedshopbApp::isUseAjaxReadMorePagination())
{
	$dataContentBehavior = ' data-content-behavior="append"';
}
?>

<script>
(function ($) {
	$(document).ready(function () {
		$(document).bind('scroll',function() {
			$('div.productList-item').each(function() {
				if ($(this).offset().top < window.pageYOffset + $('div.productList-item').height()
					&& $(this).offset().top + $(this).height() > window.pageYOffset + $('div.productList-item').height()
				)
				{
					history.replaceState({}, '', "#"+$(this).attr('id'));
				}
			});
		});
	});
})(jQuery);
</script>

<div class="redshopb-shop-category">
	<?php if (empty($extThis->category)): ?>
		<?php echo RedshopbLayoutHelper::render('common.nodata'); ?>
	<?php else: ?>
		<form action="<?php echo $action;?>" method="post" id="adminForm" name="adminForm">
			<div class="row">
				<div class="col-md-12">
					<div class="container-fluid">
						<h1 class="redshopb-shop-category-title">{category.name}</h1>
						<div class="redshopb-shop-category-desc">{category.description}</div>
						<?php if (!empty($extThis->category->subcategories)): ?>
							<!-- Show all categories -->
							<div class="customer-container">
								<div class="customer-header">
									<i class="icon-user icon-2x"></i>
									<h3><?php echo Text::_('COM_REDSHOPB_CATEGORY_LIST_TITLE'); ?></h3>
								</div>
								<div class="customer-list" id="pageCategories" <?php echo $dataContentBehavior;?>>
									<?php echo RedshopbLayoutHelper::render(
										'shop.pages.categories',
										array(
											'categories'     => $extThis->category->subcategories,
											'showPagination' => true,
											'numberOfPages'  => $categoriesPagesCount,
											'currentPage'    => 1,
											'ajaxJS'         => 'redSHOPB.shop.updateCategoriesPage(event)',
											'collectionId'   => isset($collectionId) ? (int) $collectionId : null
										)
									);?>
								</div>
							</div>
						<?php endif;
						$showAsURL = 'index.php?option=com_redshopb&view=shop&layout=category&id=' . $extThis->category->id;

if (!empty($extThis->collectionProducts) && $extThis->productsCount > 0):
?>
{template.product-list}
<?php endif; ?>
					</div>
				</div>
			</div>

			<?php if (!empty($collectionId)):?>
				<input type="hidden" name="collection_id" value="<?php echo $collectionId;?>"/>
			<?php endif;?>
			<input type="hidden" name="page" value=""/>
			<input type="hidden" name="noPages" value="<?php echo $productsPagesCount;?>"/>
			<input type="hidden" name="show_as" value="<?php echo $showAs;?>" />
			<input type="hidden" name="task" value="" />
			<input type="hidden" name="return" value="<?php echo base64_encode($action);?>" />
			<?php echo HTMLHelper::_('form.token') ?>
		</form>
	<?php endif; ?>
</div>
