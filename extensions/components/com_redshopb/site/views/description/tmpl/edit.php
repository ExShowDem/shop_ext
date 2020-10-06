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

// HTML helpers
HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('rjquery.framework');
HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('vnrbootstrap.tooltip');
HTMLHelper::_('rjquery.chosen', 'select');

$input = Factory::getApplication()->input;
$url   = 'index.php?option=com_redshopb&view=description';

if ($this->productId)
{
	$url .= '&product_id=' . (int) $this->productId;
}

$return = $input->getBase64('return');

if ($return)
{
	$url .= '&return=' . $return;
}

$action = RedshopbRoute::_($url);

echo RedshopbBrowserBreadcrumbs::renderBreadcrumbs();
?>
<script type="text/javascript">
	(function($){
		Joomla.submitbutton = function(task)
		{
			if (task == "description.cancel"
				|| document.formvalidator.isValid(document.getElementById("adminForm")))
			{
				<?php echo $this->form->getField('description')->save() ?>
				Joomla.submitform(task, document.getElementById("adminForm"));
			}
		};
	})(jQuery);
</script>
<div class="redshopb-description">
	<div class="row">
		<div class="col-md-12">
			<form action="<?php echo $action; ?>" method="post" name="adminForm" id="adminForm"
				  class="form-validate form-horizontal redshopb-description-form">
				<div class="form-group">
					<div class="control-label">
						<?php echo $this->form->getLabel('main_attribute_value_id'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('main_attribute_value_id'); ?>
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
				<?php if ($this->syncReference != '') :
				?>
					<div class="form-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('sync_related_id'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('sync_related_id'); ?>
						</div>
					</div>
				<?php
				endif;
				?>
				<!-- hidden fields -->
				<input type="hidden" name="option" value="com_redshopb">
				<input type="hidden" name="id" value="<?php echo $this->item->id; ?>">
				<input type="hidden" name="task" value="">
				<?php echo $this->form->getInput('product_id');?>
				<?php echo HTMLHelper::_('form.token'); ?>
			</form>
		</div>
	</div>
</div>
