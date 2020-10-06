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

if (!$print):
?>
	<a rel="nofollow" class="btn btn-default hasTooltip" title="<?php echo Text::_('COM_REDSHOPB_SHOP_PRINT'); ?>"
		href="javascript:if(window.print)window.print()">
		<i class="icon-print"></i>
	</a>
<?php endif;
