<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  mod_redshopb_filter
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die();

use Joomla\CMS\Language\Text;
?>

<script type="text/javascript">
	(function($) {
		$(document).ready(function()
		{
			$("#mod_redshopb_filter_stock_<?php echo $module->id ?> .mod_redshopb_filter_stock_item input")
				.click(function(event) {
					redSHOPB.shop.filters.filterProductList(event);
				});
		});
	})(jQuery);
</script>

<!-- Stock filter -->
<div id="mod_redshopb_filter_stock_<?php echo $module->id ?>" class="mod_redshopb_filter_stock">
	<?php if (!empty($stock->title)): ?>
	<h3><?php echo $stock->title; ?></h3>
	<?php endif; ?>

	<ul class="mod_redshopb_filter_stock_items unstyled list-unstyled">
		<li class="mod_redshopb_filter_stock_item">
			<label class="checkbox <?php echo  $stock->value ? 'checked' : ''; ?>">
				<input type="checkbox" <?php echo  $stock->value ? 'checked' : ''; ?>
					value="1" name="filter_stock" />
					<?php echo Text::_('MOD_REDSHOPB_FILTER_LABEL_STOCK') ?>
			</label>
		</li>
	</ul>
</div>
<!-- Stock filter - End -->
