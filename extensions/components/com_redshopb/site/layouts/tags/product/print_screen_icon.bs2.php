<?php
/**
 * @package     Aesir.E-Commerce.Tag
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;

$print = Factory::getApplication()->input->getInt('print', 0);

if ($print):
?>
	<a href="#" class="btn" onclick="window.print();return false;"><span class="icon-print"></span><?php echo Text::_('COM_REDSHOPB_SHOP_PRINT'); ?></a>
<?php endif;
