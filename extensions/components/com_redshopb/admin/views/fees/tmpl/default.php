<?php
/**
 * @package     Aesir.E-Commerce.Backend
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
?>
<script type="text/javascript">
	var rsbftPhone = 675;
</script>
<?php
RedshopbHtml::loadFooTable();

$action    = 'index.php?option=com_redshopb&view=fees';
$listOrder = $this->state->get('list.ordering');
$listDirn  = $this->state->get('list.direction');
?>
<form action="<?php echo $action; ?>" name="adminForm" class="adminForm" id="adminForm" method="post">
	<?php
	echo RedshopbLayoutHelper::render(
		'searchtools.default',
		array(
			'view' => $this,
			'options' => array(
				"searchFieldSelector" => "#filter_search_fees",
				"searchField" => "search_fees",
				"limitFieldSelector" => "#list_fees_limit",
				"activeOrder" => $listOrder,
				"activeDirection" => $listDirn,
			)
		)
	);
	?>
	<hr/>
	<?php if (empty($this->items)) : ?>
		<?php echo RedshopbLayoutHelper::render('redshopb.common.nodata'); ?>
	<?php else : ?>
		<table class="table table-striped table-hover footable js-redshopb-footable redshopb-footable toggle-circle-filled" id="feesList">
			<thead>
			<tr>
				<th>
					<input type="checkbox" name="checkall-toggle" value=""
						   title="<?php echo Text::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)"/>
				</th>
				<th class="nowrap">
					<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_PRODUCT_NAME_LABEL', 'productName', $listDirn, $listOrder); ?>
				</th>
				<th class="nowrap">
					<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_PRODUCT_CURRENCY_LABEL', 'currencyName', $listDirn, $listOrder); ?>
				</th>
				<th class="nowrap" data-hide="phone">
					<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_FEE_LIMIT_LABEL', 'f.fee_limit', $listDirn, $listOrder); ?>
				</th>
				<th class="nowrap" data-hide="phone">
					<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_FEE_AMOUNT_LABEL', 'f.fee_amount', $listDirn, $listOrder); ?>
				</th>
				<th class="nowrap" data-hide="phone">
					<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_ID', 'f.id', $listDirn, $listOrder); ?>
				</th>
			</tr>
			</thead>
			<tbody>
			<?php foreach ($this->items as $i => $item): ?>
				<tr>
					<td>
						<?php echo HTMLHelper::_('rgrid.id', $i, $item->id); ?>
					</td>
					<td>
						<a href="index.php?option=com_redshopb&task=fee.edit&id=<?php echo $item->id; ?>">
							<?php echo $item->productName; ?>
						</a>
					</td>
					<td>
						<?php echo $item->currencyName; ?>
					</td>
					<td>
						<?php echo RedshopbHelperProduct::getProductFormattedPrice($item->fee_limit, $item->alpha3); ?>
					</td>
					<td>
						<?php echo RedshopbHelperProduct::getProductFormattedPrice($item->fee_amount, $item->alpha3); ?>
					</td>
					<td>
						<?php echo $item->id; ?>
					</td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
		<?php echo $this->pagination->getPaginationLinks(null, array('showLimitBox' => false)); ?>
	<?php endif; ?>
	<div>
		<input type="hidden" name="task" value="">
		<input type="hidden" name="boxchecked" value="0">
		<?php echo HTMLHelper::_('form.token'); ?>
	</div>
</form>
