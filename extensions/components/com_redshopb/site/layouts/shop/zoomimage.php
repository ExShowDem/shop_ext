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

$imageWidth     = $data['imageWidth'];
$imageHeight    = $data['imageHeight'];
$imageBigWidth  = $data['imageBigWidth'];
$imageBigHeight = $data['imageBigHeight'];
$quality        = $data['quality'];
$productId      = $data['productId'];
$flatAttrId     = $data['flatAttrId'];
$mediaId        = $data['mediaId'];
?>
<div class="container-fluid">
	<a data-dismiss="modal" class="close" href="#"><?php echo Text::_('JTOOLBAR_CLOSE');?>&nbsp;×</a>
	<div class="row">
		<div>
			<span class="pagination-centered">
				<?php
				$productImage = RedshopbHelperProduct::getProductImage($productId, 0, $flatAttrId, $mediaId);

				if ($productImage):
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
			</span>
		</div>
	</div>

	<script type="text/javascript">
		jQuery("[data-toggle=tooltip]").tooltip({animation: false})
			.on('show', function(e) {e.stopPropagation();}).on('hidden', function(e) {e.stopPropagation();})
		Holder.run();
		CloudZoom.quickStart();
	</script>
