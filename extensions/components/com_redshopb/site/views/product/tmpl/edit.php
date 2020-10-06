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

// HTML helpers
HTMLHelper::_('rjquery.framework');
HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('vnrbootstrap.tooltip');
HTMLHelper::_('rjquery.chosen', 'select');
HTMLHelper::_('rsortablelist.main');
HTMLHelper::_('rsearchtools.main');
HTMLHelper::_('rjquery.flexslider', '.flexslider', array('slideshow' => false, 'directionNav' => false, 'animation' => 'slide', 'animationLoop' => false));
HTMLHelper::_('vnrbootstrap.modal', 'productDiscontinue');
HTMLHelper::_('vnrbootstrap.popover');

RedshopbHtml::loadFooTable();

// Manually load the redshopb js file
RHelperAsset::load('redshopb.js', 'com_redshopb');

RHelperAsset::load('lib/jquery-fileupload/jquery.fileupload.css', 'com_redshopb');

// The jQuery UI widget factory, can be omitted if jQuery UI is already included
RHelperAsset::load('lib/jquery-fileupload/vendor/jquery.ui.widget.js', 'com_redshopb');

// The Iframe Transport is required for browsers without support for XHR file uploads
RHelperAsset::load('lib/jquery-fileupload/jquery.iframe-transport.js', 'com_redshopb');

// The basic File Upload plugin
RHelperAsset::load('lib/jquery-fileupload/jquery.fileupload.js', 'com_redshopb');

$action = RedshopbRoute::_('index.php?option=com_redshopb&view=product&layout=edit');

$input              = Factory::getApplication()->input;
$fromCollectionView = RedshopbInput::isFromCollection();
$fromCompanyView    = RedshopbInput::isFromCompany();
$fromDepartmentView = RedshopbInput::isFromDepartment();
$tab                = strtolower($input->getString('tab', $input->post->getString('tab', 'details')));
$isNew              = (int) $this->item->id <= 0;
$tags               = json_encode($this->form->getValue('tag_id'));
$categories         = json_encode($this->form->getValue('categories'));
$mainCategory       = json_encode($this->form->getValue('category_id'));
$params             = $this->form->getFieldset('params');
$seoTitles          = $params['jform_params_seo_page_titles'];
$seoHeadings        = $params['jform_params_seo_page_headings'];
$seoDescriptions    = $params['jform_params_seo_page_description'];
$seoKeywords        = $params['jform_params_seo_page_keywords'];

echo RedshopbBrowserBreadcrumbs::renderBreadcrumbs();

Factory::getDocument()->addStyleDeclaration('
.tab-content > form > .tab-pane {
	display: none;
}
.tab-content > form > .active{
	display: block;
}
'
);
?>

<script type="text/javascript">
	jQuery(document).ready(function ()
	{
		Joomla.JText.load({
			COM_REDSHOPB_MEDIA_SYNC_ERROR_TIMEOUT:'<?php echo Text::_('COM_REDSHOPB_MEDIA_SYNC_ERROR_TIMEOUT', true); ?>',
			COM_REDSHOPB_MEDIA_SYNC_ERROR_APPLICATION_ERROR: '<?php echo Text::_('COM_REDSHOPB_MEDIA_SYNC_ERROR_APPLICATION_ERROR', true); ?>',
			COM_REDSHOPB_PLEASE_SELECT_ITEM: '<?php echo Text::_('COM_REDSHOPB_PLEASE_SELECT_ITEM', true); ?>',
			COM_REDSHOPB_PRODUCT_UNSAVED_CHANGES_ALERT:  '<?php echo Text::_('COM_REDSHOPB_PRODUCT_UNSAVED_CHANGES_ALERT', true); ?>'
		});

		jQuery('#adminForm').on('change', function()
		{
			redSHOPB.products.trackAltered(this);
		});

		jQuery('#imageForm').off();

		var url = '<?php echo Uri::root(); ?>index.php?option=com_redshopb&view=product';
		var data = {'<?php echo Session::getFormToken(); ?>': 1};

		var tags = <?php echo $tags;?>;
		var categories = <?php echo $categories;?>;
		var mainCategory = <?php echo $mainCategory;?>;

		<?php if ($this->item->id) : ?>
		url = url + '&product_id=<?php echo $this->item->id ?>';
		redSHOPB.products.init(url, data, tags, categories, mainCategory, <?php echo $this->item->id . ', ' . $tab?>);
		<?php else: ?>
		redSHOPB.products.init(url, data, tags, categories, mainCategory, 0, <?php echo $tab;?>);
		<?php endif;?>

		jQuery('#productTabs a[data-toggle="tab"]').on('click', function()
		{
			var targ = jQuery(this);
			jQuery('.tab-content > form > .tab-pane').removeClass('active');
			jQuery('#adminForm input[name="tab"]').val(targ.attr('href').substr(1));
		});

		jQuery('#productTabs a[href="#<?php echo $tab; ?>"]').click();
	});
