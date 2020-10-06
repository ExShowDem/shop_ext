<?php
/**
 * @package     Aesir.E-Commerce.Admin
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

$item             = $displayData['item'];
$field            = $displayData['field'];
$isEmail          = $displayData['isEmail'];
$quantityDisabled = $displayData['quantityDisabled'];

if ($quantityDisabled)
{
	$field->disabled = true;
}

$product = RedshopbEntityProduct::load($item->product_id)->getItem();

// Checking package quantities
if ($product->max_sale > 0)
{
	$field->max = $product->max_sale;
}

$field->min  = $product->min_sale;
$field->step = $product->pkg_size;

$field->class .= ' logQItem-' . $item->product_item_id;
$displayHtml   = $field->value;

if (!$isEmail)
{
	$displayHtml = $field->input;

	$unitMeasureName = 'stk';

	if (!empty($item->unit_measure_id))
	{
		$unitMeasure = RedshopbEntityUnit_Measure::getInstance($item->unit_measure_id);
		$unitMeasure->getItem();

		$unitMeasureName = $unitMeasure->get('name');
	}

	$displayHtml = '<div class="input-append">' . $displayHtml . '<span class="add-on">' . $unitMeasureName . '</span></div>';
}

?>

<td class="field_<?php echo $field->fieldname ?>">
	<?php echo $displayHtml;?>
</td>
