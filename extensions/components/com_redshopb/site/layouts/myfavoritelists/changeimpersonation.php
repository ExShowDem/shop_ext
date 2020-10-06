<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Views
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

extract($displayData);

HTMLHelper::_('behavior.keepalive');

if (isset($displayHeader) && $displayHeader): ?>
<h3><?php echo Text::_('PLG_REDSHOPB_NEED_IMPERSONATE'); ?></h3>
<?php endif; ?>
<form id="modalChangeImpersonationForm" name="modalChangeImpersonationForm" method="post">
	<button onclick="Joomla.submitform('shop.sobemployee', document.getElementById('modalChangeImpersonationForm'))"
			class="btn btn-primary"><?php echo Text::_('JYES'); ?></button>
	<button class="btn btn-danger" data-dismiss="modal" aria-hidden="true"><?php echo Text::_('JCANCEL'); ?></button>
	<input type="hidden" name="option" value="com_redshopb"/>
	<input type="hidden" name="rsbuser_id" value="<?php echo $userId; ?>">
	<?php echo HTMLHelper::_('form.token'); ?>
	<input type="hidden" name="task" value=""/>
	<?php if (isset($return)): ?>
		<input type="hidden" name="return" value="<?php echo $return; ?>">
	<?php endif; ?>
</form>
