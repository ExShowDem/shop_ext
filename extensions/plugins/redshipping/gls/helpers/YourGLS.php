<?php
/**
 * @package     Aesir\E-Commerce\Plugin\Redshipping
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\Registry\Registry;

/**
 * Class YourGLS
 *
 * @since 2.1.0
 */
class YourGLS
{
	const YOURGLS_WEBSERVICE = 'http://api.gls.dk/ws/DK/V1/CreateShipment';

	/**
	 * @var Registry
	 *
	 * @since 2.1.0
	 */
	private $params;

	/**
	 * @var \Joomla\CMS\Application\CMSApplication
	 *
	 * @since 2.1.0
	 */
	private $app;

	/**
	 * @var RedshopbEntityOrder
	 *
	 * @since 2.1.0
	 */
	private $order = null;

	/**
	 * YourGLS constructor.
	 *
	 * @param   RedshopbEntityOrder $order  Order to generate labels for
	 * @param   Registry            $params Plugin parameters
	 *
	 * @throws Exception
	 */
	public function __construct(RedshopbEntityOrder $order, Registry $params)
	{
		$this->order  = $order;
		$this->params = $params;
		$this->app    = Factory::getApplication();
	}

	/**
	 * Creates the GLS API request for print labels
	 *
	 * @param   string   $parcelShopId ID of the parcel shop
	 * @param   string   $service      GLS shipping service
	 *
	 * @throws Exception
	 *
	 * @return string
	 *
	 * @since 2.1.0
	 */
	public function createRequest($parcelShopId, $service)
	{
		$date    = new DateTime($this->order->get('shipping_date'));
		$request = new Registry;

		// Set new time based on timezone, to avoid gls confusing the date
		$timezone   = new DateTimeZone('Europe/Copenhagen');
		$timeOffset = $timezone->getOffset($date);
		$date->modify($timeOffset . ' seconds');

		$request->set('UserName', $this->params->get('username'));
		$request->set('Password', $this->params->get('password'));
		$request->set('Customerid', $this->params->get('customer_id'));
		$request->set('Contactid', $this->params->get('contact_id'));
		$request->set('ShipmentDate', $date->format('Ymd'));

		$address = $this->getAddress($this->order, $service);

		$country = RedshopbEntityCountry::getInstance($address->country_id)->getItem();

		$request->set('Addresses.Delivery.Name1', $address->name);

		if (!empty($address->name2))
		{
			$request->set('Addresses.Delivery.Name2', $address->name2);
		}

		$request->set('Addresses.Delivery.Street1', $address->address);
		$request->set('Addresses.Delivery.CountryNum', $country->numeric);
		$request->set('Addresses.Delivery.ZipCode', $address->zip);
		$request->set('Addresses.Delivery.City', $address->city);
		$request->set('Addresses.Delivery.Contact', trim("{$address->name} {$address->name2}"));

		if (!empty($address->email))
		{
			$request->set('Addresses.Delivery.Email', $address->email);
		}

		if (!empty($address->phone))
		{
			$request->set('Addresses.Delivery.Mobile', $address->phone);
			$request->set('Addresses.Delivery.Phone', $address->phone);
		}

		$weight = $this->getWeight($this->order->getId());

		$this->app->triggerEvent('onAfterYourGLSGetWeight', array($this->order, &$weight));

		$fallbackWeight = $this->params->get('weight_fallback', 0);

		if (empty($weight) && $fallbackWeight)
		{
			$weight = (float) $fallbackWeight;
		}

		$request->set('Parcels', [['weight' => $weight]]);

		switch ($service)
		{
			case 'parcelshop':
				$request->set('Services.Shopdelivery', $parcelShopId);
				break;
			case 'private':
				$request->set('Services.PrivateDelivery', 'Y');
		}

		$request->set('Services.NotificationEmail', $address->email);

		return $request->toString();
	}

	/**
	 * Calls the GLS REST API
	 *
	 * Here we request the label for the parcel
	 *
	 * @param   string $request The request created by {@see YourGLS::createRequest()}
	 *
	 * @return array
	 *
	 * @since 2.1.0
	 */
	public function callWebservice($request)
	{
		$ch = curl_init(self::YOURGLS_WEBSERVICE);

		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
		curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		$response = json_decode(curl_exec($ch), true);

		if (curl_getinfo($ch, CURLINFO_HTTP_CODE) !== 200)
		{
			$this->log($response, $request);
			$this->app->enqueueMessage(Text::_('PLG_REDSHIPPING_GLS_YOURGLS_REQUEST_FAILED'), 'warning');
		}

		curl_close($ch);

		return (array) $response;
	}

