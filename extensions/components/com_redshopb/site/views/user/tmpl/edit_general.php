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
use Joomla\CMS\Plugin\PluginHelper;

$action               = RedshopbRoute::_('index.php?option=com_redshopb&view=user');
$companyId            = RedshopbInput::getCompanyIdForm();
$departmentId         = RedshopbInput::getDepartmentIdForm();
$fromCompany          = RedshopbInput::isFromCompany();
$fromDepartment       = RedshopbInput::isFromDepartment();
$fromNewsletter       = RedshopbInput::isFromField('from_newsletter');
$availableCompanies   = explode(',', RedshopbHelperACL::listAvailableCompanies(Factory::getUser()->id));
$availableDepartments = explode(',', RedshopbHelperACL::listAvailableDepartments(Factory::getUser()->id));
$isNew                = (int) $this->item->id <= 0;
$imagePath            = RedshopbHelperThumbnail::originalToResize($this->item->image, 150, 80, 100, 0, 'users');

$this->form->setValue('name', null, trim($this->item->name1 . ' ' . $this->item->name2));

if (!$fromDepartment)
{
	$departmentId = $this->item->department_id;
}

/** @var RForm $form */
$form = $this->form;
?>
	<div class="redshopb-user-general">
		<div class="container-fluid">
			<div class="row">
				<div class="col-md-6 adapt-inputs">

					<h4>
						<?php echo Text::_('COM_REDSHOPB_USER_INFO', true) ?>
					</h4>

					<div class="form-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('role_type_id'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('role_type_id'); ?>
						</div>
					</div>
					<div class="form-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('employee_number'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('employee_number'); ?>
						</div>
					</div>
					<div class="form-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('name'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('name'); ?>
						</div>
					</div>
					<div class="form-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('printed_name'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('printed_name'); ?>
						</div>
					</div>
					<div class="form-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('name1'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('name1'); ?>
						</div>
					</div>
					<div class="form-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('name2'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('name2'); ?>
						</div>
					</div>
					<div class="form-group" id="username">
						<div class="control-label">
							<?php echo $this->form->getLabel('username'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('username'); ?>
						</div>
					</div>
					<div class="form-group" id="password">
						<div class="control-label">
							<?php echo $this->form->getLabel('password'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('password'); ?>
						</div>
					</div>
					<div class="form-group" id="password2">
						<div class="control-label">
							<?php echo $this->form->getLabel('password2'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('password2'); ?>
						</div>
					</div>
					<div class="form-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('userStatus'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('userStatus'); ?>
						</div>
					</div>
					<div class="form-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('use_company_email'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('use_company_email'); ?>
						</div>
					</div>
					<div class="form-group emailControlGroup">
						<div class="control-label">
							<?php echo $this->form->getLabel('email'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('email'); ?>
						</div>
					</div>
					<div class="form-group emailControlGroup">
						<div class="control-label">
							<?php echo $this->form->getLabel('send_email'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('send_email'); ?>
						</div>
					</div>
					<?php if ($fromCompany && $companyId) : ?>
					<input type="hidden" name="jform[company_id]" value="<?php echo $companyId; ?>">
					<?php elseif (!$fromDepartment || $isNew) : ?>
						<div class="form-group">
							<div class="control-label">
								<?php echo $this->form->getLabel('company_id'); ?>
							</div>
							<div class="controls">
								<?php echo $this->form->getInput('company_id'); ?>
							</div>
						</div>
					<?php if (count($availableCompanies) == 1) : ?>
						<script type="text/javascript">
							jQuery('document').ready(
								function () {
									jQuery('#jform_company_id').val('<?php echo $availableCompanies[0];?>').trigger('liszt:updated');
								}
							);
						</script>
					<?php endif; ?>
					<?php elseif (!$isNew): ?>
					<input type="hidden" name="jform[company_id]" value="<?php echo $this->item->company_id; ?>">
					<?php endif; ?>

					<?php if (($fromDepartment && $departmentId) || (empty($availableDepartments)) || (!empty($availableDepartments) && $availableDepartments[0] == '0')) : ?>
						<input type="hidden" name="jform[department_id]" value="<?php echo $departmentId; ?>">
					<?php else : ?>
						<div class="form-group">
							<div class="control-label">
								<?php echo $this->form->getLabel('department_id'); ?>
							</div>
							<div class="controls">
								<div id="departmentSpinner" class="col-md-3" style="display: none">
									<div class="spinner pagination-centered">
										<?php echo HTMLHelper::image('media/com_redshopb/images/ajax-loader.gif', '') ?>
									</div>
								</div>
								<div id="departmentList">
									<?php echo $this->form->getInput('department_id'); ?>
								</div>
							</div>
						</div>
					<?php endif; ?>

					<div class="form-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('registerDate'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('registerDate'); ?>
						</div>
					</div>

					<?php if ($this->item->image): ?>
						<div class="form-group">
							<div class="control-label">
								<?php echo $this->form->getLabel('deleteImage'); ?>
							</div>
							<div class="controls">
								<?php echo $this->form->getInput('deleteImage'); ?>
							</div>
						</div>
					<?php endif; ?>
					<div class="form-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('imageFileUpload'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('imageFileUpload'); ?>

							<?php if (!empty($imagePath)) : ?>
								<img src="<?php echo $imagePath; ?>"/>
							<?php endif; ?>
						</div>
					</div>

				</div>
				<div class="col-md-6 adapt-inputs">

					<h4>
						<?php echo Text::_('COM_REDSHOPB_ADDRESS_LABEL', true) ?>
					</h4>

					<div id="addressInput">
						<div class="form-group">
							<div class="control-label">
								<?php echo $this->form->getLabel('address_name'); ?>
							</div>
							<div class="controls">
								<?php echo $this->form->getInput('address_name'); ?>
							</div>
						</div>
						<div class="form-group">
							<div class="control-label">
								<?php echo $this->form->getLabel('address'); ?>
							</div>
							<div class="controls">
								<?php echo $this->form->getInput('address'); ?>
							</div>
						</div>
						<div class="form-group">
							<div class="control-label">
								<?php echo $this->form->getLabel('address2'); ?>
							</div>
							<div class="controls">
								<?php echo $this->form->getInput('address2'); ?>
							</div>
						</div>
						<div class="form-group">
							<div class="control-label">
								<?php echo $this->form->getLabel('zip'); ?>
							</div>
							<div class="controls">
								<?php echo $this->form->getInput('zip'); ?>
							</div>
						</div>
						<div class="form-group">
							<div class="control-label">
								<?php echo $this->form->getLabel('city'); ?>
							</div>
							<div class="controls">
								<?php echo $this->form->getInput('city'); ?>
							</div>
						</div>
						<div class="form-group">
							<div class="control-label">
								<?php echo $this->form->getLabel('address_phone'); ?>
							</div>
							<div class="controls">
								<?php echo $this->form->getInput('address_phone'); ?>
							</div>
						</div>
						<div class="form-group">
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
						<div class="form-group stateGroup hide">
							<div class="control-label">
								<?php echo $this->form->getLabel('state_id'); ?>
							</div>
							<div class="controls">
								<?php echo $this->form->getInput('state_id'); ?>
							</div>
						</div>
						<div class="form-group">
							<div class="control-label">
								<?php echo $this->form->getLabel('phone'); ?>
							</div>
							<div class="controls">
								<?php echo $this->form->getInput('phone'); ?>
							</div>
						</div>
						<div class="form-group">
							<div class="control-label">
								<?php echo $this->form->getLabel('cell_phone'); ?>
							</div>
							<div class="controls">
								<?php echo $this->form->getInput('cell_phone'); ?>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<input type="hidden" name="from_company" value="<?php echo $fromCompany ?>"/>
	<input type="hidden" name="from_department" value="<?php echo $fromDepartment ?>"/>
	<input type="hidden" name="from_newsletter" value="<?php echo $fromNewsletter ?>"/>
	<input type="hidden" name="option" value="com_redshopb"/>
	<input type="hidden" name="id" value="<?php echo $this->item->id; ?>"/>
	<input type="hidden" name="task" value=""/>
<?php echo HTMLHelper::_('form.token');
