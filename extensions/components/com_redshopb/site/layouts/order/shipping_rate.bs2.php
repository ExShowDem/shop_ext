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

$shippingPrice = $displayData['shippingPrice'];
$currency      = $displayData['currency'];
$isEmail       = isset($displayData['isEmail']) ? $displayData['isEmail'] : false;
?>

<div class="row-fluid">
	<div class="span12">
		<div class="row-fluid">
			<div class="span10">
				<div class="pull-right">
					<strong><?php echo Text::_('COM_REDSHOPB_ORDER_SHIPPING_PRICE'); ?></strong>
				</div>
			</div>
			<div class="span2 tnumber"<?php if ($isEmail) : ?> style="text-align: right"<?php
									  endif; ?>>
				<strong><?php echo RedshopbHelperProduct::getProductFormattedPrice($shippingPrice, $currency) ?></strong>
			</div>
		</div>
	</div>
</div>


