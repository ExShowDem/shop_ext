<?php
/**
 * @package     Aesir.E-Commerce.Admin
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Session\Session;

$data                = $displayData;
$items               = $data['options']['items'];
$pagination          = $data['options']['pagination'];
$listDirn            = $data['options']['listDirn'];
$listOrder           = $data['options']['listOrder'];
$orderColumns        = $data['options']['columns'];
$reorder             = $data['options']['reorder'];
$showFilter          = (boolean) isset($data['options']['showFilter']) ? $data['options']['showFilter'] : false;
$showPaginationLinks = isset($data['options']['showPaginationLinks']) ? $data['options']['showPaginationLinks'] : true;

RedshopbHtml::loadFooTable();
?>
	<?php if ($showFilter): ?>
		<?php
		echo RedshopbLayoutHelper::render(
			'searchtools.default',
			array(
				'view' => $data['view'],
				'options' => array(
					'searchField'         => 'search_orders',
					'searchFieldSelector' => '#filter_search_orders, #filter_date_from, #filter_date_to',
					'limitFieldSelector'  => '#list_order_limit',
					'activeOrder'         => $listOrder,
					'activeDirection'     => $listDirn
				)
			)
		);
		?>
	<p></p>
	<?php endif; ?>

	<?php if (empty($items)) : ?>
		<?php echo RedshopbLayoutHelper::render('common.nodata'); ?>
	<?php else : ?>
		<table class="table table-striped table-hover footable js-redshopb-footable redshopb-footable toggle-circle-filled" id="orderList">
			<thead>
			<tr>
	<?php
	foreach ($orderColumns as $orderColumn) :
		switch ($orderColumn) :
			case 'grid':
	?>
				<th style="width:1%">
					<input type="checkbox" name="checkall-toggle" value=""
						   title="<?php echo Text::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)"/>
				</th>
	<?php
				break;
			case 'id':
	?>
				<th style="width:10%" class="nowrap">
					<?php
					if ($reorder)
						{
						echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_ORDER_ID_TITLE', 'orders.id', $listDirn, $listOrder);
					}
					else
						{
						echo Text::_('COM_REDSHOPB_ORDER_ID_TITLE');
					}
					?>
					</th>
	<?php
				break;
			case 'customer_type':
	?>
				<th class="nowrap" data-hide="phone, tablet"  >
					<?php
					if ($reorder)
						{
						echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_ORDER_CUSTOMER_TYPE', 'orders.customer_type', $listDirn, $listOrder);
					}
					else
						{
						echo Text::_('COM_REDSHOPB_ORDER_CUSTOMER_TYPE');
					}
					?>
					</th>
	<?php
				break;
			case 'customer_name':
	?>
			<th class="nowrap" data-toggle="true">
					<?php
					if ($reorder)
						{
						echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_ORDER_CUSTOMER_TITLE', 'orders.customer_name', $listDirn, $listOrder);
					}
					else
						{
						echo Text::_('COM_REDSHOPB_ORDER_CUSTOMER_TITLE');
					}
					?>
				</th>
	<?php
				break;
			case 'vendor_name':
	?>
			<th class="nowrap" data-hide="phone, tablet">
					<?php
					if ($reorder)
						{
						echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_ORDER_VENDOR_TITLE', 'orders.vendor_name', $listDirn, $listOrder);
					}
					else
						{
						echo Text::_('COM_REDSHOPB_ORDER_VENDOR_TITLE');
					}
					?>
				</th>
	<?php
				break;
			case 'status':
	?>
			<th class="nowrap">
					<?php
					if ($reorder)
						{
						echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_ORDER_STATUS', 'orders.status', $listDirn, $listOrder);
					}
					else
						{
						echo Text::_('COM_REDSHOPB_ORDER_STATUS');
					}
					?>
				</th>
	<?php
				break;
			case 'created_date':
	?>
			<th class="nowrap" data-hide="phone">
					<?php
					if ($reorder)
						{
						echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_ORDER_ORDER_DATE_TITLE', 'orders.created_date', $listDirn, $listOrder);
					}
					else
						{
						echo Text::_('COM_REDSHOPB_ORDER_ORDER_DATE_TITLE');
					}
					?>
				</th>
	<?php
				break;
			case 'author':
	?>
			<th class="nowrap" data-hide="phone">
					<?php
					if ($reorder)
						{
						echo HTMLHelper::_('rsearchtools.sort', 'JAUTHOR', 'orders.author', $listDirn, $listOrder);
					}
					else
						{
						echo Text::_('JAUTHOR');
					}
					?>
				</th>
	<?php
				break;
			case 'log_type':
	?>
			<th class="nowrap" data-hide="phone">
					<?php
					if ($reorder)
						{
						echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_ORDER_LOG_TYPE_TITLE', 'log_type', $listDirn, $listOrder);
					}
					else
						{
						echo Text::_('COM_REDSHOPB_ORDER_LOG_TYPE_TITLE');
					}
					?>
				</th>
	<?php
				break;
			case 'actions':
	?>
			<th class="nowrap" data-hide="phone">
				<?php echo Text::_('COM_REDSHOPB_ACTIONS'); ?>
			</th>
	<?php
				break;
			case 'products':
	?>
			<th class="nowrap" data-hide="phone">
					<?php
					if ($reorder)
						{
						echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_ORDER_PRODUCTS', 'orders.products', $listDirn, $listOrder);
					}
					else
						{
						echo Text::_('COM_REDSHOPB_ORDER_PRODUCTS');
					}
					?>
				</th>
	<?php
				break;
			case 'total':
	?>
			<th class="nowrap" data-hide="phone">
					<?php
					if ($reorder)
						{
						echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_ORDER_TOTAL', 'orders.total', $listDirn, $listOrder);
					}
					else
						{
						echo Text::_('COM_REDSHOPB_ORDER_TOTAL');
					}
					?>
				</th>
	<?php
				break;
		endswitch;
	endforeach;
	?>
				<?php
				$html = null;

				Factory::getApplication()->triggerEvent('onAESECViewOrdersExtendTableHead', array(&$html));

				echo $html;
				?>
			</tr>
			</thead>
			<?php if ($items): ?>
				<tbody>
				<?php
				$canChange  = RedshopbHelperACL::getPermission('manage', 'order', array('edit.state'), true);
				$canEditOwn = RedshopbHelperACL::getPermission('manage', 'order', array('edit', 'edit.own'), true);
				$canEdit    = $canEditOwn;
				$canCheckin = $canEditOwn;

				foreach ($items as $i => $item): ?>
					<?php
					$order     = ($item instanceof RedshopbEntityOrder) ? $item : RedshopbEntityOrder::getInstance($item->id)->bind($item);
					$sentOrder = null;

					if (!is_null($item->log_type) && !empty($item->log_type))
					{
						$logType = strtoupper($item->log_type);

						if ($logType == 'EXPEDITE')
						{
							$sentOrder       = RedshopbHelperOrder::getExpeditedOrder($item->id);
							$sentOrderString = str_pad($sentOrder, 6, '0', STR_PAD_LEFT);
						}
					}
					else
					{
						$logType = 'NONE';
					}

					$isParent = '';

					if (in_array($logType, array('EXPEDITE', 'COLLECT')))
					{
						$isParent = 'js-redshopb-parent';
					}

					switch ($item->status)
					{
						case 0:
							$trClass = 'warning';
							break;
						case 1:
						case 4:
						case 5:
							$trClass = 'success';
							break;
						case 2:
						case 3:
							$trClass = 'error';
							break;
						case 6:
						case 7:
							$trClass = 'info';
							break;
						default:
							$trClass = '';
					}
					?>
					<tr class="<?php echo $trClass . ' ' . $isParent; ?>" data-id="<?php echo $item->id; ?>">

			<?php
			foreach ($orderColumns as $orderColumn) :
				switch ($orderColumn) :
					case 'grid':
			?>
						<td>
							<?php echo HTMLHelper::_('grid.id', $i, $item->id); ?>
						</td>
			<?php
						break;
					case 'id':
			?>
						<td>
							<?php if ($item->checked_out) : ?>
									<?php echo HTMLHelper::_('rgrid.checkedout', $i, $item->checked_out > 0 ? Factory::getUser($item->checked_out)->get('name') : '',
										$item->checked_out_time, 'orders.', $canCheckin
									); ?>
							<?php endif; ?>

								<?php if ($canEdit) : ?>
									<a href="<?php echo RedshopbRoute::_('index.php?option=com_redshopb&task=order.edit&id=' . $item->id); ?>">
								<?php endif; ?>
										<?php echo str_pad($item->id, 6, '0', STR_PAD_LEFT); ?>

										<?php if ($canEdit) : ?>
									</a>
										<?php endif; ?>

								<?php if ($isParent) : ?>
									<button class="btn btn-mini js-redshop-children"><i class="icon-chevron-down"></i></button>
								<?php endif; ?>
							</td>
			<?php
						break;
					case 'customer_type':
			?>
						<td>
							<?php echo Text::_('COM_REDSHOPB_' . $this->escape($item->customer_type)); ?>
						</td>
			<?php
						break;
					case 'customer_name':
			?>
						<td>
							<?php echo Text::_('COM_REDSHOPB_COMPANY') . ': ' . $this->escape($item->company_name); ?>

							<?php if (isset($item->department_name)) : ?>
									<br /><?php echo Text::_('COM_REDSHOPB_DEPARTMENT') . ': ' . $this->escape($item->department_name);  ?>
							<?php endif; ?>

							<?php if (isset($item->employee_name)) : ?>
									<br /><?php echo Text::_('COM_REDSHOPB_EMPLOYEE') . ': ' . $this->escape($item->employee_name);  ?>
							<?php endif; ?>
						</td>
			<?php
						break;
					case 'vendor_name':
			?>
						<td>
							<?php echo $this->escape($item->vendor_name); ?>
						</td>
			<?php
						break;
					case 'status':
			?>
						<td>
							<?php echo $order->renderStatusLabel(); ?>
						</td>
			<?php
						break;
					case 'created_date':
			?>
						<td>
							<?php echo HTMLHelper::_('date', $item->created_date, Text::_('DATE_FORMAT_LC4')) ?>
						</td>
			<?php
						break;
					case 'author':
			?>
						<td>
							<?php echo $this->escape($item->author); ?>
						</td>
			<?php
						break;
					case 'log_type':
			?>
						<td>
							<?php echo Text::_('COM_REDSHOPB_ORDER_LOG_TYPE_' . $logType); ?>&nbsp;

							<?php if (!is_null($sentOrder)) : ?>
									<a href="<?php echo RedshopbRoute::_('index.php?option=com_redshopb&task=order.edit&id=' . $sentOrder); ?>">
										<?php echo $sentOrderString;?>
									</a>
							<?php endif;?>
						</td>
			<?php
						break;
					case 'actions':
			?>
						<td>
							<div class="btn-group">
								<button type="button" class="btn btn-small btn-default" name="infoButton" title="<?php echo Text::_('COM_REDSHOPB_INFO'); ?>" data-order="<?php echo $item->id ?>">
									<i class="icon-info"></i>
								</button>
								<a class="btn btn-small btn-default"
								   href="<?php echo RedshopbRoute::_('index.php?option=com_redshopb&task=order.printPDF&id=' . $item->id) ?>"
								   target="_blank" title="<?php echo Text::_('COM_REDSHOPB_PRINT'); ?>">
									<i class="icon-print"></i>
								</a>
							</div>
						</td>
			<?php
						break;
					case 'products':
			?>
						<td>
							<?php echo $this->escape($item->products); ?>
						</td>
			<?php
						break;
					case 'total':
			?>
						<td>
							<?php echo $this->escape($item->total); ?>
						</td>
			<?php
						break;
				endswitch;
			endforeach;
			?>
						<?php
						$html = null;

						Factory::getApplication()->triggerEvent('onAESECViewOrdersExtendTableBody', array(&$html, $item));

						echo $html;
						?>
					</tr>
				<?php endforeach; ?>
				</tbody>
			<?php endif; ?>
		</table>
		<?php if ($showPaginationLinks): ?>
				<div><?php echo $pagination->getPaginationLinks(null, array('showLimitBox' => false));?></div>
		<?php endif; ?>
	<?php endif; ?>

	<div id="orderModal" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
			<h3 id="myModalLabel"><?php echo Text::_('COM_REDSHOPB_ORDERS_ENTER_DATA'); ?></h3>
		</div>
		<div class="modal-body" style="overflow-y: visible !important;">
			<div class="container-fluid">
				<div class="span6">
					<div id="ajaxModal"></div>
				</div>
				<div class="span6" id="delivery">
					<p><b><?php echo Text::_('COM_REDSHOPB_SHOP_DELIVERY_ADDRESS_INFO', true); ?></b></p>
					<p id="delivery-name"></p>
					<p id="delivery-address"></p>
					<p id="delivery-address2"></p>
					<p id="delivery-location"></p>
					<p id="delivery-country"></p>
					<div class="spinner" style="display:none;">
						<?php echo HTMLHelper::image('media/com_redshopb/images/ajax-loader.gif', '') ?>
					</div>
				</div>
			</div>
		</div>
		<div class="modal-footer">
			<button class="btn btn-success" data-dismiss="modal" aria-hidden="true" id="modalNext"><?php echo Text::_('JNEXT')?></button>
		</div>
	</div>

	<div>
		<input type="hidden" name="task" value="">
		<input type="hidden" name="boxchecked" value="0">
		<?php echo HTMLHelper::_('form.token'); ?>
	</div>

<div id="item-details" style="clear: both;">
</div>
<script type="text/javascript">
	function JUpdateDelivery(delivery)
	{
		var id = delivery.val();
		var location = '';

		if (jQuery.isNumeric(id) && id > 0)
		{
			jQuery.ajax({
				url: '<?php echo Uri::root(); ?>index.php?option=com_redshopb&task=shop.ajaxGetDeliveryAddress',
				data: {
					'address_id'                              : id,
					'<?php echo Session::getFormToken(); ?>' : 1
				},
				type: 'POST',
				dataType: 'json',
				beforeSend: function (xhr) {
					jQuery('#delivery').addClass('opacity-40');
					jQuery('#delivery .spinner').show();
					jQuery('#delivery-name').html('');
					jQuery('#delivery-address').html('');
					jQuery('#delivery-address2').html('');
					jQuery('#delivery-location').html('');
					jQuery('#delivery-country').html('');
				}
			}).done(function (data) {
				jQuery('#delivery .spinner').hide();
				jQuery('#delivery').removeClass('opacity-40');

				if (data.name != undefined && data.name.length > 0)
				{
					jQuery('#delivery-name').html(data.name);
				}
				if (data.address != undefined && data.address.length > 0)
				{
					jQuery('#delivery-address').html(data.address);
				}
				if (data.address2 != undefined && data.address2.length > 0)
				{
					jQuery('#delivery-address2').html(data.address2);
				}
				if (data.zip != undefined && data.zip.length > 0)
				{
					location += data.zip;
				}
				if (data.city != undefined && data.city.length > 0)
				{
					if (location.length > 0)
					{
						location += ', ' + data.city;
					}
					else
					{
						location += data.city;
					}
				}
				if (location.length > 0)
				{
					jQuery('#delivery-location').html(location);
				}
				if (data.country != undefined && data.country.length > 0)
				{
					jQuery('#delivery-country').html(data.country);
				}
			});
		}
		else
		{
			jQuery('#delivery-address').html('');
			jQuery('#delivery-address2').html('');
			jQuery('#delivery-location').html('');
			jQuery('#delivery-country').html('');

			if (jQuery.isNumeric(id) && id == -1)
			{
				jQuery('#delivery-name').html('<?php echo Text::_('COM_REDSHOPB_ORDERS_ORIGINAL_ADDRESS_EACH');?>');
			}
			else
			{
				jQuery('#delivery-name').html('<?php echo Text::_('COM_REDSHOPB_SHOP_DELIVERY_NOT_SET');?>');
			}
		}
	}

	jQuery(document).ready(function () {
		var fooTableMonitorBreakpoints = true;

		initFootableRedshopb();
		var parents = [];
		jQuery('#orderList').on('click','.js-redshop-children', function(e) {
			e.preventDefault();
			var self = this, id = jQuery(this).closest('tr').data('id');

			if(jQuery.inArray(id, parents) == -1) {
				parents.push(id);

				jQuery.ajax({
					url: '<?php echo Uri::root(); ?>index.php?option=com_redshopb&task=orders.ajaxGetChildrenOrders',
					type: 'POST',
					data: {
						'parentId': id,
						"<?php echo Session::getFormToken() ?>": 1
					},
					dataType: 'html',
					beforeSend: function() {
						jQuery(self).closest('table').addClass('redshopb-loading');
					},
					success: function (data) {
						jQuery(self).find('i').toggleClass('icon-chevron-up').toggleClass('icon-chevron-down');
						jQuery(self).closest('table').removeClass('redshopb-loading');
						jQuery(self).closest('tr').after(data);
					},
					error: function() {
						jQuery(self).closest('table').removeClass('redshopb-loading');
					}
				});
			} else {
				jQuery(self).find('i').toggleClass('icon-chevron-up').toggleClass('icon-chevron-down');
				var children = jQuery('.js-redshopb-child[data-parent=' + id + ']');

				// check the children for nested childs
				children.each(function(key, child) {
					if(jQuery(child).hasClass('js-redshopb-parent')) {
					   jQuery(child).find('i').removeClass('icon-chevron-up').addClass('icon-chevron-down');
					   var nested =  jQuery('.js-redshopb-child[data-parent=' + jQuery(child).data('id') + ']');
					   nested.hide();
					}
				});
				children.toggle();
			}
		});

		jQuery('#orderList').on('click', "button[name='infoButton']", function(e) {
			e.preventDefault();
			loadOrderDetails(jQuery(this).attr("data-order"));
		});

		var originalSubmit = Joomla.submitbutton;
		Joomla.submitbutton = function (task) {
			cid = jQuery("input[name^='cid']");
			ids = [];
			cid.each( function() {
				if (this.checked) {
					ids.push(jQuery(this).val());
				}
			});

			if (task == 'orders.expedite' || task == 'orders.collect')
			{
			   jQuery.ajax({
					url:'<?php echo Uri::root(); ?>index.php?option=com_redshopb&task=orders.ajaxCheckActionPermissions',
					type:'POST',
					data: {
						'orderIds' : ids,
						'action': task,
						"<?php echo Session::getFormToken() ?>": 1
					},
					dataType:'json',
					success : function(data)
					{
						var mShow = data.mShow;
						var error = data.msg;
						var grant = data.grant;
						window.orderTask = task;

						if (grant == 1)
						{
							if (mShow == 1)
							{
								jQuery.ajax({
									url:'<?php echo Uri::root(); ?>index.php?option=com_redshopb&task=orders.ajaxGetOrderModal',
									type:'POST',
									data: {
										'action' : task,
										'orderIds' : ids,
										"<?php echo Session::getFormToken() ?>": 1
									},
									dataType:'html',
									success : function(data)
									{
										jQuery('#ajaxModal').html(data);
										jQuery('#orderModal').modal('show');
										var delivery = jQuery('#delivery_address_id');
										delivery.chosen();
										JUpdateDelivery(delivery);
									}
								});

								jQuery('#modalNext').on('click', function(){
									if (window.orderTask != undefined)
									{
										originalSubmit(window.orderTask);
									}
								});
							}
							else
							{
								originalSubmit(task);
							}
						}
						else
						{
							if(error.length > 0)
							{
								var msg = '<div class=\"alert alert-warning\"><a class=\"close\" data-dismiss=\"alert\">×</a><h4 class=\"alert-heading\"><?php echo Text::_('WARNING')?></h4><div><p>' + error + '</p></div></div>';

								var messageContainer = jQuery('#system-message-container');
								messageContainer.html(msg);
								jQuery('html, body').animate({
									scrollTop: messageContainer.offset().top
								}, 1000);
							}

							return false;
						}
					}
				});
			}
			else if (task == 'orders.rawoutput')
			{
				jQuery.ajax({
					url:'<?php echo Uri::root(); ?>index.php?option=com_redshopb&task=orders.rawoutput',
					type:'POST',
					data: {'cid' : ids},
					dataType:'html',
					success:function(data)
					{
						jQuery('#rawContent').html(data);
					}
				});

				jQuery('#rawModal').modal('show');
			}
			else
			{
				originalSubmit(task);
			}
		}
	});

	function loadOrderDetails(orderId) {
		// Hide all boxes
		jQuery('#item-details').children("div").hide();

		if( !jQuery('#item-details-' + orderId ).length )
		{
			jQuery('#item-details').append(jQuery('<div id="item-details-' + orderId + '" style="clear: both;"><div class="spinner pagination-centered"><?php echo HTMLHelper::image("media/com_redshopb/images/ajax-loader.gif", "") ?></div></div>'));
		}

		jQuery('#item-details-' + orderId).show();

		var reqData = {'orderid': orderId, "<?php echo Session::getFormToken() ?>": 1};

		jQuery.ajax({
			url:'<?php echo Uri::root(); ?>index.php?option=com_redshopb&task=orders.ajaxOrderItems',
			type:'POST',
			data: reqData,
			dataType:'html',
			beforeSend: function (xhr) {
				jQuery('#item-details-' + orderId + ' > .spinner').show();
				jQuery('#item-details-' + orderId ).addClass('opacity-40');
			},
			success:function(data)
			{
				if (data) {
					jQuery('#item-details-' + orderId).empty();
					jQuery('#item-details-' + orderId).html(data);
					jQuery('#item-details-' + orderId).removeClass('opacity-40');
					jQuery('#item-details-' + orderId + ' > .spinner').hide();
					jQuery('#item-details-' + orderId).prop('disabled', false).trigger("liszt:updated");
					jQuery('select').chosen();
					jQuery('.hasTooltip').tooltip({"animation": true, "html": true, "placement": "top",
						"selector": false, "title": "", "trigger": "hover focus", "delay": 0, "container": false});
					initFootableRedshopb();

					jQuery('html, body').animate({
						scrollTop: jQuery('#item-details-' + orderId).offset().top
					}, 1000);
				}

				return;
			}
		});

	}
</script>
<div class="modal hide fade" id="rawModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal">x</button>
		<h3 id="myModalLabel">Expedition raw output</h3>
	</div>
	<div class="modal-body" id="rawContent">
	</div>
	<div class="modal-footer">
		<button class="btn" type="button" data-dismiss="modal">
			<?php echo Text::_('JCANCEL'); ?>
		</button>
	</div>
</div>
