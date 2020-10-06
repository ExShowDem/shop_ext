<?php
/**
 * @package     Aesir.E-Commerce.Tag
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;

if (isset($extThis->product->dropDownTypes[$product->id])): ?>
	<?php if (count($extThis->product->dropDownTypes[$product->id]) > 1): // If more one type attributes - display select ?>
		<?php
		$options = array();

		foreach ($extThis->product->dropDownTypes[$product->id] as $dropDownType):
			// Generate select
			$text      = RedshopbHelperCollection::getProductItemValueFromType($dropDownType->type_id, $dropDownType, false);
			$options[] = HTMLHelper::_('select.option', $dropDownType->id, $text, 'value', 'text');
		endforeach;
		?>
		<?php echo HTMLHelper::_(
			'select.genericlist',
			$options,
			'dropDownType[' . $product->id . ']',
			' class="dropDownAttribute" data-collection="' . $extThis->product->collectionId . '" data-currency="' . $extThis->product->currency . '"',
			'value',
			'text',
			$extThis->product->dropDownSelected[$product->id],
			'dropDownType_' . $product->id . '_' . $dropDownType->product_attribute_id
		);
?>
	<?php else: // Only one variant, so display as text ?>
		<h4 class="nowrap">
			<?php echo RedshopbHelperCollection::getProductItemValueFromType(
				$extThis->product->dropDownTypes[$product->id][0]->type_id, $extThis->product->dropDownTypes[$product->id][0], false
			); ?>
			&nbsp;&nbsp;
			<a href="<?php echo RedshopbRoute::_('index.php?option=com_redshopb&task=shop.ajaxWashAndCare&productId=' . $product->id . '&flatAttrId=' . $extThis->product->dropDownTypes[$product->id][0]->id);?>"
			   class="btn btn-link btn-small"
			   id="washCareLink_<?php echo $product->id . '_'; ?>"
			   data-toggle="modal" data-target="#myModal">
				<i class="icon-info-sign icon-large"></i>
			</a>
		</h4>
	<?php endif; ?>
<?php endif;
