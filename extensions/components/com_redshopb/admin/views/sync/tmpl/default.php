<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Templates
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\Registry\Registry;
use Joomla\CMS\Language\Text;

HTMLHelper::_('rjquery.chosen', 'select');
HTMLHelper::_('script', 'system/core.js', false, true);
HTMLHelper::_('behavior.keepalive');

$action          = RedshopbRoute::_('index.php?option=com_redshopb&view=sync');
$saveOrderingUrl = 'index.php?option=com_redshopb&task=sync.saveOrderAjax&tmpl=component';
$listOrder       = $this->state->get('list.ordering');
$listDirn        = $this->state->get('list.direction');
$saveOrder       = $listOrder == 'ordering';
$app             = Factory::getApplication();
$editSync        = $app->getUserState('list.change_sync', 0);

if ($editSync)
{
	HTMLHelper::_('rsortablelist.sortable', 'cronList', 'adminForm', $listDirn, $saveOrderingUrl, false, true);
}

$nullDate = Factory::getDbo()->getNullDate();
$tz       = new DateTimeZone(Factory::getUser()->getParam('timezone', Factory::getConfig()->get('offset')));

$user   = Factory::getUser();
$userId = $user->get('id');
$lang   = Factory::getLanguage();
?>
<script type="text/javascript">
	var b2bLogFollow = false;

	(function ($) {
		<?php if (!$editSync) : ?>
		$(document).ready(function () {
			$('#checkall-toggle').click();

			$('#log-follow-toggler a').click(function (e) {
				e.preventDefault();
				if ($(this).data('status') == 'stopped') {
					b2bLogFollow = true;
					$(this).data('status', 'playing');
					$(this).children('.log-follow-stopped').addClass('hidden');
					$(this).children('.log-follow-playing').removeClass('hidden');
					$(this).children('.log-follow-icon').removeClass('icon-stop').addClass('icon-play');
				}
				else {
					b2bLogFollow = false;
					$(this).data('status', 'stopped');
					$(this).children('.log-follow-stopped').removeClass('hidden');
					$(this).children('.log-follow-playing').addClass('hidden');
					$(this).children('.log-follow-icon').addClass('icon-stop').removeClass('icon-play');
				}
			});
		});

		function syncSelected(){
			$('.progress-log').html('');
			var allSelectedRows = $("#cronList tbody tr input[type=checkbox]:checked").length;
			var progressStep = 100;
			if (allSelectedRows > 0)
				progressStep = 100 / (allSelectedRows+1);

			var nextStep = progressStep;

			$('.main-progress-bar .progress .bar').css('width', nextStep + '%');
			$('.main-progress-bar .progress').addClass('active');
			var selectedRows = [];
			var currentRow = 0;
			$('#cronList tbody tr').each(function (idx, ele) {
				$(ele).find('input[type=checkbox]').each(function(index, element) {
						if ($(element).prop('checked'))
						{
							if (!selectedRows[currentRow - 1] || (selectedRows[currentRow - 1] && selectedRows[currentRow - 1] != ele)) {
								selectedRows[currentRow] = ele;
								currentRow++;
							}
						}
					}
				);

			});

			currentRow = 0;
			syncItem(selectedRows, currentRow, nextStep, progressStep, 20, true);
		}

		function syncItem(selectedRows, currentRow, nextStep, progressStep, persent, startSync)
		{
			var ele = selectedRows[currentRow];
			var rowCheckbox = $(ele).find('.checkboxExecute input[type=checkbox]');
			var fullSync = $('input#full-sync' + rowCheckbox.val());
			var fullSyncPrefix = '';

			if (fullSync.length)
			{
				if (fullSync.prop('checked'))
				{
					fullSyncPrefix = '&fullSync=1';
				}
			}

			var startSyncPrefix = '';
			if (startSync)
			{
				startSyncPrefix = '&startSync=true';
			}

			$.ajax({
				url: 'index.php?option=com_redshopb&task=sync.selectedItem&id=' + $(rowCheckbox).val() + fullSyncPrefix + startSyncPrefix,
				cache: false,
				dataType:'json',
				beforeSend: function (xhr) {
					$(ele).find('.progress .bar').css('width', '0%');
					$(ele).find('.progress .bar-success').css('width', persent+'%');
					$(ele).find('.progress').addClass('active');
				}
			}).always(function (data, textStatus){
				var haveErrors = false;
				if(textStatus == 'timeout' || !data) {
					var msg = '<span class="label label-important" style="white-space: normal;word-break: break-word;"><?php echo Text::_('COM_REDSHOPB_SYNC_ERROR_TIMEOUT', true); ?></span><br />';
					$('.progress-log').append($(ele).find('.cron-name').html() + ': ' + msg);
					$(ele).find('.syncLastInfo').append(msg);
					haveErrors = true;
					if (b2bLogFollow) {
						$('html, body').animate({
							scrollTop: $('#progressLogFooter').offset().top
						}, 50);
					}
				}
				else if (textStatus == 'parsererror') {
				$('.progress-log').append($(ele).find('.cron-name').html() + ': ' + data.responseText);
				$(ele).find('.syncLastInfo').append(msg);
				haveErrors = true;
				if (b2bLogFollow) {
					$('html, body').animate({
						scrollTop: $('#progressLogFooter').offset().top
					}, 50);
				}
				}
				else if (typeof data === 'undefined' || textStatus == 'error') {
					var msg = '<span class="label label-important" style="white-space: normal;word-break: break-word;"><?php echo Text::_('COM_REDSHOPB_SYNC_ERROR_APPLICATION_ERROR', true); ?></span><br />';
					$('.progress-log').append($(ele).find('.cron-name').html() + ': ' + msg);
					$(ele).find('.syncLastInfo').append(msg);
					haveErrors = true;
					if (b2bLogFollow) {
						$('html, body').animate({
							scrollTop: $('#progressLogFooter').offset().top
						}, 50);
					}
				}
				else {
					if (data && data.messages.length > 0)
					{
						$(data.messages).each(function (messageIdx, messageData) {
							var msg = '<span class="label label-' + messageData.type_message + '" style="white-space: normal;word-break: break-word;">' + messageData.message + '</span><br />';
							$('.progress-log').append($(ele).find('.cron-name').html() + ': ' + msg);
							$(ele).find('.syncLastInfo').append(msg);
							if (messageData.type_message == 'important')
							{
								haveErrors = true;
							}

							if (b2bLogFollow) {
								$('html, body').animate({
									scrollTop: $('#progressLogFooter').offset().top
								}, 50);
							}
						});
					} else {
						haveErrors = true;
					}
				}

				if(!haveErrors && data.success != false && typeof data.success !== 'undefined' && typeof data.success['parts'] !== 'undefined')
				{
					var persent = 100 - Math.ceil((100 * data.success['parts'])/data.success['total']);
					$(ele).find('.syncItemsTotal').html(data.success['total']);
					$(ele).find('.progress .bar-success').css('width', persent+'%').find('.badge').html(data.success['total'] - data.success['parts']);
					syncItem(selectedRows, currentRow, nextStep, progressStep, persent, false);
				}
				else
				{
					nextStep += progressStep;
					$('.main-progress-bar .progress .bar').css('width', nextStep + '%');
					$(ele).find('.progress').removeClass('active');
					if(haveErrors || data.success == false){
						var $progress = $(ele).find('.progress'),
							widthProgress = $progress.width(),
							widthSuccess = $progress.find('.bar-success').width(),
							persentError = Math.floor(100 * (widthProgress - widthSuccess)/widthProgress);
						$progress.append('<div class="bar bar-danger" style="width: ' + persentError +'%;"></div>');
					}
					else{
						$(ele).find('.progress .bar-success').css('width', '100%').find('.badge').html('100%');
					}

					currentRow++;
					if (selectedRows.length > currentRow)
						syncItem(selectedRows, currentRow, nextStep, progressStep, 20, true);
					else
						$('.main-progress-bar .progress').removeClass('active');
				}

			});
		}
		<?php endif; ?>
		Joomla.submitbutton = function (task)
		{
			if (task == 'sync.selected')
			{
				syncSelected();
			}
			else if (task == 'sync.clearHashedKeys')
			{
				if(confirm("<?php echo Text::_('COM_REDSHOPB_SYNC_CONFIRM_CLEAR_HASHED_KEYES', true);?>"))
				{
					Joomla.submitform(task);
				}
			}
			else if (task == 'sync.clearAllHashedKeys')
			{
				if(confirm("<?php echo Text::_('COM_REDSHOPB_SYNC_CONFIRM_CLEAR_ALL_HASHED_KEYES', true);?>"))
				{
					Joomla.submitform(task);
				}
			}
			else
			{
				Joomla.submitform(task);
			}
		}
	})(jQuery);
