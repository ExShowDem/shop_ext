<?php
/**
 * @package     Aesir.E-Commerce.Plugin
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Plugin\PluginHelper;

?>

<?php if (!PluginHelper::isEnabled('vanir', 'group_delivery_time')): ?>
	<div class="alert alert-danger"><?php echo Text::_('PLG_VANIR_GROUP_DELIVERY_TIME_ERROR_PLUGIN_DISABLED') ?></div>
<?php else: ?>
	<?php
	$delivery = RedshopbApp::getConfig()->get('stockroom_delivery_time', 'hour');
	$delivery = Text::_('COM_REDSHOPB_STOCKROOM_DELIVERY_TIME_' . strtoupper($delivery));

	/**
	 * Layout variables
	 * ======================
	 * @var  array    $displayData  List of available data.
	 * @var  string   $id           DOM ID
	 * @var  string   $name         DOM Name
	 * @var  array    $options      List of exists data
	 * @var  boolean  $required     Required status
	 * @var  array    $value        Values
	 */
	extract($displayData);
	?>
	<script type="text/javascript">
		(function($){
			$(document).ready(function(){
				// Add group delivery
				$("#vanir-group-delivery-btn-add").click(function(event){
					event.preventDefault();

					// Reset form
					$("#vanir-group-delivery-add-item-wrapper input").each(function(index){
						$(this).val("");
					});

					$(this).hide('normal', function(){
						$("#vanir-group-delivery-add-item-wrapper").show();
					});
				});

				// Group delivery form cancel
				$("#vanir-group-delivery-cancel").click(function(event){
					event.preventDefault();

					// Reset form
					$("#vanir-group-delivery-add-item-wrapper input").each(function(index){
						$(this).val("");
					});

					$("#vanir-group-delivery-add-item-wrapper").hide('normal', function(){
						$("#vanir-group-delivery-btn-add").show();
					});
				});

				// Group delivery form submit
				$("#vanir-group-delivery-submit").click(function(event){
					event.preventDefault();

					var $fieldColor = $("#vanir-group-delivery-add-item-wrapper #field-color").val();
					var $fieldMin   = $("#vanir-group-delivery-add-item-wrapper #field-min").val();
					var $fieldMax   = $("#vanir-group-delivery-add-item-wrapper #field-max").val();
					var $fieldLabel = $("#vanir-group-delivery-add-item-wrapper #field-label").val();
					var $fieldId    = $("#vanir-group-delivery-add-item-wrapper #field-id").val();

					$.post(
						"index.php?option=com_ajax&plugin=VanirAddGroupDelivery&group=vanir&format=raw",
						{
							"color" : $fieldColor,
							"min"   : $fieldMin,
							"max"   : $fieldMax,
							"label" : $fieldLabel,
							"id"    : $fieldId,
							"<?php echo Session::getFormToken() ?>": 1
						},
						function (response) {
							if ($fieldId) {
								$("#vanir-group-delivery-table tbody tr#" + $fieldId).remove();
							}else{
								$fieldId = parseInt(response);
							}

							var $col = $("<tr id=\""+$fieldId+"\">");

							$col
								.append($("<td>").css("text-align", "center").text(response))
								.append(
									$("<td>").append(
										$("<span>").css({
											"background-color" : $fieldColor,
											"width"            : "15px",
											"height"           : "15px",
											"border-radius"    : "8px",
											"display"          : "block"
										})
									)
								)
								.append($("<td>").html($fieldMin + " - " + $fieldMax + " <?php echo $delivery ?>"))
								.append($("<td>").html($fieldLabel))
								.append(
									$("<td>").append(
										$("<div>").addClass("btn-group")
											.append(
												$("<button>").addClass("btn text-center btn-small")
													.append(
														$("<i>").addClass("icon icon-edit")
													)
													.click(function(event){
														vanirEditGroupDelivery(response, event);
													})
											)
											.append(
												$("<button>").addClass("btn btn-danger text-center btn-small")
													.append(
														$("<i>").addClass("icon icon-remove")
													)
													.click(function(event){
														vanirRemoveGroupDelivery(response, event);
													})
											)
									)
								);

							$("#vanir-group-delivery-table tbody").append($col);

							// Reset form
							$("#vanir-group-delivery-add-item-wrapper input").each(function(index){
								$(this).val("");
							});

							$("#vanir-group-delivery-add-item-wrapper").hide('normal', function(){
								$("#vanir-group-delivery-btn-add").show();
							});
						}
					)
						.fail(function (response) {
							alert(response.responseText);
						});
				});
			});
		})(jQuery);
	</script>

	<script type="text/javascript">
		/**
		 * Method for remove group delivery
		 * @param id
		 * @param event
		 */
		function vanirRemoveGroupDelivery(id, event) {
			(function($){
				event.preventDefault();

				$.post(
					"index.php?option=com_ajax&plugin=VANIRDeleteGroup&group=vanir&format=raw",
					{
						"id" : id,
						"<?php echo Session::getFormToken() ?>": 1
					},
					function (response) {
						$("#vanir-group-delivery-table tbody tr#" + id).remove();
					}
				)
					.fail(function (response) {
						alert(response.responseText);
					});
			})(jQuery);
		}

		/**
		 * Method for edit group delivery
		 * @param id
		 * @param event
		 */
		function vanirEditGroupDelivery(id, event) {
			(function($){
				event.preventDefault();

				// Reset form
				$("#vanir-group-delivery-add-item-wrapper input").each(function(index){
					$(this).val("");
				});

				var $fieldColor = $("#vanir-group-delivery-add-item-wrapper #field-color");
				var $fieldMin = $("#vanir-group-delivery-add-item-wrapper #field-min");
				var $fieldMax = $("#vanir-group-delivery-add-item-wrapper #field-max");
				var $fieldLabel = $("#vanir-group-delivery-add-item-wrapper #field-label");
				var $fieldId = $("#vanir-group-delivery-add-item-wrapper #field-id");

				$.post(
					"index.php?option=com_ajax&plugin=VanirLoadGroup&group=vanir&format=json",
					{
						"id" : id,
						"<?php echo Session::getFormToken() ?>": 1
					},
					function (response) {
						response = $.parseJSON(response);

						$fieldColor.val(response.color);
						$fieldMin.val(response.min_time);
						$fieldMax.val(response.max_time);
						$fieldLabel.val(response.label);
						$fieldId.val(response.id);

						$("#vanir-group-delivery-btn-add").hide('normal', function(){
							$("#vanir-group-delivery-add-item-wrapper").show();
						});
					}
				)
					.fail(function (response) {
						alert(response.responseText);
					});
			})(jQuery);
		}
	</script>

	<table class="table table-striped" width="100%" id="vanir-group-delivery-table">
		<thead>
			<tr>
			<th width="1"><?php echo Text::_('PLG_VANIR_GROUP_DELIVERY_TIME_ID') ?></th>
			<th width="1"><?php echo Text::_('PLG_VANIR_GROUP_DELIVERY_TIME_COLOR') ?></th>
			<th width="20%"><?php echo Text::_('PLG_VANIR_GROUP_DELIVERY_TIME_DELIVERY_TIME') ?></th>
			<th><?php echo Text::_('PLG_VANIR_GROUP_DELIVERY_TIME_LABEL') ?></th>
			<th width="1" nowrap="mowrap"></th>
		</tr>
		</thead>
		<tbody>
			<tr class="well">
			<td colspan="5">
				<button class="btn btn-success text-center" id="vanir-group-delivery-btn-add"><i class="icon icon-plus"></i></button>
				<div style="display: none;" id="vanir-group-delivery-add-item-wrapper">
					<hr />
					<div class="control-group">
						<div class="control-label">
							<label><?php echo Text::_('PLG_VANIR_GROUP_DELIVERY_TIME_COLOR') ?></label>
						</div>
						<div class="controls">
							<input type="text" class="input" id="field-color" value="" maxlength="11" placeholder="#ffffff" />
						</div>
					</div>
					<div class="control-group">
						<div class="control-label">
							<label><?php echo Text::_('PLG_VANIR_GROUP_DELIVERY_TIME_DELIVERY_MIN_TIME') ?></label>
						</div>
						<div class="controls">
							<input type="text" class="input" id="field-min" value="" maxlength="11" />
						</div>
					</div>
					<div class="control-group">
						<div class="control-label">
							<label><?php echo Text::_('PLG_VANIR_GROUP_DELIVERY_TIME_DELIVERY_MAX_TIME') ?></label>
						</div>
						<div class="controls">
							<input type="text" class="input" id="field-max" value="" maxlength="11" />
						</div>
					</div>
					<div class="control-group">
						<div class="control-label">
							<label><?php echo Text::_('PLG_VANIR_GROUP_DELIVERY_TIME_LABEL') ?></label>
						</div>
						<div class="controls">
							<input type="text" class="input" id="field-label" value="" maxlength="255" />
						</div>
					</div>
					<div class="control-group">
						<button class="btn btn-primary" id="vanir-group-delivery-submit"><?php echo Text::_('JSAVE') ?></button>
						<button class="btn" id="vanir-group-delivery-cancel"><?php echo Text::_('JCANCEL') ?></button>
					</div>
					<input type="hidden" id="field-id" value="" maxlength="11" />
					<hr />
				</div>
			</td>
		</tr>
			<?php if (!empty($options)): ?>
				<?php foreach ($options as $option): ?>
					<tr id="<?php echo $option->id ?>">
						<td style="text-align: center"><?php echo $option->id ?></td>
						<td>
							<div style="background-color: <?php echo $option->color ?>; width: 15px; height: 15px; border-radius: 8px; display: block;"></div>
						</td>
						<td><?php echo $option->min_time ?> - <?php echo $option->max_time ?> <?php echo $delivery ?></td>
						<td><?php echo $option->label ?></td>
						<td>
							<div class="btn-group">
								<button class="btn text-center btn-small"
										onClick="javascript:vanirEditGroupDelivery(<?php echo $option->id ?>, event);">
									<i class="icon icon-edit"></i>
								</button>
								<button class="btn btn-danger text-center btn-small"
										onClick="javascript:vanirRemoveGroupDelivery(<?php echo $option->id ?>, event);">
									<i class="icon icon-remove"></i>
								</button>
							</div>
						</td>
					</tr>
				<?php endforeach; ?>
			<?php endif; ?>
		</tbody>
	</table>
<?php endif;
