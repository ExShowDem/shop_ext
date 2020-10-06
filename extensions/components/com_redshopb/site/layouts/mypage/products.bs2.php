<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Views
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;

$products     = isset($displayData['items']) ? $displayData['items'] : $this->items;
$collectionId = isset($displayData['collectionId']) ? $displayData['collectionId'] : 0;
$config       = ComponentHelper::getParams('com_redshopb');
$width        = $config->get('thumbnail_width', 144);
$height       = $config->get('thumbnail_height', 144);
$size         = $width . 'x' . $height;
$i            = 1;
$isShop       = RedshopbHelperPrices::displayPrices();
$cartPrefix   = (isset($cartPrefix) && !empty($cartPrefix) ? '_' . $cartPrefix : null);

$extThis                = new stdClass;
$extThis->dropDownTypes = array();
$extThis->staticTypes   = array();

$user                          = RedshopbHelperCommon::getUser();
$extThis->placeOrderPermission = ($user->b2cMode || RedshopbHelperACL::getPermission('place', 'order'));
?>
<?php if (empty($products) || empty($products->items)):?>
	<div class="productList container-fluid">
		<?php echo RedshopbLayoutHelper::render('common.nodata');?>
	</div>
	<?php return;?>
<?php endif;?>

<div class="productList container-fluid">
	<?php foreach ($products->ids as $productId):
		$productData        = $products->productData[$productId];
		$mainTemplateEntity = RedshopbEntityCategory::load($products->productData[$productId]->category_id);
		?>
		<?php echo RedshopbHelperTemplate::renderTemplate('list-product', 'shop', null, compact(array_keys(get_defined_vars())));?>
	<?php endforeach;?>
</div>
