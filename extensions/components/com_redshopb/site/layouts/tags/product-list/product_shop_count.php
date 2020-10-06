<?php
/**
 * @package     Aesir.E-Commerce.Tag
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

$quantity = 0;

foreach ($products->items as $item)
{
	$quantity += floatval($item->pkg_size);
}

?>

<span id="productsShopCount" class="products-shop-count">
	<?php echo $quantity;?>
</span>
