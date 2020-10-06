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

// HTML helpers
HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('vnrbootstrap.tooltip');
HTMLHelper::_('rjquery.chosen', 'select');

RedshopbHtml::loadFooTable();

$action = RedshopbRoute::_('index.php?option=com_redshopb&view=tax');
$isNew  = (int) $this->item->id <= 0;

echo RedshopbBrowserBreadcrumbs::renderBreadcrumbs();
?>
<script type="text/javascript">
	(function ($) {
		$(document).ready(function () {
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
<div class="redshopb-tax">
	<form action="<?php echo $action; ?>" method="post" name="adminForm" id="adminForm"
		class="form-validate form-horizontal redshopb-tax-form">
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
				<?php echo $this->form->getLabel('tax_rate'); ?>
			</div>
			<div class="controls">
				<?php echo $this->form->getInput('tax_rate'); ?>
			</div>
		</div>
		<div class="form-group">
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
				<?php echo $this->form->getLabel('is_eu_country'); ?>
			</div>
			<div class="controls">
				<?php echo $this->form->getInput('is_eu_country'); ?>
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
		<div class="form-group">
			<div class="control-label">
				<?php echo $this->form->getLabel('tax_groups'); ?>
			</div>
			<div class="controls">
				<?php echo $this->form->getInput('tax_groups'); ?>
			</div>
		</div>
		<!-- hidden fields -->
		<input type="hidden" name="option" value="com_redshopb">
		<input type="hidden" name="id" value="<?php echo $this->item->id; ?>">
		<input type="hidden" name="task" value="">
		<?php echo HTMLHelper::_('form.token'); ?>
	</form>
</div>
