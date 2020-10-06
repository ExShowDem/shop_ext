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

$action         = RedshopbRoute::_('index.php?option=com_redshopb&view=templates');
$listOrder      = $this->state->get('list.ordering');
$listDirn       = $this->state->get('list.direction');
$saveOrder      = $listOrder == 'ordering';
$countTemplates = count(RedshopbHelperTemplate::getJoomlaTemplateList());
HTMLHelper::_('bootstrap.popover');
?>
<script type="text/javascript">
	var rsbftPhone = 660;
</script>
<?php
RedshopbHtml::loadFooTable();
echo RedshopbBrowserBreadcrumbs::renderBreadcrumbs();
?>
<script type="text/javascript">
	function listItemTemplateTaskForm(g, b, t) {
		var d = document.getElementById("adminForm");
		var a = d[g];
		if (a) {
			for (var c = 0; true; c++) {
				var e = d["cb" + c];
				if (!e) {
					break
				}
				e.checked = false
			}
			a.checked = true;
			d.boxchecked.value = 1;
			d.templateName.value = t;
			Joomla.submitform(b, d)
		}
		return false
	}
</script>
<div class="redshopb-templates">
	<form action="<?php echo $action; ?>" name="adminForm" class="adminForm" id="adminForm" method="post">
		<?php
		echo RedshopbLayoutHelper::render(
			'searchtools.default',
			array(
				'view' => $this,
				'options' => array(
					'searchField' => 'search_templates',
					'searchFieldSelector' => '#filter_search_templates',
					'limitFieldSelector' => '#list_template_limit',
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
			<div class="redshopb-templates-table">
				<table
					class="table table-striped table-hover footable js-redshopb-footable redshopb-footable toggle-circle-filled" id="templateList">
					<thead>
					<tr>
						<th width="1%">
							<input type="checkbox" name="checkall-toggle" value=""
								   title="<?php echo Text::_('JGLOBAL_CHECK_ALL'); ?>"
								   onclick="Joomla.checkAll(this)"/>
						</th>
						<th width="1%" class="nowrap center">
							<?php echo HTMLHelper::_('rsearchtools.sort', 'JSTATUS', 't.state', $listDirn, $listOrder); ?>
						</th>
						<th width="1%" class="nowrap center">
							<?php echo HTMLHelper::_('rsearchtools.sort', 'JDEFAULT', 't.default', $listDirn, $listOrder); ?>
						</th>
						<th class="nowrap" data-toggle="true">
							<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_NAME', 't.name', $listDirn, $listOrder); ?>
						</th>
						<th class="nowrap" data-toggle="true">
							<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_TEMPLATE_ALIAS', 't.alias', $listDirn, $listOrder); ?>
						</th>
						<th class="nowrap" data-toggle="true">
							<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_TEMPLATE_GROUP', 't.template_group', $listDirn, $listOrder); ?>
						</th>
						<th class="nowrap" data-toggle="true">
							<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_TEMPLATE_SCOPE', 't.scope', $listDirn, $listOrder); ?>
						</th>
						<th class="nowrap" data-toggle="true">
							<?php echo Text::_('COM_REDSHOPB_FIELD_DESCRIPTION_LABEL'); ?>
						</th>
						<th>
							<?php echo Text::_('COM_REDSHOPB_TEMPLATE_CUSTOMIZATIONS'); ?>
						</th>
						<th width="1%" class="nowrap" data-hide="phone">
							<?php echo HTMLHelper::_('rsearchtools.sort', 'JGRID_HEADING_ID', 't.id', $listDirn, $listOrder); ?>
						</th>
					</tr>
					</thead>
					<?php if ($this->items): ?>
						<tbody>
						<?php foreach ($this->items as $i => $item) : ?>
							<?php
							$canChange  = RedshopbHelperACL::getPermission('manage', 'category', Array('edit.state'), true);
							$canEdit    = RedshopbHelperACL::getPermission('manage', 'category', Array('edit', 'edit.own'), true);
							$canCheckin = $canEdit;
							?>
							<tr>
								<td>
									<?php echo HTMLHelper::_('grid.id', $i, $item->id); ?>
								</td>
								<td>
									<?php echo HTMLHelper::_('rgrid.published', $item->state, $i, 'templates.', $canChange, 'cb'); ?>
								</td>
								<td>
									<?php echo HTMLHelper::_('rgrid.isdefault', $item->default, $i, 'templates.', ($canChange && !$item->default), 'cb'); ?>
								</td>
								<td>
									<?php if ($canEdit) : ?>
										<a href="<?php echo RedshopbRoute::_('index.php?option=com_redshopb&task=template.edit&id=' . $item->id); ?>">
									<?php endif; ?>

									<?php
									if ($item->checked_out)
									{
										echo HTMLHelper::_('rgrid.checkedout', $i, $item->checked_out > 0 ? Factory::getUser($item->checked_out)->get('name') : '', $item->checked_out_time, 'templates.', $canCheckin);
									}

									echo $this->escape($item->name);
									?>
									<?php if ($canEdit) : ?>
										</a>
									<?php endif; ?>
								</td>
								<td>
									<?php echo $this->escape($item->alias); ?>.php
									<?php
									$otherCustomizations = RedshopbHelperTemplate::getListExtraCustomizations($item);

									if (!empty($otherCustomizations))
									{
										foreach ($otherCustomizations as $customization)
										{
											?><br/>
											<a
												class="hasTooltip"
												data-original-title="<span style='word-wrap:break-word'><?php echo Text::_('COM_REDSHOPB_TEMPLATE_FILE_RELATIVE_PATH') . $customization->relativePath; ?></span>"
												href="#"
												onclick="return listItemTemplateTaskForm('cb<?php echo $i; ?>','template.edit', '<?php echo $customization->folder; ?>')"
											>
												<span class="label label-warning"><?php echo Text::_('COM_REDSHOPB_TEMPLATE_OVERRIDEN_BY_EXTENSION') ?></span>
											</a>
											<?php
										}
									}
									?>
								</td>
								<td>
									<?php echo $this->escape($item->template_group); ?>
								</td>
								<td>
									<?php echo $this->escape($item->scope); ?>
								</td>
								<td>
									<?php echo Text::_($item->description); ?>
								</td>
								<td>
									<table class="table-condensed">
										<?php
										$customizations      = RedshopbHelperTemplate::getListCustomizations($item);
										$countCustomizations = count($customizations);

										if ($countCustomizations > 0)
										{
											foreach ($customizations as $customization)
											{
												?>
												<tr>
													<td>
														<a href="<?php echo RedshopbRoute::_('index.php?option=com_redshopb&task=template.edit&id=' . $item->id . '&templateName=' . $customization->template); ?>">
															<?php echo $customization->template; ?>
														</a>
													</td>
													<td>
														<a class="btn btn-small btn-info" href="javascript:void(0);"
															onclick="return listItemTemplateTaskForm('cb<?php echo $i; ?>','template.edit', '<?php echo $customization->template; ?>')">
															<i class="icon-edit"></i>
														</a>
													</td>
													<td>
														<a class="btn btn-small btn-danger" href="javascript:void(0);"
															onclick="return listItemTemplateTaskForm('cb<?php echo $i; ?>','templates.delete', '<?php echo $customization->template; ?>')">
															<i class="icon-trash"></i>
														</a>
													</td>
												</tr>
												<?php
											}
										}

										if ($countTemplates > $countCustomizations)
										{
											?>
											<tr>
												<td colspan="3">
													<a class="btn btn-small btn-success" href="javascript:void(0);"
													   onclick="return listItemTemplateTaskForm('cb<?php echo $i; ?>','template.edit', 'none')">
														<i class="icon-file-text"></i>
														<?php echo Text::_('COM_REDSHOPB_TEMPLATE_ADD_CUSTOMIZATION') ?>
													</a>
												</td>
											</tr>
											<?php
										}
										?>
									</table>
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
			<div class="redshopb-templates-pagination">
				<?php echo $this->pagination->getPaginationLinks(null, array('showLimitBox' => false)); ?>
			</div>
		<?php endif; ?>

		<div>
			<input type="hidden" name="task" value="">
			<input type="hidden" name="boxchecked" value="0">
			<input type="hidden" name="templateName">
			<?php echo HTMLHelper::_('form.token'); ?>
		</div>
	</form>
</div>
