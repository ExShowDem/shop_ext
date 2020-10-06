<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

extract($displayData);

$maxCountRows = 10;

$staticTypes          = $displayData['staticTypes'];
$issetItems           = $displayData['issetItems'];
$dynamicTypes         = $displayData['dynamicTypes'];
$productId            = $displayData['productId'];
$stockroomId          = $displayData['stockroomId'];
$stockRoom            = $displayData['stockroom'];
$stockroomData        = $displayData['stockroomData'];
$issetDynamicVariants = $displayData['issetDynamicVariants'];
$unitMeasure          = $displayData['unitMeasure'];

?>
<!-- In case product has 1 product attribute -->
<table class="table table-condensed table-striped footable js-redshopb-footable redshopb-footable">
	<tbody>
	<?php foreach ($staticTypes as $key => $staticType) : ?>
		<tr>
			<th class="nowrap">
				<span><?php echo RedshopbHelperCollection::getProductItemValueFromType($staticType->type_id, $staticType); ?></span>
			</th>
			<td style="max-width:50px" class="nowrap">
				<div class="text-center <?php echo (isset($issetItems[$staticType->id])) ? 'inStock' : 'notExists' ?>">
					<?php if (isset($issetItems[$staticType->id])) : ?>
						<div class="input-group">
							<?php $stockAmount  = number_format(0, $unitMeasure->decimal_position);?>
							<?php $unlimited    = 0;?>
							<?php $inputType    = "text";?>
							<?php $inputValue   = Text::_('COM_REDSHOPB_STOCKROOM_UNLIMITED');?>
							<?php $inputClasses = 'input-xmini input-sm amountInput ajaxUpdateAmount';?>
							<?php $stockroomKey = $issetItems[$staticType->id]->id . '_' . $stockroomId; ?>

							<?php if (isset($stockroomData[$stockroomKey])) : ?>
								<?php $stockAmount = number_format((float) $stockroomData[$stockroomKey]->amount, $unitMeasure->decimal_position); ?>
								<?php $unlimited   = (int) $stockroomData[$stockroomKey]->unlimited; ?>
							<?php endif;?>

							<?php if ($unlimited == 0) : ?>
								<?php $inputType  = 'number';?>
								<?php $inputValue = $stockAmount;?>
							<?php endif; ?>

							<input type="<?php echo $inputType?>"
								   value="<?php echo $inputValue?>"
								   name="jform[amount][<?php echo $issetItems[$staticType->id]->id;?>]"
								   id="jform_amount_<?php echo $issetItems[$staticType->id]->id;?>"
								   data-unlimited="<?php echo $unlimited; ?>"
								   class="<?php echo $inputClasses;?>"
								<?php echo ($inputType == 'text') ? 'disabled="disabled"' : 'step="' . $unitMeasure->step . '" min="0"';?>
							/>
							<a class="btn btn-success ajaxUpdateAmountUnlimited"
							   href="javascript:void(0)"
							   data-field="jform_amount_<?php echo $issetItems[$staticType->id]->id;?>" >&infin;</a>
						</div>
					<?php endif;?>
				</div>
			</td>
		</tr>
	<?php endforeach; ?>
	</tbody>
</table>
