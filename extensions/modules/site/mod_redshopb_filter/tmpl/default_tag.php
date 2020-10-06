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

if (empty($tag->list))
{
	return;
}
?>

<script type="text/javascript">
	(function($) {
		$(document).ready(function() {
			$('.mod_redshopb_filter_tag_items').hide();

			$(".mod_redshopb_filter_tag_group > label").click(function(event) {
				$(this).parent().find('.mod_redshopb_filter_tag_items').slideToggle('slow');
			});

			$("#mod_redshopb_filter_tag_<?php echo $module->id ?> .mod_redshopb_filter_tag_item input").click(function(event){
				redSHOPB.shop.filters.filterProductList(event);
			});
		});
	})(jQuery);
</script>

<!-- Tags filter -->
<div class="mod_redshopb_filter_tag" id="mod_redshopb_filter_tag_<?php echo $module->id ?>">
	<?php if (!empty($tag->title)): ?>
	<h3><?php echo $tag->title; ?></h3>
	<?php endif; ?>
	<ul class="mod_redshopb_filter_tag_list unstyled list-unstyled">
		<?php foreach ($tag->list as $tagGroupName => $tagGroup): ?>
		<li class="mod_redshopb_filter_tag_group">
			<label>
				<?php echo $tagGroupName; ?><span class="count-number">(<?php echo $tagGroup->count ?>/ <?php echo $tagGroup->totalCount ?>)</span>
			</label>
			<?php if (!empty($tagGroup->tags)): ?>
				<ul class="mod_redshopb_filter_tag_items unstyled list-unstyled">
				<?php
				if ($optionCount):
					$tagShowCount = ($optionCount < $tagGroup->selectedCount) ? $tagGroup->selectedCount : $optionCount;

					if (count($tagGroup->tags) > $tagShowCount):
						$extendedTags = array_splice($tagGroup->tags, $tagShowCount);
					endif;
				endif;
				?>

				<?php foreach ($tagGroup->tags as $item): ?>
					<?php $selected = ($item->selected) ? 'checked' : ''; ?>
					<li class="mod_redshopb_filter_tag_item">
						<label class="checkbox <?php echo ($item->selected) ? 'checked' : ''; ?>">
							<input type="checkbox" value="<?php echo $item->value ?>" name="filter_tag[]" <?php echo $selected ?> /><?php echo $item->text ?><span class="count-number">(<?php echo $item->count ?> / <?php echo $item->totalCount ?>)</span>
						</label>
					</li>
				<?php endforeach; ?>

				<?php if (isset($extendedTags)): ?>
					<?php foreach ($extendedTags as $item): ?>
						<?php $selected = ($item->selected) ? 'checked' : ''; ?>
					<li class="mod_redshopb_filter_tag_item more" style="display: none;">
						<label class="checkbox <?php echo  ($item->selected) ? 'checked' : ''; ?>">
							<input type="checkbox" value="<?php echo $item->value ?>" name="filter_tag[]" <?php echo $selected ?> /><?php echo $item->text ?><span class="count-number">(<?php echo $item->count ?> / <?php echo $item->totalCount ?>)</span>
						</label>
					</li>
					<?php endforeach; ?>
					<li class="mod_redshopb_filter_tag_item show_more">
						<a href="javascript:void(0)" 
						   class="show_more_link"
						   data-target_class=".mod_redshopb_filter_tag_item.more"
						   data-more_text="<?php echo Text::_('MOD_REDSHOPB_FILTER_OPTIONS_MORE', true) ?>"
						   data-less_text="<?php echo Text::_('MOD_REDSHOPB_FILTER_OPTIONS_LESS', true) ?>">
							<i class="icon icon-plus-sign-alt"></i>
							<span><?php echo Text::_('MOD_REDSHOPB_FILTER_OPTIONS_MORE') ?></span>
						</a>
					</li>
					<?php unset($extendedTags); ?>
				<?php endif; ?>
				</ul>
			<?php endif; ?>
		</li>
		<?php endforeach; ?>
	</ul>
</div>
<!-- Tags filter - End -->
