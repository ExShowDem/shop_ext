<?php
/**
 * @package     Aesir.E-Commerce.Admin
 * @subpackage  Views
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Plugin\PluginHelper;

// HTML helpers
HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('vnrbootstrap.tooltip');

RHelperAsset::load('redshopb_quick_order.min.css', 'com_redshopb');
HTMLHelper::script('com_redshopb/redshopb.quickorder.js', array('framework' => false, 'relative' => true), array());
HTMLHelper::script('com_redshopb/redshopb.cart.js', array('framework' => false, 'relative' => true), array());

$app = Factory::getApplication();

$customerId   = $app->getUserState('shop.customer_id', 0);
$customerType = $app->getUserState('shop.customer_type', '');

$action = RedshopbRoute::_(Uri::root() . 'index.php?option=com_redshopb');

$jsCallback   = (!empty($displayData['jsCallback'])) ? $displayData['jsCallback'] : false;
$placeHolder  = (!empty($displayData['placeHolder'])) ? $displayData['placeHolder'] : Text::_('COM_REDSHOPB_MYPAGE_QUICK_ORDER_PLACE_HOLDER');
$minChars     = (!empty($displayData['minChars'])) ? (int) $displayData['minChars'] : 3;
$displayItems = (!empty($displayData['displayItems'])) ? (int) $displayData['displayItems'] : 10;
?>

<script type="text/javascript">
	jQuery(document).ready(function()
	{
		redSHOPB.quickorder.init('<?php echo Text::_('COM_REDSHOPB_ITEMS_ADDED_TO_CART');?>', '<?php echo Text::_('COM_REDSHOPB_NOTHING_SELECTED');?>');

		var input     = jQuery('#js-product-search');
		var original  = jQuery('#redshopb-quickorder-tool-addtocart-button');
		var addToCart = original.clone();

		input.on('keyup', function(event) {
			if (input.val().length >= 3)
			{
				addToCart.removeClass('btn-muted disabled');
				addToCart.addClass('btn-success');
			}

			redSHOPB.ajax.search(event, redSHOPB.quickorder.productSelect, '#js-product-search', 'quickorder');
		});

		input.closest('form').on('submit', function()
		{
			return false;
		});

		addToCart.on('click', redSHOPB.quickorder.addToCart);
		original.remove();
		jQuery('#quickorder-buttons').append(addToCart);
	});
</script>
<div class="redshopb-quickorder-tool">
	<form action="<?php echo $action ?>">
		<div class="row-fluid">
			<div class="span8">
				<input type="text" id="js-product-search" name="search" class="input input-block-level"
					   placeholder="<?php echo $placeHolder ?>" autocomplete="off" />
			</div>
			<div class="span4">
				<div id="quickorder-buttons" class="input-append">
					<span id="quickorder-attribute-container" class="in-line"></span>
					<input type="number" id="redshopb-quickorder-tool-quantity" class="input input-small" name="quantity" data-decimal="0" tabindex="21"/>
					<span class="add-on" id="redshopb-quickorder-tool-unit-name"><?php echo Text::_('COM_REDSHOPB_PRODUCT_UOM_PCS') ?></span>
					<a href="javascript:void(0);" id="redshopb-quickorder-tool-addtocart-button" class="btn btn-muted disabled" tabindex="22">
						<i class="icon icon-shopping-cart"></i>
						<span class="addtocart-span"><?php echo Text::_('COM_REDSHOPB_QUICK_ORDER_ADD') ?></span>
					</a>
				</div>
			</div>
		</div>
		<div class="row-fluid hidden searchProductResultBlock">
			<div id="js-product-search-results" class="span12"></div>
		</div>
		<?php if (PluginHelper::getPlugin('vanir', 'product_custom_text')): ?>
		<div class="row">
			<div class="col-md-12" id="quick_order_product_custom_text"></div>
		</div>
		<?php endif; ?>
		<input type="hidden" name="simple_search" value="1"/>
		<input type="hidden" name="product_id" value=""/>
		<input type="hidden" name="collection_id" value=""/>
		<input type="hidden" name="currency" value=""/>
		<input type="hidden" name="task" value=""/>
		<input type="hidden" name="result_layout" value="result-list"/>
		<input type="hidden" name="isQuickOrder"  value="true"/>
		<?php echo HTMLHelper::_('form.token') ?>
	</form>
</div>
