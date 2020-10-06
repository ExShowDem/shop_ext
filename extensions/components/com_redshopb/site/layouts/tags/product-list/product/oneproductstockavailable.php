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

$isOneProduct = false;

if (!isset($extThis->dropDownTypes[$productId]) && !isset($extThis->staticTypes[$productId]))
{
	$isOneProduct = true;
}

if ($isShop && $isOneProduct && $extThis->placeOrderPermission)
{
	$showStockAs = RedshopbHelperStockroom::getStockVisibility();

	if ($showStockAs == 'hide')
	{
		return;
	}

?>
	<script type='text/javascript'>
		jQuery(document).ready(function(){
			jQuery('.hasTooltipStock').tooltip({"html": true, "title": function(){
				return jQuery(this).siblings(".availableStock").html();
			}});
		});
	</script>
<?php

	// Stockroom amount
	$stockrooms = RedshopbHelperStockroom::getProductsStockroomData($productId);

if (!RedshopbHelperStockroom::productHasInStock($productId) || empty($stockrooms))
	{
	echo '<span class="productNoStockFlag"><i class="icon icon-circle text-error hasTooltipStock"></i><span class="availableStock hidden">' . Text::_('COM_REDSHOPB_PRODUCT_NO_STOCK') . '</span></span>';

	return;
}

	echo '<span class="productInStockFlag"><i class="icon icon-circle text-success hasTooltipStock"></i><span class="availableStock hidden">' . Text::_('COM_REDSHOPB_PRODUCT_ON_STOCK') . '</span></span>';
}
