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

$data = $displayData;

$state      = $data['state'];
$items      = $data['items'];
$pagination = $data['pagination'];
$filterForm = $displayData['filter_form'];
$formName   = $data['formName'];
$return     = isset($data['return']) ? $data['return'] : null;
$context    = isset($data['context']) ? $data['context'] : null;
$action     = $data['action'];
$document   = Factory::getDocument();

HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('rdropdown.init');
HTMLHelper::_('vnrbootstrap.tooltip');
HTMLHelper::_('rjquery.chosen', 'select');

RedshopbHtml::loadFooTable();

$collectionId = Factory::getApplication()->input->getInt('id');
$listOrder    = $state->get('list.ordering');
$listDirn     = $state->get('list.direction');
$saveOrder    = $listOrder == 'ordering';

$searchToolsOptions = array(
	"searchFieldSelector" => "#filter_search_products",
	"orderFieldSelector" => "#list_fullordering",
	"searchField" => "search_products",
	"limitFieldSelector" => "#list_product_limit",
	"activeOrder" => $listOrder,
	"activeDirection" => $listDirn,
	"formSelector" => ("#" . $formName),
	"filtersHidden" => (bool) empty($data['activeFilters'])
);

$user               = Factory::getUser();
$allowedCompanies   = explode(",", RedshopbHelperACL::listAvailableCompanies($user->id));
$mayEditProducts    = RedshopbHelperACL::getPermission('manage', 'product', array('edit','edit.own'), true);
$canManageWareHouse = RedshopbHelperACL::getPermission('manage', 'mainwarehouse');

$saveOrderLink = 'index.php?option=com_redshopb&task=collection_product_xrefs.saveOrderAjax&tmpl=component';
HTMLHelper::_('rsortablelist.sortable', 'productList', 'collectionProductsForm', strtolower($listDirn), $saveOrderLink, false, false);

$currentOrder = 0;

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
<div class="redshopb-collection-edit-collectionproducts">
	<form action="<?php echo $action; ?>" name="<?php echo $formName; ?>" class="adminForm" id="<?php echo $formName; ?>" method="post">
		<?php
		echo RedshopbLayoutHelper::render(
			'searchtools.default',
			array(
				'view' => (object) array(
						'filterForm' => $data['filter_form'],
						'activeFilters' => $data['activeFilters']
					),
				'options' => $searchToolsOptions
			)
		);
		?>

		<hr/>
		<?php if (empty($items)) : ?>
			<?php echo RedshopbLayoutHelper::render('common.nodata'); ?>
		<?php else : ?>
			<table class="table table-striped table-hover footable js-redshopb-footable redshopb-footable" id="productList">
				<thead>
				<tr>
					<th width="1%">
					</th>
					<th width="3%">
						<?php echo HTMLHelper::_('rsearchtools.sort', '<i class=\'icon-sort\'></i>', 'cpx.ordering', $listDirn, $listOrder); ?>
					</th>
					<th width="1%" class="nowrap">
						<?php echo HTMLHelper::_('rsearchtools.sort', 'JSTATUS', 'p.state', $listDirn, $listOrder, null, 'asc', '', null, $formName); ?>
					</th>
					<th width="1%" class="nowrap" data-hide="phone">
						<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_PRODUCT_DISCONTINUED', 'p.discontinued', $listDirn, $listOrder, null, 'asc', '', null, $formName); ?>
					</th>
					<th width="6%" class="nowrap" data-hide="phone">
						<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_SKU', 'p.sku', $listDirn, $listOrder, null, 'asc', '', null, $formName); ?>
					</th>
					<th class="nowrap" data-toggle="true">
						<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_NAME', 'p.name', $listDirn, $listOrder, null, 'asc', '', null, $formName); ?>
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
						$canChange  = false;
						$canEdit    = $mayEditProducts
							&& (($item->company_id && RedshopbHelperACL::getPermission('manage', 'company', array(), true, $item->company_asset_id)
								|| ((!$item->company_id) && $canManageWareHouse)));
						$canCheckin = $canEdit;

						$ordering     = ($item->collection_order != '' ? $item->collection_order : $currentOrder + 1);
						$currentOrder = $ordering;

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
						<tr>
							<td>
								<button type="button" class="btn btn-remove-from-collection">
									<?php echo Text::_('COM_REDSHOPB_COLLECTION_REMOVE_FROM_LIST'); ?>
								</button>
								<span class="collection-product-cid" style="visibility:hidden;">
									<?php echo HTMLHelper::_('rgrid.id', $i, $item->id, false, 'cid', $formName); ?>
								</span>
							</td>
							<td class="order nowrap center">
								<span class="sortable-handler hasTooltip">
									<i class="icon-move"></i>
								</span>
								<input type="text" style="display:none" name="order[]" value="<?php echo $ordering;?>" class="text-area-order" />
							</td>
							<td>
								<?php echo HTMLHelper::_('rgrid.published', $item->state, $i, 'products.', $canChange, 'cb'); ?>
							</td>
							<td>
								<span class="label label-<?php echo $discontinuedLabelClass ?>">
								<?php echo $discontinuedLabelTxt ?>
							</span>
							</td>
							<td>
								<?php echo $item->sku; ?>
							</td>
							<td>
								<?php if ($item->checked_out) : ?>
									<?php echo HTMLHelper::_('rgrid.checkedout', $i, $item->checked_out > 0 ? Factory::getUser($item->checked_out)->get('name') : '', $item->checked_out_time, 'products.', $canCheckin, 'cb', 'collectionProductsForm'); ?>
								<?php endif; ?>
								<?php
								if ($thumb != '') :
								?>
								<span class="hasTooltip" data-original-title="<div class='thumb'><?php echo htmlspecialchars($thumb, ENT_COMPAT, 'UTF-8'); ?></thumb>">
								<?php
								endif;
								?>
									<?php echo $this->escape($item->name); ?>
								<?php
								if ($thumb != '') :
								?>
								</span>
								<?php
								endif;
								?>
							</td>
							<td>
								<?php
									$categorytitles = array();

								if (!empty($item->categories)) :
									foreach ($item->categories as $category) :
										$categorytitles[] = $category->name;
									endforeach;
								endif;

									echo implode(', ', $categorytitles);
								?>
							</td>
							<td>
								<?php
									$tagtitles = array();

								if (!empty($item->tags)) :
									foreach ($item->tags as $tag) :
										$tagtitles[] = $tag->name;
									endforeach;
								endif;

									echo implode(', ', $tagtitles);
								?>
							</td>
						</tr>
					<?php endforeach; ?>
					</tbody>
				<?php endif; ?>
			</table>
			<?php if (!empty($data['showPagination'])) : ?>
				<?php echo $pagination->getPaginationLinks('tab.pagination.links', array('showLimitBox' => false)); ?>
			<?php endif; ?>
		<?php endif; ?>

		<div>
			<input type="hidden" name="task" value="collection.saveModelState">
			<input type="hidden" name="context" value="<?php echo $context; ?>" />

			<?php if ($return) : ?>
				<input type="hidden" name="return" value="<?php echo $return ?>">
			<?php endif; ?>
			<input type="hidden" name="boxchecked" value="0">
			<input type="hidden" name="layout" value="create_products">
			<input type="hidden" name="id" value="<?php echo $collectionId ?>">
			<?php echo HTMLHelper::_('form.token'); ?>
			<script type="text/javascript"><?php echo !empty($document->_script['text/javascript']) ? $document->_script['text/javascript'] : ''; ?></script>
		</div>
	</form>
</div>
