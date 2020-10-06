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
	"searchFieldSelector" => "#filter_search_product_complimentary_products",
	"orderFieldSelector" => "#list_fullordering",
	"searchField" => "search_product_complimentary_products",
	"limitFieldSelector" => "#list_product_complimentary_products_limit",
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
			$('.product-complimentary-remove').click(function(event){
				event.preventDefault();
				var productId = $(this).attr('data-productremoveid');
				var complimentaryProductId = $(this).attr('data-complimentaryremoveid');
				var productComplimentaryId = $(this).attr('data-productcomplimentaryid');
				var url = '<?php echo Uri::root(); ?>index.php?option=com_redshopb&task=product.ajaxRemoveProductComplimentary';

				$.ajax({
					url: url,
					type: 'POST',
					cache: false,
					data: {
						'<?php echo Session::getFormToken(); ?>' : 1,
						"product_id": productId,
						"complimentary_product_id": complimentaryProductId
					}
				})
				.done(function(data){
					if (data == '1') {
						$('#selectedcomplimentaryproductList').find('tr#product-row-' + productComplimentaryId).fadeOut('slow', function() {
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
		<table class="table table-striped table-hover footable js-redshopb-footable redshopb-footable toggle-circle-filled" id="selectedcomplimentaryproductList">
			<thead>
			<tr>
				<th width="10%" class="nowrap" data-hide="phone">
					&nbsp;
				</th>
				<th class="nowrap" data-toggle="true">
					<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_NAME', 'product_name', $listDirn, $listOrder); ?>
				</th>
			</tr>
			</thead>
			<?php if ($items): ?>
				<tbody>
				<?php foreach ($items as $i => $item): ?>

					<tr id="product-row-<?php echo $item->id; ?>">
						<td>
							<?php if (!$canReadOnly): ?>
							<div class="btn-group">
								<a class="product-item-remove product-complimentary-remove btn btn-default btn-danger" href="javascript:void(0);"
									data-productremoveid="<?php echo $productId; ?>"
									data-productcomplimentaryid="<?php echo $item->id; ?>"
									data-complimentaryremoveid="<?php echo $item->complimentary_product_id; ?>">
									<i class="icon-remove"></i>
								</a>
							</div>
							<?php endif ?>
						</td>
						<td>
							<?php echo $this->escape($item->product_name); ?>
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
