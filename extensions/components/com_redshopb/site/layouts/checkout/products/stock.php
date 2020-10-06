<?php
/**
 * @package     Aesir.E-Commerce.Admin
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

$item  = $displayData['item'];
$field = $displayData['field'];

$showStockAs    = $displayData['showStockAs'];
$stockPresented = $displayData['stockPresented'];

$displayHtml = ($item->stock === -1) ? Text::_('COM_REDSHOPB_STOCKROOM_UNLIMITED') : $item->stock;

if ($showStockAs == 'color_codes')
{
	$class     = '';
	$iconStock = '';

	if (isset($item->stock))
	{
		$amount = $item->stock;

		if ($amount == -1)
		{
			$class = 'inStock';
		}
		else
		{
			$amount = $amount < 0 ? 0 : $amount;
			$class  = ($amount > 0) ? 'inStock' : $class;
		}
	}

	switch ($stockPresented)
	{
		case 'semaphore':
			// Workaround for getColorAmount hardcoded fieldname
			$item->amount = $item->stock;
			$colorAmount  = RedshopbHelperProduct::getColorAmount($item);
			$iconStock    = 'ok';

			switch ($colorAmount)
			{
				case ' amountLessZero':
					$iconStock = 'remove';
					break;
				case ' amountMoreZeroLessLower':
					$iconStock = 'warning-sign';
					break;
			}

			$class      .= $colorAmount;
			$displayHtml = '<i class="icon-' . $iconStock . ' ' . $class . '"></i><br/>';

			break;
		case 'specific_color':
			$stockroomEntity = RedshopbEntityStockroom::load($item->stockroom_id);
			$style           = '';

			if ($stockroomEntity->get('color'))
			{
				$style .= 'color: ' . $stockroomEntity->get('color') . ';';
			}

			$class      .= ' stockroomSpecificColor';
			$displayHtml = '<i class="icon-circle ' . $class . '" style="' . $style . '"></i>';

			break;
	}
}

?>

<td class="field_<?php echo $field->fieldname ?>">
	<?php echo $displayHtml;?>
</td>


