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
	<div class="listProductAccessories"><?php

	foreach ($options AS $option)
	{
		?><div class="oneProductAccessories"><?php
	echo $option->text
	?></div><?php
	}
?></div><?php
