<?php
/**
 * @package     Aesir.E-Commerce.Site
 * @subpackage  mod_redshopb_megamenu
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

if (isset($item->content) && $item->content)
{
	echo '<div class="shopbMegaMenu_mod">'
		. $item->content
		. '<div class="clearfix"></div></div>';
}
