<?php
/**
 * @package     Aesir.E-Commerce.Admin
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

$item    = $displayData['item'];
$field   = $displayData['field'];
$isEmail = $displayData['isEmail'];

$sku = ($item->product_item_id) ? $item->product_item_sku : $item->product_sku;

?>

<td class="field_<?php echo $field->fieldname ?>">
	<?php echo $sku;?>
</td>


