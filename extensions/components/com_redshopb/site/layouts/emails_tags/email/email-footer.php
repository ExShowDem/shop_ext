<?php
/**
 * @package     Aesir.E-Commerce.Email_Tags
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Uri\Uri;

$imageFooter = RedshopbEntityConfig::getInstance()->getImageFooter();

if ($imageFooter != ''):
	?>
	<div class="emailfooter"><img src="<?php echo Uri::root() . $imageFooter; ?>"/></div><?php
endif;
