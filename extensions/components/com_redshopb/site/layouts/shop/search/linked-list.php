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

// Todo: might be better set this parameter in configuration
$height = 70;
$width  = 70;

?>
<div class="row textSearchResult">
	<div class="col-md-12">
		<div class="row">
			<div class="col-md-12">
				<small class="muted"><?php echo Text::_('COM_REDSHOPB_SHOP_CONTROL_INSTRUCTIONS');?></small>
			</div>
		</div>
		<?php
		if (!empty($data['result']->categories))
		{
			foreach ($data['result']->categories as $category)
			{
				$image = RedshopbHelperThumbnail::originalToResize($category->image, $width, $height, 100, 1, 'categories');

				if ($image)
				{
					$image = '<img src="' . $image . '" alt="' . RedshopbHelperThumbnail::safeAlt($category->name) . '"/>';
				}
				else
				{
					$image = RedshopbHelperMedia::drawDefaultImg($width, $height, Text::_('COM_REDSHOPB_NO_IMAGE_LABEL'));
				}

				?><a href="<?php echo RedshopbRoute::_('index.php?option=com_redshopb&view=shop&layout=category&id=' . $category->id) ?>" class="js-search_results row">
					<div class="col-md-3">
						<div class="well-small text-center text-vertical-center">
						<?php echo $image ?>
						</div>
					</div>
					<div class="col-md-9">
						<div class="text-vertical-center">
							<p>
								<?php echo $category->name;?>

								<?php if (strtolower($category->name) == strtolower($data['result']->searchTerm)):?>
									<span class="label label-warning"><?php echo Text::_('COM_REDSHOPB_SHOP_EXACT_MATCH');?></span>
								<?php endif;?>
							</p>
						</div>
					</div>
				</a>
		<?php
			}
		}
		?>
		<?php foreach ($data['result']->items AS $item): ?>
			<a href="<?php echo RedshopbRoute::_('index.php?option=com_redshopb&view=shop&layout=product&id=' . $item->id . '&category_id=' . $item->category_id);?>"
			   class="js-search_results row"
			   tabindex="0">
				<div class="col-md-3">
					<div class="well-small text-center text-vertical-center">
						<?php
						$productImage = RedshopbHelperProduct::getProductImage($item->id, 0, 0);
						$image        = '';

						if ($productImage)
						{
							$image = RedshopbHelperThumbnail::originalToResize($productImage->name, $width, $height, 100, 0, 'products', false, $productImage->remote_path);
						}

						if ($image): ?>
							<img src="<?php echo $image ?>" alt="<?php echo RedshopbHelperThumbnail::safeAlt($productImage->alt, $item->name) ?>" />
						<?php else: ?>
							<?php echo RedshopbHelperMedia::drawDefaultImg($width, $height, Text::_('COM_REDSHOPB_NO_IMAGE_LABEL')) ?>
						<?php endif; ?>
					</div>
				</div>
				<div class="col-md-9">
					<div class="text-vertical-center">
						<p>
							<span class="caption"><?php echo Text::_('COM_REDSHOPB_PRODUCT_SKU') . ': ';?></span>
							<span class="value"><?php echo $item->sku;?></span>
						</p>
						<p>
							<?php echo $item->name;?>

							<?php if (strtolower($item->name) == strtolower($data['result']->searchTerm)):?>
								<span class="label label-warning"><?php echo Text::_('COM_REDSHOPB_SHOP_EXACT_MATCH');?></span>
							<?php endif;?>
						</p>
					</div>
				</div>
			</a>
		<?php endforeach;?>
	</div>
</div>
