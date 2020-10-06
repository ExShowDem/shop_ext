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

$data = (object) $displayData;

?>

<div class="well">
	<h4><?php echo Text::_('COM_REDSHOPB_SHOP_VENDOR_INFORMATION', true); ?></h4>
	<div class="row">
		<div class="col-md-12">
			<?php if (!empty($data->orderVendor->name)) : ?>
				<p><?php echo $data->orderVendor->name; ?></p>
			<?php endif; ?>

			<?php if (!empty($data->orderVendor->address->name)) : ?>
				<p><?php echo $data->orderVendor->address->name; ?></p>
			<?php endif; ?>

			<?php if (!empty($data->orderVendor->address->address)) : ?>
				<p><?php echo $data->orderVendor->address->address; ?></p>
			<?php endif; ?>

			<?php if (!empty($data->orderVendor->address->address2)) : ?>
				<p><?php echo $data->orderVendor->address->address2; ?></p>
			<?php endif; ?>

			<?php if (!empty($data->orderVendor->address->zip) || !empty($data->orderVendor->address->city)) : ?>
				<p><?php echo $data->orderVendor->address->zip . ' ' . $data->orderVendor->address->city; ?></p>
			<?php endif; ?>

			<?php if (!empty($data->orderVendor->address->country)) : ?>
				<p><?php echo Text::_($data->orderVendor->address->country); ?></p>
			<?php endif; ?>

			<?php if (!empty($data->orderVendor->vat_number)) : ?>
				<p><?php echo JText::_('COM_REDSHOPB_VAT') . ': ' . $data->orderVendor->vat_number; ?></p>
			<?php endif; ?>

			<?php if (!empty($data->orderVendor->address->phone)) : ?>
				<p><?php echo $data->orderVendor->address->phone; ?></p>
			<?php endif; ?>

			<?php if (!empty($data->orderVendor->address->email)) : ?>
				<p><?php echo $data->orderVendor->address->email; ?></p>
			<?php endif; ?>
		</div>
	</div>
</div>
