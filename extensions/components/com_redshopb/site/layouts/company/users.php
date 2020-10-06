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
	"searchFieldSelector" => "#filter_search_users",
	"orderFieldSelector" => "#list_fullordering",
	"searchField" => "search_users",
	"limitFieldSelector" => "#list_user_limit",
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
	<?php
	// Render the toolbar?
	if ($showToolbar)
	{
		echo RedshopbLayoutHelper::render('users.toolbar', $data);
	}
	?>
<form action="<?php echo $action; ?>" name="<?php echo $formName; ?>" class="adminForm" id="<?php echo $formName; ?>"
	  method="post">
	<?php
	echo RedshopbLayoutHelper::render(
		'searchtools.default',
		array(
			'view' => (object) array(
					'filterForm' => $data['filterForm'],
					'activeFilters' => $data['activeFilters']
				),
			'options' => $searchToolsOptions
		)
	);
	?>

	<hr/>
	<?php if (empty($items)) : ?>
		<?php echo RedshopbLayoutHelper::render('common.nodata'); ?>
	<?php else : ?>
		<table class="table table-striped table-hover footable js-redshopb-footable redshopb-footable toggle-circle-filled" id="userList">
			<thead>
			<tr>
				<th width="1%">
					<input type="checkbox" name="checkall-toggle" value=""
						   title="<?php echo Text::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)"/>
				</th>
				<th class="nowrap" data-toggle="true">
					<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_NAME', 'u.name', $listDirn, $listOrder, null, 'asc', '', null, $formName); ?>
				</th>
				<th width="20%" class="nowrap" data-hide="phone">
					<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_DEPARTMENT_LABEL', 'u.department', $listDirn, $listOrder, null, 'asc', '', null, $formName); ?>
				</th>
				<th width="1%" class="nowrap" data-hide="phone">
					<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_USER_ACTIVATION', 'u.block', $listDirn, $listOrder, null, 'asc', '', null, $formName); ?>
				</th>
				<th width="20%" class="nowrap" data-hide="phone">
					<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_ROLE_LABEL', 'u.role', $listDirn, $listOrder, null, 'asc', '', null, $formName); ?>
				</th>
				<th width="1%" class="nowrap" data-hide="phone">
					<?php echo HTMLHelper::_('rsearchtools.sort', 'JGRID_HEADING_ID', 'u.id', $listDirn, $listOrder, null, 'asc', '', null, $formName); ?>
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
							<?php if ($item->checked_out) : ?>
								<?php echo HTMLHelper::_('rgrid.checkedout', $i, $item->checked_out > 0 ? Factory::getUser($item->checked_out)->get('name') : '',
									$item->checked_out_time, 'users.', $canCheckin, 'cb', $formName
								); ?>
							<?php endif; ?>
							<?php
							$itemUrl = 'index.php?option=com_redshopb&task=user.edit&id=' . $item->id
								. '&jform[company_id]=' . $companyId . '&from_company=1';

							if ($return)
							{
								$itemUrl .= '&return=' . $return;
							}
							?>
							<a href="<?php echo RedshopbRoute::_($itemUrl); ?>">
								<?php echo $item->name ?>
							</a>
						</td>
						<td>
							<?php echo $item->department; ?>
						</td>
						<td>
							<?php if ((bool) $item->block) : ?>
								<span class="label label-important"> <?php echo Text::_('JBLOCKED') ?> </span>
							<?php else : ?>
								<span class="label label-success"> <?php echo Text::_('JENABLED') ?> </span>
							<?php endif; ?>
						</td>
						<td>
							<?php echo $item->role ?>
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

	<?php
		echo RedshopbLayoutHelper::render(
			'user.wallet.actions',
			$data
		);
	?>

	<div>
		<input type="hidden" name="task" value="company.saveModelState">
		<?php if ($return) : ?>
			<input type="hidden" name="return" value="<?php echo $return ?>">
		<?php endif; ?>
		<input type="hidden" name="jform[company_id]" value="<?php echo $companyId ?>">
		<input type="hidden" name="filter[company]" value="<?php echo $companyId ?>">
		<input type="hidden" name="from_company" value="1">
		<input type="hidden" name="boxchecked" value="0">
		<?php echo HTMLHelper::_('form.token'); ?>
	</div>
</form>
