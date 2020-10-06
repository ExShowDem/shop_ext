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
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Session\Session;

// HTML helpers
HTMLHelper::_('behavior.formvalidation');
HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('vnrbootstrap.tooltip');
HTMLHelper::_('rjquery.chosen', 'select');
HTMLHelper::_('rsearchtools.main');

RedshopbHtml::loadFooTable();
?>

<script type="text/javascript">
	var rsbftPhone = 0;
	var rsbftTablet = 0;
</script>

<?php
// Variables
$action          = RedshopbRoute::_('index.php?option=com_redshopb&view=company');
$input           = Factory::getApplication()->input;
$tab             = $input->getString('tab');
$parentId        = $this->form->getValue('parent_id');
$fromCompanyView = RedshopbInput::isFromCompany();

$formControl         = $this->form->getFormControl();
$updateACLField      = $this->form->getFieldAttribute('acl_rules', 'update_to');
$config              = RedshopbApp::getConfig();
$displayVendorConfig = true;

if ($config->get('vendor_of_companies', 'parent') != 'parent'
	&& ($this->form->getValue('type') != 'main' || $this->isNew))
{
	$displayVendorConfig = false;
}

$imagePath = RedshopbHelperThumbnail::originalToResize($this->item->image, 150, 80, 100, 0, 'companies');
echo RedshopbBrowserBreadcrumbs::renderBreadcrumbs();
?>
<?php if ($this->item->id) : ?>
	<script type="text/javascript">
		var loadedCompanyTabs = {};
		(function ($) {
			function ajaxCompanyTabSetup(tabName) {
				$('a[href="#' + tabName + '"]').on('shown', function (e) {

					// Tab already loaded
					if (loadedCompanyTabs[tabName] == true) {
						return true;
					}

					// Perform the ajax request
					$.ajax({
						url: '<?php echo Uri::root(); ?>index.php?option=com_redshopb&task=company.ajax' + tabName + '&view=company&id=<?php echo $this->item->id ?>',
						type: 'POST',
						data : {
							"<?php echo Session::getFormToken() ?>": 1
						},
						beforeSend: function (xhr) {
							$('.' + tabName + '-content .spinner').show();
							$('#companyTabs').addClass('opacity-40');
						}
					}).done(function (data) {
							$('.' + tabName + '-content .spinner').hide();
							$('#companyTabs').removeClass('opacity-40');
							$('.' + tabName + '-content').html(data);
							$('select').chosen();
							$('.chzn-search').hide();
							$('.hasTooltip').tooltip({"animation": true, "html": true, "placement": "top",
								"selector": false, "title": "", "trigger": "hover focus", "delay": 0, "container": false});
							loadedCompanyTabs[tabName] = true;

							if (tabName == 'permissions')
							{
								updateACLSettings_default();
							}

							rsbftPhone = 480;
							rsbftTablet = 768;

							switch (tabName) {
								case 'companies':
									rsbftPhone = 768;
									break;
								case 'addresses':
									rsbftPhone = 660;
									break;
							}
							initFootableRedshopb();
						});
				})
			}

			$(document).ready(function () {
				ajaxCompanyTabSetup('users');
				ajaxCompanyTabSetup('companies');
				ajaxCompanyTabSetup('departments');
				ajaxCompanyTabSetup('collections');
				ajaxCompanyTabSetup('permissions');
				ajaxCompanyTabSetup('addresses');
				ajaxCompanyTabSetup('stockrooms');

				<?php if ($this->showSalesPersons):?>
				ajaxCompanyTabSetup('salespersons');
				<?php endif;?>
			});

		})(jQuery);

		function updatePermission(groupId, accessId, value) {
			var fullFieldId = '<?php echo $formControl ?>_<?php echo $updateACLField ?>_' + groupId + '_' + accessId;
			var fullFieldName = '<?php echo $this->form->getFormControl() ?>[<?php echo $updateACLField ?>][' + groupId + '][' + accessId + ']';
			if (!jQuery('#' + fullFieldId).length) {
				jQuery('<input type="hidden" name="' + fullFieldName + '" id="' + fullFieldId + '" />').appendTo(jQuery('#adminForm'));
			}
			jQuery('#' + fullFieldId).val(value);
		}
	</script>
	<?php if ($tab) : ?>
		<script type="text/javascript">
			jQuery(document).ready(function () {

				// Show the corresponding tab
				jQuery('#companyTabs a[href="#<?php echo $tab ?>"]').tab('show');
			});
		</script>
	<?php endif; ?>
