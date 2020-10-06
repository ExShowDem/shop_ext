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

HTMLHelper::_('vnrbootstrap.tooltip');
HTMLHelper::_('rjquery.chosen', 'select');
RHtml::_('vnrbootstrap.modal', 'orderModal');

$action    = RedshopbRoute::_('index.php?option=com_redshopb&view=orders');
$listOrder = $this->state->get('list.ordering');
$listDirn  = $this->state->get('list.direction');
$saveOrder = $listOrder == 'company_name';

echo RedshopbBrowserBreadcrumbs::renderBreadcrumbs();
?>
<script>
jQuery(document).ready(function() {
	var csvButton = jQuery('div.redcore div.btn-group.pull-right button:contains("Csv")');
	var onclick   = csvButton.attr('onclick');

	csvButton.removeAttr('onclick');

	csvButton.on('click', function(){
		var url = onclick.slice(onclick.indexOf('http'),-2);
		redSHOPB.ajax.generateCsvFile("orders", "#orderList", url);
	});
});
</script>
<div class="redshopb-orders">
	<form action="<?php echo $action; ?>" name="adminForm" class="adminForm" id="adminForm" method="post">
		<?php
		echo RedshopbLayoutHelper::render(
			'searchtools.default',
			array(
				'view' => $this,
				'options' => array(
					'searchField' => 'search_orders',
					'searchFieldSelector' => '#filter_search_orders',
					'limitFieldSelector' => '#list_order_limit',
					'activeOrder' => $listOrder,
					'activeDirection' => $listDirn
				)
			)
		);
		?>

		<hr/>

		<?php

		$ordersColumns = array('grid', 'id', 'customer_type', 'customer_name', 'vendor_name', 'status', 'created_date', 'log_type', 'actions');

		if (RedshopbHelperACL::getPermissionInto('impersonate', 'order'))
		{
			$ordersColumns = array('grid', 'id', 'customer_type', 'customer_name', 'vendor_name', 'status', 'created_date', 'author', 'log_type', 'actions');
		}

		echo RedshopbLayoutHelper::render(
			'orders.orders',
			array(
				'view' => $this,
				'options' => array(
					'items' => $this->items,
					'pagination' => $this->pagination,
					'listDirn' => $listDirn,
					'listOrder' => $listOrder,
					'columns' => $ordersColumns,
					'reorder' => true
				)
			)
		);
		?>
	</form>
</div>
