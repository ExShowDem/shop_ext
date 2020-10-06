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

HTMLHelper::_('vnrbootstrap.tooltip');

$isOffer = isset($isOffer) ? $isOffer : false;
$config  = RedshopbEntityConfig::getInstance();

/** @var Form $form */
$form                       = $displayData['form'];
$items                      = $displayData['items'];
$customerOrder              = $displayData['customerOrder'];
$subtotalWithoutDiscounts   = $customerOrder->subtotalWithoutDiscounts;
$total                      = $customerOrder->total;
$countCustomerSubtotals     = $displayData['countCustomerSubtotals'];
$displayData['showStockAs'] = isset($displayData['showStockAs']) ? $displayData['showStockAs'] : 'hide';
$displayData['view']        = isset($displayData['view']) ? $displayData['view'] : '';
$displayData['canEdit']     = isset($displayData['canEdit']) ? $displayData['canEdit'] : false;

$dispatcher  = RFactory::getDispatcher();
$isFromOrder = (isset($displayData['orderId']) && $displayData['orderId']) ? true : false;
$results     = $dispatcher->trigger('onRedshopbOrderVariablesReview', array(compact(array_keys(get_defined_vars()))));

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

foreach ($displayData['items'] as $item)
{
	if (isset($item->discount) && $item->discount > 0)
	{
		$showDiscountColumn = true;
	}

	if (!empty($item->attributes))
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

if (!$showAttributes && $hasAttributesColumn !== false)
{
	unset($checkoutFields[$hasAttributesColumn]);
}

if (!$showDelivery && $hasStockRoomIdColumn !== false)
{
	unset($checkoutFields[$hasStockRoomIdColumn]);
}

if ($displayData['showStockAs'] == 'hide' && $hasStockColumn !== false)
{
	unset($checkoutFields[$hasStockColumn]);
}

$tableIdPrefix = $displayData['customerType'] . '_' . $displayData['customerId'];

if ($isOffer)
{
	$tableIdPrefix .= '_offer';
}

$fooHiddenDefault = array('price_without_discount', 'final_price', 'attributes', 'discount');

if (isset($display['userCart']))
{
	$fooHiddenDefault = array('product_item_code','price_without_discount','final_price','attributes','discount','stock');
}

$fooTableHiddenFields = $config->get('checkout_hidden_fields', $fooHiddenDefault);

// Call event for support 3rd plugins can implement checkout layout.
RFactory::getDispatcher()->trigger('onVanirPrepareCheckoutFields', array(&$checkoutFields, &$displayData['items'], $form));

$colspan = count($checkoutFields) + 1;

$productRowSettings                         = array();
$productRowSettings['items']                = $displayData['items'];
$productRowSettings['config']               = $config;
$productRowSettings['isEmail']              = $displayData['isEmail'];
$productRowSettings['isOffer']              = $isOffer;
$productRowSettings['isFromOrder']          = $isFromOrder;
$productRowSettings['currency']             = $displayData['currency'];
$productRowSettings['customerId']           = $displayData['customerId'];
$productRowSettings['customerType']         = $displayData['customerType'];
$productRowSettings['checkoutFields']       = $checkoutFields;
$productRowSettings['fooTableHiddenFields'] = $fooTableHiddenFields;
$productRowSettings['lockquantity']         = $displayData['lockquantity'];
$productRowSettings['checkbox']             = $displayData['checkbox'];
$productRowSettings['view']                 = $displayData['view'];
$productRowSettings['showStockAs']          = $displayData['showStockAs'];
$productRowSettings['delivery']             = isset($displayData['delivery']) ? $displayData['delivery'] : '';
$productRowSettings['form']                 = $displayData['form'];
$productRowSettings['canEdit']              = $displayData['canEdit'];
$productRowSettings['showTaxes']            = $config->getInt('show_taxes_in_cart_module', 1);

$isOrderForm         = ($displayData['view'] == 'order');
$foundDelayedProduct = false;
?>
<?php if (!$isOrderForm):?>
	<form class="adminForm adminFormOrder" id="adminForm_<?php echo $tableIdPrefix;?>" method="post" action="<?php echo RedshopbRoute::_('index.php?option=com_redshopb&view=shop');?>">
	<input type="hidden" name="task" value="">
	<?php echo JHtmlForm::token();?>
<?php endif;?>
	<table class="table table-condensed footable js-redshopb-footable redshopb-footable"
		   id="tableCustomer_<?php echo $tableIdPrefix; ?>">
		<thead>
		<tr>
			<?php if ($displayData['checkbox']) : ?>
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
		<?php
		foreach ($items AS $key => $item)
		{
			$params = $item->params;

			if ($params->get('delayed_order', 0) == 1)
			{
				$foundDelayedProduct = true;
				continue;
			}

			$productRowSettings['item']      = $item;
			$productRowSettings['cartIndex'] = $key + 1;
			$productRowSettings['canEdit']   = ($displayData['canEdit'] == false || in_array($productRowSettings['item']->product_item_id, $displayData['feeProducts'])) ? false : true;
			echo RedshopbLayoutHelper::render('checkout.products.row', $productRowSettings);
		}
		?>

		<?php if ($foundDelayedProduct): ?>
		</tbody>
		<thead>
		<tr>
			<th colspan="<?php echo ($colspan - 1) ?>"><h3><?php echo Text::_('COM_REDSHOPB_SHOP_DELAY_ORDER') ?></h3></th>
			<th data-visible="true">&nbsp;</th>
		</tr>
		<tr>
			<?php if ($displayData['checkbox']) : ?>
				<th>&nbsp;</th>
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
		<?php
		foreach ($items AS $key => $item)
		{
			$params = $item->params;

			if ($params->get('delayed_order', 0) == 0)
			{
				continue;
			}

			$productRowSettings['item']      = $item;
			$productRowSettings['cartIndex'] = $key + 1;
			$productRowSettings['canEdit']   = ($displayData['canEdit'] == false || in_array($productRowSettings['item']->product_item_id, $displayData['feeProducts'])) ? false : true;
			echo RedshopbLayoutHelper::render('checkout.products.row', $productRowSettings);
		}
		?>
		<?php endif; ?>

		<?php if ($showDiscountColumn): ?>
			<?php echo RedshopbLayoutHelper::render(
				'checkout.products.discount_totals',
				array(
					'customerOrder' => $customerOrder,
					'currency' => $displayData['currency'],
					'shippingPrice' => $displayData['shippingPrice'],
					'subtotalWithoutDiscounts' => $subtotalWithoutDiscounts,
					'colspan' => $colspan,
					'isFromOrder' => $isFromOrder
				)
			); ?>
		<?php endif;?>

		</tbody>
	</table>
<?php $hasTotal      = ($customerOrder->total != false || $displayData['countCustomerSubtotals'] > 1);?>
<?php $hasTaxes      = !empty($customerOrder->taxs);?>
<?php $hasDiscount   = (isset($customerOrder->discount) && $customerOrder->discount > 0);?>
<?php $showSubtotals = (( $hasTotal && $subtotalWithoutDiscounts > 0) || $hasTaxes || $hasDiscount);?>

<?php if ($showSubtotals && !$showDiscountColumn): ?>
	<?php echo RedshopbLayoutHelper::render(
		'checkout.products.subtotals',
		array(
			'currency' => $displayData['currency'],
			'subtotalWithoutDiscounts' => $subtotalWithoutDiscounts,
			'isEmail' => $displayData['isEmail']
		)
	); ?>
<?php endif;?>

<?php if ($hasDiscount):?>
	<?php if (!isset($customerOrder->discount_type)):?>
		<?php $customerOrder->discount_type = 'total';?>
	<?php endif;?>

	<?php $discount = $customerOrder->discount . '%';?>

	<?php if ($customerOrder->discount_type == 'total'):?>
		<?php $discount = RedshopbHelperProduct::getProductFormattedPrice($customerOrder->discount, $displayData['currency']);?>
	<?php endif;?>

	<?php $style = '';?>

	<?php if ($displayData['isEmail']):?>
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

<?php if (!$isOrderForm):?>
	</form>
<?php endif;
