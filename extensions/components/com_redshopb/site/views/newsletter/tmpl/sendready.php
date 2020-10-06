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
use Joomla\CMS\Factory;

$action              = RedshopbRoute::_('index.php?option=com_redshopb&view=newsletter&layout=sendready&id=' . $this->item->id);
$app                 = Factory::getApplication();
$input               = $app->input;
$subtask             = $input->getCmd('subtask', '');
$nbTotalReceiversAll = count($this->subscribers);
$allSubscribeUsers   = $nbTotalReceiversAll - $this->alreadySent;
?>
<div class="row">
	<?php if ($subtask == ''): ?>
		<form action="<?php echo $action; ?>" method="post" name="adminForm" id="adminForm"
			  class="form-validate form-horizontal">
			<?php if (empty($this->nbqueue)) : ?>
				<?php if ($this->alreadySent): ?>
					<div class="alert alert-warning alert-block">
						<p><?php echo Text::sprintf('COM_REDSHOPB_NEWSLETTER_ALREADY_SENT', $this->alreadySent); ?>
							<br/><?php echo Text::_('COM_REDSHOPB_NEWSLETTER_REMOVE_ALREADY_SENT'); ?>
							<br/><?php echo HTMLHelper::_('select.booleanlist', "onlynew", 'onclick="if(this.value == 1){document.getElementById(\'nbreceivers\').innerHTML = \'' . $allSubscribeUsers . '\';}else{document.getElementById(\'nbreceivers\').innerHTML = \'' . $nbTotalReceiversAll . '\'}"', 1, Text::_('JYES'), Text::_('COM_REDSHOPB_NEWSLETTER_SEND_TO_ALL')); ?>
						</p>
					</div>
				<?php endif; ?>
			<?php else: ?>
				<div class="alert alert-info alert-block">
					<p>
						<?php echo Text::sprintf('COM_REDSHOPB_NEWSLETTER_NB_PENDING_EMAIL', $this->nbqueue, '<b><i>' . $this->item->subject . '</i></b>')
							. '<br/>' . Text::_('COM_REDSHOPB_NEWSLETTER_SEND_CONTINUE'); ?>
					</p>
					<input type="hidden" name="totalsend" value="<?php echo $this->nbqueue; ?>"/>
					<input type="hidden" name="subtask" value="continuesend" />
				</div>
			<?php endif; ?>
			<?php
			if ($nbTotalReceiversAll || $this->nbqueue) : ?>
				<div class="text-center">
					<?php
					if (empty($this->nbqueue))
					{
						echo Text::sprintf('COM_REDSHOPB_NEWSLETTER_SENT_TO_NUMBER', '<span style="font-weight:bold;" id="nbreceivers" >' . $allSubscribeUsers . '</span>');
					}
					?>
					<br/>
					<input
						class="btn btn-primary btn-lg" type="submit"
						value="<?php echo empty($this->nbqueue) ? Text::_('COM_REDSHOPB_NEWSLETTER_SEND_BUTTON') : Text::_('COM_REDSHOPB_NEWSLETTER_CONTINUE')?>"
					/>
				</div>
			<?php endif; ?>
			<input type="hidden" name="task" value="newsletter.send">
			<input type="hidden" name="tmpl" value="component">
			<?php echo HTMLHelper::_('form.token'); ?>
		</form>
	<?php endif; ?>
</div>
