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
$action = RedshopbRoute::_('index.php?option=com_redshopb&view=layout_list');
echo RedshopbBrowserBreadcrumbs::renderBreadcrumbs();
$listOrder = $this->state->get('list.ordering');
$listDirn  = $this->state->get('list.direction');

?><script type="text/javascript">
	function listItemLayoutTaskForm(b, t, r) {
		var d = document.getElementById("adminForm");
			d.id.value = t;
			Joomla.submitform(b, d);
		return false
	}
</script>
<div class="redshopb-layout_list">
	<form action="<?php echo $action; ?>" name="adminForm" class="adminForm" id="adminForm" method="post">
		<?php
		echo RedshopbLayoutHelper::render(
			'searchtools.default',
			array(
				'view' => $this,
				'options' => array(
					'searchField' => 'search_layout_list',
					'searchFieldSelector' => '#filter_search_layout_list',
					'limitFieldSelector' => '#layout_item_limit',
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
					class="table table-striped table-hover" id="layoutList">
				<thead>
				<tr>
					<th width="1%" class="nowrap center">
						<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_LAYOUT_ID', 'layout_id', $listDirn, $listOrder); ?>
					</th>
					<th><?php echo Text::_('COM_REDSHOPB_LAYOUT_RELATIVE_PATHWAYS'); ?></th>
					<th></th>
				</tr>
				</thead>
				<tbody>
				<?php
				$i                   = 0;
				$defaultTemplateName = Factory::getApplication()->getTemplate();

				foreach ($this->items as $layoutId => $pathways): ?>
				<tr>
					<td>
						<span class="badge badge-success"><?php echo $layoutId; ?></span>
					</td>
					<td>
						<?php
						$customizationFound = false;

						foreach ($pathways as $i => $path):
							$layoutsFolder = str_replace(DIRECTORY_SEPARATOR, '.', $path);
							?><a href="#" class="hasTooltip"
								 onclick="return listItemLayoutTaskForm('layout_item.edit', '<?php
									echo base64_encode($layoutId . '|' . $layoutsFolder) ?>')"
								data-original-title="<span style='word-wrap:break-word'>Full path: <?php
								echo JPATH_ROOT . DIRECTORY_SEPARATOR . $path . DIRECTORY_SEPARATOR . str_replace('.', DIRECTORY_SEPARATOR, $layoutId) . '.php';
								?></span>">
							<span class="badge <?php echo ($i == 0) ? 'badge-important' : '' ?>"><?php echo $path ?></span>
							</a><?php if (stripos(JPATH_ROOT . DIRECTORY_SEPARATOR . $path, JPATH_THEMES . DIRECTORY_SEPARATOR . $defaultTemplateName . DIRECTORY_SEPARATOR . 'html' . DIRECTORY_SEPARATOR . 'layouts') === 0):
								if ($i == 0)
								{
									$customizationFound = true;
								}
							?>
							<a class="btn btn-small btn-danger" href="javascript:void(0);"
							   onclick="return listItemLayoutTaskForm('layout_list.delete', '<?php
								echo base64_encode($layoutId . '|' . $layoutsFolder) ?>')">
								<i class="icon-trash"></i>
							</a>
								<?php endif;
							?><br/><?php
						endforeach;
						?>
					</td>
					<td><?php if (!$customizationFound) : ?>
						<a class="btn btn-small btn-success" href="javascript:void(0);"
						   onclick="return listItemLayoutTaskForm('layout_item.edit', '<?php
							echo base64_encode($layoutId); ?>')">
							<i class="icon-file-text"></i>
							<?php echo Text::_('COM_REDSHOPB_TEMPLATE_ADD_CUSTOMIZATION') ?>
						<?php endif; ?></td>
				</tr>
				<?php
				$i++;
				endforeach; ?>
				</tbody>
			</table>
			<div class="redshopb-templates-pagination">
				<?php echo $this->pagination->getPaginationLinks(null, array('showLimitBox' => false)); ?>
			</div>
		<?php endif; ?>
			<div>
				<input type="hidden" name="task" value="">
				<input type="hidden" name="id">
				<?php echo HTMLHelper::_('form.token'); ?>
			</div>
	</form>
</div>
