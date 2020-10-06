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
use Joomla\CMS\Factory;

HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('vnrbootstrap.tooltip');
HTMLHelper::_('rholder.holder');
HTMLHelper::_('rjquery.chosen', 'select');
HTMLHelper::_('rsortablelist.main');
RHelperAsset::load('shop.css', 'com_redshopb');

$app = Factory::getApplication();

$action             = RedshopbRoute::_('index.php?option=com_redshopb&view=shop&layout=productfeatured');
$showAs             = $app->getUserState('shop.show.productfeatured.ProductsAs', 'list');
$productsPagesCount = ceil($this->productsCount / $this->productsPerPage);

if (!empty($this->collections))
	:
	$productsPagesCount = ceil($this->maxPWCollections / $this->productsPerPage);
endif;

echo RedshopbBrowserBreadcrumbs::renderBreadcrumbs();
?>

<div class="redshopb-shop-productfeatured">
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
						$showAsURL     = 'index.php?option=com_redshopb&view=shop&layout=productfeatured';
						$cartPrefix    = 'productFeatured';

						echo RedshopbHelperTemplate::renderTemplate('product-list', 'shop', null,
							compact(array_keys(get_defined_vars()))
						); ?>
					</div>
				</div>
			</div>
		</div>

		<input type="hidden" name="show_as" value="<?php echo $showAs;?>" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="return" value="<?php echo base64_encode($action);?>" />
		<?php echo HTMLHelper::_('form.token') ?>
	</form>
</div>
