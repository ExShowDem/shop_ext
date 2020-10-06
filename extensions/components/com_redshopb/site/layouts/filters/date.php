<?php
/**
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;

extract($displayData);

// Add jquery UI js.
HTMLHelper::_('rjquery.datepicker');

// Load the common css
RHelperAsset::load('rdatepicker.css', 'redcore');
?>

<script type="text/javascript">
	(function($){
		$(document).ready(function(){
			$('#redshopbFilterDate-<?php echo $filter->id ?>').datepicker({
				dateFormat: "yy-mm-dd"
			});
		});
	})(jQuery);
</script>

<div class="redshopb-filter-date" id="redshopbFilterDateWrapper-<?php echo $filter->id ?>">
	<input type="text"id="redshopbFilterDate-<?php echo $filter->id ?>" class="input"
		value="<?php echo htmlentities($value) ?>" name="filter[<?php echo $filter->id ?>]" />
</div>
