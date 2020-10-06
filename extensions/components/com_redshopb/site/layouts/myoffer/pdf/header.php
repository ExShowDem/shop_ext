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
<table style="width: 100%;">
	<tr>
		<td style="width: 49%; vertical-align: top;">
			<h4><?php echo Text::_('COM_REDSHOPB_SHOP_DELIVERY_CUSTOMER_INFO', true); ?></h4>
			<br />
			<div>
				<?php
				if (isset($data->company->name) && !empty($data->company->name))
				{
					echo '<p>' . Text::_('COM_REDSHOPB_COMPANY') . ': ' . $data->company->name . '</p>';
				}

				if (isset($data->department->name) && !empty($data->department->name))
				{
					echo '<p>' . Text::_('COM_REDSHOPB_DEPARTMENT') . ': ' . $data->department->name . '</p>';
				}

				if (isset($data->employee->name) && !empty($data->employee->name))
				{
					echo '<p>' . Text::_('COM_REDSHOPB_EMPLOYEE') . ': ' . $data->employee->name . '</p>';
				}

				if (isset($data->company->addressName) && !empty($data->company->addressName))
				{
					echo '<p>' . $data->company->addressName . '</p>';
				}

				if (isset($data->company->address) && !empty($data->company->address))
				{
					echo '<p>' . $data->company->address . '</p>';
				}

				if (isset($data->company->zip) && !empty($data->company->zip))
				{
					echo '<p>' . $data->company->zip . '</p>';
				}

				if (isset($data->company->city) && !empty($data->company->city))
				{
					echo '<p>' . $data->company->city . '</p>';
				}

				if (isset($data->company->country) && !empty($data->company->country))
				{
					echo '<p>' . Text::_($data->company->country) . '</p>';
				}
				?>
			</div>
		</td>
		<td style="width: 49%; vertical-align: top;">
			<h4><?php echo Text::_('COM_REDSHOPB_SHOP_VENDOR_INFORMATION', true); ?></h4>
			<br />
			<div>
				<?php
				echo '<p>' . $data->vendor->name . '</p>';
				echo '<p>' . $data->vendorAddress->address . '</p>';
				echo '<p>' . $data->vendorAddress->zip . '</p>';
				echo '<p>' . $data->vendorAddress->city . '</p>';
				echo '<p>' . Text::_($data->vendorAddress->country) . '</p>';
				?>
			</div>
		</td>
	</tr>
</table>
<br />
<?php if (isset($data->comment) && !empty($data->comment)):?>
	<h4><?php echo Text::_('COM_REDSHOPB_ORDER_COMMENT', true); ?></h4>
	<br />
	<p><?php echo $data->comment; ?></p>
<?php endif;?>
<br />
