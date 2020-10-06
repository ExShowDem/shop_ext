<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Views
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Uri\Uri;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Session\Session;

HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('rdropdown.init');
HTMLHelper::_('vnrbootstrap.tooltip');
HTMLHelper::_('rjquery.chosen', 'select');

$companyId = $this->item->company_id;

$action = RedshopbRoute::_('index.php?option=com_redshopb&view=shipping_route');
echo RedshopbBrowserBreadcrumbs::renderBreadcrumbs();
?>
<script type="text/javascript">
	function ajaxsetAddresses(element) {
		var filterData = {};
		filterData['company_id'] = jQuery(element).val();
		filterData['<?php echo Session::getFormToken(); ?>'] = 1;

		<?php
		$url = Uri::root() . 'index.php?option=com_redshopb&task=shipping_route.ajaxGetFieldAddresses&id=' . $this->item->id;
		?>

		jQuery.ajax({
			url: '<?php echo $url  ?>',
			data: filterData,
			type: 'POST',
			dataType: 'text',
			beforeSend: function (xhr) {
				jQuery('#addressesIdsBox').addClass('opacity-40');
				jQuery('#addressesIdsBox .spinner').show();
			}
		}).done(function (data) {
			jQuery('#addressesIdsBox .spinner').hide();
			jQuery('#addressesIdsBox').removeClass('opacity-40').html(data);
			jQuery('.hasTooltip').tooltip({"animation": true, "html": true, "placement": "top",
				"selector": false, "title": "", "trigger": "hover focus", "delay": 0, "container": false});
			jQuery('select').chosen({"disable_search_threshold": 10, "allow_single_deselect": true});
			jQuery('#jform_addresses').trigger("liszt:updated");
		});
	}
	(function ($) {
		$(document).ready(function () {
			ajaxsetAddresses($("#jform_company_id"));
		});
	})(jQuery);
</script>
<div class="redshopb-shipping_route">
	<form action="<?php echo $action; ?>" method="post" name="adminForm" id="adminForm"
		  class="form-validate form-horizontal redshopb-shipping_route-form">
			<div class="form-group">
				<div class="control-label">
					<?php echo $this->form->getLabel('company_id'); ?>
				</div>
				<div class="controls">
					<?php if ($companyId) : ?>
						<input type="hidden" id="jform_company_id" name="jform[company_id]" value="<?php echo $companyId; ?>">
							<?php echo RedshopbEntityCompany::getInstance($companyId)->get('name'); ?>
					<?php else : ?>
						<?php echo $this->form->getInput('company_id'); ?>
					<?php endif; ?>
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
				<?php echo $this->form->getLabel('alias'); ?>
			</div>
			<div class="controls">
				<?php echo $this->form->getInput('alias'); ?>
			</div>
		</div>
		<div class="form-group">
			<div class="control-label">
				<?php echo $this->form->getLabel('weekday_1'); ?>
			</div>
			<div class="controls">
				<?php echo $this->form->getInput('weekday_1'); ?>
			</div>
		</div>
		<div class="form-group">
			<div class="control-label">
				<?php echo $this->form->getLabel('weekday_2'); ?>
			</div>
			<div class="controls">
				<?php echo $this->form->getInput('weekday_2'); ?>
			</div>
		</div>
		<div class="form-group">
			<div class="control-label">
				<?php echo $this->form->getLabel('weekday_3'); ?>
			</div>
			<div class="controls">
				<?php echo $this->form->getInput('weekday_3'); ?>
			</div>
		</div>
		<div class="form-group">
			<div class="control-label">
				<?php echo $this->form->getLabel('weekday_4'); ?>
			</div>
			<div class="controls">
				<?php echo $this->form->getInput('weekday_4'); ?>
			</div>
		</div>
		<div class="form-group">
			<div class="control-label">
				<?php echo $this->form->getLabel('weekday_5'); ?>
			</div>
			<div class="controls">
				<?php echo $this->form->getInput('weekday_5'); ?>
			</div>
		</div>
		<div class="form-group">
			<div class="control-label">
				<?php echo $this->form->getLabel('weekday_6'); ?>
			</div>
			<div class="controls">
				<?php echo $this->form->getInput('weekday_6'); ?>
			</div>
		</div>
		<div class="form-group">
			<div class="control-label">
				<?php echo $this->form->getLabel('weekday_7'); ?>
			</div>
			<div class="controls">
				<?php echo $this->form->getInput('weekday_7'); ?>
			</div>
		</div>
		<div class="form-group">
			<div class="control-label">
				<?php echo $this->form->getLabel('addresses'); ?>
			</div>
			<div class="controls" id="addressesIdsBox">
				<div class="spinner pagination-centered" style="display:none;">
					<?php echo HTMLHelper::image('media/com_redshopb/images/ajax-loader.gif', '') ?>
				</div>
			</div>
		</div>
		<div class="form-group">
			<div class="control-label">
				<?php echo $this->form->getLabel('max_delivery_time'); ?>
			</div>
			<div class="controls">
				<?php echo $this->form->getInput('max_delivery_time'); ?>
			</div>
		</div>
		<input type="hidden" name="id" value="<?php echo $this->item->id; ?>">
		<input type="hidden" name="task" value="">
		<?php echo HTMLHelper::_('form.token'); ?>
	</form>
</div>
