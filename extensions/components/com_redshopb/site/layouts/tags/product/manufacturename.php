<?php
/**
 * @package     Aesir.E-Commerce.Tag
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;
$manufactureName = RedshopbEntityManufacturer::getInstance($displayData['product']->manufacturer_id)->get('name');

if ($manufactureName):?>
	<span class="productManufacturerName"><?php echo $manufactureName; ?></span>
<?php endif;
