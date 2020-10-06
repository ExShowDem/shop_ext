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
HTMLHelper::_('rdropdown.init');
HTMLHelper::_('vnrbootstrap.tooltip');
HTMLHelper::_('rjquery.chosen', 'select');

RedshopbHtml::loadFooTable();

$user   = RedshopbHelperUser::getUser(Factory::getUser()->id, 'joomla');
$config = RedshopbApp::getConfig();
$width  = $config->get('thumbnail_width', 144);
$height = $config->get('thumbnail_height', 144);
$size   = $width . 'x' . $height;


$formName = (isset($displayData['formName'])) ? $displayData['formName'] : 'adminForm';
$state    = $displayData['state'];
$return   = isset($displayData['return']) ? $displayData['return'] : false;
$url      = isset($displayData['action']) ? $displayData['action'] : 'index.php?option=com_redshopb&view=products';

$url = $displayData['action'];

if ($return)
{
	$url .= '&return=' . $return;
}

$action = RedshopbRoute::_($url);

$items       = $displayData['items'];
$productId   = $displayData['productId'];
$pagination  = $displayData['pagination'];
$canReadOnly = RedshopbEntityProduct::getInstance($productId)->canReadOnly();
$showToolbar = isset($displayData['showToolbar']) && !$canReadOnly ? $displayData['showToolbar'] : false;

$listOrder = $state->get('list.ordering');
$listDirn  = $state->get('list.direction');

$searchToolsOptions = array(
	"searchFieldSelector" => "#filter_search_product_accessories",
	"orderFieldSelector" => "#list_fullordering",
	"searchField" => "search_product_accessories",
	"limitFieldSelector" => "#list_product_accessories_limit",
	"activeOrder" => $listOrder,
	"activeDirection" => $listDirn,
	"formSelector" => ("#" . $formName),
	"filtersHidden" => true
);
?>

<script type="text/javascript">
	var rsbftTablet = 960;
	var rsbftPhone = 768;
(function ($) {
	$(document).ready(function () {
		<?php if (!$canReadOnly): ?>
			$('.product-accessory-remove').click(function(event){
				event.preventDefault();
				var productId = $(this).attr('data-productremoveid');
				var accessoryProductId = $(this).attr('data-accessoryremoveid');
				var productAccessoryId = $(this).attr('data-productaccessoryid');
				var url = '<?php echo Uri::root(); ?>index.php?option=com_redshopb&task=product.ajaxRemoveProductAccessory';

				$.ajax({
					url: url,
					type: 'POST',
					cache: false,
					data: {
						'<?php echo Session::getFormToken(); ?>' : 1,
						"product_id": productId,
						"accessory_product_id": accessoryProductId
					}
				})
				.done(function(data){
					if (data == '1') {
						$('#selectedaccessoryproductList').find('tr#product-row-' + productAccessoryId).fadeOut('slow', function() {
							$(this).remove();
						});
					}
				});
			});
		<?php endif ?>

			$('#<?php echo $formName; ?>').searchtools(
				<?php echo json_encode($searchToolsOptions); ?>
			);
		});
	})(jQuery);
</script>


<form action="<?php echo $action; ?>" name="<?php echo $formName; ?>" class="adminForm" id="<?php echo $formName; ?>" method="post">
<?php
echo RedshopbLayoutHelper::render(
	'searchtools.default',
	array(
				'view' => (object) array(
						'filterForm' => $displayData['filter_form'],
						'activeFilters' => $displayData['activeFilters']
					),
				'options' => $searchToolsOptions
			)
);
?>

	<hr/>
	<?php if (empty($items)) : ?>
		<?php echo RedshopbLayoutHelper::render('common.nodata'); ?>
	<?php else : ?>
		<table class="table table-striped table-hover footable js-redshopb-footable redshopb-footable toggle-circle-filled" id="selectedaccessoryproductList">
			<thead>
			<tr>
				<th width="10%" class="nowrap" data-hide="phone">
					&nbsp;
				</th>
				<th class="nowrap" data-toggle="true">
					<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_NAME', 'product_name', $listDirn, $listOrder); ?>
				</th>
				<th>
					<?php echo Text::_('COM_REDSHOPB_DATA_TYPE_LBL') ?>
				</th>
				<th width="1%" class="nowrap">
					<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_ACCESSORIES_SELECTION', 'selection', $listDirn, $listOrder); ?>
				</th>
				<th width="1%" class="nowrap">
					<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_ACCESSORIES_HIDE_ON_COLLECTION', 'state', $listDirn, $listOrder); ?>
				</th>
			</tr>
			</thead>
			<?php if ($items): ?>
				<tbody>
				<?php foreach ($items as $i => $item): ?>

					<tr id="product-row-<?php echo $item->id; ?>">
						<td>
							<div class="btn-group">
								<?php if (!$canReadOnly): ?>
								<a class="product-item-remove product-accessory-remove btn btn-default btn-danger" href="javascript:void(0);"
									data-productremoveid="<?php echo $productId; ?>"
									data-productaccessoryid="<?php echo $item->id; ?>"
									data-accessoryremoveid="<?php echo $item->accessory_product_id; ?>">
									<i class="icon-remove"></i>
								</a>
								<?php
								endif;

if (!empty($item->attribute_value_id)):
	$itemUrl = Uri::root() . 'index.php?option=com_redshopb&task=product_item_accessory.edit&id=' . $item->id . '&jform[product_id]=' . $productId;
else:
									$itemUrl = Uri::root() . 'index.php?option=com_redshopb&task=product_accessory.edit&id=' . $item->id . '&jform[product_id]=' . $productId;
endif;

if ($return):
	$itemUrl .= '&return=' . $return;
endif;
								?>
								<a class="btn btn-default" href="<?php echo $itemUrl; ?>"><i class="icon-edit"></i></a>
							</div>
						</td>
						<td>
							<?php echo $this->escape($item->product_name); ?>
						</td>
						<td>
							<?php if (!empty($item->attribute_value_id)): ?>
								<?php echo $this->escape($item->product_item_sku); ?>
							<?php endif; ?>
						</td>
						<td>
							<?php echo $this->escape($item->selection); ?>
						</td>
						<td>
							<?php if ($item->hide_on_collection == 1): ?>
								<?php echo Text::_('JYES'); ?>
							<?php else: ?>
								<?php echo Text::_('JNO'); ?>
							<?php endif; ?>
						</td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			<?php endif; ?>
		</table>
		<?php echo $pagination->getPaginationLinks('tab.pagination.links', array('showLimitBox' => false)); ?>
	<?php endif; ?>
	<div>
			<input type="hidden" name="task" value="">
			<input type="hidden" name="jform[product_id]" value="<?php echo $productId ?>">
			<input type="hidden" name="boxchecked" value="0">
			<?php echo HTMLHelper::_('form.token'); ?>
		</div>
	</form>
</div>
