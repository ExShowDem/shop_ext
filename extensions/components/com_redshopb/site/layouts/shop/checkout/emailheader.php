<?php
/**
 * @package     Aesir.E-Commerce.Admin
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

use Joomla\CMS\Uri\Uri;

$config      = RedshopbEntityConfig::getInstance();
$imageHeader = $config->getImageHeader();

$image = $imageHeader != '' ? '<img src="' . Uri::root() . $imageHeader . '" />' : '';

?>

<div class="emailHeader">
	<?php echo $image; ?>
</div>
