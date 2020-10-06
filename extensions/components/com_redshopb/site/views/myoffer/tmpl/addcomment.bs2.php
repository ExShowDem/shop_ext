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

$action = RedshopbRoute::_('index.php?option=com_redshopb&task=myoffer.addComment');

// HTML helpers
HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('vnrbootstrap.tooltip');
HTMLHelper::_('rjquery.chosen', 'select');

echo RedshopbBrowserBreadcrumbs::renderBreadcrumbs();
?>

<div class="redshopb-myoffer">
	<form action="<?php echo $action; ?>" method="post" name="adminForm" id="adminForm" class="form-validate form-horizontal redshopb-myoffer-form">
		<div class="control-group">
		<?php echo Text::_('COM_REDSHOPB_MYOFFER_COMMENTS_LABEL')?>
		</div>
		<div class="control-group">
			<div class="control-label">
				<?php echo $this->form->getLabel('comments'); ?>
			</div>
			<div class="controls">
				<?php echo $this->form->getInput('comments'); ?>
			</div>
		</div>
		<button class="btn btn-primary" data-dismiss="modal" onclick="document.getElementById('addcomment').value=1;">
		<?php echo Text::_('COM_REDSHOPB_MYOFFER_SUBMIT_COMMENT')?></button>
		<button class="btn btn-primary" data-dismiss="modal">
		<?php echo Text::_('COM_REDSHOPB_MYOFFER_SUBMIT_WITHOUT_COMMENT')?></button>
		<input type="hidden" name="offid" value="<?php echo $this->offId; ?>">
		<input type="hidden" name="addcomment" id="addcomment" value="0">
	</form>
</div>