<?php
endif;
?>
<script type="text/javascript">
	(function ($) {
		$(document).ready(function () {
			$('#jform_parent_id').on('change', function(){
				setCompanyType($(this));
				updateRequiredFields();
			});

			function updateRequiredFields()
			{
				var type = $('#jform_type').chosen().val();
				var required = '<span class="star">&nbsp;*</span>';

				$("#address input[name^='jform[']").each(function(i, e) {
					var input = $(e);

					if (type == 'end_customer')
					{
						input.removeClass('required');
					}
					else if(input.attr('name') != 'jform[address2]' && input.attr('name') != 'jform[state_id]'
						&& input.attr('name') != 'jform[address_phone]' && input.attr('name').indexOf('jform[aECUnLockedColumns]') == -1)
					{
						input.addClass('required');
					}
					if (input.attr('name') == 'jform[country_id]')
					{
						input.trigger("liszt:updated");
					}
				});
				$("#address label[id^='jform_']").each(function(i, e) {
					var label = $(e);

					if (type == 'end_customer')
					{
						label.removeClass('required');
						label.find('span.star').remove();
					}
					else if(label.attr('for') != 'jform_address2' && label.attr('for') != 'jform_state_id' && label.attr('for') != 'jform_address_phone')
					{
						label.addClass('required');
						if (!label.find('span.star').length) label.append(required);
					}
				});
			}

			function setCompanyType($this){
				var optionClass = $this.find('option:selected').attr('class');

				if (optionClass)
				{
					optionClass = optionClass.split('_');
					var level = optionClass[1];
					var $jformType = $('#jform_type');
					var type = 'end_customer';

					switch (level) {
						case '0':
							type = 'main';
							break;
						case '1':
							type = 'customer';
							break;
					}

					$jformType.find('option:selected').attr('selected', false);
					$jformType.find('option[value="' + type + '"]').attr('selected', 'selected');
					$jformType.trigger("liszt:updated");
				}
			}

			setCompanyType($('#jform_parent_id'));
			updateRequiredFields();

			$('select#jform_site_language').on('change', function(){
				setManualLang($(this));
			});

			function setManualLang($this){
				if ($this.val() == ''){
					$('div.langField_' + $this.attr('id')).removeClass('hide');
				}else{
					$('div.langField_' + $this.attr('id')).addClass('hide');
				}
			}

			setManualLang($('select#jform_site_language'));

			// Set default country
			<?php if ($config->get('default_country_id', 0)) : ?>
			if ($('#jform_country_id').val() == '') {
				$('#jform_country_id').val("<?php echo $config->get('default_country_id') ?>").trigger("liszt:updated");
			}
			<?php endif; ?>

			Joomla.submitbutton = function(task)
			{
				if (task == "company.cancel" || document.formvalidator.isValid(document.getElementById("adminForm")))
				{
					<?php echo $this->form->getField('contact_info')->save() ?>
					Joomla.submitform(task, document.getElementById("adminForm"));
				}
			};
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
<script>
	(function ($) {
		$(document).ready(function ()
		{
			jQuery('#jform_b2c1').on('click', function(event)
			{
				var b2cUrl = jQuery('.b2c_url');

				jQuery('.b2c_url').addClass('hidden');
			});


			if(jQuery('input[name="jform[b2c]"]:checked').val() == 0)
			{
				jQuery('.b2c_url').addClass('hidden');
			}

			$('#jform_b2c0').on('click', function(){
				if(confirm("<?php echo Text::_('COM_REDSHOPB_B2C_CONFIRM_MSG');?>"))
				{
					jQuery('.b2c_url').removeClass('hidden');
					return true;
				}
				else
				{
					var b2c1 = jQuery('#jform_b2c1');
					b2c1.click();

					jQuery('label[for="jform_b2c1"]').toggleClass('active').toggleClass('btn-danger');
					jQuery('label[for="jform_b2c0"]').toggleClass('active').toggleClass('btn-success');
				}
			});
		});
	})(jQuery);

</script>
<div class="redshopb-company">
	<ul class="nav nav-tabs" id="companyTabs">
		<li class="active">
			<a href="#details" data-toggle="tab">
				<?php echo Text::_('COM_REDSHOPB_DETAILS'); ?>
			</a>
		</li>

		<?php if ($this->item->id) : ?>
			<li>
				<a href="#fields" data-toggle="tab">
					<?php echo Text::_('COM_REDSHOPB_FIELDS_TITLE') ?>
				</a>
			</li>
			<li>
				<a href="#users" data-toggle="tab">
					<?php echo Text::_('COM_REDSHOPB_USER_LIST_TITLE'); ?>
				</a>
			</li>
			<li>
				<a href="#companies" data-toggle="tab">
					<?php echo Text::_('COM_REDSHOPB_CUSTOMERS_LABEL'); ?>
				</a>
			</li>
			<li>
				<a href="#departments" data-toggle="tab">
					<?php echo Text::_('COM_REDSHOPB_DEPARTMENT_LIST_TITLE'); ?>
				</a>
			</li>
			<li>
				<a href="#collections" data-toggle="tab">
					<?php echo Text::_('COM_REDSHOPB_COLLECTION_LIST_TITLE'); ?>
				</a>
			</li>
			<li>
				<a href="#permissions" data-toggle="tab">
					<?php echo Text::_('COM_REDSHOPB_PERMISSIONS'); ?>
				</a>
			</li>
			<li>
				<a href="#addresses" data-toggle="tab">
					<?php echo Text::_('COM_REDSHOPB_ADDRESSES_VIEW_DEFAULT_TITLE'); ?>
				</a>
			</li>
			<?php if ($this->showSalesPersons) : ?>
			<li>
				<a href="#salespersons" data-toggle="tab">
					<?php echo Text::_('COM_REDSHOPB_SALES_PERSONS_VIEW_DEFAULT_TITLE'); ?>
				</a>
			</li>
			<?php endif; ?>
			<li>
				<a href="#stockrooms" data-toggle="tab">
					<?php echo Text::_('COM_REDSHOPB_STOCKROOMS_VIEW_DEFAULT_TITLE'); ?>
				</a>
			</li>
		<?php endif; ?>
	</ul>
	<div class="tab-content">
		<div class="tab-pane active" id="details">
			<div class="redshopb-company-form">
				<form action="<?php echo $action; ?>" method="post" name="adminForm" id="adminForm"
					  class="form-horizontal" enctype="multipart/form-data">
						<div class="container-fluid">
							<div class="row-fluid">
								<div class="span6 adapt-inputs">
									<?php if ((!$this->isNew && $config->get('set_webservices', 0)) || !$config->get('set_webservices', 0)) : ?>
									<div class="control-group">
										<div class="control-label">
											<?php echo $this->form->getLabel('customer_number'); ?>
										</div>
										<div class="controls">
											<?php
											if (!$this->isNew) :
												$this->form->setFieldAttribute('customer_number', 'type', 'hidden');
											?>
												<input type="text" disabled="disabled" value="<?php echo $this->item->customer_number; ?>" />
											<?php
											endif;

											echo $this->form->getInput('customer_number');
											?>
										</div>
									</div>
									<?php endif; ?>
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
											<?php echo $this->form->getLabel('requisition'); ?>
										</div>
										<div class="controls">
											<?php echo $this->form->getInput('requisition'); ?>
										</div>
									</div>

									<?php
									if (!$this->isNew && $fromCompanyView && $parentId)
										:
										?>
										<input type="hidden" name="jform[parent_id]" value="<?php echo $parentId; ?>">
									<?php
									else :
										?>
										<div class="control-group">
											<div class="control-label">
												<?php echo $this->form->getLabel('parent_id'); ?>
											</div>
											<div class="controls">
												<?php echo $this->form->getInput('parent_id'); ?>
											</div>
										</div>
									<?php
									endif;
									?>
									<div class="control-group">
										<div class="control-label">
											<?php
											echo $this->form->getLabel('type');
											?>
										</div>
										<div class="controls">
											<?php
											echo $this->form->getInput('type');
											?>
										</div>
									</div>
									<div class="control-group">
										<div class="control-label">
											<?php echo $this->form->getLabel('currency_id'); ?>
										</div>
										<div class="controls">
											<?php echo $this->form->getInput('currency_id'); ?>
										</div>
									</div>
									<?php if ($this->item->type != 'main'): ?>
										<div class="control-group">
											<div class="control-label">
												<?php
												echo $this->form->getLabel('state');
												?>
											</div>
											<div class="controls">
												<?php
												echo $this->form->getInput('state');
												?>
											</div>
										</div>
									<?php else : ?>
										<input type="hidden" name="jform[state]" value="<?php echo $this->item->state; ?>" />
									<?php endif;?>

									<?php if (RedshopbHelperACL::getPermission('manage', 'mainwarehouse', array('edit', 'edit.own'), true)):?>
										<div class="control-group">
											<div class="control-label">
												<?php echo $this->form->getLabel('show_stock_as'); ?>
											</div>
											<div class="controls">
												<?php echo $this->form->getInput('show_stock_as'); ?>
											</div>
										</div>
									<?php endif; ?>
									<div class="control-group">
										<div class="control-label">
											<?php echo $this->form->getLabel('show_retail_price'); ?>
										</div>
										<div class="controls">
											<?php echo $this->form->getInput('show_retail_price'); ?>
										</div>
									</div>
									<div class="control-group">
										<div class="control-label">
											<?php echo $this->form->getLabel('order_approval'); ?>
										</div>
										<div class="controls">
											<?php echo $this->form->getInput('order_approval'); ?>
										</div>
									</div>
									<div class="control-group">
										<div class="control-label">
											<?php echo $this->form->getLabel('use_wallets'); ?>
										</div>
										<div class="controls">
											<?php echo $this->form->getInput('use_wallets'); ?>
										</div>
									</div>
									<div class="control-group">
										<div class="control-label">
											<?php echo $this->form->getLabel('hide_company'); ?>
										</div>
										<div class="controls">
											<?php echo $this->form->getInput('hide_company'); ?>
										</div>
									</div>
									<div class="control-group">
										<div class="control-label">
											<?php echo $this->form->getLabel('site_language'); ?>
										</div>
										<div class="controls">
											<?php echo $this->form->getInput('site_language'); ?>
											<div class="langField_jform_site_language">
												<?php
												$this->form->setValue('site_language_text', null, $this->form->getValue('site_language'));
												echo $this->form->getInput('site_language_text') ?>
											</div>
										</div>
									</div>
									<div class="control-group">
										<div class="control-label">
											<?php echo $this->form->getLabel('calculate_fee'); ?>
										</div>
										<div class="controls">
											<?php echo $this->form->getInput('calculate_fee'); ?>
										</div>
									</div>
									<?php
									if (!$this->isNew
										&& RedshopbHelperACL::getPermission('manage', 'company', array('create'), true)
										&& (RedshopbHelperUser::getUserCompanyId(Factory::getUser()->id, 'joomla') == $this->item->parent_id)
										|| RedshopbHelperACL::isSuperAdmin())
									:
									?>
									<div class="control-group">
										<div class="control-label">
											<?php echo $this->form->getLabel('stockroom_verification'); ?>
										</div>
										<div class="controls">
											<?php echo $this->form->getInput('stockroom_verification'); ?>
										</div>
									</div>
									<?php endif; ?>
								</div>
								<div class="span6 adapt-inputs">
									<div class="control-group">
										<div class="control-label">
											<?php echo $this->form->getLabel('freight_amount_limit'); ?>
										</div>
										<div class="controls">
											<?php echo $this->form->getInput('freight_amount_limit'); ?>
										</div>
									</div>
									<div class="control-group">
										<div class="control-label">
											<?php echo $this->form->getLabel('freight_amount'); ?>
										</div>
										<div class="controls">
											<?php echo $this->form->getInput('freight_amount'); ?>
										</div>
									</div>
									<div class="control-group">
										<div class="control-label">
											<?php echo $this->form->getLabel('freight_product_id'); ?>
										</div>
										<div class="controls">
											<?php echo $this->form->getInput('freight_product_id'); ?>
										</div>
									</div>
									<div class="control-group">
										<div class="control-label">
											<?php echo $this->form->getLabel('wallet_product_id'); ?>
										</div>
										<div class="controls">
											<?php echo $this->form->getInput('wallet_product_id'); ?>
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
									<?php if ($config->get('show_invoice_email_field', 0)) : ?>
										<div class="control-group">
											<div class="control-label">
												<?php echo $this->form->getLabel('invoice_email'); ?>
											</div>
											<div class="controls">
												<?php echo $this->form->getInput('invoice_email'); ?>
											</div>
										</div>
									<?php endif; ?>
									<div class="control-group">
										<div class="control-label">
											<?php echo $this->form->getLabel('employee_mandatory'); ?>
										</div>
										<div class="controls">
											<?php echo $this->form->getInput('employee_mandatory'); ?>
										</div>
									</div>
									<div class="control-group">
										<div class="control-label">
											<?php echo $this->form->getLabel('price_group_ids'); ?>
										</div>
										<div class="controls">
											<?php echo $this->form->getInput('price_group_ids'); ?>
											<?php echo $this->form->getInput('price_group_ids_exists'); ?>
										</div>
									</div>
									<div class="control-group">
										<div class="control-label">
											<?php echo $this->form->getLabel('customer_discount_ids'); ?>
										</div>
										<div class="controls">
											<?php echo $this->form->getInput('customer_discount_ids'); ?>
											<?php echo $this->form->getInput('customer_discount_ids_exists'); ?>
										</div>
									</div>
									<div class="control-group">
										<div class="control-label">
											<?php echo $this->form->getLabel('send_mail_on_order'); ?>
										</div>
										<div class="controls">
											<?php echo $this->form->getInput('send_mail_on_order'); ?>
										</div>
									</div>
									<div class="control-group">
										<div class="control-label">
											<?php echo $this->form->getLabel('b2c'); ?>
										</div>
										<div class="controls">
											<?php echo $this->form->getInput('b2c'); ?>
										</div>
									</div>
									<div class="control-group b2c_url">
										<div class="control-label">
											<?php echo $this->form->getLabel('url'); ?>
										</div>
										<div class="controls">
											<?php echo $this->form->getInput('url'); ?>
										</div>
									</div>
									<div class="control-group">
										<div class="control-label">
											<?php echo $this->form->getLabel('use_collections'); ?>
										</div>
										<div class="controls">
											<?php echo $this->form->getInput('use_collections'); ?>
										</div>
									</div>
									<?php if ($this->item->image): ?>
										<div class="control-group">
											<div class="control-label">
												<?php echo $this->form->getLabel('deleteImage'); ?>
											</div>
											<div class="controls">
												<?php echo $this->form->getInput('deleteImage'); ?>
											</div>
										</div>
									<?php endif; ?>
									<div class="control-group">
										<div class="control-label">
											<?php echo $this->form->getLabel('imageFileUpload'); ?>
										</div>
										<div class="controls">
											<?php echo $this->form->getInput('imageFileUpload'); ?>
											<?php
											if (!empty($imagePath)) :?>
												<img src="<?php echo $imagePath; ?>" />
											<?php endif;?>
										</div>
									</div>
									<div class="control-group">
										<div class="control-label">
											<?php echo $this->form->getLabel('show_price'); ?>
										</div>
										<div class="controls">
											<?php echo $this->form->getInput('show_price'); ?>
										</div>
									</div>
								</div>
							</div>
							<div class="well well-small">
								<h5><?php echo Text::_('COM_REDSHOPB_COMPANY_TAX_CONFIGURATION') ?></h5>
								<div class="row-fluid">
									<div class="span6 adapt-inputs">
										<div class="control-group">
											<div class="control-label">
												<?php echo $this->form->getLabel('vat_number'); ?>
											</div>
											<div class="controls">
												<?php echo $this->form->getInput('vat_number'); ?>
											</div>
										</div>
										<?php if ($displayVendorConfig): ?>
										<div class="control-group">
											<div class="control-label">
												<?php echo $this->form->getLabel('tax_group_id'); ?>
											</div>
											<div class="controls">
												<?php echo $this->form->getInput('tax_group_id'); ?>
											</div>
										</div>
										<div class="control-group">
											<div class="control-label">
												<?php echo $this->form->getLabel('calculate_vat_on'); ?>
											</div>
											<div class="controls">
												<?php echo $this->form->getInput('calculate_vat_on'); ?>
											</div>
										</div>
										<?php endif; ?>
									</div>
									<div class="span6 adapt-inputs">
										<?php
										$userCompanyId = RedshopbHelperUser::getUserCompanyId(Factory::getUser()->id, 'joomla');
										$childrenIds   = RedshopbEntityCompany::getInstance($userCompanyId)->getChildrenIds();

										// Allowed changes just for child companies or for super user
										if (!RedshopbHelperUser::isRoot()
											&& !in_array($this->item->id, $childrenIds)
											&& $config->getInt('customer_tax_exempt', -1) == 1):
											$this->form->setFieldAttribute('tax_exempt', 'disabled', 'true');
										endif; ?>
										<div class="control-group">
											<div class="control-label">
												<?php echo $this->form->getLabel('tax_exempt'); ?>
											</div>
											<div class="controls">
												<?php echo $this->form->getInput('tax_exempt'); ?>
											</div>
										</div>
										<?php if ($displayVendorConfig): ?>
										<div class="control-group">
											<div class="control-label">
												<?php echo $this->form->getLabel('tax_based_on'); ?>
											</div>
											<div class="controls">
												<?php echo $this->form->getInput('tax_based_on'); ?>
											</div>
										</div>
										<?php if ($config->getInt('customer_tax_exempt', -1) == 1): ?>
										<div class="control-group">
											<div class="control-label">
												<?php echo $this->form->getLabel('customer_tax_exempt'); ?>
											</div>
											<div class="controls">
												<?php echo $this->form->getInput('customer_tax_exempt'); ?>
											</div>
										</div>
										<?php endif; ?>
										<?php endif; ?>
									</div>
								</div>
							</div>
							<div class="well well-small">
								<h5><?php echo Text::_('COM_REDSHOPB_ADDRESS_LABEL') ?></h5>
								<div class="row-fluid" id="address">
									<div class="span6 adapt-inputs">
										<div class="control-group">
											<div class="control-label">
												<?php
												echo $this->form->getLabel('address');
												?>
											</div>
											<div class="controls">
												<?php
												echo $this->form->getInput('address');
												?>
											</div>
										</div>
										<div class="control-group">
											<div class="control-label">
												<?php
												echo $this->form->getLabel('address2');
												?>
											</div>
											<div class="controls">
												<?php
												echo $this->form->getInput('address2');
												?>
											</div>
										</div>
										<div class="control-group">
											<div class="control-label">
												<?php
												echo $this->form->getLabel('zip');
												?>
											</div>
											<div class="controls">
												<?php
												echo $this->form->getInput('zip');
												?>
											</div>
										</div>
									</div>
									<div class="span6 adapt-inputs">
										<div class="control-group">
											<div class="control-label">
												<?php
												echo $this->form->getLabel('city');
												?>
											</div>
											<div class="controls">
												<?php
												echo $this->form->getInput('city');
												?>
											</div>
										</div>
										<div class="control-group">
											<div class="control-label">
												<?php
												echo $this->form->getLabel('address_phone');
												?>
											</div>
											<div class="controls">
												<?php
												echo $this->form->getInput('address_phone');
												?>
											</div>
										</div>
										<div class="control-group">
											<div class="control-label">
												<?php
												echo $this->form->getLabel('country_id');
												?>
											</div>
											<div class="controls">
												<?php
												echo $this->form->getInput('country_id');
												?>
											</div>
										</div>
										<div class="control-group stateGroup hide">
											<div class="control-label">
												<?php
												echo $this->form->getLabel('state_id');
												?>
											</div>
											<div class="controls">
												<?php
												echo $this->form->getInput('state_id');
												?>
											</div>
										</div>
									</div>
								</div>
							</div>
							<div class="row-fluid">
								<div class="span12">
									<div class="control-group">
										<div class="control-label">
											<?php echo $this->form->getLabel('contact_info'); ?>
										</div>
										<div class="controls">
											<?php echo $this->form->getInput('contact_info'); ?>
										</div>
									</div>
								</div>
							</div>
						</div>
						<input type="hidden" name="option" value="com_redshopb">
						<input type="hidden" name="from_company" value="<?php echo $fromCompanyView ?>">
						<input type="hidden" name="id" value="<?php echo $this->item->id; ?>">
						<input type="hidden" name="task" value="">
						<?php echo HTMLHelper::_('form.token'); ?>
				</form>
			</div>
		</div>
		<?php if ($this->item->id) : ?>
			<div class="tab-pane" id="fields">
				<?php if ($this->item->id):?>
				<script>
					jQuery(document).ready(function()
					{
						jQuery('[name^="jform[extrafields]"]').attr('form', 'adminForm');
					});
				</script>
				<div class="row-fluid fields-content">
					<div class="span12">
					<?php echo RedshopbLayoutHelper::render('fields.fields',
						array (
							'form' => $this->form,
							'formName' => 'fieldsForm',
							'scope' => 'company',
							'task' => 'company.saveFields',
							'itemId' => $this->item->id,
							'action' => RedshopbRoute::_('index.php?option=com_redshopb&view=company&layout=edit&id=' . $this->item->id),
							'return' => base64_encode('index.php?option=com_redshopb&view=company&layout=edit&tab=fields&id=' . $this->item->id)
						)
					);?>
					</div>
				</div>
				<?php endif;?>
			</div>
			<div class="tab-pane" id="users">
				<div class="container-fluid">
					<div class="row-fluid users-content">
						<div class="spinner pagination-centered">
							<?php echo HTMLHelper::image('media/com_redshopb/images/ajax-loader.gif', '') ?>
						</div>
					</div>
				</div>
			</div>

			<div class="tab-pane" id="companies">
				<div class="container-fluid">
					<div class="row-fluid companies-content">
						<div class="spinner pagination-centered">
							<?php echo HTMLHelper::image('media/com_redshopb/images/ajax-loader.gif', '') ?>
						</div>
					</div>
				</div>
			</div>

			<div class="tab-pane" id="departments">
				<div class="container-fluid">
					<div class="row-fluid departments-content">
						<div class="spinner pagination-centered">
							<?php echo HTMLHelper::image('media/com_redshopb/images/ajax-loader.gif', '') ?>
						</div>
					</div>
				</div>
			</div>

			<div class="tab-pane" id="collections">
				<div class="container-fluid">
					<div class="row-fluid collections-content">
						<div class="spinner pagination-centered">
							<?php echo HTMLHelper::image('media/com_redshopb/images/ajax-loader.gif', '') ?>
						</div>
					</div>
				</div>
			</div>

			<div class="tab-pane" id="permissions">
				<div class="container-fluid">
					<div class="row-fluid permissions-content">
						<div class="spinner pagination-centered">
							<?php echo HTMLHelper::image('media/com_redshopb/images/ajax-loader.gif', '') ?>
						</div>
					</div>
				</div>
			</div>

			<div class="tab-pane" id="addresses">
				<div class="container-fluid">
					<div class="row-fluid addresses-content">
						<div class="spinner pagination-centered">
							<?php echo HTMLHelper::image('media/com_redshopb/images/ajax-loader.gif', '') ?>
						</div>
					</div>
				</div>
			</div>

			<?php if ($this->showSalesPersons) : ?>
			<div class="tab-pane" id="salespersons">
				<div class="container-fluid">
					<div class="row-fluid salespersons-content">
						<div class="spinner pagination-centered">
							<?php echo HTMLHelper::image('media/com_redshopb/images/ajax-loader.gif', '') ?>
						</div>
					</div>
				</div>
			</div>
			<?php endif; ?>

			<div class="tab-pane" id="stockrooms">
				<div class="container-fluid">
					<div class="row-fluid stockrooms-content">
						<div class="spinner pagination-centered">
							<?php echo HTMLHelper::image('media/com_redshopb/images/ajax-loader.gif', '') ?>
						</div>
					</div>
				</div>
			</div>
		<?php endif; ?>
	</div>
</div>
