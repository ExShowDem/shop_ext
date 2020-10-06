<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Views
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;

RHelperAsset::load('lib/jquery.number.js', 'com_redshopb');
$isLog         = RedshopbHelperOrder::isLog($this->item->id);
$orderEditable = $this->item->status == 0 && !$isLog && $this->customerAvailable;
$config        = RedshopbEntityConfig::getInstance();
$originalItems = array();
RedshopbHtml::loadFooTable();
$usingShipping = RedshopbHelperOrder::isShippingAllowed($this->shippingMethods);

if ($orderEditable)
{
	$msg = 'COM_REDSHOPB_ORDER_EDIT_ITEMS_CHANGE_WARNING';

	if ($usingShipping)
	{
		$msg = 'COM_REDSHOPB_ORDER_EDIT_ITEMS_CHANGE_WARNING_WITH_SHIPPING';
	}

	Factory::getApplication()->enqueueMessage(Text::_($msg), 'notice');
}

$customerItemsSettings = array(
	'config'              => $config,
	'customerOrders'      => array($this->customerOrder),
	'orderId'             => $this->item->id,
	'form'                => $this->cartItemsForm,
	'showToolbar'         => false,
	'canEdit'             => $orderEditable,
	'checkbox'            => false,
	'quantityfield'       => 'quantity',
	'lockquantity'        => !$orderEditable,
	'return'              => base64_encode('index.php?option=com_redshopb&view=order&layout=edit&id=' . $this->item->id),
	'showDeliveryAddress' => false,
	'isEmail'             => false,
	'showStockAs'         => RedshopbHelperStockroom::getStockVisibility(),
	'view'                => 'order',
	'delivery'            => $config->get('stockroom_delivery_time', 'hour'),
	'feeProducts'         => RedshopbHelperShop::getChargeProducts('fee'),
	'renderedFrom'        => 'order'
);
?>
<style>
	table.table th label {
		font-weight: bold;
	}
</style>
<div class="tab-content">
	<div class="tab-pane active">
		<div class="container-fluid">
			<div class="row">
				<div class="col-md-12">
					<?php
					echo RedshopbLayoutHelper::render(
						'checkout.customer_basket', $customerItemsSettings
					);
					?>
				</div>
			</div>
		</div>
	</div>
</div>
