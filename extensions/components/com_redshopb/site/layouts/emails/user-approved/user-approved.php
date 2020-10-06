<?php
/**
 * @package     Aesir.E-Commerce.Template
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

/**
 * Newly approved user by administrator. RedshopbEntityUser.
 */
$user = $displayData['user'];
?>
<p>Hello <?php echo $user->get('name1') . ' ' . $user->get('name2');?>!</p>
<br>
<p>Your registration has been approved by the Administrator!</p>
<p>You can now login and use our services.</p>
