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
use Joomla\CMS\Input\Input;

$level = 0;
/** @var Input $jInput */
$jInput = Factory::getApplication()->input;

$returnUrl = null;
$return    = $jInput->getBase64('return', null);

if (!empty($return))
{
	$returnUrl = '&return=' . $return;
}

$menu                 = '';
$listOrder            = $this->state->get('list.ordering');
$listDirn             = $this->state->get('list.direction');
$i                    = 0;
$tab                  = $jInput->getString('tab');
$userRole             = RedshopbHelperUser::getUserRole();
$companiesPerPage     = RedshopbApp::getConfig()->get('shop_companies_per_page', 12);
$companiesPagesCount  = ceil($this->companiesCount / $companiesPerPage);
$departmentsPerPage   = RedshopbApp::getConfig()->get('shop_departments_per_page', 12);
$departmentsPageCount = ceil($this->departmentsCount / $departmentsPerPage);
$employeesPerPage     = RedshopbApp::getConfig()->get('shop_employees_per_page', 12);
$employeesPageCount   = ceil($this->employeesCount / $employeesPerPage);
$categoriesPerPage    = RedshopbApp::getConfig()->get('shop_categories_per_page', 12);
$categoriesPagesCount = ceil($this->categoriesCount / $categoriesPerPage);
$showPrintButton      = RedshopbApp::getConfig()->get('show_products_print', 0) > 0;
$showShopAs           = RedshopbApp::getConfig()->get('show_shop_as', 'categories');

$flexsliderOptions    = array('slideshow' => false, 'directionNav' => false, 'animation' => 'slide', 'animationLoop' => false);
$flexsliderOptionsReg = RedshopbHelperShop::options2Jregistry($flexsliderOptions);

HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('rdropdown.init');
HTMLHelper::_('vnrbootstrap.tooltip');
HTMLHelper::_('vnrbootstrap.checkbox');
HTMLHelper::_('rjquery.chosen', '.chosenSelect, .productListContainer select');
HTMLHelper::_('rjquery.flexslider', '.flexslider', $flexsliderOptions);
HTMLHelper::_('rholder.holder');

if ($showPrintButton) :
	RHtml::_('vnrbootstrap.modal', 'productsListModal');
endif;

RHelperAsset::load('shop.css', 'com_redshopb');
RHelperAsset::load('cloudzoom.css', 'com_redshopb');
RHelperAsset::load('cloudzoom.js', 'com_redshopb');
RHelperAsset::load('lib/bootstrap-multiselect.js', 'com_redshopb');
RHelperAsset::load('lib/bootstrap-multiselect.css', 'com_redshopb');
?>

<script type="text/javascript">
	var rsbftTablet = 768;
	var rsbftPhone = 480;
	var fooTableClass = '.variants-table';
	var fooTableMonitorBreakpoints = true;
</script>

<?php
RedshopbHtml::loadFooTable();

// Render a breadcrumb.
$orderId = Factory::getApplication()->getUserState('checkout.orderId', 0);

if ((int) $orderId == 0)
{
	echo RedshopbBrowserBreadcrumbs::renderBreadcrumbs();
}

