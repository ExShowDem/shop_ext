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

$imageHeader = RedshopbEntityConfig::getInstance()->getImageHeader();

if ($imageHeader != ''):
	?>
	<div class="emailHeader"><img src="<?php echo Uri::root() . $imageHeader; ?>"/></div><?php
endif;
