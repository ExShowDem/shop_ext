<?php
/**
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Views
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Session\Session;

$action = 'index.php?option=com_redshopb&view=webservice_permission';

// HTML helpers
HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('vnrbootstrap.tooltip');
HTMLHelper::_('rjquery.chosen', 'select');
$isNew = (int) $this->item->id <= 0;
?>
<form action="<?php echo $action; ?>" method="post" name="adminForm" id="adminForm"
	  class="form-validate form-horizontal">
	<div class="form-group">
		<div class="control-label">
			<?php echo $this->form->getLabel('scope'); ?>
		</div>
		<div class="controls">
			<?php echo $this->form->getInput('scope'); ?>
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
			<?php echo $this->form->getLabel('description'); ?>
		</div>
		<div class="controls">
			<?php echo $this->form->getInput('description'); ?>
		</div>
	</div>
	<div class="form-group">
		<div class="control-label">
			<?php echo $this->form->getLabel('manual'); ?>
		</div>
		<div class="controls">
			<?php echo $this->form->getInput('manual'); ?>
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

	<div class="form-group webservice-items">
		<div class="spinner pagination-centered" style="display:none;">
			<?php echo HTMLHelper::image('media/com_redshopb/images/ajax-loader.gif', '') ?>
		</div>
		<div class="webservice-items-content">
		</div>
	</div>

	<!-- hidden fields -->
	<input type="hidden" name="option" value="com_redshopb">
	<input type="hidden" name="id" value="<?php echo $this->item->id; ?>">
	<input type="hidden" name="task" value="">
	<?php echo HTMLHelper::_('form.token'); ?>
</form>
<script type="text/javascript">
	(function ($) {
		$(document).ready(function () {
			loadWebservicePermissionScope();
			jQuery('#jform_scope').change();
		});
	})(jQuery);

	var loadWebservicePermissionScope = function(){
		var $scope = jQuery('#jform_scope');
		var filterData = {};
		filterData['scope'] = $scope.val();
		filterData['webservice_permission_id'] = "<?php echo $this->item->id; ?>";
		filterData['<?php echo Session::getFormToken(); ?>'] = 1;
		jQuery.ajax({
			url: 'index.php?option=com_redshopb&task=webservice_permission.ajaxGetScopeItems',
			data: filterData,
			type: 'POST',
			dataType: 'text',
			beforeSend: function (xhr) {
				jQuery('.webservice-items-content').addClass('opacity-40');
				jQuery('.webservice-items .spinner').show();
			}
		}).done(function (data) {
			jQuery('.webservice-items .spinner').hide();
			jQuery('.webservice-items-content').removeClass('opacity-40').html(data);
			jQuery('.hasTooltip').tooltip({"animation": true, "html": true, "placement": "top",
				"selector": false, "title": "", "trigger": "hover focus", "delay": 0, "container": false});
			jQuery('select').chosen({"disable_search_threshold": 10, "allow_single_deselect": true});
		});
	};
</script>
