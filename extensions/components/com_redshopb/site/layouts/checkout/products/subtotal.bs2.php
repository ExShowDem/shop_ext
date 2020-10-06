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

$currency                 = $displayData['currency'];
$subtotalWithoutDiscounts = $displayData['subtotalWithoutDiscounts'];
$isEmail                  = $displayData['isEmail'];

$style = '';

if ($isEmail)
{
	$style = ' style="text-align: right"';
}
?>

<div class="row">
	<div class="col-md-10">
		<div class="pull-right">
			<strong><?php echo Text::_('COM_REDSHOPB_ORDER_ORDERITEMS_SUBTOTAL'); ?></strong>
		</div>
	</div>
	<div class="col-md-2 tnumber"<?php echo $style; ?>>
		<strong><?php echo RedshopbHelperProduct::getProductFormattedPrice($subtotalWithoutDiscounts, $currency) ?></strong>
	</div>
</div>
