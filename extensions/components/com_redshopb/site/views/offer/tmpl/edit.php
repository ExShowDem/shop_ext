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
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Plugin\PluginHelper;

// HTML helpers
HTMLHelper::_('behavior.formvalidation');
HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('vnrbootstrap.tooltip');
HTMLHelper::_('rjquery.chosen', 'select');
HTMLHelper::_('rsearchtools.main');


if (PluginHelper::isEnabled('vanir', 'product_custom_text'))
{
	RHelperAsset::load('script.js', 'plg_vanir_product_custom_text');
}

RedshopbHtml::loadFooTable();
?>

<script type="text/javascript">
	var rsbftPhone = 0;
	var rsbftTablet = 0;
</script>

<script type="text/javascript">
	(function ($){
		$(window).bind("beforeunload", function(){
			if ($('div.offerproducts-content button.save-offer-items').length != 0
				&& !$('div.offerproducts-content button.save-offer-items').hasClass('disabled'))
			{
				return confirm("<?php echo Text::_('COM_REDSHOPB_OFFER_RELOAD_WITHOUT_SAVING_EDITS_WARNING'); ?>");
			}
		});
	})(jQuery);
</script>

<?php
// Variables
$action   = RedshopbRoute::_('index.php?option=com_redshopb&view=offer');
$input    = Factory::getApplication()->input;
$tab      = $input->getString('tab');
$currency = RedshopbHelperPrices::getCurrency($this->item->get('customer_id'), $this->item->get('customer_type'), $this->item->get('collection_id'));

