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
use Joomla\Registry\Registry;

$data = $displayData;

$shippingConfigurationId = $data['shippingConfigurationId'];
$state                   = $data['state'];
$items                   = $data['items'];
$pagination              = $data['pagination'];
$filterForm              = $displayData['filterForm'];
$formName                = $data['formName'];
$showToolbar             = isset($data['showToolbar']) ? $data['showToolbar'] : false;
$return                  = isset($data['return']) ? $data['return'] : null;
$action                  = RedshopbRoute::_('index.php?option=com_redshopb&view=price_debtor_groups');

RedshopbHtml::loadFooTable();

// Allow to override the form action
if (isset($data['action']))
{
	$action = $data['action'];
}

$priceDebtorGroupId = Factory::getApplication()->input->getInt('id');
$listOrder          = $state->get('list.ordering');
$listDirn           = $state->get('list.direction');
$saveOrder          = $listOrder == 'ordering';

$searchToolsOptions = array(
	'filterButton' => false,
	"searchFieldSelector" => "#filter_search_shipping_configurations",
	"orderFieldSelector" => "#list_fullordering",
	"searchField" => "search_shipping_configurations",
	"limitFieldSelector" => "#list_price_debtor_group_limit",
	"activeOrder" => $listOrder,
	"activeDirection" => $listDirn,
	"formSelector" => ("#" . $formName),
	"filtersHidden" => (bool) empty($data['activeFilters'])
);

?>
<script type="text/javascript">
	(function ($) {
		$(document).ready(function () {
			$('#<?php echo $formName; ?>').searchtools(
				<?php echo json_encode($searchToolsOptions); ?>
			);
		});
	})(jQuery);
</script>
<div class="redshopb-price-debtor-group-shipping-configurations">
		<?php
		// Render the toolbar?
		if ($showToolbar)
		{
			echo RedshopbLayoutHelper::render('price_debtor_group.shipping_configurations_toolbar', $data);
		}
		?>
	<form action="<?php echo $action; ?>" name="<?php echo $formName; ?>" class="adminForm" id="<?php echo $formName; ?>" method="post">
		<?php
		echo RedshopbLayoutHelper::render(
			'searchtools.default',
			array(
				'view' => (object) array(
						'filterForm' => $data['filterForm'],
						'activeFilters' => $data['activeFilters']
					),
				'options' => $searchToolsOptions
			)
		);
		?>

		<hr/>
		<?php if (empty($items)) : ?>
			<?php echo RedshopbLayoutHelper::render('common.nodata'); ?>
		<?php else : ?>
			<table class="table table-striped table-hover footable js-redshopb-footable redshopb-footable toggle-circle-filled" id="shippingConfigurationList">
				<thead>
				<tr>
					<th width="1%">
						<input type="checkbox" name="checkall-toggle" value=""
							   title="<?php echo Text::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)"/>
					</th>
					<th width="1%" class="nowrap center">
						<?php echo HTMLHelper::_('rsearchtools.sort', 'JSTATUS', 'state', $listDirn, $listOrder); ?>
					</th>
					<th class="nowrap" data-toggle="true">
						<?php echo Text::_('COM_REDSHOPB_NAME'); ?>
					</th>
					<th width="1%" class="nowrap" data-hide="phone">
						<?php echo HTMLHelper::_('rsearchtools.sort', 'JGRID_HEADING_ID', 'id', $listDirn, $listOrder, null, 'asc', '', null, $formName); ?>
					</th>
				</tr>
				</thead>
				<?php if ($items): ?>
					<tbody>
					<?php foreach ($items as $i => $item): ?>
						<?php
						$canChange  = 1;
						$canEdit    = 1;
						$canCheckin = 1;

						$params = new Registry($item->params);
						$name   = $params->get('shipping_title', $item->plugin_name);
						?>
						<tr>
							<td>
								<?php echo HTMLHelper::_('rgrid.id', $i, $item->id, false, 'cid', $formName); ?>
							</td>
							<td>
								<?php echo HTMLHelper::_('rgrid.published', $item->state, $i, 'shipping_configurations.', $canChange, 'cb', null, null, $formName); ?>
							</td>
							<td>
								<?php if ($item->checked_out) : ?>
									<?php echo HTMLHelper::_('rgrid.checkedout', $i, $item->checked_out > 0 ? Factory::getUser($item->checked_out)->get('name') : '',
										$item->checked_out_time, 'shipping_configurations.', $canCheckin, 'cb', $formName
									); ?>
								<?php endif; ?>
								<?php
								$itemUrl = 'index.php?option=com_redshopb&task=shipping_configuration.edit&id=' . $item->id
									. '&jform[price_debtor_group_id]=' . $shippingConfigurationId . '&from_price_debtor_group=1';

								if ($return)
								{
									$itemUrl .= '&return=' . $return;
								}
								?>
								<a href="<?php echo RedshopbRoute::_($itemUrl); ?>">
									<?php echo $name ?>
								</a>
							</td>
							<td>
								<?php echo $item->id; ?>
							</td>
						</tr>
					<?php endforeach; ?>
					</tbody>
				<?php endif; ?>
			</table>
			<?php echo $pagination->getPaginationLinks('tab.pagination.links', array('showLimitBox' => false)); ?>
		<?php endif; ?>

		<div>
			<input type="hidden" name="task" value="price_debtor_group.saveModelState">
			<?php if ($return) : ?>
				<input type="hidden" name="return" value="<?php echo $return ?>">
			<?php endif; ?>
			<input type="hidden" name="filter[price_debtor_group]" value="<?php echo $shippingConfigurationId ?>">
			<input type="hidden" name="jform[price_debtor_group_id]" value="<?php echo $priceDebtorGroupId ?>">
			<input type="hidden" name="from_price_debtor_group" value="1">
			<input type="hidden" name="boxchecked" value="0">
			<?php echo HTMLHelper::_('form.token'); ?>
		</div>
	</form>
</div>
