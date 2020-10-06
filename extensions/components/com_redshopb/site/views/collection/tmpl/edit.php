<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Views
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;
RHelperAsset::load('collection.css', 'com_redshopb');

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Session\Session;

// HTML helpers
HTMLHelper::_('rjquery.framework');
HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('vnrbootstrap.tooltip');
HTMLHelper::_('rjquery.chosen', 'select');
HTMLHelper::_('rsearchtools.main');
HTMLHelper::_('rsortablelist.main');

RedshopbHtml::loadFooTable();

$url = 'index.php?option=com_redshopb&view=collection';

$return = Factory::getApplication()->input->getBase64('return');

if ($return)
{
	$url .= '&return=' . $return;
}

$action = RedshopbRoute::_($url);

$companyId      = $this->item->company_id;
$fromCompany    = $this->item->fromCompany;
$fromProduct    = $this->item->fromProduct;
$productId      = $this->state->get('collection.productId');
$fromCollection = $this->item->fromCollection;
$departmentId   = reset($this->item->department_ids);
$fromDepartment = $this->item->fromDepartment;
$isNew          = (int) $this->item->id <= 0;

$tab = Factory::getApplication()->input->getString('tab', 'createcollectionproducts');

// Can't be a tab that doesn't exist on this page
if ($tab != 'createcollectionproducts' && $tab != 'prices')
{
	$tab = 'createcollectionproducts';
}

$productIdUri = Factory::getApplication()->input->getString('product_id', '');

