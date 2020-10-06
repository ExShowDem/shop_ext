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

// HTML helpers
HTMLHelper::_('behavior.formvalidation');
HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('vnrbootstrap.tooltip');
HTMLHelper::_('rjquery.chosen', 'select');

// Variables
$action      = RedshopbRoute::_('index.php?option=com_redshopb&view=user_multi_company');
$fromCompany = RedshopbInput::isFromCompany();
$userId      = RedshopbInput::getUserIdForm();
$fromUser    = RedshopbInput::isFromUser();

echo RedshopbBrowserBreadcrumbs::renderBreadcrumbs();
?>
<div class="redshopb-user_select_company">
	<form action="<?php echo $action; ?>" method="post" name="adminForm" id="adminForm" class="form-validate form-horizontal">

		<?php foreach ($this->userCompanies as $userCompany):
			if (!$userCompany->state) :
				continue;
			endif;
			?>
			<div class="user_company-item well span4">
				<h3><?php echo $this->escape($userCompany->company_name); ?></h3>
				<h4><?php echo $this->escape($userCompany->role_name); ?></h4>
				<div class="customer-actions">
					<a
						class="btn btn-primary"
						href="<?php echo RedshopbRoute::_('index.php?option=com_redshopb&task=user_multi_company.select&company_id=' . $userCompany->company_id); ?>"
					>
						<i class="icon-lock"></i>
						<?php echo Text::_('COM_REDSHOPB_SELECT') ?>
					</a>
				</div>
			</div>
		<?php endforeach; ?>

		<!-- hidden fields -->
		<input type="hidden" name="option" value="com_redshopb">
		<input type="hidden" name="task" value="">
		<?php echo HTMLHelper::_('form.token'); ?>
	</form>
</div>
