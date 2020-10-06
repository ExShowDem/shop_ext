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
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Uri\Uri;


$sliderBox         = '#productThumbs_' . $extThis->product->collectionId . '_' . $product->id . ' .bigProductThumbs';
$sliderMiniBox     = '#productThumbs_' . $extThis->product->collectionId . '_' . $product->id . ' .smallProductThumbs';
$flexsliderOptions = array(
	'slideshow'     => false,
	'directionNav'  => false,
	'animation'     => 'slide',
	'animationLoop' => false,
	'itemWidth'     => $width
);

$flexsliderOptionsReg = RedshopbHelperShop::options2Jregistry($flexsliderOptions);

?>
<script>
	(function($) {
		$('.productList, #modalVariants').on('change', '.product-attribute', function () {
			var attr = $(this);
			var parameters = attr.attr('id').split('_');
			var productId = parameters[1];
			var collectionId = attr.data('collection');
			var currencyId = attr.data('currency');
			var productThumbs = $('#productThumbs_' + collectionId + '_' + productId);
			var productThumbsHeight = productThumbs.height();
			var accessory = $('#accessory_' + collectionId + '_' + productId);
			var thumbStyle = productThumbs.data('style');
			var isMain = parseInt(attr.attr('is-main'));
			var values = [];

			$('.product-attribute').each(function (i, e) {
				var ele = $(e);
				var aId = ele.attr('attr-id');
				values.push({
					'aId': aId,
					'aValue': ele.val()
				});
			});

			if (isMain > 0) {
				isMain = attr.attr('attr-id');
			}

			$.ajax({
				url: '<?php echo Uri::root(); ?>index.php?option=com_redshopb&task=shop.ajaxOnAttributeValueChange',
				type: 'POST',
				data: {
					'<?php echo Session::getFormToken() ?>': 1,
					'productId': productId,
					'collectionId': collectionId,
					'currencyId': currencyId,
					'attrValues': values,
					'thumbStyle': thumbStyle,
					'isMain': isMain
				},
				beforeSend: function () {
					if (isMain > 0) {
						productThumbs.empty().html(
							'<div class="spinner pagination-centered"><?php echo HTMLHelper::image('media/com_redshopb/images/ajax-loader.gif', '') ?></div>'
						).find('.spinner').height(productThumbsHeight);
					}
				},
				dataType: 'json'
			}).done(function (data) {
				var allInputs = $('#productItemInput');
				jQuery(data.attributesData).each(function (i, e) {
					if (e.aId != attr.attr('attr-id')) $('select[attr-id=' + e.aId + ']').html(e.html).trigger('liszt:updated');
				});

				if (data.productItemId > 0) {
					var inputs = allInputs.find('input');
					inputs.each(function () {
						var input = $(this);
						var name = input.attr('name').split('_');
						if (name[0] == 'quantity') {
							input.removeAttr('disabled');
						}
						name = name[0] + '_' + name[1] + '_' + data.productItemId;
						input.attr('name', name);
					});
				}
				else {
					allInputs.find('input#quantity_' + productId).attr('disabled', 'disabled');
				}

				allInputs.find('input#price_' + productId).val(data.price);
				allInputs.find('#productPrice').html(data.priceFormatted);

				if (isMain > 0) {
					// Update images
					productThumbs.html(data.imagesHtml).removeClass('hide');
					Holder.run();
					CloudZoom.quickStart();
					if ($('<?php echo $sliderMiniBox; ?>').length > 0) {
						$('<?php echo $sliderMiniBox; ?>').flexslider(eval('valuesSmallProductThumbs_' + collectionId + '_' + productId));
						$('<?php echo $sliderBox; ?>').flexslider(eval('valuesProductThumbs_' + collectionId + '_' + productId));
					} else {
						$('<?php echo $sliderBox; ?>').flexslider(<?php echo $flexsliderOptionsReg->toString() ?>);
					}

					// Update accessories
					accessory.empty().html(data.accessoriesHtml).trigger('liszt:updated');
				}
			});
		});
	})(jQuery);
</script>
<div class="row clear" id="dropdownVariants_<?php echo $extThis->product->collectionId; ?>_<?php echo $product->id; ?>">
<?php
$customerId   = Factory::getApplication()->getUserStateFromRequest('shop.customer_id', 'customer_id', 0, 'int');
$customerType = Factory::getApplication()->getUserStateFromRequest('shop.customer_type', 'customer_type', '', 'string');
$prices       = (!empty($extThis->product->prices)) ? $extThis->product->prices : array();

echo RedshopbLayoutHelper::render(
	'shop.attributeoptions',
	array (
		'collectionId'  => $extThis->product->collectionId,
		'productId'     => $product->id,
		'currency'      => $extThis->product->currency,
		'customerId'    => $customerId,
		'customerType'  => $customerType,
		'attributes'    => $extThis->productAttributes
	)
);
?>
</div>
