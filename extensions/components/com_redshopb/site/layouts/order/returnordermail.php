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

$data = (object) $displayData;

$textTitle = sprintf(
	Text::_('COM_REDSHOPB_RETRUN_ORDER_MAIL_TITLE'),
	str_pad($data->returnOrderId, 6, '0', STR_PAD_LEFT)
);
$textMain  = sprintf(
	Text::_('COM_REDSHOPB_RETRUN_ORDER_MAIL_MESSAGE'),
	$data->productName,
	$data->returnOrderQuantity
);
?>
	<table class="topreceipt">
		<tr>
			<td >
				<h4>
					<?php echo $textTitle; ?>
				</h4>
				<p id="return-order-mail-product">
					<?php echo $textMain; ?>
				</p>
				<p id="return-order-mail-product">
					<?php echo Text::_('COM_REDSHOPB_RETURN_ORDER_COMMENT'); ?>
				</p>
				<p id="return-order-mail-comment">
					<?php if (!empty($data->comment)) : ?>
						<?php echo $data->comment; ?>
					<?php else: ?>
						<?php echo Text::_('COM_REDSHOPB_RETRUN_ORDER_MAIL_NO_COMMENT'); ?>
					<?php endif; ?>
				</p>
			</td>
		</tr>
	</table>
