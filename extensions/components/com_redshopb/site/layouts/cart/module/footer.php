<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Views
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;

HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('rbootstrap.tooltip');

$customerType = $displayData['customerType'];
$customerId   = $displayData['customerId'];

?>
<div class="row cartBottomBar">
	<?php if ($customerType == 'employee') :?>
		<?php echo RedshopbLayoutHelper::render('cart.module.wallet', $displayData);?>
	<?php endif; ?>
	<?php echo RedshopbLayoutHelper::render('cart.module.total', $displayData);?>
</div>
