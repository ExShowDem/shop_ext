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
use Joomla\CMS\Session\Session;
use Joomla\CMS\Date\Date;
?>
<?php
if ($this->wallet != null)
	:
	if ($this->wallet->start != '0000-00-00 00:00:00')
	{
		$startDate = new Date($this->wallet->start);
		$startDate = $startDate->year . '-' . $startDate->month . '-' . $startDate->day;
	}
	else
	{
		$startDate = null;
	}

	if ($this->wallet->end != '0000-00-00 00:00:00')
	{
		$endDate = new Date($this->wallet->end);
		$endDate = $endDate->year . '-' . $endDate->month . '-' . $endDate->day;
	}
	else
	{
		$endDate = null;
	}
?>
<script type="text/javascript">
	(function($){
		$(document).ready(function () {
			start = $('#jform_start_date');
			<?php
			if (!is_null($startDate))
					:
			?>
			startDate = '<?php echo $startDate; ?>';
			<?php
			endif;
			?>
			<?php
			if (!is_null($endDate))
					:
			?>
			endDate = '<?php echo $endDate; ?>';
			<?php
			endif;
			?>
			end   = $('#jform_end_date');
			start.datepicker('option',
				{
					"changeMonth": true,
					"changeYear": true,
					"dateFormat": 'yy-mm-dd',
					onClose: function(selectedDate) {
						$("#jform_end_date").datepicker("option", "minDate", selectedDate);
					}
				}
			);
			end.datepicker('option',
				{
					"changeMonth": true,
					"changeYear": true,
					"dateFormat": 'yy-mm-dd',
					onClose: function(selectedDate) {
						$("#jform_start_date").datepicker("option", "maxDate", selectedDate);
					}
				}
			);
			<?php
			if (!is_null($startDate))
					:
			?>
			start.datepicker('setDate', startDate);
			end.datepicker("option", "minDate", startDate);
			<?php
			endif;
			?>
			<?php
			if (!is_null($endDate))
					:
			?>
			end.datepicker('setDate', endDate);
			start.datepicker("option", "maxDate", endDate);
			<?php
			endif;
			?>
		});
	})(jQuery);

	function btnWalletSet() {
		var val = parseFloat(jQuery('#assign-amount-id').val());
		if (isNaN(val)) val = 0;
		jQuery('#assign-amount-id').val(val);

		ajaxData = {
			'id' : <?php echo $this->item->id ?>,
			'amount' : val,
			'currency_id' : jQuery('#assign_currency_id option:selected').val(),
			"<?php echo Session::getFormToken() ?>": 1
		};
		jQuery.ajax({
			url : "<?php echo RedshopbRoute::_('index.php?option=com_redshopb&task=user.ajaxsetcredit') ?>",
			type: "Post",
			dataType: "html",
			data : ajaxData,
			cache : false,
			beforeSend: function (xhr) {
				jQuery('.spinner').show();
			},
			success: function(data) {
				jQuery('#user-wallet').html(data);
			}
		}).always(function () {
			jQuery('.spinner').hide();
		})
	}

	function btnWalletReset() {
		var val = parseFloat(jQuery('#assign-amount-id').val());
		if (isNaN(val)) val = 0;
		jQuery('#assign-amount-id').val(val);

		ajaxData = {
			'id' : <?php echo $this->item->id ?>,
			'currency_id' : jQuery('#assign_currency_id option:selected').val(),
			"<?php echo Session::getFormToken() ?>": 1
		};
		jQuery.ajax({
			url : "<?php echo RedshopbRoute::_('index.php?option=com_redshopb&task=user.ajaxresetcredit') ?>",
			type : "POST",
			data : ajaxData,
			dataType : 'html',
			cache : false,
			beforeSend: function (xhr) {
				jQuery('.spinner').show();
			},
			success: function(data) {
				jQuery('#user-wallet').html(data);
			}
		}).always(function () {
			jQuery('.spinner').hide();
		})
	}

	function btnWalletCredit() {
		var val = parseFloat(jQuery('#assign-amount-id').val());
		if (isNaN(val)) val = 0;
		jQuery('#assign-amount-id').val(val);

		ajaxData = {
			'id' : <?php echo $this->item->id ?>,
			'amount' : val,
			'currency_id' : jQuery('#assign_currency_id option:selected').val(),
			"<?php echo Session::getFormToken() ?>": 1
		};
		jQuery.ajax({
			url : "<?php echo RedshopbRoute::_('index.php?option=com_redshopb&task=user.ajaxcredit') ?>",
			type : "POST",
			data : ajaxData,
			dataType : 'html',
			cache : false,
			beforeSend: function (xhr) {
				jQuery('.spinner').show();
			},
			success: function(data) {
				jQuery('#user-wallet').html(data);
			}
		}).always(function () {
			jQuery('.spinner').hide();
		})
	}
</script>
<div class="redshopb-user-wallet">
	<div class="container-fluid">
		<div class="row">
			<div class="spinner pagination-centered" style="display:none">
				<?php echo HTMLHelper::image('media/com_redshopb/images/ajax-loader.gif', '') ?>
			</div>
		</div>
		<div class="row">
			<div class="col-md-6">
				<div class="well">
					<div class="container-fluid">
						<div class="row form-horizontal">
							<legend style="margin-bottom: 0px;"><?php echo Text::_('COM_REDSHOPB_USER_CREDIT_DATES'); ?></legend>
							<div class="form-group">
								<div class="control-label">
									<?php echo $this->form->getLabel('start_date'); ?>
								</div>
								<div class="controls">
									<?php echo $this->form->getInput('start_date'); ?>
								</div>
							</div>
							<div class="form-group">
								<div class="control-label">
									<?php echo $this->form->getLabel('end_date'); ?>
								</div>
								<div class="controls">
									<?php echo $this->form->getInput('end_date'); ?>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="well">
					<?php
						echo RedshopbLayoutHelper::render(
							'user.wallet.actions',
							array(
								'formName' => 'adminForm',
								'view' => 'list',
								'modal' => false
							)
						);
					?>
				</div>
			</div>
			<div class="col-md-6" id="user-wallet">
				<div class="well">
					<div class="row">
						<?php
						echo RedshopbLayoutHelper::render(
							'user.wallet.default',
							array(
								'wallet' => $this->wallet
							)
						);
						?>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<?php
endif;
