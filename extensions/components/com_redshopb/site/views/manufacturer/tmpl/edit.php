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

// HTML helpers
HTMLHelper::_('rjquery.framework');
HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('vnrbootstrap.tooltip');
HTMLHelper::_('rjquery.chosen', 'select');
HTMLHelper::_('behavior.formvalidator');

$action          = RedshopbRoute::_('index.php?option=com_redshopb&view=manufacturer');
$isNew           = (int) $this->item->id <= 0;
$params          = $this->form->getFieldset('params');
$seoTitles       = $params['jform_params_seo_page_titles'];
$seoHeadings     = $params['jform_params_seo_page_headings'];
$seoDescriptions = $params['jform_params_seo_page_description'];
$seoKeywords     = $params['jform_params_seo_page_keywords'];
$imagePath       = RedshopbHelperThumbnail::originalToResize($this->item->image, 150, 80, 100, 0, 'manufacturers');

Factory::getDocument()->addStyleDeclaration('
.tab-content > form > .tab-pane {
	display: none;
}
.tab-content > form > .active{
	display: block;
}
'
);

echo RedshopbBrowserBreadcrumbs::renderBreadcrumbs();
?>
<script type="text/javascript">
	Joomla.submitbutton = function(task)
	{
		if (task == "manufacturer.cancel" || document.formvalidator.isValid(document.getElementById("adminForm")))
		{
			<?php echo $this->form->getField('description')->save() ?>
			Joomla.submitform(task, document.getElementById("adminForm"));
		}
	};
</script>

<div class="row">
	<div class="col-md-12">
		<ul class="nav nav-tabs" id="categoryTabs">
			<li class="active">
				<a href="#details" data-toggle="tab">
					<?php echo Text::_('COM_REDSHOPB_DETAILS'); ?>
				</a>
			</li>
			<li>
				<a href="#seo" data-toggle="tab">
					<?php echo Text::_('COM_REDSHOPB_SEO'); ?>
				</a>
			</li>
		</ul>
	</div>
</div>

<div class="redshopb-manufacturer">
	<div class="tab-content">
		<form action="<?php echo $action; ?>" method="post" name="adminForm" id="adminForm"
			  class="form-validate form-horizontal redshopb-manufacturer-form" enctype="multipart/form-data">
			<div class="tab-pane active" id="details">
				<div class="row">
					<div class="col-md-12">
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
										<?php echo $this->form->getLabel('parent_id'); ?>
									</div>
									<div class="controls">
										<?php echo $this->form->getInput('parent_id'); ?>
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
										<?php echo $this->form->getLabel('featured'); ?>
									</div>
									<div class="controls">
										<?php echo $this->form->getInput('featured'); ?>
									</div>
								</div>
								<?php if ($this->item->image): ?>
									<div class="form-group">
										<div class="control-label">
											<?php echo $this->form->getLabel('deleteImage'); ?>
										</div>
										<div class="controls">
											<?php echo $this->form->getInput('deleteImage'); ?>
										</div>
									</div>
								<?php endif; ?>
								<div class="form-group">
									<div class="control-label">
										<?php echo $this->form->getLabel('imageFileUpload'); ?>
										<?php echo $this->form->getBackWSValueButton('image'); ?>
									</div>
									<div class="controls">
										<?php echo $this->form->getInput('imageFileUpload'); ?>

										<?php if (!empty($imagePath)) :?>
											<img src="<?php echo $imagePath; ?>" />
										<?php endif;?>
									</div>
								</div>
								<div class="form-group">
									<div class="control-label">
										<?php echo $this->form->getLabel('category'); ?>
									</div>
									<div class="controls">
										<?php echo $this->form->getInput('category'); ?>
									</div>
								</div>
								<div class="form-group">
									<div class="control-label">
										<?php echo $this->form->getLabel('description'); ?>
									</div>
									<div class="controls">
										<?php echo $this->form->getInput('description'); ?>
									</div>
								</div>
							</div>
						</div>
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
			</div>

			<!-- hidden fields -->
			<?php echo $this->form->getInput('image'); ?>
			<input type="hidden" name="option" value="com_redshopb">
			<input type="hidden" name="id" value="<?php echo $this->item->id; ?>">
			<input type="hidden" name="task" value="">
			<?php echo HTMLHelper::_('form.token'); ?>
		</form>
	</div>
</div>
