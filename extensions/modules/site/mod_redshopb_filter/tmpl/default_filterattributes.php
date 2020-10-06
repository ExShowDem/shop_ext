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

if (empty($attributeFilters))
{
	return;
}

$extendedAttributeFilters = array();
$optionCount              = 2;

if ($optionCount)
{
	foreach ($attributeFilters AS $attribute => &$attributeValue)
	{
		if (count($attributeValue) > $optionCount)
		{
			$extendedAttributeFilters[$attribute] = array_splice($attributeValue, $optionCount);
		}
	}
}

?>
<script type="text/javascript">
	(function($) {
		$(document).ready(function()
		{
			$("#mod_redshopb_filter_attribute_<?php echo $module->id ?> .mod_redshopb_filter_attribute_item input")
				.click(function(event) {
					redSHOPB.shop.filters.filterProductList(event);
				});
		});
	})(jQuery);
</script>

<div id="mod_redshopb_filter_attribute_<?php echo $module->id ?>">
	<h3><?php echo Text::_('MOD_REDSHOPB_FILTER_ATTRIBUTES_TITLE');?></h3>

	<?php foreach ($attributeFilters as $attribute => $items): ?>
	<h5><?php echo $attribute; ?></h5>
	<div class="mod_redshopb_filter_attribute_wrapper">
		<ul class="mod_redshopb_filter_attribute_items unstyled list-unstyled">
			<?php foreach ($items as $item): ?>
				<li class="mod_redshopb_filter_attribute_item">
					<label class="checkbox<?php echo  ($item->selected) ? ' checked' : ''; ?>">
						<input type="checkbox" <?php echo ($item->selected) ? 'checked' : '';?>
							   value="<?php echo $item->value ?>" name="filter_attribute[<?php echo $attribute;?>][]" />
						<?php echo $item->value ?> <span class="count-number">(<?php echo $item->count . '/' . $item->total;?>)</span>
					</label>
				</li>
			<?php endforeach; ?>

			<?php if (!empty($extendedAttributeFilters[$attribute])):?>

				<?php foreach ($extendedAttributeFilters[$attribute] AS $item):?>
					<li class="mod_redshopb_filter_attribute_item_<?php echo $attribute;?> mod_redshopb_filter_attribute_item more"<?php echo ($item->selected) ? '' : ' style="display: none;"';?>>
						<label class="checkbox<?php echo  ($item->selected) ? ' checked' : ''; ?>">
							<input type="checkbox" <?php echo ($item->selected) ? 'checked' : '';?>
								   value="<?php echo $item->value ?>" name="filter_attribute[<?php echo $attribute;?>][]" />
							<?php echo $item->value ?> <span class="count-number">(<?php echo $item->count . '/' . $item->total;?>)</span>
						</label>
					</li>
				<?php endforeach;?>

				<li class="mod_redshopb_filter_attribute_item show_more">
					<a href="javascript:void(0)"
					   class="show_more_link"
					   data-target_class=".mod_redshopb_filter_attribute_item_<?php echo $attribute;?>.more"
					   data-more_text="<?php echo Text::_('MOD_REDSHOPB_FILTER_OPTIONS_MORE', true) ?>"
					   data-less_text="<?php echo Text::_('MOD_REDSHOPB_FILTER_OPTIONS_LESS', true) ?>">
						<i class="icon icon-plus-sign-alt"></i>
						<span><?php echo Text::_('MOD_REDSHOPB_FILTER_OPTIONS_MORE') ?></span>
					</a>
				</li>
			<?php endif;?>
		</ul>
	</div>
	<?php endforeach;?>
</div>
