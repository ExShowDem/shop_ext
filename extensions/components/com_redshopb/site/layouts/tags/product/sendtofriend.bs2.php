<?php
/**
 * @package     Aesir.E-Commerce.Tag
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;

$input        = Factory::getApplication()->input;
$categoryId   = $input->getInt('category_id', 0);
$collectionId = $input->getInt('collection_id', 0);
?>
<script type="text/javascript" language="javascript">
	jQuery(document).ready(function(){
		jQuery('#sendToFriendModal').on('shown.bs.modal', function () {
			 jQuery('<iframe height="650" width="99.6%" style="zoom:0.60" src="<?php echo Uri::root(); ?>index.php?option=com_redshopb&tmpl=component&task=shop.getSendToFriendForm&id=<?php echo (int) $product->id; ?>&category_id=<?php echo $categoryId; ?>&collection_id=<?php echo $collectionId; ?>" frameborder="0"/>').appendTo('#sendToFriendModal .modal-body');
		}).on('hidden.bs.modal', function () {
			jQuery('#sendToFriendModal .modal-body iframe').remove();
		});
	});
</script>
<a class="btn hasTooltip" href="#sendToFriendModal" id="sendToFriendButton" title="<?php echo Text::_('COM_REDSHOPB_SHOP_SEND_LETTER'); ?>" role="button" data-toggle="modal">
	<i class="icon-envelope"></i>
</a>
<div id="sendToFriendModal" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="sendToFriendModalLabel" aria-hidden="true">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">X</button>
		<h3 id="sendToFriendModalLabel"><?php echo Text::_('COM_REDSHOPB_SHOP_SEND_LETTER'); ?></h3>
	</div>
	<div class="modal-body"></div>
</div>
