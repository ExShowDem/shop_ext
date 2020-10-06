<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Views
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

$collectionId  = (int) $displayData['collectionId'];
$productId     = (int) $displayData['productId'];
$currency      = $displayData['currency'];
$mainAttrValue = isset($displayData['mainAttrValue']) ? (int) $displayData['mainAttrValue'] : 0;
$customerType  = $displayData['customerType'];
$customerId    = $displayData['customerId'];
$attributes    = $displayData['attributes'];
$productItemId = isset($displayData['productItemId']) ? (int) $displayData['productItemId'] : 0;
$price         = isset($displayData['price']) ? (float) $displayData['price'] : 0.0;
$mainId        = 0;

foreach ($attributes as $attribute) :
	$options  = array();
	$values   = $attribute['values'];
	$isMain   = !empty($attribute['main_attribute']) ? (int) $attribute['main_attribute'] : 0;
	$selected = (!empty($attribute->selected)) ? (int) $attribute->selected : null;

	if ($isMain)
	{
		$mainId = $attribute['id'];
	}

	foreach ($values as $id => $value)
	{
		$options[] = HTMLHelper::_('select.option', $id, $value, 'value', 'text');
	}
?>
	<div class="form-group in-line">
		<div class="control-label">
			<label for="attribute_<?php echo $productId . '_' . $attribute['id'];?>"><?php echo $attribute['name']; ?></label>
		</div>
		<div class="controls">
			<?php echo HTMLHelper::_(
				'select.genericlist',
				$options,
				'attributes[' . $productId . ']',
				' class="product-attribute" attr-id="' . $attribute['id'] . '" data-collection="'
				. $collectionId . '" data-currency="' . $currency . '" is-main="' . $isMain . '"',
				'value',
				'text',
				$selected,
				'attribute_' . $productId . '_' . $attribute['id']
			);?>
		</div>
	</div>
<?php
endforeach;
?>

<div id="productItemInput">
	<input value="" type="text" class="input-xmini input-sm amountInput" id="quantity_<?php echo $productId ?>"
		<?php if ((int) $productItemId == 0) : echo 'disabled="disabled"';
		endif; ?>
		name="quantity_<?php echo $productId ?>_<?php echo $productItemId ?>"/>
	<input value="<?php echo $price; ?>" type="hidden" id="price_<?php echo $productId ?>"
		name="price_<?php echo $productId ?>_<?php echo $productItemId ?>"/>
	<input value="<?php echo $currency; ?>" type="hidden" id="currency_<?php echo $productId ?>"
		name="currency_<?php echo $productId ?>_<?php echo $productItemId ?>"/>
	<input value="<?php echo $collectionId; ?>" type="hidden" id="collection_<?php echo $productId ?>"
		name="collection_<?php echo $productId ?>_<?php echo $productItemId ?>"/>
	<input value="<?php echo $mainId; ?>" type="hidden" id="dropDownSelected_<?php echo $productId ?>"
		name="dropDownSelected_<?php echo $productId ?>_<?php echo $productItemId ?>"/>
	<span id="productPrice"></span>
</div>
