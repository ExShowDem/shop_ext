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
HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('vnrbootstrap.tooltip');
HTMLHelper::_('rjquery.chosen', 'select');

$url = 'index.php?option=com_redshopb&view=product_attribute_value&layout=edit';

if (!$this->isNew)
{
	$url .= '&id=' . $this->item->id;
}

if (!empty($this->productId))
{
	$url .= '&product_id=' . (int) $this->productId;
}

if (!empty($this->attributeId))
{
	$url .= '&attribute_id=' . (int) $this->attributeId;
}

$return = Factory::getApplication()->input->getBase64('return', null);

if (!empty($return))
{
	$url .= '&return=' . $return;
}

$action = RedshopbRoute::_($url);

$imagePath = RedshopbHelperThumbnail::originalToResize($this->item->image, 150, 80, 100, 0, 'product_attr_value');
?>

<?php if ($this->isConversionSet): ?>
<script type="text/javascript">
	(function($){
		$(document).ready(function(){
			// Disallow client choose another attribute type
			$('#jform_product_attribute_id').prop('disabled', true).trigger("liszt:updated").prop('disabled', false);
		});
	})(jQuery);
</script>
<?php endif; ?>

<div class="redshopb-product_attribute_value">
	<div class="row">
		<ul class="nav nav-pills" id="productAttributeValueTab">
			<li class="active">
				<a href="#productAttributeValueDetails" data-toggle="tab">
					<?php echo Text::_('COM_REDSHOPB_DETAILS'); ?>
				</a>
			</li>
			<?php if ($this->isConversionSet) : ?>
				<li>
					<a href="#productAttributeValueConversionSets" data-toggle="tab">
						<?php echo Text::_('COM_REDSHOPB_PRODUCT_ATTRIBUTE_CONVERSION_SETS_LBL') ?>
					</a>
				</li>
			<?php endif; ?>
		</ul>
		<hr />
		<form action="<?php echo $action; ?>" method="post" name="adminForm" id="adminForm"
					class="form-validate form-horizontal redshopb-product_attribute_value-form" enctype="multipart/form-data">
			<div class="tab-content">
				<div class="tab-pane active" id="productAttributeValueDetails">
					<div class="form-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('product_attribute_id'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('product_attribute_id'); ?>
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

					<?php if (!$this->isConversionSet): ?>
					<div class="form-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('value'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('value'); ?>
						</div>
					</div>
					<?php endif; ?>

					<div class="form-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('selected'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('selected'); ?>
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
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('imageFileUpload'); ?>

							<?php if (!empty($imagePath)) :?>
								<img src="<?php echo $imagePath; ?>" />
							<?php endif;?>
						</div>
					</div>

					<!-- hidden fields -->
					<?php echo $this->form->getInput('image'); ?>
				</div>
				<?php if ($this->isConversionSet): ?>
				<div class="tab-pane" id="productAttributeValueConversionSets">
					<?php if (empty($this->conversionSets)): ?>
						<?php echo RedshopbLayoutHelper::render('common.nodata'); ?>
					<?php else: ?>
						<div class="row">
						<?php foreach ($this->conversionSets as $conversionSet): ?>
							<div class="form-group">
								<div class="control-label">
									<label id="jform_product_attribute_value_conversion_<?php echo $conversionSet->id ?>-lbl"
										for="jform_product_attribute_value_conversion_<?php echo $conversionSet->id ?>"
											class="hasTooltip required" title="">
										<?php echo $conversionSet->name ?><span class="star">&nbsp;*</span>
									</label>
								</div>
								<div class="controls">
									<?php $value = (isset($this->conversions[$conversionSet->id])) ? $this->conversions[$conversionSet->id]->value : '' ?>
									<input type="text" name="jform[product_attribute_value_conversion][<?php echo $conversionSet->id ?>]"
										id="jform_product_attribute_value_conversion_<?php echo $conversionSet->id ?>"
										value="<?php echo $value ?>" maxlength="255" />
								</div>
							</div>
						<?php endforeach; ?>
						</div>
					<?php endif; ?>
				</div>
				<?php endif; ?>

				<!-- hidden fields -->
				<input type="hidden" name="option" value="com_redshopb">
				<?php echo $this->form->getInput('ordering'); ?>
				<input type="hidden" name="task" value="">
				<?php echo $this->form->getInput('id'); ?>
				<input type="hidden" name="jform[product_id]" value="<?php echo $this->attribute->product_id; ?>"/>
				<?php echo HTMLHelper::_('form.token'); ?>
			</div>
		</form>
	</div>
</div>
