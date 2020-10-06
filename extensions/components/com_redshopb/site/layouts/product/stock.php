<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Session\Session;

/**
 * Layout variables
 * ================================
 * @var  array   $displayData  Available data
 * @var  array   $stockrooms   Stockroom list
 * @var  int     $productId    Product Id
 * @var  object  $unitMeasure  Product Id
 */
extract($displayData);

$isLocked = RedshopbEntityProduct::getInstance($productId)->canReadOnly();

RedshopbHtml::loadFooTable();
?>
<script type="text/javascript">
	var loadedStockRoomTab = [];
</script>

<script type="text/javascript">
	function ajaxStockroomTabSetup(stockroomId) {
		(function($)
		{
			console.log("Setup tab");
			$('.redshopb-product-stock-navigation a[href="#stock_room_' + stockroomId + '"]')
				.on('show show.bs.tab', function (e)
				{
					ajaxStockroomExecute(stockroomId);
				});
		})(jQuery);
	}

	function ajaxStockroomExecute(stockroomId) {
		(function($)
		{
			// Tab already loaded
			if (loadedStockRoomTab[stockroomId] == true)
			{
				return true;
			}

			var stockroomTabs = $('#stockroomTabs');
			var spinner = $('.stockroom-' + stockroomId + '-content .spinner');

			var settings =
				{
					url: '<?php echo Uri::root(); ?>index.php?option=com_redshopb',
					type: 'POST',
					data: {
						'task': 'stockroom.ajaxstockroom',
						'<?php echo Session::getFormToken() ?>': 1,
						'stockroom_id': stockroomId,
						'product_id': <?php echo $productId ?>
					},
					beforeSend: function (xhr)
					{
						spinner.show();
						stockroomTabs.addClass('opacity-40');
					}
				};

			// Perform the ajax request
			$.ajax(settings)
				.done(function(data, textStatus, jqXHR)
				{
					loadedStockRoomTab[stockroomId] = true;
					$('.stockroom-' + stockroomId + '-content').html(data);

					$('select').chosen();
					$('.chzn-search').hide();
					$('.hasTooltip').tooltip({
						"animation": true,
						"html": true,
						"placement": "top",
						"selector": false,
						"title": "",
						"trigger": "hover focus",
						"delay": 0,
						"container": false
					});

					// init footable for the tab
					rsbftPhone = 480;
					rsbftTablet = 768;

					initFootableRedshopb();

					$('#flexslider_<?php echo $productId ?>_' + stockroomId).flexslider({
						slideshow : false,
						directionNav : false,
						animation : 'slide',
						animationLoop : false,
						start: function() {
							fooTableTable.trigger('footable_initialize');
						}
					});

					// Ajax update product item amount
					$('#stock_room_' + stockroomId)
						.on('change', '.ajaxUpdateAmount', function()
						{
							var $this = $(this);
							var elementId = $this.attr('id');
							var entries = elementId.split('_');
							var id = entries[2];

							var	 value = $this.val();
							var  unlimited = $this.attr('data-unlimited');
							var  $amount = $("#" + $this.attr("data-field"));

							var productItemSettings =
								{
									url: '<?php echo Uri::root(); ?>index.php?option=com_redshopb&task=stockroom.ajaxUpdateProductItemAmount',
									type: 'POST',
									data: {
										"<?php echo Session::getFormToken() ?>": 1,
										"id": stockroomId,
										"amount": value,
										"product_item": id,
										"unlimited": unlimited
									},
									beforeSend: function () {
										$amount.addClass('opacity-40');
									}
								};

							$.ajax(productItemSettings)
								.always(function(data, textStatus, jqXHR)
								{
									$amount.removeClass('opacity-40');
								});

							return true;

						});

					// Ajax make product item amount unlimited
					$('#stock_room_' + stockroomId)
						.on('click', '.ajaxUpdateAmountUnlimited', function(event)
						{
							event.preventDefault();

							var $this        = $(this);
							var $amount      = $("#stock_room_" + stockroomId + " #" + $this.attr('data-field'));
							var unlimited    = $amount.attr('data-unlimited');
							var newUnlimited = (unlimited == "0") ? "1" : "0";
							var entries      = $amount.attr('id').split('_');
							var id           = entries[2];

							var unlimitedSettings =
								{
									url: '<?php echo Uri::root(); ?>index.php?option=com_redshopb&task=stockroom.ajaxUpdateProductItemAmount',
									type: 'POST',
									data: {
										"<?php echo Session::getFormToken() ?>" : 1,
										"id" : stockroomId,
										"amount" : "",
										"product_item" : id,
										"unlimited" : newUnlimited
									},
									beforeSend: function ()
									{
										$amount.addClass('opacity-40');
									}
								};

							$.ajax(unlimitedSettings)
								.done(function(data, textStatus, jqXHR)
								{
									if (data != '1')
									{
										return false;
									}

									$amount.attr('data-unlimited', newUnlimited);

									if (unlimited == '0')
									{
										$amount
											.attr('type', 'text')
											.prop('disabled', true)
											.addClass('disabled')
											.val(Joomla.JText._('COM_REDSHOPB_STOCKROOM_UNLIMITED'));
									}
									else
									{
										$amount
											.attr('type', 'number')
											.prop('disabled', false)
											.removeClass('disabled')
											.val('<?php echo number_format(0, $unitMeasure->decimal_position);?>');
									}

									$this.toggleClass('btn-success');
								})
								.always(function(data, textStatus, jqXHR)
								{
									$amount.removeClass('opacity-40');
								});

							return true;
						});

					// Ajax update product amount
					$('#stock_room_' + stockroomId)
						.on('change', '.ajaxUpdateProductAmount', function()
						{
							var $this = $(this);
							var entries = $this.attr('id').split('_');
							var id = entries[2];
							var	value = $this.val();
							var	unlimited = $this.attr('data-unlimited');

							var productAmountSettings =
								{
									url: '<?php echo Uri::root(); ?>index.php?option=com_redshopb&task=stockroom.ajaxUpdateProductAmount',
									type: 'POST',
									data: {
										"<?php echo Session::getFormToken() ?>": 1,
										"id": stockroomId,
										"amount": value,
										"product": id,
										"unlimited": unlimited
									},
									beforeSend: function () {
										$this.addClass('opacity-40');
									}
								};

							$.ajax(productAmountSettings)
								.always(function(data, textStatus, jqXHR)
								{
									$this.removeClass('opacity-40');
								});

							return true;
						});

					// Ajax make product amount unlimited
					$('#stock_room_' + stockroomId)
						.on('click', '.ajaxUpdateProductAmountUnlimited', function(event)
						{
							event.preventDefault();
							var $this        = $(this);
							var $amount      = $("#stock_room_" + stockroomId + " #" + $this.attr('data-field'));
							var unlimited    = $amount.attr('data-unlimited');
							var newUnlimited = (unlimited == "0") ? "1" : "0";
							var entries      = $amount.attr('id').split('_');
							var id           = entries[2];


							var productUnlimitedSettings = {
								url: '<?php echo Uri::root(); ?>index.php?option=com_redshopb&task=stockroom.ajaxUpdateProductAmount',
								type: 'POST',
								data: {
									"<?php echo Session::getFormToken() ?>": 1,
									"id" : stockroomId,
									"amount" : "",
									"product" : id,
									"unlimited" : newUnlimited
								},
								beforeSend: function () {
									$amount.addClass('opacity-40');
								}
							};

							$.ajax(productUnlimitedSettings)
								.done(function (data)
								{
									if (data == "1")
									{
										$amount.attr("data-unlimited", newUnlimited);

										if (unlimited == "0")
										{
											$amount
												.attr("type", "text")
												.prop("disabled", true)
												.addClass("disabled")
												.val(Joomla.JText._('COM_REDSHOPB_STOCKROOM_UNLIMITED'));
										}
										else
										{
											$amount
												.attr("type", "number")
												.prop("disabled", false)
												.removeClass("disabled")
												.val('<?php echo number_format(0, $unitMeasure->decimal_position);?>');
										}

										$this.toggleClass('btn-success');
									}
								})
								.always(function(data, textStatus, jqXHR)
								{
									$amount.removeClass('opacity-40');
								});

							return true;
						});
				})
				.always(function(data, textStatus, jqXHR)
				{
					spinner.hide();
					stockroomTabs.removeClass('opacity-40');
				});

		})(jQuery);
	}
