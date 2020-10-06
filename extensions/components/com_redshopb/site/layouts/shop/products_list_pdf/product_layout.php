<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Layouts.Shop.Products_List_PDF
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

$item   = $displayData['item'];
$part   = isset($displayData['part']) ? (int) $displayData['part'] : 0;
$width  = $displayData['width'];
$height = $displayData['height'];
?>

<table style="width: 100%; border: 1px solid #000; border-spacing: 0px">
	<?php if ($part == 0):?>
		<tr style="border: none; border-spacing: 0px;">
			<td colspan="2" style="border: 1px solid #808080; border-spacing: 0px;">
				<h3>
					<?php echo Text::_('COM_REDSHOPB_SHOP_MODEL'); ?> : <?php echo $item->sku; ?> : <?php echo $item->name; ?> <?php echo $item->colorName; ?>
				</h3>
			</td>
		</tr>
		<tr style="border: none; border-spacing: 0px;">
			<td style="border: 1px solid #808080; width: 10%; min-height: 10%; border-spacing: 0px;">
				<?php if (isset($item->productImage)): ?>
				<div class="thumbnail">
					<img src="<?php echo RedshopbHelperThumbnail::originalToResize($item->productImage->name, $width, $height, 100, 0, 'products', false, $item->productImage->remote_path); ?>" />
				</div>
				<?php else: ?>
				<div class="thumbnail">
					<?php echo HTMLHelper::image('media/com_redshopb/images/blank.png', '', array('width' => $width, 'height' => $height)); ?>
				</div>
				<?php endif; ?>
			</td>
			<td style="width: 90%; vertical-align: bottom; border: none; border-spacing: 0px;">
				<?php echo RedshopbLayoutHelper::render(
					'shop.products_list_pdf.table_combinations',
					array(
						'item' => $item,
						'part' => $part
					)
				);?>
			</td>
		</tr>
	<?php else: ?>
		<tr style="border: none; border-spacing: 0px;">
			<td style="width: 10%; min-height: 10%; border: none; border-spacing: 0px;">
				<div class="thumbnail">
					<?php echo HTMLHelper::image('media/com_redshopb/images/blank.png', '', array('width' => $width, 'height' => $height)); ?>
				</div>
			</td>
			<td style="vertical-align: top; width: 90%; border: none; border-spacing: 0px;">
			<?php echo RedshopbLayoutHelper::render(
				'shop.products_list_pdf.table_combinations',
				array(
					'item' => $item,
					'part' => $part
				)
			);?>
			</td>
		</tr>
	<?php endif; ?>
</table>
