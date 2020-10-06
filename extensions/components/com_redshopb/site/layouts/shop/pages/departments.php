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

$departments         = $displayData['departments'];
$currentDepartmentId = $displayData['currentDepartmentId'];
$showPagination      = isset($displayData['showPagination']) ? $displayData['showPagination'] : false;
$returnUrl           = isset($displayData['returnUrl']) ? $displayData['returnUrl'] : null;

if ($showPagination)
{
	$numberOfPages = $displayData['numberOfPages'];
	$currentPage   = $displayData['currentPage'];
	$ajaxJS        = $displayData['ajaxJS'];
}

$ids = array();
$i   = 0;

foreach ($departments as $department)
{
	$ids[] = $department->id;
}

$subDepartmentsCount = RedshopbHelperDepartment::getSubDepartmentsCount($ids, true, false);

if (RedshopbApp::getConfig()->getInt('impersonation_user', 1))
{
	$subEmployeesCount = RedshopbHelperDepartment::getEmployeesCount($ids, true, false);
}

?>

<div class="row">
	<div class="row">
		<?php foreach ($departments as $department) : ?>
		<?php
		$i++;
		$departmentsCount = isset($subDepartmentsCount[$department->id]) ? $subDepartmentsCount[$department->id] : 0;
		$employeesCount   = isset($subEmployeesCount[$department->id]) ? $subEmployeesCount[$department->id] : 0;

		if ($currentDepartmentId == $department->id)
		{
			$isActive    = true;
			$classActive = ' customer-item-active';
		}
		else
		{
			$isActive    = false;
			$classActive = '';
		}

		$company = RedshopbHelperCompany::getCompanyById($department->company_id);
		$canShop = RedshopbHelperShop::canShop($department->company_id, $company->parent, $department->asset_id, 'department');
		?>
		<div class="customer-item well col-md-4<?php echo $classActive; ?>">
			<h4><?php echo $this->escape($department->name); ?></h4>

			<div class="customer-info">
				<?php if (RedshopbApp::getConfig()->getInt('impersonation_department', 1)): ?>
					<p>
						<i class="icon-building"></i>&nbsp;
						<?php echo Text::_('COM_REDSHOPB_SHOP_CUSTOMER_DEPARTMENTS_NO') . ': ' . $departmentsCount; ?>
					</p>
				<?php endif; ?>

				<?php if (RedshopbApp::getConfig()->getInt('impersonation_user', 1)): ?>
					<p>
						<i class="icon-user"></i>&nbsp;
						<?php echo Text::_('COM_REDSHOPB_SHOP_CUSTOMER_EMPLOYEES_NO') . ': ' . $employeesCount; ?>
					</p>
				<?php endif; ?>
			</div>
			<div class="customer-actions">
				<?php if ($canShop) : ?>
					<?php
					$href = RedshopbRoute::_(
						'index.php?option=com_redshopb&task=shop.sobdepartment&' .
						'company_id=' . $department->company_id .
						'&department_id=' . $department->id .
						'&rsbuser_id=0' .
						$returnUrl
					);
					?>
					<a class="btn btn-primary" href="<?php echo $href; ?>">
						<i class="icon-shopping-cart"></i>
						<?php echo Text::_('COM_REDSHOPB_SHOP') ?>
					</a>
				<?php endif; ?>

				<?php if (($employeesCount || $departmentsCount) && !$isActive): ?>
					<?php
					$href = RedshopbRoute::_(
						'index.php?option=com_redshopb&task=shop.savepath&' .
						'company_id=' . $department->company_id .
						'&department_id=' . $department->id .
						'&rsbuser_id=0' .
						$returnUrl
					);
					?>
					<a class="btn btn-success" href="<?php echo $href; ?>">
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
					'isDepartments' => true
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
