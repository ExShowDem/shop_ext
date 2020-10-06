<?php
/**
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

/**
 * Layout variables
 * ==========================
 * @var  array   $displayData   List of available data.
 * @var  object  $filter        Filter fieldset data.
 * @var  string  $value         Filter value.
 * @var  string  $jsCallback    Javascript function callback.
 * @var  array   $filterValues  Filter values list.
 */
extract($displayData);

use Joomla\Registry\Registry;

$scope = RInflector::pluralize($filter->scope);

// @TODO Need to add config for that.
$width     = 30;
$height    = 30;
$quality   = 100;
$crop      = 0;
$showCount = true;
?>
<script type="text/javascript">
	(function($){
		$(document).ready(function(){
			$("#redshopbFilterImagesWrapper-<?php echo $filter->id ?> ul li").click(function(event){
				event.preventDefault();
				$("#redshopbFilterImagesWrapper-<?php echo $filter->id ?> ul li.active").removeClass("active");
				$(this).addClass("active");
				$("#redshopb_filter_images_<?php echo $filter->id ?>").val($(this).attr("data-value"));
				<?php echo $jsCallback ?>;
			});
		});
	})(jQuery);
</script>

<div class="redshopb-filter-images" id="redshopbFilterImagesWrapper-<?php echo $filter->id ?>">
	<ul class="unstyled list-unstyled">
		<?php foreach ($filterValues as $image): ?>
			<?php
			$params = new Registry($image->params);
			$thumb  = $params->get('internal_url', '');
			?>
			<?php if (!empty($thumb)): ?>
				<?php
				$thumb = RedshopbHelperThumbnail::originalToResize($thumb, $width, $height, $quality, $crop, 'field-images');
				$class = $value === $image->title ? 'active' : '';
				?>
				<li data-value="<?php echo $image->title ?>" class="<?php echo $class ?>">
					<label>
						<img src="<?php echo $thumb ?>" title="<?php echo RedshopbHelperThumbnail::safeAlt($image->title) ?>"
							 width="<?php echo $width ?>" height="<?php echo $height ?>" /> <?php echo $image->title ?>
						<?php if ($showCount): ?>
							( <?php echo $image->count ?> / <?php echo $image->totalCount ?> )
						<?php endif; ?>
					</label>
				</li>
			<?php endif; ?>
		<?php endforeach; ?>
	</ul>
	<input type="hidden" id="redshopb_filter_images_<?php echo $filter->id ?>" name="filter[<?php echo $filter->id ?>]" value="<?php echo $value ?>" />
</div>
