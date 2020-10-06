<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Views
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;

extract($displayData);

$cartPrefix = (isset($cartPrefix) && !empty($cartPrefix) ? '_' . $cartPrefix : null);
$config     = ComponentHelper::getParams('com_redshopb');
$width      = $config->get('grid_image_width', 172);
$height     = $config->get('grid_image_height', 172);
$i          = 0;
$isShop     = RedshopbHelperPrices::displayPrices();
$columns    = (isset($columns) ? $columns : 3);

if ($columns > 6 || $columns == 5) :
	$columns = 6;
endif;

if ($columns < 1) :
	$columns = 1;
endif;

$spanSize = 12 / $columns;

$doc = Factory::getDocument();
$doc->addStyleDeclaration('.redshopb-product-image img { max-height: ' . $height . 'px; }');

$user        = RedshopbHelperUser::getUser(Factory::getUser()->id, 'joomla');
$url         = RedshopbRoute::_('index.php?option=com_redshopb&view=shop', false);
$image       = HTMLHelper::image('media/com_redshopb/images/priceLoading.gif', '');
$loadingText = Text::_('COM_REDSHOPB_VOLUME_PRICE_LOADING');
?>
<script>
	jQuery(document).ready(function() {
		redSHOPB.shop.init('<?php echo $url;?>',
			'<?php echo $image;?>',
			'<?php echo $loadingText;?>');
	});
</script>
<div class="productList productListGrid container-fluid">
<?php
if (empty($products->items)) :
?>
<?php echo RedshopbLayoutHelper::render('common.nodata'); ?>
<?php
else :
?>
<?php
foreach ($products->ids as $productId) :
	$productData        = $products->productData[$productId];
	$mainTemplateEntity = RedshopbEntityCategory::load($products->productData[$productId]->category_id);

	if (!($i % $columns)) :
		?>
		<div class="row-fluid">
		<?php
	endif;
	?>
	{template.grid-product}
	<?php
	$i ++;

	if (!($i % $columns)) :
		?>
		</div>
		<?php
	endif;
	?>
	<?php
endforeach;

if (($i % $columns)) :
	?>
</div>
	<?php
endif;
	?>
<?php
endif;
?>
</div>
