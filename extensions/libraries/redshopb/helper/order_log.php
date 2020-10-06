<?php
/**
 * @package     Aesir.E-Commerce.Libraries
 * @subpackage  Helper
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

/**
 * Order log helper.
 *
 * @package     Aesir.E-Commerce.Libraries
 * @subpackage  Helper
 * @since       1.0
 */
final class RedshopbHelperOrder_Log
{
	/**
	 * This function returns order with its items,
	 * collected orders and sent orders for log purpose.
	 * Result is a tree which path determinate order creation
	 * and flow through the system.
	 *
	 * @param   array  $orderIds  Order ids for getting log.
	 *
	 * @return string Tree with all info.
	 *
	 * @throws Exception
	 */
	public static function getOrderLog($orderIds)
	{
		if (!is_null($orderIds) && is_array($orderIds) && !empty($orderIds))
		{
			$db     = Factory::getDbo();
			$orders = array();

			$query = $db->getQuery(true)
				->select(
					array(
						$db->qn('o.id', 'WebOrderNumber'),
						$db->qn('o.customer_id', 'cid'),
						$db->qn('o.customer_type', 'ctype'),
						$db->qn('o.company_erp_id'),
						$db->qn('o.department_erp_id'),
						$db->qn('o.user_erp_id'),
						$db->qn('o.delivery_address_id'),
						$db->qn('o.delivery_address_type'),
						$db->qn('o.delivery_address_code'),
						$db->qn('o.delivery_address_name', 'Name'),
						$db->qn('o.delivery_address_name2', 'Name2'),
						$db->qn('o.delivery_address_address', 'Address'),
						$db->qn('o.delivery_address_address2', 'Address2'),
						$db->qn('o.delivery_address_zip', 'ZipCode'),
						$db->qn('o.delivery_address_city', 'City'),
						$db->qn('o.delivery_address_country_code', 'CountryCode'),
						$db->qn('o.delivery_address_state', 'State'),
						$db->qn('o.delivery_address_state_code', 'StateCode'),

						'IF (' .
							$db->qn('curr.alpha3') . ' = ' . $db->q('PTS') . ',' . $db->q('POINTS') . ',' . $db->qn('curr.alpha3') .
						') AS Currency',

						$db->qn('o.comment', 'Comment'),
						$db->qn('o.requisition', 'YourReference'),
						$db->qn('o.total_price', 'Price'),
						$db->qn('o.created_date', 'DateCreated')
					)
				)
				->from($db->qn('#__redshopb_order', 'o'))
				->leftJoin($db->qn('#__redshopb_currency', 'curr') . ' ON ' . $db->qn('curr.id') . ' = ' . $db->qn('o.currency_id'))
				->innerJoin(
					$db->qn('#__redshopb_company', 'comp')
					. ' ON ((o.customer_type = ' . $db->q('company') . ' AND ' . $db->qn('comp.id') . ' = ' . $db->qn('o.customer_id')
					. ' OR o.customer_type != ' . $db->q('company') . ' AND ' . $db->qn('comp.id') . ' = ' . $db->qn('o.customer_company') . ')'
					. ' AND ' . $db->qn('comp.type') . ' = ' . $db->q('customer') . ')'
				)
				->where($db->qn('o.id') . ' IN (' . implode(',', $orderIds) . ')');
			$db->setQuery($query);
			$customers = $db->loadObjectList();

			$feeItems = RedshopbHelperShop::getChargeProducts('fee');

			foreach ($customers as $customer)
			{
				$order                                = new stdClass;
				$deliveryAddress                      = new stdClass;
				$deliveryAddress->Name                = $customer->Name;
				$deliveryAddress->Name2               = $customer->Name2;
				$deliveryAddress->Address             = $customer->Address;
				$deliveryAddress->Address2            = $customer->Address2;
				$deliveryAddress->ZipCode             = $customer->ZipCode;
				$deliveryAddress->City                = $customer->City;
				$deliveryAddress->CountryCode         = $customer->CountryCode;
				$deliveryAddress->State               = $customer->State;
				$deliveryAddress->StateCode           = $customer->StateCode;
				$deliveryAddress->AddressType         = $customer->delivery_address_type;
				$deliveryAddress->DeliveryAddressCode = $customer->delivery_address_code;
				$customer->DeliveryAddress            = $deliveryAddress;
				$order->Customer                      = $customer;
				$order->Lines                         = self::getOrderLines($customer->WebOrderNumber);

				if (empty($order->Lines))
				{
					return null;
				}

				$items = array();

				foreach ($order->Lines as $line)
				{
					$items[] = $line->itemId;
				}

				$intersect = array_diff($items, $feeItems);

				if (empty($order->Lines) || empty($intersect))
				{
					throw new Exception(
						Text::plural(
							'COM_REDSHOPB_ORDERS_EXPEDITE_WARNING_NO_MAIN_WAREHOUSE_ITEMS',
							str_pad($order->Customer->WebOrderNumber, 6, '0', STR_PAD_LEFT)
						)
					);
				}

				$orders[] = $order;
			}

			return self::toString($orders);
		}
		else
		{
			return '<Orders/>';
		}
	}

