<?php
/**
 * @package     Aesir.E-Commerce.Template
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

/**
 * Layout variables
 * ============================
 * @var  array   $displayData  List of available data.
 * @var  object  $user         User register data.
 * @var  string  $siteName     Site name.
 */
extract($displayData);
?>

<p>Hello <?php echo $user->get('name1') . ' ' . $user->get('name2');?>!</p>
<br>
<p>Thank you for registering at <?php echo $siteName ?>.</p>
<p>Your registration is being reviewed and we'll be back to you shortly.</p>
