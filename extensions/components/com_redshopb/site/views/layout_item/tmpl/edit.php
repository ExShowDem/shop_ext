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

$action = RedshopbRoute::_('index.php?option=com_redshopb&view=layout_item');
HTMLHelper::_('behavior.keepalive');
$input = Factory::getApplication()->input;

echo RedshopbBrowserBreadcrumbs::renderBreadcrumbs();
?>
<div class="redshopb-template">
	<form action="<?php echo $action; ?>" method="post" name="adminForm" id="adminForm" class="form-validate form-horizontal redshopb-layout_item-form">
		<div class="row">
			<div class="col-md-12">
				<div class="form-group">
					<div class="control-label">
						<label id="jform_jform_layoutId-lbl" for="layoutId">
							<?php echo Text::_('COM_REDSHOPB_LAYOUT_ID')?>
						</label>
					</div>
					<div class="controls">
						<span class="badge badge-success">
						<?php echo $this->state->get('layoutId', ''); ?>
						</span>
					</div>
				</div>
				<div class="form-group">
					<div class="control-label">
						<label id="jform_jform_layoutFolder-lbl" for="layoutFolder">
							<?php echo Text::_('COM_REDSHOPB_LAYOUT_FOLDER')?>
						</label>
					</div>
					<div class="controls">
						<span class="badge badge-important">
						<?php echo str_replace('.', DIRECTORY_SEPARATOR, $this->state->get('layoutFolder', '')); ?>
						</span>
					</div>
				</div>
			</div>
		</div>
		<?php echo $this->form->renderField('content'); ?>
		<!-- hidden fields -->
		<input type="hidden" name="option" value="com_redshopb">
		<input type="hidden" name="id" value="<?php echo $input->getString('id') ?>">
		<input type="hidden" name="task" id="formTask" value="">
		<?php echo HTMLHelper::_('form.token'); ?>
	</form>
</div>
