<?php
/**
 * @package     Aesir.E-Commerce.Admin
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Layout variables
 *
 * @var   boolean    $isNew
 * @var   stdClass   $customerInfo
 */
extract($displayData);
?>
<?php if (!$isNew) : ?>
	<div id="billing-id" class="hidden"><?php echo $customerInfo->id; ?></div>
<?php endif; ?>
<div class="form-group">
	<div class="controls">
		<p id="delivery-name">
			<?php echo $customerInfo->name; ?>
		</p>
	</div>

	<div class="controls">
		<p id="delivery-address">
			<?php echo $customerInfo->address; ?>
		</p>
	</div>

	<?php if (isset($customerInfo->address2)) : ?>
	<div class="controls">
		<p id="delivery-address2">
			<?php echo $customerInfo->address2; ?>
		</p>
	</div>
	<?php endif; ?>

	<div class="controls">
		<p id="delivery-location">
			<?php echo $customerInfo->zip . ' ' . $customerInfo->city; ?>
		</p>
	</div>

	<div class="controls">
		<?php if (isset($customerInfo->state_name)) : ?>
		<p id="delivery-state">
			<?php echo $customerInfo->state_name . ','; ?>
		</p>
		&nbsp;
		<?php endif; ?>

		<p id="delivery-country">
			<?php echo $customerInfo->country; ?>
		</p>
	</div>

	<?php if (isset($customerInfo->phone)) : ?>
	<div class="controls">
		<p id="delivery-phone">
			<?php echo $customerInfo->phone; ?>
		</p>
	</div>
	<?php endif; ?>

	<div class="controls">
		<p id="delivery-email">
			<?php echo $customerInfo->email; ?>
		</p>
	</div>
</div>
