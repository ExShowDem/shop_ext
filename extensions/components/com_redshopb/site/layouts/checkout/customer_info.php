<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Views
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Language\Text;

$showTitle = $displayData['showTitle'];

/**
 * @var   RedshopbEntityCompany   $orderCompany
 * @var   stdClass                $orderDepartment
 * @var   stdClass                $orderEmployee
 */
$orderCompany    = $displayData['orderCompany'];
$orderDepartment = $displayData['orderDepartment'];
$orderEmployee   = $displayData['orderEmployee'];

?>
<?php if ($showTitle) : ?>
	<h5><?php echo Text::_('COM_REDSHOPB_SHOP_DELIVERY_CUSTOMER_INFO', true); ?></h5>
<?php endif;?>

<div class="row">
	<div class="col-md-12">
		<div class="form-group">
			<div class="control-label">
				<label class="hasTooltipLabel"
					   for="order-company"
					   data-original-title="<?php echo Text::_('COM_REDSHOPB_COMPANY');?>"
					   aria-invalid="false">
					<?php echo Text::_('COM_REDSHOPB_COMPANY'); ?>:
				</label>
			</div>
			<div class="controls">
				<span id="order-company">
					<?php echo $orderCompany->name; ?>
				</span>
			</div>
		</div>
		<div class="form-group">
			<div class="control-label">
				<label class="hasTooltipLabel"
					   for="order-company"
					   data-original-title="<?php echo Text::_('COM_REDSHOPB_VAT');?>"
					   aria-invalid="false">
					<?php echo Text::_('COM_REDSHOPB_VAT');?>:
				</label>
			</div>
			<div class="controls">
				<span id="order-vat">
					<?php echo $orderCompany->vat_number; ?>
				</span>
			</div>
		</div>

		<?php if (PluginHelper::isEnabled('vanir', 'custom_field_ean')) : ?>
			<?php
			Factory::getApplication()->triggerEvent('AECPrintEANOnOrder', array($orderCompany, &$ean));

			if (!empty($ean)) :
			?>
			<div class="form-group">
				<div class="control-label">
					<label class="hasTooltipLabel"
							for="order-company"
							data-original-title="<?php echo Text::_('PLG_VANIR_CUSTOM_FIELD_EAN_PRINT_EAN');?>"
							aria-invalid="false">
						<?php echo Text::_('PLG_VANIR_CUSTOM_FIELD_EAN_PRINT_EAN');?>:
					</label>
				</div>
				<div class="controls">
					<span id="order-ean">
						<?php echo $ean; ?>
					</span>
				</div>
			</div>
			<?php endif; ?>
		<?php endif; ?>

		<?php if (!empty($orderDepartment)) : ?>
			<div class="form-group">
				<div class="control-label">
					<label class="hasTooltipLabel"
						   for="order-department"
						   data-original-title="<?php echo Text::_('COM_REDSHOPB_DEPARTMENT');?>"
						   aria-invalid="false">
						<?php echo Text::_('COM_REDSHOPB_DEPARTMENT');?>:
					</label>
				</div>
				<div class="controls">
					<span id="order-department">
						<?php echo $orderDepartment->name; ?>
					</span>
				</div>
			</div>
		<?php endif; ?>

		<?php if (!empty($orderEmployee)) : ?>
			<div class="form-group">
				<div class="control-label">
					<label class="hasTooltipLabel"
						   for="order-employee"
						   data-original-title="<?php echo Text::_('COM_REDSHOPB_EMPLOYEE');?>"
						   aria-invalid="false">
						<?php echo Text::_('COM_REDSHOPB_EMPLOYEE');?>:
					</label>
				</div>
				<div class="controls">
					<span id="order-employee">
						<?php echo $orderEmployee->name; ?>
					</span>
				</div>
			</div>
		<?php endif; ?>
	</div>
</div>
