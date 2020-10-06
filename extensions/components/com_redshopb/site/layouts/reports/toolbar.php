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

$data      = $displayData;
$action    = $data['action'];
$separator = strpos($action, '?') !== false ? '&' : '?';
?>
<script type="text/javascript">
	function submitToFormat(ele, format)
	{
		var $form = jQuery(ele).parents('form');
		var oldAction = $form.attr('action');
		var oldTask = $form.find('[name="task"]').val();
		$form.attr('action', '<?php echo $action . $separator; ?>format=' + format)
			.find('[name="task"]').val('');
		$form.submit();
		$form.attr('action', oldAction)
			.find('[name="task"]').val(oldTask);
	}
	function printReport()
	{
		var url = '<?php echo $action . $separator; ?>print=1&tmpl=component';
		window.open(url);
	}
</script>
<div class="row">
	<div class="btn-toolbar toolbar">
		<div class="btn-group pull-right">
		</div>
		<div class="btn-group pull-right">
			<button class="btn btn-default" type="button" onclick="submitToFormat(this, 'csv');">
				<i class="icon-table"></i>
				<?php echo Text::_('LIB_REDCORE_CSV'); ?></button>
		</div>
		<div class="btn-group pull-right">
			<button class="btn btn-default" type="button" onclick="submitToFormat(this, 'xml');">
				<i class="icon-table"></i>
				<?php echo Text::_('COM_REDSHOPB_REPORTS_XML'); ?></button>
		</div>
		<div class="btn-group pull-right">
			<button class="btn btn-default" type="button" onclick="submitToFormat(this, 'json');">
				<i class="icon-download-alt"></i>
				<?php echo Text::_('COM_REDSHOPB_REPORTS_JSON'); ?></button>
		</div>
		<div class="btn-group pull-right">
			<button class="btn btn-default" type="button" onclick="printReport();">
				<i class="icon-print"></i>
				<?php echo Text::_('COM_REDSHOPB_REPORTS_PRINT'); ?></button>
		</div>
	</div>
</div>