echo RedshopbBrowserBreadcrumbs::renderBreadcrumbs();
?>
<?php
if ($this->item->id)
	:
	?>
	<script type="text/javascript">
		var loadedOfferTabs = {};
		(function ($) {
			function ajaxOfferTabSetup(tabName) {
				$('a[href="#' + tabName + '"]').on('shown.bs.tab', function (e) {
					if (tabName == 'offerproducts' && loadedOfferTabs[tabName] == true) {
						$('.' + tabName + '-content').children().remove();
					}
					// Tab already loaded
					else if (loadedOfferTabs[tabName] == true) {
						return true;
					}

					// Perform the ajax request
					$.ajax({
						url: '<?php echo Uri::root(); ?>index.php?option=com_redshopb&task=offer.ajax' + tabName + '&view=offer&offer_id=' + $('#offer_id').val(),
						type: 'POST',
						data: {'<?php echo Session::getFormToken(); ?>' : 1},
						beforeSend: function () {
							$('.spinner-' + tabName + '-content').show();
							$('#offerTabs').addClass('opacity-40');
						}
					}).done(function (data) {
						$('.spinner-' + tabName + '-content').hide();
						$('#offerTabs').removeClass('opacity-40');
						$('.' + tabName + '-content').html(data);
						$('select').chosen();
						$('.chzn-search').hide();
						$('.hasTooltip').tooltip({
							"animation": true, "html": true, "placement": "top",
							"selector": false, "title": "", "trigger": "hover focus", "delay": 0, "container": false
						});
						loadedOfferTabs[tabName] = true;

						$('.' + tabName + '-content').find('.productQuantity').on('change', function (event)
						{
							redSHOPB.offer.refreshTable(event, 'changeQuantity');
						});
						$('.' + tabName + '-content').find('.offer-item-remove').on('click', function (event)
						{
							redSHOPB.offer.refreshTable(event, 'deleteRow');
						});
					});
				})
			}

			function getGlobalValues(){
				return {
					'offer_id': $('#offer_id').val(),
					'globalDiscount': parseFloat($('.redshopb-offer-form #jform_discount').val()),
					'globalTypeDiscount': $('.redshopb-offer-form .radioPriceCondition input:checked').val(),
					'<?php echo Session::getFormToken(); ?>' : 1
				};
			}

			function getConditions(currentTr) {
				var conditions = {
					'productId': currentTr.find('.productIdField').val(),
					'discount': currentTr.find('.productDiscount').val(),
					'typeDiscount': currentTr.find('.productDiscountType').val(),
					'productItemId': currentTr.find('.productItem').val(),
					'quantity': currentTr.find('.productQuantity').val(),
					'offer_item_id': currentTr.find('.offerItemId').val()
				};

				return $.extend(conditions, getGlobalValues());
			}

			function setGlobalValues(data){
				$('.redshopb-offer-form #globalSubtotal').html(data['globalSubTotal']);
				$('.redshopb-offer-form #globalTotal').html(data['globalTotal']);
			}

			function recalculateGlobalTotal(){
				var conditions = getGlobalValues();
				var $globalTotal = $('.redshopb-offer-form #globalTotal');
				var $globalSubTotal = $('.redshopb-offer-form #globalSubtotal');
				var url = '<?php echo Uri::root(); ?>index.php?option=com_redshopb&task=offer.ajaxGetGlobalTotal';
				$.ajax({
					url: url,
					type: 'POST',
					dataType: 'json',
					data: conditions,
					cache: false,
					beforeSend: function () {
						$globalTotal.html('-');
						$globalSubTotal.html('-');
					}
				}).done(function (data) {
					$globalTotal.html(data['globalTotal']);
					$globalSubTotal.html(data['globalSubTotal']);
				});
			}

			$(document).ready(function () {
				ajaxOfferTabSetup('products');
				ajaxOfferTabSetup('offerproducts');
				var timeoutReference,
						timeout = 500;

				function disableTabs(){
					var $notActiveTabs = $('#offerTabs li:not(.active)');
					$notActiveTabs.addClass('disabled hasTooltip').attr('data-original-title', '<?php echo Text::_('COM_REDSHOPB_OFFER_MAIN_VALUES_ARE_CHANGED'); ?>');
					$('.hasTooltip').tooltip({
						"animation": true, "html": true, "placement": "top",
						"selector": false, "title": "", "trigger": "hover focus", "delay": 0, "container": false
					});
					$('.mainPriceCalculations').hide();
				}

				$('.redshopb-offer-form')
				.on('keyup keypress paste', '.textPriceCondition', function(e){
					if (e.type=='keyup' && e.keyCode!=8) return;
					if (timeoutReference) clearTimeout(timeoutReference);
					timeoutReference = setTimeout(function(){
						if (!timeoutReference) return;
						timeoutReference = null;
						recalculateGlobalTotal();
					}, timeout);})
				.on('blur', '.textPriceCondition', function(){
						if (!timeoutReference) return;
						timeoutReference = null;
						recalculateGlobalTotal();})
				.on('click', '.radioPriceCondition input', function(){
					recalculateGlobalTotal();})
				.on('change', '.mainOfferValues', function(){
					disableTabs();
				});

				$("#offerTabs a[data-toggle=tab]").on("click", function(e) {
					if ($('div.offerproducts-content button.save-offer-items').length != 0
						&& !$('div.offerproducts-content button.save-offer-items').hasClass('disabled'))
					{
						var targetElement = e.target || e.srcElement;
						var target        = $(targetElement);
						var tabName       = target.text().trim();

						if  (tabName == "Products" || tabName == "Details")
						{
							return confirm("<?php echo Text::_('COM_REDSHOPB_OFFER_CHANGE_TAB_WITHOUT_SAVING_EDITS_WARNING'); ?>");
						}
					}

					if ($(this).parent('li').hasClass("disabled")) {
						e.preventDefault();
						return false;
					}
				});

				if ($('#jform_state').val() == 0){
					disableTabs();
				}

				$('.offerScriptInit')
					.on('click', '.offer-item-add', function (event) {
						event.preventDefault();
						var $this = $(this);
						var url = '<?php echo Uri::root(); ?>index.php?option=com_redshopb&task=offer.ajaxAddOfferItem';
						var $productRowMsg = $this.parent('td').find('.product-row-msg');
						var currentTr = $this.parent('td').parent('tr');
						var conditions = getConditions(currentTr);

						var settings = {
							url: url,
							type: 'POST',
							dataType: 'json',
							data: conditions,
							cache: false
						};

						$(redSHOPB.offer).trigger("onShopBeforeAddToOffer", [settings]);

						$.ajax(settings).done(function (data) {
							setGlobalValues(data);
							if (data['statusResult'] == 1)
							{
								$productRowMsg.show().html('<?php echo Text::_('COM_REDSHOPB_OFFER_ADDED_SUCCESSFULLY'); ?>');
							}
							else
							{
								$productRowMsg.show().html('<?php echo Text::_('COM_REDSHOPB_OFFER_PRODUCT_ALREADY_ADDED'); ?>');
							}

							setTimeout(function () {
								$productRowMsg.fadeOut('fast');
							}, 1000);
						});
					})
					.on('change', '.dropdownPriceCondition', function (event) {
						$('.save-offer-items').removeClass('disabled');
						redSHOPB.offer.refreshTable(event, 'changeDiscount');
					})
					.on('keyup keypress paste', '.textPriceCondition', function(event){
						if (event.type=='keyup' && event.keyCode!=8) return;
						var $this = $(this);
						if (timeoutReference) clearTimeout(timeoutReference);
						$('.save-offer-items').removeClass('disabled');
						timeoutReference = setTimeout(function(){
							if (!timeoutReference) return;
							timeoutReference = null;
							redSHOPB.offer.refreshTable(event, 'changeDiscount');
						}, timeout);})
					.on('blur', '.textPriceCondition', function(event){
						if (!timeoutReference) return;
						timeoutReference = null;
						$('.save-offer-items').removeClass('disabled');
						redSHOPB.offer.refreshTable(event, 'changeDiscount');
					})
					.on('click', '.save-offer-items:not(.disabled)', function(event){
						event.preventDefault();
						var form    = $('form#productsOfferForm');
						var rows    = form.find('table tbody tr');
						var url     = '<?php echo Uri::root(); ?>index.php?option=com_redshopb&task=offer.ajaxApplyOfferItems';

						var settings = {
							'offer_id': $('#offer_id').val(),
							'<?php echo Session::getFormToken(); ?>' : 1,
							'form_content' : []
						};

						rows.each(function(){
							let newQuantity       = $(this).find('.productQuantity').val();
							let productId         = $(this).find('.productIdField').val();
							let discount          = $(this).find('.productDiscount').val();
							let typeDiscount      = $(this).find('.productDiscountType').val();
							let productItemId     = $(this).find('.productItem').val();
							let productPrice      = $(this).find('.productPrice').text().trim();
							let productFinalPrice = $(this).find('.productFinalPrice').text().trim();
							let customText        = $(this).find('.custom_text_value').text().trim();

							if (typeof productItemId === 'undefined')
							{
								productItemId = 0;
							}

							if (typeof customText === 'undefined')
							{
								customText = '';
							}

							settings.form_content.push({
								'productId'         : productId,
								'productItemId'     : productItemId,
								'productPrice'      : productPrice,
								'newQuantity'       : newQuantity,
								'productFinalPrice' : productFinalPrice,
								'discount'          : discount,
								'typeDiscount'      : typeDiscount,
								'customText'        : customText
							});
						});

						$.ajax({
							url: url,
							type: 'POST',
							dataType: 'json',
							data: settings,
							beforeSend: function () {
								$('.save-offer-items').addClass('disabled');
							}
						}).done(function (data) {
							if (data['statusResult'] == 0)
							{
								location.reload();
							}
							else
							{
								$('#save-offer-items-message').html(data['errorMessage']);
							}
						});
					});
			});

		})(jQuery);
	</script>
	<?php
	if ($tab)
		:
		?>
		<script type="text/javascript">
			jQuery(document).ready(function () {

				// Show the corresponding tab
				jQuery('#offerTabs a[href="#<?php echo $tab ?>"]').tab('show');
			});
		</script>
		<?php
	endif;
