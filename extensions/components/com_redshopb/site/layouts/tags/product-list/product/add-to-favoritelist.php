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

$app = Factory::getApplication();

if ($app->getUserState('shop.customer_id', 0)
	&& $app->getUserState('shop.customer_type', '') == 'employee') : ?>
	<?php RHelperAsset::load('redshopbcart.js', 'com_redshopb');?>

	<button type="button" class="btn btn-info add-to-favoritelist<?php if ($productData->favoritelists > 0) : ?> added<?php
																 endif; ?>" name="addtofavorite_<?php echo $productId; ?>"
			id="addtofavorite_<?php echo $productId; ?>">
		<i class="icon-star"></i>
	</button>
<?php endif;
