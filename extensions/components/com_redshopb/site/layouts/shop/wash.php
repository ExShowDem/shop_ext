<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Views
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

$data = $displayData;

$washProductItem = $data['washProductItem'];
$items = $data['items'];
$logos = $data['logos'];
$logosHeight = $data['logosHeight'];
$logosWidth = $data['logosWidth'];
$washAndCareWidth = $data['wAcInfoWidth'];
$washAndCareHeight = $data['wAcInfoHeight'];
$imageWidth = $data['imageWidth'];
$imageHeight = $data['imageHeight'];
$imageBigWidth = $data['imageBigWidth'];
$imageBigHeight = $data['imageBigHeight'];
$quality = $data['quality'];
$productDropdown = $data['productDropdown'];
$productId = $data['productId'];
$flatAttrId = $data['flatAttrId'];
$compositions = $data['compositions'];
$productDescription = RedshopbHelperProduct::getProductDescription($data['description']);

$description = $productDescription .
	(isset($washProductItem->description) ? RedshopbHelperProduct::getProductDescription($washProductItem->description) : '');

?>
<div class="container-fluid">
	<a data-dismiss="modal" class="close" href="#"><?php echo Text::_('JTOOLBAR_CLOSE');?>&nbsp;×</a>
	<div class="row">
		<div class="col-md-5">
			<span class="thumbnail pagination-centered">
				<?php
				$productImage = RedshopbHelperProduct::getProductImage($productId, 0, $flatAttrId);

				if ($productImage) :
					$bigThumb = RedshopbHelperThumbnail::originalToResize($productImage->name, $imageBigWidth, $imageBigHeight, $quality, 0, 'products', false, $productImage->remote_path);

					$imageAttributes = array(
						'class' => 'cloudzoom',
						'data-cloudzoom' => 'zoomImage:\'' . $bigThumb . '\', zoomPosition:\'inside\''
					);

					$smallThumb = RedshopbHelperThumbnail::originalToResize($productImage->name, $imageWidth, $imageHeight, $quality, 0, 'products', false, $productImage->remote_path);

					$thumb = HTMLHelper::_('image', $smallThumb, $productImage->name, $imageAttributes);
				?>
				<?php echo $thumb; ?>
				<?php else: ?>
					<?php echo RedshopbHelperMedia::drawDefaultImg($imageWidth, $imageHeight, Text::_('COM_REDSHOPB_NO_IMAGE_LABEL'), '#999999', '#dfdfdf') ?>
				<?php endif; ?>
				<h4><?php echo $productDropdown; ?></h4>
			</span>
		</div>
		<div class="col-md-7">
			<div class="row">
				<h3><?php echo Text::_('COM_REDSHOPB_SHOP_MODEL'); ?> : <?php echo $washProductItem->sku; ?></h3>
				<p>
					<?php echo $description; ?>
				</p>
				<?php if ($compositions) : ?>
				<h5><?php echo Text::_('COM_REDSHOPB_SHOP_QUALITY'); ?></h5>
					<table class="table table-condensed">
					<?php foreach ($compositions as $composition): ?>
					<tr>
						<td><?php echo $composition->type; ?></td>
						<td><?php echo $composition->quality; ?></td>
					</tr>
					<?php endforeach; ?>
					</table>
				<?php endif; ?>

				<?php if (!empty($items)) : ?>
					<h5><?php echo Text::_('COM_REDSHOPB_SHOP_WASH_CARE'); ?></h5>
					<div class="row">
					<?php
					foreach ($items as $key => $item)
						:
						if ($item->type_code == 'Wash/Care' && $item->image != '')
							:
							?>
							<div class="oneWashImage">
								<a href="#" data-toggle="tooltip" title="<?php echo $item->description ?>" data-placement="top">
									<?php echo '<img src="' . RedshopbHelperThumbnail::originalToResize(
										$item->image,
										$washAndCareWidth,
										$washAndCareHeight,
										$quality,
										0,
										'wash_care_spec'
									) . '" alt="' . RedshopbHelperThumbnail::safeAlt($item->description) . '" /></br>'; ?>
								</a>
							</div>
							<?php
							unset($items[$key]);
						endif;
					endforeach;
					?>
					</div>
				<?php endif; ?>

				<?php if (!empty($items)) : ?>
					<div class="row">
						<?php foreach ($items as $key => $item) :
							if ($item->image != '') : ?>
								<div class="oneWashImage">
									<a href="#" rel="tooltip" class="hasTooltip " data-toggle="tooltip" title="<?php echo $item->description ?>" data-placement="top">
										<?php echo '<img src="' . RedshopbHelperThumbnail::originalToResize(
											$item->image,
											$logosWidth,
											$logosHeight,
											$quality,
											0,
											'wash_care_spec'
										) . '" alt="' . RedshopbHelperThumbnail::safeAlt($item->description) . '" ></br>'; ?>
									</a>
								</div>
							<?php
								unset($items[$key]);
							endif; ?>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>

				<?php if (!empty($items)) : ?>
					<div class="row">
						<div class="col-md-12">
							<ul class="unstyled list-unstyled">
								<?php foreach ($items as $item) : ?>
									<li><?php echo $item->description ?></li>
								<?php endforeach; ?>
							</ul>
						</div>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</div>

	<script type="text/javascript">
		jQuery("[data-toggle=tooltip]").tooltip({animation: false})
			.on('show', function(e) {e.stopPropagation();}).on('hidden', function(e) {e.stopPropagation();})
		Holder.run();
		CloudZoom.quickStart();
	</script>
