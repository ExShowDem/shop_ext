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
use Joomla\CMS\Language\Text;

// HTML helpers
HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('vnrbootstrap.tooltip');
HTMLHelper::_('rjquery.chosen', 'select');
HTMLHelper::_('behavior.formvalidator');

$companyId   = RedshopbInput::getCompanyIdForm();
$fromCompany = RedshopbInput::isFromCompany();
$action      = RedshopbRoute::_('index.php?option=com_redshopb&view=stockroom_group');
$isNew       = (int) $this->item->id <= 0;

echo RedshopbBrowserBreadcrumbs::renderBreadcrumbs();
RHelperAsset::load('lib/spectrum/spectrum.min.css', 'com_redshopb');
RHelperAsset::load('lib/spectrum/spectrum.min.js', 'com_redshopb');
HTMLHelper::_('vnrbootstrap.timepicker');
?>

<?php if ($companyId && $isNew): ?>
<script type="text/javascript">
	Joomla.submitbutton = function(task)
	{
		if (task == "stockroom_group.cancel" || document.formvalidator.isValid(document.getElementById("adminForm")))
		{
			<?php echo $this->form->getField('description')->save() ?>
			Joomla.submitform(task, document.getElementById("adminForm"));
		}
	};

	(function($) {
		$(document).ready(function() {
			$('#jform_company_id').val("<?php echo $companyId ?>").trigger("liszt:updated");
		});
	})(jQuery);
</script>
<?php endif; ?>
<script type="text/javascript">
	(function($) {
		$(document).ready(function() {
			$('#jform_color').spectrum({
				color: "<?php echo !empty($this->item->color) ? $this->item->color : '#f9cb9c'; ?>",
				showInput: true,
				className: "full-spectrum",
				showInitial: true,
				showPalette: true,
				showSelectionPalette: true,
				maxSelectionSize: 10,
				preferredFormat: "hex",
				palette: [
					["#000","#444","#666","#999","#ccc","#eee","#f3f3f3","#fff"],
					["#f00","#f90","#ff0","#0f0","#0ff","#00f","#90f","#f0f"],
					["#f4cccc","#fce5cd","#fff2cc","#d9ead3","#d0e0e3","#cfe2f3","#d9d2e9","#ead1dc"],
					["#ea9999","#f9cb9c","#ffe599","#b6d7a8","#a2c4c9","#9fc5e8","#b4a7d6","#d5a6bd"],
					["#e06666","#f6b26b","#ffd966","#93c47d","#76a5af","#6fa8dc","#8e7cc3","#c27ba0"],
					["#c00","#e69138","#f1c232","#6aa84f","#45818e","#3d85c6","#674ea7","#a64d79"],
					["#900","#b45f06","#bf9000","#38761d","#134f5c","#0b5394","#351c75","#741b47"],
					["#600","#783f04","#7f6000","#274e13","#0c343d","#073763","#20124d","#4c1130"]
				]
			});
		});
	})(jQuery);
</script>

<div class="redshopb-stockroom_group">
	<form action="<?php echo $action; ?>" method="post" name="adminForm" id="adminForm" class="form-validate form-horizontal">
		<div class="row-fluid">
			<div class="span6">
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
						<?php echo $this->form->getLabel('color'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('color'); ?>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo $this->form->getLabel('stockrooms'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('stockrooms'); ?>
					</div>
				</div>
				<div>
					<h4 class="hasTooltip" data-original-title="<strong><?php echo Text::_('COM_REDSHOPB_STOCKROOM_GROUP_DEADLINES_LABEL');?></strong><br/><?php echo Text::_('COM_REDSHOPB_STOCKROOM_GROUP_DEADLINES_DESC');?>">
						<?php echo Text::_('COM_REDSHOPB_STOCKROOM_GROUP_DEADLINES_LABEL');?>
					</h4>
					<table class="table">
						<tr>
							<th><?php echo $this->form->getLabel('deadline_weekday_1'); ?></th>
							<th><?php echo $this->form->getLabel('deadline_weekday_2'); ?></th>
							<th><?php echo $this->form->getLabel('deadline_weekday_3'); ?></th>
							<th><?php echo $this->form->getLabel('deadline_weekday_4'); ?></th>
							<th><?php echo $this->form->getLabel('deadline_weekday_5'); ?></th>
						</tr>
						<tr>
							<td><?php echo $this->form->getInput('deadline_weekday_1'); ?></td>
							<td><?php echo $this->form->getInput('deadline_weekday_2'); ?></td>
							<td><?php echo $this->form->getInput('deadline_weekday_3'); ?></td>
							<td><?php echo $this->form->getInput('deadline_weekday_4'); ?></td>
							<td><?php echo $this->form->getInput('deadline_weekday_5'); ?></td>
						</tr>
					</table>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo $this->form->getLabel('ordering'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('ordering'); ?>
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
			</div>
		</div>
		<div class="control-group">
			<div class="control-label">
				<?php echo $this->form->getLabel('description'); ?>
			</div>
			<div class="controls">
				<?php echo $this->form->getInput('description'); ?>
			</div>
		</div>
		<!-- hidden fields -->
		<input type="hidden" name="option" value="com_redshopb">
		<input type="hidden" name="id" value="<?php echo $this->item->id; ?>">
		<input type="hidden" name="task" value="">
		<?php echo HTMLHelper::_('form.token'); ?>
	</form>
</div>
