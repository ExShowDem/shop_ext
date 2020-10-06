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

$noImage = false;

if (!isset($products->productImages[$productId])
	|| empty($products->productImages[$productId]))
{
	$noImage = true;
}

if (!$noImage)
{
	$src = RedshopbHelperThumbnail::originalToResize(
		$products->productImages[$productId][0]->name, $width, $height, 100,
		0, 'products', false, $products->productImages[$productId][0]->remote_path
	);

	if (is_bool($src))
	{
		$noImage = true;
	}
}

if ($noImage) :
	echo RedshopbHelperMedia::drawDefaultImg($width, $height, Text::_('COM_REDSHOPB_NO_IMAGE_LABEL'), '#999999', '#dfdfdf');

	return null;
endif;
?>
<a href="<?php echo $productLink ?>">
	<img src="<?php echo $src; ?>"
		 alt="<?php echo RedshopbHelperThumbnail::safeAlt($products->productImages[$productId][0]->alt, $productData->name) ?>"/>
</a>
