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
$admin           = $displayData['admin'];
$adminName       = $admin->get('name1') . ' ' . $admin->get('name2');
$activationLink  = $displayData['activationLink'];
$editLink        = $displayData['editLink'];
$joomlaUser      = $user->getJoomlaUser();
$shippingAddress = $user->getDeliveryAddress();
$billingAddress  = $user->getAddress();
?>
<p>
	<h1>Greetings<?php echo (!empty($adminName) ? $adminName : '');?>!</h1>
</p>
<p>A new user has registered to the shop. User details:</p>
<h3>User data:</h3>
<ul class="unstyled list-unstyled">
	<li>First name: <strong><?php echo $user->get('name1') ?></strong></li>
	<li>Last name: <strong><?php echo $user->get('name2') ?></strong></li>
	<li>E-mail: <strong><?php echo $joomlaUser->email ?></strong></li>
</ul>
<?php if (!empty($billingAddress->get('address'))): ?>
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
<?php endif; ?>

<?php if (!empty($shippingAddress->get('address'))): ?>
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
<?php endif; ?>
<br />
<p>You can approve this registration and activate this user by clicking <a href="<?php echo $activationLink; ?>">HERE</a>.</p>
<p>However, if you wish to edit or delete this user, you can do it <a href="<?php echo $editLink; ?>">HERE</a>.</p>