	/**
	 * Get order product lines.
	 *
	 * @param   int  $orderId  Order id.
	 *
	 * @return array Order lines.
	 */
	private static function getOrderLines($orderId)
	{
		// Get children lines
		$childrenOrders = self::getChildOrders(array($orderId), true);
		$orderIds       = array_keys($childrenOrders);

		$db              = Factory::getDbo();
		$lines           = null;
		$additionalLines = array();

		if (!empty($orderIds))
		{
			$query = $db->getQuery(true)
				->select(
					array(
						$db->qn('oi.id', 'lineId'),
						$db->qn('o.id', 'orderId'),
						$db->qn('oi.product_item_id', 'itemId'),
						$db->qn('oi.product_sku', 'ProductNo'),
						$db->qn('oi.product_id', 'productId'),
						$db->qn('oi.product_name', 'Description'),
						$db->qn('oi.price', 'Price'),
						$db->qn('oi.quantity', 'Quantity'),
						$db->qn('oi.currency', 'Currency'),
						$db->qn('o.created_date', 'DateCreated'),
						$db->qn('oi.collection_erp_id', 'collectionNo'),
						$db->qn('o.delivery_address_type'),
						$db->qn('o.delivery_address_code')
					)
				)
				->from($db->qn('#__redshopb_order_item', 'oi'))
				->innerJoin($db->qn('#__redshopb_order', 'o') . ' ON ' . $db->qn('o.id') . ' = ' . $db->qn('oi.order_id'))
				->innerJoin($db->qn('#__redshopb_sync', 's') . ' ON ' . $db->qn('oi.product_id') . ' = ' . $db->qn('s.local_id'))
				->where($db->qn('s.reference') . ' = ' . $db->q('fengel.product'))
				->where($db->qn('oi.order_id') . ' IN (' . implode(',', $orderIds) . ')')
				->where($db->qn('oi.parent_id') . ' IS NULL');
			$db->setQuery($query);
			$lines = $db->loadObjectList();
		}

		$query = $db->getQuery(true)
			->select(
				array(
					$db->qn('oi.id', 'lineId'),
					$db->qn('oi.id', 'lastLineId'),
					$db->qn('o.id', 'orderId'),
					$db->qn('oi.product_item_id', 'itemId'),
					$db->qn('oi.product_sku', 'ProductNo'),
					$db->qn('oi.product_id', 'productId'),
					$db->qn('oi.product_name', 'Description'),
					$db->qn('oi.price', 'Price'),
					$db->qn('oi.quantity', 'Quantity'),
					$db->qn('oi.currency', 'Currency'),
					$db->qn('o.created_date', 'DateCreated'),
					$db->qn('oi.collection_erp_id', 'collectionNo'),
					$db->qn('o.delivery_address_type'),
					$db->qn('o.delivery_address_code')
				)
			)
			->from($db->qn('#__redshopb_order_item', 'oi'))
			->innerJoin($db->qn('#__redshopb_order', 'o') . ' ON ' . $db->qn('o.id') . ' = ' . $db->qn('oi.order_id'))
			->innerJoin($db->qn('#__redshopb_sync', 's') . ' ON ' . $db->qn('oi.product_id') . ' = ' . $db->qn('s.local_id'))
			->where($db->qn('s.reference') . ' = ' . $db->q('fengel.product'))
			->where($db->qn('oi.order_id') . ' = ' . (int) $orderId)
			->where($db->qn('oi.parent_id') . ' IS NULL');
		$db->setQuery($query);

		if ($lines == null)
		{
			$lines = $db->loadObjectList();
		}
		else
		{
			$parentLines = $db->loadObjectList();

			foreach ($parentLines as $parentLine)
			{
				$lineFound = false;
				$newLine   = null;
				$quantity  = 0;

				foreach ($lines as $line)
				{
					if ($parentLine->itemId == $line->itemId && $parentLine->Quantity > $line->Quantity)
					{
						$line->lastLineId = $parentLine->lineId;
						$line->Currency   = $parentLine->Currency;
						$line->Price      = $parentLine->Price;

						if ($newLine == null)
						{
							$lineFound = true;
							$newLine   = $parentLine;
							$quantity  = $parentLine->Quantity - $line->Quantity;
						}
						else
						{
							$quantity = $quantity - $line->Quantity;
						}
					}
					elseif ($parentLine->itemId == $line->itemId)
					{
						$line->lastLineId = $parentLine->lineId;
						$lineFound        = true;
						$line->Currency   = $parentLine->Currency;
						$line->Price      = $parentLine->Price;
					}
				}

				if (!$lineFound)
				{
					$additionalLines[] = $parentLine;
				}
				elseif (!is_null($newLine) && $quantity > 0)
				{
					$newLine->Quantity = $quantity;
					$additionalLines[] = $newLine;
				}
			}

			if (!empty($additionalLines))
			{
				$lines = array_merge($lines, $additionalLines);
			}
		}

		foreach ($lines as $line)
		{
			$customers         = self::getLineCustomers(
				$line->orderId, (isset($childrenOrders[$line->orderId]) ? $childrenOrders[$line->orderId] : null)
			);
			$line->EndCustomer = $customers->EndCustomer;
			$line->Department  = $customers->Department;
			$line->Employee    = $customers->Employee;

			if (isset($line->lastLineId))
			{
				$line->ItemServices = self::getOrderItemServices($line->lastLineId);
				$line->Types        = self::getOrderItemTypes($line->lineId);
			}
			else
			{
				$line->ItemServices = array();
				$line->Types        = array();
			}
		}

		return $lines;
	}

