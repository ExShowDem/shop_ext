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

$action    = RedshopbRoute::_('index.php?option=com_redshopb&view=offers');
$listOrder = $this->state->get('list.ordering');
$listDirn  = $this->state->get('list.direction');
$saveOrder = $listOrder == 'id';

echo RedshopbBrowserBreadcrumbs::renderBreadcrumbs();
?>

<div class="redshopb-offers">
	<form action="<?php echo $action; ?>" name="adminForm" class="adminForm" id="adminForm" method="post">
		<?php
		echo RedshopbLayoutHelper::render(
			'searchtools.default',
			array(
				'view' => $this,
				'options' => array(
					'searchField' => 'search_offers',
					'searchFieldSelector' => '#filter_search_offers',
					'limitFieldSelector' => '#list_offer_limit',
					'activeOrder' => $listOrder,
					'activeDirection' => $listDirn
				)
			)
		);
		?>
		<hr/>
		<div class="row-fluid">
			<?php if (empty($this->items)): ?>
				<?php echo RedshopbLayoutHelper::render('common.nodata'); ?>
			<?php else: ?>
					<table class="table table-striped table-hover footable js-redshopb-footable " id="offerList">
						<thead>
							<tr>
							<th width="1%">
									<?php echo HTMLHelper::_('rsearchtools.sort', 'JGRID_HEADING_ID', 'id', $listDirn, $listOrder); ?>
								</th>
								<th width="1%">
									<input type="checkbox" name="checkall-toggle" value="" title="<?php echo Text::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)"/>
								</th>
								<th class="nowrap" >
									<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_MYOFFERS_NAME', 'name', $listDirn, $listOrder); ?>
								</th>
								<th class="nowrap center" data-toggle="true">
									<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_OFFERS_STATUS_LABEL', 'status', $listDirn, $listOrder); ?>
								</th>
								<th width="1%" class="nowrap center">
									<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_OFFERS_REVIEW_LABEL', 'off.state', $listDirn, $listOrder); ?>
								</th>
								<th class="nowrap" >
									<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_OFFERS_CUSTOMER_TYPE_LABEL', 'customer_type', $listDirn, $listOrder); ?>
								</th>
								<th class="nowrap" >
									<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_OFFERS_CUSTOMER_NAME_LABEL', 'customer_name', $listDirn, $listOrder); ?>
								</th>
								<th class="nowrap" >
									<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_OFFERS_COLLECTION_LABEL', 'collection_name', $listDirn, $listOrder); ?>
								</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($this->items as $i => $item): ?>
							<tr>
								<td>
									<?php echo $item->id; ?>
								</td>
								<td>
									<?php echo HTMLHelper::_('rgrid.id', $i, $item->id, false, 'cid', 'adminForm'); ?>
								</td>
								<td>
									<?php if ($item->checked_out) : ?>
										<?php echo HTMLHelper::_('rgrid.checkedout', $i, $item->checked_out > 0 ? Factory::getUser($item->checked_out)->get('name') : '',
											$item->checked_out_time, 'offers.', 1
										); ?>
									<?php endif; ?>
									<a href="<?php echo RedshopbRoute::_('index.php?option=com_redshopb&task=offer.edit&id=' . $item->id); ?>">
									<?php echo $this->escape($item->name);?>
									</a>
								</td>
								<td>
									<?php echo RedshopbHelperOffers::getColorForStatus($item->status);?>
								</td>
								<td>
									<?php
									if ($item->state)
									{
										echo HTMLHelper::_(
											'rgrid.action', $i, 'reject', 'offers.', '', '',
											'COM_REDSHOPB_OFFER_NOT_NEED_REVIEW_LBL', true, '', 'thumbs-up-alt', false, true, 'cb', 'adminForm', 'btn-success'
										);
									}
									else
									{
										echo HTMLHelper::_(
											'rgrid.action', $i, 'reject', 'offers.', '', '',
											'COM_REDSHOPB_OFFER_NEED_REVIEW_LBL', true, '', 'thumbs-down-alt', false, true, 'cb', 'adminForm', 'btn-warning'
										);
									}
									?>
								</td>
								<td>
								<?php echo $item->customer_type;?>
								</td>
								<td>
									<?php echo $item->customer_name;?>
								</td>
								<td>
									<?php echo $item->collection_name;?>
								</td>
							</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
					<div class="redshopb-companies-pagination">
						<?php echo $this->pagination->getPaginationLinks(null, array('showLimitBox' => false)); ?>
					</div>
			<?php endif; ?>
			<div>
				<input type="hidden" name="task" value="">
				<input type="hidden" name="boxchecked" value="0">
				<?php echo HTMLHelper::_('form.token'); ?>
			</div>
		</div>
	</form>
</div>
