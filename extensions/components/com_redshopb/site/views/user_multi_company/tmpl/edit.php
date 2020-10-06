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

// HTML helpers
HTMLHelper::_('behavior.formvalidation');
HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('vnrbootstrap.tooltip');
HTMLHelper::_('rjquery.chosen', 'select');

// Variables
$action      = RedshopbRoute::_('index.php?option=com_redshopb&view=user_multi_company');
$isNew       = (int) $this->item->id <= 0;
$fromCompany = RedshopbInput::isFromCompany();
$userId      = RedshopbInput::getUserIdForm();
$fromUser    = RedshopbInput::isFromUser();

echo RedshopbBrowserBreadcrumbs::renderBreadcrumbs();
?>
<div class="redshopb-user_multi_company">
	<form action="<?php echo $action; ?>" method="post" name="adminForm" id="adminForm" class="form-validate form-horizontal">
		<div class="row">
			<div class="col-md-6 adapt-inputs">
					<div class="form-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('user_id'); ?>
						</div>
						<div class="controls">
							<?php if ($fromUser && $userId) : ?>
								<input type="hidden" name="jform[user_id]" value="<?php echo $userId; ?>">
								<?php echo RedshopbHelperUser::getName($userId); ?>
							<?php else : ?>
								<?php echo $this->form->getInput('user_id'); ?>
							<?php endif; ?>
						</div>
					</div>
				<div class="form-group">
					<div class="control-label">
						<?php echo $this->form->getLabel('company_id'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('company_id'); ?>
					</div>
				</div>
				<div class="form-group">
					<div class="control-label">
						<?php echo $this->form->getLabel('role_id'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('role_id'); ?>
					</div>
				</div>
				<div class="form-group">
					<div class="control-label">
						<?php echo $this->form->getLabel('state'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('state'); ?>
					</div>
				</div>
			</div>
		</div>

		<!-- hidden fields -->
		<input type="hidden" name="from_company" value="<?php echo $fromCompany; ?>"/>
		<input type="hidden" name="from_user" value="<?php echo $fromUser; ?>"/>
		<input type="hidden" name="option" value="com_redshopb">
		<input type="hidden" name="id" value="<?php echo $this->item->id; ?>">
		<input type="hidden" name="task" value="">
		<?php echo HTMLHelper::_('form.token'); ?>
	</form>
</div>
