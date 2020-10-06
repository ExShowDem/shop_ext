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

RedshopbHtml::loadFooTable();

$action    = RedshopbRoute::_('index.php?option=com_redshopb&view=tags');
$listOrder = $this->state->get('list.ordering');
$listDirn  = $this->state->get('list.direction');
$saveOrder = $listOrder == 'ordering';

if (strtolower($listOrder) == 't.lft' && strtolower($listDirn) == 'asc')
	:
	RHelperAsset::load('redshopbtreetable.js', 'com_redshopb');
endif;

echo RedshopbBrowserBreadcrumbs::renderBreadcrumbs();
?>

<div class="redshopb-tags">
	<form action="<?php echo $action; ?>" name="adminForm" class="adminForm" id="adminForm" method="post">

		<?php
		echo RedshopbLayoutHelper::render(
			'searchtools.default',
			array(
				'view' => $this,
				'options' => array(
					'searchField' => 'search_tags',
					'searchFieldSelector' => '#filter_search_tags',
					'limitFieldSelector' => '#list_tag_limit',
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
			<div class="redshopb-tags-table">
				<table class="table table-hover footable js-redshopb-footable redshopb-footable toggle-circle-filled js-redshopb-tree-order" id="tagList">
					<thead>
					<tr>
						<th width="1%">
							<input type="checkbox" name="checkall-toggle" value=""
								   title="<?php echo Text::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)"/>
						</th>
						<th width="1%" class="nowrap center">
							<?php echo HTMLHelper::_('rsearchtools.sort', 'JSTATUS', 't.state', $listDirn, $listOrder); ?>
						</th>
						<th width="1%" class="nowrap">
							<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_TREE_LABEL', 'c.lft', $listDirn, $listOrder); ?>
						</th>
						<th class="nowrap" data-toggle="true">
							<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_NAME', 't.name', $listDirn, $listOrder); ?>
						</th>
						<th class="nowrap" data-toggle="true">
							<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_TYPE', 't.type', $listDirn, $listOrder); ?>
						</th>
						<th class="nowrap" data-hide="phone">
							<?php echo Text::_('COM_REDSHOPB_COMPANY_LABEL'); ?>
						</th>
						<th width="1%" class="nowrap" data-hide="phone">
							<?php echo HTMLHelper::_('rsearchtools.sort', 'JGRID_HEADING_ID', 't.id', $listDirn, $listOrder); ?>
						</th>
					</tr>
					</thead>
					<?php if ($this->items): ?>
						<tbody>
						<?php foreach ($this->items as $i => $item): ?>
							<?php
								$canChange  = RedshopbHelperACL::getPermission('manage', 'category', Array('edit.state'), true);
								$canEdit    = RedshopbHelperACL::getPermission('manage', 'category', Array('edit','edit.own'), true);
								$canCheckin = $canEdit;
								$thumb      = '';
								$thumb      = RedshopbHelperTag::getTagImageThumbHtml($item->id);
							?>
							<tr data-parent="<?php echo $item->parent_id; ?>" data-id="<?php echo $item->id; ?>" data-level="<?php echo $item->level; ?>">
								<td>
									<?php echo HTMLHelper::_('grid.id', $i, $item->id); ?>
								</td>
								<td>
									<?php echo HTMLHelper::_('rgrid.published', $item->state, $i, 'tags.', $canChange, 'cb'); ?>
								</td>
								<td class="js-redshopb-tree">
									<div class="gi">
										<span class="js-redshop-children" style="display: none;">
											<i class="icon-chevron-up"></i>
										</span>
									</div>
								</td>
								<td class="js-redshopb-title" width="20%">
									<table class="js-redshopb-tree-hierarchy">
										<tr>
											<?php
											echo ($item->level ? str_repeat('<td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>', $item->level - 1) : '')
											?>
											<td class="js-redshopb-tree-hierarchy-separator">
												<i class="icon-angle-right"></i>&nbsp;
											</td>
											<td>
												<?php if ($canEdit) : ?>
												<a href="<?php echo RedshopbRoute::_('index.php?option=com_redshopb&task=tag.edit&id=' . $item->id); ?>" title="<?php echo $this->escape($item->name); ?>">
												<?php endif; ?>
												<?php
												if ($item->image != '') :
												?>
												<span class="hasTooltip"
												data-original-title="<div class='thumb'><?php echo htmlspecialchars($thumb, ENT_COMPAT, 'UTF-8'); ?></span>">
												<?php
												endif;
												?>
														<?php echo $this->escape($item->name); ?>
												<?php
												if ($thumb != '') :
												?>
												</span>
												<?php
												endif;
												?>
												<?php if ($canEdit) : ?>
												</a>
												<?php endif; ?>
											</td>
										</tr>
									</table>
								</td>
								<td>
									<?php echo $this->escape($item->type); ?>
								</td>
								<td>
									<?php echo $this->escape($item->company); ?>
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
			<div class="redshopb-tags-pagination">
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
<?php echo RHtml::_(
	'vnrbootstrap.renderModal', 'tagsModal',
	array(
		'title' => Text::_('COM_REDSHOPB_TAG_DELETE_CONFIRM'),
		'footer' => '<button class="btn btn-default" data-dismiss="modal" aria-hidden="true">' . Text::_('JNO') . '</button>'
			. '<button class="btn btn-primary" data-dismiss="modal" onclick="Joomla.submitbutton(\'tags.delete\')">' . Text::_('JYES') . '</button>'
	),
	Text::_('COM_REDSHOPB_TAG_DELETE_INFO')
);
