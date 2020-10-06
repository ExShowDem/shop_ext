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
	'view' => (object) array(
		'filterForm' => $displayData['filter_form'],
		'activeFilters' => $displayData['activeFilters']
	),
	'options' => array(
		"searchFieldSelector" => "#filter_search_products",
		"orderFieldSelector" => "#list_fullordering",
		"searchField" => "search_products",
		"limitFieldSelector" => "#list_product_limit",
		"activeOrder" => $listOrder,
		"activeDirection" => $listDirn,
		"formSelector" => ("#" . $formName),
		"filtersHidden" => true
	)
);
?>

<script type="text/javascript">
	var rsbftTablet = 960;
	var rsbftPhone = 768;
	(function ($) {
		$(document).ready(function () {
			<?php if (!$canReadOnly): ?>
			$('.product-accessory-add').click(function(event){
				event.preventDefault();

				var productAttrId = 0;
				var accessoryProductId = $(this).attr('data-accessoryproductid');
				var productId = $(this).attr('data-productid');

				if ($('#product_item_id_' + accessoryProductId).length)
				{
					productAttrId = $('#product_item_id_' + accessoryProductId).val();
				}

				var url = '<?php echo Uri::root(); ?>index.php?option=com_redshopb&task=product.ajaxAddProductAccessory';

				$.ajax({
					url: url,
					type: 'POST',
					cache: false,
					data : {
						'<?php echo Session::getFormToken(); ?>' : 1,
						"accessory_product_id": accessoryProductId,
						"product_attr_id": productAttrId,
						"product_id": productId
					},
				})
				.done(function(data){
					var $rowMsg = $('#accessoryproductList').find('#accessory-row-msg' + accessoryProductId);

					if (data == 1) {
						$rowMsg.show().html('Added Successfully');
						redSHOPB.products.loadTab('selectedaccessories');
					}
					else
					{
						$rowMsg.show().html('Accessory Already Added');
					}
					setTimeout(function() {
						$('#accessory-row-msg' + accessoryProductId).fadeOut('fast');
					}, 1000);
				});
			});
			<?php endif ?>

			$('#<?php echo $formName; ?>').searchtools(
				<?php echo json_encode($searchToolsOptions['options']); ?>
			);
		});
	})(jQuery);
</script>


