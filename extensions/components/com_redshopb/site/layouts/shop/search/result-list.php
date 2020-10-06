<?php
/**
 * @package     Aesir.E-Commerce
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('JPATH_REDCORE') or die;

use Joomla\CMS\Language\Text;

$data = $displayData;
$i    = 0;

// Todo: might be better set this parameter in configuration
$height = 70;
$width  = 70;

?>
<script type="text/javascript">
	jQuery(document).ready(function(){
		jQuery(".textSearchResult a.js-search_results:first-of-type").addClass("active");
	});
	jQuery(document).on('hover focus', '.textSearchResult a.js-search_results', function(){
		jQuery(".textSearchResult a.js-search_results:first-of-type").removeClass("active");
	});
</script>
<div class="row textSearchResult">
	<div class="col-md-12">
		<div class="row">
			<div class="col-md-12">
				<small class="muted"><?php echo Text::_('COM_REDSHOPB_SHOP_CONTROL_INSTRUCTIONS');?></small>
			</div>
		</div>
		<?php foreach ($data['result']->items AS $item): ?>
			<a href="javascript:void(0);"
			   class="js-search_results row"
			   data-product_id="<?php echo $item->id; ?>"
			   data-unit_measure="<?php echo $item->unit_measure_text ?>"
			   data-currency="<?php echo $data['result']->currency;?>"
			   data-collection_id="<?php echo $data['result']->collectionId;?>"
			   data-text="<?php echo $item->sku . ' - ' . $item->name;?>"
			   data-pkg_size="<?php echo $item->pkg_size ?>"
			   tabindex="0">
				<div class="col-md-3">
					<div class="well-small text-center text-vertical-center">
						<?php
						$productImage = RedshopbHelperProduct::getProductImage($item->id, 0, 0);
						$image        = '';

						if ($productImage)
						{
							$image = RedshopbHelperThumbnail::originalToResize(
								$productImage->name, $width, $height, 100, 0, 'products', false, $productImage->remote_path
							);
						}

						if ($image): ?>
							<img src="<?php echo $image ?>"  alt="<?php echo RedshopbHelperThumbnail::safeAlt($productImage->alt, $item->name) ?>" />
						<?php else: ?>
							<?php echo RedshopbHelperMedia::drawDefaultImg($width, $height, Text::_('COM_REDSHOPB_NO_IMAGE_LABEL')) ?>
						<?php endif; ?>
					</div>

				</div>
				<div class="col-md-6">
					<div class="text-vertical-center">
						<p data-type="sku">
							<span class="caption"><?php echo Text::_('COM_REDSHOPB_PRODUCT_SKU') . ': ';?></span>
							<span class="value"><?php echo $item->sku;?></span>
						</p>
						<p data-type="product_title">
							<?php echo $item->name;?>

							<?php if (strtolower($item->name) == strtolower($data['result']->searchTerm)):?>
								<span class="label label-warning"><?php echo Text::_('COM_REDSHOPB_SHOP_EXACT_MATCH');?></span>
							<?php endif;?>
						</p>
					</div>
				</div>
				<div class="col-md-2">
					<div class="text-vertical-center">
						<?php
						if (isset($data['result']->prices[$item->id]->price_without_discount))
						{
							echo RedshopbHelperProduct::getProductFormattedPrice(
								$data['result']->prices[$item->id]->price_without_discount,
								$data['result']->currency
							);
						}?>
					</div>
				</div>
			</a>
			<?php $i++;?>
		<?php endforeach;?>
	</div>
</div>
