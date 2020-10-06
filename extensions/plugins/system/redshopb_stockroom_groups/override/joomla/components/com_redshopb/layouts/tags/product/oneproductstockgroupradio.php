<?php
/**
 * @package     Aesir.E-Commerce.Tag
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

if ($isShop && $isOneProduct && $extThis->placeOrderPermission)
{
	$showStockAs = $extThis->product->showStockAs;

	if ($showStockAs == 'hide')
	{
		return;
	}

	// Stockroom amount
	$stockrooms = PlgSystemRedshopb_Stockroom_Groups::getProductsStockroomData($product->id);

	if (empty($stockrooms))
	{
		return;
	}

	$hasSelected = false;

	foreach ($stockrooms as $stockroom)
	{
		$amount           = (int) $stockroom->amount;
		$stockroom->state = 0;

		if ($amount > 0 || $stockroom->unlimited)
		{
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
	}

	$config         = RedshopbApp::getConfig();
	$stockPresented = $config->getString('stock_presented', 'semaphore');

	foreach ($stockrooms as $stockroom)
	{
		$amount = (int) $stockroom->amount;
		$class  = '';
		$style  = '';

		if ($amount > 0 || $stockroom->unlimited)
		{
			$class = 'inStock';
		}

		if ($stockroom->stockroom_group_id)
		{
			$value = '_' . $stockroom->stockroom_group_id;
		}
		else
		{
			$value = $stockroom->stockroom_id . '_';
		}

		$name = 'stockroomGroupId_' . $product->id . '_' . $cartPrefix;

		?>
		<label class="radio inline stockRoomRadio">
			<input type="radio" name="<?php echo $name; ?>" value="<?php
			echo $value; ?>"
				   id="<?php echo $name; ?>"
				   class="<?php echo $name; ?>" <?php
					echo $stockroom->state ? 'checked="checked"' : '';
					echo $stockroom->state == -1 ? 'disabled="disabled"' : '' ?>/>
			<?php

			switch ($showStockAs)
			{
				case 'actual_stock':
					if ($stockroom->unlimited)
					{
						$stockAmount = Text::_('COM_REDSHOPB_STOCKROOM_UNLIMITED');
					}
					else
					{
						$stockAmount = Text::_('COM_REDSHOPB_SHOP_IN_STOCK') . ': '
							. RedshopbHelperProduct::decimalFormat($stockroom->amount, $product->id);
					}

					echo $stockroom->name . '&nbsp;(' . $stockAmount . ')';
					break;
				case 'color_codes':
					switch ($stockPresented)
					{
						case 'semaphore':
							$product->amount = $amount;

							if ($stockroom->unlimited)
							{
								$colorAmount = ' amountMoreUpper';
							}
							else
							{
								$colorAmount = RedshopbHelperProduct::getColorAmount($product);
							}

							$iconStock = 'ok';

							switch ($colorAmount)
							{
								case ' amountLessZero':
									$iconStock = 'remove';
									break;
								case ' amountMoreZeroLessLower':
									$iconStock = 'warning-sign';
									break;
							}

							$class .= ' stockroomSemaphore';
							$class .= ' ' . $colorAmount;

							?><span><?php echo $stockroom->name; ?>&nbsp;<i class="icon-<?php echo $iconStock ?> <?php echo $class ?>"></i></span><?php
							break;
						case 'specific_color':
							if ($stockroom->color)
							{
								$style .= 'color: ' . $stockroom->color . ';';
							}

							$class .= ' stockroomSpecificColor';
							?><span><?php echo $stockroom->name; ?>&nbsp;<i class="icon-circle <?php echo $class ?>" style="<?php echo $style; ?>"></i></span><?php
							break;
					}
					break;
			}
			?>
		</label>
		<?php
	}
}
