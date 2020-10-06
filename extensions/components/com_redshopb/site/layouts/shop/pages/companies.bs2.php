<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Layouts.Shop.Pages
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */
defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

$companies           = $displayData['companies'];
$subCompaniesCount   = $displayData['subCompaniesCount'];
$subDepartmentsCount = $displayData['subDepartmentsCount'];
$subEmployeesCount   = $displayData['subEmployeesCount'];
$currentCompanyId    = $displayData['currentCompanyId'];
$showPagination      = isset($displayData['showPagination']) ? $displayData['showPagination'] : false;
$returnUrl           = $displayData['returnUrl'];

if ($showPagination)
{
	$numberOfPages = $displayData['numberOfPages'];
	$currentPage   = $displayData['currentPage'];
	$ajaxJS        = $displayData['ajaxJS'];
}

$i = 0;

?>

<div class="row-fluid">
	<div class="row">
		<?php foreach ($companies as $company) : ?>
		<?php
		$i++;
		$companiesCount   = isset($subCompaniesCount[$company->id]) ? $subCompaniesCount[$company->id] : 0;
		$departmentsCount = isset($subDepartmentsCount[$company->id]) ? $subDepartmentsCount[$company->id] : 0;
		$employeesCount   = isset($subEmployeesCount[$company->id]) ? $subEmployeesCount[$company->id] : 0;

		if ($currentCompanyId == $company->id)
		{
			$isActive    = true;
			$classActive = ' customer-item-active';
		}
		else
		{
			$isActive    = false;
			$classActive = '';
		}

		// Determines if the current logged in user can use the company to shop with
		$canShop = RedshopbHelperShop::canShop($company->id, $company->parent_id, $company->asset_id, 'company');

		?>
		<div class="customer-item well span4<?php echo $classActive; ?>">
			<h4><?php echo $this->escape($company->customer_number . ' ' . $company->name); ?></h4>
			<?php
			if ($company->name2): ?>
							<h5><?php echo $this->escape($company->name2); ?></h5>
			<?php endif; ?>
			<div class="customer-info">
				<p>
					<i class="icon-globe"></i>&nbsp;
					<?php echo Text::_('COM_REDSHOPB_SHOP_CUSTOMER_COMPANIES_NO') . ': ' . $companiesCount; ?>
				</p>
				<?php if (RedshopbApp::getConfig()->getInt('impersonation_department', 1)): ?>
					<p>
						<i class="icon-building"></i>&nbsp;
						<?php echo Text::_('COM_REDSHOPB_SHOP_CUSTOMER_DEPARTMENTS_NO') . ': ' . $departmentsCount; ?>
					</p>
				<?php endif; ?>
				<?php
				if (RedshopbApp::getConfig()->getInt('impersonation_user', 1)): ?>
									<p>
										<i class="icon-user"></i>&nbsp;
										<?php echo Text::_('COM_REDSHOPB_SHOP_CUSTOMER_EMPLOYEES_NO') . ': ' . $employeesCount; ?>
									</p>
				<?php endif; ?>
			</div>
			<div class="customer-actions">
				<?php if ($canShop && !$company->b2c): ?>
					<?php
					$href = RedshopbRoute::_(
						'index.php?option=com_redshopb&task=shop.sobcompany' .
						'&company_id=' . $company->id .
						'&department_id=0' .
						'&rsbuser_id=0' .
						$returnUrl
					);
					?>

					<a class="btn btn-primary" href="<?php echo $href; ?>">
						<i class="icon-shopping-cart"></i>
						<?php echo Text::_('COM_REDSHOPB_SHOP') ?>
					</a>
				<?php endif; ?>

				<?php if (($employeesCount || $departmentsCount || $companiesCount) && !$isActive): ?>
					<?php
					$href = RedshopbRoute::_(
						'index.php?option=com_redshopb&task=shop.savepath' .
						'&company_id=' . $company->id .
						'&department_id=' .
						'&rsbuser_id=0' .
						$returnUrl
					);
					?>
					<a class="btn btn-success" href="<?php echo $href;  ?>">
						<i class="icon-signin"></i>
						<?php echo Text::_('COM_REDSHOPB_OPEN') ?>
					</a>
				<?php endif; ?>
			</div>
		</div>
		<?php if ($i % 3 == 0): ?>
	</div>
	<div class="row">
		<?php endif; ?>
		<?php endforeach; ?>
	</div>

	<?php if ($showPagination): ?>
		<?php if (RedshopbApp::isUseAjaxReadMorePagination()): ?>
			<?php echo RedshopbLayoutHelper::render(
				'shop.pages.nopagination',
				array(
					'numberOfPages' => $numberOfPages,
					'currentPage'   => $currentPage,
					'ajaxJS'        => $ajaxJS,
					'isCompanies'   => true
				)
			);?>
		<?php else: ?>
			<?php echo RedshopbLayoutHelper::render(
				'shop.pages.pagination.links',
				array(
					'numberOfPages' => $numberOfPages,
					'currentPage'   => $currentPage,
					'ajaxJS'        => $ajaxJS
				)
			);?>
		<?php endif; ?>
	<?php endif;?>
</div>
