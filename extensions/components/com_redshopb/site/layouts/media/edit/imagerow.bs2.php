<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

$data = $displayData;
$item = $data["item"];

if (!isset($data["isLockedByWebservice"]))
{
	$data["isLockedByWebservice"] = false;
}

$buttonClass = 'published';
$iconClass = 'icon-plus-sign';

if ($item->state != 1)
{
	$buttonClass = 'unpublished';
	$iconClass = 'icon-minus-sign';
}
?>

<td>
	<button
		class="btn btn-mini product-image-edit"
		<?php echo ($data["isLockedByWebservice"] ? 'disabled' : '') ?>
		type="button"
		data-id="<?php echo $item->id;?>">
		<i class="icon-edit"></i>
	</button>
</td>
<td>
	<button
		class="btn btn-danger btn-mini product-image-remove"
		type="button"
		<?php echo ($data["isLockedByWebservice"] ? 'disabled' : '') ?>
		data-id="<?php echo $item->id; ?>">
		<i class="icon-remove-sign"></i>
	</button>
</td>
<td>
	<button
		class="btn btn-mini  product-image-toggle <?php echo $buttonClass; ?>"
		type="button"
		<?php echo ($data["isLockedByWebservice"] ? 'disabled' : '') ?>
		data-id="<?php echo $item->id; ?>"
		data-state="<?php echo $item->state == 1 ? 0 : 1;?>">
		<i class="<?php echo $iconClass;?>"></i>
	</button>
</td>
<td>
	<div class="thumbnail">
		<img src="<?php echo RedshopbHelperThumbnail::originalToResize($item->name, 144, 144, 100, 0, 'products', false, $item->remote_path); ?>" />
	</div>
</td>
<td><?php echo $item->viewName; ?></td>
<td><?php echo $item->main_attribute_name; ?></td>
<td><?php echo $item->alt; ?></td>