Factory::getDocument()->addScriptDeclaration(
	"
		(function($){
			$(document).ready(function () {
				$('.tree-toggle').click(function (e) {
					e.preventDefault();
				});
				$('#clearFilterShopName').click(function() {
					$('#filterShopName').val('');
					$('.formFilterShop').submit();
				});
			});
		})(jQuery);
	"
);
?>
<div class="redshopb-shop">
	<?php
	// Check if customer is selected
	if ($this->customerId == 0 || $this->customerType == ''):
		?>
		<script type="text/javascript">
			function JAjaxCompaniesPageUpdate(event)
			{
				var targ = redSHOPB.form.getButtonTarget(event, true);
				var page = targ.attr('data-page');
				var pageTotal = targ.attr('data-page_total');
				var form = targ.closest('form');
				redSHOPB.form.getInput('page', form).val(page);
				redSHOPB.form.getInput('noPages', form).val(pageTotal);
				var $companies = jQuery('#pageCompanies');

				jQuery.ajax({
					url: form.attr('action'),
					type: 'post',
					data: redSHOPB.form.getData(form, 'shop.ajaxGetCompaniesPage'),
					beforeSend: function (xhr) {
						<?php if (RedshopbApp::isUseAjaxReadMorePagination()): ?>
						$companies.append('<div class="spinner pagination-centered"><?php echo HTMLHelper::image('media/com_redshopb/images/ajax-loader.gif', '') ?></div>');
						<?php else: ?>
						$companies.data('<div class="spinner pagination-centered"><?php echo HTMLHelper::image('media/com_redshopb/images/ajax-loader.gif', '') ?></div>');
						<?php endif; ?>
					}
				}).done(function (data) {
					<?php if (RedshopbApp::isUseAjaxReadMorePagination()): ?>
					$companies.find(".spinner.pagination-centered").remove();
					$companies.find("#redshopbPaginationLoadMore").remove();
					$companies.append(data);
					<?php else: ?>
					$companies.html(data);
					<?php endif; ?>
				});
			}

			function JAjaxDepartmentsPageUpdate(event)
			{
				var targ = redSHOPB.form.getButtonTarget(event, true);
				var page = targ.attr('data-page');
				var pageTotal = targ.attr('data-page_total');
				var form = targ.closest('form');
				redSHOPB.form.getInput('page', form).val(page);
				redSHOPB.form.getInput('noPages', form).val(pageTotal);
				var $departments = jQuery('#pageDepartments');

				jQuery.ajax({
					url: form.attr('action'),
					type: 'post',
					data: redSHOPB.form.getData(form, 'shop.ajaxGetDepartmentsPage'),
					beforeSend: function (xhr) {
						<?php if (RedshopbApp::isUseAjaxReadMorePagination()): ?>
						$departments.append('<div class="spinner pagination-centered"><?php echo HTMLHelper::image('media/com_redshopb/images/ajax-loader.gif', '') ?></div>');
						<?php else: ?>
						$departments.data('<div class="spinner pagination-centered"><?php echo HTMLHelper::image('media/com_redshopb/images/ajax-loader.gif', '') ?></div>');
						<?php endif; ?>
					}
				}).done(function (data) {
					<?php if (RedshopbApp::isUseAjaxReadMorePagination()): ?>
					$departments.find(".spinner.pagination-centered").remove();
					$departments.find("#redshopbPaginationLoadMore").remove();
					$departments.append(data);
					<?php else: ?>
					$departments.html(data);
					<?php endif; ?>
				});
			}

			function JAjaxEmployeesPageUpdate(event)
			{
				var targ = redSHOPB.form.getButtonTarget(event, true);
				var page = targ.attr('data-page');
				var pageTotal = targ.attr('data-page_total');
				var form = targ.closest('form');
				redSHOPB.form.getInput('page', form).val(page);
				redSHOPB.form.getInput('noPages', form).val(pageTotal);
				var $employees = jQuery("#pageEmployees");
				jQuery.ajax({
					url: form.attr('action'),
					type: 'post',
					data: redSHOPB.form.getData(form, 'shop.ajaxGetEmployeesPage'),
					beforeSend: function (xhr) {
						<?php if (RedshopbApp::isUseAjaxReadMorePagination()): ?>
						$employees.append('<div class="spinner pagination-centered"><?php echo HTMLHelper::image('media/com_redshopb/images/ajax-loader.gif', '') ?></div>');
						<?php else: ?>
						$employees.data('<div class="spinner pagination-centered"><?php echo HTMLHelper::image('media/com_redshopb/images/ajax-loader.gif', '') ?></div>');
						<?php endif; ?>
					}
				}).done(function (data) {
					<?php if (RedshopbApp::isUseAjaxReadMorePagination()): ?>
					$employees.find(".spinner.pagination-centered").remove();
					$employees.find("#redshopbPaginationLoadMore").remove();
					$employees.append(data);
					<?php else: ?>
					$employees.html(data);
					<?php endif; ?>
				});
			}
		</script>

		<form action="<?php echo RedshopbRoute::_('index.php?option=com_redshopb&view=shop&tab=' . $tab); ?>" name="adminForm" class="adminForm formFilterShop" id="adminForm" method="post">
			<div class="row-fluid">
				<div class="span4">
					<div class="well well-small">
						<div class="input-append shopsearch">
							<input type="text" id="filterShopName" name="filter[shopname]" class="form-control input-medium" value="<?php echo $this->filterShopName; ?>">
							<input type="hidden" name="company_id" value="<?php echo $this->companyId; ?>"/>
							<input type="hidden" name="department_id" value="<?php echo $this->departmentId; ?>"/>
							<span class="input-group-btn">
								<button class="btn btn-default hasTooltip" type="submit" title="<?php echo RHtml::tooltipText('JSEARCH_FILTER_SUBMIT'); ?>"><i class="icon icon-search"></i></button>
								<button type="button" id="clearFilterShopName" class="btn hasTooltip" title="<?php echo RHtml::tooltipText('JSEARCH_FILTER_CLEAR'); ?>">
									<?php echo Text::_('JSEARCH_FILTER_CLEAR');?>
								</button>
							</span>
						</div>
					</div>
					<?php if (!is_null($this->vendor)): ?>
						<div class="well well-small">
							<h4>
								<i class="icon-briefcase"></i>&nbsp;<?php echo Text::_('COM_REDSHOPB_SHOP_OVERALL_VENDOR') . ': ' . $this->vendor->name; ?>
							</h4>
							<?php if (RedshopbEntityConfig::getInstance()->get('vendor_of_companies', 'parent') == 'parent'): ?>
								<p class="text-right">
									<a class="btn btn-small btn-danger" href="#" onclick="Joomla.submitform('shop.vendorunset', document.getElementById('adminForm'));">
										<i class="icon-remove"></i>
										<?php echo Text::_('COM_REDSHOPB_SHOP_CHANGE_VENDOR'); ?>
									</a>
								</p>
							<?php endif; ?>

							<h4>
								<i class="icon-shopping-cart"></i>&nbsp;
								<?php echo Text::_('COM_REDSHOPB_SHOP_SHOPPERS'); ?>
							</h4>

							<?php foreach ($this->shopCustomers as $shopCustomer) : ?>

								<?php
								$route  = 'index.php?option=com_redshopb&task=shop.sob' . $shopCustomer->type;
								$route .= '&company_id=' . $shopCustomer->company_id;
								$route .= '&department_id=' . $shopCustomer->department_id;
								$route .= '&rsbuser_id=' . $shopCustomer->user_id;
								?>

								<p>
									<?php echo $shopCustomer->name; ?>
									<span class="pull-right">
										<a class="btn btn-link" href="<?php echo RedshopbRoute::_($route . $returnUrl); ?>">
											<i class="icon-shopping-cart"></i>
											<?php echo Text::_('COM_REDSHOPB_SHOP') ?>
										</a>
									</span>
								</p>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>
					<?php echo RedshopbLayoutHelper::render('menu.collapse', array('menu' => $this->menu), null, array('debug' => false)); ?>
				</div>
				<div class="span8">
					<?php if (!empty($this->companies)): ?>
						<!-- Show all companies under given company -->
						<div class="customer-container">
							<div class="customer-header">
								<i class="icon-globe icon-2x"></i>
								<h3><?php echo Text::_('COM_REDSHOPB_COMPANIES'); ?></h3>
							</div>
							<div class="customer-list" id="pageCompanies">
								<?php echo RedshopbLayoutHelper::render(
									'shop.pages.companies',
									array(
										'companies'           => $this->companies,
										'currentCompanyId'    => $this->companyId,
										'showPagination'      => true,
										'numberOfPages'       => $companiesPagesCount,
										'currentPage'         => 1,
										'ajaxJS'              => 'JAjaxCompaniesPageUpdate(event)',
										"subCompaniesCount"   => $this->subCompaniesCount,
										"subDepartmentsCount" => $this->subDepartmentsCount,
										"subEmployeesCount"   => $this->subEmployeesCount,
										"returnUrl"           => $returnUrl
									)
								);?>
							</div>
						</div>
					<?php endif; ?>

					<?php if (!empty($this->departments)): ?>
						<!-- Show all departments under given company -->
						<div class="customer-container">
							<div class="customer-header">
								<i class="icon-building icon-2x"></i>
								<h3><?php echo Text::_('COM_REDSHOPB_DEPARTMENTS'); ?></h3>
							</div>
							<div class="customer-list" id="pageDepartments">
								<?php echo RedshopbLayoutHelper::render(
									'shop.pages.departments',
									array(
										'departments'         => $this->departments,
										'currentDepartmentId' => $this->departmentId,
										'showPagination'      => true,
										'numberOfPages'       => $departmentsPageCount,
										'currentPage'         => 1,
										'ajaxJS'              => 'JAjaxDepartmentsPageUpdate(event)',
										"returnUrl"           => $returnUrl
									)
								);?>
							</div>
						</div>
					<?php endif; ?>

					<?php if (!empty($this->employees)): ?>
						<!-- Show all employees for given company/department -->
						<div class="customer-container">
							<div class="customer-header">
								<i class="icon-user icon-2x"></i>
								<h3><?php echo Text::_('COM_REDSHOPB_EMPLOYEES'); ?></h3>
							</div>
							<div class="customer-list" id="pageEmployees">
								<?php echo RedshopbLayoutHelper::render(
									'shop.pages.employees',
									array(
										'employees'         => $this->employees,
										'currentEmployeeId' => $this->userRSid,
										'showPagination'    => true,
										'numberOfPages'     => $employeesPageCount,
										'currentPage'       => 1,
										'ajaxJS'            => 'JAjaxEmployeesPageUpdate(event)',
										"returnUrl"           => $returnUrl
									)
								);?>
							</div>
						</div>
						<div class="pagination pagination-toolbar clearfix" style="text-align: center;">
							<ul class="pagination-list">
							</ul>
						</div>
					<?php endif; ?>
				</div>
			</div>
			<input type="hidden" name="task" value="">
			<input type="hidden" name="boxchecked" value="0">
			<?php echo HTMLHelper::_('form.token'); ?>
		</form>
	<?php
	// Collection layout "show as"
	else:
	?>
		<div class="row-fluid">
			<div class="span12">
				<form action="<?php echo RedshopbRoute::_('index.php?option=com_redshopb&view=shop&tab=' . $tab); ?>" name="adminForm" class="adminForm filter-form-shop" id="adminForm" method="post">
					<script type="text/javascript">
						function setWashCareModal() {
							var popoverInit = false;

							jQuery('.washCareLink').on('click', function () {
								popoverInit = false;
							});

							jQuery('.myModal')
								.on('hidden.bs.modal', function () {
									jQuery(this).removeData('modal');
								})
								.on('show.bs.modal', function () {
									if (popoverInit == false) {
										jQuery(this).find('.modal-body').html('<div class="spinner pagination-centered"><?php echo HTMLHelper::image('media/com_redshopb/images/ajax-loader.gif', '') ?></div>');
									}
								})
						}

						function initHideItemsRow(){
							var rows = jQuery('.items-row');
							rows.each(function(ir,er) {
								var row = jQuery(er);
								var showRow = true;
								var columns = row.find('.item-column');
								if (columns.length == 0)
								{
									showRow = false;
								}
								if (!showRow)
								{
									row.css('display', 'none');
								}
							});
						}

						(function ($) {
							$(document).ready(function () {

								var timerID = null;
								$(window).on('resize', function(){
									timerID && clearTimeout(timerID);
									timerID = setTimeout(function() {
										modalResize();
									},500);
								});

								function modalResize(){
									var windowWidth = $(window).width(),
										$myModal = $('#myModal');
									if (windowWidth > 1310)
									{
										$myModal.addClass('redshopb-wash-big');
									}else{
										$myModal.removeClass('redshopb-wash-big');
									}
								}

								modalResize();

								$('.productList').on('change', '.dropDownAttribute', function () {
									var $this = $(this),
										parameters = $this.attr('id').split('_'),
										collectionid = $this.data('collection'),
										currencyid = $this.data('currency'),
										dropDownSelected = $this.val(),
										$tableVariants = $('#tableVariants_' + collectionid + '_' + parameters[1]),
										$productThumbs = $('#productThumbs_' + collectionid + '_' + parameters[1]),
										$accessory = $('#accessory_' + collectionid + '_' + parameters[1]),
										tableVariantsHeight = $tableVariants.height(),
										productThumbsHeight = $productThumbs.height();

									$.ajax({
										url: '<?php echo Uri::root(); ?>index.php?option=com_redshopb&task=shop.ajaxChangeDropDownAttribute'
										+ '&product_id=' + parameters[1]
										+ '&drop_down_selected=' + dropDownSelected
										+ '&collection_id=' + collectionid
										+ '&currency_id=' + currencyid,
										type: 'POST',
										data : {
											"<?php echo Session::getFormToken() ?>": 1
										},
										beforeSend: function () {
											$tableVariants.empty().html(
												'<div class="spinner pagination-centered"><?php echo HTMLHelper::image('media/com_redshopb/images/ajax-loader.gif', '') ?></div>'
											).find('.spinner').height(tableVariantsHeight);
											$productThumbs.empty().html(
												'<div class="spinner pagination-centered"><?php echo HTMLHelper::image('media/com_redshopb/images/ajax-loader.gif', '') ?></div>'
											).find('.spinner').height(productThumbsHeight);
										}
									}).done(function (data) {
										$tableVariants.html(data);
										$productThumbs.empty();
										var sliderImage = '';
										if ($('#productImages').length > 0) {
											$('#productImages .flexslider').appendTo($productThumbs);
											$('#productImages').remove();
											sliderImage = ', #flexsliderImage_' + parameters[1] + '_' + parameters[2];
											$productThumbs.removeClass('hide');
											Holder.run();
											$('.flexslider').flexslider(<?php echo $flexsliderOptionsReg->toString() ?>);
										}
										else {
											$productThumbs.addClass('hide');
										}
										if ($('#washCareLink').length > 0) {
											$('#washCareLink_' + parameters[1] + '_' + collectionid).attr('href', $('#washCareLink').text());
											$('#washCareLink').remove();
										}
										$accessory.empty();
										if ($('#divAccessory').length > 0) {
											$('#divAccessory .dropDownAccessory').appendTo($accessory);
											$('#divAccessory').remove();
											var currentAccessory = $('#dropDownAccessory_' + parameters[1]);
											currentAccessory.multiselect({'nonSelectedText': currentAccessory.find('optgroup:first').attr('label')});
										}

										initFootableRedshopb();
										initHideItemsRow();
										$('.carousel-variants').bind('slid', function () {
											fooTableRedraw();
											initHideItemsRow();
										});
									});
								});

								initHideItemsRow();
								$('.carousel-variants').bind('slid', function () {
									fooTableRedraw();
									initHideItemsRow();
								});

								$('.productList').on('click', '.clearAmounts', function () {
									var $this = $(this),
										parameters = $this.attr('id').split('_'),
										$tableVariants = $('#tableVariants_' + parameters[1] + '_' + parameters[2]);
									$tableVariants.find('.amountInput').val('');
								})

								$('input.checkbox-onsale').checkbox({
									buttonStyle: 'btn-primary',
									buttonStyleChecked: 'btn-warning',
									checkedClass: 'icon-check',
									uncheckedClass: 'icon-check-empty'
								});

								$('.flexslider').flexslider(<?php echo $flexsliderOptionsReg->toString() ?>);
								$('a[data-toggle="tab"]').on('shown', function (e) {
									$(window).trigger('resize');
								})
								setWashCareModal();

								$('.js-stools-container-filters select').each(function(){$(this).multiselect({'nonSelectedText': $(this).find('optgroup:first').attr('label'), 'maxHeight' : 200})});

								<?php if (empty($this->collections)) :?>
								var parent = $('#filter_attribute_flat_display').parent();
								$.ajax({
									url: '<?php echo Uri::root(); ?>index.php?option=com_redshopb&task=shop.ajaxRefreshDropDowns',
									dataType: 'html',
									type: 'POST',
									beforeSend: function()
									{
										$('#filter_attribute_flat_display').remove();
										parent.html('<div class="spinner pagination-centered"><?php echo HTMLHelper::image('media/com_redshopb/images/ajax-loader.gif', '');?></div>');
									}
								}).done(function(data)
								{
									parent.hide();
									parent.html(data);
									$('#filter_attribute_flat_display-lbl').remove();
									$('#filter_attribute_flat_display').multiselect({'nonSelectedText': $(this).find('optgroup:first').attr('label'), 'maxHeight' : 200});
									parent.show();
								});
								<?php endif;?>
								<?php
								if ($showPrintButton) :?>
								var originalSubmit = Joomla.submitbutton;
								Joomla.submitbutton = function (task) {
									if (task == 'printProductsList')
									{
										jQuery.ajax({
											url: '<?php echo Uri::root(); ?>index.php?option=com_redshopb&task=shop.ajaxSetUpPrintList',
											cache: false,
											type: 'post',
											dataType: 'json'
										}).done(function (data) {
											if (data.showModal == 1)
											{
												jQuery('#ajaxModal').html(data.html);
												jQuery('#productsListModal').modal('show');
												jQuery('#list_currency').chosen();
												jQuery('#list_currency-lbl').tooltip();
												jQuery('#list_language').chosen();
												jQuery('#list_language-lbl').tooltip();
												return false;
											}
											else
											{
												jQuery('#addInputs').html(data.html);
												JPrintProductList(false);
											}
										});
									}
									else
									{
										originalSubmit(task);
									}
								};
								jQuery('#printList').on('click', function(){
									JPrintProductList(true);
								});
								<?php endif; ?>
							});
						})(jQuery);

						<?php if ($showPrintButton) :?>
						function JPrintProductList(applyFilters)
						{
							var currency = jQuery("#list_currency").val();
							var language = jQuery("#list_language").val();
							var onSale = (jQuery('#filter_onsale').attr('checked') == 'checked') ? 1 : 0;
							var search = jQuery('#filter_search_shop_products').val();
							var tag = jQuery('#filter_product_tag').val();
							var brand = jQuery('#filter_product_category_Brand').val();
							var categoryProducts = jQuery('#filter_product_category_Products').val();
							var subBrand = jQuery('#filter_product_category_SubBrand').val();
							var color = jQuery('#filter_attribute_flat_display').val();

							if (applyFilters)
							{
								var location = '<?php echo Uri::root();?>' + 'index.php?option=com_redshopb&task=shop.ajaxPrintProductsList' +
									'&list_currency=' + currency + '&list_language=' + language +
									'&action=print&showStock=1' + '&onSale=' + onSale +
									'&search=' + search + '&tag=' + tag +
									'&brand=' + brand + '&categoryProducts=' + categoryProducts +
									'&subBrand=' + subBrand + '&color=' + color;

							}
							else
							{
								currency = jQuery("#jform_currency_id").val();
								var location = '<?php echo Uri::root();?>' + 'index.php?option=com_redshopb&task=shop.ajaxPrintProductsList' +
									'&list_currency=' + currency +
									'&action=print' + '&onSale=' + onSale +
									'&search=' + search + '&tag=' + tag +
									'&brand=' + brand + '&categoryProducts=' + categoryProducts +
									'&subBrand=' + subBrand + '&color=' + color;
							}
							var newWindow = window.open(location, '_self');
						}
						<?php endif; ?>
					</script>
					<div class="row-fluid">
						<?php
						echo RedshopbLayoutHelper::render(
							'shop.searchtools.default',
							array(
								'view' => $this,
								'options' => array(
									'searchField' => 'search_shop_products',
									'searchFieldSelector' => '#filter_search_shop_products',
									'limitFieldSelector' => '#list_shop_product_limit',
									'activeOrder' => $listOrder,
									'activeDirection' => $listDirn,
									'chosenSupport' => 0,
									'onSale' => RedshopbHelperShop::areThereCampaignItems()
								)
							)
						);
						?>

						<div id="addInputs"></div>
						<input type="hidden" name="task" value="">
						<input type="hidden" name="boxchecked" value="0">
						<?php echo HTMLHelper::_('form.token'); ?>
					</div>
				</form>
			</div>
		</div>
		<div class="row-fluid productListContainer">
			<div id="myModal" class="modal hide fade myModal redshopb-wash" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
				<div class="modal-body">
				</div>
			</div>

			<div id="zoomModal" class="modal hide fade myModal redshopb-zoom" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
				<div class="modal-body zoom">
				</div>
			</div>

			<?php if ($showPrintButton) :?>
				<div id="productsListModal" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="productsListLabel" aria-hidden="true">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
						<h3 id="productsListLabel"><?php echo Text::_('COM_REDSHOPB_SHOP_PRINT_PRODUCTS_LIST'); ?></h3>
					</div>
					<div class="modal-body" style="overflow-y: visible !important;">
						<div id="ajaxModal"></div>
					</div>
					<div class="modal-footer">
						<button class="btn btn-success" data-dismiss="modal" aria-hidden="true" id="printList">
							<i class="icon icon-print">&nbsp;</i>
							<?php echo Text::_('COM_REDSHOPB_PRINT');?>
						</button>
					</div>
				</div>
			<?php endif; ?>

			<?php if (!empty($this->collections)) : ?>
				<div class="productList">
					<?php $i = 0; ?>
					<?php echo HTMLHelper::_('vnrbootstrap.startTabSet', 'collection', array()); ?>
					<?php
					foreach ($this->collections as $collection): ?>
											<?php echo HTMLHelper::_('vnrbootstrap.addTab', 'collection', 'collection_' . $collection, RedshopbHelperCollection::getName($collection, true)); ?>
											<div class="spinner pagination-centered" id="collection_spinner_<?php echo $collection; ?>">
												<?php echo HTMLHelper::image('media/com_redshopb/images/ajax-loader.gif', '') ?>
											</div>
											<div id="collection_products_<?php echo $collection; ?>"></div>
											<?php echo HTMLHelper::_('vnrbootstrap.endTab'); ?>
											<?php
											if (empty($default) && $i == 0): ?>
																		<?php $default = $collection; ?>
											<?php endif; ?>
											<?php $i++; ?>
					<?php endforeach; ?>
					<?php echo HTMLHelper::_('vnrbootstrap.endTabSet'); ?>
					<?php
					if (isset($default) && !$this->collectionId)
					{
						$id = $default;
					}
					elseif ($this->collectionId)
					{
						$id = $this->collectionId;
					}
					?>
					<script type="text/javascript">
						jQuery(document).ready(function () {
							jQuery('a[data-toggle="tab"]').on('shown', function (e) {
								id = e.target.hash.split('_')[1];
								limit = jQuery('#list_product_shop_limit').val();
								start = 0;

								if (!JTabIsLoaded(id)) {
									JLoadCollectionProducts(id, start, limit);
								} else {
									jQuery(document).trigger('refreshDropdowns', [id]);
								}
							});
							jQuery('#collectionTabs a[href="#collection_<?php echo $id ?>"]').tab('show');
						});

						function JTabIsLoaded(id) {
							loaded = window.loadedIds;

							if (loaded == undefined) {
								window.loadedIds = new Array(id);

								return false;
							}
							else {
								if (loaded.indexOf(id) != -1) {
									return true;
								}
								else {
									loaded.push(id);
									window.loadedIds = loaded;

									return false;
								}
							}
						}

						function JLoadCollectionProducts(id, start, limit) {
							var dataVar = {};
							onSale = jQuery('#filter_onsale').attr('checked');
							search = jQuery('#filter_search_shop_products').val();
							category = jQuery('#filter_product_category').val();
							flat_display = jQuery('#filter_attribute_flat_display').val();
							collection = jQuery('#filter_product_collection').val();

							var link = '<?php echo Uri::root(); ?>index.php?option=com_redshopb&task=shop.ajaxGetCollectionItems' +
								'&collectionId=' + id + '&start=' + start + '&limit=' + limit;

							if (onSale && onSale == 'checked') {
								link += '&onSale=1';
							}
							if (search !== undefined && search !== '') {
								link += '&search=' + search;
							}
							if (category !== undefined && category !== '') {
								link += '&category=' + category;
							}
							if (flat_display !== undefined && flat_display !== '') {
								link += '&flat_display=' + flat_display;
							}
							if (collection !== undefined && collection !== '') {
								link += '&collection=' + collection;
							}
							jQuery(".filter-form-shop [name^='filter']").each(function(idx,ele){
								dataVar[jQuery(ele).attr('name')] = jQuery(ele).val();
							});

							dataVar["<?php echo Session::getFormToken(); ?>"] = 1;

							jQuery.ajax({
								url: link,
								cache: false,
								type: 'post',
								data: dataVar,
								beforeSend: function (xhr) {
									jQuery('#collection_spinner_' + id).show();
									jQuery('#collection_products_' + id).html('');
								}
							}).done(function (data) {
								jQuery('#collection_spinner_' + id).hide();
								jQuery('#collection_products_' + id).html(data);
								limit = jQuery('#list_product_shop_limit').val();
								patern = new RegExp('limitstart.value=[0-9]+', 'i');
								jQuery('#collection_products_' + id + ' ul[class="pagination-list"] li a').each(function () {
									var onclick = jQuery(this).attr('onclick');

									if (onclick != undefined && onclick != null) {
										start = patern.exec(onclick)[0].split('=')[1];
									}
									else {
										start = 0;
									}

									jQuery(this).attr('onclick', 'JLoadCollectionProducts(' + id + ',' + start + ',' + limit + '); return false;');
								});

								Holder.run();
								jQuery('.flexslider').flexslider(<?php echo $flexsliderOptionsReg->toString() ?>);
								setWashCareModal();

								initFootableRedshopb();
								jQuery('.carousel-variants').bind('slid', function () {
									fooTableRedraw();
									initHideItemsRow();
								});

								jQuery('.dropDownAccessory').each(function(){jQuery(this).multiselect({'nonSelectedText': jQuery(this).find('optgroup:first').attr('label')})});
								jQuery(document).trigger('refreshDropdowns', [id]);
							});
						}

						jQuery(document).on('refreshDropdowns', function(e, wid) {
							var parent = jQuery('#filter_attribute_flat_display').parent();
							jQuery.ajax({
								url: '<?php echo Uri::root(); ?>index.php?option=com_redshopb&task=shop.ajaxRefreshDropDowns&collection_id=' + wid,
								dataType: 'html',
								type: 'POST',
								beforeSend: function()
								{
									jQuery('#filter_attribute_flat_display').remove();
									jQuery(parent).html('<div class="spinner pagination-centered"><?php echo HTMLHelper::image('media/com_redshopb/images/ajax-loader.gif', '');?></div>');
								}
							}).done(function(data)
							{
								jQuery(parent).hide();
								data = jQuery(data).find('.controls').html();
								jQuery(parent).html('<div class="control-group">' + data + '</div>');
								jQuery('#filter_attribute_flat_display').multiselect({'nonSelectedText': jQuery(this).find('optgroup:first').attr('label'), 'maxHeight' : 200});
								jQuery(parent).show();
							});
						});
					</script>
				</div>
			<?php else: ?>
				<?php echo RedshopbHelperCollection::getShopCollectionProducts($this->placeOrderPermission); ?>
			<?php endif; ?>
		</div>
	<?php endif; ?>
</div>
