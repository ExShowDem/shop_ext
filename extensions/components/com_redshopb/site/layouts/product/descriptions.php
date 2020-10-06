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

HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('rdropdown.init');
HTMLHelper::_('vnrbootstrap.tooltip');
HTMLHelper::_('rjquery.chosen', 'select');

$formName = $displayData['formName'];
$state    = $displayData['state'];
$return   = isset($displayData['return']) ? $displayData['return'] : false;
$url      = $displayData['action'];

if ($return)
{
	$url .= '&return=' . $return;
}

$action = RedshopbRoute::_($url);

$items       = $displayData['items'];
$productId   = isset($displayData['productId']) ? $displayData['productId'] : 0;
$pagination  = $displayData['pagination'];
$canReadOnly = RedshopbEntityProduct::getInstance($productId)->canReadOnly();
$showToolbar = isset($displayData['showToolbar']) && !$canReadOnly ? $displayData['showToolbar'] : false;

$formName = (isset($displayData['formName'])) ? $displayData['formName'] : 'adminForm';

$listOrder = $state->get('list.ordering');
$listDirn  = $state->get('list.direction');
$saveOrder = $listOrder == 'ordering';
$canEdit   = RedshopbHelperACL::getPermission('manage', 'product', array('edit', 'edit.own'), true);

$searchToolsOptions = array(
	'filterButton' => false,
	"searchFieldSelector" => "#filter_search_product_descriptions",
	"orderFieldSelector" => "#list_fullordering",
	"searchField" => "search_product_descriptions",
	"limitFieldSelector" => "#list_description_limit",
	"activeOrder" => $listOrder,
	"activeDirection" => $listDirn,
	"formSelector" => ("#" . $formName),
	"filtersHidden" => (bool) empty($displayData['activeFilters'])
);
?>
<script type="text/javascript">
	(function ($) {
		$(document).ready(function () {
			$('#<?php echo $formName; ?>').searchtools(
				<?php echo json_encode($searchToolsOptions); ?>
			);
		});
	})(jQuery);
</script>
<h4>
	<?php echo Text::_('COM_REDSHOPB_PRODUCT_DESCRIPTION_LIST_TITLE'); ?>
</h4>
<form action="<?php echo $action; ?>" name="<?php echo $formName ?>" class="adminForm" id="<?php echo $formName ?>" method="post">
<?php
// Render the toolbar?
if ($showToolbar)
{
	echo RedshopbLayoutHelper::render('product.descriptions.toolbar', $displayData);
}
?>
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
		<table class="table table-striped table-hover footable js-redshopb-footable redshopb-footable toggle-circle-filled" id="productDescriptionList">
			<thead>
			<tr>
				<th width="1%">
					<input type="checkbox" name="checkall-toggle" value=""
						   title="<?php echo Text::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)"/>
				</th>
				<th class="nowrap">
					<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_PRODUCT_DESCRIPTION_FLAT_ATTRIBUTE_LABEL', 'value', $listDirn, $listOrder); ?>
				</th>
				<th>
					<?php echo Text::_('COM_REDSHOPB_PRODUCT_DESCRIPTION'); ?>
				</th>
				<th width="1%" class="nowrap" data-hide="phone">
					<?php echo HTMLHelper::_('rsearchtools.sort', 'JGRID_HEADING_ID', 'pd.id', $listDirn, $listOrder); ?>
				</th>
			</tr>
			</thead>
			<tfoot>
			<tr>
				<td colspan="4">
					<?php echo $pagination->getPaginationLinks('tab.pagination.links', array('showLimitBox' => false)); ?>
				</td>
			</tr>
			</tfoot>
			<?php if ($items): ?>
				<tbody>
				<?php foreach ($items as $i => $item): ?>
					<tr>
						<td>
							<?php echo HTMLHelper::_('rgrid.id', $i, $item->id, false, 'cid', $formName); ?>
						</td>
						<td>
							<?php
							$attributeName = $item->value;

							if (empty($item->main_attribute_value_id))
							{
								$attributeName = Text::_('COM_REDSHOPB_PRODUCT_DESCRIPTION_GENERAL_DESCRIPTION');
							}

							if ($canEdit) :
								$itemUrl = 'index.php?option=com_redshopb&task=description.edit&id=' . $item->id
									. '&product_id=' . $productId;

								if ($return)
								{
									$itemUrl .= '&return=' . $return;
								}
								?>
							<a href="<?php echo RedshopbRoute::_($itemUrl); ?>">
							<?php endif; ?>
								<?php echo $this->escape($attributeName); ?>

							<?php if ($canEdit) : ?>
							</a>
							<?php endif; ?>
						</td>
						<td>
							<?php echo $item->description_intro; ?>
						</td>
						<td>
							<?php echo $item->id; ?>
						</td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			<?php endif; ?>
		</table>
	<?php endif; ?>
	<input type="hidden" name="task" value="">
	<input type="hidden" name="boxchecked" value="0">
	<?php echo HTMLHelper::_('form.token'); ?>
</form>
