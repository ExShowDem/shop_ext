<?php
/**
 * @package     Aesir.E-Commerce.Tag
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;
?>
<a class="btn btn-small" href="<?php echo RRoute::_('index.php?option=com_redshopb&task=product.printPDF&id=' . $product->id);?>" target="_blank">
	<i class="icon icon-file">PDF</i>
</a>
