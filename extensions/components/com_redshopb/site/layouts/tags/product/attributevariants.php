<?php
/**
 * @package     Aesir.E-Commerce.Tag
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;

?>
<div class="row clear" id="tableVariants_<?php echo $extThis->product->collectionId ?>_<?php echo $product->id ?>">
	<?php
	$customerId       = Factory::getApplication()->getUserStateFromRequest('shop.customer_id', 'customer_id', 0, 'int');
	$customerType     = Factory::getApplication()->getUserStateFromRequest('shop.customer_type', 'customer_type', '', 'string');
	$prices           = (!empty($extThis->product->prices)) ? $extThis->product->prices : array();
	$dropDownSelected = isset($extThis->product->dropDownSelected[$product->id]) ? $extThis->product->dropDownSelected[$product->id] : null;

	echo RedshopbLayoutHelper::render('shop.attributesvariants', array(
			'staticTypes'          => $extThis->product->staticTypes,
			'collectionId'         => '',
			'dynamicTypes'         => $extThis->product->dynamicTypes,
			'issetItems'           => $extThis->product->issetItems,
			'issetDynamicVariants' => $extThis->product->issetDynamicVariants,
			'productId'            => $product->id,
			'displayProductImages' => false,
			'productImages'        => null,
			'prices'               => $prices,
			'displayAccessories'   => false,
			'accessories'          => null,
			'showStockAs'          => $extThis->product->showStockAs,
			'currency'             => $extThis->product->currency,
			'dropDownSelected'     => $dropDownSelected,
			'customerId'           => $customerId,
			'customerType'         => $customerType,
			'placeOrderPermission' => $extThis->placeOrderPermission
		)
	);
	?>
</div>
