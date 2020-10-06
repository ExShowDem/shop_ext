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

extract($displayData);
$i = 0;

if ($permission->data):
	$permission->values = isset($permission->selected_values) ? explode(',', $permission->selected_values) : array();
	$manual             = isset($permission->manual) && $permission->manual == 0 ? ' disabled="disabled" ' : '';
	?>
	<h3><?php echo ucfirst($permission->scope) . ' ' . Text::_('JOPTIONS');?></h3>
	<fieldset class="checkboxes">
		<ul class="unstyled list-unstyled">
			<?php foreach ($permission->data as $option):
				$i++;
				$checked = in_array($option->value, $permission->values) ? ' checked="checked" ' : ''; ?>
				<li>
					<input
						type="checkbox"
						class="checkbox"
						id="jform_webservice_permission_item_ids_<?php echo $i;?>"
						name="jform[webservice_permission_item_ids][]"
						value="<?php echo htmlspecialchars($option->value, ENT_COMPAT, 'UTF-8');?>"
						<?php echo $manual;?>
						<?php echo $checked;?>
					/>
					<label for="jform_webservice_permission_item_ids_<?php echo $i;?>"><?php echo $option->text; ?></label>
				</li>
			<?php endforeach;?>
		</ul>
	</fieldset>
<?php else: ?>
	<h3><?php echo Text::_('COM_REDSHOPB_WEBSERVICE_PERMISSION_SCOPE_REQUIRED');?></h3>
	<div class="alert alert-info">
		<button type="button" class="close" data-dismiss="alert">&times;</button>
		<div class="pagination-centered">
			<h3><?php echo Text::_('COM_REDSHOPB_NOTHING_TO_DISPLAY') ?></h3>
		</div>
	</div>
<?php endif;
