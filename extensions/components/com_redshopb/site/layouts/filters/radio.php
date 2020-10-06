<?php
/**
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

extract($displayData);

// @Todo: Will be add config later.
$optionCount = 5;

if ($optionCount)
{
	$showCount = ($optionCount < count($value)) ? count($value) : $optionCount;

	if (count($filterValues) > $showCount)
	{
		$extendedFilters = array_splice($filterValues, $showCount);
	}
}
?>

<script type="text/javascript">
	(function($){
		$(document).ready(function(){
			$("#redshopbFilterRadio-<?php echo $filter->id ?>-clear").click(function(event){
				event.preventDefault();
				$("#redshopbFilterRadioWrapper-<?php echo $filter->id ?> input[type='radio']:checked").prop('checked', false);
				$('#redshopbFilterRadio-<?php echo $filter->id ?>-empty').prop('checked', true);
				<?php if (!empty($jsCallback)): ?>
				<?php echo $jsCallback ?>
				<?php endif; ?>
			});

			<?php if (isset($extendedFilters)): ?>
			$("#redshopbFilterRadioWrapper-<?php echo $filter->id ?> .show_more").click(function(event){
				event.preventDefault();

				$(this).parent().find(".redshopb-filter-radio-item.more").slideToggle();

				var showMore = $(this).find('.show_more_link');

				if ($(showMore).children('.icon').hasClass('icon-plus-sign-alt')) {
					$(showMore).children('span').text("<?php echo Text::_('MOD_REDSHOPB_FILTER_OPTIONS_LESS', true) ?>");
				}
				else {
					$(showMore).children('span').text("<?php echo Text::_('MOD_REDSHOPB_FILTER_OPTIONS_MORE', true) ?>");
				}

				$(showMore).children('.icon').toggleClass('icon-plus-sign-alt').toggleClass('icon-minus-sign-alt');
			});
			<?php endif; ?>
		});
	})(jQuery);
</script>

<div class="redshopb-filter-radio" id="redshopbFilterRadioWrapper-<?php echo $filter->id ?>">
	<?php if (!empty($filterValues)): ?>
		<ul class="unstyled list-unstyled">
		<?php foreach ($filterValues as $filterValue): ?>
			<?php
			if (is_object($filterValue)) :
				$fData  = isset($filterValue->name) ? $filterValue->name : $filterValue->data;
				$fValue = $filterValue->identifier;
			else:
					$fData  = $filterValue;
					$fValue = $filterValue;
			endif;
			?>
			<li class="redshopb-filter-radio-item">
				<label class="radio">
					<input class="radio" type="radio" name="filter[<?php echo $filter->id ?>]"
						<?php if (!empty($jsCallback)): ?>
						onchange="javascript:<?php echo $jsCallback ?>"
						<?php endif; ?>

						<?php if ($fValue == $value): ?>
							checked="checked"
						<?php endif; ?>
						value="<?php echo htmlentities($fValue) ?>" /> <?php echo $fData ?>
						<?php if (isset($filterValue->count)) : ?>
						<span class="products-count">(<?php echo $filterValue->count ?> / <?php echo $filterValue->totalCount ?>)</span>
						<?php endif; ?>
				</label>
			</li>
		<?php endforeach; ?>

		<?php if (isset($extendedFilters)): ?>
			<?php foreach ($extendedFilters as $filterValue): ?>
				<?php
				if (is_object($filterValue)) :
					$fData  = $filterValue->name;
					$fValue = $filterValue->identifier;
				else:
					$fData  = $filterValue;
					$fValue = $filterValue;
				endif;
				?>
				<li class="redshopb-filter-radio-item more" style="display: none;">
					<label class="radio">
						<input class="radio" type="radio" name="filter[<?php echo $filter->id ?>]"
							<?php if (!empty($jsCallback)): ?>
							onchange="javascript:<?php echo $jsCallback ?>"
							<?php endif; ?>

							<?php if ($fValue == $value): ?>
								checked="checked"
							<?php endif; ?>
							value="<?php echo htmlentities($fValue) ?>" /> <?php echo $fData ?>
							<?php if (isset($filterValue->count)) : ?>
							<span class="products-count">(<?php echo $filterValue->count ?> / <?php echo $filterValue->totalCount ?>)</span>
							<?php endif; ?>
					</label>
				</li>
			<?php endforeach; ?>
				<li class="redshopb-filter-radio-item show_more">
					<label>
						<a href="javascript:void(0)" class="show_more_link">
							<i class="icon icon-plus-sign-alt"></i><span><?php echo Text::_('MOD_REDSHOPB_FILTER_OPTIONS_MORE', true) ?></span>
						</a>
					</label>
				</li>
		<?php endif; ?>
		</ul>
		<a href="javascript:void(0);" id="redshopbFilterRadio-<?php echo $filter->id ?>-clear">Clear</a>
		<input id="redshopbFilterRadio-<?php echo $filter->id ?>-empty" class="hidden" type="radio"
			name="filter[<?php echo $filter->id ?>]" value="" />
	<?php endif; ?>
</div>
