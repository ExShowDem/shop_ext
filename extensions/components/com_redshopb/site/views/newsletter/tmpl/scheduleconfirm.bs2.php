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

$action = RedshopbRoute::_('index.php?option=com_redshopb&view=newsletter&layout=scheduleconfirm&id=' . $this->item->id);
HTMLHelper::_('rjquery.chosen', 'select');

?>
<div class="row-fluid">
	<form action="<?php echo $action; ?>" method="post" name="adminForm" id="adminForm"
		  class="form-validate form-horizontal">
		<div class="text-center">
			<div>
				<?php echo Text::_('COM_REDSHOPB_NEWSLETTER_SEND_DATE') . $this->calendar . '&nbsp; @ ' . $this->hours . ' : ' . $this->minutes; ?>
			</div>
			<br />
			<?php echo Text::sprintf('COM_REDSHOPB_NEWSLETTER_SENT_TO_NUMBER', '<span style="font-weight:bold;" id="nbreceivers" >' . count($this->subscribers) . '</span>'); ?>
			<br />
			<br />
			<button class="btn btn-primary btn-large" type="submit"><?php echo Text::_('COM_REDSHOPB_NEWSLETTER_SCHEDULE'); ?></button>
		</div>
		<input type="hidden" name="subtask" value="schedule"/>
		<input type="hidden" name="task" value="newsletter.send"/>
		<input type="hidden" name="tmpl" value="component"/>
		<?php echo HTMLHelper::_('form.token'); ?>
	</form>
</div>
