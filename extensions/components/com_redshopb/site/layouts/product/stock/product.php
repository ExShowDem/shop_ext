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

$unlimitedClass = 'btn ajaxUpdateProductAmountUnlimited';
$inputClass     = 'input amountInput ajaxUpdateProductAmount';
$inputType      = 'number';
$primaryAmount  = isset($stockroom->amount) ? $stockroom->amount : 0;
$amount         = number_format($primaryAmount, $unitMeasure->decimal_position, '.', '');
$unlimited      = 0;

if (!empty($stockroom->unlimited))
{
	$unlimitedClass .= ' btn-success';
	$inputClass     .= ' disabled';
	$inputType       = 'text';
	$amount          = Text::_('COM_REDSHOPB_STOCKROOM_UNLIMITED');
	$unlimited       = 1;
}

$isLocked = RedshopbEntityProduct::getInstance($productId)->canReadOnly();

?>
<div class="row">
	<div class="col-md-12">
		<div class="redshopb-product-stockroom">
			<?php echo RedshopbLayoutHelper::render('product.stock.toolbar', $displayData) ?>
			<h4><?php echo Text::_('COM_REDSHOPB_STOCKROOM_PRODUCT') ?></h4>
			<div class="input-append">
				<input
						type="<?php echo $inputType ?>"
						class="<?php echo $inputClass ?>"
						value="<?php echo $amount ?>"
						id="jform_product_<?php echo $productId ?>_amount"
						data-unlimited="<?php echo $unlimited ?>"
					<?php if (!empty($stockroom->unlimited) || $isLocked): ?>
						disabled="disabled"
					<?php endif; ?>

					<?php if ($inputType == 'number' && empty($stockroom->unlimited)): ?>
						step="<?php echo $unitMeasure->step; ?>"
						min="0"
					<?php endif; ?>
				/>
				<?php if (!$isLocked): ?>
				<a class="<?php echo $unlimitedClass ?>" href="javascript:void(0)"
				   data-field="jform_product_<?php echo $productId ?>_amount">
					<strong>&infin;</strong>
				</a>
				<?php endif ?>
			</div>
		</div>
	</div>
</div>
