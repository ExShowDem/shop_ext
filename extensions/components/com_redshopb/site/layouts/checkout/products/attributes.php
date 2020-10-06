<?php
/**
 * @package     Aesir.E-Commerce.Admin
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

$item  = $displayData['item'];
$field = $displayData['field'];

$attributes = $item->attributes;
?>

<td>
	<?php if (!empty($attributes)): ?>
		<table class="table-bordered table redshopb-attributes">
			<?php foreach ($attributes as $attrName => $attrValue) : ?>
				<tr>
					<td><strong><?php echo $attrName; ?></strong></td>
					<td><?php echo $attrValue->value; ?></td>
				</tr>
			<?php endforeach; ?>
		</table>
	<?php endif; ?>
</td>