</script>

<script type="text/javascript">
	function makeStockUnlimited(wrapperId)
	{
		(function($) {
			// Update product variants stock unlimited
			if ($('#' + wrapperId + ' .ajaxUpdateAmountUnlimited').length > 0) {
				$('#' + wrapperId + ' .ajaxUpdateAmountUnlimited').each(function(index, item){
					if ($("#" + $(item).attr("data-field")).attr("data-unlimited") == "0")
					{
						$(item).click();
					}
				});
			}

			// Update product stock unlimited
			if ($('#' + wrapperId + ' .ajaxUpdateProductAmountUnlimited').length > 0) {
				$('#' + wrapperId + ' .ajaxUpdateProductAmountUnlimited').each(function(index, item){
					if ($("#" + $(item).attr("data-field")).attr("data-unlimited") == "0")
					{
						$(item).click();
					}
				});
			}

			return true;
		})(jQuery);
	}

	(function ($)
	{
		Joomla.submitbutton = function (task)
		{
			if (task == 'product.save'
				|| task == 'product.apply'
				|| task == 'product.save2new')
			{
				var alteredForms = jQuery('form.js-altered');

				if(alteredForms.length != 0)
				{
					alteredForms.each(function (index, element) {
						form = jQuery(element);

						if(form.attr('id') != 'adminForm')
						{
							redSHOPB.products.submitDependencies(element);
						}
					})
				}
			}

			Joomla.submitform(task);
		}
	})(jQuery);
</script>

