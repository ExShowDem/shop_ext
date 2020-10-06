<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;

HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('behavior.formvalidation');
HTMLHelper::_('vnrbootstrap.tooltip');
HTMLHelper::_('rjquery.chosen', 'select');

$formName      = (isset($displayData['formName'])) ? $displayData['formName'] : 'redshopLoginForm';
$returnSuccess = (string) $displayData['returnSuccess'];
$returnFail    = (string) $displayData['returnFail'];
$action        = RedshopbRoute::_('index.php?option=com_redshopb&view=b2buserregister', false);
$form          = $displayData['form'];
$app           = Factory::getApplication();
$data          = $app->getUserState('com_redshopb.edit.b2buserregister.login.data', array());

?>

<form class="form-horizontal redshopb-userlogin-form" action="<?php echo $action ?>" id="<?php echo $formName ?>" name="<?php echo $formName ?>" method="post">
	<?php foreach ($form->getFieldset('credentials') as $field) : ?>
		<?php if (!$field->hidden) : ?>
			<div class="form-group">
				<div class="control-label">
					<?php echo $field->label; ?>
				</div>
				<div class="controls">
					<?php echo $field->input; ?>
				</div>
			</div>
		<?php endif; ?>
	<?php endforeach; ?>

	<?php if (PluginHelper::isEnabled('system', 'remember')) : ?>
		<div  class="form-group">
			<div class="control-label"><label for="remember"><?php echo Text::_('JGLOBAL_REMEMBER_ME') ?></label></div>
			<div class="controls"><input id="remember" type="checkbox"
					<?php echo (isset($data['remember']) && $data['remember']) ? 'checked' : ''; ?> name="remember" class="inputbox" value="yes"/></div>
		</div>
	<?php endif; ?>
	<div class="form-group">
		<div class="controls">
			<button type="submit" class="btn btn-primary">
				<?php echo Text::_('COM_REDSHOPB_LOGIN_FORM_SUBMIT') ?>
			</button>
		</div>
	</div>
	<input type="hidden" name="returnSuccess" value="<?php echo $returnSuccess ?>" />
	<input type="hidden" name="returnFail" value="<?php echo $returnFail ?>" />
	<input type="hidden" name="task" value="b2buserregister.login">
	<?php echo HTMLHelper::_('form.token'); ?>
</form>
