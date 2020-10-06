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
HTMLHelper::_('rjquery.chosen', 'select',
	array(
		'disable_search_threshold' => 10,
		'allow_single_deselect' => true,
		'search_contains' => true
	)
);
HTMLHelper::_('rsearchtools.main');

RedshopbHtml::loadFooTable();
?>

<script type="text/javascript">
	var rsbftPhone = 0;
	var rsbftTablet = 0;
</script>

<?php
Factory::getLanguage()->load('com_users');
$action         = RedshopbRoute::_('index.php?option=com_redshopb&view=user');
$input          = Factory::getApplication()->input;
$tab            = $input->getString('tab', 'general');
$fromCompany    = RedshopbInput::isFromCompany();
$fromDepartment = RedshopbInput::isFromDepartment();
$isNew          = (int) $this->item->id <= 0;

$roleTypes        = RedshopbHelperRole::getTypeIds();
$rolesWithNoLogin = array();
$useCompanyEmail  = 0;
$document         = Factory::getDocument();
$document->addStyleDeclaration(
	'.well .control-label
	{
		width: 20% !important;
	}
	.well .controls
	{
		margin-left: 30% !important;
	}'
);

foreach ($roleTypes as $roleType)
{
	if ($roleType->allow_access == 0)
	{
		$rolesWithNoLogin[] = $roleType->id;
	}
}

if (!is_null($this->form->getValue('use_company_email')) && $this->form->getValue('use_company_email') == '1')
{
	$useCompanyEmail = 1;
}

// Browse
if ($isNew)
{
	$useCompanyAddress = (int) $this->form->getValue('useCompanyAddress');
}

echo RedshopbBrowserBreadcrumbs::renderBreadcrumbs();
?>
<script type="text/javascript">
	var originalUsername = '';
	var originalPassword = '';

	function jOnRoleChangeSet()
	{
		var rolesWithNoLogin = [<?php echo implode(',', $rolesWithNoLogin); ?>];
		var disabledLogin = rolesWithNoLogin.indexOf(parseInt(jQuery('#jform_role_type_id').val())) != -1;
		var required = '<span class="star">&nbsp;*</span>';
		var username = jQuery('#username');
		var userLabel = username.find('#jform_username-lbl');
		var password = jQuery('#password');
		var passLabel = password.find('#jform_password-lbl');
		var password2 = jQuery('#password2');
		var pass2Label = password2.find('#jform_password2-lbl');

		if (disabledLogin)
		{
			originalUsername = username.val();
			originalPassword = jQuery('#jform_password').val();
			username.hide().find('#jform_username').val('').removeClass('required');
			userLabel.find('span.star').remove();
			password.hide().find('#jform_password').val('').removeClass('required');
			passLabel.find('span.star').remove();
			password2.hide().find('#jform_password2').val('').removeClass('required');
			pass2Label.find('span.star').remove();
		}
		else
		{
			username.show().find('#jform_username').val(originalUsername).addClass('required');
			if (!userLabel.find('span.star').length) userLabel.append(required);
			password.show().find('#jform_password').val(originalPassword).addClass('required');
			if (!passLabel.find('span.star').length) passLabel.append(required);
			password2.show().find('#jform_password2').val(originalPassword).addClass('required');
			if (!pass2Label.find('span.star').length) pass2Label.append(required);
		}
	}

	function jOnCompanyEmailSet(radioSet)
	{
		var display = 'block';

		// If use company email, then need hide 'email' field
		if (radioSet){
			display = 'none';
		}

		jQuery('.emailControlGroup').css({'display' : display});
	}

	function jOnCompanyAddressSet(radioSet)
	{
		var address = jQuery('#address');
		var disable = false;
		var required = '<span class="star">&nbsp;*</span>';
		var requiredFields = ['jform_address', 'jform_city', 'jform_country_id', 'jform_zip'];
		if (radioSet)
		{
			disable = true;
		}
		jQuery("#addressInput input[name^='jform[']").each(function(i, e) {
			var input = jQuery(e);
			input.prop('disabled', disable);
			if (disable)
			{
				input.val('');
				input.removeClass('required');
			}
			else if (jQuery.inArray(input.attr('id'), requiredFields) > -1)
			{
				input.addClass('required');
			}
			if (input.attr('name') == 'jform[country_id]')
			{
				input.trigger("liszt:updated");
			}
		});
		jQuery("#addressInput label[id^='jform_']").each(function(i, e) {
			var label = jQuery(e);
			if (disable)
			{
				label.removeClass('required');
				label.find('span.star').remove();
			}
			else if (jQuery.inArray(label.attr('for'), requiredFields) > -1)
			{
				label.addClass('required');
				if (!label.find('span.star').length) label.append(required);
			}
		});
	}
	function jOnCompanySet(company)
	{
		var departmentList = jQuery('#departmentList');
		var departmentSpinner = jQuery('#departmentSpinner');

		var departmentField = jQuery('#jform_department_id');
		var userIdField = jQuery('input[name="id"]');

		jQuery.ajax({
			url: '<?php echo Uri::root(); ?>index.php?option=com_redshopb&task=user.ajaxGetDepartments&companyId=' + company.val(),
			type: 'POST',
			data : {
				"<?php echo Session::getFormToken() ?>": 1,
				"fieldName": departmentField.attr('name'),
				"fieldId": departmentField.attr('id'),
				"userId": userIdField.val()
			},
			beforeSend: function (xhr)
			{
				departmentList.empty();
				departmentSpinner.show();
			}
		}).done(function(data)
		{
			departmentList.html(data).ready(function () {
				jQuery('#jform_department_id').chosen({
					disable_search_threshold : 10,
					allow_single_deselect : true
				});
			}).trigger('liszt:updated');
			departmentSpinner.hide();
		});
	}

