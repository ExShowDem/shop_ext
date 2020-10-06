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

RHelperAsset::load('collection.css', 'com_redshopb');

// HTML helpers
HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('vnrbootstrap.tooltip');
HTMLHelper::_('rdropdown.init');
HTMLHelper::_('rjquery.chosen', 'select');

// HTMLHelper::_('rsearchtools.main');

$url = 'index.php?option=com_redshopb&view=collection';

$return = Factory::getApplication()->input->getBase64('return');

if ($return)
{
	$url .= '&return=' . $return;
}

$action         = RedshopbRoute::_($url);
$companyId      = $this->item->company_id;
$fromCompany    = $this->item->fromCompany;
$departmentId   = reset($this->item->department_ids);
$fromDepartment = $this->item->fromDepartment;
$isNew          = (int) $this->item->id <= 0;
?>
<script type="text/javascript">
	function ajaxsetDepartments(element) {
		var filterData = {};
		filterData['company_id'] = jQuery(element).val();
		if (!filterData['company_id'])
			return;

		/*$("#jform_department_ids").each(function (idx, ele) {
		 if ($(ele).is(':selected'))
		 filterData['department_ids[]'] = $(ele).val();
		 });*/

		filterData['<?php echo Session::getFormToken(); ?>'] = 1;

		<?php
		$url = Uri::root() . 'index.php?option=com_redshopb&task=collection.ajaxGetFieldDepartments&id=' . $this->item->id
			. ($fromDepartment ? '&from_department=1&jform[department_id]=' . $departmentId : '')
			. ($fromCompany ? '&jform[from_company]=1' : '');
		?>

		jQuery.ajax({
			url: '<?php echo $url  ?>',
			data: filterData,
			type: 'POST',
			dataType: 'text',
			beforeSend: function (xhr) {
				jQuery('#departmentIdsBox').addClass('opacity-40');
				jQuery('#departmentIdsBox .spinner').show();
			}
		}).done(function (data) {
				jQuery('#departmentIdsBox .spinner').hide();
				jQuery('#departmentIdsBox').removeClass('opacity-40').html(data);
				jQuery('.hasTooltip').tooltip({"animation": true, "html": true, "placement": "top",
					"selector": false, "title": "", "trigger": "hover focus", "delay": 0, "container": false});
				jQuery('select').chosen({"disable_search_threshold": 10, "allow_single_deselect": true});
				jQuery('#jform_department_id').trigger("liszt:updated");
			});
	}
	(function ($) {
		$(document).ready(function () {
			//$('#jform_company_id').trigger("liszt:updated");
			ajaxsetDepartments($("#jform_company_id"));
		});
	})(jQuery);
</script>
<div class="redshopb-collection-create">
	<!-- Progress bar -->
	<div class="row">
		<div class="progress progress-striped">
			<div class="bar" style="width: 33%;"></div>
		</div>
	</div>

	<div class="row">
		<div class="col-md-offset-2 col-md-10">
			<div class="redshopb-collection-create-form">
				<form action="<?php echo $action; ?>" method="post" name="adminForm" id="adminForm" class="form-validate form-horizontal">
					<div class="row">
						<div class="form-group">
							<div class="control-label">
								<?php echo $this->form->getLabel('name'); ?>
							</div>
							<div class="controls">
								<?php echo $this->form->getInput('name'); ?>
							</div>
						</div>

						<?php if ($fromCompany && $companyId) : ?>
							<input type="hidden" id="jform_company_id" name="jform[company_id]" value="<?php echo $companyId; ?>">
						<?php else : ?>
							<div class="form-group">
								<div class="control-label">
									<?php echo $this->form->getLabel('company_id'); ?>
								</div>
								<div class="controls">
									<?php echo $this->form->getInput('company_id'); ?>
								</div>
							</div>
						<?php endif; ?>

						<div class="form-group">
							<div class="control-label">
								<?php echo $this->form->getLabel('currency_id'); ?>
							</div>
							<div class="controls">
								<?php echo $this->form->getInput('currency_id'); ?>
							</div>
						</div>

						<div class="form-group">
							<div class="control-label">
								<?php echo $this->form->getLabel('department_ids'); ?>
							</div>
							<div class="controls" id="departmentIdsBox">
								<?php if (!empty($this->item->departments)): ?>
									<?php foreach ($this->item->departments as $department): ?>
										<input type="hidden" name="jform[department_ids][]" value="<?php echo $department->id; ?>">
										<?php echo $department->name; ?><br />
									<?php endforeach; ?>
								<?php else: ?>
									<div class="spinner pagination-centered" style="display:none;">
										<?php echo HTMLHelper::image('media/com_redshopb/images/ajax-loader.gif', '') ?>
									</div>
								<?php endif;
?>
								<?php // Echo $this->form->getInput('department_ids'); ?>
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

					<!-- hidden fields -->
					<input type="hidden" name="jform[department_id]" value="<?php echo $departmentId; ?>">
					<input type="hidden" name="from_company" value="<?php echo $fromCompany ?>">
					<input type="hidden" name="from_department" value="<?php echo $fromDepartment ?>">
					<input type="hidden" name="collections" value="<?php echo Factory::getApplication()->input->get('collections', '', 'string') ?>">
					<input type="hidden" name="id" value="<?php echo $this->item->id; ?>">
					<input type="hidden" name="option" value="com_redshopb">
					<input type="hidden" name="layout" value="create">
					<input type="hidden" name="task" value="">
					<?php echo HTMLHelper::_('form.token'); ?>
				</form>
			</div>
		</div>
	</div>
	<hr/>
	<div class="row">
		<div class="pagination-centered">
			<div class="redshopb-collection-create-buttons">
				<button class="btn btn-large btn-danger" onclick="Joomla.submitbutton('collection.cancel')">
					<i class="icon-double-angle-left"></i>
					<?php echo Text::_('JTOOLBAR_CANCEL'); ?>
				</button>
				<button class="btn btn-large btn-success" onclick="Joomla.submitbutton('collection.createNext')">
					<?php echo Text::_('JNEXT'); ?>
					<i class="icon-double-angle-right"></i>
				</button>
			</div>
		</div>
	</div>
</div>
