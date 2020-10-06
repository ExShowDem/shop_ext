<?php
/**
 * @package     Aesir.E-Commerce.Admin
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Form\Form;

extract($displayData);

HTMLHelper::_('vnrbootstrap.tooltip');
$isOffer = isset($isOffer) ? $isOffer : false;
$config  = RedshopbEntityConfig::getInstance();

/** @var Form $form */
$form = $displayData['form'];
$data = $displayData;

$isEmail     = isset($data['isEmail']) ? $data['isEmail'] : null;
$feeProducts = isset($data['feeProducts']) ? $data['feeProducts'] : null;
$view        = isset($data['view']) ? $data['view'] : null;
$delivery    = isset($data['delivery']) ? $data['delivery'] : null;

$dispatcher  = RFactory::getDispatcher();
$isFromOrder = (isset($data['orderid']) && $data['orderid']) ? true : false;
$results     = $dispatcher->trigger('onRedshopbOrderVariablesReview', array(compact(array_keys(get_defined_vars()))));

$customerType = $customerOrderEntity->customerType;
$customerId   = $customerOrderEntity->customerId;
$currency     = $customerOrderEntity->currency;

foreach ($results as $result)
{
	if (is_array($result))
	{
		extract($result);
	}
}

$showDiscountColumn = false;
$showAttributes     = false;
$showDelivery       = false;

$modelShop = RedshopbModel::getInstance('Shop', 'RedshopbModel');

foreach ($items as $item)
{
	if (isset($item->discount) && $item->discount > 0)
	{
		$showDiscountColumn = true;
	}

	if (isset($item->attributes) && !empty($item->attributes))
	{
		$showAttributes = true;
	}

	if (isset($item->stockroom_id)
		&& $item->stockroom_id
		&& RedshopbEntityStockroom::load($item->stockroom_id))
	{
		$showDelivery = true;
	}
}

$defaultColumns       = array('product_id', 'product_item_code', 'attributes', 'quantity', 'stock', 'price_without_discount', 'discount', 'final_price');
$checkoutFields       = $config->get('checkout_fields', $defaultColumns);
$hasDiscountColumn    = array_search('discount', $checkoutFields);
$hasAttributesColumn  = array_search('attributes', $checkoutFields);
$hasStockRoomIdColumn = array_search('stockroom_id', $checkoutFields);
$hasStockColumn       = array_search('stock', $checkoutFields);

if (!$showDiscountColumn && $hasDiscountColumn !== false)
{
	unset($checkoutFields[$hasDiscountColumn]);
}

if (!$showAttributes && $hasAttributesColumn)
{
	unset($checkoutFields[$hasAttributesColumn]);
}

if (!$showDelivery && $hasStockRoomIdColumn)
{
	unset($checkoutFields[$hasStockRoomIdColumn]);
}

if ($data['showStockAs'] == 'hide' && $hasStockColumn)
{
	unset($checkoutFields[$hasStockColumn]);
}

$tableIdPrefix = $customerType . '_' . $customerId;

if ($isOffer)
{
	$tableIdPrefix .= '_offer';
}

$fooHiddenDefault = array('price_without_discount', 'final_price', 'attributes', 'discount');

if (isset($data['userCart']))
{
	$fooHiddenDefault = array('product_item_code','price_without_discount','final_price','attributes','discount','stock');
}

$fooTableHiddenFields = $config->get('checkout_hidden_fields', $fooHiddenDefault);
$colspan              = count($checkoutFields);

