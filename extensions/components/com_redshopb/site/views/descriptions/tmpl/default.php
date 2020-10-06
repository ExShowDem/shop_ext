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

echo RedshopbBrowserBreadcrumbs::renderBreadcrumbs();
?>

<script type="text/javascript">
	var rsbftTablet = 960;
	var rsbftPhone = 768;
</script>

<?php
RedshopbHtml::loadFooTable();

$listOrder = $this->state->get('list.ordering');
$listDirn  = $this->state->get('list.direction');
$canEdit   = RedshopbHelperACL::getPermission('manage', 'product', array('edit', 'edit.own'), true);
?>
<div class="redshopb-descriptions">
	<form action="<?php echo RedshopbRoute::_('index.php?option=com_redshopb&view=descriptions'); ?>" name="adminForm" class="adminForm" id="adminForm" method="post">
		<?php
			echo RedshopbLayoutHelper::render(
				'searchtools.default',
				array(
					'view' => $this,
					'options' => array(
						'searchField' => 'search_product_descriptions',
						'searchFieldSelector' => '#filter_search_product_descriptions',
						'limitFieldSelector' => '#list_description_limit',
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
			<div class="redshopb-descriptions-table">
				<table class="table table-striped table-hover footable js-redshopb-footable redshopb-footable toggle-circle-filled" id="productDescriptionList">
					<thead>
					<tr>
						<th width="1%">
							<input type="checkbox" name="checkall-toggle" value=""
								   title="<?php echo Text::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)"/>
						</th>
						<th class="nowrap">
							<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_SKU', 'pd.sku', $listDirn, $listOrder); ?>
						</th>
						<th class="nowrap">
							<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_PRODUCT_DESCRIPTION_FLAT_ATTRIBUTE_LABEL', 'pd.main_attribute_value_id', $listDirn, $listOrder); ?>
						</th>
						<th>
							<?php echo Text::_('COM_REDSHOPB_PRODUCT_DESCRIPTION'); ?>
						</th>
						<th width="1%" class="nowrap" data-hide="phone">
							<?php echo HTMLHelper::_('rsearchtools.sort', 'JGRID_HEADING_ID', 'pd.id', $listDirn, $listOrder); ?>
						</th>
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
									<?php echo $this->escape($item->sku); ?>
								</td>
								<td>
									<?php if ($canEdit) :
										$itemUrl = 'index.php?option=com_redshopb&task=description.edit&id=' . $item->id;
									?>
									<a href="<?php echo RedshopbRoute::_($itemUrl); ?>">
									<?php endif; ?>
										<?php echo $this->escape($item->value); ?>

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
					<?php endif; ?>
				</table>
			</div>
			<div class="redshopb-descriptions-pagination">
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
