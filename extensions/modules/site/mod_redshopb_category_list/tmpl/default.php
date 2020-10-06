<?php
/**
 * @package     Aesir.E-Commerce.Site
 * @subpackage  mod_redshopb_category_list
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
?>

<div class="mod_redshopb_category_list<?php echo $moduleClassSuffix ?>">
	<?php if (!empty($categories)) : ?>
		<?php foreach ($categories as $category) : ?>
			<div class="mod_redshopb_category_list_category">
				<div class="category-name-wrapper">
					<a href="<?php echo $category->url ?>">
						<h3><?php echo $category->name ?></h3>
					</a>
				</div>
				<div class="category-image-wrapper">
					<?php if (!empty($category->image)) : ?>
						<?php $image = RedshopbHelperThumbnail::getFullImagePath($category->image, 'categories'); ?>

						<?php if (!empty($image)) : ?>
							<img src="<?php echo $image ?>" class="category-image" alt="<?php echo RedshopbHelperThumbnail::safeAlt($category->name) ?>" />
						<?php endif; ?>
					<?php else: ?>
						<?php
						$width  = RedshopbEntityConfig::getInstance()->get('category_image_width', 150);
						$height = RedshopbEntityConfig::getInstance()->get('category_image_height', 150);
						echo RedshopbHelperMedia::drawDefaultImg($width, $height, Text::_('COM_REDSHOPB_NO_IMAGE_LABEL'), '#999999', '#dfdfdf');
						?>
					<?php endif; ?>
				</div>
				<div class="category-description-wrapper">
					<?php echo $category->description ?>
				</div>
			</div>
		<?php endforeach; ?>
	<?php endif ?>
</div>