	/**
	 * Get customers for given item.
	 *
	 * @param   int    $orderId    Order id.
	 * @param   array  $orderInfo  Order information (considering address, comment and req no from order tree)
	 *
	 * @return object|null Object with all customers.
	 */
	private static function getLineCustomers($orderId, $orderInfo)
	{
		$db = Factory::getDbo();

		$customers              = new stdClass;
		$customers->Employee    = new stdClass;
		$customers->Department  = new stdClass;
		$customers->EndCustomer = new stdClass;
		$orderModel             = RModel::getFrontInstance('Order');

		$order                                = $orderModel->getItem($orderId);
		$deliveryAddress                      = new stdClass;
		$deliveryAddress->Name                = $order->delivery_address_name;
		$deliveryAddress->Name2               = $order->delivery_address_name2;
		$deliveryAddress->Address             = $order->delivery_address_address;
		$deliveryAddress->Address2            = $order->delivery_address_address2;
		$deliveryAddress->ZipCode             = $order->delivery_address_zip;
		$deliveryAddress->City                = $order->delivery_address_city;
		$deliveryAddress->CountryCode         = $order->delivery_address_country_code;
		$deliveryAddress->StateCode           = $order->delivery_address_state_code;
		$deliveryAddress->AddressType         = $order->delivery_address_type;
		$deliveryAddress->DeliveryAddressCode = $order->delivery_address_code;

		if ($order->customer_type == 'employee')
		{
			$customers->Employee->WebOrderNumber        = $orderId;
			$customers->Employee->Id                    = $order->user_erp_id;
			$customers->Employee->DateCreated           = $order->created_date;
			$customers->Employee->RequestedDeliveryDate = null;

			if (isset($orderInfo->employee))
			{
				$customers->Employee->YourReference = $orderInfo->employee->requisition;
				$customers->Employee->Comment       = $orderInfo->employee->comment;

				$customers->Employee->DeliveryAddress                      = new stdClass;
				$customers->Employee->DeliveryAddress->Name                = $orderInfo->employee->delivery_address_name;
				$customers->Employee->DeliveryAddress->Name2               = $orderInfo->employee->delivery_address_name2;
				$customers->Employee->DeliveryAddress->Address             = $orderInfo->employee->delivery_address_address;
				$customers->Employee->DeliveryAddress->Address2            = $orderInfo->employee->delivery_address_address2;
				$customers->Employee->DeliveryAddress->ZipCode             = $orderInfo->employee->delivery_address_zip;
				$customers->Employee->DeliveryAddress->City                = $orderInfo->employee->delivery_address_city;
				$customers->Employee->DeliveryAddress->CountryCode         = $orderInfo->employee->delivery_address_country_code;
				$customers->Employee->DeliveryAddress->StateCode           = $orderInfo->employee->delivery_address_state_code;
				$customers->Employee->DeliveryAddress->AddressType         = $orderInfo->employee->delivery_address_type;
				$customers->Employee->DeliveryAddress->DeliveryAddressCode = $orderInfo->employee->delivery_address_code;
			}
			elseif (!is_null($orderInfo) && isset($orderInfo->order))
			{
				$customers->Employee->YourReference   = $orderInfo->order->requisition;
				$customers->Employee->Comment         = $orderInfo->order->comment;
				$customers->Employee->DeliveryAddress = $deliveryAddress;
			}
		}

		if ($order->customer_department != '')
		{
			$department = RedshopbHelperDepartment::getDepartmentById($order->customer_department, false);

			$customers->Department->WebOrderNumber        = $orderId;
			$customers->Department->Id                    = $order->department_erp_id;
			$customers->Department->DateCreated           = $order->created_date;
			$customers->Department->RequestedDeliveryDate = null;

			if (isset($orderInfo->department))
			{
				$customers->Department->YourReference = $orderInfo->department->requisition;
				$customers->Department->Comment       = $orderInfo->department->comment;

				$customers->Department->DeliveryAddress                      = new stdClass;
				$customers->Department->DeliveryAddress->Name                = $orderInfo->department->delivery_address_name;
				$customers->Department->DeliveryAddress->Name2               = $orderInfo->department->delivery_address_name2;
				$customers->Department->DeliveryAddress->Address             = $orderInfo->department->delivery_address_address;
				$customers->Department->DeliveryAddress->Address2            = $orderInfo->department->delivery_address_address2;
				$customers->Department->DeliveryAddress->ZipCode             = $orderInfo->department->delivery_address_zip;
				$customers->Department->DeliveryAddress->City                = $orderInfo->department->delivery_address_city;
				$customers->Department->DeliveryAddress->CountryCode         = $orderInfo->department->delivery_address_country_code;
				$customers->Department->DeliveryAddress->StateCode           = $orderInfo->department->delivery_address_state_code;
				$customers->Department->DeliveryAddress->AddressType         = $orderInfo->department->delivery_address_type;
				$customers->Department->DeliveryAddress->DeliveryAddressCode = $orderInfo->department->delivery_address_code;
			}
			elseif (!is_null($orderInfo) && isset($orderInfo->order))
			{
				$customers->Department->YourReference   = $orderInfo->order->requisition;
				$customers->Department->Comment         = $orderInfo->order->comment;
				$customers->Department->DeliveryAddress = $deliveryAddress;
			}
		}

		if ($order->customer_company != '')
		{
			$company = RedshopbHelperCompany::getCompanyById($order->customer_company, false);

			if ($company->type == 'end_customer')
			{
				$customers->EndCustomer->WebOrderNumber        = $orderId;
				$customers->EndCustomer->Id                    = $order->company_erp_id;
				$customers->EndCustomer->DateCreated           = $order->created_date;
				$customers->EndCustomer->RequestedDeliveryDate = null;

				if (isset($orderInfo->endCustomer))
				{
					$customers->EndCustomer->YourReference = $orderInfo->endCustomer->requisition;
					$customers->EndCustomer->Comment       = $orderInfo->endCustomer->comment;

					$customers->EndCustomer->DeliveryAddress                      = new stdClass;
					$customers->EndCustomer->DeliveryAddress->Name                = $orderInfo->endCustomer->delivery_address_name;
					$customers->EndCustomer->DeliveryAddress->Name2               = $orderInfo->endCustomer->delivery_address_name2;
					$customers->EndCustomer->DeliveryAddress->Address             = $orderInfo->endCustomer->delivery_address_address;
					$customers->EndCustomer->DeliveryAddress->Address2            = $orderInfo->endCustomer->delivery_address_address2;
					$customers->EndCustomer->DeliveryAddress->ZipCode             = $orderInfo->endCustomer->delivery_address_zip;
					$customers->EndCustomer->DeliveryAddress->City                = $orderInfo->endCustomer->delivery_address_city;
					$customers->EndCustomer->DeliveryAddress->CountryCode         = $orderInfo->endCustomer->delivery_address_country_code;
					$customers->EndCustomer->DeliveryAddress->StateCode           = $orderInfo->endCustomer->delivery_address_state_code;
					$customers->EndCustomer->DeliveryAddress->AddressType         = $orderInfo->endCustomer->delivery_address_type;
					$customers->EndCustomer->DeliveryAddress->DeliveryAddressCode = $orderInfo->endCustomer->delivery_address_code;
				}
				elseif (!is_null($orderInfo) && isset($orderInfo->order))
				{
					$customers->EndCustomer->YourReference   = $orderInfo->order->requisition;
					$customers->EndCustomer->Comment         = $orderInfo->order->comment;
					$customers->EndCustomer->DeliveryAddress = $deliveryAddress;
				}
			}
		}

		return $customers;
	}

