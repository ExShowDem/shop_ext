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

if (is_object($displayData))
{
	$displayData = (array) $displayData;
}

$email    = (!empty($displayData['email'])) ? $displayData['email'] : '';
$phone    = (!empty($displayData['phone'])) ? $displayData['phone'] : '';
$name     = (!empty($displayData['name'])) ? $displayData['name'] : '';
$name2    = (!empty($displayData['name2'])) ? $displayData['name2'] : '';
$address  = (!empty($displayData['address'])) ? $displayData['address'] : '';
$address2 = (!empty($displayData['address2'])) ? $displayData['address2'] : '';
$zip      = (!empty($displayData['zip'])) ? $displayData['zip'] : '';
$city     = (!empty($displayData['city'])) ? $displayData['city'] : '';

$location = (!empty($zip)) ? '<span class="js-address-zip">' . $zip . ',&nbsp;</span>' : '';
$location = (!empty($city)) ? $location . '<span class="js-address-city">' . $city . '</span>' : $location;

if (empty($location))
{
	$location = '<span class="js-address-zip"></span><span class="js-address-city"></span>';
}

$stateName = (!empty($displayData['state_name'])) ? $displayData['state_name'] : '';
$country   = (!empty($displayData['country'])) ? $displayData['country'] : '';
?>
<div class="js-address-wrapper">
	<p class="js-address-name js-address-name2">
		<?php echo "{$name} {$name2}";?>
	</p>
	<p class="js-address-address">
		<?php echo $address;?>
	</p>
	<p class="js-address-address2">
		<?php echo $address2;?>
	</p>
	<p class="js-address-location">
		<?php echo $location;?>
	</p>
	<p class="js-address-state-name">
		<?php echo $stateName;?>
	</p>
	<p class="js-address-country">
		<?php echo Text::_($country);?>
	</p>
	<p class="js-phone">
		<?php echo $phone;?>
	</p>
	<p class="js-email">
		<?php echo $email;?>
	</p>
</div>
