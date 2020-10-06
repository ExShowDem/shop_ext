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
$optionCount     = 5;
$extendedFilters = array();

if (!empty($filterValues) && $optionCount)
{
	foreach ($filterValues as $key => $filterValue)
	{
		if (empty($filterValue->count))
		{
			$extendedFilters[] = $filterValue;
			unset($filterValues[$key]);
		}
	}

	$showCount = ($optionCount < count($value)) ? count($value) : $optionCount;

	if (count($filterValues) > $showCount)
	{
		$extendedFilters = array_merge(array_splice($filterValues, $showCount), $extendedFilters);
	}
}

$hasValue = (!empty($value) && $value[0] != '');

?>

<div class="redshopb-filter-checkbox" id="redshopbFilterCheckboxWrapper-<?php echo $filter->id ?>">
		<ul class="unstyled list-unstyled">
		<?php if (!empty($filterValues)): ?>
		<?php foreach ($filterValues as $filterValue) : ?>
			<?php
			if (is_object($filterValue)) :
				$fData  = isset($filterValue->data) ? $filterValue->data : $filterValue->name;
				$fValue = $filterValue->identifier;
			else:
					$fData  = $filterValue;
					$fValue = $filterValue;
			endif;

			if ((!is_null($value) && in_array($fValue, $value)))
				{
				$checked = true;
			}
			else
				{
				$checked = false;
			}

			?>
			 <li class="redshopb-filter-checkbox-item-<?php echo $filter->id ?>">
				<label class="checkbox <?php
				echo (!is_null($value) && in_array($fValue, $value)) ? ' checked' : '';
				echo (empty($filterValue->count)) ? ' muted' : '' ?>">
					<input class="checkbox"
						   type="checkbox"
						   name="filter[<?php echo $filter->id ?>][]"
							<?php echo (!empty($jsCallback)) ? ' onchange="javascript: ' . $jsCallback . '"' : ''?>
							<?php echo $checked ? ' checked="checked"' : '';?>
							<?php echo (empty($filterValue->count) && !$checked) ? ' disabled="disabled' : '';?>
							value="<?php echo htmlentities($fValue) ?>" /> <?php echo $fData ?>&nbsp;
						<?php if (isset($filterValue->count)) : ?>
						<span class="products-count">(<?php echo $filterValue->count ?> / <?php echo $filterValue->totalCount ?>)</span>
						<?php endif; ?>
				</label>
			</li>
		<?php endforeach; ?>
		<?php endif; ?>

		<?php if (!empty($extendedFilters)): ?>
			<?php foreach ($extendedFilters as $filterValue): ?>
				<?php
				if (is_object($filterValue)) :
					$fData  = isset($filterValue->data) ? $filterValue->data : $filterValue->name;
					$fValue = $filterValue->identifier;
				else:
					$fData  = $filterValue;
					$fValue = $filterValue;
				endif;

				if ((!is_null($value) && in_array($fValue, $value)))
				{
					$checked = true;
				}
				else
				{
					$checked = false;
				}

				$styleValue = '';

				if (is_null($value) || !in_array($fValue, $value))
				{
					$styleValue = ' style="display: none;"';
				}
				?>
				<li class="redshopb-filter-checkbox-item-<?php echo $filter->id ?> more"<?php echo $styleValue;?>>
					<label class="checkbox
					<?php echo (!is_null($value) && in_array($fValue, $value)) ? ' checked' : '' ?>
					<?php echo (empty($filterValue->count)) ? ' muted' : '' ?>">
						<input class="checkbox"
							   type="checkbox"
							   name="filter[<?php echo $filter->id ?>][]"
							<?php echo (!empty($jsCallback)) ? ' onchange="javascript: ' . $jsCallback . '"' : ''?>
							<?php echo $checked ? ' checked="checked"' : '';?>
							<?php echo (empty($filterValue->count) && !$checked) ? ' disabled="disabled' : '';?>
							 value="<?php echo htmlentities($fValue) ?>"

						/> <?php echo $fData ?>&nbsp;
							<?php if (isset($filterValue->count)) : ?>
							<span class="products-count">(<?php echo $filterValue->count ?> / <?php echo $filterValue->totalCount ?>)</span>
							<?php endif; ?>
					</label>
				</li>
			<?php endforeach; ?>
				<li class="redshopb-filter-checkbox-item show_more">
					<label>
						<a href="javascript:void(0)"
						   class="show_more_link"
						   data-target_class=".redshopb-filter-checkbox-item-<?php echo $filter->id ?>.more"
						   data-more_text="<?php echo Text::_('MOD_REDSHOPB_FILTER_OPTIONS_MORE', true); ?>"
						   data-less_text="<?php echo Text::_('MOD_REDSHOPB_FILTER_OPTIONS_LESS', true); ?>">
								<i class="icon icon-plus-sign-alt"></i><span><?php echo Text::_('MOD_REDSHOPB_FILTER_OPTIONS_MORE', true); ?></span>
						</a>
					</label>
				</li>
		<?php endif; ?>
		</ul>
		<input class="hidden" type="checkbox" name="filter[<?php echo $filter->id ?>][]" checked="checked" value="" />
</div>
