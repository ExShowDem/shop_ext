<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Views
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;
JLoader::import('helpers.layout', JPATH_COMPONENT_ADMINISTRATOR);

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;

HTMLHelper::_('vnrbootstrap.tooltip');
HTMLHelper::_('rjquery.chosen', 'select');

$action       = RedshopbRoute::_('index.php?option=com_redshopb&view=filter_fieldsets');
$listOrder    = $this->state->get('list.ordering');
$listDirn     = $this->state->get('list.direction');
$layoutHelper = new RedshopbHelperLayout;
$user         = Factory::getUser();

// Global ACL permissions since there is no company property over layouts
$canChange  = RedshopbHelperACL::getPermission('manage', 'filter_fieldset', Array('edit.state'), false);
$canEdit    = RedshopbHelperACL::getPermission('manage', 'filter_fieldset', Array('edit', 'edit.own'), false);
$canCheckin = $canEdit;

echo RedshopbBrowserBreadcrumbs::renderBreadcrumbs();
RedshopbHtml::loadFooTable();
?>
<script type="text/javascript">
	var rsbftTablet = 960;
	var rsbftPhone = 768;
</script>
<div class="redshopb-layouts">
	<form action="<?php echo $action; ?>" name="adminForm" class="adminForm" id="adminForm" method="post">
		<div class="row-fluid">
			<?php
			echo RedshopbLayoutHelper::render(
				'searchtools.default',
				array(
					'view' => $this,
					'options' => array(
						'filterButton' => true,
						'searchField' => 'search_filter_fieldsets',
						'searchFieldSelector' => '#filter_search_filter_fieldsets',
						'limitFieldSelector' => '#list_layout_limit',
						'activeOrder' => $listOrder,
						'activeDirection' => $listDirn
					)
				)
			);
			echo $this->form->getInput('filter_fieldsets_order');
			?>
		</div>
		<?php if (empty($this->items)): ?>
			<?php echo RedshopbLayoutHelper::render('common.nodata'); ?>
		<?php else: ?>
			<hr />
			<div class="row-fluid">
				<table class="table table-striped table-hover footable js-redshopb-footable redshopb-footable toggle-circle-filled" id="filter_fieldsetList">
					<thead>
						<tr>
							<th width="1%">
								<input type="checkbox" name="checkall-toggle" value=""
									   title="<?php echo Text::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)"/>
							</th>
							<th width="5%" class="nowrap center">
								<?php echo HTMLHelper::_('rsearchtools.sort', 'JSTATUS', 'ffs.state', $listDirn, $listOrder); ?>
							</th>
							<th class="nowrap" data-toggle="true">
								<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_NAME', 'ffs.name', $listDirn, $listOrder); ?>
							</th>
						</tr>
					</thead>
					<tbody>
					<?php foreach ($this->items as $i => $item): ?>
						<tr>
							<td>
								<?php echo HTMLHelper::_('grid.id', $i, $item->id); ?>
							</td>
							<td>
								<?php echo HTMLHelper::_('rgrid.published', $item->state, $i, 'filter_fieldsets.', $canChange, 'cb'); ?>
							</td>
							<td>
								<?php if ($canEdit) : ?>
								<a href="<?php echo RedshopbRoute::_('index.php?option=com_redshopb&task=filter_fieldset.edit&id=' . $item->id); ?>">
								<?php endif; ?>
									<?php echo $this->escape($item->name); ?>

									<?php if ($canEdit) : ?>
								</a>
									<?php endif; ?>
							</td>
						</tr>
					<?php endforeach;?>
					</tbody>
				</table>
			<div class="redshopb-layouts-pagination">
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
