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
use Joomla\CMS\Component\ComponentHelper;

// HTML helpers
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
$action      = RedshopbRoute::_('index.php?option=com_redshopb&view=department');
$input       = Factory::getApplication()->input;
$tab         = $input->getString('tab');
$companyId   = RedshopbInput::getCompanyIdForm();
$fromCompany = RedshopbInput::isFromCompany();
$isNew       = (int) $this->item->id <= 0;
$config      = ComponentHelper::getComponent('com_redshopb');

$imagePath = RedshopbHelperThumbnail::originalToResize($this->item->image, 150, 80, 100, 0, 'departments');

echo RedshopbBrowserBreadcrumbs::renderBreadcrumbs();
?>
<script type="text/javascript">
	function ajaxsetDepartment(element) {
		var filterData = {};
		filterData['company_id'] = jQuery(element).val();
		if (!filterData['company_id'])
			return;

		var url = '<?php echo Uri::root(); ?>index.php?option=com_redshopb&task=department.ajaxGetFieldDepartment'
			+ '&companyId=' + filterData['company_id'] + '&id=<?php echo $this->item->id ?>';

		<?php if (!empty($this->item->parent_id)) : ?>
		url += '&parentId=' + <?php echo (int) $this->item->parent_id ?>;
		<?php endif; ?>

		jQuery.ajax({
			url: url,
			data: filterData,
			type: 'POST',
			data : {
				"<?php echo Session::getFormToken() ?>": 1
			},
			beforeSend: function (xhr) {
				jQuery('#departmentsSelectBox').addClass('opacity-40').html('<div class="spinner pagination-centered" style="width: 220px;"><?php echo HTMLHelper::image('media/com_redshopb/images/ajax-loader.gif', '');?></div>');
			}
		}).done(function (data) {
				jQuery('#departmentsSelectBox').removeClass('opacity-40').html(data);
				jQuery('.hasTooltip').tooltip({"animation": true, "html": true, "placement": "top",
					"selector": false, "title": "", "trigger": "hover focus", "delay": 0, "container": false});
				jQuery('select').chosen();
			});
	}
	(function ($) {
		$(document).ready(function () {
			ajaxsetDepartment($("#jform_company_id"));
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
<?php if ($this->item->id) : ?>
	<script type="text/javascript">
		var loadedDepartmentTabs = {};
		(function ($) {
			function ajaxDepartmentTabSetup(tabName) {
				$('a[href="#' + tabName + '"]').on('shown', function (e) {

					// Tab already loaded
					if (loadedDepartmentTabs[tabName] == true) {
						return true;
					}

					// Perform the ajax request
					$.ajax({
						url: '<?php echo Uri::root(); ?>index.php?option=com_redshopb&task=department.ajax' + tabName + '&view=department&id=<?php echo $this->item->id ?>&company_id=<?php echo $this->item->company_id ?>',
						type: 'POST',
						data : {
							"<?php echo Session::getFormToken() ?>": 1
						},
						beforeSend: function (xhr) {
							$('.' + tabName + '-content .spinner').show();
							$('#departmentTabs').addClass('opacity-40');
						}
					}).done(function (data) {
							$('.' + tabName + '-content .spinner').hide();
							$('#departmentTabs').removeClass('opacity-40');
							$('.' + tabName + '-content').html(data);
							$('select').chosen();
							$('.chzn-search').hide();
							$('.hasTooltip').tooltip({"animation": true, "html": true, "placement": "top",
								"selector": false, "title": "", "trigger": "hover focus", "delay": 0, "container": false});
							loadedDepartmentTabs[tabName] = true;

							rsbftPhone = 480;
							rsbftTablet = 768;

							switch (tabName) {
								case 'addresses':
									rsbftPhone = 660;
									break;
							}
							initFootableRedshopb();
						});
				})
			}

			$(document).ready(function () {
				ajaxDepartmentTabSetup('users');
				ajaxDepartmentTabSetup('collections');
				ajaxDepartmentTabSetup('addresses');
			});
		})(jQuery);
	</script>
	<?php if ($tab) : ?>
		<script type="text/javascript">
			jQuery(document).ready(function () {

				// Show the corresponding tab
				jQuery('#departmentTabs a[href="#<?php echo $tab ?>"]').tab('show');
			});
		</script>
	<?php endif; ?>
<?php endif; ?>
<div class="redshopb-department">
	<ul class="nav nav-tabs" id="departmentTabs">
		<li class="active">
			<a href="#details" data-toggle="tab">
				<?php echo Text::_('COM_REDSHOPB_DETAILS'); ?>
			</a>
		</li>

		<?php if ($this->item->id) : ?>
			<li>
				<a href="#users" data-toggle="tab">
					<?php echo Text::_('COM_REDSHOPB_USER_LIST_TITLE'); ?>
				</a>
			</li>
		<?php endif; ?>

		<?php if ($this->item->id) : ?>
			<li>
				<a href="#collections" data-toggle="tab">
					<?php echo Text::_('COM_REDSHOPB_COLLECTION_LIST_TITLE'); ?>
				</a>
			</li>
		<?php endif; ?>

		<?php if ($this->item->id) : ?>
			<li>
				<a href="#addresses" data-toggle="tab">
					<?php echo Text::_('COM_REDSHOPB_ADDRESSES_VIEW_DEFAULT_TITLE'); ?>
				</a>
			</li>
		<?php endif; ?>
	</ul>
	<div class="tab-content">
		<div class="tab-pane active" id="details">
			<form action="<?php echo $action; ?>" method="post" name="adminForm" id="adminForm" class="form-validate form-horizontal redshopb-department-form" enctype="multipart/form-data">
				<div class="control-group">
					<div class="control-label">
						<?php echo $this->form->getLabel('department_number'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('department_number'); ?>
					</div>
				</div>
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

				<?php if (!$isNew && $fromCompany && $companyId) : ?>
					<input type="hidden" name="jform[company_id]" value="<?php echo $companyId; ?>">
				<?php else : ?>
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('company_id'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('company_id'); ?>
						</div>
					</div>
				<?php endif; ?>

				<div class="control-group">
					<div class="control-label">
						<?php echo $this->form->getLabel('parent_id'); ?>
					</div>
					<div class="controls" id="departmentsSelectBox">
						<?php echo $this->form->getInput('parent_id'); ?>
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
						<?php echo $this->form->getLabel('country_id'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('country_id'); ?>
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
				<div class="control-group">
					<div class="control-label">
						<?php echo $this->form->getLabel('state'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('state'); ?>
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

						<?php if (!empty($imagePath)) :?>
							<img src="<?php echo $imagePath; ?>" />
						<?php endif;?>
					</div>
				</div>
				<!-- hidden fields -->
				<input type="hidden" name="option" value="com_redshopb">
				<input type="hidden" name="from_company" value="<?php echo $fromCompany ?>">
				<input type="hidden" name="id" value="<?php echo $this->item->id; ?>">
				<input type="hidden" name="task" value="">
				<?php echo HTMLHelper::_('form.token'); ?>
			</form>
		</div>
		<?php if ($this->item->id) : ?>
			<div class="tab-pane" id="users">
				<div class="container-fluid">
					<div class="row-fluid users-content">
						<div class="spinner pagination-centered">
							<?php echo HTMLHelper::image('media/com_redshopb/images/ajax-loader.gif', '') ?>
						</div>
					</div>
				</div>
			</div>
		<?php endif; ?>

		<?php if ($this->item->id) : ?>
			<div class="tab-pane" id="collections">
				<div class="container-fluid">
					<div class="row-fluid collections-content">
						<div class="spinner pagination-centered">
							<?php echo HTMLHelper::image('media/com_redshopb/images/ajax-loader.gif', '') ?>
						</div>
					</div>
				</div>
			</div>
		<?php endif; ?>

		<?php if ($this->item->id) : ?>
			<div class="tab-pane" id="addresses">
				<div class="container-fluid">
					<div class="row-fluid addresses-content">
						<div class="spinner pagination-centered">
							<?php echo HTMLHelper::image('media/com_redshopb/images/ajax-loader.gif', '') ?>
						</div>
					</div>
				</div>
			</div>
		<?php endif; ?>
	</div>
</div>