</script>
<?php if ($this->item->id) : ?>
	<script type="text/javascript">
		var loadedUserTabs = {};
		(function ($) {
			function ajaxUserTabSetup(tabName) {
				$('a[href="#' + tabName + '"]').on('click', function (e) {

					// Tab already loaded
					if (loadedUserTabs[tabName] == true) {
						return true;
					}

					var tabNameFixed;

					switch(tabName) {
						case 'multi_company':
							tabNameFixed = 'MultiCompany';
							break;
						default:
							tabNameFixed = tabName;
					}

					// Perform the ajax request
					$.ajax({
						url: '<?php echo Uri::root(); ?>index.php?option=com_redshopb&task=user.ajax' + tabNameFixed + '&view=user&id=<?php echo $this->item->id; ?>',
						type: 'POST',
						data : {
							"<?php echo Session::getFormToken() ?>": 1
						},
						beforeSend: function (xhr) {
							$('.' + tabName + '-content .spinner').show();
							$('#userTabs').addClass('opacity-40');
						}
					}).done(function (data) {
						$('.' + tabName + '-content .spinner').hide();
						$('#userTabs').removeClass('opacity-40');
						$('.' + tabName + '-content').html(data);
						$('select').chosen();
						$('.chzn-search').hide();
						$('.hasTooltip').tooltip({"animation": true, "html": true, "placement": "top",
							"selector": false, "title": "", "trigger": "hover focus", "delay": 0, "container": false});
						loadedUserTabs[tabName] = true;

						// init footable for the tab
						rsbftPhone = 480;
						rsbftTablet = 768;

						switch (tabName) {
							case 'addresses':
								rsbftPhone = 768;
								break;
						}

						initFootableRedshopb();
					});
				})
			}

			$(document).ready(function () {
				ajaxUserTabSetup('orders');
				ajaxUserTabSetup('addresses');
				ajaxUserTabSetup('multicompany');
			});
		})(jQuery);
	</script>
