<?php
/**
 * @package     Aesir.E-Commerce.Libraries
 * @subpackage  Helper
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Date\Date;

/**
 * Shop helper.
 *
 * @package     Aesir.E-Commerce.Libraries
 * @subpackage  Helper
 * @since       1.0
 */
final class RedshopbHelperStockroom
{
	/**
	 * Method for get stockroom of an product
	 *
	 * @param   int  $productId    ID of product
	 * @param   int  $stockroomId  ID of stockroom
	 *
	 * @return  object|boolean  Data of stockroom
	 * @since   1.0
	 */
	public static function getProductStockroomData($productId = 0, $stockroomId = 0)
	{
		if (!$productId || !$stockroomId)
		{
			return false;
		}

		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->select('s.*')
			->select('ref.*')
			->from($db->qn('#__redshopb_stockroom_product_xref', 'ref'))
			->leftJoin($db->qn('#__redshopb_stockroom', 's') . ' ON ' . $db->qn('s.id') . ' = ' . $db->qn('ref.stockroom_id'))
			->where($db->qn('ref.product_id') . ' = ' . (int) $productId)
			->where($db->qn('ref.stockroom_id') . ' = ' . (int) $stockroomId);

		return $db->setQuery($query)->loadObject();
	}

	/**
	 * Method for get stockroom of list product item
	 *
	 * @param   array|int  $productIds  List ID of product
	 * @param   array|int  $stockrooms  List ID of stockroom
	 *
	 * @return  array|boolean           Data of stockroom
	 * @since   1.0
	 */
	public static function getProductsStockroomData($productIds = array(), $stockrooms = array())
	{
		$productIds = (array) $productIds;
		$stockrooms = (array) $stockrooms;

		if (empty($productIds))
		{
			return false;
		}

		$productIds = ArrayHelper::toInteger($productIds);

		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->select('ref.*')
			->select($db->qn('s.name', 'name'))
			->select($db->qn('s.color', 'color'))
			->select($db->qn('s.description', 'description'))
			->select('CONCAT(' . $db->qn('ref.product_id') . ',' . $db->quote('_') . ',' . $db->qn('ref.stockroom_id') . ') AS ' . $db->qn('key'))
			->from($db->qn('#__redshopb_stockroom_product_xref', 'ref'))
			->leftJoin($db->qn('#__redshopb_stockroom', 's') . ' ON ' . $db->qn('s.id') . ' = ' . $db->qn('ref.stockroom_id'))
			->where($db->qn('ref.product_id') . ' IN (' . implode(',', $productIds) . ')')
			->order($db->qn('s.min_delivery_time') . ' ASC');

		if (!empty($stockrooms))
		{
			$stockrooms = ArrayHelper::toInteger($stockrooms);

			$query->where($db->qn('ref.stockroom_id') . ' IN (' . implode(',', $stockrooms) . ')');
		}

		return $db->setQuery($query)->loadObjectList('key');
	}

	/**
	 * Check product exists in stock
	 *
	 * @param   integer  $productId  Product id
	 *
	 * @return  boolean
	 * @since   1.0
	 */
	public static function productHasInStock($productId)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->select('p.id')
			->from($db->qn('#__redshopb_product', 'p'))
			->leftJoin($db->qn('#__redshopb_stockroom_product_xref', 'spx') . ' ON p.id = spx.product_id')
			->leftJoin($db->qn('#__redshopb_product_item', 'pi') . ' ON pi.product_id = p.id')
			->leftJoin($db->qn('#__redshopb_stockroom_product_item_xref', 'spix') . ' ON spix.product_item_id = pi.id')
			->where('p.id = ' . (int) $productId)
			->where('(spx.product_id IS NOT NULL OR spix.product_item_id IS NOT NULL)');

		// Stock relation exists, then check amount in stocks
		if ($db->setQuery($query)->loadResult())
		{
			$query = $db->getQuery(true)
				->select('p.id')
				->from($db->qn('#__redshopb_product', 'p'))
				->leftJoin($db->qn('#__redshopb_stockroom_product_xref', 'spx') . ' ON p.id = spx.product_id')
				->leftJoin($db->qn('#__redshopb_product_item', 'pi') . ' ON pi.product_id = p.id')
				->leftJoin($db->qn('#__redshopb_stockroom_product_item_xref', 'spix') . ' ON spix.product_item_id = pi.id')
				->where('p.id = ' . (int) $productId)
				->where('(spx.unlimited = 1 OR spx.amount > 0 OR spix.unlimited = 1 OR spix.amount > 0)');

			// Find positive amount for product
			if ($db->setQuery($query)->loadResult())
			{
				return true;
			}
			else
			{
				return false;
			}
		}

