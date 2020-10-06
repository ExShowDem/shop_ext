<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  mod_redshopb_filter
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die();

$price->min = floor($price->min);
$price->max = ceil($price->max);

if ($price->min < $price->max): ?>
<script type="text/javascript">
	(function($) {
		$(document).ready(function() {
			// Apply bootstrap-slider for price range
			var $priceInput = $('#mod_redshopb_filter_price_input_<?php echo $module->id ?>');
			$priceInput.bootstrapSlider({});
			$('.mod_redshopb_filter_price_numbers').width($('#mod_redshopb_filter_price_input_slider_<?php echo $module->id ?>').width() + 15);

			function checkPriceInputValue(){
				var splitPrice = $priceInput.val().split(',');
				var min = splitPrice[0],max = splitPrice[1];

				// Set empty price filter, when user max and min equal variables from slider max and min
				if (max == $priceInput.data('slider-max') && min == $priceInput.data('slider-min')){
					$priceInput.val('');
				}
			}

			$priceInput.on("slideStop", function(slideEvent){
				checkPriceInputValue();
				redSHOPB.shop.filters.filterProductList(slideEvent);
			});

			checkPriceInputValue();

			// This string help avoid close menu, when behind menu has a slider
			// Important here set display none for tooltip after bootstrapSlider initialization, so it will get right position
			$('#mod_redshopb_filter_price_<?php echo $module->id ?> .slider .tooltip').css({'display':'none'});
		});
	})(jQuery);
</script>

<!-- Price filter -->
<div class="mod_redshopb_filter_price" id="mod_redshopb_filter_price_<?php echo $module->id ?>">

	<?php if (!empty($price->title)): ?>
	<h3><?php echo $price->title; ?></h3>
	<?php endif; ?>

	<input type="text"
		name="filter_price"
		id="mod_redshopb_filter_price_input_<?php echo $module->id ?>"
		value="<?php echo $price->value ?>"
		data-slider-id="mod_redshopb_filter_price_input_slider_<?php echo $module->id ?>"
		data-slider-min="<?php echo $price->min ?>"
		data-slider-max="<?php echo $price->max ?>"
		data-slider-step="1"
		data-slider-range="true"
		<?php if (!empty($price->value)): ?>
		data-slider-value="[<?php echo $price->value ?>]"
		<?php else: ?>
		data-slider-value="[<?php echo $price->min ?>,<?php echo $price->max ?>]"
		<?php endif; ?>
		/>
	<div class="mod_redshopb_filter_price_numbers">
		<div class="pull-left"><?php echo $price->min ?></div>
		<div class="pull-right"><?php echo $price->max ?></div>
		<div class="clear"></div>
	</div>
</div>
<!-- Price filter - End -->
<?php endif;
