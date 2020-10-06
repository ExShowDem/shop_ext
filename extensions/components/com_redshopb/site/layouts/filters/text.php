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
?>

<?php if (!empty($jsCallback)): ?>
<script type="text/javascript">
	jQuery(document).ready(function(){
		jQuery("#redshopbFilterTextWrapper-<?php echo $filter->id ?> > input").keypress(function(event){
			if (event.which == 13) {
				<?php echo $jsCallback ?>
				return false;
			}
		});
	});
</script>
<?php endif; ?>

<div class="redshopb-filter-text" id="redshopbFilterTextWrapper-<?php echo $filter->id ?>">
	<input type="text" placeholder="<?php echo $filter->title ?>" class="input input-xlarge"
		name="filter[<?php echo $filter->id ?>]" value="<?php echo htmlentities($value) ?>" />
</div>
