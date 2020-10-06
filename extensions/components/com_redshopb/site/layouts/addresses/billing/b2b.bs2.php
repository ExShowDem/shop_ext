<?php
/**
 * @package     Aesir.E-Commerce.Admin
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;

/**
 * Layout vairables
 *
 * @var   boolean                    $isNew
 * @var   RedshopbEntityCompany      $company
 * @var   RedshopbEntityDepartment   $department
 * @var   RedshopbEntityUser         $employee
 */
extract($displayData);

$app            = Factory::getApplication();
$customerType   = $app->getUserState('shop.customer_type');
$config         = RedshopbEntityConfig::getInstance();
$companyAddress = $company->getAddress();

switch ($customerType)
{
	case 'department':
		$pTagIdForName = 'order-department';
		$name          = isset($department->name) ? $department->name : '';
		$address       = $department->getBillingAddress();
		break;
	case 'employee':
		$pTagIdForName = 'order-company';
		$name          = isset($company->name) ? $company->name : '';
		$address       = $employee->getBillingAddress();
		break;
	default:
		$pTagIdForName = 'order-company';
		$name          = isset($company->name) ? $company->name : '';
		$address       = $company->getBillingAddress();
}

?>
<?php if (!$isNew) : ?>
	<div id="billing-id" class="hidden"><?php echo $address->get('id'); ?></div>
<?php endif; ?>
<div class="form-group">
	<div class="controls">
		<p id="<?php echo $pTagIdForName; ?>">
			<?php echo $name; ?>
		</p>
	</div>

	<div class="controls">
		<p id="order-address">
			<?php echo $address->address; ?>
		</p>
	</div>

	<?php if (isset($address->address2)) : ?>
		<div class="controls">
			<p id="order-address2">
				<?php echo $address->address2; ?>
			</p>
		</div>
	<?php endif; ?>

	<div class="controls">
		<p id="order-location">
			<?php echo $address->zip . ' ' . $address->city; ?>
		</p>
	</div>

	<div class="controls">
		<p id="order-country">
			<?php echo $address->getCountry()->name ?>
		</p>
	</div>

	<div class="controls">
		<p id="order-vat">
			<?php echo Text::_('COM_REDSHOPB_VAT') . ': ' . $company->vat_number; ?>
		</p>
	</div>

	<?php if (PluginHelper::isEnabled('vanir', 'custom_field_ean')) : ?>
		<?php
		RFactory::getDispatcher()->trigger('AECPrintEANOnOrder', array($company, &$ean));

		if (!empty($ean)) :
			?>
			<div class="controls">
				<p id="order-ean">
					<?php echo Text::_('PLG_VANIR_CUSTOM_FIELD_EAN_PRINT_EAN') . ': ' . $ean; ?>
				</p>
			</div>
		<?php endif; ?>
	<?php endif; ?>

	<?php if (isset($address->phone)) : ?>
		<div class="controls">
			<p id="order-phone">
				<?php echo $address->phone; ?>
			</p>
		</div>
	<?php endif; ?>

	<?php if (isset($address->email)) : ?>
		<div class="controls">
			<p id="order-email">
				<?php echo $address->email; ?>
			</p>
		</div>
	<?php endif; ?>

	<?php if ($config->get('show_invoice_email_field', 0) && $app->getUserState('checkout.invoice_email_toggle') != 1) : ?>
		<div class="controls">
			<p id="order-invoice-email">
				<?php echo $app->getUserState('checkout.invoice_email', $company->invoice_email); ?>
			</p>
		</div>
	<?php endif; ?>

	<br />
	<div class="controls">
		<p id="order-att">
			<?php
			$employeeName = $employee->name1;

			if (isset($employee->name2))
			{
				$employeeName .= " {$employee->name2}";
			}

			echo Text::_('COM_REDSHOPB_ADDRESS_ATTENTION') . ': ' . $employeeName;
			?>
		</p>
	</div>
</div>
