<?php
/**
 * @package     Aesir.E-Commerce.Admin
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

$item         = $displayData['item'];
$isEmail      = $displayData['isEmail'];
$canEdit      = $displayData['canEdit'];
$isOffer      = $displayData['isOffer'];
$customerId   = $displayData['customerId'];
$customerType = $displayData['customerType'];
$index        = $displayData['index'];

if ($isEmail)
{
	return '<td></td>';
}

$inputDisabled = (!$canEdit || $isOffer) ? ' disabled="disabled"' : '';
$inputId       = 'order-checkbox-item_' . $item->product_id;
$inputId      .= '_' . ($item->type_id) ? $item->type_id : $item->id;
$inputId      .= '_' . $customerType . '-' . $customerId;
$inputId      .= '_' . $index;
?>

<td>
	<input type="checkbox" class="orderItemCheckBox" value="1" id="<?php echo $inputId;?>"<?php echo $inputDisabled;?>/>
</td>

