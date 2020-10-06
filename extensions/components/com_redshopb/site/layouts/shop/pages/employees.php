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

$employees         = $displayData['employees'];
$currentEmployeeId = $displayData['currentEmployeeId'];
$showPagination    = isset($displayData['showPagination']) ? $displayData['showPagination'] : false;
$returnUrl         = isset($displayData['returnUrl']) ? $displayData['returnUrl'] : null;

if ($showPagination)
{
	$numberOfPages = $displayData['numberOfPages'];
	$currentPage   = $displayData['currentPage'];
	$ajaxJS        = $displayData['ajaxJS'];
}

$i = 0;
?>

<div class="row">
	<div class="row">
		<?php foreach ($employees as $employee) : ?>
		<?php $i++;

		if ($currentEmployeeId == $employee->id)
		{
			$isActive    = true;
			$classActive = ' customer-item-active';
		}
		else
		{
			$isActive    = false;
			$classActive = '';
		}

		$company           = RedshopbHelperCompany::getCompanyById($employee->company_id);
		$companyUseWallets = $company->useWallet;
		$canShop           = RedshopbHelperShop::canShop($employee->company_id, $company->parent, 0, 'employee');
		?>
		<div class="customer-item well col-md-4<?php echo $classActive; ?>">
			<!-- Show order by myself for users which can impersonate others -->
			<?php if ($isActive): ?>
				<h4><?php echo Text::_('COM_REDSHOPB_SHOP_MYSELF'); ?></h4>
			<?php else: ?>
				<h4><?php echo $this->escape($employee->name1); ?></h4>
			<?php endif; ?>
			<?php
			if ($employee->name2): ?>
							<h5><?php echo $this->escape($employee->name2); ?></h5>
			<?php endif; ?>
			<div class="customer-info">
				<?php if ($companyUseWallets) : ?>
					<?php $balances = RedshopbHelperWallet::getMoneyAmount($employee->wallet_id); ?>
					<?php
					if (!empty($balances)): ?>
						<p>
						<div class="accordion" id="customerBalance<?php echo $employee->id; ?>">
							<div class="accordion-group">
								<div class="accordion-heading">
									<a class="accordion-toggle collapsed" data-toggle="collapse"
										data-parent="#customerBalance<?php echo $employee->id; ?>"
										href="#collapseBalance<?php echo $employee->id; ?>">
										<i class="icon-money"></i>
										<h5><?php echo Text::_('COM_REDSHOPB_SHOP_CUSTOMER_WALLET'); ?></h5>
									</a>
								</div>
								<div id="collapseBalance<?php echo $employee->id ?>" class="accordion-body collapse">
									<div class="accordion-inner">
										<table class="table table-condensed table-striped table-bordered">
											<tr>
												<th><?php echo Text::_('COM_REDSHOPB_CURRENCY'); ?></th>
												<th><?php echo Text::_('COM_REDSHOPB_AMOUNT'); ?></th>
											</tr>
											<?php foreach ($balances as $balance): ?>
												<tr>
													<td><?php echo $balance['currency']; ?></td>
													<td><?php echo $balance['amount']; ?></td>
												</tr>
											<?php endforeach; ?>
										</table>
									</div>
								</div>
							</div>
						</div>
						</p>
					<?php else: ?>
						<p>
							<i class="icon-money"></i>&nbsp;
							<b><?php echo Text::_('COM_REDSHOPB_SHOP_USER_WALLET_EMPTY'); ?></b>
						</p>
					<?php endif; ?>
				<?php endif; ?>
				<p>
					<i class="icon-user"></i>&nbsp;
					<?php
					if ($isActive && RedshopbHelperUser::isRoot($employee->id))
					{
						echo Text::_('COM_REDSHOPB_ROLE_LABEL') . ': ' . ucfirst(Text::_('COM_REDSHOPB_SUPER_ADMIN'));
					}
					else
					{
						echo Text::_('COM_REDSHOPB_ROLE_LABEL') . ': ' . preg_replace('/[0-9]{2} :: /i', '', $employee->role);
					}
					?>
				</p>

				<p>
					<?php if (isset($employee->employee_number) && $employee->employee_number != '' && count($employee->employee_number) > 0) : ?>
						<i class="icon-info-sign"></i>&nbsp;
						<?php echo Text::_('COM_REDSHOPB_USER_NUMBER_LABEL') . ': #' . $this->escape($employee->employee_number); ?>
					<?php else : ?>
						<i class="icon-info-sign"></i>&nbsp;
						<b><?php echo Text::_('COM_REDSHOPB_USER_NUMBER_NOT_SET'); ?></b>
					<?php endif; ?>
				</p>
			</div>
			<div class="customer-actions">
				<?php if ($canShop && !strstr($employee->employee_number, '-guest')) : ?>
					<?php
					$href = RedshopbRoute::_(
						'index.php?option=com_redshopb&task=shop.sobemployee' .
						'&company_id=' . $employee->company_id .
						'&department_id=' . $employee->department_id .
						'&rsbuser_id=' . $employee->id .
						$returnUrl
					);
					?>
					<a class="btn btn-primary" href="<?php echo $href; ?>">
						<i class="icon-shopping-cart"></i>
						<?php echo Text::_('COM_REDSHOPB_SHOP') ?>
					</a>
				<?php
				endif;
				?>
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
					'isEmployees'   => true
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
