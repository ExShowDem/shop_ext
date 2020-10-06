<?php
/**
 * @package     Aesir.E-Commerce.Tag
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

if (isset($extThis->product->dropDownTypes[$product->id])):
	$firstType = reset($extThis->product->dropDownTypes[$product->id]);
	echo $firstType->name;
endif;
