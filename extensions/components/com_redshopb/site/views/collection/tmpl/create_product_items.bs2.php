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
HTMLHelper::_('vnrbootstrap.checkbox');
HTMLHelper::_('rjquery.flexslider', '.flexslider', array('slideshow' => false, 'directionNav' => false, 'animation' => 'slide', 'animationLoop' => false));

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

$doc = Factory::getDocument();
$doc->addScriptDeclaration("
	(function($) {
		$(document).ready(function() {
			$('#create-products-next').click(function(){
				$('#adminForm input[name=task]').val('collection.createNext');
				document.forms['#adminForm'].submit();
			});
		});
	})( jQuery );
"
);
?>
<?php if ($this->item->id) : ?>
	<script type="text/javascript">
		var rsbftTablet = 720;
		var rsbftPhone = 480;
		var fooTableClass = '.js-redshopb-footable';
		var fooTableMonitorBreakpoints = true;

		(function ($) {
			function ajaxProductsSetup() {
				$.ajax({
					url: '<?php echo Uri::root(); ?>index.php?option=com_redshopb&view=collection&task=collection.ajaxcreatecollectionproducts'
						+ '&id=<?php echo $this->item->id ?>'
						+ '&company_id=<?php echo $this->item->company_id ?>'
						+ '&layout=<?php echo Factory::getApplication()->input->getCmd('layout'); ?>'
						+ "&<?php echo Session::getFormToken(); ?>=1",
					type: 'POST',
					data : {
						"<?php echo Session::getFormToken() ?>": 1
					},
					beforeSend: function (xhr) {
						$('.collection-product-items-content .spinner').show();
					}
				}).done(function (data) {
						$('.collection-product-items-content .spinner').hide();
						$('.collection-product-items-content').html(data);
						$('select').chosen();
						$('.chzn-search').hide();
						$('.hasTooltip').tooltip({"animation": true, "html": true, "placement": "top",
							"selector": false, "title": "", "trigger": "hover focus", "delay": 0, "container": false});

						assignSelectRemoveButtons();
						assignAddRemoveButtons();
					});
			}

			function assignAddRemoveButtons() {
				$(".btn-collection-product-variants").each(function (idx, ele) {
					$(ele).unbind('click');
					$(ele).click(function () {
						getCollectionProductItems(ele);
					});
				});
			}

			function getCollectionProductItems(obj) {
				var selectedRow = $(obj).closest('tr');

				if ($(selectedRow).next('tr').hasClass("collection-product-items-box")) {
					$(selectedRow).next('tr').show("slow");
				}
				else {
					var selectedProductId = $(selectedRow).find('input[name="cid[]"]').val();

					$.ajax({
						url: '<?php echo Uri::root(); ?>index.php?option=com_redshopb&view=collection&task=collection.ajaxcreatecollectionproductItems'
							+ '&id=<?php echo $this->item->id ?>'
							+ '&product_id=' + selectedProductId
							+ '&company_id=<?php echo $this->item->company_id ?>'
							+ '&layout=<?php echo Factory::getApplication()->input->getCmd('layout'); ?>'
							+ '&<?php echo Session::getFormToken(); ?>=1',
						type: 'POST',
						data : {
							"<?php echo Session::getFormToken() ?>": 1
						},
						beforeSend: function (xhr) {
							$('.collection-product-items-content .spinner').show();
							$('.collection-product-items-content').addClass('opacity-40');
						}
					}).done(function (data) {
						$('.collection-product-items-content').removeClass('opacity-40');
						$('.collection-product-items-content .spinner').hide();
						$(selectedRow).after('<tr class="collection-product-items-box"><td colspan="7">' + data + '</td></tr>');


						$('select').chosen();
						$('.chzn-search').hide();
						$('.hasTooltip').tooltip({"animation": true, "html": true, "placement": "top",
							"selector": false, "title": "", "trigger": "hover focus", "delay": 0, "container": false});

						initFootableRedshopb();

						$(selectedRow).parent().find('.flexslider').flexslider({
							slideshow : false,
							directionNav : false,
							animation : 'slide',
							animationLoop : false,
							start: function() {
								fooTableRedraw();
							}
						});
					});
				}

				$(obj).unbind('click');
				$(obj).click(function () {
					closeCollectionProductItems(obj);
				});
			}

			function assignSelectRemoveButtons() {
				$('#collection-productList').on('change', '.collection-product-item-on', function () {
					// Split id to get Product ID and Product Item ID
					var $this = $(this),
						entries = $this.attr('id').split('_');

					// Collect all the information to send out
					var product_item_id = entries[2];

					addProductItemInput(product_item_id, $this.attr('checked'));
				});
			}

			function addProductItemInput(product_item_id, isChecked) {
				var productItemInput = false;
				$('#collection-product-items-selected :input').each(function (idx, ele) {
					if ($(ele).val() == product_item_id) {
						productItemInput = ele;
						return false;
					}
				});

				if (isChecked)
				{
					if (productItemInput == false){
						var newProductItem = '<input type="hidden" name="cid[]" value="' + product_item_id + '" />';
						$("#collection-product-items-selected").append(newProductItem);

						$('#jform_productitem_' + product_item_id).closest('label');
					}
				}
				else
				{
					if (productItemInput != false) {
						$('#jform_productitem_' + product_item_id).closest('label');
						$(productItemInput).remove();
					}
				}
			}

			function closeCollectionProductItems(obj) {
				var selectedRow = $(obj).closest('tr').next('tr');
				selectedRow.hide("slow");

				$(obj).unbind('click');
				$(obj).click(function () {
					getCollectionProductItems(obj);
				});
			}


			$(document).ready(function () {
				ajaxProductsSetup();

				$('.collection-product-items-content').on('click', 'input.check-all-variants-in-column', function(){
					// We are in Footable
					if ($(this).parent().prop('tagName') == 'DIV')
					{
						var divId = $(this).parent().parent().index();

						$(this).closest('table')
							.find('div > div:nth-child(' + (divId + 1) + ')')
							.find('input[type=checkbox]')
							.not(this)
							.prop("checked", this.checked)
							.change();
					}
					else
					{
						var tdId = $(this).closest('td').index();

						$(this).closest('table')
							.find('tr > td:nth-child(' + (tdId + 1) + ')')
							.find('input[type=checkbox]')
							.not(this)
							.prop("checked", this.checked)
							.change();
					}

					return true;
				});

				$('.collection-product-items-content').on('click', 'input.check-all-variants-in-row', function(){
					// We are in Footable
					if ($(this).parent().prop('tagName') == 'DIV')
					{
						$(this).closest('tr')
							.prev()
							.find('input[type=checkbox]')
							.not(this)
							.prop("checked", this.checked)
							.change();
					}

					$(this).closest('tr')
						.find('input[type=checkbox]')
						.not(this)
						.prop("checked", this.checked)
						.change();
					return true;
				});
			});
		})(jQuery);
	</script>
