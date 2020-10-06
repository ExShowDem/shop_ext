<?php
/**
 * @package     Aesir.E-Commerce.Admin
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

$data = $displayData;

// ACL Model
$aclModel = RModel::getAdminInstance('ACL');

// Parameters sent for the layout
$assetId     = $data['options']['asset_id'];
$sectionName = $data['options']['section'];
$inputId     = $data['options']['input_id'];
$inputName   = $data['options']['input_name'];
$groupId     = $data['options']['group_id'];
$groupName   = $data['options']['group_name'];
$activeGroup = $data['options']['active_group'];
$simpleUX    = $data['options']['simple'];

$rules   = $aclModel->getRuleCollection($groupId, $assetId, $sectionName, $simpleUX);
$scripts = array();
?>
<table class="table table-striped">
	<thead>
		<tr>
			<th class="actions" id="actions-th<?php echo $groupId ?>">
				<span class="acl-action">
					<?php echo Text::_('JLIB_RULES_ACTION') ?>
				</span>
			</th>
			<th class="settings" id="settings-th<?php echo $groupId ?>">
				<span class="acl-action">
					<?php echo Text::_('JLIB_RULES_SELECT_SETTING') ?>
				</span>
			</th>
			<th class="scope" id="scope-th<?php echo $groupId ?>">
				<span class="acl-action">
					<?php echo Text::_('COM_REDSHOPB_ACL_SCOPE') ?>
				</span>
			</th>
		</tr>
	</thead>
	<tbody>
		<?php
		foreach ($rules as $rule) :
			$calculatedRule = $rule->granted;

			// Get the actual setting for the action for this company (directly applied to the asset id)
			$assetRule = $rule->granted;

			// Sets value for script (to set in actual to-be-stored ruleset fields)
			$scripts[] = 'updatePermission(' . $groupId . ',' . $rule->access_id . ',' . $assetRule . ');';
		?>
		<tr>
		<td headers="actions-th<?php echo $groupId ?>">
		<label for="<?php echo $inputId ?>_<?php echo $rule->access_name ?>_<?php echo $groupId ?>" class="hasTooltip" title="<?php echo htmlspecialchars(Text::_($rule->access_description), ENT_COMPAT, 'UTF-8') ?>">
			<?php echo Text::_($rule->access_title); ?>
		</label>
		</td>
		<td headers="settings-th<?php echo $groupId ?>">
		<?php
		if ($assetRule >= 0)
				:
			?>
			<select class="input-small" name="<?php echo $inputName ?>[<?php echo $rule->access_id ?>][<?php echo $groupId ?>]" id="<?php echo $inputId ?>_<?php echo $rule->access_id ?>_<?php echo $groupId ?>" title="<?php echo Text::sprintf('JLIB_RULES_SELECT_ALLOW_DENY_GROUP', Text::_($rule->access_title), trim($groupName)) ?>" onchange="updatePermission(<?php echo $groupId ?>,<?php echo $rule->access_id ?>,jQuery(this).val());">
			<option value="1"<?php if ($assetRule) : ?> selected="selected"<?php
							 endif; ?>><?php echo Text::_('JLIB_RULES_ALLOWED') ?></option>

		<option value="0"<?php if (!$assetRule) : ?> selected="selected"<?php
						 endif; ?>><?php echo Text::_('JLIB_RULES_DENIED') ?></option>
			<?php
		else:
				?>
				<label class="hasTooltip" title="<?php echo htmlspecialchars(Text::_('COM_REDSHOPB_ACL_UNDEFINED_DESC'), ENT_COMPAT, 'UTF-8') ?>">
					<?php echo Text::_('COM_REDSHOPB_ACL_UNDEFINED'); ?>
				</label>
				<?php
		endif;
				?>
				</select>
			</td>
			<td headers="scope-th<?php echo $groupId ?>">
				<?php
				echo Text::_('COM_REDSHOPB_ACL_SCOPE_' . ($rule->scope == '' ? 'NONE' : $rule->scope));
				?>
			</td>
		</tr>
		<?php
		endforeach;
		?>
	</tbody>
</table>
<script type="text/javascript">
	function updateACLSettings_<?php echo ($activeGroup ? 'default' : $groupId) ?>() {
		<?php echo implode("\n", $scripts); ?>
	}
</script>
