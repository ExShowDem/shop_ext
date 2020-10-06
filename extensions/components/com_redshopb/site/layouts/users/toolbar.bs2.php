<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

$data = $displayData;

$formName = $data['formName'];
$return   = isset($data['return']) ? $data['return'] : null;
?>
<script type="text/javascript">
	function submitToCSV(ele)
	{
		var $form = jQuery(ele).parents('form');
		var oldAction = $form.attr('action');
		var oldTask = $form.find('[name="task"]').val();
		$form.attr('action', 'index.php?option=com_redshopb&view=users&format=csv')
			.find('[name="task"]').val('');
		$form.submit();
		$form.attr('action', oldAction)
			.find('[name="task"]').val(oldTask);
	}
</script>
<h2>
	<?php echo Text::_('COM_REDSHOPB_USER_LIST_TITLE'); ?>
</h2>
<div class="row-fluid">
	<div class="btn-toolbar toolbar">
		<div class="btn-group">
			<button class="btn btn-success" onclick="Joomla.submitform('user.add',
				document.getElementById('<?php echo $formName; ?>'))" href="#">
				<i class="icon-file-text-alt"></i>
				<?php echo Text::_('JTOOLBAR_NEW') ?>
			</button>

			<button class="btn"
					onclick="if (document.<?php echo $formName; ?>.boxchecked.value==0){alert('<?php echo Text::_('COM_REDSHOPB_PLEASE_SELECT_ITEM', true); ?>');}
						else{ Joomla.submitform('user.edit', document.getElementById('<?php echo $formName; ?>'))}"
					href="#">
				<i class="icon-edit"></i>
				<?php echo Text::_('JTOOLBAR_EDIT') ?>
			</button>
			<button class="btn btn-danger"
					onclick="if (document.<?php echo $formName; ?>.boxchecked.value==0){alert('<?php echo Text::_('COM_REDSHOPB_PLEASE_SELECT_ITEM', true); ?>');}
						else{ Joomla.submitform('users.delete', document.getElementById('<?php echo $formName; ?>'))}"
					href="#">
				<i class="icon-trash"></i>
				<?php echo Text::_('JTOOLBAR_DELETE') ?>
			</button>
		</div>
		<div class="btn-group">
			<button class="btn" type="button" onclick="if (document.<?php echo $formName; ?>.boxchecked.value == 0)
			{alert('<?php echo Text::_('COM_REDSHOPB_PLEASE_SELECT_ITEM', true); ?>');jQuery('#walletModal').modal('hide');}
			else {jQuery('#walletModal').modal('toggle');}">
				<i class="icon-money"></i>
				<?php echo Text::_('COM_REDSHOPB_USER_ADD_CREDIT_MONEY') ?>
			</button>
		</div>
		<div class="btn-group pull-right">
			<button class="btn" type="button" onclick="submitToCSV(this);">
				<i class="icon-table"></i>
				<?php echo Text::_('LIB_REDCORE_CSV'); ?></button>
		</div>
		<div class="btn-group pull-right">
			<?php echo RedshopbLayoutHelper::render('import.form', array('model' => 'users', 'return' => $return)); ?>
		</div>
	</div>
</div>
