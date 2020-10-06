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
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Session\Session;

RHelperAsset::load('collection.css', 'com_redshopb');

// HTML helpers
HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('vnrbootstrap.tooltip');
HTMLHelper::_('rjquery.chosen', 'select');
HTMLHelper::_('rsearchtools.main');

RedshopbHtml::loadFooTable();

$url = 'index.php?option=com_redshopb&view=collection';

$return = Factory::getApplication()->input->getBase64('return');

if ($return)
{
	$url .= '&return=' . $return;
}

$action = RedshopbRoute::_($url);

$companyId      = RedshopbInput::getCompanyIdForm();
$fromCompany    = RedshopbInput::isFromCompany();
$fromCollection = RedshopbInput::isFromCollection();
$departmentId   = RedshopbInput::getDepartmentIdForm();
$fromDepartment = RedshopbInput::isFromDepartment();
$isNew          = (int) $this->item->id <= 0;

$input = Factory::getApplication()->input;
echo $input->get('list[product_limit]');

// Transitional added products
$cids = $input->get('cids', '');

?>
<?php if ($this->item->id) : ?>
	<script type="text/javascript">
		function addToCollectionProducts(obj, pid, withEffect) {
			var selectedRow = jQuery(obj).closest('tr');
			var selectedId = jQuery(obj).closest('td').find('input:checkbox').val();

			if (selectedId == undefined && pid)
			{
				selectedId = pid;
			}
			else
			{
				var productInputBox = getProductInputBox(selectedId, 'collection-products-selected');

				if (withEffect)
				{
					selectedRow.fadeOut(400, function() {
						jQuery(obj).hide();
						jQuery(selectedRow).find(".btn-remove-from-collection").show();
						var lastTr = jQuery("#collection-productList tbody tr:last");

						if (lastTr.length == 0)
						{
							jQuery("#collection-productList tbody").html(selectedRow);
						}
						else
						{
							lastTr.after(selectedRow);
						}

						selectedRow.fadeIn(400, function() {
							// init footable for the tab
							jQuery('.js-redshopb-footable').footable({
								paginate: false,
								sort: false,
								breakpoints: {
									phone: 480,
									tablet: 1024
								}
							});
						});
					});
				}
				else
				{
					selectedRow.detach();
				}
			}

			var newProduct = '<input type="hidden" name="cid[]" value="' + selectedId + '" />';
			jQuery('#collection-products-selected').append(newProduct);
			jQuery('#collection-products-products-selected').append(newProduct);
		}

		function getProductInputBox(selectedId, divId)
		{
			var productInputBox = false;
			jQuery('#' + divId + ' :input').each(function (idx, ele) {
				if (jQuery(ele).val() == selectedId) {
					productInputBox = ele;
				}
			});

			return productInputBox;
		}

		function removeFromCollection(obj) {
			var selectedRow = jQuery(obj).closest('tr');
			var selectedId = jQuery(obj).closest('td').find('input:checkbox').val();

			var productInputBox = getProductInputBox(selectedId, 'collection-products-selected');
			var productInputBoxAdd = getProductInputBox(selectedId, 'collection-products-products-selected');

			if (productInputBox != false) {
				$(productInputBox).remove();
			}

			if (productInputBoxAdd != false) {
				$(productInputBoxAdd).remove();
			}

			jQuery(selectedRow).find(".btn-add-to-collection").show();
			jQuery(obj).hide();
			selectedRow.detach();

			jQuery("#productList tr:last").after(selectedRow);
			selectedRow.fadeOut();
			selectedRow.fadeIn();
		}

		function processTransitionalProducts()
		{
			<?php
			if ($cids != '')
					:
				$cidsarray = explode('_', $cids);

				foreach ($cidsarray as $cid)
				:
			?>
			addToCollectionProducts(jQuery('.btn-add-to-collection-<?php echo $cid ?>'), <?php echo $cid ?>, false);
			<?php
				endforeach;
			endif;
			?>
		}

		(function ($) {
			var newProductAdded = false;

			function ajaxProductsSetup(element) {
				$.ajax({
					url: '<?php echo Uri::root(); ?>index.php?option=com_redshopb&view=collection&task=collection.ajaxcreateproducts'
						+ '&id=<?php echo $this->item->id ?>&<?php echo Session::getFormToken() ?>=1',
					dataType: 'text',
					type: 'POST',
					data : {
						"<?php echo Session::getFormToken() ?>": 1
					},
					beforeSend: function (xhr) {
						$('.products-content .spinner').show();
						$('.products-content').addClass('opacity-40');
					}
				}).done(function (data) {
						$('.products-content .spinner').hide();
						$('.products-content').removeClass('opacity-40').html(data);
						$('select').chosen();
						$('.chzn-search').hide();
						$('.hasTooltip').tooltip({"animation": true, "html": true, "placement": "top",
							"selector": false, "title": "", "trigger": "hover focus", "delay": 0, "container": false});

						// init footable for the tab
						jQuery('.js-redshopb-footable').footable({
							paginate: false,
							sort: false,
							breakpoints: {
								phone: 480,
								tablet: 1024
							}
						});

						processTransitionalProducts();
					});
			}

			function ajaxCollectionProductsSetup(element) {
				$.ajax({
					url: '<?php echo Uri::root(); ?>index.php?option=com_redshopb&view=collection&task=collection.ajaxcreatecollectionproducts'
						+ '&id=<?php echo $this->item->id ?>'
						+ '&layout=<?php echo Factory::getApplication()->input->getCmd('layout'); ?>'
						+ '&cids=<?php echo $cids ?>'
						+ "&<?php echo Session::getFormToken(); ?>=1",
					dataType: 'text',
					type: 'POST',
					data : {
						"<?php echo Session::getFormToken() ?>": 1
					},
					beforeSend: function (xhr) {
						$('.collection-products-content .spinner').show();
						$('.collection-products-content').addClass('opacity-40');
					}
				}).done(function (data) {
						$('.collection-products-content .spinner').hide();
						$('.collection-products-content').removeClass('opacity-40').html(data);
						$('select').chosen();
						//$('.chzn-search').hide();
						$('.hasTooltip').tooltip({"animation": true, "html": true, "placement": "top",
							"selector": false, "title": "", "trigger": "hover focus", "delay": 0, "container": false});

						// init footable for the tab
						jQuery('.js-redshopb-footable').footable({
							paginate: false,
							sort: false,
							breakpoints: {
								phone: 480,
								tablet: 1024
							}
						});
					});
			}

			$(document).ready(function () {
				ajaxProductsSetup('');
				ajaxCollectionProductsSetup('');

				$('body').on('click', '.btn-add-to-collection', function(){
					newProductAdded = true;
					addToCollectionProducts(this, 0, true);
					return true;
				});
				$('body').on('click', '.btn-remove-from-collection', function(){
					removeFromCollection(this);
					return true;
				});

				if ($("#collection-products-selected").find('input[name="cid[]"]').length > 0){
					newProductAdded = true;
				}

				$('.create-products-next').click(function(){
					if (!newProductAdded) {
						alert(Joomla.JText._('COM_REDSHOPB_NOTHING_SELECTED', 'Nothing Selected'));
						return false;
					}
					$('#adminForm input[name=task]').val('collection.createNext');
					$('#adminForm').submit();
				});
			});
		})(jQuery);
	</script>
