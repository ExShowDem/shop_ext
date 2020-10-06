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
use Joomla\CMS\Plugin\PluginHelper;

HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('behavior.tooltip');
HTMLHelper::_('rjquery.chosen', 'select');
HTMLHelper::_('rbvalidate.framework');

/**
 * Layout variables
 * ========================================
 * @var  object $form       Form object.
 * @var  string $action     Form action.
 * @var  string $return     Return url.
 * @var  string $cancel     Form cancel url.
 * @var  string $returnFail Return fail url (Base64 encode)
 */
extract($displayData);

RHelperAsset::load('redshopb.register.js', 'com_redshopb');

$dispatcher = RFactory::getDispatcher();

$dispatcher->trigger('onVanirPrepareRegistrationForm', array(&$form));

$formName             = isset($formName) ? $formName : 'registerForm';
$returnFail           = isset($returnFail) ? (string) $returnFail : '';
$extraFields          = $form->getFieldset('user_extra_fields');
$companyFields        = $form->getFieldset('company_extra_fields');
$allowCompanyRegister = (boolean) RedshopbEntityConfig::getInstance()->get('allow_company_register');
$isUseBilling         = (boolean) $form->getValue('usebilling');
?>

<script type="text/javascript">
	(function ($) {
		$(document).ready(function ($) {
			redSHOPB.register.isAllowCompanyRegister = <?php echo ($allowCompanyRegister) ? 'true' : 'false'; ?>;
			redSHOPB.register.formId = "<?php echo $formName ?>";
			redSHOPB.register.init();
		})
	})(jQuery)
</script>

