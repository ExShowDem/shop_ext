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

extract($displayData);

// Allow to override the form action
if (!isset($action))
{
	$action = RedshopbRoute::_('index.php?option=com_redshopb&view=stockrooms');
}

$companyId = Factory::getApplication()->input->getInt('id');
$listOrder = $state->get('list.ordering');
$listDirn  = $state->get('list.direction');
$saveOrder = $listOrder == 'ordering';

// Company filter will not enable search tools
if (isset($activeFilters['company']))
{
	unset($activeFilters['company']);
}

$searchToolsOptions = array(
	"filterButton"        => false,
	"searchFieldSelector" => "#filter_search",
	"searchField"         => "search",
	"limitFieldSelector"  => "abc#list_stockrooms_limit",
	"activeOrder"         => $listOrder,
	"activeDirection"     => $listDirn,
	"formSelector"        => ("#" . $formName)
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

<?php if ($showToolbar): ?>
	<?php echo RedshopbLayoutHelper::render('stockrooms.toolbar', $displayData); ?>
<?php endif; ?>

<form action="<?php echo $action; ?>" name="<?php echo $formName; ?>" class="adminForm" id="<?php echo $formName; ?>" method="post">
	<?php
	echo RedshopbLayoutHelper::render(
		'searchtools.default',
		array(
			'view' => (object) array(
				'filterForm' => $filterForm,
				'activeFilters' => $activeFilters
			),
			'options' => $searchToolsOptions
		)
	);
	?>

	<hr/>
	<?php if (empty($items)) : ?>
		<?php echo RedshopbLayoutHelper::render('common.nodata'); ?>
	<?php else : ?>
		<table class="table table-striped table-hover footable js-redshopb-footable redshopb-footable toggle-circle-filled" id="stockroomslist">
			<thead>
			<tr>
				<th width="1%">
					<input type="checkbox" name="checkall-toggle" value="" title="<?php echo Text::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)"/>
				</th>
				<th class="nowrap">
					<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_STOCKROOM_NAME_LABEL', 's.name', $listDirn, $listOrder); ?>
				</th>
				<th class="nowrap" data-hide="phone">
					<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_STOCKROOM_MIN_DELIVERY_TIME_LABEL', 's.min_delivery_time', $listDirn, $listOrder); ?>
				</th>
				<th class="nowrap" data-hide="phone">
					<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_STOCKROOM_MAX_DELIVERY_TIME_LABEL', 's.max_delivery_time', $listDirn, $listOrder); ?>
				</th>
				<th class="nowrap" data-hide="phone">
					<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_ID', 's.id', $listDirn, $listOrder); ?>
				</th>
			</tr>
			</thead>
			<tbody>
			<?php foreach ($items as $i => $item): ?>
				<tr>
					<td>
						<?php echo HTMLHelper::_('rgrid.id', $i, $item->id, false, 'cid', $formName); ?>
					</td>
					<td>
						<?php if ($item->checked_out) : ?>
							<?php
							$canCheckin = ($item->checked_out == Factory::getUser()->id) || ($item->checked_out == 0);
							echo HTMLHelper::_('rgrid.checkedout', $i, $item->checked_out > 0 ? Factory::getUser($item->checked_out)->get('name') : '', $item->checked_out_time, 'stockrooms.', $canCheckin) . $item->name;
							?>
						<?php else: ?>
							<?php
							$itemUrl = 'index.php?option=com_redshopb&task=stockroom.edit&id=' . $item->id
								. '&jform[company_id]=' . $companyId . '&from_company=1';

							if ($return)
							{
								$itemUrl .= '&return=' . $return;
							}
							?>
							<a href="<?php echo RedshopbRoute::_($itemUrl); ?>">
								<?php echo $item->name ?>
							</a>
						<?php endif; ?>
					</td>
					<td>
						<?php echo ($item->min_delivery_time) ? (int) $item->min_delivery_time : '-'; ?>
					</td>
					<td>
						<?php echo ($item->max_delivery_time) ? (int) $item->max_delivery_time : '-'; ?>
					</td>
					<td>
						<?php echo $item->id; ?>
					</td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>

		<?php echo $pagination->getPaginationLinks('tab.pagination.links', array('showLimitBox' => false)); ?>
	<?php endif; ?>

	<div>
		<input type="hidden" name="task" value="company.saveModelState">
		<?php if ($return) : ?>
			<input type="hidden" name="return" value="<?php echo $return ?>">
		<?php endif; ?>
		<input type="hidden" name="jform[company_id]" value="<?php echo $companyId ?>">
		<input type="hidden" name="from_company" value="1">
		<input type="hidden" name="boxchecked" value="0">
		<?php echo HTMLHelper::_('form.token'); ?>
	</div>
</form>
