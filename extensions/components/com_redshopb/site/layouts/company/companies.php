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
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;

$data = $displayData;

$state       = $data['state'];
$items       = $data['items'];
$pagination  = $data['pagination'];
$filterForm  = $displayData['filter_form'];
$formName    = $data['formName'];
$showToolbar = isset($data['showToolbar']) ? $data['showToolbar'] : false;
$return      = isset($data['return']) ? $data['return'] : null;
$action      = RedshopbRoute::_('index.php?option=com_redshopb&view=users');

// Allow to override the form action
if (isset($data['action']))
{
	$action = $data['action'];
}

$companyId = Factory::getApplication()->input->getInt('id');
$listOrder = $state->get('list.ordering');
$listDirn  = $state->get('list.direction');
$saveOrder = $listOrder == 'ordering';

// Company filter will not enable search tools
if (isset($data['activeFilters']['company']))
{
	unset($data['activeFilters']['company']);
}

$searchToolsOptions = array(
	"searchFieldSelector" => "#filter_search_companies",
	"orderFieldSelector" => "#list_fullordering",
	"searchField" => "search_companies",
	"limitFieldSelector" => "#list_company_limit",
	"activeOrder" => $listOrder,
	"activeDirection" => $listDirn,
	"formSelector" => ("#" . $formName),
	"filtersHidden" => (bool) empty($data['activeFilters'])
);
?>
<script type="text/javascript">
	(function ($) {
		$(document).ready(function () {
			$('#<?php echo $formName; ?>').searchtools(
				<?php echo json_encode($searchToolsOptions); ?>
			);
		});
	})(jQuery);
</script>
<div class="redshopb-company-companies">
	<form action="<?php echo $action; ?>" name="<?php echo $formName; ?>" class="adminForm" id="<?php echo $formName; ?>" method="post">
		<?php
		// Render the toolbar?
		if ($showToolbar)
		{
			echo RedshopbLayoutHelper::render('companies.toolbar', $data);
		}
		?>

		<?php
		if (isset($displayData['search']) && $displayData['search'] == true)
		{
			echo RedshopbLayoutHelper::render(
				'searchtools.default',
				array(
				'view' => (object) array(
						'filterForm' => $data['filter_form'],
						'activeFilters' => $data['activeFilters']
					),
				'options' => $searchToolsOptions
				)
			);
		}
		?>

		<hr/>
		<?php if (empty($items)) : ?>
			<?php echo RedshopbLayoutHelper::render('common.nodata'); ?>
		<?php else : ?>
			<table class="table table-striped table-hover footable js-redshopb-footable redshopb-footable toggle-circle-filled" id="companyList">
				<thead>
				<tr>
					<th width="1%">
						<input type="checkbox" name="checkall-toggle" value=""
							   title="<?php echo Text::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)"/>
					</th>
					<th width="1%" class="nowrap center">
						<?php echo HTMLHelper::_('rsearchtools.sort', 'JSTATUS', 'c.state', $listDirn, $listOrder, null, 'asc', '', null, $formName); ?>
					</th>
					<th class="nowrap" data-toggle="true">
						<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_NAME', 'c.name', $listDirn, $listOrder, null, 'asc', '', null, $formName); ?>
					</th>
					<th width="4%" class="nowrap" data-hide="phone">
						<?php echo Text::_('COM_REDSHOPB_USERS') ?>
					</th>
					<th width="12%" class="nowrap" data-hide="phone">
						<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_ADDRESS_LABEL', 'c.address', $listDirn, $listOrder, null, 'asc', '', null, $formName); ?>
					</th>
					<th width="8%" class="nowrap" data-hide="phone">
						<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_ZIP_LABEL', 'c.zip', $listDirn, $listOrder, null, 'asc', '', null, $formName); ?>
					</th>
					<th width="10%" class="nowrap" data-hide="phone">
						<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_CITY_LABEL', 'c.city', $listDirn, $listOrder, null, 'asc', '', null, $formName); ?>
					</th>
					<th width="10%" class="nowrap" data-hide="phone">
						<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_COUNTRY_LABEL', 'c.country', $listDirn, $listOrder, null, 'asc', '', null, $formName); ?>
					</th>
					<th width="1%" class="nowrap" data-hide="phone">
						<?php echo HTMLHelper::_('rsearchtools.sort', 'JGRID_HEADING_ID', 'c.id', $listDirn, $listOrder, null, 'asc', '', null, $formName); ?>
					</th>
				</tr>
				</thead>
				<?php if ($items): ?>
					<tbody>
					<?php foreach ($items as $i => $item): ?>
						<?php
						$canChange  = 1;
						$canEdit    = 1;
						$canCheckin = 1;
						?>
						<tr>
							<td>
								<?php echo HTMLHelper::_('rgrid.id', $i, $item->id, false, 'cid', $formName); ?>
							</td>
							<td>
								<?php echo HTMLHelper::_('rgrid.published', $item->state, $i, 'companies.', $canChange, 'cb', null, null, $formName); ?>
							</td>
							<td>
								<?php if (isset($item->checked_out)) : ?>
									<?php echo HTMLHelper::_('rgrid.checkedout', $i, $item->checked_out > 0 ? Factory::getUser($item->checked_out)->get('name') : '',
										$item->checked_out_time, 'companies.', $canCheckin, 'cb', $formName
									); ?>
								<?php endif; ?>
								<?php
								$itemUrl = 'index.php?option=com_redshopb&task=company.edit&id=' . $item->id
									. '&from_company=1';

								if ($return)
								{
									$itemUrl .= '&return=' . $return;
								}
								?>
								<a href="<?php echo RedshopbRoute::_($itemUrl); ?>">
									<?php echo $item->name ?>
									<?php if ($item->name2): ?>
										<br /><small><?php echo $item->name2; ?></small>
									<?php endif; ?>
								</a>
							</td>
							<td>
								<?php echo $item->users ?>
							</td>
							<td>
								<?php echo $item->address ?>
							</td>
							<td>
								<?php echo $item->zip ?>
							</td>
							<td>
								<?php echo $item->city ?>
							</td>
							<td>
								<?php echo Text::_($item->country); ?>
							</td>
							<td>
								<?php echo $item->id; ?>
							</td>
						</tr>
					<?php endforeach; ?>
					</tbody>
				<?php endif; ?>
			</table>

			<?php echo $pagination->getPaginationLinks('tab.pagination.links', array('showLimitBox' => false)); ?>
		<?php endif; ?>

		<div>
			<input type="hidden" name="task" value="company.saveModelState">
			<?php if ($return) : ?>
				<input type="hidden" name="return" value="<?php echo $return ?>">
			<?php endif; ?>
			<input type="hidden" name="from_company" value="1">
			<input type="hidden" name="boxchecked" value="0">
			<?php echo HTMLHelper::_('form.token'); ?>
		</div>
	</form>
</div>
