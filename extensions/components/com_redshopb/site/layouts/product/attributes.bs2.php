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
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Component\ComponentHelper;

$data = $displayData;

$state      = $data['state'];
$items      = $data['items'];
$pagination = $data['pagination'];
$formName   = $data['formName'];
$url        = isset($displayData['action']) ? $displayData['action'] : 'index.php?option=com_redshopb&view=product_attributes';

$productId = $displayData['productId'];
$url      .= '&product_id=' . (int) $productId;

$canReadOnly = RedshopbEntityProduct::getInstance($productId)->canReadOnly();
$showToolbar = isset($data['showToolbar']) && !$canReadOnly ? $data['showToolbar'] : false;

$return = isset($displayData['return']) ? $displayData['return'] : false;

if ($return)
{
	$url .= '&return=' . $return;
}

$action = RedshopbRoute::_($url);

$user                 = Factory::getUser();
$listOrder            = $state->get('list.ordering');
$listDirn             = $state->get('list.direction');
$saveOrder            = $listOrder == 'a.ordering';
$saveOrderingUrl      = 'index.php?option=com_redshopb&task=product_attributes.saveOrderAjax&tmpl=component';
$saveOrderingValueUrl = 'index.php?option=com_redshopb&task=product_attribute_values.saveOrderValueAjax&tmpl=component';
$config               = ComponentHelper::getParams('com_redshopb');
$width                = $config->get('thumbnail_width', 144);
$height               = $config->get('thumbnail_height', 144);

if (!$canReadOnly) : ?>
<script type="text/javascript">
	function deleteAttributeTypeId(i) {
		document.getElementById('cb' + i).checked = true;
		var form = document.getElementById('<?php echo $formName; ?>');
		Joomla.submitform('product_attributes.delete', form);
	}

	function deleteAttributeValueId(i) {
		document.getElementById('vb' + i).checked = true;
		var form = document.getElementById('<?php echo $formName; ?>');
		Joomla.submitform('product_attribute_values.delete', form);
	}

	(function ($) {
		$(document).ready(function () {
			var sortableList = new $.JSortableList('table.productAttributeList tbody', '<?php echo $formName; ?>', '<?php echo $listDirn; ?>',
				'<?php echo $saveOrderingUrl ?>', {nested: false,group: 'nested'}, 'nested');

			$('ul.attribute-values').sortable({
				nested: false,
				group: 'nestedchild',
				vertical: false,
				sortableHandle:'.sortable-handler',
				stop:function (e, ui) {
					$(ui.item).css({opacity:0});
					$(ui.item).animate({
						opacity:1
					}, 800, function (){
						$(ui.item).css('opacity','');
					});

					$('[name="vid[]"]', $(ui.item).parents('ul')).attr('checked', true);

					//serialize form then post to callback url
					var formData = $('#<?php echo $formName; ?>').serialize();
					formData = formData.replace('task', '');
					$.get('<?php echo $saveOrderingValueUrl ?>', formData);

					//remove cloned checkboxes
					$('[name="vid[]"]', $(ui.item).parents('ul')).attr('checked', false);
				}
			});
		});
	})(jQuery);

