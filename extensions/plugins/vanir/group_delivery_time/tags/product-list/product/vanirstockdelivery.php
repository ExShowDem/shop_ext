<?php
/**
 * @package     Aesir.E-Commerce.Tag
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

// @codingStandardsIgnoreFile

defined('_JEXEC') or die;

use Joomla\CMS\Plugin\PluginHelper;
?>

<?php if (PluginHelper::isEnabled('vanir', 'group_delivery_time')) :
	?>
	<?php
	JImport("plugins.vanir.group_delivery_time.helper.helper", JPATH_ROOT);
	$stockroomId = PlgVanirGroupDeliveryTimeHelper::getMinDeliveryStock($productId);
	?>
	<?php if ($stockroomId) :
	?>
		<?php $vanirStock = PlgVanirGroupDeliveryTimeHelper::getDeliveryTime($stockroomId); ?>
		<?php
		if ($vanirStock) :
	?>
			<div class="vanir_stock_delivery">
				<div class="pull-left">
					<div style="background-color: <?php echo $vanirStock->color ?>; width: 15px; height: 15px; border-radius: 8px; display: block; margin-right: 5px;"></div>
				</div>
				<p><?php echo $vanirStock->label ?></p>
			</div>
		<?php endif; ?>
	<?php endif; ?>
<?php endif;