echo RedshopbBrowserBreadcrumbs::renderBreadcrumbs();
?>
<script type="text/javascript">
	var loadedProductTabs = {};
	(function ($) {
		function ajaxProductTabSetup(tabName) {
			$('a[href="#' + tabName + '"]').on('click', function (e) {
				ajaxExecute(tabName);
			})
		}

		function ajaxExecute(tabName, forced) {
			// Tab already loaded
			if (loadedProductTabs[tabName] == true && !forced) {
				return true;
			}

			// Perform the ajax request
			$.ajax({
				url: '<?php echo Uri::root(); ?>index.php?option=com_redshopb&view=collection&task=collection.ajax' + tabName
					+ '&id=<?php echo $this->item->id ?>'
					+ '&company_id=<?php echo $this->item->company_id ?>'
					+ '&layout=<?php echo Factory::getApplication()->input->getCmd('layout'); ?>',
				dataType: 'text',
				type : 'post',
				data : {
					"<?php echo Session::getFormToken() ?>": 1
				},
				beforeSend: function (xhr) {
					$('.' + tabName + '-content .spinner').show();
					$('.' + tabName + '-content').addClass('opacity-40');
				}
			}).done(function (data) {
					$('.' + tabName + '-content .spinner').hide();
					$('.' + tabName + '-content').removeClass('opacity-40');
					$('.' + tabName + '-content').html(data);
					$('select').chosen();
					$('.chzn-search').hide();
					$('.hasTooltip').tooltip({"animation": true, "html": true, "placement": "top",
						"selector": false, "title": "", "trigger": "hover focus", "delay": 0, "container": false});
					loadedProductTabs[tabName] = true;

					// init footable for the tab
					$('.js-redshopb-footable').footable({
						paginate: false,
						sort: false,
						breakpoints: {
							phone: 480,
							tablet: 1024
						}
					});

					if (tabName == 'prices')
					{
						<?php if (!empty($productIdUri)) : ?>
						$('.btn-price-productid-' + <?php echo $productIdUri ?>).click();
						<?php endif; ?>
					}

				});
		}

		function getProductInputBox(selectedId)
		{
			var productInputBox = false;
			$('#collection-products-selected :input').each(function (idx, ele) {
				if ($(ele).val() == selectedId) {
					productInputBox = ele;
				}
			});

			return productInputBox;
		}

		function removeFromCollection(obj) {
				var selectedRow = $(obj).closest('tr');
				var selectedId = $(obj).closest('td').find('input:checkbox').val();
				var collectionId = $('input[name="id"]').val();

				if (selectedId != undefined && collectionId != undefined)
				{
					$.ajax({
						url: '<?php echo Uri::root(); ?>index.php?option=com_redshopb&task=collection.ajaxRemoveProduct',
						type: 'post',
						data: 'product_id='+selectedId+'&collection_id='+collectionId+'&<?php echo Session::getFormToken(); ?>=1',
						dataType: 'json'
					}).done(function (data) {
						$(obj).hide();
						selectedRow.detach();
						selectedRow.fadeOut();
					});
				}
			}

		function getPrices(obj) {
			var selectedRow = $(obj).closest('tr');
			var selectedId = $(obj).closest('td').find('input:checkbox').val();
			var pricesLoaded = '.productid-prices-row' + selectedId;

			if ($(pricesLoaded).length > 0)
			{
				$(pricesLoaded).remove();
			}
			else
			{
				$.ajax({
					url: '<?php echo Uri::root(); ?>index.php?option=com_redshopb&view=collection&task=collection.ajaxcollectionprices'
						+ '&id=<?php echo $this->item->id ?>'
						+ '&product_id=' + selectedId
						+ '&company_id=<?php echo $this->item->company_id ?>'
						+ '&layout=<?php echo Factory::getApplication()->input->getCmd('layout'); ?>',
					type: 'post',
					data : {
						"<?php echo Session::getFormToken() ?>": 1
					},
					beforeSend: function (xhr) {
						$(selectedRow).after('<tr class="collection-product-items-box productid-prices-row' + selectedId + '"><td colspan="7"><div class="spinner pagination-centered"><?php echo HTMLHelper::image('media/com_redshopb/images/ajax-loader.gif', '') ?></div></td></tr>');
					}
				}).done(function (data) {
					$('.productid-prices-row' + selectedId + ' td').html(data);

					$('select').chosen();
					$('.chzn-search').hide();
					$('.hasTooltip').tooltip({"animation": true, "html": true, "placement": "top",
						"selector": false, "title": "", "trigger": "hover focus", "delay": 0, "container": false});

					// init footable for the tab
					jQuery('.js-redshopb-footable-variants').footable({
						paginate: false,
						sort: false,
						breakpoints: {
							phone: 480,
							tablet: 1024
						}
					});

				});
			}
		}

		$(document).ready(function () {
			// Show the corresponding tab
			$('.nav-tabs a[href="#<?php echo $tab ?>"]').tab('show').click();

			ajaxExecute('<?php echo $tab ?>');
			ajaxProductTabSetup('createcollectionproducts');
			ajaxProductTabSetup('prices');

			$('body').on('click', '.btn-remove-from-collection', function(){
				removeFromCollection(this);
				return true;
			}).on('click', '.btn-show-prices', function(){
				getPrices(this);
				return true;
			});
		});
	})(jQuery);
