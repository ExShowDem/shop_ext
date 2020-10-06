<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Views
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

/**
 * Extracted
 *
 * @var $displayData
 * @var $id
 * @var $cartPrefix
 * @avr $options
 */

extract($displayData);

if (empty($options))
{
	return;
}
?>
	<div class="checkboxProductAccessories"><?php

	foreach ($options AS $option)
	{
		?><label class="checkbox">
		<input type="checkbox" name="accessory[<?php
		echo $id ?>][]" id="dropCheckboxAccessory_<?php
echo $id . $cartPrefix . '_' . $option->value ?>" class="dropCheckboxAccessory dropCheckboxAccessory_<?php
echo $id . $cartPrefix ?>" value="<?php echo $option->value ?>" <?php
if ($option->disable)
	{
	echo ' disabled="disabled"';
}

if (in_array($option->value, $selected))
	{
	echo ' checked="checked"';
}
	?>/><?php
	echo $option->text
	?></label><?php
	}
?></div><?php
