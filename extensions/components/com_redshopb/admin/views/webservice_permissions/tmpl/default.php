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

$action    = RedshopbRoute::_('index.php?option=com_redshopb&view=webservice_permissions');
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
					'searchField' => 'search_webservice_permissions',
					'searchFieldSelector' => '#filter_search_webservice_permissions',
					'limitFieldSelector' => '#list_webservice_permissions_limit',
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
							<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_NAME', 'wp.name', $listDirn, $listOrder); ?>
						</th>
						<th data-toggle="true">
							<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_WEBSERVICE_PERMISSIONS_SCOPE', 'wp.scope', $listDirn, $listOrder); ?>
						</th>
						<th data-toggle="true">
							<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_DESCRIPTION', 'wp.description', $listDirn, $listOrder); ?>
						</th>
						<th data-toggle="true">
							<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_WEBSERVICE_PERMISSIONS_MANUAL', 'wp.manual', $listDirn, $listOrder); ?>
						</th>
						<th data-toggle="true">
							<?php echo HTMLHelper::_('rsearchtools.sort', 'JSTATUS', 'wp.state', $listDirn, $listOrder); ?>
						</th>
					</tr>
					</thead>
					<?php if ($this->items): ?>
						<tbody>
						<?php foreach ($this->items as $i => $item): ?>
							<tr>
								<td class="redshopb-webservice_permission_id">
									<?php echo HTMLHelper::_('grid.id', $i, $item->id); ?>
									<span style="display:none;" id="permission_manual_option_cb<?php echo $i; ?>"><?php echo $item->manual; ?></span>
								</td>
								<td>
									<a href="<?php echo RedshopbRoute::_('index.php?option=com_redshopb&task=webservice_permission.edit&id=' . $item->id); ?>">
										<?php echo $this->escape($item->name); ?>
									</a>
								</td>
								<td>
									<?php echo $this->escape($item->scope); ?>
								</td>
								<td>
									<?php echo $this->escape($item->description); ?>
								</td>
								<td>
									<label class="label <?php echo $item->manual ? 'label-success' : ''; ?>">
										<?php echo Text::_(($item->manual ? 'JYES' : 'JNO')); ?>
									</label>
								</td>
								<td>
									<?php echo HTMLHelper::_('rgrid.published', $item->state, $i, 'webservice_permissions.', true, 'cb'); ?>
								</td>
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
<script type="text/javascript">
	(function ($) {
		$(document).ready(function () {
			Joomla.submitbutton = function(task)
			{
				if (task == 'webservice_permissions.delete')
				{
					var showConfirmation = false;
					var confirmed = true;

					jQuery('.redshopb-webservice_permission_id input').each(function(){
						if (!showConfirmation && jQuery('#permission_manual_option_' + jQuery(this).attr('id')).html() == '0')
						{
							showConfirmation = true;
						}
					});
					if (showConfirmation)
					{
						confirmed = confirm('<?php echo Text::_('COM_REDSHOPB_WEBSERVICE_PERMISSIONS_DELETE_WARNING', true, true); ?>');
					}
					if (confirmed)
					{
						Joomla.submitform(task, document.getElementById('adminForm'));
					}
				}
				else
				{
					Joomla.submitform(task, document.getElementById('adminForm'));
				}
			}
		});

	})(jQuery);
</script>
