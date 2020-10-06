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
use Joomla\CMS\Form\Form;

HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('vnrbootstrap.tooltip');
HTMLHelper::_('rholder.holder');
HTMLHelper::_('rjquery.chosen', 'select');
HTMLHelper::_('rsortablelist.main');
RHelperAsset::load('shop.css', 'com_redshopb');

$app = Factory::getApplication();

$action         = RedshopbRoute::_('index.php?option=com_redshopb&view=shop&layout=productlist');
$xml            = $this->filterForm->getXml();
$redshopbConfig = RedshopbApp::getConfig();
$sortByField    = $this->filterForm->getField('sort_by');
$this->filterForm->setFieldAttribute('sort_by', 'sortdirstate', 'shop.show.productlist.SortByDir');
$isSearch = $app->input->getString('search');

$comParams         = RedshopbApp::getConfig();
$menuParams        = $app->getMenu()->getActive()->params;
$showCategoryParam = $menuParams->get('show_inline_category_filter', $comParams->get('show_inline_category_filter', 1));

$showCategoryFilter = ($showCategoryParam == 1);

if ($showCategoryFilter)
{
	/** @var Form $filterForm */
	$filterForm = $this->filterForm;
	$filterForm->setFieldAttribute('product_category', 'onchange', 'this.form.submit();', 'filter');
	$filterForm->setFieldAttribute('product_category', 'multiple', 'false', 'filter');
	$filterForm->setFieldAttribute('product_category', 'filterproducts', 'false', 'filter');

	$categoryField       = $this->filterForm->getField('product_category', 'filter');
	$categoryFilterValue = $app->getUserState('filter.product_category');

	if (!empty($categoryFilterValue))
	{
		$categoryField->setValue($categoryFilterValue);
	}
}

$showAs             = $app->getUserState('shop.show.productlist.ProductsAs', 'list');
$productsPagesCount = ceil($this->productsCount / $this->productsPerPage);

if (!empty($this->collections))
	:
	$productsPagesCount = ceil($this->maxPWCollections / $this->productsPerPage);
endif;

echo RedshopbBrowserBreadcrumbs::renderBreadcrumbs();
?>

<div class="redshopb-shop-productlist">
	<?php if (!is_null($this->productLManufacturer) && $this->productLManufacturer->isLoaded()): ?>
		<div class="redshopb-shop-productlist-manufacturer">
			<h1><?php echo $this->productLManufacturer->get('name'); ?></h1>
			<div class="row-fluid">
				<div class="span8 lead">
					<?php echo $this->productLManufacturer->get('description'); ?>
				</div>
				<div class="span4">
					<?php
					$manufacturerImage  = $this->productLManufacturer->get('image', '');
					$manufacturerWidth  = $redshopbConfig->get('manufacturer_image_width', 256);
					$manufacturerHeight = $redshopbConfig->get('manufacturer_image_height', 256);
					?>
					<div class="redshopb-manufacturer-image">
						<?php if (!empty($manufacturerImage)): ?>
							<img src="<?php echo RedshopbHelperThumbnail::originalToResize($manufacturerImage, $manufacturerWidth, $manufacturerHeight, 100, 0, 'manufacturers') ?>"
								 alt="<?php echo RedshopbHelperThumbnail::safeAlt($this->productLManufacturer->get('name')) ?>"/>
						<?php else: ?>
							<?php
							echo RedshopbHelperMedia::drawDefaultImg($manufacturerWidth, $manufacturerHeight, Text::_('COM_REDSHOPB_NO_IMAGE_LABEL'));
							?>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</div>
	<?php elseif ($isSearch != '') : ?>
		<div class="row-fluid">
			<div class="span12">
				<h1><?php echo Text::_('JSEARCH'); ?></h1>
			</div>
		</div>
	<?php endif; ?>

	<?php if ($this->tagRecord) : ?>
		<div class="row-fluid">
			<div class="span8 tag-name">
				<h1><?php echo $this->tagRecord->name ?></h1>
			</div>
			<div class="span4 tag-thumbnail">
				<?php $thumb = RedshopbHelperTag::getTagImageThumbHtml($this->tagRecord->id);

				if ($thumb != '') : ?>
					<?php echo $thumb;?>
				<?php endif;?>
			</div>
		</div>
	<?php endif; ?>

	<form action="<?php echo $action;?>" method="post" id="adminForm" name="adminForm">
		<div class="row-fluid">
			<div class="span12">
				<div class="container-fluid">
					<div class="row-fluid">
						<?php
						$extThis       = $this;
						$numberOfPages = $productsPagesCount;
						$showAsURL     = 'index.php?option=com_redshopb&view=shop&layout=productlist';
						$cartPrefix    = 'productList';

						echo RedshopbHelperTemplate::renderTemplate('product-list', 'shop', null,
							compact(array_keys(get_defined_vars()))
						); ?>
					</div>
				</div>
			</div>
		</div>

		<input type="hidden" name="page" value=""/>
		<input type="hidden" name="noPages" value="<?php echo $productsPagesCount;?>"/>
		<input type="hidden" name="show_as" value="<?php echo $showAs;?>" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="return" value="<?php echo base64_encode($action);?>" />
		<?php echo HTMLHelper::_('form.token') ?>
	</form>
</div>