<form action="<?php echo $action; ?>" name="<?php echo $formName; ?>" class="adminForm" id="<?php echo $formName; ?>" method="post">
<?php echo RedshopbLayoutHelper::render('searchtools.default', $searchToolsOptions); ?>
	<hr/>
	<?php if (empty($items)) : ?>
		<?php echo RedshopbLayoutHelper::render('common.nodata'); ?>
	<?php else : ?>
		<table class="table table-striped table-hover footable js-redshopb-footable redshopb-footable toggle-circle-filled" id="accessoryproductList">
			<thead>
			<tr>
				<th width="1%">
					<input type="checkbox" name="checkall-toggle" value=""
						   title="<?php echo Text::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)"/>
				</th>
				<th width="10%" class="nowrap" data-hide="phone">
					<?php echo Text::_('ADD'); ?>
				</th>
				<th width="1%" class="nowrap">
					<?php echo HTMLHelper::_('rsearchtools.sort', 'JSTATUS', 'p.state', $listDirn, $listOrder); ?>
				</th>
				<th width="1%" class="nowrap" data-hide="phone">
					<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_PRODUCT_DISCONTINUED', 'p.discontinued', $listDirn, $listOrder); ?>
				</th>
				<th width="8%" class="nowrap" data-hide="phone">
					<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_SKU', 'p.sku', $listDirn, $listOrder); ?>
				</th>
				<th class="nowrap" data-toggle="true">
					<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_NAME', 'p.name', $listDirn, $listOrder); ?>
				</th>
				<th width="10%" class="nowrap" data-hide="phone">
					<?php echo Text::_('COM_REDSHOPB_COMPANY_LABEL'); ?>
				</th>
				<th width="20%" class="nowrap" data-hide="phone">
					<?php echo Text::_('COM_REDSHOPB_CATEGORIES_LABEL'); ?>
				</th>
				<th width="20%" class="nowrap" data-hide="phone">
					<?php echo Text::_('COM_REDSHOPB_TAG_LIST_TITLE'); ?>
				</th>
			</tr>
			</thead>
			<?php if ($items): ?>
				<tbody>
				<?php foreach ($items as $i => $item): ?>
					<?php
					$canChange  = RedshopbHelperACL::getPermission('manage', 'product', Array('edit.state'), true) && !$canReadOnly;
					$canEdit    = RedshopbHelperACL::getPermission('manage', 'product', Array('edit','edit.own'), true);
					$canCheckin = $canEdit;

					// Discontinued label
					if ($item->discontinued)
					{
						$discontinuedLabelClass = 'important';
						$discontinuedLabelTxt   = Text::_('JYES');
					}

					else
					{
						$discontinuedLabelClass = 'success';
						$discontinuedLabelTxt   = Text::_('JNO');
					}

					$thumb = RedshopbHelperProduct::getProductImageThumbHtml($item->id);
					?>
					<tr id="product-row-<?php echo $item->id; ?>">
						<td>
							<?php echo HTMLHelper::_('grid.id', $i, $item->id); ?>
						</td>
						<td>
						<div id="accessory-row-msg<?php echo $item->id; ?>" class="alert alert-info" style="display: none;"></div>
						<?php
						$options   = array();
						$options[] = HTMLHelper::_('select.option', '', Text::_('JOPTION_SELECT_SKU'));
						$columns   = RedshopbHelperProduct::getSKUCollection($productId, 'objectList', false);

						if ($columns)
						{
							foreach ($columns as &$column)
							{
								$options[] = (object) array('text' => $column->sku, 'value' => $column->pi_id);
							}

							echo HTMLHelper::_('select.genericlist', $options, 'product_item_id_' . $item->id, 'class="inputbox"', 'value', 'text', '');
							echo '<br />';
						}

						if (!$canReadOnly):
						?>
						<a class="product-item-add product-accessory-add" href="javascript:void(0);"
						data-accessoryproductid="<?php echo $item->id; ?>"
						data-productid="<?php echo $productId; ?>">
						<button><?php echo Text::_('COM_REDSHOPB_ACCESSORY_ADD');?></button>
						</a>
						<?php endif ?>
						</td>
						<td>
							<?php echo HTMLHelper::_('rgrid.published', $item->state, $i, 'products.', $canChange, 'cb', null, null, $formName); ?>
						</td>
						<td>
							<span class="label label-<?php echo $discontinuedLabelClass ?>">
							<?php echo $discontinuedLabelTxt ?>
						</span>
						</td>
						<td>
							<?php echo $this->escape($item->sku); ?>
						</td>
						<td>
							<?php
							if ($item->checked_out) :
								   echo HTMLHelper::_(
									   'rgrid.checkedout',
									   $i,
									   $item->checked_out > 0 ? Factory::getUser($item->checked_out)->get('name') : '',
									   $item->checked_out_time,
									   'products.',
									   $canCheckin
								   );
							endif;
							?>
							<?php if ($canEdit) : ?>
							<a href="<?php echo RedshopbRoute::_('index.php?option=com_redshopb&task=product.edit&id=' . $item->id); ?>">
							<?php endif; ?>

							<?php if ($thumb != '') : ?>
								<span
									class="hasTooltip"
									data-original-title="<div class='thumb'><?php echo htmlspecialchars($thumb, ENT_COMPAT, 'UTF-8'); ?></div>"
								>
							<?php endif; ?>
									<?php echo $this->escape($item->name); ?>

							<?php if ($thumb != '') : ?>
								</span>
							<?php endif; ?>

							<?php if ($canEdit) : ?>
							</a>
							<?php endif; ?>
						</td>
						<td>
							<?php echo $this->escape(RedshopbEntityCompany::getInstance($item->company_id)->get('name')); ?>
						</td>
						<td>
							<?php
							if (!empty($item->categories)):
								$categories = array();

								foreach ($item->categories as $category):
									$categories[] = $category->name;
								endforeach;
								echo implode(', ', $categories);
							endif;
							?>
						</td>
						<td>
							<?php
							if (!empty($item->tags)):
								$tags = array();

								foreach ($item->tags as $tag):
									$tags[] = $tag->name;
								endforeach;
								echo implode(', ', $tags);
							endif;
							?>
						</td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			<?php endif; ?>
		</table>
		<?php echo $pagination->getPaginationLinks('tab.pagination.links', array('showLimitBox' => false)); ?>
	<?php endif; ?>
	<input type="hidden" name="task" value="">
	<input type="hidden" name="jform[product_id]" value="<?php echo $productId ?>">
	<input type="hidden" name="boxchecked" value="0">
	<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>"/>
	<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>"/>
	<?php echo HTMLHelper::_('form.token'); ?>
	</form>
</div>