	/**
	 * Get order item types for log purpose.
	 *
	 * @param   int  $orderItemId  Order item id (product item id).
	 *
	 * @return array List of types for order item.
	 */
	private static function getOrderItemTypes($orderItemId)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);

		$query->select(
			array(
				$db->qn('name', 'TypeId'),
				$db->qn('sku', 'TypeSku')
			)
		)
			->from($db->qn('#__redshopb_order_item_attribute'))
			->where($db->qn('order_item_id') . ' = ' . (int) $orderItemId)
			->order('ordering');
		$db->setQuery($query);

		return $db->loadObjectList();
	}

	/**
	 * Get order item accessories for log purpose.
	 *
	 * @param   string  $lineId  Product line id.
	 *
	 * @return array List of accessories for order item.
	 */
	private static function getOrderItemServices($lineId)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select(
			array(
				$db->qn('oi.product_sku', 'ServiceItemNo'),
				$db->qn('oi.product_name', 'Description'),
				$db->qn('oi.price', 'Price'),
				$db->qn('oi.currency', 'Currency')
			)
		)
			->from($db->qn('#__redshopb_order_item', 'oi'))
			->where($db->qn('oi.parent_id') . ' = ' . (int) $lineId);
		$db->setQuery($query);
		$accessories = $db->loadObjectList();

		return $accessories;
	}

	/**
	 * Outputs XML string for given order log.
	 *
	 * @param   array  $orders  Array of order objects.
	 *
	 * @return string XML tree.
	 */
	public static function toString($orders)
	{
		$xml = '<Orders>';

		foreach ($orders as $order)
		{
			$xml     .= '<Order>';
			$customer = $order->Customer;
			$lines    = $order->Lines;

			$xml            .= '<Customer>';
			$xml            .= '     <WebOrderNumber>' . $customer->WebOrderNumber . '</WebOrderNumber>';
			$xml            .= '     <Id>' . $customer->company_erp_id . '</Id>';
			$xml            .= '     <DateCreated>' . $customer->DateCreated . '</DateCreated>';
			$xml            .= '     <RequestedDeliveryDate/>';
			$xml            .= '     <YourReference><![CDATA[' . $customer->YourReference . ']]></YourReference>';
			$xml            .= '     <Currency>' . $customer->Currency . '</Currency>';
			$xml            .= '     <Price>' . $customer->Price . '</Price>';
			$xml            .= '     <Comment><![CDATA[' . $customer->Comment . ']]></Comment>';
			$deliveryAddress = $customer->DeliveryAddress;

			$xml .= '     <DeliveryAddress>';
			$xml .= '          <DeliveryAddressCode>' . $deliveryAddress->DeliveryAddressCode . '</DeliveryAddressCode>';
			$xml .= '          <AddressType>' . $deliveryAddress->AddressType . '</AddressType>';

			if (isset($deliveryAddress->Name) && !empty($deliveryAddress->Name))
			{
				$xml .= '     <Name><![CDATA[' . $deliveryAddress->Name . ']]></Name>';
			}
			else
			{
				$xml .= '     <Name/>';
			}

			if (isset($deliveryAddress->Name2) && !empty($deliveryAddress->Name2))
			{
				$xml .= '     <Name2><![CDATA[' . $deliveryAddress->Name2 . ']]></Name2>';
			}
			else
			{
				$xml .= '     <Name2/>';
			}

			if (isset($deliveryAddress->Address) && !empty($deliveryAddress->Address))
			{
				$xml .= '     <Address><![CDATA[' . $deliveryAddress->Address . ']]></Address>';
			}
			else
			{
				$xml .= '     <Address/>';
			}

			if (isset($deliveryAddress->Address2) && !empty($deliveryAddress->Address2))
			{
				$xml .= '     <Address2><![CDATA[' . $deliveryAddress->Address2 . ']]></Address2>';
			}
			else
			{
				$xml .= '     <Address2/>';
			}

			if (isset($deliveryAddress->City) && !empty($deliveryAddress->City))
			{
				$xml .= '     <City><![CDATA[' . $deliveryAddress->City . ']]></City>';
			}
			else
			{
				$xml .= '     <City/>';
			}

			if (isset($deliveryAddress->ZipCode) && !empty($deliveryAddress->ZipCode))
			{
				$xml .= '     <ZipCode>' . $deliveryAddress->ZipCode . '</ZipCode>';
			}
			else
			{
				$xml .= '     <ZipCode/>';
			}

			if (isset($deliveryAddress->CountryCode) && !empty($deliveryAddress->CountryCode))
			{
				$xml .= '     <CountryCode>' . $deliveryAddress->CountryCode . '</CountryCode>';
			}
			else
			{
				$xml .= '     <CountryCode/>';
			}

			if (isset($deliveryAddress->StateCode) && !empty($deliveryAddress->StateCode))
			{
				$xml .= '     <StateCode>' . $deliveryAddress->StateCode . '</StateCode>';
			}
			else
			{
				$xml .= '     <StateCode/>';
			}

			$xml .= '     </DeliveryAddress>';
			$xml .= '</Customer>';
			$xml .= '<Lines>';

			foreach ($lines as $line)
			{
				$xml .= '<Line>';
				$xml .= '<productNo>' . $line->ProductNo . '</productNo>';
				$xml .= '     <Description><![CDATA[' . $line->Description . ']]></Description>';
				$xml .= '     <quantity>' . $line->Quantity . '</quantity>';
				$xml .= '     <Currency>' . $line->Currency . '</Currency>';
				$xml .= '     <Price>' . $line->Price . '</Price>';
				$xml .= '     <collectionNo>' . $line->collectionNo . '</collectionNo>';

				if (isset($line->Types) && !is_null($line->Types) && is_array($line->Types))
				{
					$xml .= '     <types>';

					foreach ($line->Types as $type)
					{
						$xml .= '     <type>';
						$xml .= '          <typeId>' . $type->TypeId . '</typeId>';
						$xml .= '          <typeSku>' . $type->TypeSku . '</typeSku>';
						$xml .= '     </type>';
					}

					$xml .= '     </types>';
				}
				else
				{
					$xml .= '     <types />';
				}

				if (isset($line->ItemServices) && !is_null($line->ItemServices) && is_array($line->ItemServices))
				{
					$xml .= '     <ItemServices>';

					foreach ($line->ItemServices as $service)
					{
						$xml .= '     <ItemService>';
						$xml .= '          <ServiceItemNo>' . $service->ServiceItemNo . '</ServiceItemNo>';
						$xml .= '          <Description><![CDATA[' . $service->Description . ']]></Description>';
						$xml .= '          <Currency>' . $service->Currency . '</Currency>';
						$xml .= '          <Price>' . $service->Price . '</Price>';
						$xml .= '     </ItemService>';
					}

					$xml .= '     </ItemServices>';
				}
				else
				{
					$xml .= '     <ItemServices/>';
				}

				$endCustomer = $line->EndCustomer;

				if (isset($endCustomer) && isset($endCustomer->WebOrderNumber))
				{
					$xml .= '     <EndCustomer>';
					$xml .= '          <WebOrderNumber>' . $endCustomer->WebOrderNumber . '</WebOrderNumber>';
					$xml .= '          <Id>' . $endCustomer->Id . '</Id>';

					if (isset($endCustomer->DateCreated))
					{
						$xml .= '      <DateCreated>' . $endCustomer->DateCreated . '</DateCreated>';
					}
					else
					{
						$xml .= '      <DateCreated/>';
					}

					$xml .= '          <RequestedDeliveryDate/>';

					if (isset($endCustomer->YourReference))
					{
						$xml .= '      <YourReference><![CDATA[' . $endCustomer->YourReference . ']]></YourReference>';
					}
					else
					{
						$xml .= '      <YourReference/>';
					}

					if (isset($endCustomer->Comment))
					{
						$xml .= '      <Comment><![CDATA[' . $endCustomer->Comment . ']]></Comment>';
					}
					else
					{
						$xml .= '      <Comment/>';
					}

					if (isset($endCustomer->DeliveryAddress))
					{
						$deliveryAddress = $endCustomer->DeliveryAddress;
						$xml            .= '       <DeliveryAddress>';
						$xml            .= '            <DeliveryAddressCode>' . $deliveryAddress->DeliveryAddressCode . '</DeliveryAddressCode>';
						$xml            .= '            <AddressType>' . $deliveryAddress->AddressType . '</AddressType>';

						if (isset($deliveryAddress->Name) && !empty($deliveryAddress->Name))
						{
							$xml .= '        <Name><![CDATA[' . $deliveryAddress->Name . ']]></Name>';
						}
						else
						{
							$xml .= '        <Name/>';
						}

						if (isset($deliveryAddress->Name2) && !empty($deliveryAddress->Name2))
						{
							$xml .= '        <Name2><![CDATA[' . $deliveryAddress->Name2 . ']]></Name2>';
						}
						else
						{
							$xml .= '        <Name2/>';
						}

						if (isset($deliveryAddress->Address) && !empty($deliveryAddress->Address))
						{
							$xml .= '        <Address><![CDATA[' . $deliveryAddress->Address . ']]></Address>';
						}
						else
						{
							$xml .= '        <Address/>';
						}

						if (isset($deliveryAddress->Address2) && !empty($deliveryAddress->Address2))
						{
							$xml .= '        <Address2><![CDATA[' . $deliveryAddress->Address2 . ']]></Address2>';
						}
						else
						{
							$xml .= '        <Address2/>';
						}

						if (isset($deliveryAddress->City) && !empty($deliveryAddress->City))
						{
							$xml .= '        <City><![CDATA[' . $deliveryAddress->City . ']]></City>';
						}
						else
						{
							$xml .= '        <City/>';
						}

						if (isset($deliveryAddress->ZipCode) && !empty($deliveryAddress->ZipCode))
						{
							$xml .= '        <ZipCode>' . $deliveryAddress->ZipCode . '</ZipCode>';
						}
						else
						{
							$xml .= '        <ZipCode/>';
						}

						if (isset($deliveryAddress->CountryCode) && !empty($deliveryAddress->CountryCode))
						{
							$xml .= '        <CountryCode>' . $deliveryAddress->CountryCode . '</CountryCode>';
						}
						else
						{
							$xml .= '        <CountryCode/>';
						}

						if (isset($deliveryAddress->StateCode) && !empty($deliveryAddress->StateCode))
						{
							$xml .= '        <StateCode>' . $deliveryAddress->StateCode . '</StateCode>';
						}
						else
						{
							$xml .= '        <StateCode/>';
						}

						$xml .= '      </DeliveryAddress>';
					}
					else
					{
						$xml .= '          <DeliveryAddress>';
						$xml .= '               <DeliveryAddressCode/>';
						$xml .= '               <AddressType/>';
						$xml .= '               <Name/>';
						$xml .= '               <Name2/>';
						$xml .= '               <Address/>';
						$xml .= '               <Address2/>';
						$xml .= '               <City/>';
						$xml .= '               <ZipCode/>';
						$xml .= '               <CountryCode/>';
						$xml .= '               <StateCode/>';
						$xml .= '          </DeliveryAddress>';
					}

					$xml .= '     </EndCustomer>';
				}
				else
				{
					$xml .= '     <EndCustomer>';
					$xml .= '          <WebOrderNumber/>';
					$xml .= '          <Id/>';
					$xml .= '          <DateCreated/>';
					$xml .= '          <RequestedDeliveryDate/>';
					$xml .= '          <YourReference/>';
					$xml .= '          <Comment/>';
					$xml .= '          <DeliveryAddress>';
					$xml .= '               <DeliveryAddressCode/>';
					$xml .= '               <AddressType/>';
					$xml .= '               <Name/>';
					$xml .= '               <Name2/>';
					$xml .= '               <Address/>';
					$xml .= '               <Address2/>';
					$xml .= '               <City/>';
					$xml .= '               <ZipCode/>';
					$xml .= '               <CountryCode/>';
					$xml .= '               <StateCode/>';
					$xml .= '          </DeliveryAddress>';
					$xml .= '     </EndCustomer>';
				}

				$department = $line->Department;

				if (isset($department) && isset($department->WebOrderNumber))
				{
					$xml .= '     <Department>';
					$xml .= '          <WebOrderNumber>' . $department->WebOrderNumber . '</WebOrderNumber>';
					$xml .= '          <Id>' . $department->Id . '</Id>';

					if (isset($department->DateCreated))
					{
						$xml .= '      <DateCreated>' . $department->DateCreated . '</DateCreated>';
					}
					else
					{
						$xml .= '      <DateCreated/>';
					}

					$xml .= '          <RequestedDeliveryDate/>';

					if (isset($department->YourReference))
					{
						$xml .= '      <YourReference><![CDATA[' . $department->YourReference . ']]></YourReference>';
					}
					else
					{
						$xml .= '      <YourReference/>';
					}

					if (isset($department->Comment))
					{
						$xml .= '      <Comment><![CDATA[' . $department->Comment . ']]></Comment>';
					}
					else
					{
						$xml .= '      <Comment/>';
					}

					if (isset($department->DeliveryAddress))
					{
						$deliveryAddress = $department->DeliveryAddress;
						$xml            .= '       <DeliveryAddress>';
						$xml            .= '            <DeliveryAddressCode>' . $deliveryAddress->DeliveryAddressCode . '</DeliveryAddressCode>';
						$xml            .= '            <AddressType>' . $deliveryAddress->AddressType . '</AddressType>';

						if (isset($deliveryAddress->Name) && !empty($deliveryAddress->Name))
						{
							$xml .= '        <Name><![CDATA[' . $deliveryAddress->Name . ']]></Name>';
						}
						else
						{
							$xml .= '        <Name/>';
						}

						if (isset($deliveryAddress->Name2) && !empty($deliveryAddress->Name2))
						{
							$xml .= '        <Name2><![CDATA[' . $deliveryAddress->Name2 . ']]></Name2>';
						}
						else
						{
							$xml .= '        <Name2/>';
						}

						if (isset($deliveryAddress->Address) && !empty($deliveryAddress->Address))
						{
							$xml .= '        <Address><![CDATA[' . $deliveryAddress->Address . ']]></Address>';
						}
						else
						{
							$xml .= '        <Address/>';
						}

						if (isset($deliveryAddress->Address2) && !empty($deliveryAddress->Address2))
						{
							$xml .= '        <Address2><![CDATA[' . $deliveryAddress->Address2 . ']]></Address2>';
						}
						else
						{
							$xml .= '        <Address2/>';
						}

						if (isset($deliveryAddress->City) && !empty($deliveryAddress->City))
						{
							$xml .= '        <City><![CDATA[' . $deliveryAddress->City . ']]></City>';
						}
						else
						{
							$xml .= '        <City/>';
						}

						if (isset($deliveryAddress->ZipCode) && !empty($deliveryAddress->ZipCode))
						{
							$xml .= '        <ZipCode>' . $deliveryAddress->ZipCode . '</ZipCode>';
						}
						else
						{
							$xml .= '        <ZipCode/>';
						}

						if (isset($deliveryAddress->CountryCode) && !empty($deliveryAddress->CountryCode))
						{
							$xml .= '        <CountryCode>' . $deliveryAddress->CountryCode . '</CountryCode>';
						}
						else
						{
							$xml .= '        <CountryCode/>';
						}

						if (isset($deliveryAddress->StateCode) && !empty($deliveryAddress->StateCode))
						{
							$xml .= '        <StateCode>' . $deliveryAddress->StateCode . '</StateCode>';
						}
						else
						{
							$xml .= '        <StateCode/>';
						}

						$xml .= '      </DeliveryAddress>';
					}
					else
					{
						$xml .= '          <DeliveryAddress>';
						$xml .= '               <DeliveryAddressCode/>';
						$xml .= '               <AddressType/>';
						$xml .= '               <Name/>';
						$xml .= '               <Name2/>';
						$xml .= '               <Address/>';
						$xml .= '               <Address2/>';
						$xml .= '               <City/>';
						$xml .= '               <ZipCode/>';
						$xml .= '               <CountryCode/>';
						$xml .= '               <StateCode/>';
						$xml .= '          </DeliveryAddress>';
					}

					$xml .= '     </Department>';
				}
				else
				{
					$xml .= '     <Department>';
					$xml .= '          <WebOrderNumber/>';
					$xml .= '          <Id/>';
					$xml .= '          <DateCreated/>';
					$xml .= '          <RequestedDeliveryDate/>';
					$xml .= '          <YourReference/>';
					$xml .= '          <Comment/>';
					$xml .= '          <DeliveryAddress>';
					$xml .= '               <DeliveryAddressCode/>';
					$xml .= '               <AddressType/>';
					$xml .= '               <Name/>';
					$xml .= '               <Name2/>';
					$xml .= '               <Address/>';
					$xml .= '               <Address2/>';
					$xml .= '               <City/>';
					$xml .= '               <ZipCode/>';
					$xml .= '               <CountryCode/>';
					$xml .= '               <StateCode/>';
					$xml .= '          </DeliveryAddress>';
					$xml .= '     </Department>';
				}

				$employee = $line->Employee;

				if (isset($employee) && isset($employee->WebOrderNumber))
				{
					$xml .= '     <Employee>';
					$xml .= '          <WebOrderNumber>' . $employee->WebOrderNumber . '</WebOrderNumber>';
					$xml .= '          <Id>' . $employee->Id . '</Id>';

					if (isset($employee->DateCreated))
					{
						$xml .= '      <DateCreated>' . $employee->DateCreated . '</DateCreated>';
					}
					else
					{
						$xml .= '      <DateCreated/>';
					}

					$xml .= '          <RequestedDeliveryDate/>';

					if (isset($employee->YourReference))
					{
						$xml .= '      <YourReference><![CDATA[' . $employee->YourReference . ']]></YourReference>';
					}
					else
					{
						$xml .= '      <YourReference/>';
					}

					if (isset($employee->Comment))
					{
						$xml .= '      <Comment><![CDATA[' . $employee->Comment . ']]></Comment>';
					}
					else
					{
						$xml .= '      <Comment/>';
					}

					if (isset($employee->DeliveryAddress))
					{
						$deliveryAddress = $employee->DeliveryAddress;
						$xml            .= '       <DeliveryAddress>';
						$xml            .= '            <DeliveryAddressCode>' . $deliveryAddress->DeliveryAddressCode . '</DeliveryAddressCode>';
						$xml            .= '            <AddressType>' . $deliveryAddress->AddressType . '</AddressType>';

						if (isset($deliveryAddress->Name) && !empty($deliveryAddress->Name))
						{
							$xml .= '        <Name><![CDATA[' . $deliveryAddress->Name . ']]></Name>';
						}
						else
						{
							$xml .= '        <Name/>';
						}

						if (isset($deliveryAddress->Name2) && !empty($deliveryAddress->Name2))
						{
							$xml .= '        <Name2><![CDATA[' . $deliveryAddress->Name2 . ']]></Name2>';
						}
						else
						{
							$xml .= '        <Name2/>';
						}

						if (isset($deliveryAddress->Address) && !empty($deliveryAddress->Address))
						{
							$xml .= '        <Address><![CDATA[' . $deliveryAddress->Address . ']]></Address>';
						}
						else
						{
							$xml .= '        <Address/>';
						}

						if (isset($deliveryAddress->Address2) && !empty($deliveryAddress->Address2))
						{
							$xml .= '        <Address2><![CDATA[' . $deliveryAddress->Address2 . ']]></Address2>';
						}
						else
						{
							$xml .= '        <Address2/>';
						}

						if (isset($deliveryAddress->City) && !empty($deliveryAddress->City))
						{
							$xml .= '        <City><![CDATA[' . $deliveryAddress->City . ']]></City>';
						}
						else
						{
							$xml .= '        <City/>';
						}

						if (isset($deliveryAddress->ZipCode) && !empty($deliveryAddress->ZipCode))
						{
							$xml .= '        <ZipCode>' . $deliveryAddress->ZipCode . '</ZipCode>';
						}
						else
						{
							$xml .= '        <ZipCode/>';
						}

						if (isset($deliveryAddress->CountryCode) && !empty($deliveryAddress->CountryCode))
						{
							$xml .= '        <CountryCode>' . $deliveryAddress->CountryCode . '</CountryCode>';
						}
						else
						{
							$xml .= '        <CountryCode/>';
						}

						if (isset($deliveryAddress->StateCode) && !empty($deliveryAddress->StateCode))
						{
							$xml .= '        <StateCode>' . $deliveryAddress->StateCode . '</StateCode>';
						}
						else
						{
							$xml .= '        <StateCode/>';
						}

						$xml .= '      </DeliveryAddress>';
					}
					else
					{
						$xml .= '          <DeliveryAddress>';
						$xml .= '               <DeliveryAddressCode/>';
						$xml .= '               <AddressType/>';
						$xml .= '               <Name/>';
						$xml .= '               <Name2/>';
						$xml .= '               <Address/>';
						$xml .= '               <Address2/>';
						$xml .= '               <City/>';
						$xml .= '               <ZipCode/>';
						$xml .= '               <CountryCode/>';
						$xml .= '               <StateCode/>';
						$xml .= '          </DeliveryAddress>';
					}

					$xml .= '     </Employee>';
				}
				else
				{
					$xml .= '     <Employee>';
					$xml .= '          <WebOrderNumber/>';
					$xml .= '          <Id/>';
					$xml .= '          <DateCreated/>';
					$xml .= '          <RequestedDeliveryDate/>';
					$xml .= '          <YourReference/>';
					$xml .= '          <Comment/>';
					$xml .= '          <DeliveryAddress>';
					$xml .= '               <DeliveryAddressCode/>';
					$xml .= '               <AddressType/>';
					$xml .= '               <Name/>';
					$xml .= '               <Name2/>';
					$xml .= '               <Address/>';
					$xml .= '               <Address2/>';
					$xml .= '               <City/>';
					$xml .= '               <ZipCode/>';
					$xml .= '               <CountryCode/>';
					$xml .= '               <StateCode/>';
					$xml .= '          </DeliveryAddress>';
					$xml .= '     </Employee>';
				}

				$xml .= '</Line>';
			}

			$xml .= '</Lines>';

			$xml .= '</Order>';
		}

		$xml .= '</Orders>';

		return $xml;
	}

	/**
	 * Get child order ids for given order id.
	 *
	 * @param   array    $orderIds          Order ids.
	 * @param   boolean  $collectOrderInfo  (optional) When true, looks and returns only the last children of each branch,
	 *                                                 leaving the tree in the array and the order info (address, comment,
	 *                                                 req no for each entity - end customer, department, employee) in each index of the matrix
	 *
	 * @return array Order ids.
	 */
	public static function getChildOrders($orderIds, $collectOrderInfo = false)
	{
		if (empty($orderIds))
		{
			return array();
		}

		// If collecting order information, it can only be for one order at a time or it won't make sense
		if ($collectOrderInfo && count($orderIds) > 1)
		{
			$orderIds = array($orderIds[0]);
		}

		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->select('DISTINCT ' . $db->qn('order_id'))
			->from($db->qn('#__redshopb_order_logs'))
			->where($db->qn('new_order_id') . ' IN (' . implode(',', $orderIds) . ')');
		$db->setQuery($query);

		$directOrders = $db->loadColumn();

		if (is_null($directOrders) || empty($directOrders))
		{
			return array();
		}

		if ($collectOrderInfo)
		{
			$resultOrders = array();

			foreach ($directOrders as $orderId)
			{
				$query->clear()
					->select('*')
					->from($db->qn('#__redshopb_order'))
					->where($db->qn('id') . ' = ' . $orderId);
				$db->setQuery($query);
				$orderInfo    = $db->loadObject();
				$orderCompany = RedshopbHelperCompany::getCompanyByCustomer($orderInfo->customer_id, $orderInfo->customer_type, false);

				// Sets order information for each child order.  When going up in the chain this info will be overriden by parent info when required
				$resultOrderInfo                = new stdClass;
				$resultOrderInfo->customer_type = $orderInfo->customer_type;

				// Collects info (endCustomer, department and employee) only for end customers
				if ($orderCompany->type == 'end_customer')
				{
					$resultOrderInfo->endCustomer                                = new stdClass;
					$resultOrderInfo->endCustomer->delivery_address_id           = $orderInfo->delivery_address_id;
					$resultOrderInfo->endCustomer->delivery_address_name         = $orderInfo->delivery_address_name;
					$resultOrderInfo->endCustomer->delivery_address_name2        = $orderInfo->delivery_address_name2;
					$resultOrderInfo->endCustomer->delivery_address_address      = $orderInfo->delivery_address_address;
					$resultOrderInfo->endCustomer->delivery_address_address2     = $orderInfo->delivery_address_address2;
					$resultOrderInfo->endCustomer->delivery_address_zip          = $orderInfo->delivery_address_zip;
					$resultOrderInfo->endCustomer->delivery_address_city         = $orderInfo->delivery_address_city;
					$resultOrderInfo->endCustomer->delivery_address_country      = $orderInfo->delivery_address_country;
					$resultOrderInfo->endCustomer->delivery_address_country_code = $orderInfo->delivery_address_country_code;
					$resultOrderInfo->endCustomer->delivery_address_code         = $orderInfo->delivery_address_code;
					$resultOrderInfo->endCustomer->delivery_address_type         = $orderInfo->delivery_address_type;
					$resultOrderInfo->endCustomer->comment                       = $orderInfo->comment;
					$resultOrderInfo->endCustomer->requisition                   = $orderInfo->requisition;

					// Department info only for departments and employees with department
					if ($orderInfo->customer_type == 'department' || $orderInfo->customer_type == 'employee')
					{
						$resultOrderInfo->department                                = new stdClass;
						$resultOrderInfo->department->delivery_address_id           = $orderInfo->delivery_address_id;
						$resultOrderInfo->department->delivery_address_name         = $orderInfo->delivery_address_name;
						$resultOrderInfo->department->delivery_address_name2        = $orderInfo->delivery_address_name2;
						$resultOrderInfo->department->delivery_address_address      = $orderInfo->delivery_address_address;
						$resultOrderInfo->department->delivery_address_address2     = $orderInfo->delivery_address_address2;
						$resultOrderInfo->department->delivery_address_zip          = $orderInfo->delivery_address_zip;
						$resultOrderInfo->department->delivery_address_city         = $orderInfo->delivery_address_city;
						$resultOrderInfo->department->delivery_address_country      = $orderInfo->delivery_address_country;
						$resultOrderInfo->department->delivery_address_country_code = $orderInfo->delivery_address_country_code;
						$resultOrderInfo->department->delivery_address_code         = $orderInfo->delivery_address_code;
						$resultOrderInfo->department->delivery_address_type         = $orderInfo->delivery_address_type;
						$resultOrderInfo->department->comment                       = $orderInfo->comment;
						$resultOrderInfo->department->requisition                   = $orderInfo->requisition;

						// Employee info only for employees
						if ($orderInfo->customer_type == 'employee')
						{
							$resultOrderInfo->employee                                = new stdClass;
							$resultOrderInfo->employee->delivery_address_id           = $orderInfo->delivery_address_id;
							$resultOrderInfo->employee->delivery_address_name         = $orderInfo->delivery_address_name;
							$resultOrderInfo->employee->delivery_address_name2        = $orderInfo->delivery_address_name2;
							$resultOrderInfo->employee->delivery_address_address      = $orderInfo->delivery_address_address;
							$resultOrderInfo->employee->delivery_address_address2     = $orderInfo->delivery_address_address2;
							$resultOrderInfo->employee->delivery_address_zip          = $orderInfo->delivery_address_zip;
							$resultOrderInfo->employee->delivery_address_city         = $orderInfo->delivery_address_city;
							$resultOrderInfo->employee->delivery_address_country      = $orderInfo->delivery_address_country;
							$resultOrderInfo->employee->delivery_address_country_code = $orderInfo->delivery_address_country_code;
							$resultOrderInfo->employee->delivery_address_code         = $orderInfo->delivery_address_code;
							$resultOrderInfo->employee->delivery_address_type         = $orderInfo->delivery_address_type;
							$resultOrderInfo->employee->comment                       = $orderInfo->comment;
							$resultOrderInfo->employee->requisition                   = $orderInfo->requisition;

							// Unsets department if the employee doesn't have one
							if (!RedshopbHelperUser::getUserDepartmentId($orderInfo->customer_id, 'redshopb', true))
							{
								$resultOrderInfo->department = null;
							}
						}
					}
				}

				$childrenOrders = self::getChildOrders(array($orderId), true);

				if (count($childrenOrders))
				{
					foreach ($childrenOrders as $childOrderId => $childOrderInfo)
					{
						switch ($childOrderInfo->customer_type)
						{
							case 'employee':
								if ($resultOrderInfo->customer_type == 'department' && isset($resultOrderInfo->department))
								{
									$childOrderInfo->department = $resultOrderInfo->department;
								}

								if ($resultOrderInfo->customer_type == 'company' && isset($resultOrderInfo->endCustomer))
								{
									$childOrderInfo->endCustomer = $resultOrderInfo->endCustomer;
								}

								break;

							case 'department':
								if ($resultOrderInfo->customer_type == 'company' && isset($resultOrderInfo->endCustomer))
								{
									$childOrderInfo->endCustomer = $resultOrderInfo->endCustomer;
								}
								break;
						}

						$resultOrders[$childOrderId] = $childOrderInfo;
					}
				}
				else
				{
					$resultOrders[$orderId] = $resultOrderInfo;
				}
			}
		}
		else
		{
			$resultOrders = $directOrders;
		}

		return $resultOrders;
	}
}
