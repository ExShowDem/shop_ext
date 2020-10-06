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

// HTML helpers
HTMLHelper::_('vnrbootstrap.tooltip');

echo RedshopbBrowserBreadcrumbs::renderBreadcrumbs();
?>
<div class="redshopb-mywallet">
	<h1><?php echo Text::_('COM_REDSHOPB_MYWALLET_TITLE'); ?></h1>
	<div class="well mywallet_list">
	<h3><?php echo Text::_('COM_REDSHOPB_MYWALLET_STATUS'); ?></h3>

	<?php if (!empty($this->wallets->credit)): ?>
		<?php foreach ($this->wallets->credit as $wallet): ?>
			<div class="text-right">
					<?php echo $wallet['symbol'] . ' ' . $wallet['amount']; ?>
			</div>
		<?php endforeach; ?>
	<?php endif; ?>
	</div>
	<hr />
	<div class="text-right">
		<a href="<?php echo RedshopbRoute::_('index.php?option=com_redshopb&view=shop', false) ?>" class="btn btn-success">
			<?php echo Text::_('COM_REDSHOPB_MYWALLET_PURCHASE_MORE') ?>
		</a>
	</div>
	<?php if (!empty($this->recent_purchases)): ?>
	<div class="recent-purchases">
		<h3><?php echo Text::_('COM_REDSHOPB_MYWALLET_RECENT_PURCHASES') ?></h3>
		<table class="table table-striped table-hover footable js-redshopb-footable redshopb-footable toggle-circle-filled" id="orderList">
			<thead>
				<th style="width: 25%" class="nowrap"><?php echo Text::_('COM_REDSHOPB_MYWALLET_ORDER_NUMBER') ?></th>
				<th style="width: 25%" class="nowrap" data-hide="phone"><?php echo Text::_('COM_REDSHOPB_MYWALLET_ORDER_DATE') ?></th>
				<th style="width: 25%" class="nowrap" data-hide="phone"><?php echo Text::_('COM_REDSHOPB_MYWALLET_ORDER_PURCHASES_CREDITS') ?></th>
				<th style="width: 25%" class="nowrap"><span class="pull-right"><?php echo Text::_('COM_REDSHOPB_MYWALLET_ORDER_PRICE') ?></span></th>
			</thead>
			<tbody>
				<?php foreach ($this->recent_purchases as $order): ?>
				<tr>
					<td><?php echo str_pad($order->id, 6, '0', STR_PAD_LEFT); ?></td>
					<td><?php echo HTMLHelper::_('date', $order->created_date, Text::_('DATE_FORMAT_LC4')) ?></td>
					<td><?php echo $order->currency ?></td>
					<td><div class="pull-right"><?php echo RHelperCurrency::getFormattedPrice($order->total_price, $order->currency_id) ?></div></td>
				</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>
	<?php endif; ?>
</div>
