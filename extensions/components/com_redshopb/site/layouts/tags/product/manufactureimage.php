<?php
/**
 * @package     Aesir.E-Commerce.Tag
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;
$thumb = RedshopbHelperManufacturer::getImageThumbHtml($displayData['product']->manufacturer_id, true);

if ($thumb): ?>
	<span class="productManufacturerImage"><?php echo $thumb ?></span>
<?php endif;
