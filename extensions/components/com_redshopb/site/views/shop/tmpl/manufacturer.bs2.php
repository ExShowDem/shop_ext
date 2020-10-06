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

$app              = Factory::getApplication();
$action           = RedshopbRoute::_('index.php?option=com_redshopb&view=shop&layout=manufacturer&id=' . $this->manufacturer->id);
$showAs           = $app->getUserState('shop.show.manufacturer.ProductsAs', 'list');
$sortByField      = $this->filterForm->getField('sort_by');
$productShowField = $this->filterForm->getField('product_category_limit');
$productsPerPage  = $app->getUserState('shop.productLimit', RedshopbApp::getConfig()->get('shop_categories_per_page', 12));
$id               = 0;

$productsPagesCount = ceil($this->productsCount / $productsPerPage);

if (!empty($this->collections))
	:
	$productsPagesCount = ceil($this->maxPWCollections / $productsPerPage);
endif;

if (!empty($productsPerPage))
	:
	$productShowField->setValue($productsPerPage);
endif;

$menuParams        = $app->getMenu()->getActive()->params;
$showCategoryParam = $menuParams->get('show_inline_category_filter', RedshopbApp::getConfig()->get('show_inline_category_filter', 1));

$showCategoryFilter = ($showCategoryParam == 1);
$categoryField      = null;

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

echo RedshopbBrowserBreadcrumbs::renderBreadcrumbs();

?>
<div class="redshopb-shop-productlist">
	<?php if (!is_null($this->manufacturer) && $this->manufacturer->isLoaded()):
		$id = $this->manufacturer->get('id');
		?>
		<div class="redshopb-shop-productlist-manufacturer">
			<h1><?php echo $this->manufacturer->get('name'); ?></h1>
			<div class="row-fluid">
				<div class="span8 lead">
					<?php echo $this->manufacturer->get('description'); ?>
				</div>
				<div class="span4">
					<?php
					$manufacturerImage  = $this->manufacturer->get('image', '');
					$manufacturerWidth  = RedshopbEntityConfig::getInstance()->get('manufacturer_image_width', 256);
					$manufacturerHeight = RedshopbEntityConfig::getInstance()->get('manufacturer_image_height', 256);
					?>
					<div class="redshopb-manufacturer-image">
						<?php if (!empty($manufacturerImage)): ?>
							<img alt="<?php echo RedshopbHelperThumbnail::safeAlt($this->manufacturer->get('name')) ?>"
								 src="<?php echo RedshopbHelperThumbnail::originalToResize($manufacturerImage, $manufacturerWidth, $manufacturerHeight, 100, 0, 'manufacturers') ?>"/>
						<?php else: ?>
							<?php
							echo RedshopbHelperMedia::drawDefaultImg($manufacturerWidth, $manufacturerHeight, Text::_('COM_REDSHOPB_NO_IMAGE_LABEL'));
							?>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</div>
	<?php endif; ?>
	<form action="<?php echo $action; ?>" method="post" id="adminForm" name="adminForm">
		<div class="row-fluid">
			<div class="span12">
				<div class="container-fluid">
					<div class="row-fluid">
						<?php
						$extThis           = $this;
						$cartPrefix        = 'inManuf' . $this->manufacturer->id;
						$numberOfPages     = $productsPagesCount;
						$showAsURL         = 'index.php?option=com_redshopb&view=shop&layout=manufacturer&id=' . $id;
						$showProductFilter = false;

						echo RedshopbHelperTemplate::renderTemplate('product-list', 'shop', null,
							compact(array_keys(get_defined_vars()))
						); ?>
					</div>
				</div>
			</div>
		</div>

		<input type="hidden" name="page" value=""/>
		<input type="hidden" name="noPages" value="<?php echo $productsPagesCount;?>"/>
		<input type="hidden" name="show_as" value="<?php echo $showAs; ?>"/>
		<input type="hidden" name="task" value=""/>
		<?php echo HTMLHelper::_('form.token') ?>
	</form>
</div>
