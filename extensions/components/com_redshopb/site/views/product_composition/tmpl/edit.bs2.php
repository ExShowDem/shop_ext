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
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Session\Session;

// HTML helpers
HTMLHelper::_('rjquery.framework');
HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('vnrbootstrap.tooltip');
HTMLHelper::_('rjquery.chosen', 'select');


$url = 'index.php?option=com_redshopb&view=product_composition';

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

echo RedshopbBrowserBreadcrumbs::renderBreadcrumbs();
?>
<script type="text/javascript">
	function updateMainAttributes(product_id)
	{
		var attributes = jQuery('#attributes');
		jQuery.ajax({
			url: '<?php echo Uri::root(); ?>index.php?option=com_redshopb&task=product_composition.ajaxgetmainattributes&product_id=' + product_id,
			cache: false,
			dataType:'html',
			type: 'POST',
			data: '<?php echo Session::getFormToken();?>=1',
			beforeSend: function (xhr)
			{
				attributes.html('');
				jQuery('#redshopb-attributes-loading').show();
			}
		}).done(function (data)
		{
			jQuery('#redshopb-attributes-loading').hide();
			attributes.html(data);
			jQuery('select').chosen();
		});
	}

	<?php if ($this->isNew): ?>
		jQuery(document).ready(function()
		{
			var product_id = jQuery('#jform_product_id').val();
			updateMainAttributes(product_id);
		});
	<?php endif;?>
</script>

<div class="redshopb-product_composition">
	<form action="<?php echo $action; ?>" method="post" name="adminForm" id="adminForm" class="form-validate form-horizontal redshopb-product_composition-form">
		<?php echo $this->form->renderField('product_id');?>
		<div>
			<div id="attributes">
				<div class="control-group">
					<div class="control-label">
						<?php echo $this->form->getLabel('flat_attribute_value_id'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('flat_attribute_value_id'); ?>
					</div>
				</div>
			</div>
			<div class="control-group" id="redshopb-attributes-loading" style="display: none">
				<div class="controls">
					<div>
						<?php echo HTMLHelper::image('media/com_redshopb/images/ajax-loader.gif', ''); ?>
					</div>
				</div>
			</div>
		</div>

		<div class="control-group">
			<div class="control-label">
				<?php echo $this->form->getLabel('type'); ?>
			</div>
			<div class="controls">
				<?php echo $this->form->getInput('type'); ?>
			</div>
		</div>

		<div class="control-group">
			<div class="control-label">
				<?php echo $this->form->getLabel('quality'); ?>
			</div>
			<div class="controls">
				<?php echo $this->form->getInput('quality'); ?>
			</div>
		</div>

		<!-- hidden fields -->
		<input type="hidden" name="option" value="com_redshopb">
		<input type="hidden" name="id" value="<?php echo $this->item->id; ?>">
		<input type="hidden" name="task" value="">
		<?php echo HTMLHelper::_('form.token'); ?>
	</form>
</div>
