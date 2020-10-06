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
use Joomla\CMS\Form\Form;

// HTML helpers
HTMLHelper::_('rjquery.framework');
HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('vnrbootstrap.tooltip');
HTMLHelper::_('rjquery.chosen', 'select');

$action = RedshopbRoute::_('index.php?option=com_redshopb&view=field_value');


echo RedshopbBrowserBreadcrumbs::renderBreadcrumbs();

/** @var Form $form */
$form   = $this->form;
$params = $form->getFieldset('params');

?><div class="redshopb-field">
	<form action="<?php echo $action; ?>" method="post" name="adminForm" id="adminForm"
		  class="form-validate redshopb-field-form form-horizontal" enctype="multipart/form-data">
				<?php foreach ($form->getFieldset('standard') AS $field):
					$backWSValueButton = $this->form->getBackWSValueButton($field->fieldname, $field->group);
					echo $field->renderField(
						array(
							'backWSValueButton' => $backWSValueButton,
							'class' => $backWSValueButton ? 'controlGroupForOverrideField' : ''
						)
					);
				endforeach; ?>

				<?php if (isset($params)):
					foreach ($params AS $field): ?>
						<?php
						if (strtolower($field->type) != 'hidden'):
							if (($field->id == 'jform_params_deleteImage' && $form->getValue('image', 'params'))
								|| $field->id != 'jform_params_deleteImage'):
							?>
							<div class="control-group">
								<div class="control-label">
									<?php echo $field->label; ?>
								</div>
								<div class="controls">
									<?php echo $field->input;

									if ($field->id == 'jform_params_deleteImage'):
									?>
										<img src="<?php
										echo RedshopbHelperThumbnail::originalToResize($form->getValue('image', 'params'), '100', '100', 100, 0, 'field_values');
										?>">
										<?php
									endif;
										?>
								</div>
							</div>
							<?php
							endif;
						else:
								echo $field->input;
						endif;
					endforeach;
				endif; ?>

		<?php if ($this->fromField && $this->fieldId) : ?>
			<input type="hidden" name="jform[field_id]" value="<?php echo $this->fieldId; ?>">
		<?php elseif (!$this->isNew): ?>
			<input type="hidden" name="jform[field_id]" value="<?php echo $this->item->field_id; ?>">
		<?php endif; ?>

		<!-- hidden fields -->
		<input type="hidden" name="option" value="com_redshopb">
		<input type="hidden" name="id" value="<?php echo $this->item->id; ?>" />
		<input type="hidden" name="task" value="">
		<?php echo HTMLHelper::_('form.token'); ?>
	</form>
</div>
