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

$action    = RedshopbRoute::_('index.php?option=com_redshopb&view=myoffers');
$listOrder = $this->state->get('list.ordering');
$listDirn  = $this->state->get('list.direction');

$user = Factory::getUser();
RedshopbHtml::loadFooTable();

echo RedshopbBrowserBreadcrumbs::renderBreadcrumbs();
?>
<script type="text/javascript" language="javascript">
	jQuery(document).ready(function(){
		jQuery('[data-toggle="modal"]').on('click', function() {
			jQuery(jQuery(this).attr('href')).data('trigger', this);
		});
		jQuery('#myOfferModal').on('show.bs.modal', function () {
			var $invoker = jQuery(jQuery(this).data('trigger'));
			var source = $invoker.data('source');
			jQuery('#modalIdField').val(source);
		});
	});
</script>
<div class="redshopb-myoffers">
	<form action="<?php echo $action; ?>" name="adminForm" class="adminForm" id="adminForm" method="post">
	<?php
		echo RedshopbLayoutHelper::render(
			'searchtools.default',
			array(
				'view' => $this,
				'options' => array(
					'filtersHidden' => true,
					'searchField' => 'search_myoffers',
					'searchFieldSelector' => '#filter_search_myoffers',
					'limitFieldSelector' => '#list_myoffer_limit',
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
				<div class="redshopb-myoffers-table">
					<table class="table table-striped table-hover footable js-redshopb-footable redshopb-footable toggle-circle-filled" id="addressList">
						<thead>
							<tr>
								<th width="1%">
									<input type="checkbox" name="checkall-toggle" value="" title="<?php echo Text::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)"/>
								</th>
								<th></th>
								<th class="nowrap center" data-toggle="true">
									<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_MYOFFERS_NAME', 'off.name', $listDirn, $listOrder); ?>
								</th>
								<th class="nowrap" data-hide="phone">
									<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_MYOFFERS_NO_OF_PRODUCTS_LABEL', 'off.count_products', $listDirn, $listOrder); ?>
								</th>
								<th class="nowrap">
									<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_MYOFFERS_STATUS_LABEL', 'off.status', $listDirn, $listOrder); ?>
								</th>
								<?php if ($this->canImpersonate): ?>
									<th class="nowrap" >
										<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_OFFERS_CUSTOMER_TYPE_LABEL', 'customer_type', $listDirn, $listOrder); ?>
									</th>
									<th class="nowrap" >
										<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_OFFERS_CUSTOMER_NAME_LABEL', 'customer_name', $listDirn, $listOrder); ?>
									</th>
								<?php endif; ?>
								<th class="nowrap" data-hide="phone, tablet">
									<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_MYOFFERS_TOTAL_PRICE_LABEL', 'off.total', $listDirn, $listOrder); ?>
								</th>
								<th width="1%" class="nowrap" data-hide="phone, tablet">
									<?php echo HTMLHelper::_('rsearchtools.sort', 'JGRID_HEADING_ID', 'off.id', $listDirn, $listOrder); ?>
								</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($this->items as $i => $item): ?>
							<tr>
								<td>
									<?php echo HTMLHelper::_('rgrid.id', $i, $item->id, false, 'cid', 'adminForm'); ?>
								</td>
								<td>
									<div class="btn-group">
										<?php
										if (in_array($item->status, array('accepted')))
										{
											echo HTMLHelper::_(
												'rgrid.action', $i, 'checkoutCart', 'myoffer.', '', 'COM_REDSHOPB_SHOP_CHECKOUT',
												'', true, 'shopping-cart', '', true, true, 'cb', 'adminForm', ''
											);
										}

										if (in_array($item->status, array('sent')))
										{
											?>
											<a class="btn btn-small btn-sm btn-success hasTooltip" href="#myOfferModal"  data-toggle="modal" role="button" data-source="<?php
											echo $item->id; ?>" data-original-title="<?php
						echo Text::_('COM_REDSHOPB_OFFER_ACCEPT_LBL'); ?>">
																	<i class="icon-thumbs-up-alt"></i>
																</a>
																	<?php
										}

										if (in_array($item->status, array('sent', 'accepted')))
										{
											echo HTMLHelper::_(
												'rgrid.action', $i, 'reject', 'myoffers.', '', 'COM_REDSHOPB_OFFER_REJECT_LBL',
												'', true, 'thumbs-down-alt', '', true, true, 'cb', 'adminForm', 'btn-warning'
											);
										}

										if (!in_array($item->status, array('ordered')))
										{
											echo HTMLHelper::_(
												'rgrid.action', $i, 'delete', 'myoffers.', '', 'COM_REDSHOPB_OFFER_DELETE_LBL',
												'', true, 'trash', '', true, true, 'cb', 'adminForm', 'btn-danger'
											);
										}
										?>
									</div>
								</td>
								<td>
									<a href="<?php echo RedshopbRoute::_('index.php?option=com_redshopb&task=myoffer.edit&id=' . $item->id); ?>">
									<?php echo $this->escape($item->name);?>
									</a>
								</td>
								<td>
								<?php echo $item->count_products;?>
								</td>
								<td>
								<?php echo RedshopbHelperOffers::getColorForStatus($item->status);?>
								</td>
								<?php if ($this->canImpersonate): ?>
									<td>
										<?php echo Text::_('COM_REDSHOPB_OFFERS_CUSTOMER_TYPE_' . strtoupper($item->customer_type)); ?>
									</td>
									<td>
										<?php echo $item->customer_name;?>
									</td>
								<?php endif; ?>
								<td>
								<?php echo $item->total;?>
								</td>
								<td>
									<?php echo $item->id;?>
								</td>
							</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			<?php endif; ?>
			<div>
				<input type="hidden" name="task" value="">
				<input type="hidden" name="boxchecked" value="0">
				<?php echo HTMLHelper::_('form.token'); ?>
			</div>
		</div>
	</form>
	<?php echo RHtml::_(
		'vnrbootstrap.renderModal', 'myOfferModal',
		array(
				'title' => Text::_('COM_REDSHOPB_OFFER_ADD_OFFER_TO_CART_LBL')
			),
		RedshopbLayoutHelper::render('myoffer.acceptform')
	); ?>
</div>
