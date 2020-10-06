<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Views
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

$data      = $displayData;
$productId = $data['productId'];
$inList    = false;

$lists = array();

if (is_array($data['lists']))
{
	$lists = $data['lists'];
}

$productLists = $data['productLists'];
?>
	<div id="product_add_msg" class="alert-error"></div>
	<h5><?php echo Text::_('COM_REDSHOPB_MYFAVORITELISTS_VIEW_DEFAULT_TITLE') ?></h5>
<?php
if (count($lists)) :
	foreach ($lists as $list) :
		if (in_array($list->id, $productLists)) :
			$inList = true;
		endif;
		?>
		<p>
			<label for="favoritelist_<?php echo $productId ?>_<?php echo $list->id ?>">
				<input
					class="toggle-product-favorite-list-<?php echo $productId ?>" data-product="<?php echo $productId ?>" data-list="<?php echo $list->id ?>" type="checkbox"
					name="favoritelist_<?php echo $productId ?>_<?php echo $list->id ?>"
					id="favoritelist_<?php echo $productId ?>_<?php echo $list->id ?>" <?php echo $inList ? 'checked="checked"' : ''; ?>
				/>
					<?php echo $list->name ?>
			</label>
		</p>
		<?php
	endforeach;
endif;
?>
	<p>
	<div class="input-group">
		<input type="text" placeholder="<?php echo Text::_('COM_REDSHOPB_MYFAVORITELIST_NEW_LIST') ?>" name="list-new-<?php echo $productId ?>" id="list-new-<?php echo $productId ?>" class="input-small" />
		<button type="button" href="#" id="list-new-button-<?php echo $productId ?>" class="btn disabled addon">+</button>
	</div>
	</p>
<?php

if ($inList)
{
	?>
	<script>
		jQuery(document).ready(function(){
			jQuery('#addtofavorite_<?php echo $productId ?>').addClass('added');
		});
	</script>
	<?php
}
