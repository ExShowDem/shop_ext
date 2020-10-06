<?php
/**
 * @package     Aesir.E-Commerce.Admin
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Language\Text;

$data = $displayData;

// Parameters sent for the layout
$companyId                    = $data['options']['company_id'];
$assetId                      = $data['options']['asset_id'];
$sectionName                  = $data['options']['section'];
$inputId                      = $data['options']['input_id'];
$inputName                    = $data['options']['input_name'];
$simpleUX                     = $data['options']['simple'];
$allowAdministratorRoleChange = !empty($data['options']['allowAdministratorRoleChange'])
	&& $data['options']['allowAdministratorRoleChange'] == 'true';

// Get the available user groups (roles)
$groups        = RedshopbEntityCompany::getInstance($companyId)->getRoles();
?>
<p class="rule-desc">
	<?php echo Text::_('COM_REDSHOPB_COMPANY_PERMISSIONS_DESC') ?>
</p>
<div id="permissions-sliders" class="tabbable tabs-left">
	<ul class="nav nav-tabs">
		<?php
			$i = 0;

		foreach ($groups as $group) :
			if (!$allowAdministratorRoleChange && $group->type == 'admin')
			{
				continue;
			}

			$active = "";

			if (!$i)
			{
				$active = "active";
			}
		?>
		<li class="<?php echo $active ?>">
		<a href="#permission-<?php echo $group->id ?>" data-toggle="tab">
			<?php echo $group->name ?>
		</a>
		</li>
		<?php
		$i++;
		endforeach;
		?>
	</ul>
	<div class="tab-content">
		<?php
			$i = 0;

		foreach ($groups as $group) :
			if (!$allowAdministratorRoleChange && $group->type == 'admin')
			{
				continue;
			}

			$active = false;

			if (!$i)
			{
				$active = true;
			}
			?>
			<div class="tab-pane<?php echo ($active ? ' active' : '') ?>" id="permission-<?php echo $group->id ?>">
		<?php
		if ($active)
		{
			echo RedshopbLayoutHelper::render(
				'acl.grouppermissions',
				array(
					'view' => $data['view'],
					'options' => array (
						'asset_id' => $assetId,
						'section' => $sectionName,
						'input_id' => $inputId,
						'input_name' => $inputName,
						'group_id' => $group->id,
						'group_name' => $group->name,
						'active_group' => $active,
						'simple' => $simpleUX
					)
				),
				'',
				array('client' => 0)
			);
		}
		else
		{
		?>
				<div class="spinner pagination-centered">
					<p>&nbsp;</p>
					<p>&nbsp;</p>
					<p>&nbsp;</p>
					<?php echo HTMLHelper::image('media/com_redshopb/images/ajax-loader.gif', '') ?>
				</div>
		<?php
		}
		?>
		</div>
		<?php

		$i++;
		endforeach;
		?>
	</div>
</div>
<div class="alert alert-info">
	<?php echo Text::_('COM_REDSHOPB_COMPANY_PERMISSIONS_NOTE'); ?>
</div>
<script type="text/javascript">
	var loadedGroupTabs = {};
	(function ($) {
		function ajaxPermissionsTabSetup(groupId, groupName) {
			$('a[href="#permission-' + groupId + '"]').on('shown', function (e) {

				// Tab already loaded
				if (loadedGroupTabs[groupId] == true) {
					return true;
				}

				// Perform the ajax request
				$.ajax({
					url: '<?php echo Uri::root(); ?>index.php?option=com_redshopb&task=acl.ajaxgrouppermissions&view=acl&asset_id=<?php echo $assetId ?>&section_name=<?php echo $sectionName ?>&input_id=<?php echo $inputId ?>&input_name=<?php echo $inputName ?>&group_id=' + groupId + '&group_name=' + groupName + '&simple=<?php echo $simpleUX ?>',
					type: 'POST',
					data : {
						"<?php echo Session::getFormToken() ?>": 1
					},
					beforeSend: function (xhr) {
						$('#permission-' + groupId + ' .spinner').show();
						$('#permissions-sliders').addClass('opacity-40');
					}
				}).done(function (data) {
						$('#permission-' + groupId + ' .spinner').hide();
						$('#permissions-sliders').removeClass('opacity-40');
						$('#permission-' + groupId).html(data);
						$('select').chosen();
						$('.chzn-search').hide();
						$('.hasTooltip').tooltip({"animation": true, "html": true, "placement": "top",
							"selector": false, "title": "", "trigger": "hover focus", "delay": 0, "container": false});
						loadedGroupTabs[groupId] = true;

						// Auto submit search fields after loading AJAX
						$('.js-enter-submits').enterSubmits();

						// sets default permission set in the right fields
						eval('updateACLSettings_' + groupId + '()');
					});
			})
		}

		$(document).ready(function () {
			<?php
				$i = 0;

			foreach ($groups as $group) :
				if (!$allowAdministratorRoleChange && $group->type == 'admin')
				{
					continue;
				}

				if ($i)
				{
			?>
			ajaxPermissionsTabSetup(<?php echo $group->id ?>,'<?php echo $group->name ?>');
			<?php
				}

				$i++;
			endforeach;
			?>
		});
	})(jQuery);
</script>
