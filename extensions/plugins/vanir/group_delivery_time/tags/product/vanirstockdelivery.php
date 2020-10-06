<?php
/**
 * @package     Aesir.E-Commerce.Tag
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

// @codingStandardsIgnoreFile

defined('_JEXEC') or die;

use Joomla\CMS\Session\Session;
use Joomla\CMS\Plugin\PluginHelper;

if (PluginHelper::isEnabled('vanir', 'group_delivery_time') && $isShop && $extThis->placeOrderPermission)
{
	$showStockAs = $extThis->product->showStockAs;

	if ($showStockAs == 'hide')
	{
		return;
	}

	JImport("plugins.vanir.group_delivery_time.helper.helper", JPATH_ROOT);
	$stockroomId = PlgVanirGroupDeliveryTimeHelper::getMinDeliveryStock($product->id);

	if (!$stockroomId)
	{
		return;
	}

	$vanirStock = PlgVanirGroupDeliveryTimeHelper::getDeliveryTime($stockroomId);

	if (!$vanirStock)
	{
		return;
	}
	?>
	<script type="text/javascript">
		(function($){
			$(document).ready(function(){
				if ($("#stockroom_<?php echo $product->id ?>_inProd<?php echo $product->id ?>").length) {
					$("#stockroom_<?php echo $product->id ?>_inProd<?php echo $product->id ?>").change(function(event){
						event.preventDefault();

						$.post(
							"index.php?option=com_ajax&plugin=VanirLoadGroupFromStockRoom&group=vanir&format=json",
							{
								"id" : $(this).val(),
								"<?php echo Session::getFormToken() ?>": 1
							},
							function (response) {
								response = $.parseJSON(response);
								$("#vanir_stock_<?php echo $product->id ?> .vanir_stock_color").css("background-color", response.color);
								$("#vanir_stock_<?php echo $product->id ?> .vanir_stock_label").html(response.label);
							}
						)
							.fail(function (response) {
								alert(response.responseText);
							});
					});
				}
			});
		})(jQuery);
	</script>

	<div class="vanir_stock_delivery" id="vanir_stock_<?php echo $product->id ?>">
		<div class="pull-left">
			<div class="vanir_stock_color"
			     style="background-color: <?php echo $vanirStock->color ?>; width: 15px; height: 15px; border-radius: 8px; display: block; margin-right: 5px;"></div>
		</div>
		<p class="vanir_stock_label"><?php echo $vanirStock->label ?></p>
	</div>
	<?php
}
