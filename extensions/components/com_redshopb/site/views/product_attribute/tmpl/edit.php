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
HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('vnrbootstrap.tooltip');
HTMLHelper::_('rjquery.chosen', 'select');

$url = 'index.php?option=com_redshopb&view=product_attribute&layout=edit';

if (!empty($this->productId))
{
	$url .= '&product_id=' . (int) $this->productId;
}

$return = Factory::getApplication()->input->getBase64('return', null);

if (!empty($return))
{
	$url .= '&return=' . $return;
}

$action = RedshopbRoute::_($url);

$isNew     = (int) $this->item->id <= 0;
$imagePath = RedshopbHelperThumbnail::originalToResize($this->item->image, 150, 80, 100, 0, 'product_attribute');

?>

<?php if ($this->item->id && $this->item->conversion_sets): ?>
<script type="text/javascript">
	(function($) {
		$(document).ready(function(){
			$("#redshopb-product-attribute-conversion-sets-add").click(function(event){
				event.preventDefault();
				$.ajax({
					url: '<?php echo Uri::root(); ?>index.php?option=com_redshopb&task=conversion.ajaxLoadConversionForm',
					cache: false,
					dataType: 'html',
					type: 'POST',
					data: {
						'product_attribute' : <?php echo $this->item->id ?>,
						'<?php echo Session::getFormToken(); ?>' : 1
					}
				}).done(function (data) {
					$("#productAttributesConversionSetsData").append(data);
				});
			});
		});
	})(jQuery);
</script>
<?php endif; ?>

<div class="redshopb-product_attribute">
	<div class="row">
		<ul class="nav nav-pills" id="productAttributesTab">
			<li class="active">
				<a href="#productAttributesDetails" data-toggle="tab">
					<?php echo Text::_('COM_REDSHOPB_DETAILS'); ?>
				</a>
			</li>
			<?php if ($this->item->id && $this->item->conversion_sets) : ?>
				<li>
					<a href="#productAttributesConversionSets"
						data-toggle="tab"><?php echo Text::_('COM_REDSHOPB_PRODUCT_ATTRIBUTE_CONVERSION_SETS_LBL') ?></a>
				</li>
			<?php endif; ?>
		</ul>
		<hr />
		<div class="tab-content">
			<div class="tab-pane active" id="productAttributesDetails">
				<form action="<?php echo $action; ?>" method="post" name="adminForm" id="adminForm"
					class="form-validate form-horizontal redshopb-product_attribute-form" enctype="multipart/form-data">

					<div class="form-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('name'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('name'); ?>
						</div>
					</div>
					<?php echo $this->form->renderField('product_id');?>
					<div class="form-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('type_id'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('type_id'); ?>
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
							<?php echo $this->form->getLabel('main_attribute'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('main_attribute'); ?>
						</div>
					</div>

					<div class="form-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('conversion_sets'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('conversion_sets'); ?>
						</div>
					</div>

					<div class="form-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('enable_sku_value_display'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('enable_sku_value_display'); ?>
						</div>
					</div>

					<?php if ($this->item->id && !$this->item->conversion_sets) : ?>
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
					<?php endif; ?>

					<!-- hidden fields -->
					<input type="hidden" name="option" value="com_redshopb">
					<?php echo $this->form->getInput('ordering'); ?>
					<input type="hidden" name="id" value="<?php echo $this->item->id; ?>" />
					<input type="hidden" name="task" value="" />
					<?php echo HTMLHelper::_('form.token'); ?>
				</form>
			</div>
			<?php if ($this->item->id): ?>
			<div class="tab-pane" id="productAttributesConversionSets">
				<div id="productAttributesConversionSetsData" class="redshopb-product_attribute-conversion_sets-data">
				<?php if (!empty($this->item->conversions)): ?>
					<?php
					$conversionForm = RedshopbModel::getInstance('Conversion', 'RedshopbModel')->getForm();

					foreach ($this->item->conversions as $conversion) :
						$conversionForm->bind($conversion);
						echo RedshopbLayoutHelper::render('product.conversionsets.form', array('form' => $conversionForm, 'conversion' => $conversion));
					endforeach;
					?>
				<?php endif; ?>
				</div>
				<div class="">
					<button class="btn btn-success" id="redshopb-product-attribute-conversion-sets-add" data-product_attribute="<?php echo $this->item->id ?>">
						<i class="icon-plus"></i> <?php echo Text::_('COM_REDSHOPB_PRODUCT_ATTRIBUTE_CONVERSION_SETS_ADD_BTN') ?>
					</button>
				</div>
			</div>
			<?php endif; ?>
		</div>
	</div>
</div>