<!-- SOF TABS -->
<div class="row">
	<div class="col-md-12">
		<ul class="nav nav-tabs" id="productTabs">
			<li>
				<a href="#details" data-toggle="tab">
					<?php echo Text::_('COM_REDSHOPB_DETAILS'); ?>
				</a>
			</li>
			<li>
				<a href="#seo" data-toggle="tab">
					<?php echo Text::_('COM_REDSHOPB_SEO'); ?>
				</a>
			</li>
			<?php if ($this->item->id || $this->anyRequired) : ?>
				<li>
					<a href="#fields"
					   data-toggle="tab"><?php echo Text::_('COM_REDSHOPB_FIELDS_TITLE') ?></a>
				</li>
			<?php endif; ?>

			<?php if ($this->item->id) : ?>
				<li>
					<a href="#ordering" data-toggle="tab" data-ajax-tab-load="true"><?php echo Text::_('COM_REDSHOPB_PRODUCT_ORDERING_TITLE') ?></a>
				</li>
				<li>
					<a href="#images"
					   data-toggle="tab"><?php echo Text::_('COM_REDSHOPB_PRODUCT_IMAGES_TITLE') ?></a>
				</li>
				<li>
					<a href="#compositions"
					   data-toggle="tab" data-ajax-tab-load="true"><?php echo Text::_('COM_REDSHOPB_PRODUCT_COMPOSITIONS_TITLE') ?></a>
				</li>
				<li>
					<a href="#attributes"
					   data-toggle="tab" data-ajax-tab-load="true"><?php echo Text::_('COM_REDSHOPB_PRODUCT_ATTRIBUTE_LIST_TITLE') ?></a>
				</li>
				<li>
					<a href="#combinations" data-toggle="tab" data-ajax-tab-load="true"><?php echo Text::_('COM_REDSHOPB_COMBINATIONS') ?></a>
				</li>
				<li>
					<a href="#tableprices" data-toggle="tab" data-ajax-tab-load="true"><?php echo Text::_('COM_REDSHOPB_TABLE_PRICES') ?></a>
				</li>
				<li>
					<a href="#prices" data-toggle="tab" data-ajax-tab-load="true"><?php echo Text::_('COM_REDSHOPB_PRICES') ?></a>
				</li>
				<li>
					<a href="#stock" data-toggle="tab" data-ajax-tab-load="true"><?php echo Text::_('COM_REDSHOPB_STOCK') ?></a>
				</li>
				<li>
					<a href="#collections" data-toggle="tab" data-ajax-tab-load="true">
						<?php echo Text::_('COM_REDSHOPB_COLLECTION_LIST_TITLE') ?>
					</a>
				</li>
				<li>
					<a href="#descriptions" data-toggle="tab" data-ajax-tab-load="true"><?php echo Text::_('COM_REDSHOPB_PRODUCT_DESCRIPTIONS') ?></a>
				</li>
				<?php if (true == false): // Wash and care hidden until backlog review?>
					<li>
						<a href="#washcarespecs" data-toggle="tab" data-ajax-tab-load="true"><?php echo Text::_('COM_REDSHOPB_WASH_CARE_SPEC_LIST_TITLE') ?></a>
					</li>
				<?php endif;?>
				<li>
					<a href="#accessories" data-toggle="tab" data-ajax-tab-load="true"><?php echo Text::_('COM_REDSHOPB_ACCESSORIES_TITLE') ?></a>
				</li>
				<li>
					<a href="#selectedaccessories" data-toggle="tab" data-ajax-tab-load="true"><?php echo Text::_('COM_REDSHOPB_SELECTED_ACCESSORIES_TITLE') ?></a>
				</li>
				<li>
					<a href="#complimentaryproducts" data-toggle="tab" data-ajax-tab-load="true"><?php echo Text::_('COM_REDSHOPB_COMPLIMENTARY_PRODUCTS_TITLE') ?></a>
				</li>
				<li>
					<a href="#selectedcomplimentaryproducts" data-toggle="tab" data-ajax-tab-load="true"><?php echo Text::_('COM_REDSHOPB_SELECTED_COMPLIMENTARY_PRODUCTS_TITLE') ?></a>
				</li>
				<li>
					<a href="#packaging"
					   data-toggle="tab"><?php echo Text::_('COM_REDSHOPB_PRODUCT_PACKAGING_TITLE') ?></a>
				</li>
			<?php endif; ?>
		</ul>

		<div class="tab-content">
			<form action="<?php echo $action;?>"
				  method="post"
				  name="adminForm"
				  id="adminForm"
				  class="form-validate form-horizontal"
				  enctype="multipart/form-data"
				  data-save-task="product.apply">
				<div class="tab-pane" id="details">
					<div class="row">
						<div class="col-md-12">
							<?php if ((bool) $this->item->discontinued) : ?>
								<div class="alert">
									<button type="button" class="close" data-dismiss="alert">&times;</button>
									<strong>
										<i class="icon-warning-sign"></i>
										<?php echo Text::_('COM_REDSHOPB_ITEM_DISCONTINUED_ALERT') ?>
									</strong>
								</div>
							<?php endif; ?>
							<div class="row">
								<div class="col-md-6 adapt-inputs">
									<div class="form-group">
										<div class="control-label">
											<?php echo $this->form->getLabel('name'); ?>
										</div>
										<div class="controls">
											<?php echo $this->form->getInput('name'); ?>
										</div>
									</div>
									<div class="form-group">
										<div class="control-label">
											<?php echo $this->form->getLabel('company_id'); ?>
										</div>
										<div class="controls">
											<?php echo $this->form->getInput('company_id'); ?>
										</div>
									</div>
									<div class="form-group">
										<div class="control-label">
											<?php echo $this->form->getLabel('category_id'); ?>
										</div>
										<div class="controls">
											<?php echo $this->form->getInput('category_id'); ?>
										</div>
									</div>
									<div class="form-group">
										<div class="control-label">
											<?php echo $this->form->getLabel('categories'); ?>
										</div>
										<div class="controls">
											<?php echo $this->form->getInput('categories'); ?>
										</div>
									</div>
									<div class="form-group">
										<div class="control-label">
											<?php echo $this->form->getLabel('manufacturer_id'); ?>
										</div>
										<div class="controls">
											<?php echo $this->form->getInput('manufacturer_id'); ?>
										</div>
									</div>
									<div class="form-group">
										<div class="control-label">
											<?php echo $this->form->getLabel('tag_id'); ?>
										</div>
										<div class="controls">
											<?php echo $this->form->getInput('tag_id'); ?>
										</div>
									</div>
									<div class="form-group">
										<div class="control-label">
											<?php echo $this->form->getLabel('featured'); ?>
										</div>
										<div class="controls">
											<?php echo $this->form->getInput('featured'); ?>
										</div>
									</div>
									<div class="form-group">
										<div class="control-label">
											<?php echo $this->form->getLabel('state'); ?>
										</div>
										<div class="controls">
											<?php echo $this->form->getInput('state'); ?>
										</div>
									</div>
									<div class="form-group">
										<div class="control-label">
											<?php echo $this->form->getLabel('service'); ?>
										</div>
										<div class="controls">
											<?php echo $this->form->getInput('service'); ?>
										</div>
									</div>
									<div class="form-group">
										<div class="control-label">
											<?php echo $this->form->getLabel('campaign'); ?>
										</div>
										<div class="controls">
											<?php echo $this->form->getInput('campaign'); ?>
										</div>
									</div>
									<div class="form-group">
										<div class="control-label">
											<?php echo $this->form->getLabel('decimal_position'); ?>
										</div>
										<div class="controls">
											<?php echo $this->form->getInput('decimal_position'); ?>
										</div>
									</div>
									<div class="form-group">
										<div class="control-label">
											<?php echo $this->form->getLabel('tax_group_id'); ?>
										</div>
										<div class="controls">
											<?php echo $this->form->getInput('tax_group_id'); ?>
										</div>
									</div>
								</div>
								<div class="col-md-6 adapt-inputs">
									<div class="form-group">
										<div class="control-label">
											<?php echo $this->form->getLabel('stock_lower_level'); ?>
										</div>
										<div class="controls">
											<?php echo $this->form->getInput('stock_lower_level'); ?>
										</div>
									</div>
									<div class="form-group">
										<div class="control-label">
											<?php echo $this->form->getLabel('stock_upper_level'); ?>
										</div>
										<div class="controls">
											<?php echo $this->form->getInput('stock_upper_level'); ?>
										</div>
									</div>
									<div class="form-group">
										<div class="control-label">
											<?php echo $this->form->getLabel('sku'); ?>
										</div>
										<div class="controls">
											<?php echo $this->form->getInput('sku'); ?>
										</div>
									</div>
									<div class="form-group">
										<div class="control-label">
											<?php echo $this->form->getLabel('manufacturer_sku'); ?>
										</div>
										<div class="controls">
											<?php echo $this->form->getInput('manufacturer_sku'); ?>
										</div>
									</div>
									<div class="form-group">
										<div class="control-label">
											<?php echo $this->form->getLabel('related_sku'); ?>
										</div>
										<div class="controls">
											<?php echo $this->form->getInput('related_sku'); ?>
										</div>
									</div>
									<div class="form-group">
										<div class="control-label">
											<?php echo $this->form->getLabel('unit_measure_id'); ?>
										</div>
										<div class="controls">
											<?php echo $this->form->getInput('unit_measure_id'); ?>
										</div>
									</div>
									<div class="form-group">
										<div class="control-label">
											<?php echo $this->form->getLabel('template_id'); ?>
										</div>
										<div class="controls">
											<?php echo $this->form->getInput('template_id'); ?>
										</div>
									</div>
									<div class="form-group">
										<div class="control-label">
											<?php echo $this->form->getLabel('print_template_id'); ?>
										</div>
										<div class="controls">
											<?php echo $this->form->getInput('print_template_id'); ?>
										</div>
									</div>
									<div class="form-group">
										<div class="control-label">
											<?php echo $this->form->getLabel('filter_fieldset_id'); ?>
										</div>
										<div class="controls">
											<?php echo $this->form->getInput('filter_fieldset_id'); ?>
										</div>
									</div>
									<div class="form-group">
										<div class="control-label">
											<?php echo $this->form->getLabel('date_new'); ?>
										</div>
										<div class="controls">
											<?php echo $this->form->getInput('date_new'); ?>
										</div>
									</div>
									<div class="form-group">
										<div class="control-label">
											<?php echo $this->form->getLabel('customer_ids'); ?>
										</div>
										<div class="controls">
											<?php echo $this->form->getInput('customer_ids'); ?>
										</div>
									</div>
									<div class="form-group">
										<div class="control-label">
											<?php echo $this->form->getLabel('price'); ?>
										</div>
										<div class="controls">
											<?php echo $this->form->getInput('price'); ?>
										</div>
									</div>
									<div class="form-group">
										<div class="control-label">
											<?php echo $this->form->getLabel('retail_price'); ?>
										</div>
										<div class="controls">
											<?php echo $this->form->getInput('retail_price'); ?>
										</div>
									</div>
									<div class="form-group">
										<div class="control-label">
											<?php echo $this->form->getLabel('weight'); ?>
										</div>
										<div class="controls">
											<?php echo $this->form->getInput('weight'); ?>
										</div>
									</div>
									<div class="form-group">
										<div class="control-label">
											<?php echo $this->form->getLabel('volume'); ?>
										</div>
										<div class="controls">
											<?php echo $this->form->getInput('volume'); ?>
										</div>
									</div>
									<div class="form-group">
										<div class="control-label">
											<?php echo $this->form->getLabel('publish_date'); ?>
										</div>
										<div class="controls">
											<?php echo $this->form->getInput('publish_date'); ?>
										</div>
									</div>
									<div class="form-group">
										<div class="control-label">
											<?php echo $this->form->getLabel('unpublish_date'); ?>
										</div>
										<div class="controls">
											<?php echo $this->form->getInput('unpublish_date'); ?>
										</div>
									</div>
									<div class="form-group">
										<div class="control-label">
											<?php echo $this->form->getLabel('calc_type'); ?>
										</div>
										<div class="controls">
											<?php echo $this->form->getInput('calc_type'); ?>
										</div>
									</div>
								</div>
							</div>
							<!-- hidden fields -->
							<input type="hidden" name="option" value="com_redshopb">
							<input type="hidden" name="id" value="<?php echo $this->item->id; ?>">
							<input type="hidden" name="task" value="">
							<input type="hidden" name="from_collection" value="<?php echo $fromCollectionView; ?>">
							<input type="hidden" name="from_company" value="<?php echo $fromCompanyView; ?>">
							<input type="hidden" name="from_department" value="<?php echo $fromDepartmentView; ?>">
							<input type="hidden" name="tab" value="details"/>
							<?php echo HTMLHelper::_('form.token'); ?>
						</div>
					</div>
				</div>
				<div class="tab-pane" id="seo">
					<div class="row">
						<div class="col-md-12">
							<div class="alert">
								<button type="button" class="close" data-dismiss="alert">&times;</button>
								<strong>
									<i class="icon-warning-sign"></i>
									<?php echo Text::_('COM_REDSHOPB_SEO_CONFIG_OVERRIDE_WARNING') ?>
								</strong>
							</div>
							<div class="row">
								<div class="col-md-12 adapt-inputs">
									<div class="form-group">
										<div class="control-label">
											<?php echo $seoTitles->label; ?>
										</div>
										<div class="controls">
											<?php echo $seoTitles->input; ?>
										</div>
									</div>
									<div class="form-group">
										<div class="control-label">
											<?php echo $seoHeadings->label; ?>
										</div>
										<div class="controls">
											<?php echo $seoHeadings->input; ?>
										</div>
									</div>
									<div class="form-group">
										<div class="control-label">
											<?php echo $seoDescriptions->label; ?>
										</div>
										<div class="controls">
											<?php echo $seoDescriptions->input; ?>
										</div>
									</div>
									<div class="form-group">
										<div class="control-label">
											<?php echo $seoKeywords->label; ?>
										</div>
										<div class="controls">
											<?php echo $seoKeywords->input; ?>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<?php if ($this->item->id):?>
					<div class="tab-pane" id="ordering">
						<div class="col-md-12 ordering-content tab-content">
							<div class="spinner pagination-centered">
								<?php echo HTMLHelper::image('media/com_redshopb/images/ajax-loader.gif', '') ?>
							</div>
						</div>
					</div>
				<?php endif;?>

				<?php if ($this->item->id || $this->anyRequired):?>
					<div class="tab-pane" id="fields">
						<div class="row fields-content">
							<div class="col-md-12">
								<?php echo RedshopbLayoutHelper::render('fields.fields',
									array(
										'form' => $this->form,
										'fieldsUsedInTemplate' => $this->state->get('fieldsUsedInTemplate'),
										'formName' => 'fieldsForm',
										'scope' => 'product',
										'task' => 'product.saveFields',
										'itemId' => $this->item->id,
										'action' => RedshopbRoute::_('index.php?option=com_redshopb&view=product&layout=edit&id=' . $this->item->id),
										'return' => base64_encode('index.php?option=com_redshopb&view=product&layout=edit&tab=fields&id=' . $this->item->id))
								);?>
							</div>
						</div>
					</div>
				<?php endif;?>
				<div class="tab-pane" id="packaging">
					<div class="row">
						<div class="col-md-12 packaging-content tab-content">
							<div class="form-group">
								<div class="control-label">
									<?php echo $this->form->getLabel('min_sale'); ?>
								</div>
								<div class="controls">
									<?php echo $this->form->getInput('min_sale'); ?>
								</div>
							</div>
							<div class="form-group">
								<div class="control-label">
									<?php echo $this->form->getLabel('max_sale'); ?>
								</div>
								<div class="controls">
									<?php echo $this->form->getInput('max_sale'); ?>
								</div>
							</div>
							<div class="form-group">
								<div class="control-label">
									<?php echo $this->form->getLabel('pkg_size'); ?>
								</div>
								<div class="controls">
									<?php echo $this->form->getInput('pkg_size'); ?>
								</div>
							</div>
						</div>
					</div>
				</div>
			</form>
			<div class="tab-pane " id="images">
				<?php if ($this->item->id) : ?>
					<form action="<?php echo $action;?>" method="post" name="imageForm" id="imageForm" class="form-horizontal" enctype="multipart/form-data">
						<div class="row images-content form-horizontal">
							<div class="col-md-12">
								<div class="thumbnail">
									<div id="images-toolbar" class="btn-toolbar">
										<?php if (!$this->isLockedByWebservice): ?>
										<div class="btn-group">
											<button
													class="btn btn-lg btn-success product-image-add"
													type="button"
													data-id="0">
												<i class="icon-file-text"></i>
												<?php echo Text::_('COM_REDSHOPB_MEDIA_IMAGE_ADD_NEW') ?>
											</button>
										</div>
										<?php endif ?>
										<div class="btn-group">
											<?php if ($this->isLockedByWebservice): ?>
												<button
														class="btn btn-lg btn-success"
														id="product-image-sync"
														type="button"
														name="product-image-sync">
													<i class="icon-refresh"></i>
													<?php echo Text::_('COM_REDSHOPB_MEDIA_IMAGE_SYNC') ?>
												</button>
											<?php endif; ?>
										</div>
									</div>
									<div class="well hide" id="divSyncImage">
										<div id="progressImagesSync" class="progress progress-striped">
											<div class="bar bar-success" style="width: 0%"></div>
										</div>
										<h5><?php echo Text::_('COM_REDSHOPB_MEDIA_IMAGE_PROGRESS_LOG') ?></h5>
										<div class="progress-log" id="progress-log"></div>
									</div>
									<div id="images-edit-area" class="pagination-centered">
									</div>
									<br />
									<div class="alert image-edit-area-alert" style="display:none;">
										<button type="button" class="close">&times;</button>
										<div class="pagination-centered">
											<h3></h3>
										</div>
									</div>
									<table class="table table-striped table-hover product-images-table <?php echo (!$this->images) ? 'hide' : ''; ?>">
										<thead>
										<tr>
											<th style="width:1%;"><?php echo Text::_('COM_REDSHOPB_MEDIA_IMAGE_EDIT'); ?></th>
											<th style="width:1%;"><?php echo Text::_('COM_REDSHOPB_MEDIA_IMAGE_DELETE'); ?></th>
											<th style="width:1%;"><?php echo Text::_('COM_REDSHOPB_STATE_LABEL'); ?></th>
											<th style="width:1%;"><?php echo Text::_('COM_REDSHOPB_MEDIA_IMAGE_THUMB'); ?></th>
											<th style="width:10%;"><?php echo Text::_('COM_REDSHOPB_MEDIA_IMAGE_VIEW_ANGLE_LABEL'); ?></th>
											<th style="width:10%;"><?php echo Text::_('COM_REDSHOPB_MEDIA_IMAGE_FLAT_ATTRIBUTE_LABEL'); ?></th>
											<th><?php echo Text::_('COM_REDSHOPB_MEDIA_IMAGE_TITLE_LABEL'); ?></th>
											<th><?php echo Text::_('COM_REDSHOPB_ORDER');?></th>
										</tr>
										</thead>
										<tbody>
										<?php if ($this->images): ?>
											<?php foreach ($this->images as $image): ?>
												<tr>
													<?php echo RedshopbLayoutHelper::render(
														'media.edit.imagerow',
														array('item' => $image, 'isLockedByWebservice' => $this->isLockedByWebservice)
													);?>
												</tr>
											<?php endforeach; ?>
										<?php endif; ?>
										</tbody>
									</table>
								</div>
							</div>
						</div>
						<?php echo HTMLHelper::_('form.token'); ?>
					</form>
				<?php endif;?>
			</div>
			<div class="tab-pane " id="compositions">
				<div class="row ">
					<div class="col-md-12 compositions-content tab-content">
						<div class="spinner pagination-centered">
							<?php echo HTMLHelper::image('media/com_redshopb/images/ajax-loader.gif', '') ?>
						</div>
					</div>
				</div>
			</div>
			<div class="tab-pane" id="attributes">
				<div class="row">
					<div class="col-md-12 attributes-content tab-content">
						<div class="spinner pagination-centered">
							<?php echo HTMLHelper::image('media/com_redshopb/images/ajax-loader.gif', '') ?>
						</div>
					</div>
				</div>
			</div>
			<div class="tab-pane" id="combinations">
				<div class="row">
					<div class="col-md-12 combinations-content tab-content">
						<div class="spinner pagination-centered">
							<?php echo HTMLHelper::image('media/com_redshopb/images/ajax-loader.gif', '') ?>
						</div>
					</div>
				</div>
			</div>
			<div class="tab-pane" id="tableprices">
				<div class="row">
					<div class="col-md-12 tableprices-content tab-content">
						<div class="spinner pagination-centered">
							<?php echo HTMLHelper::image('media/com_redshopb/images/ajax-loader.gif', '') ?>
						</div>
					</div>
				</div>
			</div>
			<div class="tab-pane" id="prices">
				<div class="row">
					<div class="col-md-12 prices-content tab-content">
						<div class="spinner pagination-centered">
							<?php echo HTMLHelper::image('media/com_redshopb/images/ajax-loader.gif', '') ?>
						</div>
					</div>
				</div>
			</div>
			<div class="tab-pane" id="stock">
				<div class="row">
					<div class="col-md-12 stock-content tab-content">
						<div class="spinner pagination-centered">
							<?php echo HTMLHelper::image('media/com_redshopb/images/ajax-loader.gif', '') ?>
						</div>
					</div>
				</div>
			</div>
			<div class="tab-pane" id="collections">
				<div class="row">
					<div class="col-md-12 collections-content tab-content">
						<div class="spinner pagination-centered">
							<?php echo HTMLHelper::image('media/com_redshopb/images/ajax-loader.gif', '') ?>
						</div>
					</div>
				</div>
			</div>
			<div class="tab-pane" id="descriptions">
				<div class="row">
					<div class="col-md-12 descriptions-content tab-content">
						<div class="spinner pagination-centered">
							<?php echo HTMLHelper::image('media/com_redshopb/images/ajax-loader.gif', '') ?>
						</div>
					</div>
				</div>
			</div>
			<?php if (true == false): // Wash and care hidden until backlog review?>
				<div class="tab-pane" id="washcarespecs">
					<div class="row">
						<div class="col-md-12 washcarespecs-content tab-content">
							<div class="spinner pagination-centered">
								<?php echo HTMLHelper::image('media/com_redshopb/images/ajax-loader.gif', '') ?>
							</div>
						</div>
					</div>
				</div>
			<?php endif;?>
			<div class="tab-pane" id="accessories">
				<div class="row">
					<div class="col-md-12 accessories-content tab-content">
						<div class="spinner pagination-centered">
							<?php echo HTMLHelper::image('media/com_redshopb/images/ajax-loader.gif', '') ?>
						</div>
					</div>
				</div>
			</div>
			<div class="tab-pane" id="selectedaccessories">
				<div class="row">
					<div class="col-md-12 selectedaccessories-content tab-content">
						<div class="spinner pagination-centered">
							<?php echo HTMLHelper::image('media/com_redshopb/images/ajax-loader.gif', '') ?>
						</div>
					</div>
				</div>
			</div>
			<div class="tab-pane" id="complimentaryproducts">
				<div class="row">
					<div class="col-md-12 complimentaryproducts-content tab-content">
						<div class="spinner pagination-centered">
							<?php echo HTMLHelper::image('media/com_redshopb/images/ajax-loader.gif', '') ?>
						</div>
					</div>
				</div>
			</div>
			<div class="tab-pane" id="selectedcomplimentaryproducts">
				<div class="row">
					<div class="col-md-12 selectedcomplimentaryproducts-content tab-content">
						<div class="spinner pagination-centered">
							<?php echo HTMLHelper::image('media/com_redshopb/images/ajax-loader.gif', '') ?>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<!-- EOF TABS -->

<div id="productDiscontinue" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
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