<?php endif; ?>

<div class="redshopb-collection-create-productitems">
	<div class="row-fluid">
		<div class="progress progress-success">
			<div class="bar" style="width: 100%;"></div>
		</div>
	</div>

	<div class="row-fluid">
		<div class="pagination-centered">
			<button class="btn btn-large btn-danger" onclick="Joomla.submitbutton('collection.createCancel')">
				<i class="icon-double-angle-left"></i>
				<?php echo Text::_('JTOOLBAR_BACK'); ?>
			</button>
			<button class="btn btn-large btn-success" onclick="Joomla.submitbutton('collection.createNext')">
				<?php echo Text::_('JEND'); ?>
				<i class="icon-double-angle-right"></i>
			</button>
		</div>
	</div>

	<h3><?php echo Text::_('COM_REDSHOPB_COLLECTION_PRODUCT_LIST_ITEMS_TITLE'); ?></h3>

	<div id="collection-product-items">
		<div class="row-fluid collection-product-items-content">
			<div class="spinner pagination-centered">
				<?php echo HTMLHelper::image('media/com_redshopb/images/ajax-loader.gif', '') ?>
			</div>
		</div>
	</div>

	<div class="redshopb-collection-create-productitems-form">
		<form action="<?php echo $action; ?>" method="post" name="adminForm" id="adminForm" class="form-validate form-horizontal">
			<!-- hidden fields -->
			<input type="hidden" name="jform[department_id]" value="<?php echo $departmentId; ?>">
			<input type="hidden" name="from_company" value="<?php echo $fromCompany ?>">
			<input type="hidden" name="from_collection" value="<?php echo $fromCollection ?>">
			<input type="hidden" name="from_department" value="<?php echo $fromDepartment ?>">
			<input type="hidden" name="id" value="<?php echo $this->item->id; ?>">
			<input type="hidden" name="option" value="com_redshopb">
			<input type="hidden" name="layout" value="create_product_items">
			<input type="hidden" name="task" value="">

			<div id="collection-product-items-selected" style="visibility: hidden;">
				<?php if (!empty($this->collectionProductItems)) : ?>
					<?php foreach ($this->collectionProductItems as $collectionProductItem) : ?>
						<input type="hidden" name="cid[]" value="<?php echo $collectionProductItem->product_item_id; ?>">
					<?php endforeach; ?>
				<?php endif; ?>
			</div>
			<?php echo HTMLHelper::_('form.token'); ?>
		</form>
	</div>

	<hr/>

	<div class="row-fluid">
		<div class="pagination-centered">
			<button class="btn btn-large btn-danger" onclick="Joomla.submitbutton('collection.createCancel')">
				<i class="icon-double-angle-left"></i>
				<?php echo Text::_('JTOOLBAR_BACK'); ?>
			</button>
			<button class="btn btn-large btn-success" onclick="Joomla.submitbutton('collection.createNext')">
				<?php echo Text::_('JEND'); ?>
				<i class="icon-double-angle-right"></i>
			</button>
		</div>
</div>
