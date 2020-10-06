<?php
/**
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

extract($displayData);

if (empty($filterValues))
{
	return;
}

if (!is_array($value))
{
	$value = array($value);
}

$hasProducts = array();

foreach ($filterValues AS $index => $filterValue)
{
	if (!is_object($filterValue))
	{
		$objectValue             = new stdClass;
		$objectValue->data       = $filterValue;
		$objectValue->identifier = $filterValue;
		$filterValue             = $objectValue;
		$filterValues[$index]    = $filterValue;
	}

	$filterValue->classes = array('redshopb-filter-divelements-element');
	$filterValue->active  = false;
	$filterValue->style   = 'cursor: pointer;';

	if (in_array($filterValue->identifier, $value))
	{
		$filterValue->active    = true;
		$filterValue->classes[] = 'active';
	}

	if (empty($filterValue->count))
	{
		$filterValue->classes[] = 'muted';
		$filterValue->style     = 'cursor: not-allowed;';
	}

	if ($multiple)
	{
		$filterValue->identifier = htmlspecialchars($filterValue->identifier);
	}
}

$inputName  = 'filter[' . $filter->id . ']';
$inputType  = 'hidden';
$inputClass = 'redshopb-filter-divelements-element-value';

if ($multiple)
{
	$inputName  .= '[]';
	$inputType   = 'checkbox';
	$inputClass .= ' hidden';
}

?>
<script type="text/javascript">
	(function($){
		$(document).ready(function()
		{
			var filterOptions = $("#redshopbFilterDivElementsWrapper-<?php echo $filter->id ?> .redshopb-filter-divelements-element")
				.not('.muted');

			if(filterOptions.length == 1)
			{
				filterOptions[0].toggleClass('active')
			}

			filterOptions.click(function(event){
				event.preventDefault();
				<?php if (!$multiple): ?>
				// Clear all active class
				$("#redshopbFilterDivElementsWrapper-<?php echo $filter->id ?> .redshopb-filter-divelements-element.active").removeClass("active");
				$(this).toggleClass("active");
				var value = $(this).find(".redshopb-filter-divelements-element-value").val();
				$("#redshopbFilterDivElementsValue-<?php echo $filter->id ?>").val(value);
				<?php else: ?>
				if ($(this).hasClass("active")) {
					$(this).find("input.redshopb-filter-divelements-element-value").prop("checked", false);
				}
				else {
					$(this).find("input.redshopb-filter-divelements-element-value").prop("checked", true);
				}
				$(this).toggleClass("active");
				<?php endif; ?>

				<?php if (!empty($jsCallback)): ?>
				<?php echo $jsCallback ?>
				<?php endif; ?>
			});
		});
	})(jQuery);
</script>
<div class="redshopb-filter-divelements" id="redshopbFilterDivElementsWrapper-<?php echo $filter->id ?>">
	<?php foreach ($filterValues as $filterValue):?>

		<div class="<?php echo implode(' ', $filterValue->classes); ?>" style="<?php echo $filterValue->style;?>">
			<span class="redshopb-filter-divelements-element-text">
				<?php echo  $filterValue->data; ?>
			</span>
			<span class="products-count">
				(<?php echo $filterValue->count . '/' . $filterValue->totalCount?>)
			</span>
			<input type="<?php echo $inputType; ?>"
				   name="<?php echo $inputName; ?>"
				   class="<?php echo $inputClass;?>"
				   value="<?php echo $filterValue->identifier; ?>"
				<?php echo ($filterValue->active && $inputType == 'checkbox') ? 'checked' : '';?>/>
		</div>
	<?php endforeach; ?>

	<?php if (!$multiple): ?>
		<input
			id="redshopbFilterDivElementsValue-<?php echo $filter->id ?>"
			name="<?php echo $inputName ?>"
			value="<?php echo htmlentities($value) ?>"/>
	<?php else: ?>
		<input class="hidden" type="checkbox" name="<?php echo $inputName ?>" checked="checked" value="" />
	<?php endif ?>
</div>
