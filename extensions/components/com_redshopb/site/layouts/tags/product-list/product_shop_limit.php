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

$params          = empty($displayData['params']) ? array() : explode(',', $displayData['params']);
$multiOf         = empty($params[0]) ? 12 : $params[0];
$max             = empty($params[1]) ? 50 : $params[1];
$default         = empty($params[2]) ? $multiOf : $params[2];
$i               = $multiOf;
$app             = Factory::getApplication();
$productsPerPage = (int) $app->input->getInt(
	'product_shop_limit', $app->getUserState('shop.productLimit', RedshopbApp::getConfig()->get('shop_products_per_page', $default))
);
?>
<select name="product_shop_limit" class="input-mini" onchange="redSHOPB.shop.updateProductsLimit(event);">
	<?php while ($i <= $max): ?>
	<option value="<?php echo $i?>" <?php echo ($i == $productsPerPage) ? 'selected="selected"' : ''; ?>><?php echo $i; ?></option>
		<?php $i += $multiOf; ?>
	<?php endwhile; ?>
</select>
