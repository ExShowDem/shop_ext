<?php
/**
 * @package     Redcore
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2016 - 2018 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

extract($displayData);

$attributes = array(
	'class="hide ' . (!empty($class) ? $class : '') . '"',
	$disabled ? 'disabled' : '',
	$readonly ? 'readonly' : ''
);
?>
<div class="well well-sm well-small" style="cursor: not-allowed; ">
	<?php echo trim($value) == '' ? '<br />' : $value ?>
	<textarea name="<?php
	echo $name; ?>" id="<?php
	echo $id; ?>" <?php
	echo implode(' ', $attributes); ?>><?php echo htmlspecialchars($value, ENT_COMPAT, 'UTF-8'); ?></textarea>
</div>
