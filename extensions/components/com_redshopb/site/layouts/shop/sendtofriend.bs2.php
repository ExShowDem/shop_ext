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

extract($displayData);

HTMLHelper::_('vnrbootstrap.modal', 'sendToFriendModal');
HTMLHelper::_('behavior.tooltip');
HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('behavior.formvalidation');

$user = Factory::getUser();

if (!$user->guest)
{
	$form->setValue('your_name', null, $form->getValue('your_name', null, $user->name));
	$rsUser = RedshopbHelperUser::getUser();

	if (!$rsUser || $rsUser->use_company_email == 0)
	{
		$form->setValue('your_email', null, $user->email);
	}

	$form->removeField('captcha', null);
	$form->setFieldAttribute('your_email', 'disabled', 'true', null);
	$form->setFieldAttribute('your_email', 'required', 'false', null);
}

?>
<script type="text/javascript" language="javascript">
	sendFriendSubmitButton = function (task) {
		var sendFriendForm = document.getElementById('sendFriendForm');

		if (document.formvalidator.isValid(sendFriendForm)) {
			Joomla.submitform(task, sendFriendForm);
		}
	};
</script>
<form id="sendFriendForm" name="sendFriendForm" method="post" class="form-validate form-vertical"
	  action="<?php echo RedshopbRoute::_('index.php?option=com_redshopb&view=shop&layout=product&id=' . $productId . '&category_id=' . $categoryId . '&collection_id=' . $collectionId, false); ?>">

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
	<input type="hidden" value="" name="task">
	<div class="control-group">
		<div class="controls">
			<input type="submit" class="btn btn-primary validate" onclick="sendFriendSubmitButton('shop.sendToFriend')" value="<?php echo Text::_('JSUBMIT'); ?>"/>
		</div>
	</div>
</form>
