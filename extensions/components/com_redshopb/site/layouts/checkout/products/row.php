<?php
/**
 * @package     Aesir.E-Commerce.Admin
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;

$items                = $displayData['items'];
$item                 = $displayData['item'];
$form                 = $displayData['form'];
$config               = $displayData['config'];
$canEdit              = $displayData['canEdit'];
$isEmail              = $displayData['isEmail'];
$isOffer              = $displayData['isOffer'];
$isFromOrder          = $displayData['isFromOrder'];
$cartIndex            = $displayData['cartIndex'];
$currency             = $displayData['currency'];
$customerId           = $displayData['customerId'];
$customerType         = $displayData['customerType'];
$checkoutFields       = $displayData['checkoutFields'];
$fooTableHiddenFields = $displayData['fooTableHiddenFields'];
$quantityDisabled     = ($displayData['lockquantity'] || !$canEdit || $isOffer);
$tdClasses            = array('orderItemRow');
$tdTitle              = '';
$itemParams           = isset($item->params) ? $item->params : null;

if (isset($item->enouthOnStock) && $item->enouthOnStock == false)
{
	$tdClasses[] = 'error hasTooltip';
	$tdTitle     = 'title="' . Text::_('PLG_REDSHOPB_KIER_SPECIFIC_STOCKROOM_DONT_HAVE_SELECTED_PRODUCT') . '"';
}

// Settings for checkout.products.checkbox
$checkboxSettings                 = array();
$checkboxSettings['item']         = $item;
$checkboxSettings['isEmail']      = $isEmail;
$checkboxSettings['canEdit']      = $canEdit;
$checkboxSettings['customerId']   = $customerId;
$checkboxSettings['customerType'] = $customerType;
$showCheckbox                     = $displayData['checkbox'];

$view           = $displayData['view'];
$group          = null;
$fieldIdPostfix = '';

// @todo this needs to be refactored to unify the form input names see VNR-4047
if ($view != 'order')
{
	$group = 'customer-' . $customerType . '_customerId-' . $customerId;

	foreach (RedshopbHelperCart::cartFieldsForCheck() as $itemForCheck)
	{
		$group .= '_' . $itemForCheck;

		if (!property_exists($item, $itemForCheck))
		{
			continue;
		}

		$group .= '-' . $item->{$itemForCheck};
	}

	$group .= '_currencyId-' . $item->currency_id;
}
elseif ($view == 'order' || $view == 'orders')
{
	$fieldIdPostfix = $item->id;

	foreach (RedshopbHelperCart::cartFieldsForCheck($isFromOrder) as $itemForCheck)
	{
		$fieldIdPostfix .= '_';

		if (!property_exists($item, $itemForCheck))
		{
			continue;
		}

		$fieldIdPostfix .= $item->{$itemForCheck};
	}
}

$cartShowImage   = $config->getInt('checkout_show_product_image', 0);
$thumbnailWidth  = $config->getInt('checkout_image_width', 256);
$thumbnailHeight = $config->getInt('checkout_image_height', 256);

// Settings for checkout.products.{$fieldName}
$layoutSettings = array(
	'items'            => $displayData['items'],
	'item'             => $item,
	'isEmail'          => $isEmail,
	'isOffer'          => $isOffer,
	'currency'         => $currency,
	'cartShowImage'    => $cartShowImage,
	'thumbnailWidth'   => $thumbnailWidth,
	'thumbnailHeight'  => $thumbnailHeight,
	'customerId'       => $displayData['customerId'],
	'customerType'     => $displayData['customerType'],
	'showStockAs'      => $displayData['showStockAs'],
	'stockPresented'   => $config->getString('stock_presented', 'semaphore'),
	'showTaxes'        => $displayData['showTaxes'],
	'delivery'         => $displayData['delivery'],
	'quantityDisabled' => $quantityDisabled
);
?>

<tr <?php echo 'class="' . implode(' ', $tdClasses) . '"' . $tdTitle;?>>

	<?php if ($showCheckbox): ?>
		<?php echo RedshopbLayoutHelper::render('checkout.products.checkbox', $checkboxSettings);?>
	<?php endif; ?>

	<?php foreach ($checkoutFields AS $fieldName) :?>
		<?php $stockroom = null;?>
		<?php $field     = $form->getField($fieldName);?>
		<?php $hidePrice = false;?>
		<?php RFactory::getDispatcher()->trigger('onBeforeRedshopbProcessTagPrice', array(&$item->price, &$hidePrice, 0, $item->product_id));?>

		<?php if ($view != 'order'):?>
			<?php $field->group = $group;?>
			<?php $field->name  = $field->fieldname;?>
			<?php $field->id    = $field->fieldname;?>
		<?php elseif ($view == 'order' || $view == 'orders'):?>
			<?php $fieldId   = $field->fieldname . '_' . $fieldIdPostfix;?>
			<?php $field->id = $fieldId;?>
		<?php endif;?>

		<?php $field->value                = !empty($item->{$field->fieldname} && !is_array($item->{$field->fieldname})) ? $item->{$field->fieldname} : '';?>

		<?php $layoutSettings['field']     = $field;?>
		<?php $layoutSettings['hidePrice'] = $hidePrice;?>

		<?php echo RedshopbLayoutHelper::render('checkout.products.' . $field->fieldname, $layoutSettings);?>
	<?php endforeach; ?>

	<td data-visible="true">
		<?php
		if ($canEdit != false && !$isOffer):
			$buttonSuffix = $customerType . '_' . $customerId;

			foreach (RedshopbHelperCart::cartFieldsForCheck($isFromOrder) as $itemForCheck)
			{
				$buttonSuffix .= '_';

				if (property_exists($item, $itemForCheck))
				{
					$buttonSuffix .= $item->{$itemForCheck};
				}
			}

			$buttonSuffix .= '_' . $cartIndex;

			// It make sense only if more than one item in the cart and it is not already ordered item
			if (RedshopbApp::getConfig()->getAllowSplittingOrder()
				&& count($items) > 1
				&& empty($item->order_id)
				&& $canEdit):
				if ($itemParams && $itemParams->get('delayed_order', 0) == 1): ?>
						<a href="javascript:void(0);"
						   class="btn btn-small btn-info item-back-to-main-order logDItem-<?php echo $item->product_item_id ?>"
						   name="order-delay-item_<?php echo $buttonSuffix; ?>"
						   data-cart_item_hash="<?php echo $item->hash; ?>"
						   onclick="redSHOPB.shop.cart.itemBackToMainOrder(event);" data-checkout>
							<i class="icon icon-arrow-up-2"></i>
							<?php echo Text::_('COM_REDSHOPB_SHOP_BACK_TO_MAIN_ORDER'); ?>
						</a>
				<?php else:
					$amountOfItemsInDefaultOrder = 0;

	foreach ($items as $checkItem)
					{
		if ($checkItem->params->get('delayed_order', 0) == 0)
		{
			$amountOfItemsInDefaultOrder++;
		}
	}

	if ($amountOfItemsInDefaultOrder > 1):
		$app          = Factory::getApplication();
		$shippingDate = $app->getUserState('checkout.shipping_date', array());
		$userDate     = null;
		$isAvailable  = true;

		if (array_key_exists($customerType . '_' . $customerId, $shippingDate))
		{
			$userDate = $shippingDate[$customerType . '_' . $customerId];
		}

		if ($userDate)
		{
			$isAvailable = RedshopbHelperStockroom::checkIfProductCanBeShippedInTime($item, $customerId, $customerType, $userDate);
		}

		if (!$isAvailable):
			?>
			<a href="javascript:void(0);"
			   class="btn btn-small btn-info order-delay-item logDItem-<?php echo $item->product_item_id ?>"
			   name="order-delay-item_<?php echo $buttonSuffix; ?>"
			   onclick="redSHOPB.shop.cart.delayItem(event);" data-checkout>
				<i class="icon icon-arrow-down-2"></i>
				<?php echo Text::_('COM_REDSHOPB_SHOP_DELAY_ORDER'); ?>
			</a>
		<?php endif; ?>
	<?php endif; ?>
				<?php endif; ?>
			<?php endif; ?>

			<?php if (Factory::getApplication()->input->get('view') != 'order'): ?>
			<a href="javascript:void(0);"
			   class="btn btn-small btn-danger order-delete-item logDItem-<?php echo $item->product_item_id ?>"
				<?php echo (!$canEdit) ? 'disabled="disabled"' : ''; ?>
			   name="order-delete-item_<?php echo $buttonSuffix; ?>"
			   onclick="redSHOPB.shop.cart.removeItem(event);" data-checkout>
				<i class="icon icon-trash"></i>
				<?php echo Text::_('COM_REDSHOPB_SHOP_REMOVE'); ?>
			</a>
			<?php endif; ?>
		<?php endif; ?>
	</td>
</tr>