<?php endif; ?>
<script type="text/javascript">
	jQuery(document).ready(function () {
		<?php if ($tab) : ?>
		// Show the corresponding tab
		jQuery('#userTabs a[href="#<?php echo $tab ?>"]').tab('show');
		<?php endif; ?>
		function checkCountry() {
			var selectedCountryId = jQuery('#jform_country_id').find('option:selected');
			if (selectedCountryId.length){
				if (selectedCountryId.data('has_state') == 1){
					jQuery('.stateGroup').removeClass('hide')
				}else{
					jQuery('.stateGroup').addClass('hide')
				}
			}
		}

		checkCountry();
		jQuery(document).on('change', '#jform_country_id', function () {
			checkCountry();
		});

		originalUsername = jQuery('#jform_username').val();
		originalPassword = jQuery('#jform_password').val();
		jOnRoleChangeSet();
		jOnCompanyEmailSet(<?php echo $useCompanyEmail; ?>);

		<?php if ($isNew) : ?>
		jQuery('label[for=jform_useCompanyAddress<?php echo $useCompanyAddress; ?>]').trigger('click');
		<?php endif; ?>
	});
</script>
<div class="redshopb-user">
	<form action="<?php echo $action; ?>" method="post" name="adminForm" id="adminForm" class="form-validate form-horizontal redshopb-user-form" enctype="multipart/form-data">

		<?php
		echo HTMLHelper::_('vnrbootstrap.startTabSet', 'user', array('active' => 'general'));

		echo HTMLHelper::_('vnrbootstrap.addTab', 'user', 'general', Text::_('COM_REDSHOPB_GENERAL', true));
		echo $this->loadTemplate('general');
		echo HTMLHelper::_('vnrbootstrap.endTab');

		if (!$this->isNew || $this->anyRequired)
		{
			echo HTMLHelper::_('vnrbootstrap.addTab', 'user', 'fields', Text::_('COM_REDSHOPB_FIELDS_TITLE', true));
			echo $this->loadTemplate('fields');
			echo HTMLHelper::_('vnrbootstrap.endTab');
		}

		if (!$this->isNew)
		{
			if (!is_null($this->wallet))
			{
				echo HTMLHelper::_('vnrbootstrap.addTab', 'user', 'wallet', Text::_('COM_REDSHOPB_USER_WALLET', true));
				echo $this->loadTemplate('wallet');
				echo HTMLHelper::_('vnrbootstrap.endTab');
			}

			echo HTMLHelper::_('vnrbootstrap.addTab', 'user', 'orders', Text::_('COM_REDSHOPB_ORDER_LIST_TITLE', true));
			?>
			<div class="container-fluid">
				<div class="row-fluid orders-content">
					<div class="spinner pagination-centered">
						<?php echo HTMLHelper::image('media/com_redshopb/images/ajax-loader.gif', '') ?>
					</div>
				</div>
			</div>
			<?php
			echo HTMLHelper::_('vnrbootstrap.endTab');
			echo HTMLHelper::_('vnrbootstrap.addTab', 'user', 'addresses', Text::_('COM_REDSHOPB_ADDRESSES_VIEW_DEFAULT_TITLE', true));
			?>
			<div class="container-fluid">
				<div class="row-fluid addresses-content">
					<div class="spinner pagination-centered">
						<?php echo HTMLHelper::image('media/com_redshopb/images/ajax-loader.gif', '') ?>
					</div>
				</div>
			</div>
			<?php
			echo HTMLHelper::_('vnrbootstrap.endTab');

			if (RedshopbHelperACL::isSuperAdmin())
			{
				echo HTMLHelper::_('vnrbootstrap.addTab', 'user', 'joomla_usergroups', Text::_('COM_REDSHOPB_USER_JOOMLA_USERGROUPS', true));
				echo $this->loadTemplate('joomla_usergroups');
				echo HTMLHelper::_('vnrbootstrap.endTab');
			}

			echo HTMLHelper::_('vnrbootstrap.addTab', 'user', 'multicompany', Text::_('COM_REDSHOPB_USER_MULTI_COMPANY', true));
			?>
			<div class="container-fluid">
				<div class="row-fluid multicompany-content">
					<div class="spinner pagination-centered">
						<?php echo HTMLHelper::image('media/com_redshopb/images/ajax-loader.gif', '') ?>
					</div>
				</div>
			</div>
			<?php
			echo HTMLHelper::_('vnrbootstrap.endTab');
		}

		echo HTMLHelper::_('vnrbootstrap.endTabSet');
		?>
	</form>
</div>
