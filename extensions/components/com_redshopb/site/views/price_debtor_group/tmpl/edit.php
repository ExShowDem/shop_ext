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
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Session\Session;

$action = RedshopbRoute::_('index.php?option=com_redshopb&view=price_debtor_group');
$isNew  = (int) $this->item->id <= 0;
$input  = Factory::getApplication()->input;
$tab    = $input->getString('tab');

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
			url: '<?php echo Uri::root(); ?>index.php?option=com_redshopb&task=price_debtor_group.ajaxcompanies',
			cache: false,
			type: 'POST',
			dataType:'html',
			data: {
				'companies': companies,
				'companyid': companyid,
				"<?php echo Session::getFormToken() ?>": 1
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
	<?php
	if ($tab)
	:
	?>
	<script type="text/javascript">
	jQuery(document).ready(function () {

		// Show the corresponding tab
		jQuery('#priceDebtorGroupTabs a[href="#<?php echo $tab ?>"]').tab('show');
	});
	</script>
	<?php
	endif;
	?>
<div class="redshopb-price_debtor_group">
	<ul class="nav nav-tabs" id="priceDebtorGroupTabs">
		<li class="active">
			<a href="#details" data-toggle="tab">
				<?php echo Text::_('COM_REDSHOPB_DETAILS'); ?>
			</a>
		</li>
		<?php if ($this->item->id && $this->paymentsEnabled) : ?>
		<li>
			<a href="#payment_configurations"
				data-toggle="tab"><?php echo Text::_('COM_REDSHOPB_DEBTOR_GROUP_PAYMENT_METHODS') ?></a>
		</li>
		<?php endif; ?>

		<?php if ($this->item->id) : ?>
		<li>
			<a href="#shipping_configurations" data-toggle="tab">
				<?php echo Text::_('COM_REDSHOPB_DEBTOR_GROUP_SHIPPING_METHODS') ?>
			</a>
		</li>
		<?php endif; ?>
	</ul>
	<div class="tab-content">
		<div class="tab-pane active" id="details">
			<form action="<?php echo $action; ?>" method="post" name="adminForm" id="adminForm"
				class="form-validate form-horizontal redshopb-price_debtor_group-form">
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
						<?php echo $this->form->getLabel('show_stock_as'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('show_stock_as'); ?>
					</div>
				</div>
				<div class="form-group">
					<div class="control-label">
						<?php echo $this->form->getLabel('default'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('default'); ?>
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
		<?php if ($this->item->id && $this->paymentsEnabled && !empty($this->paymentConfigurationsModel)) : ?>
		<div class="tab-pane" id="payment_configurations">
		<?php
			echo RedshopbLayoutHelper::render('price_debtor_group.payment_configurations', array(
					'paymentConfigurationId' => $this->item->id,
					'state' => $this->paymentConfigurationsModel->getState(),
					'items' => $this->paymentConfigurationsModel->getItems(),
					'pagination' => $this->paymentConfigurationsPagination,
					'filterForm' => $this->paymentConfigurationsModel->getForm(),
					'activeFilters' => $this->paymentConfigurationsModel->getActiveFilters(),
					'formName' => $this->paymentConfigurationsPagination->get('formName'),
					'showToolbar' => true,
					'action' => RedshopbRoute::_('index.php?option=com_redshopb&view=price_debtor_group&model=payment_configurations'),
					'return' => base64_encode('index.php?option=com_redshopb&view=price_debtor_group&layout=edit&id='
						. $this->item->id . '&tab=payment_configurations&from_price_debtor_group=1'
					)
				)
			);
		?>
		</div>
		<?php endif; ?>

		<?php if ($this->item->id && !empty($this->shippingConfigurationsModel)) : ?>
			<div class="tab-pane" id="shipping_configurations">
				<?php
				echo RedshopbLayoutHelper::render('price_debtor_group.shipping_configurations', array(
						'shippingConfigurationId' => $this->item->id,
						'state' => $this->shippingConfigurationsModel->getState(),
						'items' => $this->shippingConfigurationsModel->getItems(),
						'pagination' => $this->shippingConfigurationsPagination,
						'filterForm' => $this->shippingConfigurationsModel->getForm(),
						'activeFilters' => $this->shippingConfigurationsModel->getActiveFilters(),
						'formName' => $this->shippingConfigurationsPagination->get('formName'),
						'showToolbar' => true,
						'action' => RedshopbRoute::_('index.php?option=com_redshopb&view=price_debtor_group&model=shipping_configurations'),
						'return' => base64_encode('index.php?option=com_redshopb&view=price_debtor_group&layout=edit&id='
							. $this->item->id . '&tab=shipping_configurations&from_price_debtor_group=1'
						)
					)
				);
				?>
			</div>
		<?php endif; ?>
	</div>
</div>
