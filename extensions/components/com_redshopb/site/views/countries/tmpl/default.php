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

$action    = RedshopbRoute::_('index.php?option=com_redshopb&view=countries');
$listOrder = $this->state->get('list.ordering');
$listDirn  = $this->state->get('list.direction');
$saveOrder = $listOrder == 'ordering';

// Global ACL permissions since there is no company property over currencies
$canChange  = RedshopbHelperACL::getPermission('manage', 'product', Array('edit.state'), false);
$canEdit    = RedshopbHelperACL::getPermission('manage', 'product', Array('edit','edit.own'), false);
$canCheckin = $canEdit;

echo RedshopbBrowserBreadcrumbs::renderBreadcrumbs();

?>
<div class="redshopb-currencies">
	<form action="<?php echo $action; ?>" name="adminForm" class="adminForm" id="adminForm" method="post">

		<?php
		echo RedshopbLayoutHelper::render(
			'searchtools.default',
			array(
				'view' => $this,
				'options' => array(
					'filterButton' => false,
					'searchField' => 'search_countries',
					'searchFieldSelector' => '#filter_search_countries',
					'limitFieldSelector' => '#list_country_limit',
					'activeOrder' => $listOrder,
					'activeDirection' => $listDirn
				)
			)
		);
		?>

		<hr/>
		<?php if (empty($this->items)) : ?>
			<?php echo RedshopbLayoutHelper::render('common.nodata'); ?>
		<?php else : ?>
			<div class="redshopb-currencies-table">
				<table class="table table-striped table-hover" id="countryList">
					<thead>
					<tr>
						<th width="1%" class="hidden-phone">
							<input type="checkbox" name="checkall-toggle" value=""
								   title="<?php echo Text::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)"/>
						</th>
						<th class="nowrap hidden-phone">
							<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_NAME', 'c.name', $listDirn, $listOrder); ?>
						</th>
						<th class="nowrap hidden-phone">
							<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_COUNTRY_ALPHA2_LBL', 'c.alpha2', $listDirn, $listOrder); ?>
						</th>
						<th class="nowrap hidden-phone">
							<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_COUNTRY_ALPHA3_LBL', 'c.alpha3', $listDirn, $listOrder); ?>
						</th>
						<th class="nowrap hidden-phone">
							<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_COUNTRY_NUMERIC_LBL', 'c.numeric', $listDirn, $listOrder); ?>
						</th>
						<th class="nowrap hidden-phone">
							<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_COUNTRY_EU_ZONE_LABEL', 'c.eu_zone', $listDirn, $listOrder); ?>
						</th>
						<th width="1%" class="nowrap hidden-phone">
							<?php echo HTMLHelper::_('rsearchtools.sort', 'JGRID_HEADING_ID', 'c.id', $listDirn, $listOrder); ?>
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
											$item->checked_out_time, 'countries.', $canCheckin
										); ?>
									<?php endif; ?>

									<?php if ($canEdit && $item->editAllowed) : ?>
									<a href="<?php echo RedshopbRoute::_('index.php?option=com_redshopb&task=country.edit&id=' . $item->id); ?>">
									<?php endif; ?>
										<?php echo $this->escape($item->name); ?>

									<?php if ($canEdit && $item->editAllowed) : ?>
									</a>
									<?php endif; ?>
								</td>
								<td>
									<?php echo $this->escape($item->alpha2); ?>
								</td>
								<td>
									<?php echo $this->escape($item->alpha3); ?>
								</td>
								<td>
									<?php echo $this->escape($item->numeric); ?>
								</td>
								<td>
									<?php
									if ($item->eu_zone)
									{
										echo Text::_('JYES');
									}
									else
									{
										echo Text::_('JNO');
									}
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
