<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Views
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

extract($displayData);

$favouriteList = $displayData['favourite_list'];
?>
	<table width="100%" class="content">
		<thead>
		<tr>
			<th>&nbsp;</th>
			<th><?php echo Text::_('COM_REDSHOPB_MYFAVORITELIST_PRODUCTS_LIST_PRODUCT'); ?></th>
			<th><?php echo Text::_('COM_REDSHOPB_MYFAVORITELIST_PRODUCTS_LIST_QUANTITY'); ?></th>
			<th><?php echo Text::_('COM_REDSHOPB_MYFAVORITELIST_PRODUCTS_LIST_PRICE'); ?></th>
			<th><?php echo Text::_('COM_REDSHOPB_MYFAVORITELIST_PRODUCTS_LIST_TOTAL'); ?></th>
		</tr>
		</thead>
		<tbody>
		<?php foreach ($favouriteList['products'] as $index => $product): ?>
			<tr>
				<td class="image">
					<?php if ($product['product_image']): ?>
						<img src="<?php echo $product['product_image'] ?>"
							alt="<?php echo RedshopbHelperThumbnail::safeAlt($product['product_name']) ?>" />
					<?php else: ?>
						<?php echo RedshopbHelperMedia::drawDefaultImg(72, 72, Text::_('COM_REDSHOPB_NO_IMAGE_LABEL')) ?>
					<?php endif; ?>
				</td>
				<td>
					<?php echo $product['sku']; ?>
					<br>
					<?php echo $product['name']; ?>
					<br>
					<?php echo is_null($product['attr_name']) ? '' : $product['attr_name']; ?>
				</td>
				<td>
					<?php echo $product['quantity']; ?>
				</td>
				<td>
					<?php echo $product['product_price']; ?>
				</td>
				<td>
					<?php echo $product['total_price']; ?>
				</td>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>

	<div class="row">
		<div class="products-total pull-right">
			<?php echo Text::_('COM_REDSHOPB_MYFAVORITELIST_PRODUCTS_LIST_TOTAL') . ' : ' . $favouriteList['grand_total_price']; ?>
		</div>
	</div>
