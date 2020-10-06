<?php
/**
 * @package     Aesir.E-Commerce
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('JPATH_REDCORE') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;

$data = $displayData;

/** @var JFormFieldTimePicker $field */
$field    = $data['field'];
$class    = $data['class'];
$id       = $data['id'];
$options  = $field->getOptions();
$required = (bool) $data['required'] ? 'required ' : '';
$value    = $data['value'];
$name     = $data['name'];

HTMLHelper::_('vnrbootstrap.timepicker');

$script = "(function($){
	$(document).ready(function () {
	$('#" . $id . "').timepicker(
	" . $options . "
	);
	});
	})(jQuery);
";

// Add the script to the document.
Factory::getDocument()->addScriptDeclaration($script);
?>
<div class="input-group bootstrap-timepicker timepicker input-append bootstrap-timepicker-component">
	<input class="visible-inline <?php echo $required ?><?php echo $class ?>" name="<?php echo $name ?>" type="text"
			id="<?php echo $id ?>" <?php echo $required ? ' required="required" ' : '' ?> value="<?php echo $value ?>" />
	<span class="input-group-addon add-on">
		<i class="icon-time"></i>
	</span>
</div>
