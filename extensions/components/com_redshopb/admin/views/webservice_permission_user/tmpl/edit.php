<?php
/**
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Views
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;

$action = 'index.php?option=com_redshopb&view=webservice_permission_user';

// HTML helpers
HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('vnrbootstrap.tooltip');
HTMLHelper::_('rjquery.chosen', 'select');
$isNew = (int) $this->item->user_id <= 0;
?>
<form action="<?php echo $action; ?>" method="post" name="adminForm" id="adminForm" class="form-horizontal">
	<div class="form-group">
		<div class="control-label">
			<?php echo $this->form->getLabel('webservice_permission_user_id'); ?>
		</div>
		<div class="controls">
			<?php echo $this->form->getInput('webservice_permission_user_id'); ?>
		</div>
	</div>
	<div class="form-group">
		<div class="control-label">
			<?php echo $this->form->getLabel('webservice_permissions'); ?>
		</div>
		<div class="controls">
			<?php echo $this->form->getInput('webservice_permissions'); ?>
		</div>
	</div>

	<!-- hidden fields -->
	<input type="hidden" name="option" value="com_redshopb">
	<input type="hidden" name="id" value="<?php echo $this->item->user_id; ?>">

	<?php if ($this->item->user_id > 0) : ?>
		<input type="hidden" name="user_id" value="<?php echo $this->item->user_id; ?>">
	<?php endif; ?>
	<input type="hidden" name="task" value="">
	<?php echo HTMLHelper::_('form.token'); ?>
</form>
