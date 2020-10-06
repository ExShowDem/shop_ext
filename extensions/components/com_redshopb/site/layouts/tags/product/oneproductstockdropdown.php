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
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;

if ($isShop && $isOneProduct && $extThis->placeOrderPermission)
{
	$showStockAs = $extThis->product->showStockAs;

	if ($showStockAs == 'hide')
	{
		return;
	}

	// Stockroom amount
	$stockrooms = RedshopbHelperStockroom::getProductsStockroomData($product->id);
	$class      = array();
	$style      = array();
	$iconStock  = array();

	if (empty($stockrooms))
	{
		return;
	}

	$config         = RedshopbApp::getConfig();
	$stockPresented = $config->getString('stock_presented', 'semaphore');
	$hasSelected    = false;

	foreach ($stockrooms as $stockroom)
	{
		$amount                              = (int) $stockroom->amount;
		$class[$stockroom->stockroom_id]     = '';
		$style[$stockroom->stockroom_id]     = '';
		$iconStock[$stockroom->stockroom_id] = 'circle';
		$stockroom->state                    = 0;

		if ($amount > 0 || $stockroom->unlimited)
		{
			$class[$stockroom->stockroom_id] = 'inStock';

			if (!$hasSelected)
			{
				$stockroom->state = 1;
				$hasSelected      = true;
			}
		}
		else
		{
			$stockroom->state = -1;
		}

		if ($showStockAs == 'color_codes')
		{
			if ($stockPresented == 'semaphore')
			{
				$product->amount = $amount;

				if ($stockroom->unlimited)
				{
					$colorAmount = ' amountMoreUpper';
				}
				else
				{
					$colorAmount = RedshopbHelperProduct::getColorAmount($product);
				}

				$iconStock[$stockroom->stockroom_id] = 'ok';

				switch ($colorAmount)
				{
					case ' amountLessZero':
						$iconStock[$stockroom->stockroom_id] = 'remove';
						break;
					case ' amountMoreZeroLessLower':
						$iconStock[$stockroom->stockroom_id] = 'warning-sign';
						break;
				}

				$class[$stockroom->stockroom_id] .= ' stockroomSemaphore';
				$class[$stockroom->stockroom_id] .= ' ' . $colorAmount;
			}
			elseif ($stockPresented == 'specific_color')
			{
				if ($stockroom->color)
				{
					$style[$stockroom->stockroom_id] .= 'color: ' . $stockroom->color . ';';
				}

				$class[$stockroom->stockroom_id] .= ' stockroomSemaphoreBadge';
			}
		}
	}

	HTMLHelper::_('rjquery.framework');
	RHelperAsset::load('js/vendor/select2/select2.full.js', 'com_redshopb');
	RHelperAsset::load('css/vendor/select2/select2.css', 'com_redshopb');
	$selector = "stockroom_" . $product->id . "_" . $cartPrefix;

	if (!array_key_exists('initStockroomDropdownSelector', RedshopbHelperTemplate::$layoutsInitValues))
	{
		RedshopbHelperTemplate::$layoutsInitValues['initStockroomDropdownSelector'] = true;

		// Color style for dropdown
		Factory::getDocument()->addScriptDeclaration('
		(function($){
			$(document).ready(function () {
				function formatState (state) {
					if (!state.id) { return state.text; }
					var $stateElement = $(state.element);
					if ($stateElement.data(\'displaycolor\') == \'color_codes\'){
						return $(\'<span>\' + state.text + \'<i class="icon-\'+$stateElement.data(\'icon\')+\' \'+$stateElement.data(\'class\')+\'" style="\'+$stateElement.data("style")+\';"></i></span>\');
					}else{
						return $(\'<span>\' + state.text + \'</span>\');
					}
				};
				function template(data, container) {
					var $dataElement = $(data.element);
					if ($dataElement.data(\'displaycolor\') == \'color_codes\'){
						return $(\'<span>\' + data.text + \'<i class="icon-\'+$dataElement.data(\'icon\')+\' \'+$dataElement.data(\'class\')+\'" style="\'+$dataElement.data("style")+\';"></i></span>\');
					}else{
						return $(\'<span>\' + data.text + \'</span>\');
					}
				}
			
				$(".stockroomDropdown").show().removeClass(\'chzn-done\').next().remove();
				$(".stockroomDropdown").select2({
					minimumResultsForSearch: Infinity,
					templateResult: formatState,
					templateSelection: template
				});
			});
		})(jQuery);'
		);
	}
		?>
	<!-- Stockroom amount -->
	<select name="stockroom_<?php echo $product->id ?>" class="input chosen-icon stockroomDropdown <?php echo $selector ?>"
			id="<?php echo $selector ?>">
	<?php $isSelected = false; ?>

	<?php foreach ($stockrooms as $stockroom): ?>
			<option value="<?php
			echo $stockroom->stockroom_id ?>"
				<?php if ($stockroom->state > 0 && $isSelected === false):?>
					<?php echo ' selected="selected"' ?>
					<?php $isSelected = true; ?>
				<?php else: ?>
					<?php echo '' ?>
				<?php endif; ?><?php
				echo ($stockroom->state == -1) ? 'disabled="disabled"' : '' ?>
			data-class="<?php echo $class[$stockroom->stockroom_id] ?>"
			data-style="<?php echo $style[$stockroom->stockroom_id]; ?>"
			data-icon="<?php echo $iconStock[$stockroom->stockroom_id]; ?>"
			data-displaycolor="<?php echo $showStockAs; ?>"
			>
				<?php if ($showStockAs == 'actual_stock'): ?>
					<?php if ($stockroom->unlimited): ?>
						<?php echo $stockroom->name ?> (<?php echo Text::_('COM_REDSHOPB_STOCKROOM_UNLIMITED') ?>)
					<?php else: ?>
						<?php $stockAmount = RedshopbHelperProduct::decimalFormat($stockroom->amount, $product->id); ?>
						<?php echo $stockroom->name ?> (<?php echo Text::_('COM_REDSHOPB_SHOP_IN_STOCK') ?>: <?php echo $stockAmount ?>)
					<?php endif; ?>
				<?php elseif ($showStockAs == 'color_codes'): ?>
					<?php echo $stockroom->name ?>
				<?php endif; ?>
			</option>
	<?php endforeach; ?>
	</select>
	<!-- Stockroom - end -->
<?php
}
