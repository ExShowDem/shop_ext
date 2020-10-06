<?php
/**
 * @package     Aesir.E-Commerce.Admin
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

$item     = $displayData['item'];
$field    = $displayData['field'];
$currency = $displayData['currency'];

if ((float) $field->value > 0.0)
{
	if (isset($item->discount_type) && $item->discount_type == RedshopbHelperPrices::DISCOUNT_TOTAL)
	{
		$field->value = RedshopbHelperProduct::getProductFormattedPrice($field->value, $currency, false);
	}
	else
	{
		$field->value = $field->value . '%';
	}
}
?>

<td class="field_<?php echo $field->fieldname ?>">
	<?php echo $field->value;?>
</td>


