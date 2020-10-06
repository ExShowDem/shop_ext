<?php
/**
 * @package     Aesir.E-Commerce.Tag
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

$product = $displayData['product'];
$extThis = $displayData['extThis'];

$productImages = isset($extThis->product->productImages[$product->id]) ? $extThis->product->productImages[$product->id] : null;
$manufacturer  = RedshopbEntityManufacturer::getInstance($product->manufacturer_id);
$price         = !empty($extThis->product->prices[$product->id]->price) ? $extThis->product->prices[$product->id]->price : '';
$currency      = !empty($extThis->product->prices[$product->id]->currency) ? $extThis->product->prices[$product->id]->currency : '';

$collectionId = $extThis->collectionId;
$productLink  = RedshopbRoute::_(
	'index.php?option=com_redshopb&view=shop&layout=product&id=' . $product->id
	. '&category_id=' . $product->category_id . '&collection=' . $collectionId
);
?>
<?php if (RedshopbApp::useRichSnippets()): ?>
	<div class="well" itemscope itemtype="http://schema.org/Product">
		<div itemprop="name"><?php echo $product->name ?></div>
		<div itemprop="sku"><?php echo $product->sku ?></div>
		<a href="<?php echo Uri::root(true) . $productLink ?>" itemprop="url"></a>
		<?php
		$src = RedshopbHelperThumbnail::originalToResize(
			$productImages[0]->name,
			$width,
			$height,
			100,
			0,
			'products',
			false,
			$productImages[0]->remote_path
		);

		if (!is_null($productImages) && !empty($productImages)
			&& ($src)
		) :
		?>
			<img src="<?php echo $src ?>" itemprop="image"/>
		<?php endif; ?>

		<?php if (!empty($product->description)): ?>
			<div itemprop="description"><?php echo RedshopbHelperProduct::getProductDescription($product->description->description) ?></div>
		<?php endif; ?>

		<?php if ($manufacturer->isLoaded()): ?>
			<div itemprop="brand"><?php echo $manufacturer->get('name') ?></div>
		<?php endif; ?>
		<div itemprop="offers" itemscope itemtype="http://schema.org/Offer">
			<?php if (!empty($price)): ?>
				<div itemprop="price"><?php echo (float) $price ?></div>
			<?php endif; ?>

			<?php if (!empty($currency)): ?>
				<div itemprop="priceCurrency"><?php echo $currency ?></div>
			<?php endif; ?>
			<div itemprop="availability">
				<?php if (RedshopbHelperStockroom::productHasInStock($product->id)): ?>
					<?php echo Text::_('COM_REDSHOPB_PRODUCT_ON_STOCK') ?>
				<?php else: ?>
					<?php echo Text::_('COM_REDSHOPB_PRODUCT_NO_STOCK') ?>
				<?php endif; ?>
			</div>
		</div>
	</div>
<?php endif;
