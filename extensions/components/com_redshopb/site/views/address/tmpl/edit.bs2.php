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
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;

// HTML helpers
HTMLHelper::_('behavior.formvalidation');
HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('vnrbootstrap.tooltip');
HTMLHelper::_('rjquery.chosen', 'select');

// Variables
$action         = RedshopbRoute::_('index.php?option=com_redshopb&view=address');
$isNew          = (int) $this->item->id <= 0;
$fromCompany    = RedshopbInput::isFromCompany();
$fromDepartment = RedshopbInput::isFromDepartment();
$fromUser       = RedshopbInput::isFromUser();
$createFlag     = Factory::getApplication()->input->getCmd('create');

// Browse
if (!$isNew)
{
	$this->form->setFieldAttribute('department_customer_id', 'readonly', 'true');
	$this->form->setFieldAttribute('company_customer_id', 'readonly', 'true');
	$this->form->setFieldAttribute('employee_customer_id', 'readonly', 'true');
	$this->form->setFieldAttribute('customer_type', 'readonly', 'true');
}

echo RedshopbBrowserBreadcrumbs::renderBreadcrumbs();
?>
<script type="text/javascript">
	(function ($) {
		$(document).ready(function () {
			$('#adminForm')
				.on('change', '#jform_customer_type', function () {
					$('.customer_id_class').addClass('hide');
					$('.' + $(this).val() + '_customer_id_class').removeClass('hide');
				});
			function checkCountry() {
				var selectedCountryId = $('#jform_country_id').find('option:selected');
				if (selectedCountryId.length){
					if (selectedCountryId.data('has_state') == 1){
						$('.stateGroup').removeClass('hide')
					}else{
						$('.stateGroup').addClass('hide')
					}
				}
			}

			checkCountry();
			$(document).on('change', '#jform_country_id', function () {
				checkCountry();
			});
		});
	})(jQuery);
</script>
<div class="redshopb-address">
	<form action="<?php echo $action; ?>" method="post" name="adminForm" id="adminForm" class="form-validate form-horizontal">
		<div class="row-fluid">
			<div class="span6 adapt-inputs">
				<div class="control-group">
					<div class="control-label">
						<?php echo $this->form->getLabel('name'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('name'); ?>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo $this->form->getLabel('name2'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('name2'); ?>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo $this->form->getLabel('address'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('address'); ?>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo $this->form->getLabel('address2'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('address2'); ?>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo $this->form->getLabel('zip'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('zip'); ?>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo $this->form->getLabel('city'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('city'); ?>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo $this->form->getLabel('email'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('email'); ?>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo $this->form->getLabel('phone'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('phone'); ?>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo $this->form->getLabel('country_id'); ?>
					</div>
					<div class="controls">
						<?php
						$countryForm = $this->form->getInput('country_id');
						$params      = array(&$countryForm, true);

						$dispatcher = RFactory::getDispatcher();
						PluginHelper::importPlugin('vanir');
						$dispatcher->trigger('post_process_countries', $params);

						echo $countryForm;
						?>
					</div>
				</div>
				<div class="control-group stateGroup hide">
					<div class="control-label">
						<?php echo $this->form->getLabel('state_id'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('state_id'); ?>
					</div>
				</div>
				<?php if (!$createFlag): ?>
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('type'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('type'); ?>
						</div>
					</div>
				<?php endif; ?>
			</div>
			<?php
			if ($fromUser) :
				echo $this->form->getInput('customer_type');
				echo $this->form->getInput('employee_customer_id');
			else :
				?>
				<div class="span6 adapt-inputs">
					<?php if (!$isNew || $this->item->type != 2) : ?>
						<div class="control-group">
							<div class="control-label">
								<?php echo $this->form->getLabel('customer_type'); ?>
							</div>
							<div class="controls">
								<?php echo $this->form->getInput('customer_type'); ?>
							</div>
						</div>
					<?php endif; ?>

					<?php if ($isNew || (!$isNew && $this->form->getValue('customer_type') == 'employee')): ?>
						<div class="control-group employee_customer_id_class customer_id_class<?php
						echo ($this->form->getValue('customer_type') != 'employee') ? ' hide' : '';?>">
							<div class="control-label">
								<?php echo $this->form->getLabel('employee_customer_id'); ?>
							</div>
							<div class="controls">
								<?php echo $this->form->getInput('employee_customer_id'); ?>
							</div>
						</div>
					<?php endif; ?>

					<?php if ($isNew || (!$isNew && $this->form->getValue('customer_type') == 'department')): ?>
						<div class="control-group department_customer_id_class customer_id_class<?php
						echo ($this->form->getValue('customer_type') != 'department') ? ' hide' : '';?>">
							<div class="control-label">
								<?php echo $this->form->getLabel('department_customer_id'); ?>
							</div>
							<div class="controls">
								<?php echo $this->form->getInput('department_customer_id'); ?>
							</div>
						</div>
					<?php endif; ?>

					<?php if ($isNew || (!$isNew && $this->form->getValue('customer_type') == 'company')): ?>
						<div class="control-group company_customer_id_class customer_id_class<?php
						echo ($this->form->getValue('customer_type') != 'company') ? ' hide' : '';?>">
							<div class="control-label">
								<?php echo $this->form->getLabel('company_customer_id'); ?>
							</div>
							<div class="controls">
								<?php echo $this->form->getInput('company_customer_id'); ?>
							</div>
						</div>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		</div>

		<!-- hidden fields -->
		<input type="hidden" name="from_company" value="<?php echo $fromCompany; ?>"/>
		<input type="hidden" name="from_department" value="<?php echo $fromDepartment; ?>"/>
		<input type="hidden" name="from_user" value="<?php echo $fromUser; ?>"/>
		<input type="hidden" name="create" value="<?php echo $createFlag; ?>"/>
		<input type="hidden" name="option" value="com_redshopb">
		<input type="hidden" name="id" value="<?php echo $this->item->id; ?>">
		<input type="hidden" name="task" value="">
		<?php echo HTMLHelper::_('form.token'); ?>
	</form>
</div>
