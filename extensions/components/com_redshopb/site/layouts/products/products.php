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

HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('rdropdown.init');
HTMLHelper::_('vnrbootstrap.tooltip');
HTMLHelper::_('rjquery.chosen', 'select');
HTMLHelper::_('vnrbootstrap.modal', 'productDiscontinue');
?>

<script type="text/javascript">
var rsbftTablet = 960;
var rsbftPhone = 768;

jQuery(document).ready(function() {
	var csvButton = jQuery('#main > div.redcore div.btn-group.pull-right button:contains("Csv")');
	var onclick   = csvButton.attr('onclick');

	csvButton.removeAttr('onclick');

	csvButton.on('click', function(){
		var ifCondition = document.<?php echo (isset($displayData['formName'])) ? $displayData['formName'] : 'adminForm'; ?>.boxchecked.value==0;

		if (ifCondition)
		{
			alert('<?php echo Text::_('COM_REDSHOPB_PRODUCT_NO_PRODUCTS_SELECTED_DOWNLOAD_CSV', true); ?>');
		}
		else
		{
			var url = onclick.slice(onclick.indexOf('http'),-2);
			redSHOPB.ajax.generateCsvFile("products", "#productList", url);
		}
	});
});
</script>
<?php
RedshopbHtml::loadFooTable();

foreach (get_object_vars($displayData['this']) as $key => $value)
{
	$this->$key = $value;
}

$formName = (isset($displayData['formName'])) ? $displayData['formName'] : 'adminForm';

$action    = $displayData['action'];
$listOrder = $this->state->get('list.ordering');
$listDirn  = $this->state->get('list.direction');
$saveOrder = $listOrder == 'ordering';

$redshopbParams = RedshopbApp::getConfig();
$relatedSKUName = $redshopbParams->get('related_sku_name', Text::_('COM_REDSHOPB_RELATED_DEFAULT_SKU')) . ' ' . Text::_('COM_REDSHOPB_SKU');
?>
<form action="<?php echo $action; ?>" name="adminForm" class="adminForm" id=<?php echo $formName ?> method="post">
	<?php
	if (isset($displayData['search']) && $displayData['search'] == true)
	{
		echo RedshopbLayoutHelper::render(
			'searchtools.default',
			array(
				'view' => $this,
				'options' => array(
					'searchField' => 'search_products',
					'searchFieldSelector' => '#filter_search_products',
					'limitFieldSelector' => '#list_product_limit',
					'activeOrder' => $listOrder,
					'activeDirection' => $listDirn
				)
			)
		);
	}
	?>

	<hr/>
	<?php if (empty($this->items)) : ?>
		<?php echo RedshopbLayoutHelper::render('common.nodata'); ?>
	<?php else : ?>
		<table class="table table-striped table-hover footable js-redshopb-footable redshopb-footable toggle-circle-filled" id="productList">
			<thead>
			<tr>
				<th width="1%">
					<input type="checkbox" name="checkall-toggle" value=""
						title="<?php echo Text::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)"/>
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
					<?php echo Text::_('COM_REDSHOPB_MANUFACTURERS_LABEL'); ?>
				</th>
				<th width="20%" class="nowrap" data-hide="phone">
					<?php echo Text::_('COM_REDSHOPB_TAG_LIST_TITLE'); ?>
				</th>
				<th width="1%" data-hide="phone">
					<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_ID', 'p.id', $listDirn, $listOrder); ?>
				</th>
			</tr>
			</thead>
			<?php if ($this->items): ?>
				<tbody>
				<?php
				$canChange  = RedshopbHelperACL::getPermission('manage', 'product', Array('edit.state'), true);
				$canEdit    = RedshopbHelperACL::getPermission('manage', 'product', Array('edit','edit.own'), true);
				$canCheckin = $canEdit;

				foreach ($this->items as $i => $item): ?>
					<?php
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
							<?php echo HTMLHelper::_('grid.id', $i, $item->id); ?>
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
							<?php
								echo $this->escape($item->sku);

							if ($item->manufacturer_sku != '') :
								echo '<br /><br /><strong>' . Text::_('COM_REDSHOPB_MANUFACTURER_SKU') . ':</strong> ' . $this->escape($item->manufacturer_sku);
							endif;

							if ($item->related_sku != '') :
								echo '<br /><br /><strong>' . $relatedSKUName . ':</strong> ' . $this->escape($item->related_sku);
							endif;
							?>
						</td>
						<td>
							<?php if ($item->checked_out) : ?>
								<?php echo HTMLHelper::_('rgrid.checkedout', $i, $item->checked_out > 0 ? Factory::getUser($item->checked_out)->get('name') : '',
									$item->checked_out_time, 'products.', $canCheckin
								); ?>
							<?php endif; ?>

							<?php if ($canEdit) : ?>
							<a href="<?php echo RedshopbRoute::_('index.php?option=com_redshopb&task=product.edit&id=' . $item->id); ?>">
							<?php endif; ?>
							<?php
							if ($thumb != '') :
							?>
							<span class="hasTooltip"
							  data-original-title="<div class='thumb'><?php echo htmlspecialchars($thumb, ENT_COMPAT, 'UTF-8'); ?></div>">
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
							<?php if ($canEdit) : ?>
							</a>
							<?php endif; ?>
						</td>
						<td>
							<?php echo $this->escape($item->company); ?>
						</td>
						<td>
							<?php
							if (!empty($item->categories)):
								$categories = array();

								foreach ($item->categories as $category):
									$categories[] = RedshopbEntityCategory::getInstance($category)->get('name');
								endforeach;
								echo implode(', ', $categories);
							endif;
							?>
						</td>
						<td>
							<?php echo $item->manufacturer_name ?>
						</td>
						<td>
							<?php
							if (!empty($item->tags)):
								$tags = array();

								foreach ($item->tags as $tag):
									$tags[] = RedshopbEntityTag::getInstance($tag)->get('name');
								endforeach;
								echo implode(', ', $tags);
							endif;
							?>
						</td>
						<td>
							<?php echo $item->id; ?>
						</td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			<?php endif; ?>
		</table>
		<?php echo $this->pagination->getPaginationLinks(null, array('showLimitBox' => false)); ?>
	<?php endif; ?>

	<div>
		<input type="hidden" name="task" value="">
		<input type="hidden" name="boxchecked" value="0">
		<?php echo HTMLHelper::_('form.token'); ?>
	</div>
</form>

<div id="productDiscontinue" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-content">
		<div class="modal-dialog">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
				<h3 id="myModalLabel"><i class="icon-warning-sign"></i>&nbsp;<?php echo Text::_('COM_REDSHOPB_PRODUCT_DISCONTINUE_CONFIRM'); ?></h3>
			</div>
			<div class="modal-body">
				<p><?php echo Text::_('COM_REDSHOPB_PRODUCT_DISCONTINUE_INFO'); ?></p>
			</div>
			<div class="modal-footer">
				<button class="btn btn" data-dismiss="modal" aria-hidden="true"><?php echo Text::_('JNO')?></button>
				<button class="btn btn-primary" data-dismiss="modal" onclick="Joomla.submitbutton('products.discontinue')"><?php echo Text::_('JYES')?></button>
			</div>
		</div>
	</div>
</div>