		// Stock relations not exists, then set product as available
		else
		{
			return true;
		}
	}

	/**
	 * Method for get stockroom of an product item
	 *
	 * @param   int    $productItemId  ID of product item
	 * @param   array  $stockrooms     List ID of stockroom
	 *
	 * @return  object|boolean         Data of stockroom
	 * @since   1.0
	 */
	public static function getProductItemStockroomData($productItemId = 0, $stockrooms = array())
	{
		$productItemId = (int) $productItemId;

		if (!$productItemId)
		{
			return false;
		}

		return self::getProductItemsStockroomData(array($productItemId), $stockrooms);
	}

	/**
	 * Method for get stockroom of list product item
	 *
	 * @param   array  $productItemIds  List ID of product item
	 * @param   array  $stockrooms      List ID of stockroom
	 *
	 * @return  array|boolean           Data of stockroom
	 * @since   1.0
	 */
	public static function getProductItemsStockroomData($productItemIds = array(), $stockrooms = array())
	{
		if (empty($productItemIds) || !is_array($productItemIds))
		{
			return false;
		}

		$productItemIds = ArrayHelper::toInteger($productItemIds);

		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->select('ref.*')
			->select($db->qn('s.name', 'name'))
			->select($db->qn('s.description', 'description'))
			->select(
				'CONCAT(' . $db->qn('ref.product_item_id') . ',' . $db->quote('_') . ',' . $db->qn('ref.stockroom_id') . ') AS ' . $db->qn('key')
			)
			->from($db->qn('#__redshopb_stockroom_product_item_xref', 'ref'))
			->leftJoin($db->qn('#__redshopb_stockroom', 's') . ' ON ' . $db->qn('s.id') . ' = ' . $db->qn('ref.stockroom_id'))
			->where($db->qn('ref.product_item_id') . ' IN (' . implode(',', $productItemIds) . ')');

		if (!empty($stockrooms) && $stockrooms[0] != null)
		{
			if (!is_array($stockrooms))
			{
				$stockrooms = array($stockrooms);
			}

			$stockrooms = ArrayHelper::toInteger($stockrooms);

			$query->where($db->qn('ref.stockroom_id') . ' IN (' . implode(',', $stockrooms) . ')');
		}

		return $db->setQuery($query)->loadObjectList('key');
	}

	/**
	 * Method for check if available of product with stockroom:
	 *       - True: If there are no stockroom.
	 *       - True: If there are have some stockroom for this product and have at least 1 stockroom with amount is higher than 0.
	 *       - False: Otherwise.
	 *
	 * @param   int   $productId  Id of product.
	 * @param   bool  $countZero  Return true if 0 stockrooms are found.
	 *
	 * @return  boolean
	 * @since   1.0
	 */
	public static function checkProductHasAvailableStockroom($productId, $countZero = true)
	{
		$productId = (int) $productId;

		if (!$productId)
		{
			return false;
		}

		$db = Factory::getDbo();

		if ($countZero)
		{
			$cZero = 'WHEN COUNT(' . $db->qn('stockref.id') . ') = 0 THEN 1';
		}
		else
		{
			$cZero = 'WHEN COUNT(' . $db->qn('stockref.id') . ') = 0 THEN 0';
		}

		$query = $db->getQuery(true)
			->select(
				'CASE
					' . $cZero . '
					WHEN COUNT(' . $db->qn('stockref.id') . ') > 0 THEN
						CASE
							WHEN (SELECT SUM(' . $db->qn('amount') . ') FROM ' . $db->qn('#__redshopb_stockroom_product_xref', 'spx')
								. ' WHERE ' . $db->qn('spx.product_id') . ' = ' . $db->qn('stockref.product_id') . ') > 0 THEN 1
							WHEN (SELECT SUM(' . $db->qn('unlimited') . ') FROM ' . $db->qn('#__redshopb_stockroom_product_xref', 'spx')
								. ' WHERE ' . $db->qn('spx.product_id') . ' = ' . $db->qn('stockref.product_id') . ') > 0 THEN 1
							ELSE 0
						END
				END AS ' . $db->qn('stock_available')
			)
			->from($db->qn('#__redshopb_stockroom_product_xref', 'stockref'))
			->where($db->qn('stockref.product_id') . ' = ' . $productId);

		return (boolean) $db->setQuery($query)->loadResult();
	}

	/**
	 * Method for get available stockroom of product with piority:
	 *       - Stockroom with minimum delivery time.
	 *       - Stockroom with unlimited amount.
	 *       - Stockroom with highest available amount.
	 *
	 * @param   integer        $productId      Id of product.
	 * @param   integer        $productItemId  Id of product item.
	 * @param   integer|float  $quantity       Product amount to be taken from the stock.
	 *
	 * @return  integer|boolean      Id of available stockroom. False otherwise.
	 * @since   1.0
	 */
	public static function getProductAvailableStockroom($productId, $productItemId = 0, $quantity = 1)
	{
		$productId     = (int) $productId;
		$productItemId = (int) $productItemId;
		$db            = Factory::getDbo();
		$query         = $db->getQuery(true);

		if (!$productId && !$productItemId)
		{
			return false;
		}

		if ($productItemId)
		{
			$query->from($db->qn('#__redshopb_stockroom_product_item_xref', 'spx'))
				->where($db->qn('spx.product_item_id') . ' = ' . $productItemId);
		}
		else
		{
			$query->from($db->qn('#__redshopb_stockroom_product_xref', 'spx'))
				->where($db->qn('spx.product_id') . ' = ' . $productId);
		}

		$query->select($db->qn('spx.stockroom_id'))
			->innerJoin($db->qn('#__redshopb_stockroom', 's') . ' ON ' . $db->qn('s.id') . ' = ' . $db->qn('spx.stockroom_id'))
			->where('(' . $db->qn('spx.unlimited') . ' = 1 OR ' . $db->qn('spx.amount') . ' >= ' . (float) $quantity . ')')
			->order($db->qn('s.min_delivery_time') . ' ASC,' . $db->qn('spx.unlimited') . ' DESC, ' . $db->qn('spx.amount') . ' DESC');

		return $db->setQuery($query, 0, 1)->loadResult();
	}

	/**
	 * Method for check if available of product item with stockroom:
	 *       - True: If there are no stockroom.
	 *       - True: If there are have some stockroom for this product and have at least 1 stockroom with amount is higher than 0.
	 *       - False: Otherwise.
	 *
	 * @param   int   $productItemId  Id of product item.
	 * @param   bool  $countZero      Return true if 0 stockrooms are found.
	 *
	 * @return  boolean
	 * @since   1.0
	 */
	public static function checkProductItemHasAvailableStockroom($productItemId, $countZero = true)
	{
		$productItemId = (int) $productItemId;

		if (!$productItemId)
		{
			return false;
		}

		$db = Factory::getDbo();

		if ($countZero)
		{
			$cZero = 'WHEN COUNT(' . $db->qn('stockref.id') . ') = 0 THEN 1';
		}
		else
		{
			$cZero = 'WHEN COUNT(' . $db->qn('stockref.id') . ') = 0 THEN 0';
		}

		$query = $db->getQuery(true)
			->select(
				'CASE
					' . $cZero . '
					WHEN COUNT(' . $db->qn('stockref.id') . ') > 0 THEN
						CASE
							WHEN (SELECT SUM(' . $db->qn('amount') . ') FROM ' . $db->qn('#__redshopb_stockroom_product_item_xref', 'spx')
								. ' WHERE ' . $db->qn('spx.product_item_id') . ' = ' . $db->qn('stockref.product_item_id') . ') > 0 THEN 1
							WHEN (SELECT SUM(' . $db->qn('unlimited') . ') FROM ' . $db->qn('#__redshopb_stockroom_product_item_xref', 'spx')
								. ' WHERE ' . $db->qn('spx.product_item_id') . ' = ' . $db->qn('stockref.product_item_id') . ') > 0 THEN 1
							ELSE 0
						END
				END AS ' . $db->qn('stock_available')
			)
			->from($db->qn('#__redshopb_stockroom_product_item_xref', 'stockref'))
			->where($db->qn('stockref.product_item_id') . ' = ' . $productItemId);

		return (boolean) $db->setQuery($query)->loadResult();
	}

	/**
	 * Get Pick Up Stockroom List
	 *
	 * @param   int  $companyId  Company id
	 *
	 * @return  mixed
	 * @since   1.0
	 */
	public static function getPickUpStockroomList($companyId = 0)
	{
		return self::getCompanyStockrooms($companyId, true);
	}

	/**
	 * Get Company Stockroom List
	 *
	 * @param   int   $companyId            Company id
	 * @param   bool  $getPickupStockrooms  Get just pick-up stockrooms
	 *
	 * @return  mixed
	 * @since   1.0
	 */
	public static function getCompanyStockrooms($companyId = 0, $getPickupStockrooms = false)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->select('*')
			->from($db->qn('#__redshopb_stockroom'))
			->where('state = 1')
			->order('ordering ASC');

		if ($getPickupStockrooms)
		{
			$query->where('pick_up = 1');
		}

		if ($companyId)
		{
			$query->where('(company_id = ' . (int) $companyId . ' OR company_id IS NULL)');
		}

		RFactory::getDispatcher()->trigger('onRedshopbGetPickUpStockroomList', array(&$query));

		return $db->setQuery($query)
			->loadObjectList();
	}

	/**
	 * Check is allowed current stockroom for pick up
	 *
	 * @param   integer  $stockroomId  Stockroom id
	 * @param   integer  $companyId    Company id
	 *
	 * @return  boolean
	 * @since   1.0
	 */
	public static function pickUpStockroomAllowed($stockroomId, $companyId)
	{
		if (!$stockroomId)
		{
			return true;
		}

		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->select('id')
			->from($db->qn('#__redshopb_stockroom'))
			->where('state = 1')
			->where('pick_up = 1')
			->where('(company_id = ' . (int) $companyId . ' OR company_id IS NULL)')
			->where('id = ' . (int) $stockroomId);

		return (bool) $db->setQuery($query)
			->loadResult();
	}

	/**
	 * Get stock visibility status for the current logged-in user
	 *
	 * @return  mixed
	 * @since   1.0
	 */
	public static function getStockVisibility()
	{
		if (RedshopbHelperUser::isRoot())
		{
			$showStockAs = 'actual_stock';
		}
		else
		{
			$showStockAs = RedshopbApp::getUser()
				->getCompany()
				->getStockVisibility();
		}

		return $showStockAs;
	}

	/**
	 * Checks if product quantity is in stock.
	 *
	 * @param   int    $productId      Product id to check.
	 * @param   int    $productItemId  Product item id to check.
	 * @param   int    $stockroomId    Stockroom to check status in.
	 * @param   float  $quantity       Product quantity to check.
	 *
	 * @return  boolean   True if there is enough products in provided stockroom, false otherwise.
	 *
	 * @since   1.12.65
	 */
	public static function isProductInStock($productId, $productItemId = 0, $stockroomId = 0, $quantity = 0.0)
	{
		if (!$productId)
		{
			return false;
		}

		$db = Factory::getDbo();

		if ($productItemId > 0)
		{
			$query = $db->getQuery(true)
				->select('SUM(amount) as amount')
				->select('SUM(unlimited) as unlimited')
				->from($db->qn('#__redshopb_stockroom_product_item_xref'))
				->where($db->qn('product_item_id') . ' = ' . (int) $productItemId);
		}
		else
		{
			$query = $db->getQuery(true)
				->select('SUM(amount) as amount')
				->select('SUM(unlimited) as unlimited')
				->from($db->qn('#__redshopb_stockroom_product_xref'))
				->where($db->qn('product_id') . ' = ' . (int) $productId);
		}

		if ($stockroomId > 0)
		{
			$query->where($db->qn('stockroom_id') . ' = ' . (int) $stockroomId);
		}

		$stockStatus = $db->setQuery($query)->loadObject();

		if (empty($stockroomId) && (empty($stockStatus) || ($stockStatus->unlimited == null && $stockStatus->amount == null)))
		{
			return true;
		}

		return ($stockStatus->unlimited > 0 || $stockStatus->amount >= $quantity);
	}

	/**
	 * Check if product has stockroom.
	 *
	 * @param   int  $productId      Product id.
	 * @param   int  $productItemId  Product item id.
	 *
	 * @return  boolean
	 *
	 * @since   1.12.69
	 */
	public static function hasAvailableStockroom($productId, $productItemId)
	{
		if ($productItemId)
		{
			return self::checkProductItemHasAvailableStockroom($productItemId, false);
		}

		return self::checkProductHasAvailableStockroom($productId, false);
	}

	/**
	 * Get holidays
	 *
	 * @param   integer  $countryId  Country it
	 *
	 * @return boolean|mixed
	 *
	 * @since  1.12.82
	 */
	public static function getHolidays($countryId = null)
	{
		$today    = new DateTime('today');
		$holidays = array('annual' => array(), 'date' => array());
		$db       = Factory::getDbo();
		$query    = $db->getQuery(true)
			->select('year, month, day')
			->from($db->qn('#__redshopb_holiday'))
			->where(
				'((year IS NOT NULL AND CAST(CONCAT(year, LPAD(month, 2, 0), LPAD(day, 2, 0)) AS SIGNED INTEGER) >= '
				. (int) $today->format('Ynd') . ') OR year IS NULL OR year = 0 OR year = ' . $db->q('') . ')'
			);

		if (!empty($countryId))
		{
			$query->where('country_id = ' . (int) $countryId);
		}

		$results = $db->setQuery($query)
			->loadObjectList();

		if (!empty($results))
		{
			foreach ($results as $result)
			{
				$date = sprintf("%02d", $result->month) . '-' . sprintf("%02d", $result->day);

				if ($result->year)
				{
					$holidays['date'][] = $result->year . '-' . $date;
				}
				else
				{
					$holidays['annual'][] = $date;
				}
			}
		}

		return $holidays;
	}

	/**
	 * getMinimumDeliveryPeriodForOrder
	 *
	 * @param   integer  $customerId    Customer id
	 * @param   string   $customerType  Customer type
	 * @param   boolean  $mainOrder     Main order = true, delay order = false
	 *
	 * @return integer
	 * @since  1.12.82
	 */
	public static function getMinimumDeliveryPeriodForOrder($customerId = 0, $customerType = '', $mainOrder = true)
	{
		$config  = RedshopbApp::getConfig();
		$minDate = $config->getInt('shipping_skip_from_today', 0);

		if (!$config->getAllowSplittingOrder())
		{
			return $minDate;
		}

		$cart       = RedshopbHelperCart::getCart($customerId, $customerType);
		$stockrooms = array();
		$dispatcher = RFactory::getDispatcher();
		$result     = -1;

		$defaultMinDate = $minDate;

		$items = $cart->get('items', array());

		if (!empty($items))
		{
			foreach ($items as $item)
			{
				if ($mainOrder ? $item['params']->get('delayed_order', 0) != 0 : $item['params']->get('delayed_order', 0) == 0)
				{
					continue;
				}

				if (empty($item['stockroomId']))
				{
					$dispatcher->trigger('onRedshopbExpandCheckMinimumDeliveryPeriodForOrder', array($item, &$result, $customerId, $customerType));

					if ($result != -1)
					{
						$minDate = $result;

						continue;
					}

					return $result;
				}

				if (!self::isProductInStock($item['productId'], $item['productItem'], $item['stockroomId'], $item['quantity']))
				{
					return -1;
				}

				if (!in_array($item['stockroomId'], $stockrooms))
				{
					$stockrooms[] = $item['stockroomId'];
				}
			}
		}

		$offers = $cart->get('offers', array());

		if (!empty($offers))
		{
			foreach ($offers as $offer)
			{
				foreach ($offer as $item)
				{
					if (empty($item['stockroomId']))
					{
						$dispatcher->trigger(
							'onRedshopbExpandCheckMinimumDeliveryPeriodForOrder',
							array($item, &$result, $customerId, $customerType)
						);

						if ($result != -1)
						{
							$minDate = $result;

							continue;
						}

						return $result;
					}

					if (!self::isProductInStock($item['productId'], $item['productItem'], $item['stockroomId'], $item['quantity']))
					{
						return -1;
					}

					if (!in_array($item['stockroomId'], $stockrooms))
					{
						$stockrooms[] = $item['stockroomId'];
					}
				}
			}
		}

		if (empty($stockrooms))
		{
			return $minDate;
		}

		$currentTime = Date::getInstance();
		$currentTime->setTimezone(new \DateTimeZone(Factory::getUser()->getParam('timezone', Factory::getConfig()->get('offset'))));

		foreach ($stockrooms as $stockroomId)
		{
			$minDeliveryTime = RedshopbEntityStockroom::getInstance($stockroomId)
				->get('min_delivery_time', 0);

			if (!$minDeliveryTime)
			{
				continue;
			}

			if ($config->getString('stockroom_delivery_time', 'hour') == 'hour')
			{
				if ($minDate >= $defaultMinDate + 1)
				{
					break;
				}

				$deliveryTime = clone $currentTime;
				$deliveryTime->modify('+' . $minDeliveryTime . ' hour');

				if ($currentTime->format('Ymd', true) < $deliveryTime->format('Ymd', true))
				{
					if ($minDate < $defaultMinDate + 1)
					{
						$minDate = $defaultMinDate + 1;
					}

					break;
				}
			}
			else
			{
				if ($minDeliveryTime > $minDate)
				{
					$minDate = $minDeliveryTime;
				}
			}
		}

		return $minDate;
	}

	/**
	 * checkIfProductCanBeShippedInTime
	 *
	 * @param   object|array  $item              Cart item
	 * @param   integer       $customerId        Customer id
	 * @param   string        $customerType      Customer type
	 * @param   null|string   $userShippingDate  User shipping date
	 *
	 * @return boolean
	 *
	 * @since 1.12.82
	 */
	public static function checkIfProductCanBeShippedInTime($item, $customerId, $customerType, $userShippingDate = null)
	{
		if (is_null($userShippingDate) || empty($userShippingDate))
		{
			return true;
		}

		$config         = RedshopbApp::getConfig();
		$minDate        = $config->getInt('shipping_skip_from_today', 0);
		$dispatcher     = RFactory::getDispatcher();
		$defaultMinDate = $minDate;
		$item           = (array) $item;
		$user           = Factory::getUser();
		$jConfig        = Factory::getConfig();
		$currentTime    = Date::getInstance();
		$currentTime->setTimezone(new \DateTimeZone($user->getParam('timezone', $jConfig->get('offset'))));

		if (empty($item['stockroomId']))
		{
			$result = -1;
			$dispatcher->trigger('onRedshopbExpandCheckMinimumDeliveryPeriodForOrder', array($item, &$result, $customerId, $customerType));

			if ($result != -1)
			{
				$minDate = $result;
			}
			else
			{
				return false;
			}
		}
		else
		{
			if (!self::isProductInStock($item['productId'], $item['productItem'], $item['stockroomId'], $item['quantity']))
			{
				return false;
			}

			$minDeliveryTime = RedshopbEntityStockroom::getInstance($item['stockroomId'])
				->get('min_delivery_time', 0);

			if ($minDeliveryTime)
			{
				if ($config->getString('stockroom_delivery_time', 'hour') == 'hour')
				{
					if ($minDate < $defaultMinDate + 1)
					{
						$deliveryTime = clone $currentTime;
						$deliveryTime->modify('+' . $minDeliveryTime . ' hour');

						if ($currentTime->format('Ymd', true) < $deliveryTime->format('Ymd', true))
						{
							if ($minDate < $defaultMinDate + 1)
							{
								$minDate = $defaultMinDate + 1;
							}
						}
					}
				}
				else
				{
					if ($minDeliveryTime > $minDate)
					{
						$minDate = $minDeliveryTime;
					}
				}
			}
		}

		$minTime = Date::getInstance();
		$minTime->setTimezone(new \DateTimeZone($user->getParam('timezone', $jConfig->get('offset'))));
		$userTime = Date::getInstance($userShippingDate);
		$userTime->setTimezone(new \DateTimeZone($user->getParam('timezone', $jConfig->get('offset'))));

		if ($minDate)
		{
			$minTime->modify('+' . $minDate . ' day');
		}

		if ($userTime->format('Ymd') >= $minTime->format('Ymd'))
		{
			return true;
		}

		return false;
	}
}
