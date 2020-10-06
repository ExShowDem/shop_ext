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

$category = RedshopbEntityCategory::load($extThis->category->id);

if ($category->isLoaded() && (int) $category->get('parent_id') > 1):
	$parent = RedshopbEntityCategory::load((int) $category->get('parent_id'));

	if ($parent->isLoaded() && (int) $parent->get('parent_id') > 1):
		$ancestor = RedshopbEntityCategory::load((int) $parent->get('parent_id'));

		if (!empty($ancestor->get('image'))):
			$imagePath = RedshopbHelperThumbnail::getFullImagePath($ancestor->get('image'), 'categories');

			if ($imagePath):
				?>
				<img src="<?php echo Uri::root() . $imagePath ?>"
					 alt="<?php echo RedshopbHelperThumbnail::safeAlt($ancestor->get('name')) ?>"/>
				<?php
			endif;
		else:
			$width  = RedshopbEntityConfig::getInstance()->get('category_image_width', 150);
			$height = RedshopbEntityConfig::getInstance()->get('category_image_height', 150);
			echo RedshopbHelperMedia::drawDefaultImg($width, $height, Text::_('COM_REDSHOPB_NO_IMAGE_LABEL'), '#999999', '#dfdfdf');
		endif;
	endif;
endif;
