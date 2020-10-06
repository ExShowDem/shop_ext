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

$isNew = (int) $this->item->id <= 0;

// HTML helpers
HTMLHelper::_('vnrbootstrap.tooltip');
HTMLHelper::_('rjquery.chosen', 'select');
HTMLHelper::_('behavior.formvalidator');

$isNew = ($this->item->id) ? false : true;

// Variables
$action = RedshopbRoute::_('index.php?option=com_redshopb&view=newsletter&layout=edit&id=' . $this->item->id);

echo RedshopbBrowserBreadcrumbs::renderBreadcrumbs();
?>
<script type="text/javascript">
	(function($){
		Joomla.submitbutton = function(task)
		{
			if (task == "newsletter.cancel" || document.formvalidator.isValid(document.getElementById("adminForm")))
			{
				<?php echo $this->form->getField('body')->save() ?>
				Joomla.submitform(task, document.getElementById("adminForm"));
			}
		};
	})(jQuery);
</script>
<div class="row-fluid">
	<form action="<?php echo $action; ?>" method="post" name="adminForm" id="adminForm" class="form-validate form-horizontal">
		<div class="tab-content">
			<div class="tab-pane active" id="details">
				<div class="row-fluid">
					<div class="span12 adapt-inputs">
						<div class="control-group">
							<div class="control-label">
								<?php echo $this->form->getLabel('name'); ?>
							</div>
							<div class="controls">
								<?php echo $this->form->getInput('name'); ?>
							</div>
						</div>
						<div class="control-group">
							<div class="control-label">
								<?php echo $this->form->getLabel('alias'); ?>
							</div>
							<div class="controls">
								<?php echo $this->form->getInput('alias'); ?>
							</div>
						</div>
						<div class="control-group">
							<div class="control-label">
								<?php echo $this->form->getLabel('state'); ?>
							</div>
							<div class="controls">
								<?php echo $this->form->getInput('state'); ?>
							</div>
						</div>
						<div class="control-group">
							<div class="control-label">
								<?php echo $this->form->getLabel('template_id'); ?>
							</div>
							<div class="controls">
								<?php echo $this->form->getInput('template_id'); ?>
							</div>
						</div>
						<div class="control-group">
							<div class="control-label">
								<?php echo $this->form->getLabel('newsletter_list_id'); ?>
							</div>
							<div class="controls">
								<?php echo $this->form->getInput('newsletter_list_id'); ?>
							</div>
						</div>
						<div class="control-group">
							<div class="control-label">
								<?php echo $this->form->getLabel('subject'); ?>
							</div>
							<div class="controls">
								<?php echo $this->form->getInput('subject'); ?>
							</div>
						</div>
						<div class="control-group">
							<div class="control-label">
								<?php echo $this->form->getLabel('body'); ?>
							</div>
							<div class="controls">
								<?php echo $this->form->getInput('body'); ?>
							</div>
						</div>
					</div>
				</div>
			</div>
			<!-- hidden fields -->
			<input type="hidden" name="task" value="">
			<?php echo HTMLHelper::_('form.token'); ?>
		</div>
	</form>
</div>
