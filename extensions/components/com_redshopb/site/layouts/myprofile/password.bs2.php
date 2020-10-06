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
use Joomla\CMS\Form\Form;

$action = RedshopbRoute::_('index.php?option=com_redshopb&view=myprofile');

/** @var Form $form */
$form = $displayData['form'];
?>
<div class="row-fluid">
	<div class="span12">
		<h5><?php echo Text::_('COM_REDSHOPB_MYPROFILE_CHANGE_PASSWORD'); ?></h5>
		<form action="<?php echo $action; ?>" method="post" class="form-jquery-validate">

			<?php foreach ($form->getFieldsets() as $group => $fieldset) : ?>
				<?php $fields = $form->getFieldset($group); ?>

				<?php if (count($fields) < 1) : ?>
					<?php continue;?>
				<?php endif;?>
				<fieldset>
					<?php // Iterate through the fields in the set and display them. ?>
					<?php foreach ($fields as $field) : ?>
						<?php if ($field->hidden) : // If the field is hidden, just render input. ?>
							<?php echo $field->input; ?>
							<?php continue;?>
						<?php endif; ?>

						<?php if ($field->fieldname == 'password1'): // Special treatment ?>
							<div class="control-group">
								<div class="control-label">
									<?php echo $field->label; ?>
								</div>
								<div class="controls">
									<?php // Disables auto-complete ?>
									<input type="password" style="display:none">
									<?php echo $field->input; ?>
								</div>
							</div>
							<?php continue;?>
						<?php endif;?>

						<?php echo $form->renderField($field->fieldname);?>
					<?php endforeach;?>
				</fieldset>
			<?php endforeach;?>
			<p>
				<input type="submit" name="editmyprofile" class="validate" value="<?php echo Text::_('COM_REDSHOPB_MYPROFILE_CHANGE_PASSWORD_BUTTON'); ?>"/>
			</p>
			<input type="hidden" value="myprofile.changePassword" name="task">
			<?php echo HTMLHelper::_('form.token'); ?>
		</form>
	</div>
</div>
