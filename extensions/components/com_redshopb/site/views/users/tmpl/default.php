<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Views
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('rdropdown.init');
HTMLHelper::_('vnrbootstrap.tooltip');
HTMLHelper::_('rjquery.chosen', 'select');
?>
<?php
RedshopbHtml::loadFooTable();

$action    = RedshopbRoute::_('index.php?option=com_redshopb&view=users');
$listOrder = $this->state->get('list.ordering');
$listDirn  = $this->state->get('list.direction');
$saveOrder = $listOrder == 'ordering';

echo RedshopbBrowserBreadcrumbs::renderBreadcrumbs();
?>
<script>
jQuery(document).ready(function() {
	var csvButton = jQuery('#main > div.redcore div.btn-group.pull-right button:contains("Csv")');
	var onclick   = csvButton.attr('onclick');

	csvButton.removeAttr('onclick');

	csvButton.on('click', function(){
		var url = onclick.slice(onclick.indexOf('http'),-2);
		redSHOPB.ajax.generateCsvFile("users", "#userList", url);
	});
});
</script>
<script type="text/javascript">
	function JActivateUser(id)
	{
		jQuery('input[type="checkbox"][name="cid\[\]"][value="' + id + '"]').click();
		Joomla.submitbutton('users.activate');
	}
	function JBlockUser(id)
	{
		jQuery('input[type="checkbox"][name="cid\[\]"][value="' + id + '"]').click();
		Joomla.submitbutton('users.block');
	}
</script>
<div class="redshopb-users">
	<form action="<?php echo $action; ?>" name="adminForm" class="adminForm" id="adminForm" method="post">

		<?php
		echo RedshopbLayoutHelper::render(
			'searchtools.default',
			array(
				'view' => $this,
				'options' => array(
					'searchField' => 'search_users',
					'searchFieldSelector' => '#filter_search_users',
					'limitFieldSelector' => '#list_user_limit',
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
			<div class="redshopb-users-table">
				<table class="table table-striped table-hover footable js-redshopb-footable redshopb-footable toggle-circle-filled" id="userList">
					<thead>
					<tr>
						<th width="1%">
							<input type="checkbox" name="checkall-toggle" value=""
								   title="<?php echo Text::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)"/>
						</th>
						<th  data-toggle="true">
							<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_USER_NUMBER_LABEL', 'u.employee_number', $listDirn, $listOrder); ?>
						</th>
						<th  data-toggle="true">
							<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_NAME', 'u.name', $listDirn, $listOrder); ?>
						</th>
						<th  data-toggle="true">
							<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_USER_FIELD_USERNAME_LABEL', 'j.username', $listDirn, $listOrder); ?>
						</th>
						<th width="20%" class="nowrap" data-hide="tablet,phone">
							<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_COMPANY_LABEL', 'u.company', $listDirn, $listOrder); ?>
						</th>
						<th width="20%" class="nowrap" data-hide="tablet,phone">
							<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_DEPARTMENT_LABEL', 'u.department', $listDirn, $listOrder); ?>
						</th>
						<th width="1%" class="nowrap" data-hide="phone">
							<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_USER_ACTIVATION', 'u.block', $listDirn, $listOrder); ?>
						</th>
						<th width="20%" class="nowrap" data-hide="phone">
							<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_ROLE_LABEL', 'u.role', $listDirn, $listOrder); ?>
						</th>
						<th width="1%" class="nowrap" data-hide="phone">
							<?php echo HTMLHelper::_('rsearchtools.sort', 'JGRID_HEADING_ID', 'u.id', $listDirn, $listOrder); ?>
						</th>
					</tr>
					</thead>
					<?php if ($this->items): ?>
						<tbody>
						<?php foreach ($this->items as $i => $item): ?>
							<?php
							$canEdit    = RedshopbHelperACL::getPermission('manage', 'user', Array('edit','edit.own'), true);
							$canCheckin = $canEdit;
							?>
							<tr>
								<td>
									<?php echo HTMLHelper::_('grid.id', $i, $item->id); ?>
								</td>
								<td>
									<?php echo $item->employee_number; ?>
								</td>
								<td>
									<?php if ($item->checked_out) : ?>
										<?php echo HTMLHelper::_('rgrid.checkedout', $i, $item->checked_out > 0 ? Factory::getUser($item->checked_out)->get('name') : '',
											$item->checked_out_time, 'users.', $canCheckin
										); ?>
									<?php endif; ?>

									<?php if ($canEdit) : ?>
										<a href="<?php echo RedshopbRoute::_('index.php?option=com_redshopb&task=user.edit&id=' . $item->id); ?>">
									<?php endif; ?>
										<?php echo $this->escape($item->name); ?>

									<?php if ($canEdit) : ?>
										</a>
									<?php endif; ?>
								</td>
								<td>
									<?php echo $this->escape($item->username); ?>
								</td>
								<td>
									<?php echo $this->escape($item->company); ?>
								</td>
								<td>
									<?php echo $this->escape($item->department); ?>
								</td>
								<td>
									<?php if ((bool) $item->block) : ?>
									<?php if ($canEdit): ?><a href="#" onclick="JActivateUser('<?php echo $item->id; ?>')"><?php
									endif; ?>
										<span class="badge badge-important"> <?php echo Text::_('JBLOCKED'); ?></span>

									<?php if ($canEdit): ?></a><?php
									endif; ?>
									<?php else : ?>
									<?php if ($canEdit): ?><a href="#" onclick="JBlockUser('<?php echo $item->id; ?>')"><?php
									endif; ?>
										<span class="badge badge-success"> <?php echo Text::_('JENABLED'); ?></span>

									<?php if ($canEdit): ?></a><?php
									endif; ?>
									<?php endif; ?>
								</td>
								<td>
									<?php echo $this->escape($item->role); ?>
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
			<div class="redshopb-users-pagination">
				<?php echo $this->pagination->getPaginationLinks(null, array('showLimitBox' => false)); ?>
			</div>
		<?php endif; ?>

		<?php
			echo RedshopbLayoutHelper::render(
				'user.wallet.actions',
				array(
					'formName' => 'adminForm',
					'view' => 'list',
					'filterForm' => $this->filterForm
				)
			);
		?>

		<div>
			<input type="hidden" name="task" value="">
			<input type="hidden" name="boxchecked" value="0">
			<?php echo HTMLHelper::_('form.token'); ?>
		</div>
	</form>
</div>