$productRowSettings                         = array();
$productRowSettings['items']                = $items;
$productRowSettings['config']               = $config;
$productRowSettings['isEmail']              = $isEmail;
$productRowSettings['isOffer']              = $isOffer;
$productRowSettings['isFromOrder']          = $isFromOrder;
$productRowSettings['currency']             = $currency;
$productRowSettings['customerId']           = $customerId;
$productRowSettings['customerType']         = $customerType;
$productRowSettings['checkoutFields']       = $checkoutFields;
$productRowSettings['fooTableHiddenFields'] = $fooTableHiddenFields;
$productRowSettings['lockquantity']         = $data['lockquantity'];
$productRowSettings['checkbox']             = $data['checkbox'];
$productRowSettings['view']                 = $view;
$productRowSettings['showStockAs']          = $data['showStockAs'];
$productRowSettings['delivery']             = $delivery;
$productRowSettings['form']                 = $displayData['form'];
$productRowSettings['showTaxes']            = $config->getInt('show_taxes_in_cart_module', 1);
?>

	<table class="table table-condensed footable js-redshopb-footable redshopb-footable"
		   id="tableCustomer_<?php echo $tableIdPrefix; ?>">
		<thead>
		<tr>
			<?php if ($data['checkbox']) : ?>
				<th>&nbsp;</th>
				<?php $colspan++;?>
			<?php endif; ?>

			<?php foreach ($checkoutFields AS $fieldName):?>
				<?php $thClassName     = 'class="field_' . $fieldName . '"' ?>
				<?php $thDataAttribute = (in_array($fieldName, $fooTableHiddenFields)) ? ' data-hide="phone,tablet"' : '';?>
				<?php $field           = $form->getField($fieldName);?>
				<th <?php echo $thClassName . $thDataAttribute;?>>
					<?php echo $field->label; ?>
				</th>
			<?php endforeach;?>
			<th data-visible="true">&nbsp;</th>
		</tr>
		</thead>
		<tbody>
		<?php $countItems = count($items); ?>

		<?php for ($i = 0; $i < $countItems; $i++):?>
			<?php $productRowSettings['item']      = $items[$i];?>
			<?php $productRowSettings['canEdit']   = is_array($feeProducts)
				&& (in_array($item->product_item_id, $feeProducts))
				? false
				: true;
			?>
			<?php $productRowSettings['cartIndex'] = $i + 1; ?>
			<?php echo RedshopbLayoutHelper::render('checkout.products.row', $productRowSettings);?>
		<?php endfor;?>

		<?php if ($showDiscountColumn): ?>
			<?php echo RedshopbLayoutHelper::render(
				'checkout.products.discount_totals',
				array(
					'customerOrder' => $customerOrder,
					'currency' => $currency,
					'shippingPrice' => $shippingPrice,
					'subtotalWithoutDiscounts' => $subtotalWithoutDiscounts,
					'colspan' => $colspan,
					'isFromOrder' => $isFromOrder
				)
			); ?>
		<?php endif;?>

		</tbody>
	</table>
<?php $hasTotal      = isset($data['total']) && ($data['total'] != false || $countCustomerSubtotals > 1);?>
<?php $hasTaxes      = !empty($customerOrder->taxs);?>
<?php $hasDiscount   = (isset($customerOrder->discount) && $customerOrder->discount > 0);?>
<?php $showSubtotals = (( $hasTotal && $subtotalWithoutDiscounts > 0) || $hasTaxes || $hasDiscount);?>

<?php if ($showSubtotals && !$showDiscountColumn): ?>
	<?php echo RedshopbLayoutHelper::render(
		'checkout.products.subtotals',
		array(
			'currency' => $currency,
			'subtotalWithoutDiscounts' => $subtotalWithoutDiscounts,
			'isEmail' => $isEmail
		)
	); ?>
<?php endif;?>

<?php if ($hasDiscount):?>
	<?php if (!isset($customerOrder->discount_type)):?>
		<?php $customerOrder->discount_type = 'total';?>
	<?php endif;?>

	<?php $discount = $customerOrder->discount . '%';?>

	<?php if ($customerOrder->discount_type == 'total'):?>
		<?php $discount = RedshopbHelperProduct::getProductFormattedPrice($customerOrder->discount, $currency);?>
	<?php endif;?>

	<?php $style = '';?>

	<?php if ($isEmail):?>
		<?php $style = ' style="text-align: right"';?>
	<?php endif;?>
	<div class="row-fluid">
		<div class="span10">
			<div class="pull-right text-right">
				<strong><?php echo Text::_('COM_REDSHOPB_OFFER_GLOBAL_DISCOUNT'); ?></strong>
			</div>
		</div>
		<div class="span2 tnumber"<?php echo $style;?>>
			<?php echo $discount; ?>
		</div>
	</div>
<?php endif;?>

<?php echo JHtmlForm::token();
