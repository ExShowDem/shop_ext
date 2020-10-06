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

$isAjax         = (isset($isAjax) ? $isAjax : false);
$document       = Factory::getDocument();
$miniWidth      = 115;
$miniHeight     = 115;
$thumbsExists   = false;
$initMiniThumbs = false;
$collectionId   = empty($collectionId) ? (int) (empty($extThis->product->collectionId) ? 0 : $extThis->product->collectionId) : (int) $collectionId;
$productId      = empty($productId) ? (int) $product->id : (int) $productId;

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

if (!empty($productImages))
{
	$thumbsExists = true;

	if (count($productImages) > 1)
	{
		$initMiniThumbs = true;
	}
}

if (!$isAjax)
{
	$document                        = Factory::getDocument();
	$flexMiniOptions                 = array(
		'animation'     => 'slide',
		'controlNav'    => false,
		'animationLoop' => false,
		'slideshow'     => false,
		'itemWidth'     => $miniWidth,
		'itemMargin'    => 5,
		'asNavFor'      => $sliderBox
	);
	$flexsliderOptions['controlNav'] = false;
	$flexsliderOptions['sync']       = $sliderMiniBox;

	if ($initMiniThumbs)
	{
		HTMLHelper::_('rjquery.flexslider', $sliderMiniBox, $flexMiniOptions);
	}

	$flexsliderMiniOptionsReg = RedshopbHelperShop::options2Jregistry($flexMiniOptions);
	$flexsliderOptionsReg     = RedshopbHelperShop::options2Jregistry($flexsliderOptions);
	HTMLHelper::_('rjquery.flexslider', $sliderBox, $flexsliderOptions);

	$document->addScriptDeclaration('
		var valuesSmallProductThumbs_' . $collectionId . '_' . $productId . ' = ' . $flexsliderMiniOptionsReg->toString() . ';
		var valuesProductThumbs_' . $collectionId . '_' . $productId . ' = ' . $flexsliderOptionsReg->toString() . ';
	'
	);
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
<div class="productThumbs" id="productThumbs_<?php echo $collectionId; ?>_<?php echo $productId; ?>" data-style="imagescarouselnavigation">
<?php } ?>
	<div class="flexslider bigProductThumbs">
		<ul class="slides">
			<?php
			if ($thumbsExists) :
				foreach ($productImages as $image) :
					$image     = is_array($image) ? $image[0] : $image;
					$result    = RedshopbHelperThumbnail::originalIsBigger($image->name, $width, $height, 100, 0, 'products', $image->remote_path);
					$cloudInit = '';

					if ($result['result'])
					{
						$cloudInit = 'data-cloudzoom="useParentProportions: true, zoomImage: \'' . $result['original'] . '\','
							. ' disableOnScreenWidth: \'768\', zoomWidth: \'' . $result['thumbInfo'][0] . '\','
							. ' zoomHeight: \'' . $result['thumbInfo'][1] . '\'" class="cloudzoom"';
					}
					?>
					<li>
						<div class="thumbnail">
							<img src="<?php echo $result['thumb'] ?>" <?php echo $cloudInit ?>
								 alt="<?php echo RedshopbHelperThumbnail::safeAlt($image->alt, $product->name) ?>"/>
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
<?php if ($initMiniThumbs) : ?>
	<div class="flexslider smallProductThumbs">
		<ul class="slides">
			<?php foreach ($productImages as $image) : ?>
				<li>
					<div class="thumbnail">
						<?php $path = RedshopbHelperThumbnail::originalToResize(
							$image->name, $miniWidth, $miniHeight, 100, 0, 'products', false, $image->remote_path
						);
						?>
						<img src="<?php echo $path ?>" alt="<?php echo RedshopbHelperThumbnail::safeAlt($image->alt, $product->name) ?>"/>
					</div>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
<?php endif; ?>

<?php if (!$isAjax): ?>
	</div>
<?php endif;
