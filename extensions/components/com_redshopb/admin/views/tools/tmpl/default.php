<?php
/**
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  View
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

HTMLHelper::_('vnrbootstrap.tooltip');

?>
<script type="text/javascript">
	(function ($) {
		$(document).ready(function () {
			Joomla.submitbutton = function(task)
			{
				if (task == 'tools.redshopbDefaults')
				{
					if (confirm('<?php echo Text::_('COM_REDSHOPB_TOOLS_RESET_CONFIRM', true, true); ?>'))
					{
						Joomla.submitform(task, document.getElementById('adminForm'));
					}
				}
			}
		});

	})(jQuery);
</script>
<form method="post" name="adminForm" id="adminForm"
	  class="form-horizontal">

	<h3><?php echo Text::_('COM_REDSHOPB_TOOLS_EXECUTE_CLI_PATCHES'); ?></h3>
	<table class="table table-striped table-hover footable js-redshopb-footable redshopb-footable toggle-circle-filled" id="feesList">
		<thead>
		<tr>
			<th class="nowrap">
				CLI
			</th>
			<th class="nowrap">
				<?php echo Text::_('COM_REDSHOPB_TOOLS_EXECUTE_PATCH'); ?>
			</th>
		</tr>
		</thead>
		<tbody>
		<?php foreach ($this->cliItems as $i => $item): ?>
			<tr>
				<td>
					<?php echo str_replace('_', ' ', $item); ?>
				</td>
				<td>
					<a class="button btn btn-primary" href="index.php?option=com_redshopb&task=tools.executeCliPatch&cli=<?php echo $item; ?>">
						<?php echo Text::_('COM_REDSHOPB_TOOLS_EXECUTE_PATCH'); ?>
					</a>
				</td>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>

	<h3><?php echo Text::_('COM_REDSHOPB_TOOLS_EXECUTE_PATCHES'); ?></h3>
	<table class="table table-striped table-hover footable js-redshopb-footable redshopb-footable toggle-circle-filled" id="feesList">
		<thead>
		<tr>
			<th class="nowrap">
				<?php echo Text::_('JVERSION'); ?>
			</th>
			<th class="nowrap">
				<?php echo Text::_('COM_REDSHOPB_TOOLS_EXECUTE_PATCH'); ?>
			</th>
		</tr>
		</thead>
		<tbody>
		<?php foreach ($this->items as $i => $item): ?>
			<tr>
				<td>
					<?php echo $item; ?>
				</td>
				<td>
					<a class="button btn btn-primary" href="index.php?option=com_redshopb&task=tools.executePatch&version=<?php echo $item; ?>">
						<?php echo Text::_('COM_REDSHOPB_TOOLS_EXECUTE_PATCH'); ?>
					</a>
				</td>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>

	<!-- hidden fields -->
	<input type="hidden" name="option" value="com_redshopb">
	<input type="hidden" name="task" value="">
	<?php echo HTMLHelper::_('form.token'); ?>
</form>

