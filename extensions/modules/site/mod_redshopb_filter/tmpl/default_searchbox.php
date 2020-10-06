<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  mod_redshopb_filter
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die();
?>
<?php if (!$searchEnabled):?>
	<input type="hidden"
		   name="filter_search"
		   value="<?php echo $search->value ?>" 
		   id="mod_redshopb_filter_search_input_<?php echo $module->id ?>" 
		   data-protected="true"/>
	<?php return;?>
<?php endif;?>

<script type="text/javascript">
	(function($) {
		$(document).ready(function() {
			$(document).on('keyup', '#mod_redshopb_filter_search_input_<?php echo $module->id ?>', function () {
				var $cleanSearchField = $('#clean_search_field_<?php echo $module->id ?>');
				if ($(this).val()){
					$cleanSearchField.val(0);
				}else{
					$cleanSearchField.val(1);
				}
			});

			// Enable "Enter" button on search input
			$("#mod_redshopb_filter_search_input_<?php echo $module->id ?>").keypress(function(event){
				if (event.which == 13) {
					redSHOPB.shop.filters.filterProductList(event);
					return false;
				}
			});

			// Process for press on search button
			$("#mod_redshopb_filter_search_btn_<?php echo $module->id ?>").click(function(event){
				event.preventDefault();
				redSHOPB.shop.filters.filterProductList(event);
			});
		});
	})(jQuery);
</script>

<!-- Search input box -->
<div class="mod_redshopb_filter_search">
	<?php if (!empty($search->title)): ?>
		<h3><?php echo $search->title; ?></h3>
	<?php endif; ?>
	<div class="input-group">
		<input id="mod_redshopb_filter_search_input_<?php echo $module->id ?>" type="text" class="form-control"
			   width="<?php echo $search->width ?>"
			   placeholder="<?php echo $search->hint ?>" name="filter_search"
			   value="<?php echo $search->value ?>" />
		<div class="input-group-btn">
			<button id="mod_redshopb_filter_search_btn_<?php echo $module->id ?>" type="button" class="btn btn-primary">
				<span class="icon-search"></span>
			</button>
		</div>
	</div>
	<input type="hidden" id="clean_search_field_<?php echo $module->id ?>" value="<?php echo $search->value ? 0 : 1 ?>" name="clean_search_field">
</div>
<!-- Search input box - End -->
