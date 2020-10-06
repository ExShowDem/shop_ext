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

$tab = Factory::getApplication()->input->getString('tab', '');

// HTML helpers
HTMLHelper::_('vnrbootstrap.tooltip');
HTMLHelper::_('rjquery.chosen', 'select');
HTMLHelper::_('rsearchtools.main');
RHtml::_('vnrbootstrap.renderModal', 'newletterSendModal');

// Variables
$action = RedshopbRoute::_('index.php?option=com_redshopb&view=newsletter&layout=edit&id=' . $this->item->id);

RedshopbHelperNewsletter_List::mailPreviewScriptInit('newsletterPreviewArea', null, $this->item->subject);

echo RedshopbBrowserBreadcrumbs::renderBreadcrumbs();
?>
<script type="text/javascript">
	var isLoadedRecipients = false;

	(function($) {
		$(document).ready(function() {
			// Load recipient tab
			$('a[href="#recipients"]').on('click', function(event){
				if (isLoadedRecipients) {
					return true;
				}
				else {
					// Perform the ajax request
					$.ajax({
						url: '<?php echo Uri::root(); ?>index.php?option=com_redshopb&view=newsletter&task=newsletter.ajaxLoadSubscribers',
						type: 'POST',
						data: {
							"id": <?php echo $this->item->id ?>,
							"<?php echo Session::getFormToken() ?>": 1,
							"newsletter_list_id": <?php echo $this->item->newsletter_list_id ?>
						},
						beforeSend: function (xhr) {
							$('.recipients-content .spinner').show();
							$('#newsletterTab').addClass('opacity-40');
						}
					}).done(function (data) {
						isLoadedRecipients = true;
						$('#newsletterTab').removeClass('opacity-40');
						$('.recipients-content').html(data);
					}).error(function (error) {
						isLoadedRecipients = false;
						$('#newsletterTab').removeClass('opacity-40');
						$('.recipients-content .spinner').hide();
						$('<p>').addClass('text-error')
							.html('<?php echo Text::_('COM_REDSHOPB_NEWSLETTER_LIST_ERROR_LOAD_RECIPIENTS'); ?>')
							.appendTo($('.recipients-content'));
					});
				}
			});

			<?php if ($tab): ?>
			$('#newsletterTab a[href="#<?php echo $tab ?>"]').tab('show');
			<?php endif; ?>
		});
	})(jQuery);
</script>
<div class="row">
	<!-- Tab -->
	<ul class="nav nav-tabs" id="newsletterTab">
		<li class="active">
			<a href="#details" data-toggle="tab">
				<?php echo Text::_('COM_REDSHOPB_NEWSLETTER_PREVIEW_DETAIL'); ?>
			</a>
		</li>
		<li>
			<a href="#recipients" data-toggle="tab">
				<?php echo Text::_('COM_REDSHOPB_NEWSLETTER_LIST_RECIPIENTS_TITLE') ?>
			</a>
		</li>
	</ul>

	<div class="tab-content">
		<div class="tab-pane active" id="details">
			<form action="<?php echo $action; ?>" method="post" name="adminForm" id="adminForm" class="form-validate form-horizontal">
				<div class="newsletter_body" id="newsletterPreviewArea">
					<?php echo $this->item->body; ?>
				</div>
				<input type="hidden" name="task" value="">
				<input type="hidden" name="id" value="<?php echo $this->item->id; ?>">
				<?php echo HTMLHelper::_('form.token'); ?>
			</form>
		</div>
		<div class="tab-pane" id="recipients">
			<div class="container-fluid recipients-content">
				<div class="spinner pagination-centered">
					<?php echo HTMLHelper::image('media/com_redshopb/images/ajax-loader.gif', '') ?>
				</div>
			</div>
		</div>
	</div>
</div>