	/**
	 * Calculates the total weight of an order
	 *
	 * @param   integer $orderId Used to get order items
	 *
	 * @return float|integer
	 *
	 * @since 2.1.0
	 */
	private function getWeight($orderId)
	{
		$db = Factory::getDbo();

		$query = RedshopbHelperOrder::getOrderItemsQuery($orderId);
		$query->clear('select');
		$query->select($db->qn('oi.product_id'))->select($db->qn('oi.quantity'));

		$products = $db->setQuery($query)->loadAssocList();

		$weights = array();

		foreach ($products as $product)
		{
			$entity = RedshopbEntityProduct::getInstance($product['product_id']);

			$weights[] = $product['quantity'] * $entity->getItem()->weight;
		}

		return array_sum($weights);
	}

	/**
	 * Error logging for failed requests
	 *
	 * @param   array  $response Response from the GLS REST API
	 * @param   string $request  Request sent to the GLS REST API
	 *
	 * @return void
	 *
	 * @since 2.1.0
	 */
	private function log($response, $request = '')
	{
		$log = $this->params->get('error_log', JPATH_ADMINISTRATOR . '/logs/plg_redshipping_gls.log');

		$time = date('Y-m-d H:i:s');

		$error = "[{$time}] (Order: {$this->order->getId()}) Message: {$response['Message']}; ";

		if (array_key_exists('ModelState', $response) && is_array($response['ModelState']))
		{
			foreach ($response['ModelState'] as $errmsg)
			{
				$error .= "{$errmsg[0]}; ";
			}
		}

		$error .= PHP_EOL;
		$error .= 'Payload: ' . $request;
		$error .= PHP_EOL . PHP_EOL;

		file_put_contents($log, $error, FILE_APPEND);
	}

	/**
	 * Fetches the address based on the customer type and delivery service chosen
	 *
	 * @param   RedshopbEntityOrder $order    Current order
	 * @param   string              $service  GLS delivery service
	 *
	 * @throws Exception
	 *
	 * @return stdClass
	 *
	 * @since 2.1.0
	 */
	private function getAddress(RedshopbEntityOrder $order, $service)
	{
		$customer = RedshopbEntityCustomer::getInstance($order->get('customer_id'), $order->get('customer_type'));
		$user     = RedshopbEntityUser::loadActive(true);

		if ($customer->getCompany()->get('b2c', 0) && 'parcelshop' === $service && !$user->isLoaded())
		{
			$service .= '_guest';
		}

		switch ($service)
		{
			case 'parcelshop' :
				$address = $customer->getAddress()->getExtendedData();

				break;

			default:
			case 'parcelshop_guest' :
			case 'private' :
			case 'business' :
				$address = RedshopbEntityAddress::getInstance($order->get('delivery_address_id'))->getExtendedData();
				break;
		}

		$address = $this->validateAddress($address);

		return $address;
	}

	/**
	 * Updates the internal order with new GLS parameters
	 *
	 * @param   array $newParams New parameters to add to the order
	 *
	 * @return void
	 *
	 * @since 2.1.0
	 */
	public function updateOrder(array $newParams)
	{
		$params = $this->order->get('params', new Registry);

		if (!($params instanceof Registry))
		{
			$params = new Registry($params);
		}

		foreach ($newParams as $param => $value)
		{
			$params->set('gls_' . $param, $value);
		}

		$this->order->params = $params->toString();

		$this->order->save();
	}

	/**
	 * Checks if the name is set on the address. If not assigns the current users name.
	 *
	 * @param   stdClass $address Address
	 *
	 * @return stdClass
	 *
	 * @since 2.1.0
	 */
	private function validateAddress($address)
	{
		$user = RedshopbEntityUser::loadActive(true);

		$user = $user->isLoaded() ? $user->getItem() : Factory::getUser();

		if (empty($address->name))
		{
			$address->name = $user->name;
		}

		if (empty($address->email))
		{
			$address->email = $user->email;
		}

		return $address;
	}
}
