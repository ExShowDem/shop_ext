<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Layouts.Templates.Product-List-Style.Massive
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

/**
 * Layout variables
 * =================================
 * @var   array  $displayData Layout data
 * @var   object $extThis     This object
 * @var   string $cartPrefix  Cart prefix
 */
extract($displayData);

$config     = RedshopbApp::getConfig();
$width      = $config->get('thumbnail_width', 144);
$height     = $config->get('thumbnail_height', 144);
$size       = $width . 'x' . $height;
$i          = 1;
$user       = RedshopbHelperUser::getUser(Factory::getUser()->id, 'joomla');
$isShop     = RedshopbHelperPrices::displayPrices();
$cartPrefix = (isset($cartPrefix) && !empty($cartPrefix) ? '_' . $cartPrefix : null);
$categoryId = !empty($extThis->category->id) ? $extThis->category->id : 0;

$url         = RedshopbRoute::_('index.php?option=com_redshopb&view=shop', false);
$image       = HTMLHelper::image('media/com_redshopb/images/priceLoading.gif', '');
$loadingText = Text::_('COM_REDSHOPB_VOLUME_PRICE_LOADING');

?>
<script>
	jQuery(document).ready(function () {
		redSHOPB.shop.init('<?php echo $url; ?>', '<?php echo $image; ?>', '<?php echo $loadingText; ?>');
	});
</script>
<div class="productList">
	<?php if (empty($products->items)): ?>
		<?php echo RedshopbLayoutHelper::render('common.nodata'); ?>
	<?php else : ?>
		<?php
		foreach ($products->ids as $productId) :
			$productData        = $products->productData[$productId];
			$mainTemplateEntity = RedshopbEntityCategory::load($products->productData[$productId]->category_id);
			?>
			{template.massive-product}
		<?php endforeach; ?>
	<?php endif; ?>
</div>
