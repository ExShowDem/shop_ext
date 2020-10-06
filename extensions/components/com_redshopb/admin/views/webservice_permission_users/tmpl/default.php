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
<?php
RedshopbHtml::loadFooTable();

$action    = RedshopbRoute::_('index.php?option=com_redshopb&view=webservice_permission_users');
$listOrder = $this->state->get('list.ordering');
$listDirn  = $this->state->get('list.direction');
$saveOrder = $listOrder == 'ordering';

?>
<div class="redshopb-webservice_permissions">
	<form action="<?php echo $action; ?>" name="adminForm" class="adminForm" id="adminForm" method="post">

		<?php
		echo RedshopbLayoutHelper::render(
			'searchtools.default',
			array(
				'view' => $this,
				'options' => array(
					'searchField' => 'search_webservice_permission_users',
					'searchFieldSelector' => '#filter_search_webservice_permission_users',
					'limitFieldSelector' => '#list_webservice_permission_users_limit',
					'activeOrder' => $listOrder,
					'activeDirection' => $listDirn
				)
			)
		);
		?>

		<hr/>
		<?php if (empty($this->items)) : ?>
			<?php echo RedshopbLayoutHelper::render('redshopb.common.nodata'); ?>
		<?php else : ?>
			<div class="redshopb-webservice_permissions-table">
				<table class="table table-striped table-hover footable js-redshopb-footable redshopb-footable toggle-circle-filled" id="webservice_permissionList">
					<thead>
					<tr>
						<th width="1%">
							<input type="checkbox" name="checkall-toggle" value=""
								   title="<?php echo Text::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)"/>
						</th>
						<th data-toggle="true">
							<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_WEBSERVICE_PERMISSION_USER_TITLE', 'u.name', $listDirn, $listOrder); ?>
						</th>
						<?php foreach ($this->permissions as $scope => $permissions) : ?>
							<th data-toggle="true" class="nowrap center">
								<?php echo Text::sprintf('COM_REDSHOPB_USER_WEBSERVICE_PERMISSIONS_SCOPE_LABEL', $scope) ?> <br />
								<?php $permissionNames = array();

								foreach ($permissions as $permission) :
									$permissionNames[] = '<span class="hasTooltip" data-original-title="' . $permission->description . '">' . $permission->name . '</span>';
								endforeach;
									echo implode(' / ', $permissionNames);
								?>
							</th>
						<?php endforeach; ?>
					</tr>
					</thead>
					<?php if ($this->items): ?>
						<tbody>
						<?php foreach ($this->items as $i => $item): ?>
							<tr>
								<td>
									<?php echo HTMLHelper::_('grid.id', $i, $item->id); ?>
								</td>
								<td>
									<a href="<?php echo RedshopbRoute::_('index.php?option=com_redshopb&task=webservice_permission_user.edit&user_id=' . $item->user_id); ?>">
										<?php echo $this->escape($item->name); ?>
									</a>
								</td>
								<?php foreach ($this->permissions as $scope => $permissions) : ?>
									<td class="nowrap center">
										<?php
										$permissionValues = array();

										foreach ($permissions as $permission) :
											$permissionSelected = in_array($permission->id, $item->webservice_permissions);
											$permissionValues[] = '<label class="label ' . ($permissionSelected ? 'label-success' : '')
												. ' hasTooltip" data-original-title="<strong>' . $permission->name . '</strong><br />' . $permission->description . '">'
												. Text::_(($permissionSelected ? 'JYES' : 'JNO')) . '</label>';
										endforeach;
										echo implode(' / ', $permissionValues);
										?>
									</td>
								<?php endforeach; ?>
							</tr>
						<?php endforeach; ?>
						</tbody>
					<?php endif; ?>
				</table>
			</div>
			<div class="redshopb-webservice_permissions-pagination">
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
