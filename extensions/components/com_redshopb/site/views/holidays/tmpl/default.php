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

HTMLHelper::_('rbootstrap.tooltip');
HTMLHelper::_('rjquery.chosen', 'select');

?>
<script type="text/javascript">
	var rsbftTablet = 960;
	var rsbftPhone = 768;
</script>
<?php
RedshopbHtml::loadFooTable();

$action    = RedshopbRoute::_('index.php?option=com_redshopb&view=holidays');
$listOrder = $this->state->get('list.ordering');
$listDirn  = $this->state->get('list.direction');
$saveOrder = $listOrder == 'ordering';

// Global ACL permissions since there is no company property over currencies
$canChange  = RedshopbHelperACL::getPermission('manage', 'product', Array('edit.state'), false);
$canEdit    = RedshopbHelperACL::getPermission('manage', 'product', Array('edit', 'edit.own'), false);
$canCheckin = $canEdit;

?>
<div class="redshopb-holidays">
	<form action="<?php echo $action; ?>" name="adminForm" class="adminForm" id="adminForm" method="post">

		<?php
		echo RedshopbLayoutHelper::render(
			'searchtools.default',
			array(
				'view'    => $this,
				'options' => array(
					'filterButton'        => false,
					'searchField'         => 'search_holidays',
					'searchFieldSelector' => '#filter_search_holidays',
					'limitFieldSelector'  => '#list_country_limit',
					'activeOrder'         => $listOrder,
					'activeDirection'     => $listDirn
				)
			)
		);
		?>

		<hr/>
		<?php if (empty($this->items)) : ?>
			<?php echo RedshopbLayoutHelper::render('redshopb.common.nodata'); ?>
		<?php else : ?>
			<div class="redshopb-currencies-table">
				<table class="table table-hover footable js-redshopb-footable redshopb-footable toggle-circle-filled js-redshopb-tree-order"
					   id="holidayList">
					<thead>
					<tr>
						<th width="1%" class="hidden-phone">
							<input type="checkbox" name="checkall-toggle" value=""
								   title="<?php echo Text::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)"/>
						</th>
						<th class="nowrap hidden-phone">
							<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_TITLE', 'c.title', $listDirn, $listOrder); ?>
						</th>
						<th class="nowrap hidden-phone">
							<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_COUNTRY_FORM_TITLE', 'c.country', $listDirn, $listOrder); ?>
						</th>
						<th class="nowrap hidden-phone">
							<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_DATE', 'c.month', $listDirn, $listOrder); ?>
						</th>
					</tr>
					</thead>
					<?php if ($this->items) : ?>
						<tbody>
						<?php foreach ($this->items as $i => $item): ?>
							<tr>
								<td>
									<?php echo HTMLHelper::_('grid.id', $i, $item->id); ?>
								</td>
								<td>
									<?php if ($item->checked_out) : ?>
										<?php echo HTMLHelper::_('rgrid.checkedout', $i, $item->checked_out > 0 ? Factory::getUser($item->checked_out)->get('name') : '',
											$item->checked_out_time, 'holidays.', $canCheckin
										); ?>
									<?php endif; ?>

									<?php if ($canEdit && $item->editAllowed) : ?>
									<a href="<?php echo RedshopbRoute::_('index.php?option=com_redshopb&task=holiday.edit&id=' . $item->id); ?>">
									<?php endif; ?>
										<?php echo $this->escape($item->name); ?>

										<?php if ($canEdit && $item->editAllowed) : ?>
									</a>
										<?php endif; ?>
								</td>
								<td>
									<?php echo $this->escape(Text::_($item->country)); ?>
								</td>
								<td>
									<?php echo $this->escape($item->day) . " / " . $this->escape($item->month); ?>

									<?php if (!empty($this->escape($item->year))) : ?>
										<?php echo " / " . $this->escape($item->year); ?>
									<?php endif; ?>
								</td>
							</tr>
						<?php endforeach; ?>
						</tbody>
					<?php endif; ?>
				</table>
			</div>
			<div class="redshopb-currencies-pagination">
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
