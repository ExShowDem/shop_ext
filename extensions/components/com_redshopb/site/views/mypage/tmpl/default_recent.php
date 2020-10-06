<?php
/**
 * @package     Aesir.E-Commerce.Admin
 * @subpackage  Views
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;

// HTML helpers
HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('vnrbootstrap.tooltip');
HTMLHelper::_('rjquery.chosen', 'select');

$listOrder = $this->state->get('list.ordering');
$listDirn  = $this->state->get('list.direction');

$columns = array('id', 'status', 'created_date', 'products', 'total');

$userIsAdmin = RedshopbHelperACL::isSuperAdmin();

if ($userIsAdmin)
{
	$columns = array('id', 'status','customer_name', 'created_date', 'products', 'total');
}
?>

<div class="tab-pane" id="myPageRecentOrders">
	<?php
	echo RedshopbLayoutHelper::render(
		'orders.orders',
		array(
			'view' => $this,
			'options' => array(
				'items'               => $this->items,
				'pagination'          => $this->pagination,
				'listDirn'            => $listDirn,
				'listOrder'           => $listOrder,
				'columns'             => $columns,
				'reorder'             => false,
				'showPaginationLinks' => true,
				'showFilter'          => true
			)
		)
	);
	?>
</div>
