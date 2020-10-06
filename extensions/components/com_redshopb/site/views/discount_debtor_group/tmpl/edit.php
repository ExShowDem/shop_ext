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
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Session\Session;

$action    = RedshopbRoute::_('index.php?option=com_redshopb&view=discount_debtor_group');
$isNew     = (int) $this->item->id <= 0;
$companies = addslashes(json_encode($this->form->getValue('customer_ids')));

// HTML helpers
HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('vnrbootstrap.tooltip');
HTMLHelper::_('rjquery.chosen', 'select');

echo RedshopbBrowserBreadcrumbs::renderBreadcrumbs();
?>
<script type="text/javascript">
	function getCompanies(companies)
	{
		var companyid = jQuery('#jform_company_id').val();
		jQuery.ajax({
			url: '<?php echo Uri::root(); ?>index.php?option=com_redshopb&task=discount_debtor_group.ajaxcompanies',
			cache: false,
			type: 'POST',
			dataType:'html',
			data: {
				'companyid': companyid,
				'companies': companies,
				"<?php echo Session::getFormToken(); ?>" : 1
			},
			beforeSend: function (xhr) {
				jQuery('#redshopb-companies').html('');
				jQuery('#redshopb-companies-loading').show();
			}
		}).done(function (data) {
			jQuery('#redshopb-companies-loading').hide();
			jQuery('#redshopb-companies').html(data);
			jQuery('select').chosen();
		});
	}

	jQuery(document).ready(function () {
		getCompanies("<?php echo $companies ?>");
	});
</script>
<div class="redshopb-discount_debtor_group">
	<form action="<?php echo $action; ?>" method="post" name="adminForm" id="adminForm"
		class="form-validate form-horizontal redshopb-discount_debtor_group-form">
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
				<?php echo $this->form->getLabel('code'); ?>
			</div>
			<div class="controls">
				<?php echo $this->form->getInput('code'); ?>
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
				<?php echo $this->form->getLabel('customer_ids'); ?>
			</div>
			<div class="controls">
				<?php echo $this->form->getInput('customer_ids'); ?>
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
		<!-- hidden fields -->
		<input type="hidden" name="option" value="com_redshopb">
		<input type="hidden" name="id" value="<?php echo $this->item->id; ?>">
		<input type="hidden" name="task" value="">
		<?php echo HTMLHelper::_('form.token'); ?>
	</form>
</div>