</script>
<?php endif; ?>
<div class="row-fluid">
	<form action="<?php echo $action; ?>" name="<?php echo $formName; ?>" class="adminForm"
		  id="<?php echo $formName; ?>" method="post">
		<?php
		if ($showToolbar) : ?>
			<?php echo $this->sublayout('toolbar', $data); ?>
		<?php endif;?>
		<hr/>
		<?php if (empty($items)) : ?>
			<div class="alert alert-info">
				<div class="pagination-centered">
					<h3><?php echo Text::_('COM_REDSHOPB_PRODUCT_ATTRIBUTE_REQUIRE_SET_FROM_PRODUCT_MORE_ONE_TYPE') ?></h3>
				</div>
			</div>
		<?php else : ?>
			<table class="table table-striped table-hover productAttributeList footable js-redshopb-footable redshopb-footable toggle-circle-filled">
				<thead>
				<tr>
					<th width="1%" class="nowrap center"></th>
					<th width="15%" class="nowrap" data-toggle="true"></th>
					<th class="nowrap" data-hide="phone"></th>
					<th width="10%"></th>
				</tr>
				</thead>
				<?php if ($items) : ?>
					<tbody class="nested">
					<?php foreach ($items as $i => $item) : ?>
						<?php
							$canChange             = !$canReadOnly;
							$canEdit               = 1;
							$canCheckin            = 1;
							$enableSkuValueDisplay = (bool) $item->enable_sku_value_display;
						?>
						<tr>
							<td class="order nowrap center">
								<div style="display: none"><?php echo HTMLHelper::_('rgrid.id', $i, $item->id, false, 'cid', $formName, 'attr'); ?></div>
								<?php
								if ($canChange) : ?>
									<?php $disableClassName = ''; ?>
									<?php $disabledLabel    = ''; ?>
									<?php
									if (!$saveOrder) : ?>
										<?php $disabledLabel    = Text::_('JORDERINGDISABLED'); ?>
										<?php $disableClassName = 'inactive tip-top'; ?>
									<?php endif;?>
									<span class="sortable-handler hasTooltip <?php echo $disableClassName; ?>"
										  title="<?php echo $disabledLabel; ?>">
									<i class="icon-move"></i>
								</span>
									<input type="text" style="display:none" name="order[]" size="5" value="<?php echo $item->ordering; ?>" class="width-20 text-area-order "/>
								<?php else : ?>
									<span class="sortable-handler inactive"><i class="icon-move"></i></span>
								<?php endif; ?>
							</td>
							<td>
								<?php if ($item->checked_out) : ?>
									<?php echo HTMLHelper::_(
										'rgrid.checkedout',
										$i,
										$item->checked_out > 0 ? Factory::getUser($item->checked_out)->get('name') : '',
										$item->checked_out_time,
										'product_attributes.',
										$canCheckin,
										'attr',
										$formName
									); ?>
								<?php endif; ?>
								<div class="img-center img-list">
									<?php if (isset($item->image) && !empty($item->image)) : ?>
										<?php $increment  = RedshopbHelperMedia::getIncrementFromFilename($item->image); ?>
										<?php $folderName = RedshopbHelperMedia::getFolderName($increment); ?>
										<?php $image      = Uri::root() . 'media/com_redshopb/images/originals/product_attribute/' . $folderName . '/' . $item->image; ?>
										<img src="<?php echo $image; ?>">
									<?php else : ?>
										<?php echo RedshopbHelperMedia::drawDefaultImg($width, $height, Text::_('COM_REDSHOPB_NO_IMAGE_LABEL'), '#999999', '#dfdfdf'); ?>
									<?php endif; ?>
								</div>

								<?php $itemUrl = 'index.php?option=com_redshopb&id=' . $item->id . '&product_id=' . $productId; ?>

								<?php if ($return) : ?>
									<?php $itemUrl .= '&return=' . $return; ?>
								<?php endif;?>
								<a href="<?php echo RedshopbRoute::_($itemUrl . '&task=product_attribute.edit'); ?>" data-action="leaveForm">
									<?php echo $item->name ?>
								</a>
								<p class="muted">
									<small>
										<?php echo RedshopbHelperProduct_Attribute::getSkuRepresentation($item->id, true); ?>
									</small>
								</p>
								<?php if ($canChange) : ?>
								<a class="btn btn-danger btn-small"
								   onclick="jQuery('#attr<?php echo $i; ?>').prop('checked', true); redSHOPB.products.tabSubmit(event);"
								   href="javascript:void(0)"
								   data-task="product_attributes.delete">
									<i class="icon-trash"></i>
								</a>

								<?php
									echo HTMLHelper::_('rgrid.published', $item->state, $i, 'product_attributes.', $canChange, 'attr', null, null, $formName);
								endif;
								?>

							</td>
							<td>
								<?php if (!empty($item->values)) : ?>
									<ul class="list-inline attribute-values nestedchild unstyled list-unstyled" id="productAttributeValueList<?php echo $item->id ?>">
										<?php foreach ($item->values as $vi => $value) : ?>
											<?php $valueUrl = 'index.php?option=com_redshopb&task=product_attribute_value.edit&id=' . $value->id . '&product_id=' . $productId; ?>

											<?php if ($return) : ?>
												<?php $valueUrl .= '&return=' . $return; ?>
											<?php endif; ?>
											<?php $labelClass = 'important'; ?>

											<?php if ($value->state == 1) : ?>
												<?php $labelClass = 'success'; ?>
											<?php endif; ?>
											<li class="pull-left">
												<div style="display: none">
													<?php echo HTMLHelper::_('rgrid.id', $value->id, $value->id, false, 'vid', 'adminForm', 'vb'); ?>
												</div>
												<?php if ($canChange) : ?>
													<?php $disableClassName = ''; ?>
													<?php $disabledLabel    = ''; ?>

													<?php if (!$saveOrder) : ?>
														<?php $disabledLabel    = Text::_('JORDERINGDISABLED'); ?>
														<?php $disableClassName = 'inactive tip-top'; ?>
														<span class="sortable-handler hasTooltip <?php echo $disableClassName; ?>"
															  title="<?php echo $disabledLabel; ?>">
															<i class="icon-move"></i>
														</span>
													<?php else : ?>
														<span class="sortable-handler inactive">
															<i class="icon-move"></i>
														</span>
													<?php endif; ?>
													<?php $dataValue = ''; ?>

													<?php if ($item->conversion_sets) : ?>
														<?php $selectedConversion = RedshopbHelperConversion::getProductAtrributeDefaultConversionSet($item->id); ?>

														<?php if (!empty($selectedConversion)) : ?>
															<?php $defaultConversionData = RedshopbHelperConversion::getProductAttributeValueConversionData($value->id, $selectedConversion->id); ?>

															<?php if (!empty($defaultConversionData) && isset($defaultConversionData[$selectedConversion->id])) : ?>
																<?php $dataValue = $defaultConversionData[$selectedConversion->id]->value; ?>
															<?php endif; ?>
														<?php endif; ?>
													<?php else : ?>
														<?php $dataValue = RedshopbEntityType::getFieldValue($item->type_id, $value, true); ?>
													<?php endif; ?>

													<?php if ($enableSkuValueDisplay) : ?>
														<?php $dataValue .= ' (' . $value->sku . ')'; ?>
													<?php endif; ?>

													<?php $dataValue = (empty($dataValue)) ? $value->value : $dataValue; ?>

													<?php if ($dataValue != '') : ?>
														<span class="redshopb-type label label-<?php echo $labelClass; ?>"
															  onmouseover="document.getElementById('attribute_value_delete<?php echo $value->id; ?>').style.visibility = 'visible';"
															  onmouseout="document.getElementById('attribute_value_delete<?php echo $value->id; ?>').style.visibility = 'hidden';"
														>
															<a href="<?php echo RedshopbRoute::_($valueUrl); ?>"
															   style="background-color:inherit" class="label label-<?php echo $labelClass; ?>" data-action="leaveForm">
															<?php echo $dataValue; ?>
															</a>
															<a
																href="javascript:void(0);"
																style="visibility: hidden; display: inline;"
																id="attribute_value_delete<?php echo $value->id; ?>"
																class="btn btn-danger btn-mini"
																onclick="jQuery('#vb<?php echo $value->id; ?>').prop('checked', true); redSHOPB.products.tabSubmit(event);"
																data-task="product_attribute_values.delete">
																<i class="icon-trash"></i>
															</a>
														</span>
													<?php endif; ?>
												<?php endif;?>
											</li>
										<?php endforeach; ?>
									</ul>
								<?php else : ?>
									<div class="alert alert-info">
										<div class="pagination-centered">
											<h3><?php echo Text::_('COM_REDSHOPB_PRODUCT_ATTRIBUTE_REQUIRE_SET_FROM_TYPE_AT_LAST_ONE_ATTRIBUTE'); ?></h3>
										</div>
									</div>
								<?php endif; ?>
							</td>
							<td>
								<?php if (!$canReadOnly) : ?>
								<a class="btn btn-default pull-right"
								   onclick="jQuery('#attribute').val('<?php echo $item->id; ?>'); redSHOPB.form.submit(event);"
								   data-task="product_attribute_value.add"
								   data-action="leaveForm">
									<i class="icon-plus-sign"></i>
									<?php echo Text::_('JTOOLBAR_NEW_ATTRIBUTE'); ?>
								</a>
								<?php endif ?>
							</td>
						</tr>
					<?php endforeach; ?>
					</tbody>
				<?php endif; ?>
			</table>
		<?php endif; ?>

		<input type="hidden" name="task" value="">
		<input type="hidden" name="boxchecked" value="0">
		<input type="hidden" name="attribute_id" id="attribute" value="">
		<?php echo HTMLHelper::_('form.token'); ?>
	</form>
</div>
