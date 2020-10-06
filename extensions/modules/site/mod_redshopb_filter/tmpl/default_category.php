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

$extendedCategories = array();

if ($optionCount)
{
	foreach ($category->categories as $key => $item)
	{
		if (empty($item->count))
		{
			$extendedCategories[] = $item;
			unset($category->categories[$key]);
		}
	}

	if (count($category->categories) > $optionCount)
	{
		$extendedCategories = array_merge(array_splice($category->categories, $optionCount), $extendedCategories);
	}
}

?>
<script type="text/javascript">
	(function($) {
		$(document).ready(function()
		{
			$("#mod_redshopb_filter_category_<?php echo $module->id ?> .mod_redshopb_filter_category_item input")
				.click(function(event) {
					redSHOPB.shop.filters.filterProductList(event);
				});
		});
	})(jQuery);
</script>

<!-- Category filter -->
<div id="mod_redshopb_filter_category_<?php echo $module->id ?>">
	<h3><?php echo $category->title; ?></h3>
	<div class="mod_redshopb_filter_category_wrapper">
		<ul class="mod_redshopb_filter_category_items unstyled list-unstyled">
			<?php foreach ($category->categories as $item): ?>
				<li class="mod_redshopb_filter_category_item">
					<label class="checkbox<?php echo  ($item->selected) ? ' checked' : ''; ?>">
						<input type="checkbox" <?php echo ($item->selected) ? 'checked' : '';?>
							   value="<?php echo $item->value ?>" name="filter_category[]" />
						<?php echo $item->text ?> <span class="count-number">(<?php echo $item->count . '/' . $item->total;?>)</span>
					</label>
				</li>
			<?php endforeach; ?>

			<?php foreach ($extendedCategories AS $item):?>
				<li class="mod_redshopb_filter_category_item more"<?php echo ($item->selected) ? '' : ' style="display: none;"';?>>
					<label class="checkbox<?php echo  ($item->selected) ? ' checked' : ''; echo (empty($item->count)) ? ' muted' : '' ?>">
						<input type="checkbox" <?php echo ($item->selected) ? 'checked' : '';?>
							<?php echo (empty($item->count)) ? ' disabled="disabled' : '';?>
							   value="<?php echo $item->value ?>" name="filter_category[]" />
						<?php echo $item->text ?> <span class="count-number">(<?php echo $item->count . '/' . $item->total;?>)</span>
					</label>
				</li>
			<?php endforeach;?>

			<?php if (!empty($extendedCategories)):?>
				<li class="mod_redshopb_filter_category_item show_more">
					<a href="javascript:void(0)"
					   class="show_more_link"
					   data-target_class=".mod_redshopb_filter_category_item.more"
					   data-more_text="<?php echo Text::_('MOD_REDSHOPB_FILTER_OPTIONS_MORE', true) ?>"
					   data-less_text="<?php echo Text::_('MOD_REDSHOPB_FILTER_OPTIONS_LESS', true) ?>">
						<i class="icon icon-plus-sign-alt"></i>
						<span><?php echo Text::_('MOD_REDSHOPB_FILTER_OPTIONS_MORE') ?></span>
					</a>
				</li>
			<?php endif;?>
		</ul>
	</div>
</div>
<!-- Category filter - End -->
