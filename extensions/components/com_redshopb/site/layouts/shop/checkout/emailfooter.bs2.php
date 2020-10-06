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
$imageFooter = $config->getImageFooter();
$image       = $imageFooter != '' ? '<img src="' . Uri::root() . $imageFooter . '" />' : '';
?>
<div class="emailfooter">
	<?php echo $image; ?>
</div>
