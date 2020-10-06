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

HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('rdropdown.init');
HTMLHelper::_('vnrbootstrap.tooltip');
HTMLHelper::_('rjquery.chosen', 'select');
?>

<script type="text/javascript">
	var rsbftTablet = 960;
	var rsbftPhone = 768;
</script>

<?php
RedshopbHtml::loadFooTable();

$listOrder = $this->state->get('list.ordering');
$listDirn  = $this->state->get('list.direction');
$canEdit   = RedshopbHelperACL::getPermission('manage', 'mainwarehouse', array('edit', 'edit.own'), true);
$canChange = RedshopbHelperACL::getPermission('manage', 'mainwarehouse', Array('edit.state'), true);

echo RedshopbBrowserBreadcrumbs::renderBreadcrumbs();
?>
<div class="redshopb-wash_care_specs">
	<form action="<?php echo RedshopbRoute::_('index.php?option=com_redshopb&view=wash_care_specs'); ?>" name="adminForm" class="adminForm" id="adminForm" method="post">
		<?php
			echo RedshopbLayoutHelper::render(
				'searchtools.default',
				array(
					'view' => $this,
					'options' => array(
						'searchField' => 'search_wash_care_specs',
						'searchFieldSelector' => '#filter_search_wash_care_specs',
						'limitFieldSelector' => '#list_wash_care_specs_limit',
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
			<div class="redshopb-wash_care_specs-table">
				<table class="table table-striped table-hover footable js-redshopb-footable washCareSpecList redshopb-footable toggle-circle-filled" id="washCareSpecList">
					<thead>
						<tr>
							<th width="1%">
								<input type="checkbox" name="checkall-toggle" value=""
									   title="<?php echo Text::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)"/>
							</th>
							<th width="1%" class="nowrap">
								<?php echo HTMLHelper::_('rsearchtools.sort', 'JSTATUS', 'wcs.state', $listDirn, $listOrder); ?>
							</th>
							<th class="nowrap">
								<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_WASH_CARE_SPEC_TYPE_CODE_LABEL', 'wcs.type_code', $listDirn, $listOrder); ?>
							</th>
							<th class="nowrap">
								<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_WASH_CARE_SPEC_CODE_LABEL', 'wcs.code', $listDirn, $listOrder); ?>
							</th>
							<th>
								<?php echo Text::_('COM_REDSHOPB_WASH_CARE_SPEC_DESCRIPTION_LABEL'); ?>
							</th>
							<th width="1%" class="nowrap" data-hide="phone">
								<?php echo HTMLHelper::_('rsearchtools.sort', 'JGRID_HEADING_ID', 'wcs.id', $listDirn, $listOrder); ?>
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
								<?php echo HTMLHelper::_('rgrid.published', $item->state, $i, 'wash_care_specs.', $canChange, 'cb'); ?>
							</td>
							<td>
								<?php echo $this->escape($item->type_code); ?>
							</td>
							<td>
								<?php if ($canEdit) :
									$itemUrl = 'index.php?option=com_redshopb&task=wash_care_spec.edit&id=' . $item->id;
								?>
								<a href="<?php echo RedshopbRoute::_($itemUrl); ?>">
								<?php endif; ?>
									<?php echo $this->escape($item->code); ?>

									<?php if ($canEdit) : ?>
								</a>
									<?php endif; ?>
							</td>
							<td>
								<?php echo $this->escape(strip_tags($item->description)); ?>
							</td>
							<td>
								<?php echo $item->id; ?>
							</td>
						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>
			</div>
			<div class="redshopb-wash_care_specs-pagination">
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
