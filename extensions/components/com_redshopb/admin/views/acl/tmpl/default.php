<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Templates
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Language\Text;

HTMLHelper::_('behavior.formvalidation');
HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('vnrbootstrap.tooltip');
HTMLHelper::_('rjquery.chosen', 'select');

// If there is only one company, adds slashes to force Javascript interpret it as an array with one element
if (count($this->companies) == 1)
{
	$this->companies[0] = '\'' . $this->companies[0] . '\'';
}

// If there is only one department, adds slashes to force Javascript interpret it as an array with one element
if (count($this->departments) == 1)
{
	$this->departments[0] = '\'' . $this->departments[0] . '\'';
}


$formControl    = $this->form->getFormControl();
$updateACLField = $this->form->getFieldAttribute('acl_rules', 'update_to');
$companies      = implode(',', $this->companies);
$departments    = implode(',', $this->departments);

?>
<script type="text/javascript">
	var companies = new Array(<?php echo $companies ?>);
	var departments = new Array(<?php echo $departments ?>);
	var aclLock = false;
	var currentRecord = 0;
	var progress = 0;
	var allRecords = 0;

	(function ($) {
		$(document).ready(function () {
			<?php if ($this->rebuildACLBase): ?>
			aclRebuildAll();
			<?php else: ?>
			$('.progress-icon').hide();
			<?php endif; ?>
		});

		function companyRebuildACL(id)
		{
			if (aclLock)
			{
				setTimeout(function() { companyRebuildACL(id); },1000);
			}
			else
			{
				aclLock = true;
				$.ajax({
					url: 'index.php?option=com_redshopb&task=acl.ajaxRebuildCompany&id=' + id + '&<?php echo Session::getFormToken() ?>=1',
					cache: false,
					dataType:'json'
				}).always(function (data, textStatus){
					aclLock = false;
					currentRecord++;
					progress = 100*(currentRecord/allRecords);
					$('.main-progress-bar .progress .bar').css('width', progress + '%');
					$('.progress-log').append(data.responseText + '<br />');
					if (progress == 100) {
						$('.progress-icon').hide();
						$('.main-progress-bar .progress').removeClass('active').removeClass('progress-striped');
					}
				});
			}
		}

		function departmentRebuildACL(id)
		{
			if (aclLock)
			{
				setTimeout(function() { departmentRebuildACL(id); },1000);
			}
			else
			{
				aclLock = true;
				$.ajax({
					url: 'index.php?option=com_redshopb&task=acl.ajaxRebuildDepartment&id=' + id + '&<?php echo Session::getFormToken() ?>=1',
					cache: false,
					dataType:'json',
				}).always(function (data, textStatus){
					aclLock = false;
					currentRecord++;
					progress = 100*(currentRecord/allRecords);
					$('.main-progress-bar .progress .bar').css('width', progress + '%');
					$('.progress-log').append(data.responseText + '<br />');
					if (progress == 100) {
						$('.progress-icon').hide();
						$('.main-progress-bar .progress').removeClass('active').removeClass('progress-striped');
					}
				});
			}
		}

		function aclRebuildAll(){
			currentRecord = 0;
			progress = 0;

			$('.progress-log').html('');
			allRecords = companies.length + departments.length;

			if (allRecords)
			{
				$('.main-progress-bar .progress .bar').css('width', progress + '%');
				$('.main-progress-bar .progress').addClass('active');
				companies.forEach(function (ele) {
					companyRebuildACL(ele);
				});
				departments.forEach(function (ele) {
					departmentRebuildACL(ele);
				});
			}
			else
			{
				progress = 100;
				$('.main-progress-bar .progress .bar').css('width', progress + '%');
				$('.progress-icon').hide();
				$('.main-progress-bar .progress').removeClass('active').removeClass('progress-striped');
			}
		}

		var loadedAclTabs = {};
		function ajaxPermissionsTabSetup(tabName) {
			// Tab already loaded
			if (loadedAclTabs[tabName] == true) {
				return true;
			}

			// Perform the ajax request
			$.ajax({
				url: 'index.php?option=com_redshopb&task=acl.ajax' + tabName + '&view=acl&<?php echo Session::getFormToken() ?>=1',
				beforeSend: function (xhr) {
					$('.' + tabName + '-content .spinner').show();
					$('#permissionsTabs').addClass('opacity-40');
				}
			}).done(function (data) {
				$('.' + tabName + '-content .spinner').hide();
				$('#permissionsTabs').removeClass('opacity-40');
				$('.' + tabName + '-content').html(data);
				$('select').chosen();
				$('.chzn-search').hide();
				$('.hasTooltip').tooltip({"animation": true, "html": true, "placement": "top",
					"selector": false, "title": "", "trigger": "hover focus", "delay": 0, "container": false});
				loadedAclTabs[tabName] = true;

				// Auto submit search fields after loading AJAX
				$('.js-enter-submits').enterSubmits();

				if (tabName == 'permissions')
				{
					updateACLSettings_default();
				}
			});
		}

	})(jQuery);

	function updatePermission(groupId, accessId, value) {
		var fullFieldId = '<?php echo $formControl ?>_<?php echo $updateACLField ?>_' + groupId + '_' + accessId;
		var fullFieldName = '<?php echo $formControl ?>[<?php echo $updateACLField ?>][' + groupId + '][' + accessId + ']';
		if (!jQuery('#' + fullFieldId).length) {
			jQuery('<input type="hidden" name="' + fullFieldName + '" id="' + fullFieldId + '" />').appendTo(jQuery('#adminForm'));
		}
		jQuery('#' + fullFieldId).val(value);
	}
</script>
<form method="post" name="adminForm" id="adminForm"
	  class="form-validate form-horizontal">
	<ul class="nav nav-tabs" id="permissionsTabs">
		<li class="active">
			<a href="#progressLog" data-toggle="tab">
				<?php echo Text::_('COM_REDSHOPB_ACL_REBUILD'); ?>
			</a>
		</li>
	</ul>
	<div class="tab-content">
		<div class="tab-pane active" id="progressLog">
			<div class="alert alert-info main-progress-bar">
				<h3><?php echo Text::_('COM_REDSHOPB_ACL_REBUILD_PROGRESS') ?>
					&nbsp;
					<span class="progress-icon">
						<i class="icon-spinner icon-spin icon-large"></i>
					</span>
				</h3>
				<div class="progress progress-striped">
					<div class="bar bar-success" style="width: 0%"></div>
				</div>
			</div>
			<div class="well">
				<h3><?php echo Text::_('COM_REDSHOPB_ACL_REBUILD_PROGRESS_LOG') ?></h3>
				<div class="progress-log"></div>
			</div>
		</div>
	</div>
	<!-- hidden fields -->
	<input type="hidden" name="option" value="com_redshopb">
	<input type="hidden" name="id" value="1">
	<input type="hidden" name="task" value="">
	<?php echo HTMLHelper::_('form.token'); ?>
</form>
