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
use Joomla\Registry\Registry;

$data = $displayData;

$state = $data['state'];
$items = $data['items'];

/** @var RPagination $pagination */
$pagination  = $data['pagination'];
$formName    = $data['formName'];
$action      = $data['action'];
$isManage    = $data['isManage'];
$showToolbar = $data['showToolbar'];

$listOrder = $data['listOrder'];
$listDirn  = $data['listDirn'];

$searchToolsOptions = array(
	'view' => (object) array(
		'filterForm' => $displayData['filter_form'],
		'activeFilters' => $displayData['activeFilters']
	),
	'options' => array(
		'filterButton' => true,
		'searchFieldSelector' => '#filter_search_' . $formName,
		'orderFieldSelector' => '#list_fullordering',
		'searchField' => 'search_' . $formName,
		'limitFieldSelector' => '#list_' . $formName . '_limit',
		'activeOrder' => $listOrder,
		'activeDirection' => $listDirn,
		'formSelector' => '#' . $formName,
		'filtersHidden' => (bool) empty($displayData['activeFilters'])
	)
);

$filterOptions           = new Registry($searchToolsOptions['options']);
$needChangeImpersonation = false;
$app                     = Factory::getApplication();

if (!$app->getUserState('shop.customer_type') || !$app->getUserState('shop.customer_id', 0))
{
	$needChangeImpersonation = true;
}

$user    = Factory::getUser();
$colspan = 5;
?>
<form action="<?php echo RedshopbRoute::_($action) ?>" name="<?php echo $formName ?>" class="adminForm" id="<?php echo $formName ?>" method="post">
<?php if ($showToolbar):?>
	<div class="row">
		<div class="col-md-12">
			<?php echo RedshopbLayoutHelper::render('myfavoritelists.toolbar', $displayData);?>
		</div>
	</div>
<?php endif;?>
	<div class="row">
		<div class="col-md-12">
			<?php echo RedshopbLayoutHelper::render('searchtools.default', $searchToolsOptions);?>
		</div>
	</div>
	<hr/>
	<div class="row redshopb-myfavoritelists-table">
		<div class="col-md-12">
			<table class="table table-striped table-hover" id="MyFavoritesList">
				<thead>
					<tr>
					<?php if ($showToolbar):?>
					<th width="1%">
						&nbsp;
					</th>
					<?php endif;?>
					<th class="nowrap center">
						<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_MYFAVORITELIST_NAME', 'fl.name', $listDirn, $listOrder); ?>
					</th>
					<th class="nowrap" data-hide="phone, tablet" data-toggle="true">
						<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_MYFAVORITELIST_VISIBLE_OTHERS', 'fl.visible_others', $listDirn, $listOrder); ?>
					</th>
					<th class="nowrap" data-hide="phone">
						<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_MYFAVORITELIST_CREATEDDATE_LABEL', 'fl.created_date', $listDirn, $listOrder); ?>
					</th>
					<?php if ($isManage): ?>
						<th class="nowrap" data-hide="phone, tablet">
							<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_MYFAVORITELIST_OWNER', 'usr.name1', $listDirn, $listOrder); ?>
						</th>
						<th width="1%" class="nowrap" data-hide="phone, tablet">
							<?php echo HTMLHelper::_('rsearchtools.sort', 'JGRID_HEADING_ID', 'fl.id', $listDirn, $listOrder); ?>
						</th>
						<?php $colspan += 2;?>
					<?php endif; ?>
					<th></th>
				</tr>
				</thead>
				<tfoot>
					<tr>
						<td colspan="<?php echo $colspan;?>">
							<?php echo $pagination->getPaginationLinks(null, array('showLimitBox' => false)); ?>
						</td>
					</tr>
				</tfoot>
				<tbody>
					<?php foreach ($items as $i => $item) :?>
					<?php $canCheckin = ($user->authorise('core.manage', 'com_checkin') || $item->checked_out == $user->get('id') || $item->checked_out == null); ?>
					<tr>
						<?php if ($showToolbar):?>
						<td>
							<a href="<?php echo RedshopbRoute::_('index.php?option=com_redshopb&task=myfavoritelists.delete&id=' . $item->id); ?>">
								<i class="icon-trash"></i>
							</a>
						</td>
						<?php endif;?>
						<td>
							<span class="hide">
								<?php echo HTMLHelper::_('rgrid.id', $i, $item->id, false, 'cid', $formName); ?>
							</span>
							<?php if ($showToolbar):?>
								<?php if ($item->checked_out) : ?>
									<?php echo HTMLHelper::_(
										'rgrid.checkedout',
										$i,
										$item->checked_out > 0 ? Factory::getUser($item->checked_out)->get('name') : '',
										$item->checked_out_time,
										'myfavoritelists.',
										$canCheckin,
										'cb',
										$formName
									); ?>
								<?php endif; ?>

							<?php endif;?>
							<a href="<?php echo RedshopbRoute::_($data['itemLink'] . '&id=' . $item->id); ?>">
								<?php echo $this->escape($item->name);?>
							</a>
						</td>
						<td>
							<?php if ($item->visible_others == 1): ?>
								<?php echo Text::_('JYES'); ?>
							<?php else: ?>
								<?php echo Text::_('JNO'); ?>
							<?php endif; ?>
						</td>
						<td>
							<?php echo HTMLHelper::_('date', $item->created_date, Text::_('DATE_FORMAT_LC4')); ?>
						</td>
						<?php if ($isManage): ?>
							<td>
								<?php echo $item->username ?>
							</td>
							<td>
								<?php echo $item->id; ?>
							</td>
						<?php endif; ?>

						<td><?php if (!$needChangeImpersonation) : ?><a class="btn btn-default pull-right"
							   href="javascript:void(0);"
							   onclick="return listItemTaskForm('cb<?php echo $i ?>','myfavoritelists.checkout','<?php echo $formName ?>')">
								<?php echo Text::_('COM_REDSHOPB_MYFAVORITELIST_CHECK_OUT') ?>
							<?php endif; ?></td>
					</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
	</div>
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
	<?php echo HTMLHelper::_('form.token'); ?>
</form>

<?php if (empty($items)):?>
	<?php echo RedshopbLayoutHelper::render('common.nodata'); ?>
<?php endif;
