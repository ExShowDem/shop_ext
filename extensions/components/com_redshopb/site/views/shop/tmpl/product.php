<?php
/**
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Views
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Session\Session;

HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('vnrbootstrap.tooltip');
HTMLHelper::_('rholder.holder');
HTMLHelper::_('rjquery.chosen', 'select');
HTMLHelper::_('rsortablelist.main');

RHelperAsset::load('shop.css', 'com_redshopb');
RHelperAsset::load('cloudzoom.css', 'com_redshopb');
RHelperAsset::load('cloudzoom.js', 'com_redshopb');

$doc     = Factory::getDocument();
$product = $this->product->items[0];
$config  = RedshopbApp::getConfig();
$width   = $config->get('product_image_width', 256);
$height  = $config->get('product_image_height', 256);
$isShop  = RedshopbHelperPrices::displayPrices();

$url         = RedshopbRoute::_('index.php?option=com_redshopb&view=shop', false);
$image       = HTMLHelper::image('media/com_redshopb/images/priceLoading.gif', '');
$loadingText = Text::_('COM_REDSHOPB_VOLUME_PRICE_LOADING');

?>
<script>
	jQuery(document).ready(function()
	{
		redSHOPB.shop.init('<?php echo $url;?>',
			'<?php echo $image;?>',
			'<?php echo $loadingText;?>');
	});

	var rsbftTablet = 768;
	var rsbftPhone = 480;
	var fooTableClass = '.variants-table';
	var fooTableMonitorBreakpoints = true;
</script>

<?php
RedshopbHtml::loadFooTable();

$sliderBox         = '#productThumbs_' . $this->product->collectionId . '_' . $product->id . ' .bigProductThumbs';
$sliderMiniBox     = '#productThumbs_' . $this->product->collectionId . '_' . $product->id . ' .smallProductThumbs';
$flexsliderOptions = array(
	'slideshow'     => false,
	'directionNav'  => false,
	'animation'     => 'slide',
	'animationLoop' => false,
	'itemWidth'     => $width
);

$flexsliderOptionsReg = RedshopbHelperShop::options2Jregistry($flexsliderOptions);

$action = RedshopbRoute::_('index.php?option=com_redshopb&view=shop');

echo RedshopbBrowserBreadcrumbs::renderBreadcrumbs();
?>

<script type="text/javascript">
	(function($){
		$(document).ready(function(){
			$('.productList').on('change', '.dropDownAttribute', function () {
				var $this = $(this),
					parameters = $this.attr('id').split('_'),
					collectionid = $this.data('collection'),
					currencyid = $this.data('currency'),
					dropDownSelected = $this.val(),
					$tableVariants = $('#tableVariants_' + collectionid + '_' + parameters[1]),
					$productThumbs = $('#productThumbs_' + collectionid + '_' + parameters[1]),
					$accessory = $('#accessory_' + collectionid + '_' + parameters[1]),
					tableVariantsHeight = $tableVariants.height(),
					productThumbsHeight = $productThumbs.height(),
					thumbStyle = $('#productThumbs_' + collectionid + '_' + parameters[1]).data('style');
				$.ajax({
					url: '<?php echo Uri::root(); ?>index.php?option=com_redshopb&task=shop.ajaxChangeDropDownAttribute'
					+ '&product_id=' + parameters[1]
					+ '&drop_down_selected=' + dropDownSelected
					+ '&collection_id=' + collectionid
					+ '&currency_id=' + currencyid
					+ '&forlayout=products'
					+ '&thumbStyle=' + thumbStyle,
					type: 'POST',
					data : {
						"<?php echo Session::getFormToken() ?>": 1
					},
					beforeSend: function () {
						$tableVariants.empty().html(
							'<div class="spinner pagination-centered"><?php echo HTMLHelper::image('media/com_redshopb/images/ajax-loader.gif', '') ?></div>'
						).find('.spinner').height(tableVariantsHeight);
						$productThumbs.empty().html(
							'<div class="spinner pagination-centered"><?php echo HTMLHelper::image('media/com_redshopb/images/ajax-loader.gif', '') ?></div>'
						).find('.spinner').height(productThumbsHeight);
					}
				}).done(function (data) {
					$tableVariants.html(data);
					$productThumbs.empty();
					if ($('#productImages').length > 0 && typeof $.flexslider != 'undefined') {
						$('#productImages .bigProductThumbs').appendTo($productThumbs);
						if ($('#productImages .smallProductThumbs').length > 0){
							$('#productImages .smallProductThumbs').appendTo($productThumbs);
						}
						$('#productImages').remove();
						$productThumbs.removeClass('hide');
						Holder.run();
						CloudZoom.quickStart();
						if ($('<?php echo $sliderMiniBox; ?>').length > 0){
							$('<?php echo $sliderMiniBox; ?>').flexslider(eval('valuesSmallProductThumbs_' + collectionid + '_' + parameters[1]));
							$('<?php echo $sliderBox; ?>').flexslider(eval('valuesProductThumbs_' + collectionid + '_' + parameters[1]));
						}else{
							$('<?php echo $sliderBox; ?>').flexslider(<?php echo $flexsliderOptionsReg->toString() ?>);
						}
					}
					else {
						$productThumbs.addClass('hide');
					}
					$accessory.empty();
					if ($('#divAccessory').length > 0) {
						$('#divAccessory .dropDownAccessory').appendTo($accessory);
						$('#divAccessory').remove();
						var currentAccessory = $('#dropDownAccessory_' + parameters[1]);
						currentAccessory.multiselect({'nonSelectedText': currentAccessory.find('optgroup:first').attr('label')});
					}

					initFootableRedshopb();
					$('.carousel-variants').bind('slid', function () {
						fooTableRedraw();
					});
				});
			});

			$('.carousel-variants').bind('slid', function () {
				$(this).find('div.item.active table.variants-table').each(function () {
					fooTableRedraw($(this).data('footable'));
					fooTableCheckPhoneTabletDesktop($(this).data('footable'));
				});
			});

			$('.productList').on('click', '.clearAmounts', function () {
				var $this = $(this),
					parameters = $this.attr('id').split('_'),
					$tableVariants = $('#tableVariants_' + parameters[1] + '_' + parameters[2]);
				$tableVariants.find('.amountInput').val('');
			});

			CloudZoom.quickStart();
		});
	})(jQuery);
</script>
<?php
$extThis           = $this;
$cartPrefix        = 'inProd' . $product->id;
$isOneProduct      = false;
$fieldsData        = RedshopbHelperProduct::loadProductFields($product->id, true);
$extThis->category = RedshopbEntityCategory::load($extThis->category->id);

if (count($extThis->product->dropDownTypes) == 0 && count($extThis->product->staticTypes) == 0)
{
	$isOneProduct = true;
}

echo RedshopbHelperTemplate::renderTemplate('product', 'shop', $product->template_id, compact(array_keys(get_defined_vars())));