<?php endif; ?>
<style>
	#collection-products .js-stools{
		display:none !important;
	}
</style>
<div class="redshopb-collection-create-products">
	<div class="row-fluid">
		<div class="progress progress-striped">
			<div class="bar" style="width: 66%;"></div>
		</div>
	</div>

	<div class="row-fluid">
		<div class="pagination-centered">
			<button class="btn btn-large btn-danger create-products-cancel" onclick="Joomla.submitbutton('collection.createCancel')">
				<i class="icon-double-angle-left"></i>
				<?php echo Text::_('JTOOLBAR_BACK'); ?>
			</button>
			<button class="btn btn-large btn-success create-products-next">
				<?php echo Text::_('JNEXT'); ?>
				<i class="icon-double-angle-right"></i>
			</button>
		</div>
	</div>

	<div class="redshopb-collection-create-products-form">
		<form action="<?php echo $action; ?>" method="post" name="adminForm" id="adminForm"
			  class="form-validate form-horizontal">
			<!-- hidden fields -->
			<input type="hidden" name="jform[department_id]" value="<?php echo $departmentId; ?>">
			<input type="hidden" name="from_company" value="<?php echo $fromCompany ?>">
			<input type="hidden" name="from_collection" value="<?php echo $fromCollection ?>">
			<input type="hidden" name="from_department" value="<?php echo $fromDepartment ?>">
			<input type="hidden" name="id" value="<?php echo $this->item->id; ?>">
			<input type="hidden" name="option" value="com_redshopb">
			<input type="hidden" name="layout" value="create_products">
			<input type="hidden" name="task" value="">

			<div id="collection-products-selected" style="visibility: hidden;">
				<?php if (!empty($this->collectionProducts)) : ?>
					<?php foreach ($this->collectionProducts as $collectionProductId) : ?>
						<input type="hidden" name="cid[]" value="<?php echo $collectionProductId; ?>">
					<?php endforeach; ?>
				<?php endif; ?>
			</div>
			<?php echo HTMLHelper::_('form.token'); ?>
		</form>
	</div>

	<?php if ($this->item->id) : ?>
		<div class="row-fluid accordion">
			<div class="accordion-group">
				<div class="accordion-heading">
					<a
						class="accordion-toggle"
						data-toggle="collapse"
						data-parent="#products"
						href="#products">
						<i class="icon-chevron-down"></i>
						<?php echo Text::_('COM_REDSHOPB_PRODUCT_LIST_TITLE'); ?>
					</a>
				</div>
				<div id="products" class="accordion-body collapse in">
					<div class="container-fluid">
						<div class="row-fluid products-content">
							<div class="spinner pagination-centered">
								<?php echo HTMLHelper::image('media/com_redshopb/images/ajax-loader.gif', '') ?>
							</div>
						</div>
					</div>
				</div>

				<div class="accordion-heading">
					<a
						class="accordion-toggle"
						data-toggle="collapse"
						data-parent="#collection-products"
						href="#collection-products">
						<i class="icon-chevron-down"></i>
						<?php echo Text::_('COM_REDSHOPB_COLLECTION_PRODUCT_LIST_TITLE'); ?>
					</a>
				</div>
				<div id="collection-products" class="accordion-body collapse in">
					<div class="container-fluid">
						<div class="row-fluid collection-products-content">
							<div class="spinner pagination-centered">
								<?php echo HTMLHelper::image('media/com_redshopb/images/ajax-loader.gif', '') ?>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	<?php endif; ?>

	<hr/>
	<div class="row-fluid">
		<div class="pagination-centered">
			<button class="btn btn-large btn-danger create-products-cancel" onclick="Joomla.submitbutton('collection.createCancel')">
				<i class="icon-double-angle-left"></i>
				<?php echo Text::_('JTOOLBAR_BACK'); ?>
			</button>
			<button class="btn btn-large btn-success create-products-next">
				<?php echo Text::_('JNEXT'); ?>
				<i class="icon-double-angle-right"></i>
			</button>
		</div>
	</div>
</div>
