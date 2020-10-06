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
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Session\Session;

RHelperAsset::load('product_sheets.css', 'com_redshopb');

$action = RedshopbRoute::_('index.php?option=com_redshopb&view=product_sheets');

// HTML helpers
HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('vnrbootstrap.tooltip');
HTMLHelper::_('rjquery.chosen', 'select');
HTMLHelper::_('rholder.holder');
HTMLHelper::_('rsearchtools.main');
HTMLHelper::_('rjquery.flexslider', '.flexslider', array('slideshow' => false, 'directionNav' => false, 'animation' => 'slide', 'animationLoop' => false));

echo RedshopbBrowserBreadcrumbs::renderBreadcrumbs();
?>
<script type="text/javascript">

		function ajaxExecute(tabName) {
			jQuery.ajax({
				url: '<?php echo Uri::root(); ?>index.php?option=com_redshopb&view=product_sheets&task=product_sheets.ajax' + tabName,
				data : {
					"<?php echo Session::getFormToken() ?>": 1
				},
				async: true,
				type: 'post',
				dataType: 'text',
				beforeSend: function (xhr) {
					jQuery('.' + tabName + '-content .spinner').show();
					jQuery('.' + tabName + '-content').addClass('opacity-40');
				}
			}).done(function (data) {
				jQuery('.' + tabName + '-content .spinner').hide();
				jQuery('.' + tabName + '-content')
					.removeClass('opacity-40')
					.html(data);

				jQuery('select').chosen();
				jQuery('.chzn-search').hide();
				jQuery('.hasTooltip').tooltip({"animation": true, "html": true, "placement": "top",
					"selector": false, "title": "", "trigger": "hover focus", "delay": 0, "container": false});

				if (tabName == 'selectedproducts')
				{
					jQuery('#selectedproducts select').prop('disabled', true).trigger("liszt:updated");
				}
			});
		}

		function removeFromProductList(obj) {
			var productId = jQuery(obj).attr('name').replace('product_','');
			var dropDownSelected = jQuery(obj).parent().parent().find('select.dropDownAttribute').val();
			var reqData		= {productId: productId, dropDownSelected: dropDownSelected, "<?php echo Session::getFormToken() ?>": 1};

			if (typeof productId !== 'undefined')
			{
				jQuery(obj)
					.parents('.product-box')
					.remove();
				jQuery.ajax({
					url: '<?php echo Uri::root(); ?>index.php?option=com_redshopb&task=product_sheets.ajaxRemoveProductFromList',
					type: 'post',
					data: reqData,
					dataType: 'json'
				}).done(function (data) {
					displayMessage(data);
				});
			}
		}

		function addToProductList(obj) {
			var productId = jQuery(obj).attr('name').replace('product_','');
			var dropDownSelected = jQuery(obj).parent().parent().find('select.dropDownAttribute').val();
			var reqData		= {productId: productId, dropDownSelected: dropDownSelected, "<?php echo Session::getFormToken() ?>": 1};
			if (typeof productId !== 'undefined')
			{
				jQuery.ajax({
					url: '<?php echo Uri::root(); ?>index.php?option=com_redshopb&task=product_sheets.ajaxAddProductToList',
					type: 'post',
					data: reqData,
					dataType: 'json'
				}).done(function (data) {
					ajaxExecute('selectedproducts');
					displayMessage(data);
				});
			}
		}

		jQuery(document).ready(function () {
			jQuery('body').on('change', '.dropDownAttribute', function(){
				var $this = jQuery(this),
					parameters = $this.attr('id').split('_'),
					dropDownSelected = $this.val(),
					$productThumb = jQuery('#productThumb_' + parameters[1] + ' .thumbnail');
				jQuery.ajax({
					url: '<?php echo Uri::root(); ?>index.php?option=com_redshopb&task=product_sheets.ajaxChangeDropDownAttribute'
						+ '&product_id=' + parameters[1]
						+ '&drop_down_selected=' + dropDownSelected,
					type: 'post',
					data : {
						"<?php echo Session::getFormToken() ?>": 1
					},
					beforeSend: function () {
						$productThumb.html(
							'<div class="spinner pagination-centered"><?php echo HTMLHelper::image('media/com_redshopb/images/ajax-loader.gif', '') ?></div>'
						).find('.spinner');
					}
				}).done(function (data) {
					$productThumb.html(data);
				});
			});
			ajaxExecute('products');
			ajaxExecute('selectedproducts');

			jQuery('body').on('click', '.btn-remove-from-list', function(){
				removeFromProductList(this);
				return true;
			});

			jQuery('body').on('click', '.btn-add-to-list', function(){
				addToProductList(this);
				return true;
			});
		});
</script>

<div class="redshopb-product_sheets">
	<form action="<?php echo $action; ?>" method="post" name="adminForm" id="adminForm" class="form-horizontal">
		<input type="hidden" name="option" value="com_redshopb">
		<input type="hidden" name="task" value="">
		<?php echo HTMLHelper::_('form.token'); ?>
	</form>
	<div class="container-fluid">
		<div class="row-fluid product_sheets">
			<div class="span12">
				<ul class="nav nav-tabs">
					<li class="active">
						<a href="#products" data-toggle="tab"><?php echo Text::_('COM_REDSHOPB_PRODUCT_LIST_TITLE'); ?></a>
					</li>
					<li>
						<a href="#selectedproducts" data-toggle="tab"><?php echo Text::_('COM_REDSHOPB_PRODUCT_SHEETS_SELECTED_PRODUCTS'); ?></a>
					</li>
				</ul>
				<div class="tab-content">
					<div class="tab-pane active" id="products">
						<div class="container-fluid">
							<div class="row-fluid products-content">
								<div class="span12">
									<div class="spinner pagination-centered">
										<?php echo HTMLHelper::image('media/com_redshopb/images/ajax-loader.gif', '') ?>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="tab-pane" id="selectedproducts">
						<div class="container-fluid">
							<div class="row-fluid selectedproducts-content">
								<div class="span12">
									<div class="spinner pagination-centered">
										<?php echo HTMLHelper::image('media/com_redshopb/images/ajax-loader.gif', '') ?>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
