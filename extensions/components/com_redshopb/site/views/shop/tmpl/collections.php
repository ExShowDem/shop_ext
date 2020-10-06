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

$collectionPerPage     = RedshopbApp::getConfig()->get('shop_categories_per_page', 12);
$collectionsPagesCount = 1;
$collectionCount       = count($this->collections);

// @todo: make sure that $this->categoriesCount is stable and is always from the same type
if (!empty($collectionPerPage) && !is_array($collectionCount))
{
	$collectionsPagesCount = ceil($collectionCount / $collectionPerPage);
}

$dataContentBehavior = '';

if (RedshopbApp::isUseAjaxReadMorePagination())
{
	$dataContentBehavior = ' data-content-behavior="append"';
}

$action = RRoute::_('index.php?option=com_redshopb&view=shop&layout=collections');

RHelperAsset::load('shop.css', 'com_redshopb');
echo RedshopbBrowserBreadcrumbs::renderBreadcrumbs();
?>
<div class="redshopb-shop-collections">
	<form method="post" action="<?php echo $action;?>" id="adminForm" name="adminForm">

		<?php if (!empty($this->collections)) : ?>
			<div class="customer-container">
				<div class="customer-header">
					<i class="icon-book icon-2x"></i>
					<h3><?php echo Text::_('COM_REDSHOPB_COLLECTION_LIST'); ?></h3>
				</div>
				<div class="customer-list" id="pageCollections"<?php echo $dataContentBehavior ?>>
					<?php echo RedshopbLayoutHelper::render(
						'shop.pages.collections',
						array(
							'collections'    => $this->collections,
							'showPagination' => true,
							'numberOfPages'  => $collectionsPagesCount
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
