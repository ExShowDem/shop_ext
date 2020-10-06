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

$level = 0;

HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('rdropdown.init');
HTMLHelper::_('vnrbootstrap.tooltip');
HTMLHelper::_('vnrbootstrap.checkbox');
HTMLHelper::_('rjquery.chosen', '.chosenSelect, .productListContainer select');
HTMLHelper::_('rholder.holder');

$categoriesPerPage    = RedshopbApp::getConfig()->get('shop_categories_per_page', 12);
$categoriesPagesCount = 1;

// @toDO: make sure that $this->categoriesCount is stable and is always from the same type
if (!empty($categoriesPerPage) && !is_array($this->categoriesCount))
{
	$categoriesPagesCount = ceil($this->categoriesCount / $categoriesPerPage);
}

$dataContentBehavior = '';

if (RedshopbApp::isUseAjaxReadMorePagination())
{
	$dataContentBehavior = ' data-content-behavior="append"';
}

$action = RRoute::_('index.php?option=com_redshopb&view=shop&layout=categories');

RHelperAsset::load('shop.css', 'com_redshopb');
echo RedshopbBrowserBreadcrumbs::renderBreadcrumbs();
?>
<div class="redshopb-shop-categories">
	<form method="post" action="<?php echo $action;?>" id="adminForm" name="adminForm">

		<?php if (!empty($this->categories)) : ?>
			<div class="customer-container">
				<div class="customer-header">
					<i class="icon-user icon-2x"></i>
					<h3><?php echo Text::_('COM_REDSHOPB_CATEGORY_LIST_TITLE'); ?></h3>
				</div>
				<div class="customer-list" id="pageCategories"<?php echo $dataContentBehavior;?>>
					<?php echo RedshopbLayoutHelper::render(
						'shop.pages.categories',
						array(
							'categories'     => $this->categories,
							'showPagination' => true,
							'numberOfPages'  => $categoriesPagesCount,
							'currentPage'    => 1,
							'ajaxJS'         => 'redSHOPB.shop.updateCategoriesPage(event);'
						)
					);?>
				</div>
			</div>
		<?php else : ?>
			<?php echo RedshopbLayoutHelper::render('common.nodata'); ?>
		<?php endif; ?>
		<input type="hidden" name="option" value="com_redshopb">
		<input type="hidden" name="task" value="" />
		<?php echo HTMLHelper::_('form.token') ?>
	</form>
</div>
