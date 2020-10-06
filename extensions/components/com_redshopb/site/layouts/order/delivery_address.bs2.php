<?php
/**
 * @package     Aesir.E-Commerce.Admin
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

$customerOrder = $displayData['current_customer_order'];

?>

<div class="row-fluid">
	<div class="span12">
		<h5><?php echo Text::_('COM_REDSHOPB_ORDER_DELIVERY_ADDRESS_TITLE', true); ?></h5>
		<p>
			<?php echo $customerOrder->delivery_address_name?>
		</p>

		<?php if (isset($customerOrder->delivery_address_name2)): ?>
			<p>
				<?php echo $customerOrder->delivery_address_name2 ?>
			</p>
		<?php endif;?>

		<p>
			<?php echo $customerOrder->delivery_address_address ?>
		</p>

		<?php if (isset($customerOrder->delivery_address_address2)): ?>
			<p>
				<?php echo $customerOrder->delivery_address_address2 ?>
			</p>
		<?php endif; ?>

		<p>
			<?php echo $customerOrder->delivery_address_zip ?>, <?php echo $customerOrder->delivery_address_city ?>
		</p>
		<p>
			<?php if ($customerOrder->delivery_address_state):?>
				<?php echo $customerOrder->delivery_address_state . ',&nbsp;';?>
			<?php endif;?>
			<?php echo $customerOrder->delivery_address_country ?>
		</p>
	</div>

</div>


