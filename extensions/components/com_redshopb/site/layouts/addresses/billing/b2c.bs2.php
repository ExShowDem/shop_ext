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
<div class="control-group">
	<div class="controls">
		<p id="billing-name">
			<?php echo "{$customerInfo->name} {$customerInfo->name2}"; ?>
		</p>
	</div>

	<div class="controls">
		<p id="billing-address">
			<?php echo $customerInfo->address; ?>
		</p>
	</div>

	<?php if (isset($customerInfo->address2)) : ?>
	<div class="controls">
		<p id="billing-address2">
			<?php echo $customerInfo->address2; ?>
		</p>
	</div>
	<?php endif; ?>

	<div class="controls">
		<p id="billing-location">
			<?php echo $customerInfo->zip . ' ' . $customerInfo->city; ?>
		</p>
	</div>

	<div class="controls">
		<?php if (isset($customerInfo->state_name)) : ?>
		<p id="billing-state">
			<?php echo $customerInfo->state_name . ','; ?>
		</p>
		&nbsp;
		<?php endif; ?>

		<p id="billing-country">
			<?php echo $customerInfo->country; ?>
		</p>
	</div>

	<?php if (isset($customerInfo->phone)) : ?>
	<div class="controls">
		<p id="billing-phone">
			<?php echo $customerInfo->phone; ?>
		</p>
	</div>
	<?php endif; ?>

	<div class="controls">
		<p id="billing-email">
			<?php echo $customerInfo->email; ?>
		</p>
	</div>
</div>
