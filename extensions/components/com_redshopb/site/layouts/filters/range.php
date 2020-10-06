<?php
/**
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

extract($displayData);

// Load Bootstrap-Slider library if price filter enable.
RHelperAsset::load('lib/bootstrap-slider/bootstrap-slider.min.js', 'com_redshopb');
RHelperAsset::load('lib/bootstrap-slider/bootstrap-slider.min.css', 'com_redshopb');

$filterMin = ($filterMin >= $filterMax) ? 0 : $filterMin;

$min          = $filterMin;
$max          = $filterMax;
$currentValue = array($min, $max);

if (is_array($value))
{
	$value = reset($value);
}

$inputValue = $value;

if (!empty($value))
{
	$value = explode('-', $value);
	$min   = ((float) $value[0] <= (float) $min) ? $value[0] : $min;
	$max   = ((float) $value[1] >= (float) $max) ? $value[1] : $max;

	$currentValue[0] = $value[0];
	$currentValue[1] = $value[1];
}
?>

<script type="text/javascript">
	(function($){
		$(document).ready(function(){
			var $filterRange<?php echo $filter->id ?> = $("#redshopbFilterRange-<?php echo $filter->id ?>");
			$filterRange<?php echo $filter->id ?>.bootstrapSlider({
				range: true,
				step: 1,
				min: <?php echo $filterMin ?>,
				max: <?php echo $filterMax ?>,
				value: [<?php echo (float) $currentValue[0] ?>, <?php echo (float) $currentValue[1] ?>]
			});
			$filterRange<?php echo $filter->id ?>.data({
				'slider-max': <?php echo $filterMax ?>,
				'slider-min': <?php echo $filterMin ?>
			});

			function checkInputFilterRange<?php echo $filter->id ?>(){
				var splitPrice = $filterRange<?php echo $filter->id ?>.val();
				var min = splitPrice[0],max = splitPrice[1];

				// Set empty price filter, when user max and min equal variables from slider max and min
				if (max == $filterRange<?php echo $filter->id ?>.data('slider-max') && min == $filterRange<?php echo $filter->id ?>.data('slider-min')){
					$("#redshopbFilterRange-<?php echo $filter->id ?>-input").val('');
				}
			}

			$filterRange<?php echo $filter->id ?>.on("slideStop", function(event){
				<?php if (!empty($jsCallback)): ?>
				$("#redshopbFilterRange-<?php echo $filter->id ?>-label").text(event.value[0] + ' - ' + event.value[1]);
				$("#redshopbFilterRange-<?php echo $filter->id ?>-input").val(event.value[0] + '-' + event.value[1]);
				checkInputFilterRange<?php echo $filter->id ?>();
				<?php echo $jsCallback ?>
				<?php endif; ?>
			});

			checkInputFilterRange<?php echo $filter->id ?>();
		});
	})(jQuery);
</script>

<div class="redshopb-filter-range" id="redshopbFilterRangeWrapper-<?php echo $filter->id ?>">
	<div id="redshopbFilterRange-<?php echo $filter->id ?>"></div>
	<div class="redshopb-filter-range-number">
		<div class="pull-left"><?php echo $min ?></div>
		<div class="pull-right"><?php echo $max ?></div>
		<div class="clear"></div>
		<input type="hidden" id="redshopbFilterRange-<?php echo $filter->id ?>-input" name="filter[<?php echo $filter->id ?>]" value="<?php echo $inputValue ?>" />
	</div>
</div>
