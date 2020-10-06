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
			$("#mod_redshopb_filter_campaign_price_<?php echo $module->id ?> .mod_redshopb_filter_campaign_price_item input")
				.click(function(event) {
					redSHOPB.shop.filters.filterProductList(event);
				});
		});
	})(jQuery);
</script>

<!-- Campaign price filter -->
<div id="mod_redshopb_filter_campaign_price_<?php echo $module->id ?>" class="mod_redshopb_filter_campaign_price">
	<?php if (!empty($campaignPrice->title)): ?>
	<h3><?php echo $campaignPrice->title; ?></h3>
	<?php endif; ?>

	<ul class="mod_redshopb_filter_campaign_price_items unstyled list-unstyled">
		<li class="mod_redshopb_filter_campaign_price_item">
			<label class="checkbox <?php echo  $campaignPrice->value ? 'checked' : ''; ?>">
				<input type="checkbox" <?php echo  $campaignPrice->value ? 'checked' : ''; ?>
					value="1" name="filter_campaign_price" />
					<?php echo Text::_('MOD_REDSHOPB_FILTER_LABEL_CAMPAIGN_PRICE') ?>
			</label>
		</li>
	</ul>
</div>
<!-- Campaign price filter - End -->
