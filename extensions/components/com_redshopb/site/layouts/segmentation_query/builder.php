<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Views
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

$configuration = $displayData['configuration'];
$jsCallback    = $displayData['jsCallback'];
$builderValue  = $displayData['builderValue'];
?>

<script type="text/javascript">
	(function($){
		$(document).ready(function(){
			<?php if (!empty($builderValue)): ?>
			var queryBuilderValue = <?php echo $builderValue ?>;
			<?php endif; ?>

			// Apply jQuery Builder
			$('#segmentationQueryBuilder').queryBuilder({
				plugins: ['bt-tooltip-errors'],
				filters: <?php echo json_encode($configuration) ?>,
				<?php if (!empty($builderValue)): ?>
				rules: queryBuilderValue,
				<?php endif; ?>
			})
			.on('afterCreateRuleFilters.queryBuilder', function(event, rule) {
				// Re-apply chosen for new select box
				$('select').chosen({"disable_search_threshold":10,"allow_single_deselect":true});
			})
			.on('afterCreateRuleOperators.queryBuilder', function(event, rule) {
				// Re-apply chosen for new select box
				$('select').chosen({"disable_search_threshold":10,"allow_single_deselect":true});
			})
			.on('afterCreateRuleInput.queryBuilder', function(event, rule) {
				// Re-apply chosen for new select box
				$('select').chosen({"disable_search_threshold":10,"allow_single_deselect":true});
			});

			$('#segmentation_query-builder-apply').click(function(event) {
				event.preventDefault();

				var sqlRaw = $('#segmentationQueryBuilder').queryBuilder('getSQL', false, false);
				var jsonRaw = JSON.stringify($('#segmentationQueryBuilder').queryBuilder('getRules'), undefined, 1);

				if (sqlRaw.sql != '') {
					<?php if (!empty($jsCallback)): ?>
					<?php echo $jsCallback ?>(sqlRaw.sql, jsonRaw);
					<?php endif; ?>
				}
			});

			$('#segmentation_query-builder-reset').click(function(event){
				event.preventDefault();
				$('#segmentationQueryBuilder').queryBuilder('reset');
			});
		});
	})(jQuery);
</script>

<div class="redshopb-segmentation_query-builder">
	<div class="redshopb-segmentation_query-builder-toolbar">
		<button class="btn btn-warning" id="segmentation_query-builder-reset">
			<?php echo Text::_('COM_REDSHOPB_SEGMENTATION_QUERY_RESET') ?>
		</button>
		<button class="btn btn-success" id="segmentation_query-builder-apply">
			<?php echo Text::_('COM_REDSHOPB_SEGMENTATION_QUERY_APPLY') ?>
		</button>
	</div>
	<div class="clearfix"></div>
	<div id="segmentationQueryBuilder">
	</div>
</div>
