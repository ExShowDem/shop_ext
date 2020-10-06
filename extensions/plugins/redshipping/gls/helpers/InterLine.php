<?php
/**
 * @package     Aesir\E-Commerce\Plugin\Redshipping
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

use Joomla\CMS\Factory;
use Joomla\Registry\Registry;

/**
 * Class InterLine
 *
 * @since 2.1.0
 */
class InterLine
{
	/**
	 * @var Registry
	 *
	 * @since 2.1.0
	 */
	private $params;

	/**
	 * InterLine constructor.
	 *
	 * @param   Registry $params Plugin parameters
	 *
	 */
	public function __construct(Registry $params)
	{
		$this->params = $params;
	}


	/**
	 * Create InterLine CSV
	 *
	 * @param   RedshopbEntityOrder $order          Order we are generation label for
	 * @param   string              $parcelShopId   ID of the chosen parcel shop
	 *
	 * @throws Exception
	 *
	 * @return boolean
	 *
	 * @since 2.1.0
	 */
	public function createCSV(RedshopbEntityOrder $order, $parcelShopId)
	{
		$folder = $this->params->get('interline_folder', JPATH_PLUGINS . '/redshipping/gls/interline');

		$data     = $order->getItem();
		$date     = new DateTimeImmutable($order->get('shipping_date'));
		$customer = RedshopbEntityCustomer::getInstance($order->get('customer_id'), $order->get('customer_type'));

		if ($customer->getCompany()->get('b2c', 0))
		{
			$address = RedshopbEntityAddress::getInstance($order->get('delivery_address_id'))->getExtendedData();
		}
		else
		{
			$address = $customer->getAddress()->getExtendedData();
		}

		$line = array(
			$data->id,
			$address->name,
			$address->address,
			$address->address2,
			$address->zip,
			null, // Country
			$date->format('d-m-y'),
			$this->getWeight($order->getId()),
			1,
			null,
			null,
			'A',
			'Z',
			trim("{$address->name} {$address->name2}"),
			null,
			null,
			$address->email,
			$address->phone,
			$this->params->get('default_mail_notification', false) === false
				? null
				: 'E',
			null,
			$parcelShopId
		);

		$fh = fopen("{$folder}/{$data->id}_{$date}", 'w');

		if (false === $fh)
		{
			return false;
		}

		$length = fputcsv($fh, $line);

		fclose($fh);

		return false !== $length;
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
}
