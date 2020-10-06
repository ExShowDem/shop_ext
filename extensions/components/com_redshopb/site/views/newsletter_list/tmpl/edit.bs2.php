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

$isNew = (int) $this->item->id <= 0;

// HTML helpers
HTMLHelper::_('vnrbootstrap.tooltip');
HTMLHelper::_('rjquery.chosen', 'select');
HTMLHelper::_('rsearchtools.main');

RedshopbHtml::loadFooTable();

$isNew = ($this->item->id) ? false : true;

$tab = Factory::getApplication()->input->getString('tab', '');

// Load jQuery Builder library
RHelperAsset::load('lib/query-builder/query-builder.standalone.min.js', 'com_redshopb');
RHelperAsset::load('lib/query-builder/query-builder.default.min.css', 'com_redshopb');

// Variables
$action = RedshopbRoute::_('index.php?option=com_redshopb&view=newsletter_list&layout=edit&id=' . $this->item->id);

echo RedshopbBrowserBreadcrumbs::renderBreadcrumbs();
?>

<?php if (!$isNew): ?>
<script type="text/javascript">
	var isLoadedRecipients = false;

	(function($) {
		$(document).ready(function() {
			// Load recipient tab
			$('a[href="#recipients"]').on('show', function(event){
				if (isLoadedRecipients) {
					return true;
				}
				else {
					// Perform the ajax request
					$.ajax({
						url: '<?php echo Uri::root() ?>index.php?option=com_redshopb&view=newsletter_list&task=newsletter_list.ajaxLoadSegmentation',
						type: 'POST',
						data: {
							"<?php echo Session::getFormToken() ?>": 1,
							"id": <?php echo $this->item->id ?>
						},
						beforeSend: function (xhr) {
							$('.recipients-content .spinner').show();
							$('#newsletterListTab').addClass('opacity-40');
						}
					}).done(function (data) {
						isLoadedRecipients = true;
						$('#newsletterListTab').removeClass('opacity-40');
						$('.recipients-content').html(data);
					}).error(function (error) {
						isLoadedRecipients = false;
						$('#newsletterListTab').removeClass('opacity-40');
						$('.recipients-content .spinner').hide();
						$('<p>').addClass('text-error')
							.html(Joomla.JText._('COM_REDSHOPB_NEWSLETTER_LIST_ERROR_LOAD_RECIPIENTS'))
							.appendTo($('.recipients-content'));
					});
				}
			});
		});
	})(jQuery);
</script>
<?php endif; ?>

<?php if ($tab): ?>
<script type="text/javascript">
	(function ($) {
		$(document).ready(function () {
			$('#newsletterListTab a[href="#<?php echo $tab ?>"]').tab('show');
		});
	})(jQuery);
</script>
<?php endif; ?>

<div class="row-fluid">
	<!-- Tab -->
	<ul class="nav nav-tabs" id="newsletterListTab">
		<li class="active">
			<a href="#details" data-toggle="tab">
				<?php echo Text::_('COM_REDSHOPB_DETAILS'); ?>
			</a>
		</li>
		<?php if (!$isNew): ?>
		<li>
			<a href="#recipients" data-toggle="tab">
				<?php echo Text::_('COM_REDSHOPB_NEWSLETTER_LIST_RECIPIENTS_TITLE') ?>
			</a>
		</li>
		<?php endif; ?>
	</ul>

	<!-- Tab content -->
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
					</div>
				</div>
			</div>
			<?php if (!$isNew): ?>
			<div class="tab-pane" id="recipients">
				<div class="container-fluid recipients-content">
					<div class="spinner pagination-centered">
						<?php echo HTMLHelper::image('media/com_redshopb/images/ajax-loader.gif', '') ?>
					</div>
				</div>
			</div>
			<?php endif; ?>

			<!-- hidden fields -->
			<?php echo $this->form->getInput('segmentation_query'); ?>
			<?php echo $this->form->getInput('segmentation_json'); ?>
			<input type="hidden" name="option" value="com_redshopb">
			<input type="hidden" id="newsletter_list_id" name="id" value="<?php echo $this->item->id; ?>">
			<input type="hidden" name="task" value="">
			<?php echo HTMLHelper::_('form.token'); ?>
		</div>
	</form>
</div>
