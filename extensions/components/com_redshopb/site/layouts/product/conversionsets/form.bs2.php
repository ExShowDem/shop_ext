<?php
/**
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Session\Session;

extract($displayData);

$action    = (isset($action)) ? $action : RedshopbRoute::_('index.php?option=com_redshopb&task=conversion.ajaxSaveConversion');
$class     = (!empty($conversion->id)) ? 'well' : 'alert alert-block';
$imagePath = false;
$uniqueId  = time();

if (!empty($conversion->id))
{
	$uniqueId .= $conversion->id;
}

$uniqueId = md5($uniqueId);

// Make form fields has unique id
foreach ($form->getFieldset() as $field)
{
	$form->setFieldAttribute($field->__get('fieldname'), 'id', $field->__get('fieldname') . '-' . $uniqueId);
}

if (!empty($conversion->image))
{
	$imagePath = RedshopbHelperThumbnail::originalToResize($conversion->image, 150, 80, 100, 0, 'prod_attr_conv');
}
?>

<script type="text/javascript">
	(function($){
		$(document).ready(function(){
			rRadioGroupButtonsSet('#conversion-set-<?php echo $uniqueId ?>');
			rRadioGroupButtonsEvent('#conversion-set-<?php echo $uniqueId ?>');
			$('.hasTooltip').tooltip({"html": true,"container": "body"});

			// Form submit
			$('#conversion-set-<?php echo $uniqueId ?>').submit(function(event){
				event.preventDefault();
				var formData = new FormData($(this)[0]);

				$.ajax({
					url: $(this).attr('action'),
					data: formData,
					async: false,
					contentType: false,
					processData: false,
					cache: false,
					type: 'POST',
					success: function(data) {
						if (data != "0") {
							$("#jform_id_<?php echo $uniqueId ?>").val(data);

							if ($("#conversion-set-<?php echo $uniqueId ?> .redshopb-product_attribute-conversion_sets").hasClass('alert alert-block')) {
								$("#conversion-set-<?php echo $uniqueId ?> .redshopb-product_attribute-conversion_sets").removeClass('alert alert-block').addClass('well');
							}

							if (typeof $("#image-disable-<?php echo $uniqueId ?>") != 'undefined') {
								$("#image-disable-<?php echo $uniqueId ?>").remove();
								$("#image-upload-wrapper-<?php echo $uniqueId ?>").removeClass('hidden');
							}
						}
					}
				});
			});

			// Conversion remove
			$("#redshopb-conversion_sets-remove-<?php echo $uniqueId ?>").click(function(event){
				event.preventDefault();

				var convId = $("#jform_id_<?php echo $uniqueId ?>").val();

				// If this is not exist conversion, just remove HTML code
				if (convId == "0") {
					$("#conversion-set-<?php echo $uniqueId ?>").hide('fast', function() {
						$(this).remove();
					});

					return false;
				}

				var convAttribute = $("#jform_product_attribute_id_<?php echo $uniqueId ?>").val();

				$.ajax({
					url: "<?php echo Uri::root(); ?>index.php?option=com_redshopb&task=conversion.ajaxRemoveConversion",
					data: {
						"product_attribute_id": convAttribute,
						"id": convId,
						"<?php echo Session::getFormToken(); ?>": 1
					},
					cache: false,
					type: 'POST'
				}).done(function (data) {
					if (data == "1") {
						$("#conversion-set-<?php echo $uniqueId ?>").hide('fast', function() {
							$(this).remove();
						});
					}
				});

				return false;
			});
		});
	})(jQuery);
</script>

<form method="post" name="conversionForm" id="conversion-set-<?php echo $uniqueId ?>" class="form form-horizontal form-validate conversion-set-form" action="<?php echo $action ?>">
	<div class="redshopb-product_attribute-conversion_sets <?php echo $class ?>">
		<div class="row-fluid">
			<div class="span10">
				<div class="control-group">
					<div class="control-label">
						<?php echo $form->getLabel('name'); ?>
					</div>
					<div class="controls">
						<?php echo $form->getInput('name'); ?>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo $form->getLabel('imageFileUpload'); ?>
					</div>
					<div class="controls">
						<?php $wrapperClass = ''; ?>

						<?php if (empty($conversion->id)): ?>
							<?php $wrapperClass = 'hidden'; ?>
							<input type="text" disabled="disabled" class="input input-xlarge disabled" id="image-disable-<?php echo $uniqueId ?>"
								value="<?php echo Text::_('COM_REDSHOPB_PRODUCT_ATTRIBUTE_CONVERSION_FOR_UPLOAD_IMAGE_SAVE_FIRST'); ?>" />
						<?php endif; ?>
						<div id="image-upload-wrapper-<?php echo $uniqueId ?>" class="<?php echo $wrapperClass ?>">
							<?php echo $form->getInput('imageFileUpload'); ?>

							<?php if ($imagePath): ?>
								<img src="<?php echo $imagePath ?>" />
							<?php endif; ?>
						</div>
						<?php echo $form->getInput('image'); ?>
					</div>
				</div>
				<div class="control-group" id="group-<?php echo $uniqueId ?>">
					<div class="control-label">
						<?php echo $form->getLabel('default'); ?>
					</div>
					<div class="controls">
						<?php echo $form->getInput('default'); ?>
					</div>
				</div>
			</div>
			<div class="span2">
				<button type="submit" class="btn btn-primary btn-block">
					<i class="icon-save"></i> <?php echo Text::_('JAPPLY') ?>
				</button>
				<button type="button" id="redshopb-conversion_sets-remove-<?php echo $uniqueId ?>" class="btn btn-danger btn-block">
					<i class="icon-remove"></i> <?php echo Text::_('JREMOVE') ?>
				</button>
			</div>
		</div>
	</div>
	<!-- hidden fields -->
	<?php echo $form->getInput('id'); ?>
	<?php echo $form->getInput('product_attribute_id'); ?>
	<?php echo HTMLHelper::_('form.token'); ?>
</form>
