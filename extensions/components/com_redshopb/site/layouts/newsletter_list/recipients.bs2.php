<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Views
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Session\Session;

$newsLetterListId = $displayData['newsletterListId'];
$subscribers      = $displayData['subscribers'];
$builderValue     = $displayData['builderValue'];
$subscribersCount = (!empty($subscribers) && is_array($subscribers)) ? count($subscribers) : 0;
?>

<script type="text/javascript">
	function updateSegmentationQuery(sqlQuery, jsonQuery) {
		(function($){
			if (sqlQuery != '') {
				// Update segmentation query string
				$('#jform_segmentation_query').val(sqlQuery);
				$('#jform_segmentation_json').val(jsonQuery);

				// Get newsletter id
				var currentId = $('#newsletter_list_id').val();

				// Perform the ajax request
				$.ajax({
					url: '<?php echo Uri::root(); ?>index.php?option=com_redshopb&task=newsletter_list.ajaxUpdateSegmentationQuery',
					dataType: "json",
					method: "POST",
					data: {
						"id": currentId,
						"<?php echo Session::getFormToken() ?>": 1,
						"criteria": sqlQuery,
						"criteria_json": jsonQuery
					},
					cache: false
				}).done(function (data) {
					// Update results subscribers count
					$('#newsletterListRecipientsSubscribersCount').text(data);
					$('a[href="#newsletterListRecipientsSubscribers"]').click();
				}).error(function (error) {
				});
			}
		})(jQuery);
	}

	function ajaxReloadSubscribers() {
		(function($) {
			// Get newsletter id
			var currentId = $('#newsletter_list_id').val();

			// Perform the ajax request
			$.ajax({
				url: "<?php echo Uri::root(); ?>index.php?option=com_redshopb&view=newsletter_list&task=newsletter_list.ajaxLoadSubscribers",
				type: 'POST',
				data: {
					"id": currentId,
					"<?php echo Session::getFormToken() ?>": 1
				},
				cache: false
			}).done(function (data) {
				$('#newsletterListRecipientsSubscribersContent').html(data);
				$('select').chosen();
				$('.chzn-search').hide();
				$('.hasTooltip').tooltip({"animation": true, "html": true, "placement": "top", "selector": false, "title": "", "trigger": "hover focus", "delay": 0, "container": false});
				initFootableRedshopb();
			}).error(function (error) {
			});
		})(jQuery);
	}
</script>

<script type="text/javascript">
	(function($) {
		$(document).ready(function() {
			ajaxReloadSubscribers();

			$('a[href="#newsletterListRecipientsSubscribers"]').on('show', function(event) {
				ajaxReloadSubscribers();
			});
		});
	})(jQuery);
</script>

<div class="redshopb-newsletter_list-recipients">
	<div class="row-fluid">
		<div class="span2">
			<!-- Recipients Tab Menu -->
			<ul class="nav nav-pills" id="newsletterListRecipientsTab">
				<li>
					<a href="#newsletterListRecipientsFilter" data-toggle="tab">
						<?php echo Text::_('COM_REDSHOPB_NEWSLETTER_LIST_FILTER_TITLE'); ?>
					</a>
				</li>
				<li class="active">
					<a href="#newsletterListRecipientsSubscribers" data-toggle="tab">
						<?php echo Text::_('COM_REDSHOPB_NEWSLETTER_LIST_RECIPIENTS_TITLE') ?> <span class="badge badge-info" id="newsletterListRecipientsSubscribersCount"><?php echo $subscribersCount ?></span>
					</a>
				</li>
			</ul>
		</div>
		<div class="span10">
			<!-- Recipients Tab Content -->
			<div class="tab-content">
				<div class="tab-pane" id="newsletterListRecipientsFilter">
					<?php echo RedshopbHelperSegmentation_Query::render(array(), 'updateSegmentationQuery', $builderValue) ?>
				</div>
				<div class="tab-pane active" id="newsletterListRecipientsSubscribers">
					<div id="newsletterListRecipientsSubscribersContent">
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
