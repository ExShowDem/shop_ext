<?php
/**
 * @package     Aesir.E-Commerce.Template
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

/**
 * Newly added user.  RedshopbEntityUser.
 */
$user            = $displayData['user'];
$joomlaUser      = $user->getJoomlaUser();
$shippingAddress = $user->getDeliveryAddress();
$billingAddress  = $user->getAddress();
?>
<p>A new user has registered to the shop.  User details:</p>
<h3>User data:</h3>
<ul class="unstyled list-unstyled">
	<li>First name: <strong><?php echo $user->get('name1') ?></strong></li>
	<li>Last name: <strong><?php echo $user->get('name2') ?></strong></li>
	<li>E-mail: <strong><?php echo $joomlaUser->email ?></strong></li>
</ul>
<h3>Billing address:</h3>
<ul class="unstyled list-unstyled">
	<li>Address: <strong><?php echo $billingAddress->get('address'); ?></strong></li>

	<?php if (!empty($billingAddress->get('address2'))) : ?>
		<li>Second line: <strong><?php echo $billingAddress->get('address2'); ?></strong></li>
	<?php endif; ?>
	<li>Zip: <strong><?php echo $billingAddress->get('zip'); ?></strong></li>
	<li>City: <strong><?php echo $billingAddress->get('city'); ?></strong></li>

	<?php if (!empty($billingAddress->getState()->get('name'))) : ?>
		<li>State: <strong><?php echo $billingAddress->get('zip'); ?></strong></li>
	<?php endif; ?>
	<li>Country: <strong><?php echo $billingAddress->getCountry()->get('name') ?></strong></li>
</ul>
<h3>Shipping address:</h3>
<ul class="unstyled list-unstyled">
	<li>Address: <strong><?php echo $shippingAddress->get('address'); ?></strong></li>

	<?php if (!empty($shippingAddress->get('address2'))) : ?>
		<li>Second line: <strong><?php echo $shippingAddress->get('address2'); ?></strong></li>
	<?php endif; ?>
	<li>Zip: <strong><?php echo $shippingAddress->get('zip'); ?></strong></li>
	<li>City: <strong><?php echo $shippingAddress->get('city'); ?></strong></li>

	<?php if (!empty($shippingAddress->getState()->get('name'))) : ?>
		<li>State: <strong><?php echo $shippingAddress->get('zip'); ?></strong></li>
	<?php endif; ?>
	<li>Country: <strong><?php echo $shippingAddress->getCountry()->get('name') ?></strong></li>
</ul>