endif;
?>
<script type="text/javascript">
	(function ($) {
		$(document).ready(function () {
			$('#adminForm')
				.on('change', '#jform_customer_type', function () {
					$('.customer_id_class').addClass('hide');
					$('.' + $(this).val() + '_id_class').removeClass('hide');
					$('#jform_' + $(this).val() + '_id_chzn').css("width", "");
				});

			function expirationDateDisplay(){
				if ($('.expirationDateOfferSwitcher input:checked').val() == 1){
					$('.expirationInputDiv').removeClass('hide');
				}else{
					$('.expirationInputDiv').addClass('hide');
				}
			}

			expirationDateDisplay();

			$('.expirationDateOfferSwitcher')
				.on('click', 'label', function(){
					$('#' + $(this).attr('for')).prop('checked', true)
						.change(); // Manually trigger the change event
				})
				.on('change', 'input', function(){
					expirationDateDisplay();
				});
		});
	})(jQuery);
</script>
<div class="redshopb-offer">
	<ul class="nav nav-tabs" id="offerTabs">
		<li class="active">
			<a href="#details" data-toggle="tab">
				<?php echo Text::_('COM_REDSHOPB_OFFER_DETAILS'); ?>
			</a>
		</li>

		<?php
		if ($this->item->id)
			:
			?>
			<li>
				<a href="#products" data-toggle="tab">
					<?php echo Text::_('COM_REDSHOPB_OFFER_PRODUCTS'); ?>
				</a>
			</li>
			<li>
				<a href="#offerproducts" data-toggle="tab">
					<?php echo Text::_('COM_REDSHOPB_OFFER_PRODUCTS_IN_OFFER'); ?>
				</a>
			</li>
			<?php
		endif;
		?>
	</ul>
	<div class="tab-content">
		<div class="tab-pane active" id="details">
			<div class="redshopb-offer-form">
				<form action="<?php echo $action; ?>" method="post" name="adminForm" id="adminForm"
					  class="form-horizontal">
					<div class="container-fluid">
						<div class="row">
							<div class="form-group">
								<div class="control-label">
									<?php echo $this->form->getLabel('name'); ?>
								</div>
								<div class="controls">
									<?php echo $this->form->getInput('name'); ?>
								</div>
							</div>
							<div class="form-group">
								<div class="control-label">
									<?php echo $this->form->getLabel('expiration_date_switcher'); ?>
								</div>
								<div class="controls">
									<?php echo $this->form->getInput('expiration_date_switcher'); ?>
								</div>
							</div>
							<div class="form-group expirationInputDiv">
								<div class="control-label">
									<?php echo $this->form->getLabel('expiration_date'); ?>
								</div>
								<div class="controls">
									<?php echo $this->form->getInput('expiration_date'); ?>
								</div>
							</div>
							<div class="form-group">
								<div class="control-label">
									<?php echo $this->form->getLabel('customer_type'); ?>
								</div>
								<div class="controls">
									<?php echo $this->form->getInput('customer_type'); ?>
								</div>
							</div>
							<div class="form-group employee_id_class customer_id_class<?php
							echo ($this->form->getValue('customer_type') != 'employee') ? ' hide' : ''; ?>">
								<div class="control-label">
									<?php echo $this->form->getLabel('user_id'); ?>
								</div>
								<div class="controls">
									<?php echo $this->form->getInput('user_id'); ?>
								</div>
							</div>
							<div class="form-group department_id_class customer_id_class<?php
							echo ($this->form->getValue('customer_type') != 'department') ? ' hide' : ''; ?>">
								<div class="control-label">
									<?php echo $this->form->getLabel('department_id'); ?>
								</div>
								<div class="controls">
									<?php echo $this->form->getInput('department_id'); ?>
								</div>
							</div>
							<div class="form-group company_id_class customer_id_class<?php
							echo ($this->form->getValue('customer_type') != 'company') ? ' hide' : ''; ?>">
								<div class="control-label">
									<?php echo $this->form->getLabel('company_id'); ?>
								</div>
								<div class="controls">
									<?php echo $this->form->getInput('company_id'); ?>
								</div>
							</div>
							<div class="form-group">
								<div class="control-label">
									<?php echo $this->form->getLabel('collection_id'); ?>
								</div>
								<div class="controls">
									<?php echo $this->form->getInput('collection_id'); ?>
								</div>
							</div>
							<div class="form-group">
								<div class="control-label">
									<?php echo $this->form->getLabel('status'); ?>
								</div>
								<div class="controls">
									<?php echo $this->form->getInput('status'); ?>
								</div>
							</div>
							<div class="form-group">
								<div class="control-label">
									<?php echo $this->form->getLabel('comments'); ?>
								</div>
								<div class="controls">
									<?php echo $this->form->getInput('comments'); ?>
								</div>
							</div>
							<div class="mainPriceCalculations">
								<div class="form-group">
									<div class="control-label">
										<?php echo $this->form->getLabel('subtotal'); ?>
									</div>
									<div class="controls" id="globalSubtotal">
										<?php echo RedshopbHelperProduct::getProductFormattedPrice($this->form->getValue('subtotal'), $currency); ?>
									</div>
								</div>
								<div class="form-group">
									<div class="control-label">
										<?php echo $this->form->getLabel('discount_type'); ?>
									</div>
									<div class="controls">
										<?php echo $this->form->getInput('discount_type'); ?>
									</div>
								</div>
								<div class="form-group">
									<div class="control-label">
										<?php echo $this->form->getLabel('discount'); ?>
									</div>
									<div class="controls">
										<?php echo $this->form->getInput('discount'); ?>
									</div>
								</div>
								<div class="form-group">
									<div class="control-label">
										<?php echo $this->form->getLabel('total'); ?>
									</div>
									<div class="controls" id="globalTotal">
										<?php echo RedshopbHelperProduct::getProductFormattedPrice($this->form->getValue('total'), $currency); ?>
									</div>
								</div>
							</div>
						</div>
					</div>
					<input type="hidden" name="option" value="com_redshopb">
					<input type="hidden" name="id" value="<?php echo $this->item->id; ?>" id="offer_id">
					<?php echo $this->form->getInput('state'); ?>
					<input type="hidden" name="task" value="">
					<?php echo HTMLHelper::_('form.token'); ?>
				</form>
			</div>
		</div>
		<?php
		if ($this->item->id)
			:
			?>
			<div class="tab-pane" id="products">
				<div class="container-fluid">
					<div class="row">
						<div class="spinner pagination-centered spinner-products-content">
							<?php echo HTMLHelper::image('media/com_redshopb/images/ajax-loader.gif', '') ?>
						</div>
						<div class="products-content offerScriptInit"></div>
					</div>
				</div>
			</div>

			<div class="tab-pane" id="offerproducts">
				<div class="container-fluid">
					<div class="row">
						<div class="spinner pagination-centered spinner-offerproducts-content">
							<?php echo HTMLHelper::image('media/com_redshopb/images/ajax-loader.gif', '') ?>
						</div>
						<div class="offerproducts-content offerScriptInit"></div>
					</div>
				</div>
			</div>

			<?php
		endif;
		?>
	</div>
</div>