</script>

<script type="text/javascript">
	(function($){
		$(document).ready(function() {
			<?php if (!empty($stockrooms)): ?>
			<?php foreach ($stockrooms as $stockroom): ?>
			ajaxStockroomTabSetup(<?php echo $stockroom->id ?>);
			<?php endforeach; ?>
			<?php endif; ?>

			var lowerLevel = jQuery('#jform_stock_lower_level');

			jQuery('#js-stock-lower-level').val(lowerLevel.val()).on('change', function()
			{
				lowerLevel.val(jQuery(this).val()).trigger('change');
			});

			lowerLevel.on('change', function()
			{
				jQuery('#js-stock-lower-level').val(jQuery(this).val());
			});

			var upperLevel = jQuery('#jform_stock_upper_level');

			jQuery('#js-stock-upper-level').val(upperLevel.val()).on('change', function()
			{
				upperLevel.val(jQuery(this).val()).trigger('change');
			});

			upperLevel.on('change', function()
			{
				jQuery('#js-stock-upper-level').val(jQuery(this).val());
			});
		});
	})(jQuery);
</script>

<div class="redshopb-product-stock-wrapper">
	<div class="row">
		<div class="col-md-3">
			<div class="redshopb-product-stock-navigation">
				<ul class="nav nav-pills nav-stacked" id="stockroomTabs">
					<li class="active">
						<a data-toggle="tab" href="#stock_levels"><?php echo Text::_('COM_REDSHOPB_PRODUCT_STOCK_LEVELS') ?></a>
					</li>
					<?php if (!empty($stockrooms)): ?>
						<?php foreach ($stockrooms as $stockroom): ?>
							<li>
								<a data-toggle="tab" href="#stock_room_<?php echo $stockroom->id ?>"><?php echo $stockroom->name ?></a>
							</li>
						<?php endforeach; ?>
					<?php endif; ?>
				</ul>
			</div>
		</div>
		<div class="col-md-9">
			<div class="tab-content">
				<!-- Stock levels part -->
				<div class="tab-pane active" id="stock_levels">
					<div class="row">
						<div class="col-md-12">
							<div class="form-group">
								<div class="control-label">
									<label><?php echo Text::_('COM_REDSHOPB_PRODUCT_STOCK_LOWER_LEVEL_LABEL');?></label>
								</div>
								<div class="controls">
									<input type="number" id="js-stock-lower-level"
											<?php echo ($isLocked) ? 'disabled' : '' ?>
										   step="<?php echo $unitMeasure->step;?>">
								</div>
							</div>
							<div class="form-group">
								<div class="control-label">
									<label><?php echo Text::_('COM_REDSHOPB_PRODUCT_STOCK_UPPER_LEVEL_LABEL');?></label>
								</div>
								<div class="controls">
									<input type="number" id="js-stock-upper-level"
										<?php echo ($isLocked) ? 'disabled' : '' ?>
										   step="<?php echo $unitMeasure->step;?>">
								</div>
							</div>
						</div>
					</div>
				</div>

				<?php if (!empty($stockrooms)): ?>
					<!-- Stock room amount -->
					<?php foreach ($stockrooms as $stockroom): ?>
						<div class="tab-pane" id="stock_room_<?php echo $stockroom->id ?>">
							<div class="row">
								<div class="col-md-12 stockroom-<?php echo $stockroom->id ?>-content">
									<div class="spinner pagination-centered">
										<?php echo HTMLHelper::image('media/com_redshopb/images/ajax-loader.gif', '') ?>
									</div>
								</div>
							</div>
						</div>
					<?php endforeach; ?>
				<?php endif; ?>

			</div>
		</div>
	</div>
</div>
