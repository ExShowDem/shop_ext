<?php

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\Registry\Registry;

/** @var stdClass $displayData Imported layout data*/

if (isset($displayData->order))
{
	$params  = new Registry($displayData->order->params);
	$service = $params->get('gls_delivery_service');
	$show    = !$params->get('gls_webservice_failed', false);
}
else
{
	$show    = true;
	$service = Factory::getSession()->get('gls_delivery_service', null, 'redshipping_gls');
}

$parcelshop = $displayData->parcelshop;

if (true === $show) :
?>
<div class="well-small">
	<p><?php echo Text::sprintf('PLG_REDSHIPPING_GLS_DELIVERY_SERVICE', ucwords($service)); ?></p>

	<?php if ('parcelshop' === $service) : ?>
		<p><?php echo $parcelshop->CompanyName; ?></p>
		<p><?php echo $parcelshop->Streetname; ?></p>
		<p><?php echo $parcelshop->Streetname2; ?></p>
		<p><?php echo $parcelshop->ZipCode . ', ' . $parcelshop->CityName; ?></p>
	<?php endif; ?>

	<?php if (isset($displayData->consignmentId)) : ?>
		<p><?php echo Text::sprintf('PLG_REDSHIPPING_GLS_DELIVERY_PARCEL_NUMBER', $displayData->consignmentId); ?></p>
	<?php endif; ?>
</div>
<?php endif;
