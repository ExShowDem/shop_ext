<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Views
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

$showTitle   = $displayData['showTitle'];
$orderVendor = $displayData['orderVendor'];
?>
<?php if ($showTitle):?>
	<h4><?php echo Text::_('COM_REDSHOPB_SHOP_VENDOR_INFORMATION', true); ?></h4>
<?php endif;?>
<div class="row-fluid">
	<div class="span12">
		<?php if (!empty($orderVendor->name)):?>
			<p><?php echo $orderVendor->name;?></p>
		<?php endif;?>

		<?php if (!empty($orderVendor->address)):?>
			<?php echo RedshopbLayoutHelper::render('addresses.shipping_address', $orderVendor->address);?>
		<?php endif;?>
	</div>
</div>
