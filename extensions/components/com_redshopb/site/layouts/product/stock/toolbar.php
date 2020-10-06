<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

extract($displayData);

$isLocked = RedshopbEntityProduct::getInstance($productId)->canReadOnly();
?>
<h3>
	<?php echo Text::_('COM_REDSHOPB_STOCK') ?>
	<?php if (!$isLocked) : ?>
	<div class="pull-right">
		<div class="btn-toolbar">
			<div class="btn-group">
				<a class="btn btn-success" onclick="makeStockUnlimited('stock_room_<?php echo $stockroomId; ?>');" href="javascript:void(0)">
					<i class="icon-plus-sign"></i>
					<?php echo Text::_('JTOOLBAR_UNLIMITED_STOCK'); ?>
				</a>
			</div>
		</div>
	</div>
	<?php endif ?>
</h3>
