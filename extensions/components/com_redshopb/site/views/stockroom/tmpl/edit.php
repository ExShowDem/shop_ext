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
$action      = RedshopbRoute::_('index.php?option=com_redshopb&view=stockroom');
$isNew       = (int) $this->item->id <= 0;

echo RedshopbBrowserBreadcrumbs::renderBreadcrumbs();
RHelperAsset::load('lib/spectrum/spectrum.min.css', 'com_redshopb');
RHelperAsset::load('lib/spectrum/spectrum.min.js', 'com_redshopb');
?>

<?php if ($companyId && $isNew): ?>
<script type="text/javascript">
	Joomla.submitbutton = function(task)
	{
		if (task == "stockroom.cancel" || document.formvalidator.isValid(document.getElementById("adminForm")))
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

<div class="redshopb-stockroom">
	<form action="<?php echo $action; ?>" method="post" name="adminForm" id="adminForm" class="form-validate form-horizontal">
<?php
	echo HTMLHelper::_('vnrbootstrap.startTabSet', 'stockroom', array('active' => 'general'));
	echo HTMLHelper::_('vnrbootstrap.addTab', 'stockroom', 'general', Text::_('COM_REDSHOPB_GENERAL', true));
?>
		<div class="row">
			<div class="col-md-6">
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
						<?php echo $this->form->getLabel('color'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('color'); ?>
					</div>
				</div>
				<div class="form-group">
					<div class="control-label">
						<?php echo $this->form->getLabel('company_id'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('company_id'); ?>
					</div>
				</div>
				<div class="form-group">
					<div class="control-label">
						<?php echo $this->form->getLabel('ordering'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('ordering'); ?>
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
			<div class="col-md-6">
				<div class="form-group">
					<div class="control-label">
						<?php echo $this->form->getLabel('min_delivery_time'); ?>
					</div>
					<div class="controls">
						<div class="input-append">
							<?php echo $this->form->getInput('min_delivery_time'); ?>
							<span class="add-on">
								<?php echo Text::_('COM_REDSHOPB_STOCKROOM_DELIVERY_TIME_' . strtoupper($this->delivery)) ?>
							</span>
						</div>
					</div>
				</div>
				<div class="form-group">
					<div class="control-label">
						<?php echo $this->form->getLabel('max_delivery_time'); ?>
					</div>
					<div class="controls">
						<div class="input-append">
							<?php echo $this->form->getInput('max_delivery_time'); ?>
							<span class="add-on">
								<?php echo Text::_('COM_REDSHOPB_STOCKROOM_DELIVERY_TIME_' . strtoupper($this->delivery)) ?>
							</span>
						</div>
					</div>
				</div>
				<div class="form-group">
					<div class="control-label">
						<?php echo $this->form->getLabel('stock_lower_level'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('stock_lower_level'); ?>
					</div>
				</div>
				<div class="form-group">
					<div class="control-label">
						<?php echo $this->form->getLabel('stock_upper_level'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('stock_upper_level'); ?>
					</div>
				</div>
				<div class="form-group">
					<div class="control-label">
						<?php echo $this->form->getLabel('pick_up'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('pick_up'); ?>
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<div class="form-group">
					<div class="control-label">
						<?php echo $this->form->getLabel('description'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('description'); ?>
					</div>
				</div>
			</div>
		</div>
<?php
	echo HTMLHelper::_('vnrbootstrap.endTab');
	echo HTMLHelper::_('vnrbootstrap.addTab', 'stockroom', 'address', Text::_('COM_REDSHOPB_ADDRESS_LABEL', true));
?>
		<div class="row">
			<div class="col-md-12">
				<div class="form-group">
					<div class="control-label">
						<?php echo $this->form->getLabel('address_name'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('address_name'); ?>
					</div>
				</div>
				<div class="form-group">
					<div class="control-label">
						<?php echo $this->form->getLabel('address_name2'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('address_name2'); ?>
					</div>
				</div>
				<div class="form-group">
					<div class="control-label">
						<?php echo $this->form->getLabel('address'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('address'); ?>
					</div>
				</div>
				<div class="form-group">
					<div class="control-label">
						<?php echo $this->form->getLabel('address2'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('address2'); ?>
					</div>
				</div>
				<div class="form-group">
					<div class="control-label">
						<?php echo $this->form->getLabel('zip'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('zip'); ?>
					</div>
				</div>
				<div class="form-group">
					<div class="control-label">
						<?php echo $this->form->getLabel('city'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('city'); ?>
					</div>
				</div>
				<div class="form-group">
					<div class="control-label">
						<?php echo $this->form->getLabel('phone'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('phone'); ?>
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
				<div class="form-group stateGroup hide">
					<div class="control-label">
						<?php echo $this->form->getLabel('state_id'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('state_id'); ?>
					</div>
				</div>
			</div>
		</div>
<?php
	echo HTMLHelper::_('vnrbootstrap.endTab');
	echo HTMLHelper::_('vnrbootstrap.endTabSet');
?>
		<!-- hidden fields -->
		<input type="hidden" name="option" value="com_redshopb">
		<input type="hidden" name="id" value="<?php echo $this->item->id; ?>">
		<input type="hidden" name="from_company" value="<?php echo $fromCompany ?>">
		<input type="hidden" name="task" value="">
		<?php echo HTMLHelper::_('form.token'); ?>
	</form>
</div>