</script>
<form action="<?php echo $action; ?>" name="adminForm" class="adminForm" id="adminForm" method="post">

	<?php if (!$editSync): ?>
	<div class="alert alert-info main-progress-bar">
		<h3><?php echo Text::_('COM_REDSHOPB_SYNC_PROGRESS') ?></h3>
		<div class="progress progress-striped">
			<div class="bar bar-success" style="width: 0%"></div>
		</div>
	</div>
	<?php endif; ?>

	<?php if (empty($this->items)) : ?>
		<?php echo RedshopbLayoutHelper::render('redshopb.common.nodata'); ?>
	<?php else : ?>
		<table class="table table-striped table-hover" id="cronList">
			<thead>
			<tr>
				<th width="1%" class="hidden-phone">
					<input type="checkbox" name="checkall-toggle" value="" id="checkall-toggle"
						   title="<?php echo Text::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)"/>
				</th>
				<th width="1%" class="hidden-phone">
					<?php echo Text::_('COM_REDSHOPB_SYNC_EXECUTE_FULL_SYNC'); ?>
				</th>
				<?php if ($editSync): ?>
				<th width="1%"></th>
				<?php endif; ?>
				<th width="10%" class="nowrap hidden-phone">
					<?php echo Text::_('COM_REDSHOPB_NAME'); ?>
				</th>
				<th width="5%" class="nowrap hidden-phone">
					<?php echo Text::_('COM_REDSHOPB_SYNC_LAST_START_TIME'); ?>
				</th>
				<th width="5%" class="nowrap hidden-phone">
					<?php echo Text::_('COM_REDSHOPB_SYNC_LAST_END_TIME'); ?>
				</th>
				<th width="5%" class="nowrap hidden-phone">
					<?php echo Text::_('COM_REDSHOPB_SYNC_SCHEDULE_NEXT'); ?>
				</th>
				<th width="30%" class="nowrap hidden-phone">
					<?php echo Text::_('COM_REDSHOPB_SYNC_PROGRESS'); ?>
				</th>
			</tr>
			</thead>
			<?php if ($this->items): ?>
				<tbody>
				<?php foreach ($this->items as $i => $item):
					$orderkey          = array_search($item->id, $this->ordering[$item->parent_id]);
					$item->start_time  = ($item->start_time != $nullDate) ? Factory::getDate($item->start_time, 'UTC')->setTimeZone($tz) : false;
					$item->finish_time = ($item->finish_time != $nullDate) ? Factory::getDate($item->finish_time, 'UTC')->setTimeZone($tz) : false;
					$item->next_start  = ($item->next_start != $nullDate) ? Factory::getDate($item->next_start, 'UTC')->setTimeZone($tz) : false;
					$canCheckin        = $user->authorise('core.manage', 'com_checkin') || $item->checked_out == $userId || $item->checked_out == 0;
					$params            = new Registry;
					$params->loadString($item->params);
					$extension = 'plg_rb_sync_' . $item->plugin;
					$lang->load(strtolower($extension), JPATH_ADMINISTRATOR, null, false, true)
					|| $lang->load(strtolower($extension), JPATH_PLUGINS . '/rb_sync/' . $item->plugin, null, false, true);
					$progressPoint    = $item->items_total > 0 ? (int) ($item->items_processed / $item->items_total * 100) : 0;
					$messages         = RedshopbHelperSync::getStatusMessages($item->last_status_messages);
					$numberOfMessages = count($messages);

					if ($numberOfMessages > 1000) :
						$messages = array_splice($messages, $numberOfMessages - 1000);
					endif;

					// Get the parents of item for sorting
					if ($item->level > 1)
					{
						$parentsStr      = "";
						$currentParentId = $item->parent_id;
						$parentsStr      = " " . $currentParentId;

						for ($i2 = 0; $i2 < $item->level; $i2++)
						{
							foreach ($this->ordering as $k => $v)
							{
								$v = implode("-", $v);
								$v = "-" . $v . "-";

								if (strpos($v, "-" . $currentParentId . "-") !== false)
								{
									$parentsStr     .= " " . $k;
									$currentParentID = $k;
									break;
								}
							}
						}
					}
					else
					{
						$parentsStr = "";
					}
					?>
					<tr sortable-group-id="<?php echo $item->parent_id; ?>" item-id="<?php echo $item->id ?>" parents="<?php echo $parentsStr ?>" level="<?php echo $item->level ?>">
						<td class="checkboxExecute">
							<?php echo HTMLHelper::_('rgrid.id', $i, $item->id); ?>
						</td>
						<td>
							<?php
							if ($params->get('can_use_full_sync', 0) == 1):
							?>
								<input type="checkbox" value="<?php echo $item->id; ?>" name="full-sync[]" id="full-sync<?php echo $item->id; ?>"
									   onclick="Joomla.isChecked(this.checked, document.getElementById('adminForm'));" />
							<?php
							endif;
							?>
						</td>
						<?php if ($editSync): ?>
						<td>
							<span class="sortable-handler hasTooltip">
								<i class="icon-move"></i>
							</span>
							<input type="text" style="display:none" name="order[]" size="5" value="<?php echo $orderkey + 1; ?>" />
						</td>
						<?php endif; ?>
						<td>
							<?php if ($item->checked_out) : ?>
								<?php echo HTMLHelper::_('rgrid.checkedout', $i, $item->checked_out > 0 ? Factory::getUser($item->checked_out)->get('name') : '', $item->checked_out_time, 'sync.', $canCheckin); ?>
							<?php endif; ?>
							<span class="nowrap">
								<label for="cb<?php echo $i ?>">
									<?php echo str_repeat('<span class="gi">|&mdash;</span>', $item->level - 1) ?>
									<span class="cron-name"><?php echo Text::_('COM_REDSHOPB_SYNC_' . $this->escape($item->plugin) . '_' . $this->escape($item->name)); ?></span>
								</label>
							</span>
						</td>
						<td>
							<?php if (!$item->start_time) : ?>
								<span class="badge badge-important"> <?php echo Text::_('JNO') ?> </span>
							<?php else : ?>
								<span class="badge badge-success"> <?php echo $this->escape($item->start_time); ?> </span>
							<?php endif; ?>
						</td>
						<td>
							<?php if (!$item->finish_time) : ?>
								<span class="badge badge-important"> <?php echo Text::_('JNO') ?> </span>
							<?php else : ?>
								<span class="badge badge-success"> <?php echo $this->escape($item->finish_time); ?> </span>
							<?php endif; ?>
						</td>
						<td>
							<?php if (!$item->next_start) : ?>
								<span class="badge badge-important"> <?php echo Text::_('JNO') ?> </span>
							<?php else : ?>
								<span class="badge badge-success"> <?php echo $this->escape($item->next_start); ?> </span>
							<?php endif; ?>
						</td>
						<td class="accordion" id="accordionInfo<?php echo $item->id; ?>">
							<button type="button" id="syncInfoButton" class="btn btn-info btn-small pull-left accordion-toggle" style="margin-right: 5px;"
									data-toggle="collapse" data-parent="#accordionInfo<?php echo $item->id; ?>" href="#collapseSync<?php echo $item->id; ?>"
							>
								<i class="icon-info"></i>
							</button>
							<span class="badge pull-right syncItemsTotal"><?php echo $this->escape($item->items_total); ?></span>
							<div class="progress progress-striped">
								<div class="bar bar-success" style="width: <?php echo $progressPoint; ?>%; text-align: right;">
									<span class="badge"><?php echo $item->items_processed; ?></span>
								</div>
							</div>
							<div id="collapseSync<?php echo $item->id; ?>" class="accordion-body collapse">
								<strong><?php echo Text::_('COM_REDSHOPB_SYNC_LAST_SYNC_INFO', true); ?></strong><br />
								<div class="syncLastInfo">
									<?php if (is_array($messages)) : ?>
										<?php foreach ($messages as $msg) : ?>
											<?php
												$explodeMessage = explode('-', $msg);
												$msgType        = trim(array_pop($explodeMessage));
											?>
											<label class="label label-<?php echo $msgType; ?>" style="white-space: normal;word-break: break-word;">
												<?php
												$date     = trim($explodeMessage[0]);
												$datetime = DateTime::createFromFormat('Y.m.d H:i:s', $date, new \DateTimeZone('UTC'));

												if ($date && $datetime)
												{
													unset($explodeMessage[0]);
													$datetime->setTimezone($tz);
													echo $datetime->format('Y.m.d H:i:s') . ' - ' . implode('-', $explodeMessage);
												}
												else
												{
													echo implode('-', $explodeMessage);
												}
												?>
											</label><br />
										<?php endforeach;?>
									<?php endif; ?>
								</div>
							</div>
						</td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			<?php endif; ?>
		</table>
	<?php endif; ?>

	<?php if (!$editSync): ?>
	<div class="well">
		<h3><?php echo Text::_('COM_REDSHOPB_SYNC_PROGRESS_LOG') ?></h3>
		<div class="progress-log"></div>
		<span id="progressLogFooter"></span>
	</div>
	<?php endif; ?>
	<div>
		<input type="hidden" name="task" value="">
		<input type="hidden" name="boxchecked" value="0">
		<?php echo HTMLHelper::_('form.token'); ?>
	</div>
	<div id="log-follow-toggler">
		<a href="#" data-status="stopped">
			<span class="log-follow-stopped">
				<?php echo Text::_('COM_REDSHOPB_SYNC_FOLLOW_LOG_STOPPED'); ?>
			</span>
			<span class="log-follow-playing hidden">
				<?php echo Text::_('COM_REDSHOPB_SYNC_FOLLOW_LOG_PLAYING'); ?>
			</span>
			<i class="log-follow-icon icon-stop"></i>
		</a>
	</div>
</form>
