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

HTMLHelper::_('behavior.tooltip');
HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('behavior.formvalidation');

$form   = $this->form;
$input  = Factory::getApplication()->input;
$isSend = $input->getInt('send', 0);
$source = $input->get('source', '');

if (!$this->isModal)
{
	echo RedshopbBrowserBreadcrumbs::renderBreadcrumbs();
}

if ($isSend)
{
	return;
}
?>
<div class="redshopb-myoffer-request-offer form-horizontal">
	<?php if (!$this->isModal):?>
	<h3><?php echo $this->title; ?></h3>
	<?php endif;?>
	<form id="sendOfferForm" name="sendOfferForm" method="post" class="form-validate form-vertical"
		  action="<?php echo RedshopbRoute::_('index.php?option=com_redshopb&view=myoffer&layout=requestoffer'); ?>">

		<?php foreach ($form->getFieldset('default') as $name => $field) : ?>
			<div class="control-group">
				<div class="control-label">
					<?php echo $field->label; ?>
				</div>
				<div class="controls">
					<?php echo $field->input; ?>
				</div>
			</div>
		<?php endforeach; ?>
		<?php echo HTMLHelper::_('form.token'); ?>

		<?php if ($this->isModal): ?>
			<input type="hidden" name="tmpl" value="component">
		<?php endif; ?>
		<input type="hidden" name="source" value="<?php echo $source; ?>">
		<input type="hidden" value="myoffer.requestOffer" name="task">
		<div class="control-group">
			<div class="controls">
				<button type="submit" class="btn btn-success validate">
					<i class="icon-envelope"></i>&nbsp;<?php echo Text::_('COM_REDSHOPB_OFFER_SEND_REQUEST'); ?>
				</button>
			</div>
		</div>
	</form>
</div>

