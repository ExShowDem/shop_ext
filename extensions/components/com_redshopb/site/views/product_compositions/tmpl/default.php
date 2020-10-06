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

$action    = RedshopbRoute::_('index.php?option=com_redshopb&view=product_compositions');
$listOrder = $this->state->get('list.ordering');
$listDirn  = $this->state->get('list.direction');
$saveOrder = $listOrder == 'ordering';

echo RedshopbBrowserBreadcrumbs::renderBreadcrumbs();
?>
<script type="text/javascript">
	var rsbftTablet = 960;
	var rsbftPhone = 768;
</script>

<div class="redshopb-product_compositions">
	<form action="<?php echo $action; ?>" name="adminForm" class="adminForm" id="adminForm" method="post">

		<?php
		echo RedshopbLayoutHelper::render(
			'searchtools.default',
			array(
				'view' => $this,
				'options' => array(
					'filterButton' => false,
					'searchField' => 'search_product_compositions',
					'searchFieldSelector' => '#filter_search_product_compositions',
					'limitFieldSelector' => '#list_product_compositions_limit',
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
			<div class="redshopb-product_compositions-table">
				<table class="table table-striped table-hover" id="productCompositionList">
					<thead>
						<tr>
							<th width="1%" class="hidden-phone">
								<input type="checkbox" name="checkall-toggle" value="" title="<?php echo Text::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)"/>
							</th>
							<th class="nowrap hidden-phone">
								<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_ID', 'pc.id', $listDirn, $listOrder, null); ?>
							</th>
							<th class="nowrap hidden-phone">
								<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_PRODUCT', 'product_name', $listDirn, $listOrder); ?>
							</th>
							<th class="nowrap hidden-phone">
								<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_PRODUCT_COMPOSITION_FLAT_ATTRIBUTE_LABEL', 'product_attribute_value_name', $listDirn, $listOrder); ?>
							</th>
							<th class="nowrap hidden-phone">
								<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_PRODUCT_COMPOSITION_TYPE', 'pc.type', $listDirn, $listOrder); ?>
							</th>
							<th class="nowrap hidden-phone">
								<?php echo Text::_('COM_REDSHOPB_PRODUCT_COMPOSITION_QUALITY'); ?>
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
								<a href="<?php echo RedshopbRoute::_('index.php?option=com_redshopb&task=product_composition.edit&id=' . $item->id); ?>">
									<?php echo $item->id; ?>
								</a>
							</td>
							<td>
								<?php echo $this->escape($item->product_name); ?>
							</td>
							<td>
								<?php echo $this->escape($item->product_attribute_value_name); ?>
							</td>
							<td>
								<?php echo $this->escape($item->type); ?>
							</td>
							<td>
								<?php echo $this->escape($item->quality); ?>
							</td>
						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>
			</div>
			<div class="redshopb-product_compositions-pagination">
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
