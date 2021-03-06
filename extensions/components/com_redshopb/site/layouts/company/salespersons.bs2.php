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
	"searchFieldSelector" => "#filter_search_salespersons",
	"orderFieldSelector" => "#list_fullordering",
	"searchField" => "search_salespersons",
	"limitFieldSelector" => "#list_salesperson_limit",
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
	echo RedshopbLayoutHelper::render('salespersons.toolbar', $data);
}
?>
<div class="redshopb-company-salespersons">
	<form action="<?php echo $action; ?>" name="<?php echo $formName; ?>" class="adminForm" id="<?php echo $formName; ?>" method="post">
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
			<table class="table table-striped table-hover footable js-redshopb-footable redshopb-footable toggle-circle-filled" id="salespersonList">
				<thead>
				<tr>
					<th width="1%">
						<input type="checkbox" name="checkall-toggle" value=""
							   title="<?php echo Text::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)"/>
					</th>
					<th class="nowrap" data-toggle="true">
						<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_NAME', 'u.name', $listDirn, $listOrder, null, 'asc', '', null, $formName); ?>
					</th>
					<th width="1%" class="nowrap" data-hide="phone">
						<?php echo HTMLHelper::_('rsearchtools.sort', 'JGLOBAL_EMAIL', 'u.email', $listDirn, $listOrder, null, 'asc', '', null, $formName); ?>
					</th>
					<th width="1%" class="nowrap" data-hide="phone">
						<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_USER_ACTIVATION', 'u.block', $listDirn, $listOrder, null, 'asc', '', null, $formName); ?>
					</th>
					<th width="1%" class="nowrap" data-hide="phone">
						<?php echo HTMLHelper::_('rsearchtools.sort', 'JGRID_HEADING_ID', 'u.id', $listDirn, $listOrder, null, 'asc', '', null, $formName); ?>
					</th>
				</tr>
				</thead>
				<?php if ($items): ?>
					<tbody>
					<?php foreach ($items as $i => $item): ?>
						<tr>
							<td>
								<?php echo HTMLHelper::_('rgrid.id', $i, $item->id, false, 'cid', $formName); ?>
							</td>
							<td>
								<?php echo $item->name ?>
							</td>
							<td>
								<?php echo $item->email; ?>
							</td>
							<td>
								<?php if ((bool) $item->block) : ?>
									<span class="badge badge-important"> <?php echo Text::_('JBLOCKED') ?> </span>
								<?php else : ?>
									<span class="badge badge-success"> <?php echo Text::_('JENABLED') ?> </span>
								<?php endif; ?>
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
			<input type="hidden" name="filter[company]" value="<?php echo $companyId ?>">
			<input type="hidden" name="from_company" value="1">
			<input type="hidden" name="boxchecked" value="0">
			<input type="hidden" name="newsalespersons" value="">
			<input type="hidden" name="company_id" value="<?php echo $companyId ?>">
			<?php echo HTMLHelper::_('form.token'); ?>
		</div>
	</form>
</div>
<?php
	echo RedshopbLayoutHelper::render(
		'salespersons.modal',
		array_merge(
			$data,
			array(
				'companyId' => $companyId
			)
		)
	);
