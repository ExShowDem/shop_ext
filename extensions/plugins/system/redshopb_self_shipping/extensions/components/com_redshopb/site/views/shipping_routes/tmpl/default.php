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

HTMLHelper::_('vnrbootstrap.tooltip');
HTMLHelper::_('rjquery.chosen', 'select');

$action    = RedshopbRoute::_('index.php?option=com_redshopb&view=shipping_routes');
$listOrder = $this->state->get('list.ordering');
$listDirn  = $this->state->get('list.direction');
$saveOrder = $listOrder == 'ordering';
?>
<?php
RedshopbHtml::loadFooTable();
echo RedshopbBrowserBreadcrumbs::renderBreadcrumbs();
?>
<div class="redshopb-shipping_routes">
	<form action="<?php echo $action; ?>" name="adminForm" class="adminForm" id="adminForm" method="post">

		<?php
		echo RedshopbLayoutHelper::render(
			'searchtools.default',
			array(
				'view' => $this,
				'options' => array(
					'searchField' => 'search_shipping_routes',
					'searchFieldSelector' => '#filter.search_shipping_routes',
					'limitFieldSelector' => '#list_shipping_routes_limit',
					'activeOrder' => $listOrder,
					'activeDirection' => $listDirn,
					'filterButton' => false
				)
			)
		);
		?>
		<hr/>
		<?php if (empty($this->items)) : ?>
			<?php echo RedshopbLayoutHelper::render('common.nodata'); ?>
		<?php else : ?>
			<div class="redshopb-shipping_rates-table">
				<table class="table table-hover footable js-redshopb-footable redshopb-footable toggle-circle-filled js-redshopb-tree-order" id="shipping_routeList">
					<thead>
					<tr>
						<th width="1%">
							<input type="checkbox" name="checkall-toggle" value=""
								   title="<?php echo Text::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)"/>
						</th>
						<th width="1%" class="nowrap center">
							<?php echo HTMLHelper::_('rsearchtools.sort', 'JSTATUS', 'sr.state', $listDirn, $listOrder); ?>
						</th>
						<th class="nowrap" data-toggle="true">
							<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_NAME', 'sr.name', $listDirn, $listOrder); ?>
						</th>
						<th class="nowrap" data-toggle="true">
							<?php echo Text::_('COM_REDSHOPB_COMPANY'); ?>
						</th>
						<th class="nowrap" data-toggle="true">
							<?php echo Text::_('PLG_SYSTEM_REDSHOPB_SHIPPING_ROUTES_SELECTED_DAYS'); ?>
						</th>
						<th class="nowrap" data-toggle="true">
							<?php echo HTMLHelper::_('rsearchtools.sort', 'PLG_SYSTEM_REDSHOPB_SHIPPING_ROUTES_DELIVERY_TIME', 'sr.max_delivery_time', $listDirn, $listOrder); ?>
						</th>
						<th class="nowrap" data-toggle="true">
							<?php echo Text::_('PLG_SYSTEM_REDSHOPB_SHIPPING_ROUTES_DELIVERY_ADDRESSES'); ?>
						</th>
						<th width="1%" class="nowrap" data-hide="phone">
							<?php echo HTMLHelper::_('rsearchtools.sort', 'JGRID_HEADING_ID', 'sr.id', $listDirn, $listOrder); ?>
						</th>
					</tr>
					</thead>
					<?php if ($this->items): ?>
						<tbody>
						<?php foreach ($this->items as $i => $item): ?>
							<?php
							$canChange  = RedshopbHelperACL::getPermission('manage', 'address', Array('edit.state'), true);
							$canEdit    = RedshopbHelperACL::getPermission('manage', 'address', Array('edit','edit.own'), true);
							$canCheckin = $canEdit;
							?>
							<tr>
								<td>
									<?php echo HTMLHelper::_('grid.id', $i, $item->id); ?>
								</td>
								<td>
									<?php echo HTMLHelper::_('rgrid.published', $item->state, $i, 'shipping_routes.', $canChange, 'cb'); ?>
								</td>
								<td>
									<?php if ($canEdit) : ?>
									<a href="<?php echo RedshopbRoute::_('index.php?option=com_redshopb&task=shipping_route.edit&id=' . $item->id); ?>">
									<?php endif; ?>

										<?php echo $this->escape($item->name); ?>

										<?php if ($canEdit) : ?>
									</a>
										<?php endif; ?>
								</td>
								<td>
									<?php echo $this->escape($item->company_name); ?>
								</td>
								<td>
									<?php echo $item->weekday_1 ? Text::_('PLG_SYSTEM_REDSHOPB_SHIPPING_ROUTE_CHECKBOX_1') . ' ' : ''; ?>
									<?php echo $item->weekday_2 ? Text::_('PLG_SYSTEM_REDSHOPB_SHIPPING_ROUTE_CHECKBOX_2') . ' ' : ''; ?>
									<?php echo $item->weekday_3 ? Text::_('PLG_SYSTEM_REDSHOPB_SHIPPING_ROUTE_CHECKBOX_3') . ' ' : ''; ?>
									<?php echo $item->weekday_4 ? Text::_('PLG_SYSTEM_REDSHOPB_SHIPPING_ROUTE_CHECKBOX_4') . ' ' : ''; ?>
									<?php echo $item->weekday_5 ? Text::_('PLG_SYSTEM_REDSHOPB_SHIPPING_ROUTE_CHECKBOX_5') . ' ' : ''; ?>
									<?php echo $item->weekday_6 ? Text::_('PLG_SYSTEM_REDSHOPB_SHIPPING_ROUTE_CHECKBOX_6') . ' ' : ''; ?>
									<?php echo $item->weekday_7 ? Text::_('PLG_SYSTEM_REDSHOPB_SHIPPING_ROUTE_CHECKBOX_7') . ' ' : ''; ?>
								</td>
								<td>
									<?php echo (new DateTime($item->max_delivery_time))->format('H:i'); ?>
								</td>
								<td>
									<?php
										$addresses = explode(',', $item->address_names);

									foreach ($addresses as $key => $address):
										echo '- ' . $address . ' ' . Text::_($item->country_name) . '<br />';

										if ($key > 5):
											echo '...';
											break;
										endif;
									endforeach;
									?>
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
			<div class="redshopb-shipping_rates-pagination">
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
