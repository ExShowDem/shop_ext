<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;

$data = $displayData;

$formName = $data['formName'];
$modal    = true;

if (isset($data['modal']))
{
	$modal = $data['modal'];
}

HTMLHelper::_('rjquery.chosen', 'select');
HTMLHelper::_('vnrbootstrap.modal', 'walletModal');
RHelperAsset::load('rdatepicker.css', 'redcore');
$document = Factory::getDocument();
$style    = '.modal-body .control-label
			{
				text-align: left !important;
			}
			.modal-body .controls
			{
				margin-left: 0px !important;
			}
			.modal-body
			{
				overflow-y: visible !important;
			}';
$document->addStyleDeclaration($style);

$config          = ComponentHelper::getParams('com_redshopb');
$defaultCurrency = $config->get('default_currency', 0);
?>

<?php
if ($modal)
:
?>
<script type="text/javascript">
function btnWalletSet() {
val = parseFloat(jQuery('#assign-amount-id').val());
if (val == 'NaN') val = 0;
jQuery('#assign-amount-id').val(val);
if (document.<?php echo $formName; ?>.boxchecked.value==0)
	alert('<?php echo Text::_('COM_REDSHOPB_PLEASE_SELECT_ITEM', true); ?>');
else
	Joomla.submitform('user.setcredit', document.getElementById('<?php echo $formName; ?>'))
}

function btnWalletReset() {
val = parseFloat(jQuery('#assign-amount-id').val());
if (val == 'NaN') val = 0;
jQuery('#assign-amount-id').val(val);
if (document.<?php echo $formName; ?>.boxchecked.value==0)
	alert('<?php echo Text::_('COM_REDSHOPB_PLEASE_SELECT_ITEM', true); ?>');
else
	Joomla.submitform('user.resetcredit', document.getElementById('<?php echo $formName; ?>'))
}

function btnWalletCredit() {
val = parseFloat(jQuery('#assign-amount-id').val());
if (val == 'NaN') val = 0;
jQuery('#assign-amount-id').val(val);
if (document.<?php echo $formName; ?>.boxchecked.value==0)
	alert('<?php echo Text::_('COM_REDSHOPB_PLEASE_SELECT_ITEM', true); ?>');
else
	Joomla.submitform('user.credit', document.getElementById('<?php echo $formName; ?>'));
}
</script>

<div class="modal hide fade" id="walletModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
<div class="modal-header">
<button type="button" class="close" data-dismiss="modal">x</button>
<h3 id="myModalLabel"><?php echo Text::_('COM_REDSHOPB_USER_WALLET'); ?></h3>
</div>
<div class="modal-body">
<?php endif; ?>
	<div class="container-fluid">
		<div class="row-fluid form-horizontal">
			<div class="<?php echo $modal ? 'span6' : 'span12'; ?>">
				<fieldset>
					<legend style="margin-bottom: 0px;"><?php echo Text::_('COM_REDSHOPB_USER_CREDIT_MONEY'); ?></legend>
					<div class="control-group">
						<label id="assign-currency-lbl" for="assign_currency_id" class="control-label">
							<?php echo Text::_('COM_REDSHOPB_CURRENCY_FORM_TITLE'); ?>
						</label>

						<div id="assign-currency-action" class="combo controls">
							<select name="assign[currency_id]" class="inputbox" id="assign_currency_id">
								<?php echo HTMLHelper::_('select.options', HTMLHelper::_('rbcurrency.currencies'), 'value', 'text', $defaultCurrency); ?>
							</select>
						</div>
					</div>
					<div class="control-group">
						<label id="assign-amount-lbl" for="assign-amount-id" class="control-label">
							<?php echo Text::_('COM_REDSHOPB_AMOUNT'); ?>
						</label>

						<div id="assign-amount-action" class="combo controls">
							<input id="assign-amount-id" type="text" value="0" name="assign[amount]">
						</div>
					</div>
				</fieldset>
			</div>
<?php if ($modal) : ?>
			<div class="span6">
				<fieldset>
					<legend style="margin-bottom: 0px;"><?php echo Text::_('COM_REDSHOPB_USER_CREDIT_DATES'); ?></legend>
					<div class="control-group">
						<div class="control-label">
							<?php echo $data['filterForm']->getLabel('start_date', 'credit_money'); ?>
						</div>
						<div class="controls">
							<?php echo $data['filterForm']->getInput('start_date', 'credit_money'); ?>
						</div>
					</div>
					<div class="control-group">
						<div class="control-label">
							<?php echo $data['filterForm']->getLabel('end_date', 'credit_money'); ?>
						</div>
						<div class="controls">
							<?php echo $data['filterForm']->getInput('end_date', 'credit_money'); ?>
						</div>
					</div>
				</fieldset>
			</div>
<?php endif; ?>
		</div>
	</div>
<?php if ($modal) : ?>
</div>
<div class="modal-footer">
<button class="btn btn-danger" type="button" onclick="btnWalletReset();">
	<?php echo Text::_('COM_REDSHOPB_USER_CREDIT_RESET'); ?>
</button>
<button class="btn btn-success" type="button" onclick="btnWalletSet();">
	<?php echo Text::_('COM_REDSHOPB_USER_CREDIT_SET'); ?>
</button>
<button class="btn btn-primary" type="submit" onclick="btnWalletCredit()">
	<?php echo Text::_('COM_REDSHOPB_USER_ADD_CREDIT_MONEY'); ?>
</button>
</div>
</div>

<?php else : ?>

<div class="container-fluid">
	<div class="row-fluid">
		<div class="span12 text-right">
			<button class="btn btn-danger" type="button" onclick="btnWalletReset();">
				<?php echo Text::_('COM_REDSHOPB_USER_CREDIT_RESET'); ?>
			</button>
			<button class="btn btn-success" type="button" onclick="btnWalletSet();">
				<?php echo Text::_('COM_REDSHOPB_USER_CREDIT_SET'); ?>
			</button>
			<button class="btn btn-primary" type="button" onclick="btnWalletCredit()">
				<?php echo Text::_('COM_REDSHOPB_USER_ADD_CREDIT_MONEY'); ?>
			</button>
		</div>
	</div>
</div>

<?php endif;
