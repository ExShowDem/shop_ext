<?php
/**
 * @package     Aesir.E-Commerce.Tag
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;

// In case render by Layout class, not by redSHOPB Tag system
if (isset($displayData))
{
	extract($displayData);
}

$isAjax       = (isset($isAjax) ? $isAjax : false);
$collectionId = empty($collectionId) ? (int) $extThis->product->collectionId : (int) $collectionId;
$productId    = empty($productId) ? (int) $product->id : (int) $productId;

if (empty($productImages))
{
	if (!empty($extThis->product->productImages[$productId]))
	{
		$productImages = $extThis->product->productImages[$productId];
	}
	else
	{
		$productImages = array();
	}
}

if (!$isAjax)
{
	HTMLHelper::_('rjquery.flexslider', $sliderBox, $flexsliderOptions);
	$document = Factory::getDocument();
	$document->addStyleDeclaration('
	#productThumbs_' . $collectionId . '_' . $productId . ' .bigProductThumbs .thumbnail{
		width: ' . $width . 'px;
		height: ' . $height . 'px;
		box-sizing: border-box;
	}
	' . $sliderBox . ', ' . $sliderBox . ' .bigProductThumbs .flex-viewport {
		width: ' . $width . 'px;
		height: ' . $height . 'px;
	}
'
	);
?>
<div class="productThumbs" id="productThumbs_<?php echo $collectionId; ?>_<?php echo $productId; ?>" data-style="images">
<?php } ?>
	<div class="flexslider bigProductThumbs">
		<ul class="slides">
			<?php
			if (!empty($productImages)) :
				foreach ($productImages as $image) :
					$result    = RedshopbHelperThumbnail::originalIsBigger($image->name, $width, $height, 100, 0, 'products', $image->remote_path);
					$cloudInit = '';

					if ($result['result'])
					{
						$cloudInit = 'data-cloudzoom="zoomImage: \'' . $result['original'] . '\', zoomPosition: \'inside\'" class="cloudzoom"';
					}
					?>
					<li>
						<div class="thumbnail">
							<img src="<?php echo $result['thumb']; ?>" <?php echo $cloudInit; ?> alt="<?php echo RedshopbHelperThumbnail::safeAlt($image->alt, $product->name) ?>" />
						</div>
					</li>
					<?php
				endforeach;
			else :
				?>
				<li>
					<div class="thumbnail">
						<?php
						echo RedshopbHelperMedia::drawDefaultImg($width, $height, Text::_('COM_REDSHOPB_NO_IMAGE_LABEL'), '#999999', '#dfdfdf');
						?>
					</div>
				</li>
				<?php
			endif;
			?>
		</ul>
	</div>
	<?php if (!$isAjax): ?>
</div>
	<?php endif;