</script>
<div class="redshopb-collection">
	<form action="<?php echo $action; ?>" method="post" name="adminForm" id="adminForm" class="form-horizontal redshopb-collection-form">
		<div class="form-horizontal collection-main-information">
			<div class="form-group">
				<div class="col-sm-2 control-label horizontal-controls">
					<?php echo $this->form->getLabel('name'); ?>
				</div>
				<div class="col-sm-10 control horizontal-controls">
					<?php echo $this->form->getInput('name'); ?>
				</div>
			</div>
			<div class="clearfix"></div>

			<?php if ($fromCompany && $companyId) : ?>
				<div class="form-group">
					<div class="col-sm-2 control-label horizontal-controls">
						<?php echo $this->form->getLabel('company_id'); ?>
					</div>
					<div class="col-sm-10 control horizontal-controls">
						<input type="hidden" name="jform[company_id]" value="<?php echo $companyId; ?>">
						<?php echo $this->item->company->name; ?>
					</div>
				</div>
			<?php elseif ($fromProduct && $productId): ?>
				<input type="hidden" name="from_product" value="<?php echo $fromProduct ?>">
				<input type="hidden" name="jform[product_id]" value="<?php echo $productId; ?>">
			<?php else : ?>
				<div class="form-group">
					<div class="col-sm-2 control-label horizontal-controls">
						<?php echo $this->form->getLabel('company_id'); ?>
					</div>
					<div class="col-sm-10 control horizontal-controls">
						<?php echo RedshopbEntityCompany::getInstance($this->form->getValue('company_id'))->get('name'); ?>
					</div>
				</div>
			<?php endif; ?>
			<div class="clearfix"></div>

			<?php if ($this->item->currency->id): ?>
			<div class="form-group">
				<div class="col-sm-2 control-label horizontal-controls">
					<?php echo $this->form->getLabel('currency_id'); ?>
				</div>
				<div class="col-sm-10 control horizontal-controls">
					<?php echo $this->item->currency->name . ' (' . $this->item->currency->alpha3 . ')'; ?>
				</div>
			</div>
			<?php else: ?>
			<div class="form-group">
				<div class="col-sm-2 control-label horizontal-controls">
					<?php echo $this->form->getLabel('currency_id'); ?>
				</div>
				<div class="col-sm-10 control horizontal-controls">
					<?php echo $this->form->getInput('currency_id'); ?>
				</div>
			</div>
			<?php endif; ?>
			<div class="clearfix"></div>

			<div class="form-group">
				<div class="col-sm-2 control-label horizontal-controls">
					<?php echo $this->form->getLabel('department_ids'); ?>
				</div>
				<div class="col-sm-10 control horizontal-controls">
					<?php echo $this->form->getInput('department_ids'); ?>
				</div>
			</div>
			<div class="clearfix"></div>

			<div class="form-group">
				<div class="col-sm-2 control-label horizontal-controls">
					<?php echo $this->form->getLabel('state'); ?>
				</div>
				<div class="col-sm-10 control horizontal-controls">
					<?php echo $this->form->getInput('state'); ?>
				</div>
			</div>
			<div class="clearfix"></div>
		</div>

		<!-- hidden fields -->
		<input type="hidden" name="jform[department_id]" value="<?php echo $departmentId; ?>">
		<input type="hidden" name="from_collection" value="<?php echo $fromCollection ?>">
		<input type="hidden" name="from_company" value="<?php echo $fromCompany ?>">
		<input type="hidden" name="jform[company_id]" value="<?php echo $this->item->company_id; ?>">

		<?php if ($this->item->currency->id): ?>
		<input type="hidden" name="jform[currency_id]" value="<?php echo $this->item->currency_id; ?>">
		<?php endif; ?>
		<input type="hidden" name="from_department" value="<?php echo $fromDepartment ?>">
		<input type="hidden" name="option" value="com_redshopb">
		<input type="hidden" name="id" value="<?php echo $this->item->id; ?>">
		<input type="hidden" name="task" value="">
		<?php echo HTMLHelper::_('form.token'); ?>
	</form>
	<div class="redshopb-collection-data">
		<ul class="nav nav-tabs">
			<li class="active">
				<a href="#createcollectionproducts" data-toggle="tab"><?php echo Text::_('COM_REDSHOPB_PRODUCT_LIST_TITLE'); ?></a>
			</li>
			<li>
				<a href="#prices" data-toggle="tab"><?php echo Text::_('COM_REDSHOPB_PRICES'); ?></a>
			</li>
		</ul>
		<div class="tab-content">
			<div class="tab-pane active" id="createcollectionproducts">
				<div class="row createcollectionproducts-content">
					<div class="spinner pagination-centered">
						<?php echo HTMLHelper::image('media/com_redshopb/images/ajax-loader.gif', '') ?>
					</div>
				</div>
			</div>
			<div class="tab-pane" id="combinations">
				<div class="row combinations-content">
					<div class="spinner pagination-centered">
						<?php echo HTMLHelper::image('media/com_redshopb/images/ajax-loader.gif', '') ?>
					</div>
				</div>
			</div>
			<div class="tab-pane" id="prices">
				<div class="row prices-content">
					<div class="spinner pagination-centered">
						<?php echo HTMLHelper::image('media/com_redshopb/images/ajax-loader.gif', '') ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