<div class="redshopb-user-register">
	<div class="redshopb-userregister">
		<form action="<?php echo $action ?>" method="post" name="<?php echo $formName ?>" id="<?php echo $formName ?>"
			  class="form-jquery-validate redshopb-userregister-form">
			<div class="row-fluid">
				<div class="span6">
					<div class="">
						<h4><?php echo Text::_('COM_REDSHOPB_B2BUSER_ACCOUNT_INFORMATION') ?></h4>
						<?php if ($allowCompanyRegister): ?>
							<div class="control-group">
								<div class="control-label">
									<?php echo $form->getLabel('register_type'); ?>
								</div>
								<div class="controls">
									<?php echo $form->getInput('register_type'); ?>
								</div>
							</div>
							<?php if (RedshopbEntityConfig::getInstance()->get('show_invoice_email_field', 0)) : ?>
								<div class="bussinessDiv hidden">
									<div class="control-group">
										<div class="control-label">
											<?php echo $form->getLabel('invoice_email'); ?>
										</div>
										<div class="controls">
											<?php echo $form->getInput('invoice_email'); ?>
										</div>
									</div>
								</div>
							<?php endif; ?>
						<?php endif; ?>
						<?php
						$registerCompany   = RedshopbEntityCompany::load($form->getValue('company_id'));
						$departmentCompany = RedshopbEntityDepartment::getInstance($form->getValue('department_id'));
						?>
						<?php if (!$registerCompany->get('b2c', 0)): ?>
							<div class="control-group b2c-company-infor">
								<div class="control-label">
									<label><?php echo Text::_('COM_REDSHOPB_COMPANY') ?></label>
								</div>
								<div class="controls">
									<strong><?php echo $registerCompany->get('name'); ?></strong>
								</div>
							</div>
							<?php if ($departmentCompany->isValid()): ?>
								<div class="control-group b2c-department-infor">
									<div class="control-label">
										<label><?php echo Text::_('COM_REDSHOPB_DEPARTMENT') ?></label>
									</div>
									<div class="controls">
										<strong><?php echo $departmentCompany->get('name'); ?></strong>
									</div>
								</div>
							<?php endif; ?>
						<?php endif; ?>
						<div class="control-group">
							<div class="control-label">
								<?php echo $form->getLabel('name1'); ?>
							</div>
							<div class="controls">
								<?php echo $form->getInput('name1'); ?>
							</div>
						</div>
						<div class="control-group">
							<div class="control-label">
								<?php echo $form->getLabel('name2'); ?>
							</div>
							<div class="controls">
								<?php echo $form->getInput('name2'); ?>
							</div>
						</div>
						<div class="control-group">
							<div class="control-label">
								<?php echo $form->getLabel('email'); ?>
							</div>
							<div class="controls">
								<?php echo $form->getInput('email'); ?>
							</div>
						</div>
						<div class="control-group">
							<div class="control-label">
								<?php echo $form->getLabel('username'); ?>
							</div>
							<div class="controls">
								<?php echo $form->getInput('username'); ?>
							</div>
						</div>
						<div class="control-group">
							<div class="control-label">
								<?php echo $form->getLabel('password'); ?>
							</div>
							<div class="controls">
								<?php echo $form->getInput('password'); ?>
							</div>
						</div>
						<div class="control-group">
							<div class="control-label">
								<?php echo $form->getLabel('password2'); ?>
							</div>
							<div class="controls">
								<?php echo $form->getInput('password2'); ?>
							</div>
						</div>
						<?php if (!empty($extraFields)): ?>
							<?php foreach ($extraFields as $field): ?>
								<div class="form-group personalDiv">
									<?php if (!$field->hidden) : ?>
										<div class="control-label">
											<?php echo $field->label; ?>

											<?php if ($field->getAttribute('multiple_values') === "1") : ?>
												<button type="button"
														class="btn btn-success btn-small clear pull-right add-field-row"
														value="<?php echo $field->getAttribute('field_id'); ?>">
													<i class="icon-plus-sign"></i> <?php echo Text::_('COM_REDSHOPB_FIELDS_ADD_NEW_FIELD_DATA_VALUE'); ?>
												</button>
											<?php endif; ?>
										</div>
									<?php endif; ?>
									<div class="controls">
										<?php echo $field->input; ?>
									</div>
								</div>
							<?php endforeach; ?>
						<?php endif; ?>

						<?php if ($allowCompanyRegister && !empty($companyFields)): ?>
							<?php foreach ($companyFields as $field): ?>
								<div class="form-group bussinessDiv hidden">
									<?php if (!$field->hidden) : ?>
										<div class="control-label">
											<?php echo $field->label; ?>

											<?php if ($field->getAttribute('multiple_values') === "1") : ?>
												<button type="button"
														class="btn btn-success btn-small clear pull-right add-field-row"
														value="<?php echo $field->getAttribute('field_id'); ?>">
													<i class="icon-plus-sign"></i> <?php echo Text::_('COM_REDSHOPB_FIELDS_ADD_NEW_FIELD_DATA_VALUE'); ?>
												</button>
											<?php endif; ?>
										</div>
									<?php endif; ?>
									<div class="controls">
										<?php echo $field->input; ?>
									</div>
								</div>
							<?php endforeach; ?>
						<?php endif; ?>
					</div>
				</div>
				<div class="span6">
					<div class="">
						<h4><?php echo Text::_('COM_REDSHOPB_B2BUSER_ADDRESS_BILLING') ?></h4>
						<div class="control-group bussiness-company-name-wrapper hidden">
							<?php
							RFactory::getDispatcher()->trigger('onLookupIntegrationRenderLayout', array(&$lookupLayout));

							echo $lookupLayout;
							?>
							<div class="control-label">
								<?php echo $form->getLabel('business_company_name'); ?>
							</div>
							<div class="controls">
								<?php echo $form->getInput('business_company_name'); ?>
							</div>
						</div>
						<div class="bussinessDiv hidden">
							<div class="control-group">
								<div class="control-label">
									<?php echo $form->getLabel('vat_number'); ?>
								</div>
								<div class="controls">
									<?php echo $form->getInput('vat_number'); ?>
								</div>
							</div>
						</div>
						<?php
						$dispatcher->trigger('AECRegisterPrintFieldAfterVAT', array($form, &$vatField));

						echo $vatField;
						?>
						<div class="control-group">
							<div class="control-label">
								<?php echo $form->getLabel('billing_address'); ?>
							</div>
							<div class="controls">
								<?php echo $form->getInput('billing_address'); ?>
							</div>
						</div>
						<div class="control-group">
							<div class="control-label">
								<?php echo $form->getLabel('billing_address2'); ?>
							</div>
							<div class="controls">
								<?php echo $form->getInput('billing_address2'); ?>
							</div>
						</div>
						<div class="control-group">
							<div class="control-label">
								<?php echo $form->getLabel('billing_zip'); ?>
							</div>
							<div class="controls">
								<?php echo $form->getInput('billing_zip'); ?>
							</div>
						</div>
						<div class="control-group">
							<div class="control-label">
								<?php echo $form->getLabel('billing_city'); ?>
							</div>
							<div class="controls">
								<?php echo $form->getInput('billing_city'); ?>
							</div>
						</div>
						<div class="control-group">
							<div class="control-label">
								<?php echo $form->getLabel('billing_phone'); ?>
							</div>
							<div class="controls">
								<?php echo $form->getInput('billing_phone'); ?>
							</div>
						</div>
						<div class="control-group">
							<div class="control-label">
								<?php echo $form->getLabel('billing_country_id'); ?>
							</div>
							<div class="controls">
								<?php
								$billingCountryForm = $form->getInput('billing_country_id');
								$params             = array(&$billingCountryForm, false);

								$dispatcher = RFactory::getDispatcher();
								PluginHelper::importPlugin('vanir');
								$dispatcher->trigger('post_process_countries', $params);

								echo $billingCountryForm;
								?>
							</div>
						</div>
						<div class="control-group billingStateGroup hide">
							<div class="control-label">
								<?php echo $form->getLabel('billing_state_id'); ?>
							</div>
							<div class="controls">
								<?php echo $form->getInput('billing_state_id'); ?>
							</div>
						</div>
						<hr/>
						<h4><?php echo Text::_('COM_REDSHOPB_B2BUSER_ADDRESS_SHIPPING') ?></h4>
						<label class="checkbox">
							<?php echo $form->getInput('usebilling'); ?>&nbsp;
							<?php echo Text::_('COM_REDSHOPB_B2BUSER_USE_ADDRESS_BILLING_SHIPPING') ?>
						</label>
						<div id="shippingaddress" class="js-registration-shipping-form-wrapper" style="display: none;">
							<div class="control-group">
								<div class="control-label">
									<?php echo $form->getLabel('shipping_name1'); ?>
								</div>
								<div class="controls">
									<?php echo $form->getInput('shipping_name1'); ?>
								</div>
							</div>
							<div class="control-group">
								<div class="control-label">
									<?php echo $form->getLabel('shipping_name2'); ?>
								</div>
								<div class="controls">
									<?php echo $form->getInput('shipping_name2'); ?>
								</div>
							</div>
							<div class="control-group">
								<div class="control-label">
									<?php echo $form->getLabel('shipping_address'); ?>
								</div>
								<div class="controls">
									<?php echo $form->getInput('shipping_address'); ?>
								</div>
							</div>
							<div class="control-group">
								<div class="control-label">
									<?php echo $form->getLabel('shipping_address2'); ?>
								</div>
								<div class="controls">
									<?php echo $form->getInput('shipping_address2'); ?>
								</div>
							</div>
							<div class="control-group">
								<div class="control-label">
									<?php echo $form->getLabel('shipping_zip'); ?>
								</div>
								<div class="controls">
									<?php echo $form->getInput('shipping_zip'); ?>
								</div>
							</div>
							<div class="control-group">
								<div class="control-label">
									<?php echo $form->getLabel('shipping_city'); ?>
								</div>
								<div class="controls">
									<?php echo $form->getInput('shipping_city'); ?>
								</div>
							</div>
							<div class="control-group">
								<div class="control-label">
									<?php echo $form->getLabel('shipping_phone'); ?>
								</div>
								<div class="controls">
									<?php echo $form->getInput('shipping_phone'); ?>
								</div>
							</div>
							<div class="control-group">
								<div class="control-label">
									<?php echo $form->getLabel('shipping_country_id'); ?>
								</div>
								<div class="controls">
									<?php
									$shippingCountryForm = $form->getInput('shipping_country_id');
									$params              = array(&$shippingCountryForm, false);

									$dispatcher = RFactory::getDispatcher();
									PluginHelper::importPlugin('vanir');
									$dispatcher->trigger('post_process_countries', $params);

									echo $shippingCountryForm;
									?>
								</div>
							</div>
							<div class="control-group shippingStateGroup hide">
								<div class="control-label">
									<?php echo $form->getLabel('shipping_state_id'); ?>
								</div>
								<div class="controls">
									<?php echo $form->getInput('shipping_state_id'); ?>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="control-group">
				<div class="controls">
					<button type="button" class="btn btn-primary validate"
							onclick="redSHOPB.register.submitForm('b2buserregister.saveUser')">
						<?php echo Text::_('JREGISTER'); ?>
					</button>
					<a class="btn" href="<?php echo $cancel ?>" title="<?php echo Text::_('JCANCEL'); ?>">
						<?php echo Text::_('JCANCEL'); ?>
					</a>
				</div>
			</div>
			<!-- hidden fields -->
			<input type="hidden" name="option" value="com_redshopb"/>
			<input type="hidden" name="task" value=""/>
			<input type="hidden" name="return" value="<?php echo $return ?>"/>
			<input type="hidden" name="returnFail" value="<?php echo $returnFail ?>"/>
			<input type="hidden" name="role_type_id"
				   value="<?php echo RedshopbHelperRole::getRoleIdByName('Employee with login') ?>"/>
			<?php echo HTMLHelper::_('form.token'); ?>
		</form>
	</div>
</div>
