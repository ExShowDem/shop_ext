<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Views
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;

if (is_object($displayData))
{
	$displayData = (array) $displayData;
}

$showTitle         = $displayData['showTitle'];
$deliveryAddressId = (!empty($displayData['address']['id'])) ? $displayData['address']['id'] : 0;
$name              = (!empty($displayData['address']['name'])) ? $displayData['address']['name'] : '';
$address           = (!empty($displayData['address']['address'])) ? $displayData['address']['address'] : '';
$address2          = (!empty($displayData['address']['address2'])) ? $displayData['address']['address2'] : '';
$zip               = (!empty($displayData['address']['zip'])) ? $displayData['address']['zip'] : '';
$city              = (!empty($displayData['address']['city'])) ? $displayData['address']['city'] : '';
$phone             = (!empty($displayData['address']['phone'])) ? $displayData['address']['phone'] : '';
$stateName         = (!empty($displayData['address']['state_name'])) ? $displayData['address']['state_name'] : '';
$country           = (!empty($displayData['address']['country'])) ? $displayData['address']['country'] : '';

$fields = $displayData['fields'];

if (!empty($deliveryAddressId))
{
	$fields->delivery_address_id->setValue($deliveryAddressId);
}

$countryForm = $fields->country_id->input;
$params      = array(&$countryForm, true);

PluginHelper::importPlugin('vanir');
Factory::getApplication()->triggerEvent('post_process_countries', $params);

$title = 'COM_REDSHOPB_SHOP_ENTER_DELIVERY_ADDRESS';
?>

<?php if ($showTitle) : ?>
	<h4><?php echo Text::_($title, true); ?></h4>
<?php endif;?>
<div class="row">
	<div class="col-md-6">
		<div class="form-group">
			<div class="control-label">
				<?php echo $fields->name->label; ?>
			</div>
			<div class="controls">
				<?php echo $fields->name->input; ?>
			</div>
		</div>
		<div class="form-group">
			<div class="control-label">
				<?php echo $fields->email->label; ?>
			</div>
			<div class="controls">
				<?php echo $fields->email->input; ?>
			</div>
		</div>
		<div class="form-group">
			<div class="control-label">
				<?php echo $fields->phone->label; ?>
			</div>
			<div class="controls">
				<?php echo $fields->phone->input; ?>
			</div>
		</div>
	</div>
	<div class="col-md-6">
		<div class="form-group">
			<div class="control-label">
				<?php echo $fields->address->label; ?>
			</div>
			<div class="controls">
				<?php echo $fields->address->input; ?>
			</div>
		</div>
		<div class="form-group">
			<div class="control-label">
				<?php echo $fields->address2->label; ?>
			</div>
			<div class="controls">
				<?php echo $fields->address2->input; ?>
			</div>
		</div>
		<div class="form-group">
			<div class="control-label">
				<?php echo $fields->city->label; ?>
			</div>
			<div class="controls">
				<?php echo $fields->city->input; ?>
			</div>
		</div>
		<div class="form-group">
			<div class="control-label">
				<?php echo $fields->zip->label; ?>
			</div>
			<div class="controls">
				<?php echo $fields->zip->input; ?>
			</div>
		</div>
		<div class="form-group">
			<div class="control-label">
				<?php echo $fields->country_id->label; ?>
			</div>
			<div class="controls">
				<?php echo $countryForm; ?>
			</div>
		</div>
		<div class="form-group billingStateGroup hide">
			<div class="control-label">
				<?php echo $fields->state_id->label; ?>
			</div>
			<div class="controls">
				<?php echo $fields->state_id->input; ?>
			</div>
		</div>
	</div>
</div>
