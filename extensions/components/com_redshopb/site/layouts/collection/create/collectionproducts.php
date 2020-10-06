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

$collectionId = Factory::getApplication()->input->getInt('id');
$layout       = Factory::getApplication()->input->getCmd('layout');
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

// @todo Collection Products filter will not enable search tools for list limit
// $data['filter_form']->removeField('product_limit', 'list');

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
<div class="redshopb-collection-collectionproducts">
	<form action="<?php echo $action; ?>" name="<?php echo $formName; ?>" class="adminForm" id="<?php echo $formName; ?>" method="post">
		<?php
		echo RedshopbLayoutHelper::render(
			'searchtools.default',
			array(
				'view' => (object) array(
						'filterForm' => $filterForm,
						'activeFilters' => $data['activeFilters']
					),
				'options' => $searchToolsOptions
			)
		);
		?>
		<div class="redshopb-collection-collectionproducts-table">
			<table class="table table-striped table-hover footable js-redshopb-footable redshopb-footable" id="collection-productList">
				<thead>
				<tr>
					<th style="width:1%;">
					</th>
					<?php if ($layout == 'create_products'): ?>
					<?php endif; ?>
					<th style="width:1%;" class="nowrap">
						<?php echo HTMLHelper::_('rsearchtools.sort', 'JSTATUS', 'p.state', $listDirn, $listOrder, null, 'asc', '', null, $formName); ?>
					</th>
					<th class="nowrap" data-hide="phone, tablet">
						<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_PRODUCT_DISCONTINUED', 'p.discontinued', $listDirn, $listOrder, null, 'asc', '', null, $formName); ?>
					</th>
					<th style="width:8%;" class="nowrap" data-hide="phone">
						<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_SKU', 'p.sku', $listDirn, $listOrder, null, 'asc', '', null, $formName); ?>
					</th>
					<th class="nowrap" data-toggle="true">
						<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_NAME', 'p.name', $listDirn, $listOrder, null, 'asc', '', null, $formName); ?>
					</th>
					<th style="width:20%;" class="nowrap" data-hide="phone, tablet">
						<?php echo Text::_('COM_REDSHOPB_CATEGORIES_LABEL'); ?>
					</th>
					<th width="20%" class="nowrap" data-hide="phone, tablet">
						<?php echo Text::_('COM_REDSHOPB_TAG_LIST_TITLE'); ?>
					</th>
				</tr>
				</thead>
				<tbody>
					<?php if (empty($items)) : ?>
					<?php else : ?>
						<?php if ($items): ?>
							<?php foreach ($items as $i => $item): ?>
								<?php
								$canChange  = false;
								$canEdit    = 1;
								$canCheckin = 1;

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
										<?php if ($layout == 'create_products') : ?>
											<button type="button" class="btn btn-add-to-collection" style="display:none;">
												<?php echo Text::_('COM_REDSHOPB_COLLECTION_ADD_TO_LIST'); ?>
											</button>
											<button type="button" class="btn btn-remove-from-collection">
												<?php echo Text::_('COM_REDSHOPB_COLLECTION_REMOVE_FROM_LIST'); ?>
											</button>
										<?php else : ?>
											<button type="button" class="btn btn-collection-product-variants">
												<?php echo Text::_('COM_REDSHOPB_COLLECTION_VARIANTS'); ?>
											</button>
										<?php endif; ?>
										<span class="collection-product-cid" style="visibility:hidden;">
											<?php echo HTMLHelper::_('rgrid.id', $i, $item->id, false, 'cid', $formName); ?>
										</span>
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
										<?php
										if ($thumb != '') :
										?>
										<span class="hasTooltip" data-original-title="<div class='thumb'><?php echo htmlspecialchars($thumb, ENT_COMPAT, 'UTF-8'); ?></div>">
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
						<?php endif; ?>
					<?php endif; ?>
				</tbody>
			</table>
		</div>
		<div class="redshopb-collection-collectionproducts-pagination">
			<?php echo $pagination->getPaginationLinks('tab.pagination.links', array('showLimitBox' => false)); ?>
		</div>
		<div>
			<input type="hidden" name="task" value="collection.saveModelState">
			<input type="hidden" name="context" value="<?php echo $context; ?>">

			<?php if ($return) : ?>
				<input type="hidden" name="return" value="<?php echo $return ?>">
			<?php endif; ?>
			<input type="hidden" name="boxchecked" value="0">
			<input type="hidden" name="from_collection" value="1">
			<input type="hidden" name="layout" value="create_products">
			<input type="hidden" name="id" value="<?php echo $collectionId ?>">
			<input type="hidden" name="jform[id]" value="<?php echo $collectionId ?>">
			<?php echo HTMLHelper::_('form.token'); ?>
			<script type="text/javascript"><?php echo !empty($document->_script['text/javascript']) ? $document->_script['text/javascript'] : ''; ?></script>
		</div>
	</form>
</div>
