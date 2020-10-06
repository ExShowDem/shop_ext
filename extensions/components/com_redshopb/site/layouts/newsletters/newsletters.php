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

HTMLHelper::_('vnrbootstrap.tooltip');
HTMLHelper::_('rjquery.chosen', 'select');

RedshopbHtml::loadFooTable();

foreach (get_object_vars($displayData['this']) as $key => $value)
{
	$this->$key = $value;
}

$formName  = (isset($displayData['formName'])) ? $displayData['formName'] : 'adminForm';
$action    = $displayData['action'];
$listOrder = $this->state->get('list.ordering');
$listDirn  = $this->state->get('list.direction');
?>

<div class="redshopb-newsletters">
	<form action="<?php echo $action; ?>" name="adminForm" class="adminForm" id="adminForm" method="post">
		<?php
		echo RedshopbLayoutHelper::render(
			'searchtools.default',
			array(
				'view' => $this,
				'options' => array(
					'searchField'         => 'search_newsletters',
					'searchFieldSelector' => '#filter_search_newsletters',
					'limitFieldSelector'  => '#list_newsletters_limit',
					'activeOrder'         => $listOrder,
					'activeDirection'     => $listDirn
				)
			)
		);
		?>

		<hr/>

		<?php if (empty($this->items)): ?>
			<?php echo RedshopbLayoutHelper::render('common.nodata'); ?>
		<?php else: ?>
			<table class="table table-striped table-hover footable js-redshopb-footable redshopb-footable toggle-circle-filled" id="newsletters">
				<thead>
					<tr>
						<th width="1%">
							<input type="checkbox" name="checkall-toggle" value="" title="<?php echo Text::_('JGLOBAL_CHECK_ALL'); ?>"
								onclick="Joomla.checkAll(this)" />
						</th>
						<th width="1%" class="nowrap">
							<?php echo HTMLHelper::_('rsearchtools.sort', 'JSTATUS', 'nl.state', $listDirn, $listOrder); ?>
						</th>
						<th class="nowrap" data-toggle="true">
							<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_NEWSLETTER_NAME', 'nl.name', $listDirn, $listOrder); ?>
						</th>
						<th width="20%" class="nowrap" data-hide="phone">
							<?php echo Text::_('COM_REDSHOPB_COMPANY_LABEL'); ?>
						</th>
						<th width="1%" class="nowrap" data-hide="phone">
							<?php echo Text::_('COM_REDSHOPB_ID'); ?>
						</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($this->items as $i => $item): ?>
						<?php
						$canChange  = RedshopbHelperACL::getPermission('manage', 'product', Array('edit.state'), true);
						$canEdit    = RedshopbHelperACL::getPermission('manage', 'product', Array('edit','edit.own'), true);
						$canCheckin = $canEdit;
						?>
					<tr>
						<td>
							<?php echo HTMLHelper::_('grid.id', $i, $item->id); ?>
						</td>
						<td>
							<?php echo HTMLHelper::_('rgrid.published', $item->state, $i, 'newsletters.', $canChange, 'cb'); ?>
						</td>
						<td>
							<?php if ($item->checked_out) : ?>
								<?php echo HTMLHelper::_('rgrid.checkedout', $i, $item->checked_out > 0 ? Factory::getUser($item->checked_out)->get('name') : '', $item->checked_out_time, 'newsletters.', $canCheckin); ?>
							<?php endif; ?>

							<?php if ($canEdit) : ?>
							<a href="<?php echo RedshopbRoute::_('index.php?option=com_redshopb&task=newsletter.edit&id=' . $item->id); ?>">
							<?php endif; ?>
							<?php echo $this->escape($item->name); ?>

							<?php if ($canEdit) : ?>
							</a>
							<?php endif; ?>
						</td>
						<td>
							<?php // @TODO: Need to know which company should display here. Company of owner or company inside the criteria ?>
						</td>
						<td>
							<?php echo $item->id ?>
						</td>
					</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif; ?>
		<div>
			<input type="hidden" name="task" value="">
			<input type="hidden" name="boxchecked" value="0">
			<?php echo HTMLHelper::_('form.token'); ?>
		</div>
	</form>
</div>
