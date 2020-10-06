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

HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('rdropdown.init');
HTMLHelper::_('vnrbootstrap.tooltip');
HTMLHelper::_('rjquery.chosen', 'select');

$action    = RedshopbRoute::_('index.php?option=com_redshopb&view=companies');
$listOrder = $this->state->get('list.ordering');
$listDirn  = $this->state->get('list.direction');
$saveOrder = $listOrder == 'ordering';

?>
<div class="redshopb-companies-debtors">
	<form action="<?php echo $action; ?>" name="adminForm" class="adminForm" id="adminForm" method="post">

		<?php
		echo RedshopbLayoutHelper::render(
			'searchtools.default',
			array(
				'view' => $this,
				'options' => array(
					'searchField' => 'search_companies',
					'searchFieldSelector' => '#filter_search_companies',
					'limitFieldSelector' => '#list_company_limit',
					'activeOrder' => $listOrder,
					'activeDirection' => $listDirn
				)
			)
		);
		?>

		<hr/>
		<?php if (empty($this->items)) : ?>
			<?php echo RedshopbLayoutHelper::render('common.nodata'); ?>
		<?php else : ?>
			<div class="redshopb-companies-debtors-table">
				<table class="table table-striped table-hover" id="companyList">
					<thead>
					<tr>
						<th width="1%" class="hidden-phone">
							<input type="checkbox" name="checkall-toggle" value=""
								   title="<?php echo Text::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)"/>
						</th>
						<th width="1%" class="nowrap center">
							<?php echo HTMLHelper::_('rsearchtools.sort', 'JSTATUS', 'c.state', $listDirn, $listOrder); ?>
						</th>
						<th>
							<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_COMPANY_CUSTOMER_NUMBER', 'c.customer_number', $listDirn, $listOrder); ?>
						</th>
						<th class="nowrap hidden-phone">
							<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_NAME', 'c.name', $listDirn, $listOrder); ?>
						</th>
						<th width="20%" class="nowrap hidden-phone">
							<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_COMPANY_PARENT_LBL', 'c.customer_at', $listDirn, $listOrder); ?>
						</th>
						<th width="4%" class="nowrap hidden-phone">
							<?php echo Text::_('COM_REDSHOPB_USERS') ?>
						</th>
						<th width="12%" class="nowrap hidden-phone">
							<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_ADDRESS_LABEL', 'c.address', $listDirn, $listOrder); ?>
						</th>
						<th width="8%" class="nowrap hidden-phone">
							<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_ZIP_LABEL', 'c.zip', $listDirn, $listOrder); ?>
						</th>
						<th width="10%" class="nowrap hidden-phone">
							<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_CITY_LABEL', 'c.city', $listDirn, $listOrder); ?>
						</th>
						<th width="10%" class="nowrap hidden-phone">
							<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_COUNTRY_LABEL', 'c.country', $listDirn, $listOrder); ?>
						</th>
						<th width="1%" class="nowrap hidden-phone">
							<?php echo HTMLHelper::_('rsearchtools.sort', 'JGRID_HEADING_ID', 'c.id', $listDirn, $listOrder); ?>
						</th>
					</tr>
					</thead>
					<?php if ($this->items): ?>
						<tbody>
						<?php foreach ($this->items as $i => $item): ?>
							<?php
							$canChange  = RedshopbHelperACL::getPermission('manage', 'company', Array('edit.state'), false, $item->asset_id);
							$canEdit    = RedshopbHelperACL::getPermission('manage', 'company', Array('edit','edit.own'), false, $item->asset_id);
							$canCheckin = $canEdit;
							?>
							<tr>
								<td>
									<?php echo HTMLHelper::_('grid.id', $i, $item->id); ?>
								</td>
								<td>
									<?php echo HTMLHelper::_('rgrid.published', $item->state, $i, 'companies.', $canChange, 'cb'); ?>
								</td>
								<td>
									<?php echo $this->escape($item->customer_number); ?>
								</td>
								<td>
									<?php echo str_repeat('<span class="gi">|&mdash;</span>', $item->level - 1) ?>
									<?php if ($canEdit) : ?>
										<a href="<?php echo RedshopbRoute::_('index.php?option=com_redshopb&task=company.edit&id=' . $item->id); ?>">
									<?php endif; ?>
											<?php echo $this->escape($item->name); ?>

									<?php if ($canEdit) : ?>
										</a>
									<?php endif; ?>
								</td>
								<td>
									<?php echo $this->escape($item->customer_at); ?>
								</td>
								<td>
									<?php echo $this->escape($item->users); ?>
								</td>
								<td>
									<?php echo $this->escape($item->address); ?>
								</td>
								<td>
									<?php echo $this->escape($item->zip); ?>
								</td>
								<td>
									<?php echo $this->escape($item->city); ?>
								</td>
								<td>
									<?php echo $this->escape(Text::_($item->country)); ?>
								</td>
								<td>
									<?php echo $item->id; ?>
								</td>
							</tr>
						<?php endforeach; ?>
						</tbody>
					<?php endif; ?>
				</table>
			</div>
			<div class="redshopb-companies-debtors-pagination">
				<?php echo $this->pagination->getPaginationLinks(null, array('showLimitBox' => false)); ?>
			</div>
		<?php endif; ?>

		<div>
			<input type="hidden" name="task" value="">
			<input type="hidden" name="boxchecked" value="0">
			<?php echo HTMLHelper::_('form.token'); ?>
		</div>
	</form>
</div>
