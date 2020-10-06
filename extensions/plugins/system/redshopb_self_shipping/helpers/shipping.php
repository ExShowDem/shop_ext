<?php
/**
 * @package     Aesir.E-Commerce.Plugin
 * @subpackage  Redshipping
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\Registry\Registry;

/**
 * Handles Redshopb Self shipping
 *
 * @package     Aesir.E-Commerce.Plugin
 * @subpackage  Shipping
 * @since       1.6
 */
class ShippingHelperRedshopb_Self_Shipping extends RedshopbShippingPluginHelperShipping
{
	/**
	 * Check for new status change from Shipping Gateway
	 *
	 * @param   string $extensionName   Name of the extension
	 * @param   string $ownerName       Name of the owner
	 * @param   object $deliveryAddress Delivery address
	 * @param   array  $cart            Shopping Cart
	 *
	 * @return array
	 * @throws Exception
	 */
	public function getShippingRates($extensionName, $ownerName, $deliveryAddress, $cart)
	{
		$shippingRates         = RedshopbShippingHelper::getShippingRates($this->shippingName, $extensionName, $ownerName, $deliveryAddress, $cart);
		$shippingRatesExtended = array();

		if (!empty($shippingRates))
		{
			foreach ($shippingRates as $key => $shippingRate)
			{
				$shoppingConfiguration = RedshopbEntityShipping_Configuration::getInstance($shippingRate->shipping_configuration_id)->loadItem();

				if ($shoppingConfiguration)
				{
					$app                 = Factory::getApplication();
					$configurationParams = new Registry($shoppingConfiguration->get('params'));
					$shippingRoute       = RedshopbEntityShipping_Route::getInstance($configurationParams->get('shipping_route_id'))->loadItem();
					$dayOfTheWeek        = date('N');
					$deliveryAddressId   = (int) $app->getUserState('checkout.delivery_address_id', 0);
					$addresses           = (array) $shippingRoute->get('addresses');

					// This is a quick checkout and Delivery address is not yet selected. We need to get the default delivery address id here
					if (!$deliveryAddressId)
					{
						/** @var RedshopbModelOrder $model */
						$model = RedshopbModelAdmin::getFrontInstance('Order');

						/** @var JForm $form */
						$form          = $model->getForm();
						$deliveryField = $form->getField('delivery_address_id');
						$deliveryField->renderField();

						if (!empty($deliveryField->defaultAddress))
						{
							$deliveryAddressId = $deliveryField->defaultAddress['identifier'];
						}
					}

					// If the selected delivery address is not in the shipping route selected addresses then we skip it
					if (!in_array($deliveryAddressId, $addresses))
					{
						continue;
					}

					for ($i = 0; $i < 7; $i++)
					{
						$id = (($dayOfTheWeek + $i) % 8) + ($dayOfTheWeek + $i > 7 ? 1 : 0);
						$this->addShippingRates($configurationParams, $shippingRatesExtended, $shippingRate, $shippingRoute, $id);
					}
				}
			}
		}

		return $shippingRatesExtended;
	}

	/**
	 * Adding new shipping rates if possible
	 *
	 * @param   object  $shoppingConfigurationParams  Shipping configuration Params
	 * @param   array   $shippingRatesExtended        Shipping Rates array
	 * @param   object  $shippingRate                 Shipping Rate
	 * @param   object  $shippingRoute                Shipping Route
	 * @param   int     $id                           Day Id
	 *
	 * @return void
	 */
	private function addShippingRates($shoppingConfigurationParams, &$shippingRatesExtended, $shippingRate, $shippingRoute, $id)
	{
		if ($shippingRoute->get('weekday_' . $id) == 0)
		{
			return;
		}

		$dayOfTheWeek = date('N');
		$currentTime  = new DateTime;
		$shippingDate = $this->getShippingDateFromDay($id);

		// Check if the time is already past maximum delivery time
		if ($dayOfTheWeek == $id && $currentTime < (new DateTime($shippingRoute->get('max_delivery_time'))))
		{
			return;
		}

		// Check if the saturday or sunday is turned off in the plugin parameters
		switch ($id)
		{
			case 6:
				if ($shoppingConfigurationParams->get('include_saturday') === '0')
				{
					return;
				}
				break;
			case 7:
				if ($shoppingConfigurationParams->get('include_sunday') === '0')
				{
					return;
				}
				break;
		}

		// We need to check if the given date is holiday
		if ($shoppingConfigurationParams->get('include_holidays') === '0')
		{
			$db    = Factory::getDbo();
			$query = $db->getQuery(true)
				->select('id')
				->from('#__redshopb_holiday')
				->where($db->qn('year') . ' = ' . $db->q($shippingDate->format('Y')))
				->where($db->qn('month') . ' = ' . $db->q($shippingDate->format('m')))
				->where($db->qn('day') . ' = ' . $db->q($shippingDate->format('d')));
			$db->setQuery($query);

			// We have found at least one holiday
			if ($db->loadResult())
			{
				return;
			}
		}

		$name                    = $shippingRoute->get('name');
		$shippingRateClone       = clone $shippingRate;
		$shippingRateClone->id  .= '_shipping_route_day_' . $shippingRoute->get('id') . '_shipping_route_day_' . $id;
		$shippingRateClone->name = $name . ' ' . $shippingDate->format('d.m.Y');
		$shippingRatesExtended[] = $shippingRateClone;
	}

	/**
	 * Get Shipping Date from Day number
	 *
	 * @param   int  $id  Day Id
	 *
	 * @return DateTime
	 */
	public function getShippingDateFromDay($id)
	{
		$dayOfTheWeek = date('N');
		$daysToAdd    = ((7 - $dayOfTheWeek) + $id) % 7;
		$shippingDate = new DateTime('+ ' . $daysToAdd . ' days');

		return $shippingDate;
	}
}
