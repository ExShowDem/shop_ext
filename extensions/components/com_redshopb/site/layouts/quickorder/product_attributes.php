<?php
/**
 * @package     Aesir.E-Commerce.Template
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

$product    = $displayData['product'];
$attributes = $displayData['attributes'];
?>

<script type="text/javascript">
	jQuery(document).ready(function()
	{
		jQuery('.quickorder-product-attribute').on('change', function(event) {
			redSHOPB.quickorder.populateProductItem();
		});
	});
</script>


<?php
foreach ($attributes as $attribute) :
	$options  = array();
	$values   = $attribute->values;
	$isMain   = !empty($attribute->main_attribute) ? (int) $attribute->main_attribute : 0;
	$selected = (!empty($attribute->selected)) ? (int) $attribute->selected : null;

	foreach ($values as $value)
	{
		$options[] = HTMLHelper::_('select.option', $value->id, $value->value, 'value', 'text');
	}
?>

<div class="form-group in-line">
	<div class="control-label">
		<label for="attribute_<?php echo $productId . '_' . $attribute->id;?>"><?php echo $attribute->name; ?></label>
	</div>
	<div class="controls">
		<?php echo HTMLHelper::_(
			'select.genericlist',
			$options,
			'attributes[' . $product->id . ']',
			' class="quickorder-product-attribute" attr-id="' . $attribute->id . '" is-main="' . $isMain . '"',
			'value',
			'text',
			$selected,
			'attribute_' . $product->id . '_' . $attribute->id
		);?>
	</div>
</div>

<?php
endforeach;
?>

<input type="hidden" id="quickorder-item-id" value="">
<input type="hidden" id="quickorder-item-price" value="">
