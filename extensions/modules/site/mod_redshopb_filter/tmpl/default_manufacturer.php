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

$extendedManufacturers = array();
?>

<?php if (!empty($manufacturer->items)): ?>
<script type="text/javascript">
	(function($) {
		$(document).ready(function()
		{
			$("#mod_redshopb_filter_manufacturer_<?php echo $module->id ?> .mod_redshopb_filter_manufacturer_item input")
				.click(function(event) {
					redSHOPB.shop.filters.filterProductList(event);
				});
		});
	})(jQuery);
</script>

<!-- Manufacturer filter -->
<div id="mod_redshopb_filter_manufacturer_<?php echo $module->id ?>" class="mod_redshopb_filter_manufacturer">

	<?php if (!empty($manufacturer->title)): ?>
	<h3><?php echo $manufacturer->title; ?></h3>
	<?php endif; ?>

	<div class="mod_redshopb_filter_manufacturer_wrapper">
		<?php if (!empty($manufacturer->items)): ?>
			<ul class="mod_redshopb_filter_manufacturer_items unstyled list-unstyled">
				<?php
				if ($optionCount):
					foreach ($manufacturer->items as $key => $item)
					{
						if (empty($item->count))
						{
							$extendedManufacturers[] = $item;
							unset($manufacturer->items[$key]);
						}
					}

					if (count($manufacturer->items) > $optionCount):
						$extendedManufacturers = array_merge(array_splice($manufacturer->items, $optionCount), $extendedManufacturers);
					endif;
				endif;

				foreach ($manufacturer->items as $item): ?>
					<?php $selected = ($item->selected) ? 'checked' : ''; ?>
					<li class="mod_redshopb_filter_manufacturer_item">
						<label class="checkbox <?php echo  ($item->selected) ? 'checked' : ''; ?>">
							<input type="checkbox" <?php echo $selected ?>
								value="<?php echo $item->value ?>" name="filter_manufacturer[]" />
								<?php echo $item->text ?><span class="count-number">(<?php echo $item->count ?> / <?php echo $item->totalCount ?>)</span>
						</label>
					</li>
				<?php endforeach; ?>

				<?php if (!empty($extendedManufacturers)): ?>
					<?php foreach ($extendedManufacturers as $item): ?>
					<?php $selected = ($item->selected) ? 'checked' : ''; ?>
					<li class="mod_redshopb_filter_manufacturer_item more"<?php echo ($item->selected) ? '' : ' style="display: none;"';?>>
						<label class="checkbox <?php
						echo  ($item->selected) ? 'checked' : '';
						echo (empty($item->count)) ? ' muted' : '' ?>">
							<input type="checkbox" <?php echo $selected ?>
								<?php echo (empty($item->count)) ? ' disabled="disabled' : '';?>
								value="<?php echo $item->value ?>" name="filter_manufacturer[]" />
								<?php echo $item->text ?><span class="count-number">(<?php echo $item->count ?> / <?php echo $item->totalCount ?>)</span>
						</label>
					</li>
					<?php endforeach; ?>
					<li class="mod_redshopb_filter_manufacturer_item show_more">
						<a href="javascript:void(0)" 
						   class="show_more_link"
						   data-target_class=".mod_redshopb_filter_manufacturer_item.more"
						   data-more_text="<?php echo Text::_('MOD_REDSHOPB_FILTER_OPTIONS_MORE', true) ?>"
						   data-less_text="<?php echo Text::_('MOD_REDSHOPB_FILTER_OPTIONS_LESS', true) ?>">
							<i class="icon icon-plus-sign-alt"></i>
							<span><?php echo Text::_('MOD_REDSHOPB_FILTER_OPTIONS_MORE') ?></span>
						</a>
					</li>
					<?php unset($extendedManufacturers); ?>
				<?php endif; ?>
				</ul>
		<?php endif; ?>
	</div>
</div>
<!-- Manufacturer filter - End -->
<?php endif;
