<?php
/**
 * @package     Aesir.E-Commerce.Admin
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;

use Joomla\CMS\Language\Text;

$item            = $displayData['item'];
$field           = $displayData['field'];
$isEmail         = $displayData['isEmail'];
$isOffer         = $displayData['isOffer'];
$cartShowImage   = $displayData['cartShowImage'];
$thumbnailWidth  = $displayData['thumbnailWidth'];
$thumbnailHeight = $displayData['thumbnailHeight'];

if ($isEmail)
{
	echo '<td class="field_' . $field->fieldname . '">';
	echo $item->product_sku . ' : ' . $item->product_name;
	$customText = null;
	Factory::getApplication()->triggerEvent('onVanirProductCustomTextGetField', array($item, &$customText, $isOffer));
	echo $customText;
	echo '</td>';

	return;
}

$url = 'index.php?option=com_redshopb&view=shop&layout=product&id=' . $item->product_id;

if (!empty($item->category_id))
{
	$url .= '&category_id=' . (int) $item->category_id;
}

if (!empty($item->collection_id))
{
	$url .= '&collection=' . $item->collection_id;
}

$url         = RedshopbRoute::_($url);
$displayName = $item->product_name;
$productLink = '';

if ($item->category_id != '')
{
	$productLink = $url;
	$displayName = '<a href="' . $url . '">' . $displayName . '</a>';
}

$thumb = '';

if (!isset($item->productItem))
{
	$item->productItem = 0;
}

if ($cartShowImage == 1)
{
	$thumb = RedshopbHelperProduct::getProductImageThumbHtml(
		$item->product_id,
		$item->productItem,
		0,
		true,
		$thumbnailWidth,
		$thumbnailHeight
	);

	if ($thumb == '')
	{
		$thumb = RedshopbHelperMedia::drawDefaultImg(
			$thumbnailWidth, $thumbnailHeight, Text::_('COM_REDSHOPB_NO_IMAGE_LABEL'), '#999999', '#dfdfdf'
		);
	}
}
?>

<td class="field_<?php echo $field->fieldname ?>">
	<?php if ($cartShowImage == 1) : ?>
		<div class='img-thumb'>
			<a href="<?php echo $productLink ?>">
				<?php echo $thumb; ?>
			</a>
		</div>
	<?php endif;?>
	<input type="hidden" value="<?php echo $item->product_id; ?>" id="<?php echo $field->id; ?>"/>
	<?php echo $displayName;?>
	<?php
	$customText = null;
	Factory::getApplication()->triggerEvent('onVanirProductCustomTextGetField', array($item, &$customText, $isOffer));
	echo $customText;
	?>
	<?php if (!empty($item->accessories)): ?>
		<span class="accessory-list">
			<?php // @todo Tables inside of spans are not valid markup ?>
			<table class="table table-condensed table-bordered">
				<tbody>
				<?php foreach ($item->accessories as $accessory) : ?>
					<?php $hideCollection = !empty($accessory['hide_on_collection']);?>
					<?php $hasPrice       = ($accessory['price'] > 0);?>
					<?php
					if (!$hideCollection || $hasPrice) : ?>
						<tr>
						<td>
							<small>
								<?php echo '+ ' . $accessory['sku'] . ' ' . $accessory['product_name']; ?>
							</small>
						</td>
					</tr>
					<?php endif; ?>
				<?php endforeach; ?>
				</tbody>
			</table>
		</span>
	<?php endif;?>
</td>
