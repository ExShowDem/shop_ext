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
?>
<?php if (RedshopbApp::useRichSnippets()): ?>
	<div class="hidden" itemscope itemtype="http://schema.org/Product">
		<div itemprop="name"><?php echo $productData->name ?></div>
		<div itemprop="sku"><?php echo $productData->sku ?></div>
		<a href="<?php echo Uri::root(true) . $productLink ?>" itemprop="url"></a>
		<?php
		$src = RedshopbHelperThumbnail::originalToResize(
			$products->productImages[$productId][0]->name,
			$width,
			$height,
			100,
			0,
			'products',
			false,
			$products->productImages[$productId][0]->remote_path
		);

		if (isset($products->productImages[$productId])
			&& !empty($products->productImages[$productId])
			&& ($src)
		) :
		?>
			<img src="<?php echo $src ?>" itemprop="image"/>
		<?php endif; ?>

		<?php if (!empty($productDescription)): ?>
			<div itemprop="description"><?php echo $productDescription ?></div>
		<?php endif; ?>

		<?php if (!empty($manufactureName)) : ?>
			<div itemprop="brand"><?php echo $manufactureName ?></div>
		<?php endif; ?>

		<div itemprop="offers" itemscope itemtype="http://schema.org/Offer">
			<?php if (!empty($currency)): ?>
				<div itemprop="priceCurrency"><?php echo $currency ?></div>
			<?php endif; ?>

			<?php if (!empty($price)): ?>
				<div itemprop="price"><?php echo $price ?></div>
			<?php endif; ?>

			<div itemprop="availability">
				<?php if (RedshopbHelperStockroom::productHasInStock($productData->id)): ?>
					<?php echo Text::_('COM_REDSHOPB_PRODUCT_ON_STOCK') ?>
				<?php else: ?>
					<?php echo Text::_('COM_REDSHOPB_PRODUCT_NO_STOCK') ?>
				<?php endif; ?>
			</div>
		</div>
	</div>
<?php endif;
