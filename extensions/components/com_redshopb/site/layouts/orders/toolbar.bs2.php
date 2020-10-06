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

$formName = $displayData['formName'];
?>
<h2>
	<?php echo Text::_('COM_REDSHOPB_ORDER_LIST_TITLE'); ?>
</h2>
<div class="row-fluid">
	<div class="btn-toolbar">
		<div class="btn-group">
			<?php if (RedshopbHelperACL::getPermission('manage', 'order', array('create'), true)): ?>
			<button class="btn btn-success" onclick="Joomla.submitform('order.add',
				document.getElementById('<?php echo $formName; ?>'))" href="#">
				<i class="icon-file-text-alt"></i>
				<?php echo Text::_('JTOOLBAR_NEW') ?>
			</button>
			<?php endif; ?>

			<?php if (RedshopbHelperACL::getPermission('manage', 'order', array('edit'), true)): ?>
			<button class="btn"
					onclick="if (document.<?php echo $formName; ?>.boxchecked.value==0){alert('<?php echo Text::_('COM_REDSHOPB_PLEASE_SELECT_ITEM', true); ?>');}
						else{ Joomla.submitform('order.edit', document.getElementById('<?php echo $formName; ?>'))}"
					href="#">
				<i class="icon-edit"></i>
				<?php echo Text::_('JTOOLBAR_EDIT') ?>
			</button>
			<?php endif; ?>
		</div>
		<?php if (RedshopbHelperACL::getPermission('manage', 'order', array('delete'), true)): ?>
		<div class="btn-group">
			<button class="btn btn-danger"
					onclick="if (document.<?php echo $formName; ?>.boxchecked.value==0){alert('<?php echo Text::_('COM_REDSHOPB_PLEASE_SELECT_ITEM', true); ?>');}
						else{ Joomla.submitform('orders.delete', document.getElementById('<?php echo $formName; ?>'))}"
					href="#">
				<i class="icon-trash"></i>
				<?php echo Text::_('JTOOLBAR_DELETE') ?>
			</button>
		</div>
		<?php endif; ?>
	</div>
</div>
